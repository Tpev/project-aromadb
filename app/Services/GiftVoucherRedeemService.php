<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\GiftVoucher;
use App\Models\GiftVoucherRedemption;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GiftVoucherRedeemService
{
    public const BOOKING_ONLINE_SOURCE = 'booking_online';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_APPLIED = 'applied';
    public const STATUS_RELEASED = 'released';
    public const BOOKING_ONLINE_HOLD_MINUTES = 30;
    public const BOOKING_ONLINE_RELEASE_GRACE_MINUTES = 5;

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

    public function reserveForAppointment(
        GiftVoucher $voucher,
        int $amountCents,
        int $appointmentId,
        ?int $actorUserId = null,
        ?string $note = null,
        string $source = self::BOOKING_ONLINE_SOURCE
    ): GiftVoucherRedemption {
        if ($amountCents <= 0) {
            throw ValidationException::withMessages(['amount_eur' => 'Montant invalide.']);
        }

        $actorUserId = $actorUserId ?: auth()->id();
        if (!$actorUserId) {
            throw ValidationException::withMessages(['amount_eur' => 'Utilisateur introuvable pour reserver le bon cadeau.']);
        }

        return DB::transaction(function () use ($voucher, $amountCents, $appointmentId, $actorUserId, $note, $source) {
            /** @var GiftVoucher $freshVoucher */
            $freshVoucher = GiftVoucher::query()
                ->whereKey($voucher->id)
                ->lockForUpdate()
                ->firstOrFail();

            $existing = GiftVoucherRedemption::query()
                ->where('gift_voucher_id', $freshVoucher->id)
                ->where('appointment_id', $appointmentId)
                ->where('source', $source)
                ->whereIn('status', [self::STATUS_RESERVED, self::STATUS_APPLIED])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            if (!$freshVoucher->is_active) {
                throw ValidationException::withMessages(['amount_eur' => 'Ce bon cadeau est desactive.']);
            }

            if ($freshVoucher->isExpired()) {
                throw ValidationException::withMessages(['amount_eur' => 'Ce bon cadeau est expire.']);
            }

            if ($freshVoucher->remaining_amount_cents <= 0) {
                throw ValidationException::withMessages(['amount_eur' => 'Ce bon cadeau est deja epuise.']);
            }

            if ($amountCents > $freshVoucher->remaining_amount_cents) {
                throw ValidationException::withMessages([
                    'amount_eur' => 'Le montant depasse le solde restant du bon cadeau.',
                ]);
            }

            $redemption = GiftVoucherRedemption::create([
                'gift_voucher_id' => $freshVoucher->id,
                'user_id' => $actorUserId,
                'amount_cents' => $amountCents,
                'note' => $note,
                'appointment_id' => $appointmentId,
                'invoice_id' => null,
                'source' => $source,
                'status' => self::STATUS_RESERVED,
            ]);

            $freshVoucher->remaining_amount_cents = max(0, $freshVoucher->remaining_amount_cents - $amountCents);
            $freshVoucher->save();

            return $redemption->fresh();
        });
    }

    public function finalizeReservedForAppointment(
        GiftVoucher $voucher,
        int $appointmentId,
        ?string $note = null,
        string $source = self::BOOKING_ONLINE_SOURCE
    ): int {
        return DB::transaction(function () use ($voucher, $appointmentId, $note, $source) {
            $alreadyApplied = GiftVoucherRedemption::query()
                ->where('gift_voucher_id', $voucher->id)
                ->where('appointment_id', $appointmentId)
                ->where('source', $source)
                ->where('status', self::STATUS_APPLIED)
                ->lockForUpdate()
                ->sum('amount_cents');

            if ($alreadyApplied > 0) {
                return (int) $alreadyApplied;
            }

            /** @var GiftVoucherRedemption|null $reserved */
            $reserved = GiftVoucherRedemption::query()
                ->where('gift_voucher_id', $voucher->id)
                ->where('appointment_id', $appointmentId)
                ->where('source', $source)
                ->where('status', self::STATUS_RESERVED)
                ->lockForUpdate()
                ->first();

            if (!$reserved) {
                return 0;
            }

            $reserved->status = self::STATUS_APPLIED;
            $reserved->released_at = null;
            if ($note) {
                $reserved->note = trim((string) $reserved->note . "\n" . $note);
            }
            $reserved->save();

            /** @var GiftVoucher $freshVoucher */
            $freshVoucher = GiftVoucher::query()
                ->whereKey($voucher->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($freshVoucher->remaining_amount_cents <= 0) {
                $freshVoucher->remaining_amount_cents = 0;
                $freshVoucher->is_active = false;
                $freshVoucher->save();
            }

            return (int) $reserved->amount_cents;
        });
    }

    public function releaseReservedForAppointment(
        Appointment $appointment,
        ?string $note = null,
        string $source = self::BOOKING_ONLINE_SOURCE
    ): int {
        return DB::transaction(function () use ($appointment, $note, $source) {
            $reserved = GiftVoucherRedemption::query()
                ->where('appointment_id', $appointment->id)
                ->where('source', $source)
                ->where('status', self::STATUS_RESERVED)
                ->lockForUpdate()
                ->get();

            if ($reserved->isEmpty()) {
                return 0;
            }

            $releasedTotal = 0;

            foreach ($reserved as $redemption) {
                /** @var GiftVoucher $voucher */
                $voucher = GiftVoucher::query()
                    ->whereKey($redemption->gift_voucher_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $voucher->remaining_amount_cents += (int) $redemption->amount_cents;
                $voucher->save();

                $redemption->status = self::STATUS_RELEASED;
                $redemption->released_at = now();
                if ($note) {
                    $redemption->note = trim((string) $redemption->note . "\n" . $note);
                }
                $redemption->save();

                $releasedTotal += (int) $redemption->amount_cents;
            }

            return $releasedTotal;
        });
    }

    public function releaseStaleOnlineReservations(?Carbon $threshold = null): int
    {
        $threshold = $threshold ?: now()->subMinutes(
            self::BOOKING_ONLINE_HOLD_MINUTES + self::BOOKING_ONLINE_RELEASE_GRACE_MINUTES
        );

        $appointmentIds = GiftVoucherRedemption::query()
            ->where('source', self::BOOKING_ONLINE_SOURCE)
            ->where('status', self::STATUS_RESERVED)
            ->where('created_at', '<=', $threshold)
            ->whereNotNull('appointment_id')
            ->pluck('appointment_id')
            ->filter()
            ->unique()
            ->all();

        $releasedCount = 0;

        foreach ($appointmentIds as $appointmentId) {
            $appointment = Appointment::query()->find($appointmentId);

            if (! $appointment) {
                $releasedCount += GiftVoucherRedemption::query()
                    ->where('appointment_id', $appointmentId)
                    ->where('source', self::BOOKING_ONLINE_SOURCE)
                    ->where('status', self::STATUS_RESERVED)
                    ->count();

                DB::transaction(function () use ($appointmentId) {
                    $reserved = GiftVoucherRedemption::query()
                        ->where('appointment_id', $appointmentId)
                        ->where('source', self::BOOKING_ONLINE_SOURCE)
                        ->where('status', self::STATUS_RESERVED)
                        ->lockForUpdate()
                        ->get();

                    foreach ($reserved as $redemption) {
                        $voucher = GiftVoucher::query()
                            ->whereKey($redemption->gift_voucher_id)
                            ->lockForUpdate()
                            ->first();

                        if ($voucher) {
                            $voucher->remaining_amount_cents += (int) $redemption->amount_cents;
                            $voucher->save();
                        }

                        $redemption->status = self::STATUS_RELEASED;
                        $redemption->released_at = now();
                        $redemption->note = trim((string) $redemption->note . "\nReservation expiree sans rendez-vous.");
                        $redemption->save();
                    }
                });

                continue;
            }

            if (($appointment->status ?? null) !== 'pending') {
                continue;
            }

            $released = $this->releaseReservedForAppointment(
                $appointment,
                'Reservation Stripe expiree sans paiement.'
            );

            if ($released > 0) {
                $releasedCount++;
            }
        }

        return $releasedCount;
    }
}
