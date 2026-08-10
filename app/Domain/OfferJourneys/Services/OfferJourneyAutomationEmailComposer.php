<?php

namespace App\Domain\OfferJourneys\Services;

use App\Models\User;
use Illuminate\Support\Str;

class OfferJourneyAutomationEmailComposer
{
    public function __construct(private readonly OfferJourneyEmailContent $emailContent)
    {
    }

    /** @return array{content: array, style: array, preheader: string} */
    public function compose(array $input, array $existingConfig, User $user): array
    {
        $validated = $this->emailContent->validatePortable(
            $this->blocks($input, $existingConfig),
            array_merge($this->emailContent->styleFor($user), [
                'primary_color' => $input['primary_color'] ?? data_get($existingConfig, 'email_style.primary_color', '#647a0b'),
            ]),
            $user
        );

        return [
            ...$validated,
            'preheader' => $this->emailContent->validateHeader($input['preheader'] ?? '', 'preheader', 180),
        ];
    }

    private function blocks(array $input, array $config): array
    {
        $existing = collect(data_get($config, 'email_content.blocks', []))->keyBy('type');
        $block = fn (string $type, array $data) => [
            'id' => data_get($existing->get($type), 'id', (string) Str::uuid()),
            'type' => $type,
            'data' => $data,
        ];
        $blocks = collect();

        if (filled($input['heading'] ?? null)) {
            $blocks->push($block('heading', ['text' => $input['heading'], 'level' => 'h1', 'align' => 'left']));
        }
        if (filled($input['image_url'] ?? null)) {
            $blocks->push($block('image', ['url' => $input['image_url'], 'alt' => $input['image_alt'] ?? '', 'width' => 'full', 'align' => 'center']));
        }
        $blocks->push($block('paragraph', ['text' => $input['body'], 'align' => 'left']));
        if (filled($input['button_label'] ?? null) && filled($input['button_url'] ?? null)) {
            $blocks->push($block('button', ['label' => $input['button_label'], 'url' => $input['button_url'], 'variant' => 'filled', 'align' => 'left']));
        }
        if (filled($input['details_text'] ?? null)) {
            $blocks->push($block('details', ['title' => $input['details_title'] ?: 'Informations pratiques', 'text' => $input['details_text']]));
        }
        $blocks->push($block('signature', ['text' => $input['signature'] ?: '{{nom_praticien}}', 'show_contact' => true]));

        return ['blocks' => $blocks->values()->all()];
    }
}
