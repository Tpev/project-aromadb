<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class DocumentNumberFormatter
{
    public const DEFAULT_INVOICE_FORMAT = 'FAC-{YYYY}-{SEQ:4}';

    public const DEFAULT_QUOTE_FORMAT = 'DEV-{YYYY}-{SEQ:4}';

    public const RESET_FREQUENCIES = ['never', 'yearly', 'monthly'];

    public function validate(string $format): string
    {
        $format = trim($format);

        if ($format === '') {
            $this->fail('Le format de numérotation est obligatoire.');
        }

        if (mb_strlen($format) > 64) {
            $this->fail('Le format de numérotation ne peut pas dépasser 64 caractères.');
        }

        preg_match_all('/\{(?:YYYY|YY|MM|SEQ(?::(?:[1-9]|10))?)\}/', $format, $matches);
        $tokens = $matches[0] ?? [];
        $sequenceTokens = array_values(array_filter(
            $tokens,
            fn (string $token) => str_starts_with($token, '{SEQ')
        ));

        if (count($sequenceTokens) !== 1) {
            $this->fail('Le format doit contenir exactement une séquence : {SEQ} ou {SEQ:4}.');
        }

        $yearTokens = array_values(array_filter(
            $tokens,
            fn (string $token) => in_array($token, ['{YYYY}', '{YY}'], true)
        ));

        if (count($yearTokens) > 1 || count(array_filter($tokens, fn (string $token) => $token === '{MM}')) > 1) {
            $this->fail('Les éléments année et mois ne peuvent apparaître qu’une seule fois.');
        }

        $staticText = preg_replace('/\{(?:YYYY|YY|MM|SEQ(?::(?:[1-9]|10))?)\}/', '', $format);
        if (str_contains((string) $staticText, '{') || str_contains((string) $staticText, '}')) {
            $this->fail('Le format contient un élément inconnu.');
        }

        if (! preg_match('/^[\pL\pN ._\/#-]*$/u', (string) $staticText)) {
            $this->fail('Le format contient un caractère non autorisé.');
        }

        if (mb_strlen($this->format($format, now(), PHP_INT_MAX)) > 128) {
            $this->fail('Le numéro généré serait trop long.');
        }

        return $format;
    }

    public function format(string $format, CarbonInterface $date, int $sequence): string
    {
        $rendered = str_replace(
            ['{YYYY}', '{YY}', '{MM}'],
            [$date->format('Y'), $date->format('y'), $date->format('m')],
            $format
        );

        return (string) preg_replace_callback(
            '/\{SEQ(?::([1-9]|10))?\}/',
            function (array $matches) use ($sequence): string {
                $padding = isset($matches[1]) ? (int) $matches[1] : 1;

                return str_pad((string) $sequence, $padding, '0', STR_PAD_LEFT);
            },
            $rendered
        );
    }

    public function periodKey(string $resetFrequency, CarbonInterface $date): string
    {
        return match ($resetFrequency) {
            'monthly' => $date->format('Ym'),
            'yearly' => $date->format('Y'),
            default => 'all',
        };
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['numbering_format' => $message]);
    }
}
