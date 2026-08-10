<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyEmailAsset;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OfferJourneyEmailContent
{
    public const VERSION = 'blocks-v1';
    public const TYPES = ['heading', 'paragraph', 'image', 'button', 'callout', 'divider', 'spacer', 'details', 'signature'];
    public const VARIABLES = ['prenom', 'offre', 'nom_praticien', 'lien_offre', 'lien_ressource'];

    public function defaultContent(): array
    {
        return ['blocks' => [
            ['id' => (string) Str::uuid(), 'type' => 'heading', 'data' => ['text' => 'Une information pour vous', 'level' => 'h1', 'align' => 'left']],
            ['id' => (string) Str::uuid(), 'type' => 'paragraph', 'data' => ['text' => "Bonjour {{prenom}},\n\nRédigez ici votre message.", 'align' => 'left']],
            ['id' => (string) Str::uuid(), 'type' => 'button', 'data' => ['label' => 'Découvrir', 'url' => '{{lien_offre}}', 'variant' => 'filled', 'align' => 'left']],
            ['id' => (string) Str::uuid(), 'type' => 'signature', 'data' => ['text' => '{{nom_praticien}}', 'show_contact' => true]],
        ]];
    }

    public function defaultStyle(): array
    {
        return [
            'primary_color' => '#647a0b',
            'background_color' => '#f7f8f3',
            'content_background' => '#ffffff',
            'button_style' => 'rounded',
            'text_size' => 'normal',
        ];
    }

    public function styleFor(User $user): array
    {
        $style = $this->defaultStyle();
        $primary = data_get($user->konva_branding_settings, 'colors.primary');
        $background = data_get($user->konva_branding_settings, 'colors.background');
        if (is_string($primary) && preg_match('/^#[0-9a-fA-F]{6}$/', $primary)) {
            $style['primary_color'] = strtolower($primary);
        }
        if (is_string($background) && preg_match('/^#[0-9a-fA-F]{6}$/', $background)) {
            $style['background_color'] = strtolower($background);
        }

        return $style;
    }

    /** @return array{content: array, style: array} */
    public function validate(array $content, array $style, User $user, OfferJourneyMessageCampaign $campaign): array
    {
        return $this->validateContent($content, $style, $user, $campaign);
    }

    /** @return array{content: array, style: array} */
    public function validatePortable(array $content, array $style, User $user): array
    {
        return $this->validateContent($content, $style, $user, null);
    }

    private function validateContent(array $content, array $style, User $user, ?OfferJourneyMessageCampaign $campaign): array
    {
        $blocks = $content['blocks'] ?? null;
        if (! is_array($blocks) || count($blocks) > 60) {
            throw ValidationException::withMessages(['content' => 'Un email peut contenir jusqu’à 60 blocs.']);
        }

        $normalized = [];
        foreach (array_values($blocks) as $index => $block) {
            if (! is_array($block) || ! in_array($block['type'] ?? null, self::TYPES, true)) {
                throw ValidationException::withMessages(["content.blocks.$index" => 'Ce type de bloc n’est pas autorisé.']);
            }
            $type = $block['type'];
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
            $id = preg_match('/^[A-Za-z0-9_-]{1,80}$/', (string) ($block['id'] ?? ''))
                ? (string) $block['id']
                : (string) Str::uuid();
            $normalized[] = ['id' => $id, 'type' => $type, 'data' => $this->validateBlock($type, $data, $index, $user, $campaign)];
        }

        return [
            'content' => ['blocks' => $normalized],
            'style' => $this->validateStyle($style),
        ];
    }

    public function containsVariable(array $content, string $variable): bool
    {
        return str_contains((string) json_encode($content, JSON_UNESCAPED_UNICODE), '{{'.$variable.'}}');
    }

    public function validateHeader(mixed $value, string $field, int $max): string
    {
        $value = trim((string) $value);
        if (mb_strlen($value) > $max) {
            throw ValidationException::withMessages([$field => "Ce texte dépasse $max caractères."]);
        }
        if (preg_match('/<\s*\/?\s*[a-z][^>]*>/i', $value)) {
            throw ValidationException::withMessages([$field => 'Le HTML n’est pas autorisé.']);
        }
        preg_match_all('/{{\s*([^{}]+?)\s*}}/', $value, $matches);
        foreach ($matches[1] ?? [] as $variable) {
            if (! in_array(trim($variable), self::VARIABLES, true)) {
                throw ValidationException::withMessages([$field => 'La variable {{'.trim($variable).'}} n’est pas autorisée.']);
            }
        }

        return $value;
    }

    private function validateBlock(string $type, array $data, int $index, User $user, ?OfferJourneyMessageCampaign $campaign): array
    {
        return match ($type) {
            'heading' => [
                'text' => $this->plain($data['text'] ?? '', 180, $index, true),
                'level' => $this->choice($data['level'] ?? 'h2', ['h1', 'h2'], 'niveau de titre', $index),
                'align' => $this->choice($data['align'] ?? 'left', ['left', 'center', 'right'], 'alignement', $index),
            ],
            'paragraph' => [
                'text' => $this->plain($data['text'] ?? '', 3000, $index, true),
                'align' => $this->choice($data['align'] ?? 'left', ['left', 'center', 'right'], 'alignement', $index),
            ],
            'image' => $this->validateImage($data, $index, $user, $campaign),
            'button' => [
                'label' => $this->plain($data['label'] ?? '', 80, $index, true),
                'url' => $this->url($data['url'] ?? '', $index),
                'variant' => $this->choice($data['variant'] ?? 'filled', ['filled', 'outline'], 'style de bouton', $index),
                'align' => $this->choice($data['align'] ?? 'left', ['left', 'center', 'right'], 'alignement', $index),
            ],
            'callout' => [
                'title' => $this->plain($data['title'] ?? '', 120, $index),
                'text' => $this->plain($data['text'] ?? '', 1200, $index, true),
                'tone' => $this->choice($data['tone'] ?? 'olive', ['olive', 'neutral'], 'style d’encadré', $index),
            ],
            'divider' => [],
            'spacer' => ['size' => $this->choice($data['size'] ?? 'medium', ['small', 'medium', 'large'], 'espacement', $index)],
            'details' => [
                'title' => $this->plain($data['title'] ?? 'Informations pratiques', 120, $index, true),
                'text' => $this->plain($data['text'] ?? '', 1500, $index, true),
            ],
            'signature' => [
                'text' => $this->plain($data['text'] ?? '{{nom_praticien}}', 500, $index, true),
                'show_contact' => (bool) ($data['show_contact'] ?? true),
            ],
        };
    }

    private function validateImage(array $data, int $index, User $user, ?OfferJourneyMessageCampaign $campaign): array
    {
        if (! $campaign) {
            $url = trim((string) ($data['url'] ?? ''));
            if (! filter_var($url, FILTER_VALIDATE_URL) || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
                throw ValidationException::withMessages(["content.blocks.$index.url" => 'Utilisez une adresse complète commençant par https:// pour l’image.']);
            }

            return [
                'url' => $url,
                'alt' => $this->plain($data['alt'] ?? '', 180, $index, true),
                'width' => $this->choice($data['width'] ?? 'full', ['full', 'large', 'medium'], 'largeur d’image', $index),
                'align' => $this->choice($data['align'] ?? 'center', ['left', 'center', 'right'], 'alignement', $index),
            ];
        }

        $assetId = (int) ($data['asset_id'] ?? 0);
        $owned = OfferJourneyEmailAsset::query()
            ->whereKey($assetId)
            ->where('user_id', $user->id)
            ->where('offer_journey_message_campaign_id', $campaign->id)
            ->exists();
        if (! $owned) {
            throw ValidationException::withMessages(["content.blocks.$index.asset_id" => 'Choisissez une image appartenant à cette campagne.']);
        }

        return [
            'asset_id' => $assetId,
            'alt' => $this->plain($data['alt'] ?? '', 180, $index, true),
            'width' => $this->choice($data['width'] ?? 'full', ['full', 'large', 'medium'], 'largeur d’image', $index),
            'align' => $this->choice($data['align'] ?? 'center', ['left', 'center', 'right'], 'alignement', $index),
        ];
    }

    private function validateStyle(array $style): array
    {
        $defaults = $this->defaultStyle();
        $style = array_merge($defaults, $style);
        foreach (['primary_color', 'background_color', 'content_background'] as $field) {
            if (! preg_match('/^#[0-9a-fA-F]{6}$/', (string) $style[$field])) {
                throw ValidationException::withMessages(["style.$field" => 'Choisissez une couleur au format hexadécimal.']);
            }
            $style[$field] = strtolower($style[$field]);
        }
        $style['button_style'] = $this->choice($style['button_style'], ['rounded', 'square', 'pill'], 'style de bouton', 0);
        $style['text_size'] = $this->choice($style['text_size'], ['compact', 'normal', 'large'], 'taille de texte', 0);

        return array_intersect_key($style, $defaults);
    }

    private function plain(mixed $value, int $max, int $index, bool $required = false): string
    {
        $value = trim((string) $value);
        if (($required && $value === '') || mb_strlen($value) > $max) {
            throw ValidationException::withMessages(["content.blocks.$index" => $required && $value === '' ? 'Ce bloc ne peut pas être vide.' : "Ce texte dépasse $max caractères."]);
        }
        if (preg_match('/<\s*\/?\s*[a-z][^>]*>/i', $value)) {
            throw ValidationException::withMessages(["content.blocks.$index" => 'Le HTML n’est pas autorisé dans les emails.']);
        }
        $this->variables($value, $index);

        return $value;
    }

    private function variables(string $value, int $index): void
    {
        preg_match_all('/{{\s*([^{}]+?)\s*}}/', $value, $matches);
        foreach ($matches[1] ?? [] as $variable) {
            if (! in_array(trim($variable), self::VARIABLES, true)) {
                throw ValidationException::withMessages(["content.blocks.$index" => 'La variable {{'.trim($variable).'}} n’est pas autorisée.']);
            }
        }
    }

    private function url(mixed $value, int $index): string
    {
        $value = trim((string) $value);
        if (in_array($value, ['{{lien_offre}}', '{{lien_ressource}}'], true)) {
            return $value;
        }
        if (mb_strlen($value) > 2000 || ! filter_var($value, FILTER_VALIDATE_URL) || ! in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw ValidationException::withMessages(["content.blocks.$index.url" => 'Utilisez un lien complet commençant par https://.']);
        }

        return $value;
    }

    private function choice(mixed $value, array $allowed, string $label, int $index): string
    {
        if (! in_array($value, $allowed, true)) {
            throw ValidationException::withMessages(["content.blocks.$index" => "Le $label n’est pas autorisé."]);
        }

        return (string) $value;
    }
}
