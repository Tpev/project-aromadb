<?php

namespace App\Services;

use App\Jobs\DiscoverEarlierSlotOffersJob;
use App\Mail\AppointmentEarlierSlotClaimedClientMail;
use App\Mail\AppointmentEarlierSlotClaimedTherapistMail;
use App\Mail\AppointmentCancelledByClient;
use App\Mail\AppointmentCancellationConfirmedClientMail;
use App\Mail\AppointmentRescheduledClientMail;
use App\Mail\AppointmentRescheduledTherapistMail;
use App\Models\Appointment;
use App\Support\AppointmentLocationFingerprint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Stripe\StripeClient;

class AppointmentLifecycleService
{
    public function __construct(
        private readonly AppointmentAvailabilityService $availability,
        private readonly GiftVoucherRedeemService $giftVouchers,
    ) {
    }

    public function cancel(
        Appointment $appointment,
        string $actorType,
        ?int $actorId = null,
        ?string $reason = null,
        bool $enforceDeadline = true,
        bool $allowPast = false
    ): array {
        $wasPending = false;
        $wasPast = false;

        $result = DB::transaction(function () use ($appointment, $actorType, $actorId, $reason, $enforceDeadline, $allowPast, &$wasPending, &$wasPast) {
            $locked = Appointment::query()->with(['user', 'clientProfile', 'product'])
                ->lockForUpdate()
                ->findOrFail($appointment->id);

            if ($locked->isCancelled()) {
                return ['appointment' => $locked, 'changed' => false];
            }

            $this->assertManageable($locked, $enforceDeadline, 'annulation', $allowPast);
            $wasPending = $locked->isPendingPayment();
            $wasPast = $locked->appointment_date?->isPast() ?? false;
            $financialFollowUp = $locked->requiresFinancialFollowUp();

            $locked->forceFill([
                'status' => Appointment::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by_type' => $actorType,
                'cancelled_by_id' => $actorId,
                'cancellation_reason' => $reason ? trim($reason) : null,
                'financial_follow_up_required' => $financialFollowUp,
                'reminder_24h_queued_at' => null,
                'reminder_1h_queued_at' => null,
            ])->saveQuietly();

            $locked->activities()->create([
                'action' => 'cancelled',
                'actor_type' => $actorType,
                'actor_id' => $actorId,
                'metadata' => [
                    'reason' => $reason ? trim($reason) : null,
                    'status_before' => $appointment->status,
                    'historical_correction' => $wasPast,
                    'financial_follow_up' => $financialFollowUp,
                    'financial_policy' => config('appointments.cancellation'),
                ],
            ]);

            return ['appointment' => $locked, 'changed' => true];
        }, 3);

        /** @var Appointment $cancelled */
        $cancelled = $result['appointment'];

        if (!$result['changed']) {
            return $result;
        }

        if ($wasPending) {
            $this->expireStripeCheckout($cancelled);
            $this->releaseTemporaryGiftVoucher($cancelled);
        }

        $this->syncGoogleAfterCommit($cancelled);
        $this->queueCancellationNotifications(
            $cancelled,
            $actorType,
            !($actorType === 'practitioner' && $wasPast)
        );
        $this->queueEarlierSlotDiscovery($cancelled, $cancelled->appointment_date);

        return $result;
    }

