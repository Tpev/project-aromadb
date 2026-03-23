<?php

namespace App\Support;

class InstallmentPlan
{
    public const MIN_COUNT = 2;
    public const MAX_COUNT = 12;
    public const MIN_STRIPE_CENTS = 50;

    /**
     * @param  mixed  $raw
     * @return int[]
     */
    public static function sanitizeAllowed($raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $clean = [];
        foreach ($raw as $value) {
            $n = (int) $value;
            if ($n < self::MIN_COUNT || $n > self::MAX_COUNT) {
                continue;
            }
            $clean[$n] = $n;
        }

        ksort($clean);
        return array_values($clean);
    }

    public static function build(int $totalCents, int $count): ?array
    {
        if ($count < self::MIN_COUNT || $count > self::MAX_COUNT) {
            return null;
        }

        if ($totalCents < 1) {
            return null;
        }

        $base = intdiv($totalCents, $count);
        $remainder = $totalCents - ($base * $count);
        $first = $base + $remainder;

        if ($base < self::MIN_STRIPE_CENTS || $first < self::MIN_STRIPE_CENTS) {
            return null;
        }

        return [
            'count' => $count,
            'total_cents' => $totalCents,
            'base_cents' => $base,
            'first_cents' => $first,
            'remainder_cents' => $remainder,
        ];
    }

    /**
     * @param  int[]  $allowed
     * @return array<int, array<string, int>>
     */
    public static function plansForAllowed(int $totalCents, array $allowed): array
    {
        $plans = [];
        foreach (self::sanitizeAllowed($allowed) as $count) {
            $plan = self::build($totalCents, $count);
            if ($plan) {
                $plans[$count] = $plan;
            }
        }
        return $plans;
    }
}

