<?php

namespace App\Support;

use App\Models\Product;
use Mews\Purifier\Facades\Purifier;

class ProductDescription
{
    public static function sanitize(?string $html): string
    {
        return Purifier::clean($html ?? '', [
            'HTML.Allowed' => 'p,br,strong,b,em,i,u',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty' => false,
        ]);
    }

    /** Keep the original plain-text contract for invoices, search and integrations. */
    public static function payload(array $input, ?Product $existing = null): array
    {
        if (array_key_exists('description_html', $input)) {
            $html = self::sanitize($input['description_html']);
            $text = preg_replace('/<p>\s*<br\s*\/?\s*>\s*<\/p>/i', '<p></p>', $html);
            $text = preg_replace('/<br\s*\/?\s*>/i', "\n", $text);
            $text = preg_replace('/<\/p>\s*<p>/i', "\n", $text);
            $text = trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            return [
                'description' => $text !== '' ? $text : null,
                'description_html' => $text !== '' && preg_match('/<(strong|b|em|i|u)>/i', $html) ? $html : null,
            ];
        }

        $text = $input['description'] ?? null;
        // Old forms and the textarea fallback must not erase unchanged formatting.
        if ($existing && (! array_key_exists('description', $input)
            || self::normalize($text) === self::normalize($existing->description))) {
            return [
                'description' => $existing->description,
                'description_html' => $existing->description_html,
            ];
        }

        return ['description' => $text, 'description_html' => null];
    }

    public static function html(Product $product): string
    {
        return filled($product->description_html)
            ? self::sanitize($product->description_html)
            : nl2br(e($product->description ?? ''));
    }

    private static function normalize(?string $text): string
    {
        return trim(str_replace(["\r\n", "\r"], "\n", $text ?? ''));
    }
}