    public function reschedule(
        Appointment $appointment,
        Carbon $newStart,
        string $actorType,
        ?int $actorId = null,
        bool $enforceDeadline = true,
        bool $skipAvailability = false
    ): array {
        $oldStart = $appointment->appointment_date?->copy();
        $slotLock = Cache::lock(
            'appointment-reschedule:'.$appointment->user_id.':'.$newStart->format('YmdHi'),
            30
        );

        if (!$slotLock->get()) {
            throw ValidationException::withMessages([
                'appointment_time' => 'Ce créneau est en cours de réservation. Veuillez réessayer dans quelques secondes.',
            ]);
        }

        try {
            $updated = DB::transaction(function () use ($appointment, $newStart, $actorType, $actorId, $enforceDeadline, $skipAvailability, &$oldStart) {
                $locked = Appointment::query()->with(['user', 'clientProfile', 'product', 'practiceLocation'])
                    ->lockForUpdate()
                    ->findOrFail($appointment->id);

                $this->assertManageable($locked, $enforceDeadline, 'modification');
                $oldStart = $locked->appointment_date->copy();

                if ($oldStart->equalTo($newStart)) {
                    return ['appointment' => $locked, 'changed' => false, 'old_start' => $oldStart];
                }

                if (!$skipAvailability) {
                    $this->availability->assertAvailable($locked, $newStart, true);
                }

                $locked->forceFill([
                    'appointment_date' => $newStart,
                    'rescheduled_at' => now(),
                    'rescheduled_by_type' => $actorType,
                    'rescheduled_by_id' => $actorId,
                    'reminder_24h_sent_at' => null,
                    'reminder_1h_sent_at' => null,
                    'reminder_24h_queued_at' => null,
                    'reminder_1h_queued_at' => null,
                ])->saveQuietly();

                $locked->activities()->create([
                    'action' => 'rescheduled',
                    'actor_type' => $actorType,
                    'actor_id' => $actorId,
                    'metadata' => [
                        'old_start' => $oldStart->toIso8601String(),
                        'new_start' => $newStart->toIso8601String(),
                        'availability_override' => $skipAvailability,
                    ],
                ]);

                return ['appointment' => $locked, 'changed' => true, 'old_start' => $oldStart];
            }, 3);
        } finally {
            $slotLock->release();
        }

        if ($updated['changed']) {
            $this->syncGoogleAfterCommit($updated['appointment']);
            $this->queueRescheduleNotifications($updated['appointment'], $updated['old_start'], $actorType);
            $this->queueEarlierSlotDiscovery($updated['appointment'], $updated['old_start']);
        }

        return $updated;
    }

    public function expirePendingPayment(Appointment $appointment): array
    {
        $result = DB::transaction(function () use ($appointment) {
            $locked = Appointment::query()->with(['user', 'clientProfile', 'product'])
                ->lockForUpdate()
                ->findOrFail($appointment->id);

            if (!$locked->isPendingPayment() || !$locked->stripe_session_id) {
                return ['appointment' => $locked, 'changed' => false];
            }

            $locked->forceFill([
                'status' => Appointment::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by_type' => 'system',
                'cancelled_by_id' => null,
                'cancellation_reason' => 'Paiement non finalisé dans le délai imparti.',
                'financial_follow_up_required' => false,
                'reminder_24h_queued_at' => null,
                'reminder_1h_queued_at' => null,
            ])->saveQuietly();

            $locked->activities()->create([
                'action' => 'payment_expired',
                'actor_type' => 'system',
                'metadata' => [
                    'status_before' => $appointment->status,
                    'stripe_session_id' => $locked->stripe_session_id,
                ],
            ]);

            return ['appointment' => $locked, 'changed' => true];
        }, 3);

        if (!$result['changed']) {
            return $result;
        }

        $expired = $result['appointment'];
        $this->expireStripeCheckout($expired);
        $this->releaseTemporaryGiftVoucher($expired);
        $this->syncGoogleAfterCommit($expired);
        $this->queueEarlierSlotDiscovery($expired, $expired->appointment_date);

        return $result;
    }

    public function assertManageable(
        Appointment $appointment,
        bool $enforceDeadline,
        string $action,
        bool $allowPast = false
    ): void
    {
        if (
            $appointment->external
            || $appointment->isCompleted()
            || !$appointment->appointment_date
            || (!$allowPast && $appointment->appointment_date->isPast())
        ) {
            throw ValidationException::withMessages([
                'appointment' => "Ce rendez-vous est passé ou terminé et ne peut plus faire l’objet d’une {$action} en ligne.",
            ]);
        }

        if ($appointment->isCancelled()) {
            throw ValidationException::withMessages([
                'appointment' => 'Ce rendez-vous est déjà annulé.',
            ]);
        }

        if ($enforceDeadline && now()->gt($appointment->managementDeadlineAt())) {
            $hours = max(0, (int) ($appointment->user?->cancellation_notice_hours ?? 0));
            throw ValidationException::withMessages([
                'appointment' => "La {$action} en ligne n’est plus disponible à moins de {$hours} h du rendez-vous. Contactez directement votre praticien.",
            ]);
        }
    }

