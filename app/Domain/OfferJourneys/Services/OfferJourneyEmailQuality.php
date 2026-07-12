<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;

class OfferJourneyEmailQuality
{
    public function __construct(private readonly OfferJourneyEmailContent $content)
    {
    }

    /** @return array{errors: array<int, string>, warnings: array<int, string>} */
    public function inspect(OfferJourneyMessageCampaign $campaign, array $content, array $style, ?string $preheader): array
    {
        $errors = [];
        $warnings = [];
        if (trim((string) $campaign->subject) === '') {
            $errors[] = 'Ajoutez un objet à l’email.';
        }
        if (count($content['blocks'] ?? []) === 0) {
            $errors[] = 'Ajoutez au moins un bloc au message.';
        }
        if (trim((string) $preheader) === '') {
            $warnings[] = 'Ajoutez un texte d’aperçu pour mieux présenter le message dans la boîte de réception.';
        }
        $primary = $style['primary_color'] ?? '#647a0b';
        $contentBackground = $style['content_background'] ?? '#ffffff';
        if ($this->contrast($primary, $contentBackground) < 4.5) {
            $errors[] = 'La couleur principale manque de contraste avec le fond du message.';
        }
        $hasFilledButton = collect($content['blocks'] ?? [])->contains(
            fn ($block) => ($block['type'] ?? null) === 'button' && data_get($block, 'data.variant', 'filled') === 'filled'
        );
        if ($hasFilledButton && $this->contrast($primary, '#ffffff') < 4.5) {
            $errors[] = 'La couleur du bouton manque de contraste avec son texte blanc.';
        }
        $usesOfferLink = $this->content->containsVariable($content, 'lien_offre')
            || str_contains((string) $campaign->subject, '{{lien_offre}}')
            || str_contains((string) $preheader, '{{lien_offre}}');
        if ($usesOfferLink && $campaign->journeys()->count() === 0) {
            $buttonUsesOfferLink = collect($content['blocks'] ?? [])->contains(
                fn ($block) => ($block['type'] ?? null) === 'button' && data_get($block, 'data.url') === '{{lien_offre}}'
            );
            if ($buttonUsesOfferLink) {
                $errors[] = 'Associez une page avant d’utiliser son lien dans un bouton.';
            } else {
                $warnings[] = 'Le message utilise le lien de l’offre, mais aucune page n’est encore associée.';
            }
        }
        $bytes = strlen((string) json_encode($content, JSON_UNESCAPED_UNICODE));
        if ($bytes > 100000) {
            $errors[] = 'Le contenu de l’email est trop volumineux.';
        } elseif ($bytes > 70000) {
            $warnings[] = 'Le message devient volumineux. Retirez quelques blocs si possible.';
        }

        return compact('errors', 'warnings');
    }

    private function contrast(string $first, string $second): float
    {
        $luminance = function (string $hex): float {
            $values = array_map(fn ($offset) => hexdec(substr($hex, $offset, 2)) / 255, [1, 3, 5]);
            $values = array_map(fn ($value) => $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4, $values);

            return 0.2126 * $values[0] + 0.7152 * $values[1] + 0.0722 * $values[2];
        };
        [$light, $dark] = [$luminance($first), $luminance($second)];

        return (max($light, $dark) + 0.05) / (min($light, $dark) + 0.05);
    }
}
