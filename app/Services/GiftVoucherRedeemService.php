<?php

namespace App\Services;

use App\Models\GiftVoucher;
use App\Models\GiftVoucherRedemption;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GiftVoucherRedeemService
{
    public function redeem(GiftVoucher $voucher, int $amountCents, ?string $note = null): GiftVoucher
    {
        if ($amountCents <= 0) {
            throw ValidationException::withMessages(['amount_eur' => 'Montant invalide.']);
        }

        if (!$voucher->is_active) {
            throw ValidationException::withMessages(['amount_eur' => 'Ce bon cadeau est désactivé.']);
        }

        if ($voucher->isExpired()) {
            throw ValidationException::withMessages(['amount_eur' => 'Ce bon cadeau est expiré.']);
        }

        if ($voucher->remaining_amount_cents <= 0) {
            throw ValidationException::withMessages(['amount_eur' => 'Ce bon cadeau est déjà épuisé.']);
        }

        if ($amountCents > $voucher->remaining_amount_cents) {
            throw ValidationException::withMessages([
                'amount_eur' => 'Le montant dépasse le solde restant du bon cadeau.',
            ]);
        }

        return DB::transaction(function () use ($voucher, $amountCents, $note) {
            GiftVoucherRedemption::create([
                'gift_voucher_id' => $voucher->id,
                'user_id' => auth()->id(),
                'amount_cents' => $amountCents,
                'note' => $note,
            ]);

            $voucher->remaining_amount_cents = $voucher->remaining_amount_cents - $amountCents;

            if ($voucher->remaining_amount_cents <= 0) {
                $voucher->remaining_amount_cents = 0;
                // You can either auto-disable or keep active but unusable.
                $voucher->is_active = false;
            }

            $voucher->save();

            return $voucher->fresh();
        });
    }
}
