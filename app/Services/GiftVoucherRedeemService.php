<?php

namespace App\Services;

use App\Models\GiftVoucher;
use App\Models\GiftVoucherRedemption;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GiftVoucherRedeemService
{
    public function redeem(
        GiftVoucher $voucher,
        int $amountCents,
        ?string $note = null,
        ?int $actorUserId = null,
        ?int $appointmentId = null,
        ?int $invoiceId = null,
        string $source = 'manual',
        string $status = 'applied'
    ): GiftVoucher
    {
        if ($amountCents <= 0) {
            throw ValidationException::withMessages(['amount_eur' => 'Montant invalide.']);
        }

        $actorUserId = $actorUserId ?: auth()->id();
        if (!$actorUserId) {
            throw ValidationException::withMessages(['amount_eur' => 'Utilisateur introuvable pour appliquer le bon cadeau.']);
        }

        return DB::transaction(function () use ($voucher, $amountCents, $note, $actorUserId, $appointmentId, $invoiceId, $source, $status) {
            /** @var GiftVoucher $freshVoucher */
            $freshVoucher = GiftVoucher::query()
                ->whereKey($voucher->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$freshVoucher->is_active) {
                throw ValidationException::withMessages(['amount_eur' => 'Ce bon cadeau est désactivé.']);
            }

            if ($freshVoucher->isExpired()) {
                throw ValidationException::withMessages(['amount_eur' => 'Ce bon cadeau est expiré.']);
            }

            if ($freshVoucher->remaining_amount_cents <= 0) {
                throw ValidationException::withMessages(['amount_eur' => 'Ce bon cadeau est déjà épuisé.']);
            }

            if ($amountCents > $freshVoucher->remaining_amount_cents) {
                throw ValidationException::withMessages([
                    'amount_eur' => 'Le montant dépasse le solde restant du bon cadeau.',
                ]);
            }

            GiftVoucherRedemption::create([
                'gift_voucher_id' => $freshVoucher->id,
                'user_id' => $actorUserId,
                'amount_cents' => $amountCents,
                'note' => $note,
                'appointment_id' => $appointmentId,
                'invoice_id' => $invoiceId,
                'source' => $source,
                'status' => $status,
            ]);

            $freshVoucher->remaining_amount_cents = $freshVoucher->remaining_amount_cents - $amountCents;

            if ($freshVoucher->remaining_amount_cents <= 0) {
                $freshVoucher->remaining_amount_cents = 0;
                // You can either auto-disable or keep active but unusable.
                $freshVoucher->is_active = false;
            }

            $freshVoucher->save();

            return $freshVoucher->fresh();
        });
    }
}