    private function queueCancellationNotifications(
        Appointment $appointment,
        string $actorType,
        bool $notifyClient = true
    ): void
    {
        $clientEmail = $appointment->clientProfile?->email;
        if ($notifyClient && $clientEmail) {
            Mail::to($clientEmail)->queue(
                (new AppointmentCancellationConfirmedClientMail($appointment))->afterCommit()
            );
        }

        if (in_array($actorType, ['client', 'token'], true)) {
            $practitionerEmail = $appointment->user?->company_email ?: $appointment->user?->email;
            if ($practitionerEmail) {
                Mail::to($practitionerEmail)->queue(
                    (new AppointmentCancelledByClient($appointment))->afterCommit()
                );
            }
        }
    }

    private function queueRescheduleNotifications(Appointment $appointment, Carbon $oldStart, string $actorType): void
    {
        if ($actorType === 'earlier_slot') {
            if ($appointment->clientProfile?->email) {
                Mail::to($appointment->clientProfile->email)->queue(
                    (new AppointmentEarlierSlotClaimedClientMail($appointment, $oldStart))->afterCommit()
                );
            }

            $practitionerEmail = $appointment->user?->company_email ?: $appointment->user?->email;
            if ($practitionerEmail) {
                Mail::to($practitionerEmail)->queue(
                    (new AppointmentEarlierSlotClaimedTherapistMail($appointment, $oldStart))->afterCommit()
                );
            }

            return;
        }

        if ($appointment->clientProfile?->email) {
            Mail::to($appointment->clientProfile->email)->queue(
                (new AppointmentRescheduledClientMail($appointment, $oldStart))->afterCommit()
            );
        }

        $practitionerEmail = $appointment->user?->company_email ?: $appointment->user?->email;
        if ($practitionerEmail && $actorType !== 'practitioner') {
            Mail::to($practitionerEmail)->queue(
                (new AppointmentRescheduledTherapistMail($appointment, $oldStart))->afterCommit()
            );
        }
    }

    private function queueEarlierSlotDiscovery(Appointment $appointment, ?Carbon $releasedStart): void
    {
        if (
            ! config('appointments.earlier_slots.enabled', false)
            || ! $releasedStart
            || ! $appointment->product_id
            || (int) $appointment->duration <= 0
        ) {
            return;
        }

        $mode = in_array($appointment->type, ['cabinet', 'visio', 'domicile', 'entreprise'], true)
            ? $appointment->type
            : $appointment->getResolvedMode();
        $locationFingerprint = in_array($mode, ['domicile', 'entreprise'], true)
            ? AppointmentLocationFingerprint::for($appointment)
            : null;

        DB::afterCommit(function () use ($appointment, $releasedStart, $mode, $locationFingerprint) {
            DiscoverEarlierSlotOffersJob::dispatch(
                (int) $appointment->id,
                (int) $appointment->user_id,
                (int) $appointment->product_id,
                $releasedStart->copy()->startOfMinute()->toIso8601String(),
                (int) $appointment->duration,
                $mode,
                $mode === 'cabinet' && $appointment->practice_location_id
                    ? (int) $appointment->practice_location_id
                    : null,
                $locationFingerprint,
            );
        });
    }

    private function syncGoogleAfterCommit(Appointment $appointment): void
    {
        DB::afterCommit(function () use ($appointment) {
            try {
                Appointment::query()->find($appointment->id)?->syncToGoogle();
            } catch (\Throwable $exception) {
                Log::error('Google Calendar lifecycle sync failed without blocking the appointment.', [
                    'appointment_id' => $appointment->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        });
    }

    private function expireStripeCheckout(Appointment $appointment): void
    {
        if (!$appointment->stripe_session_id || !$appointment->user?->stripe_account_id) {
            return;
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $stripe->checkout->sessions->expire(
                $appointment->stripe_session_id,
                [],
                ['stripe_account' => $appointment->user->stripe_account_id]
            );
        } catch (\Throwable $exception) {
            Log::warning('Unable to expire appointment Stripe Checkout session.', [
                'appointment_id' => $appointment->id,
                'stripe_session_id' => $appointment->stripe_session_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function releaseTemporaryGiftVoucher(Appointment $appointment): void
    {
        try {
            $this->giftVouchers->releaseReservedForAppointment(
                $appointment,
                'Rendez-vous annulé avant finalisation du paiement.'
            );
        } catch (\Throwable $exception) {
            Log::warning('Unable to release temporary gift voucher reservation.', [
                'appointment_id' => $appointment->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
