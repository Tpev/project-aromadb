<?php

namespace App\Support;

use App\Models\Appointment;

class ProductCalendarColor
{
    public const RULES = ['nullable', 'string', 'regex:/\A#[0-9a-fA-F]{6}\z/'];

    public const PALETTE = [
        '#2563EB' => 'Bleu',
        '#0F766E' => 'Turquoise',
        '#7C3AED' => 'Violet',
        '#DB2777' => 'Rose',
        '#C2410C' => 'Orange',
        '#B91C1C' => 'Rouge',
        '#647A0B' => 'Olive',
        '#FDE68A' => 'Jaune clair',
    ];

    public static function normalize(mixed $value): ?string
    {
        return is_string($value) && preg_match('/\A#[0-9a-fA-F]{6}\z/', $value)
            ? strtoupper($value)
            : null;
    }

    public static function payload(array $input): array
    {
        // Omitting the field in older forms must not clear an existing color.
        return array_key_exists('calendar_color', $input)
            ? ['calendar_color' => self::normalize($input['calendar_color'])]
            : [];
    }

    public static function forAppointment(Appointment $appointment): ?string
    {
        if ($appointment->external || $appointment->isCancelled()) {
            return null;
        }

        $product = $appointment->product;

        if (! $product || (int) $product->user_id !== (int) $appointment->user_id) {
            return null;
        }

        return self::normalize($product->calendar_color);
    }

    public static function textColor(string $background): string
    {
        $background = self::normalize($background) ?? '#FFFFFF';
        $channels = array_map(function (int $offset) use ($background): float {
            $channel = hexdec(substr($background, $offset, 2)) / 255;

            return $channel <= 0.04045 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;
        }, [1, 3, 5]);
        $luminance = $channels[0] * 0.2126 + $channels[1] * 0.7152 + $channels[2] * 0.0722;

        // Choose the higher contrast of black and white (at least 4.5:1).
        return ($luminance + 0.05) / 0.05 >= 1.05 / ($luminance + 0.05) ? '#000000' : '#FFFFFF';
    }
}
