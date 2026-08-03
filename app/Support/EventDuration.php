<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class EventDuration
{
    public const MAX_MINUTES = 525_600;

    private const UNIT_FACTORS = [
        'minutes' => 1,
        'hours' => 60,
        'days' => 1_440,
    ];

    public static function rules(): array
    {
        return [
            'duration' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_MINUTES, 'required_without:duration_value'],
            'duration_value' => ['nullable', 'numeric', 'gt:0', 'required_without:duration'],
            'duration_unit' => ['nullable', 'required_with:duration_value', Rule::in(array_keys(self::UNIT_FACTORS))],
        ];
    }

    public static function messages(): array
    {
        return [
            'duration.required_without' => 'Indiquez la durée de l’événement.',
            'duration_value.required_without' => 'Indiquez la durée de l’événement.',
            'duration_value.gt' => 'La durée doit être supérieure à zéro.',
            'duration_unit.required_with' => 'Choisissez une unité de durée.',
            'duration_unit.in' => 'L’unité de durée sélectionnée est invalide.',
        ];
    }

    public static function normalizePayload(array $validated): array
    {
        $hasFriendlyInput = Arr::exists($validated, 'duration_value')
            && $validated['duration_value'] !== null
            && $validated['duration_value'] !== '';

        if ($hasFriendlyInput) {
            $unit = (string) ($validated['duration_unit'] ?? '');
            $factor = self::UNIT_FACTORS[$unit] ?? null;

            if ($factor === null) {
                throw ValidationException::withMessages([
                    'duration_unit' => 'L’unité de durée sélectionnée est invalide.',
                ]);
            }

            $minutes = (int) round((float) $validated['duration_value'] * $factor);
            $errorField = 'duration_value';
        } else {
            $minutes = (int) ($validated['duration'] ?? 0);
            $errorField = 'duration';
        }

        if ($minutes < 1 || $minutes > self::MAX_MINUTES) {
            throw ValidationException::withMessages([
                $errorField => 'La durée doit être comprise entre une minute et un an.',
            ]);
        }

        $validated['duration'] = $minutes;
        unset($validated['duration_value'], $validated['duration_unit']);

        return $validated;
    }

    public static function inputForMinutes(?int $minutes): array
    {
        $minutes = max(1, (int) ($minutes ?: 60));

        if ($minutes % self::UNIT_FACTORS['days'] === 0) {
            return ['value' => $minutes / self::UNIT_FACTORS['days'], 'unit' => 'days'];
        }

        if ($minutes % self::UNIT_FACTORS['hours'] === 0) {
            return ['value' => $minutes / self::UNIT_FACTORS['hours'], 'unit' => 'hours'];
        }

        return ['value' => $minutes, 'unit' => 'minutes'];
    }

    public static function format(?int $minutes): string
    {
        if ($minutes === null || $minutes < 1) {
            return '—';
        }

        $days = intdiv($minutes, self::UNIT_FACTORS['days']);
        $remaining = $minutes % self::UNIT_FACTORS['days'];
        $hours = intdiv($remaining, self::UNIT_FACTORS['hours']);
        $remainingMinutes = $remaining % self::UNIT_FACTORS['hours'];
        $parts = [];

        if ($days > 0) {
            $parts[] = $days.' '.($days === 1 ? 'jour' : 'jours');
        }

        if ($hours > 0) {
            $parts[] = $hours.' h'.($remainingMinutes > 0 ? ' '.$remainingMinutes : '');
        } elseif ($remainingMinutes > 0) {
            $parts[] = $remainingMinutes.' min';
        }

        return implode(' ', $parts);
    }
}
