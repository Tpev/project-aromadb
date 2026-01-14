<?php

namespace App\Services;

use App\Models\GiftVoucher;
use Illuminate\Support\Str;

class GiftVoucherCodeGenerator
{
    public function generateUniqueCode(): string
    {
        // Example: AM-XXXX-XXXX-XXXX (uppercase alnum)
        // 4 blocks of 4 => 16 chars + separators.
        for ($i = 0; $i < 20; $i++) {
            $raw = strtoupper(Str::random(16));
            $raw = preg_replace('/[^A-Z0-9]/', 'A', $raw);

            $code = 'AM-' . implode('-', str_split($raw, 4));

            $exists = GiftVoucher::where('code', $code)->exists();
            if (!$exists) return $code;
        }

        // Worst-case fallback
        return 'AM-' . strtoupper(Str::uuid()->toString());
    }
}
