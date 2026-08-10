<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class OfferJourneyEmailRenderer
{
    public function __construct(private readonly OfferJourneyEmailContent $content)
    {
    }

    /** @return array{html: string, text: string} */
    public function render(
        OfferJourneyMessageCampaign $campaign,
        array $variables,
        string $unsubscribeUrl,
        string $category = 'marketing',
        ?array $content = null,
        ?array $style = null,
        ?string $preheader = null
    ): array {
        $user = $campaign->user ?: User::query()->findOrFail($campaign->user_id);
        $content ??= $campaign->content_json ?: $this->content->defaultContent();
        $style = array_merge($this->content->defaultStyle(), $style ?? $campaign->style_json ?? []);
        $preheader ??= $campaign->preheader ?: '';
        $assets = ($campaign->relationLoaded('emailAssets') ? $campaign->emailAssets : $campaign->emailAssets()->get())->keyBy('id');
        $therapistName = $user->company_name
            ?: trim(($user->first_name ?? '').' '.($user->last_name ?? ''))
            ?: $user->name
            ?: 'Votre praticien';
        $variables = array_merge([
            'prenom' => 'à vous',
            'offre' => $campaign->name,
            'nom_praticien' => $therapistName,
            'lien_offre' => '',
            'lien_ressource' => '',
        ], $variables);

        $htmlRows = '';
        $textParts = [];
        foreach ($content['blocks'] ?? [] as $block) {
            $htmlRows .= $this->htmlBlock($block, $style, $assets, $variables, $user);
            $text = $this->textBlock($block, $assets, $variables, $user);
            if ($text !== '') {
                $textParts[] = $text;
            }
        }

        $logoUrl = null;
        if ($user->portal_logo_path && Storage::disk('public')->exists($user->portal_logo_path)) {
            $logoUrl = Storage::disk('public')->url($user->portal_logo_path);
        }

        $html = view('emails.offer-journeys.message-html', [
            'messageSubject' => $this->replace($campaign->subject, $variables),
            'therapistName' => $therapistName,
            'body' => '',
            'renderedBlocksHtml' => $htmlRows,
            'preheader' => $this->replace($preheader, $variables),
            'unsubscribeUrl' => $unsubscribeUrl,
            'category' => $category,
            'emailStyle' => $style,
            'logoUrl' => $logoUrl,
        ])->render();

        $text = implode("\n\n", $textParts)."\n\n---\nCe message vous est adressé par $therapistName via Olithea.";
        if ($category === 'marketing') {
            $text .= "\nSe désinscrire de ces suivis : $unsubscribeUrl";
        }

        return ['html' => $html, 'text' => trim($text)];
    }

    /** @return array{html: string, text: string} */
    public function renderPortable(
        User $user,
        string $subject,
        string $offerName,
        array $content,
        array $style,
        array $variables,
        string $unsubscribeUrl,
        string $category = 'marketing',
        ?string $preheader = null
    ): array {
        $campaign = new OfferJourneyMessageCampaign([
            'user_id' => $user->id,
            'name' => $offerName,
            'subject' => $subject,
            'preheader' => $preheader,
            'content_json' => $content,
            'style_json' => $style,
        ]);
        $campaign->setRelation('user', $user);
        $campaign->setRelation('emailAssets', collect());

        return $this->render($campaign, $variables, $unsubscribeUrl, $category, $content, $style, $preheader);
    }

    public function plainBody(OfferJourneyMessageCampaign $campaign, array $content): string
    {
        $user = $campaign->user ?: User::query()->findOrFail($campaign->user_id);
        $assets = $campaign->emailAssets()->get()->keyBy('id');
        $parts = [];
        foreach ($content['blocks'] ?? [] as $block) {
            $text = $this->textBlock($block, $assets, [
                'prenom' => '{{prenom}}', 'offre' => '{{offre}}',
                'nom_praticien' => '{{nom_praticien}}', 'lien_offre' => '{{lien_offre}}',
                'lien_ressource' => '{{lien_ressource}}',
            ], $user);
            if ($text !== '') {
                $parts[] = $text;
            }
        }

        return mb_substr(implode("\n\n", $parts), 0, 6000);
    }

    private function htmlBlock(array $block, array $style, $assets, array $variables, User $user): string
    {
        $type = $block['type'] ?? '';
        $data = $block['data'] ?? [];
        $primary = $style['primary_color'];
        $baseSize = ['compact' => 14, 'normal' => 16, 'large' => 18][$style['text_size']] ?? 16;
        $requestedAlign = $data['align'] ?? 'left';
        $align = in_array($requestedAlign, ['left', 'center', 'right'], true) ? $requestedAlign : 'left';
        $text = fn (string $value): string => nl2br(e($this->replace($value, $variables)));

        return match ($type) {
            'heading' => '<tr><td style="padding:12px 28px 6px;text-align:'.$align.';font-size:'.(($data['level'] ?? 'h2') === 'h1' ? 28 : 22).'px;line-height:1.25;font-weight:700;color:#1f2937;">'.$text($data['text'] ?? '').'</td></tr>',
            'paragraph' => '<tr><td style="padding:8px 28px;text-align:'.$align.';font-size:'.$baseSize.'px;line-height:1.65;color:#374151;">'.$text($data['text'] ?? '').'</td></tr>',
            'image' => $this->imageHtml($data, $assets),
            'button' => $this->buttonHtml($data, $style, $variables),
            'callout' => '<tr><td style="padding:10px 28px;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td style="padding:18px;border-left:4px solid '.$primary.';background:'.(($data['tone'] ?? 'olive') === 'olive' ? '#f4f7e8' : '#f3f4f6').';font-size:'.$baseSize.'px;line-height:1.55;color:#374151;">'.(($data['title'] ?? '') !== '' ? '<strong style="display:block;margin-bottom:6px;color:#1f2937;">'.$text($data['title']).'</strong>' : '').$text($data['text'] ?? '').'</td></tr></table></td></tr>',
            'divider' => '<tr><td style="padding:16px 28px;"><div style="height:1px;background:#e5e7eb;font-size:1px;line-height:1px;">&nbsp;</div></td></tr>',
            'spacer' => '<tr><td height="'.(['small' => 12, 'medium' => 24, 'large' => 40][$data['size'] ?? 'medium'] ?? 24).'" style="font-size:1px;line-height:1px;">&nbsp;</td></tr>',
            'details' => '<tr><td style="padding:10px 28px;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #d1d5db;"><tr><td style="padding:16px;font-size:'.$baseSize.'px;line-height:1.55;color:#374151;"><strong style="display:block;margin-bottom:8px;color:#1f2937;">'.$text($data['title'] ?? 'Informations pratiques').'</strong>'.$text($data['text'] ?? '').'</td></tr></table></td></tr>',
            'signature' => '<tr><td style="padding:14px 28px 22px;font-size:'.$baseSize.'px;line-height:1.55;color:#374151;">'.$text($data['text'] ?? '{{nom_praticien}}').$this->contactHtml($data, $user).'</td></tr>',
            default => '',
        };
    }

    private function imageHtml(array $data, $assets): string
    {
        if (filled($data['url'] ?? null)) {
            $width = ['full' => 564, 'large' => 480, 'medium' => 320][$data['width'] ?? 'full'] ?? 564;
            $requestedAlign = $data['align'] ?? 'center';
            $align = in_array($requestedAlign, ['left', 'center', 'right'], true) ? $requestedAlign : 'center';

            return '<tr><td align="'.$align.'" style="padding:10px 28px;"><img src="'.e($data['url']).'" width="'.$width.'" alt="'.e($data['alt'] ?? '').'" style="display:block;width:100%;max-width:'.$width.'px;height:auto;border:0;"></td></tr>';
        }

        $asset = $assets->get((int) ($data['asset_id'] ?? 0));
        if (! $asset || ! Storage::disk('public')->exists($asset->path)) {
            return '';
        }
        $width = ['full' => 564, 'large' => 480, 'medium' => 320][$data['width'] ?? 'full'] ?? 564;
        $requestedAlign = $data['align'] ?? 'center';
        $align = in_array($requestedAlign, ['left', 'center', 'right'], true) ? $requestedAlign : 'center';
        $url = Storage::disk('public')->url($asset->path);

        return '<tr><td align="'.$align.'" style="padding:10px 28px;"><img src="'.e($url).'" width="'.$width.'" alt="'.e($data['alt'] ?? '').'" style="display:block;width:100%;max-width:'.$width.'px;height:auto;border:0;"></td></tr>';
    }

    private function buttonHtml(array $data, array $style, array $variables): string
    {
        $primary = $style['primary_color'];
        $requestedAlign = $data['align'] ?? 'left';
        $align = in_array($requestedAlign, ['left', 'center', 'right'], true) ? $requestedAlign : 'left';
        $radius = ['square' => 2, 'rounded' => 6, 'pill' => 24][$style['button_style']] ?? 6;
        $filled = ($data['variant'] ?? 'filled') === 'filled';
        $url = $this->replace($data['url'] ?? '', $variables);
        $label = e($this->replace($data['label'] ?? '', $variables));

        return '<tr><td align="'.$align.'" style="padding:14px 28px;"><table role="presentation" cellspacing="0" cellpadding="0"><tr><td style="border:1px solid '.$primary.';border-radius:'.$radius.'px;background:'.($filled ? $primary : '#ffffff').';"><a href="'.e($url).'" style="display:inline-block;padding:12px 20px;font-size:16px;line-height:1.2;font-weight:700;color:'.($filled ? '#ffffff' : $primary).';text-decoration:none;border-radius:'.$radius.'px;">'.$label.'</a></td></tr></table></td></tr>';
    }

    private function contactHtml(array $data, User $user): string
    {
        if (! ($data['show_contact'] ?? true)) {
            return '';
        }
        $parts = array_filter([$user->company_email ?: $user->email, $user->company_phone]);

        return $parts ? '<span style="display:block;margin-top:6px;font-size:13px;color:#6b7280;">'.e(implode(' · ', $parts)).'</span>' : '';
    }

    private function textBlock(array $block, $assets, array $variables, User $user): string
    {
        $data = $block['data'] ?? [];

        return match ($block['type'] ?? '') {
            'heading', 'paragraph' => $this->replace($data['text'] ?? '', $variables),
            'image' => '[Image : '.($data['alt'] ?? '').']',
            'button' => $this->replace($data['label'] ?? '', $variables).' : '.$this->replace($data['url'] ?? '', $variables),
            'callout' => trim($this->replace(($data['title'] ?? '')."\n".($data['text'] ?? ''), $variables)),
            'details' => trim($this->replace(($data['title'] ?? '')."\n".($data['text'] ?? ''), $variables)),
            'signature' => trim($this->replace($data['text'] ?? '{{nom_praticien}}', $variables).(($data['show_contact'] ?? true) ? "\n".implode(' · ', array_filter([$user->company_email ?: $user->email, $user->company_phone])) : '')),
            default => '',
        };
    }

    private function replace(string $value, array $variables): string
    {
        return strtr($value, collect($variables)->mapWithKeys(fn ($value, $key) => ['{{'.$key.'}}' => (string) $value])->all());
    }
}
