<?php

namespace App\Services;

use App\Mail\AppointmentEarlierSlotAvailableMail;
use App\Models\Appointment;
use App\Models\AppointmentEarlierSlotOffer;
use App\Models\AppointmentEarlierSlotOpportunity;
use App\Support\AppointmentLocationFingerprint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppointmentEarlierSlotService
{
    public const STATE_AVAILABLE = 'available';

    public const STATE_CLAIMED = 'claimed';

    public const STATE_TAKEN = 'taken';

    public const STATE_UNAVAILABLE = 'unavailable';

    public const STATE_BUSY = 'busy';

    public function __construct(
        private readonly AppointmentAvailabilityService $availability,
        private readonly AppointmentLifecycleService $lifecycle,
    ) {}

    public function discover(
        int $releasedAppointmentId,
        int $userId,
        int $productId,
        string $slotStart,
        int $duration,
        string $mode,
        ?int $practiceLocationId,
        ?string $locationFingerprint = null,
    ): ?AppointmentEarlierSlotOpportunity {
        if (! config('appointments.earlier_slots.enabled', false)) {
            return null;
        }

        $start = Carbon::parse($slotStart)->startOfMinute();
        $mode = $this->normalizeMode($mode);
        $releasedAppointment = Appointment::query()
            ->with('clientProfile')
            ->whereKey($releasedAppointmentId)
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
        $releasedAppointmentId = $releasedAppointment?->id;
        $locationFingerprint = in_array($mode, ['domicile', 'entreprise'], true)
            ? ($this->normalizeLocationFingerprint($locationFingerprint)
                ?: ($releasedAppointment ? AppointmentLocationFingerprint::for($releasedAppointment) : null))
            : null;

        if (
            ! $start->isFuture()
            || $duration <= 0
            || $productId <= 0
            || ! $mode
            || ($mode === 'cabinet' && ! $practiceLocationId)
            || (in_array($mode, ['domicile', 'entreprise'], true) && ! $locationFingerprint)
        ) {
            return null;
        }

        $lockKey = 'earlier-slot-discovery:'.hash('sha256', implode('|', [
            $userId,
            $productId,
            $start->toIso8601String(),
            $duration,
            $mode,
            $practiceLocationId ?? 'none',
            $locationFingerprint ?? 'none',
        ]));
        $lock = Cache::lock($lockKey, 60);

        if (! $lock->get()) {
            return null;
        }

        try {
            $existing = AppointmentEarlierSlotOpportunity::query()
                ->where('user_id', $userId)
                ->where('product_id', $productId)
                ->where('slot_start', $start)
                ->where('duration', $duration)
                ->where('mode', $mode)
                ->where('location_fingerprint', $locationFingerprint)
                ->where('status', AppointmentEarlierSlotOpportunity::STATUS_OPEN)
                ->where('expires_at', '>', now())
                ->when(
                    $practiceLocationId,
                    fn ($query) => $query->where('practice_location_id', $practiceLocationId),
                    fn ($query) => $query->whereNull('practice_location_id')
                )
                ->first();

            if ($existing) {
                return $existing;
            }

            $candidates = Appointment::query()
                ->with(['clientProfile', 'user', 'product', 'practiceLocation'])
                ->where('user_id', $userId)
                ->where('product_id', $productId)
                ->where('duration', $duration)
                ->where('wants_earlier_slot', true)
                ->where('appointment_date', '>', $start)
                ->when($releasedAppointmentId, fn ($query) => $query->whereKeyNot($releasedAppointmentId))
                ->where(function ($query) {
                    $query->whereNull('external')->orWhere('external', false);
                })
                ->notCancelled()
                ->orderBy('appointment_date')
                ->get()
                ->reject(fn (Appointment $appointment) => $appointment->isPendingPayment() || $appointment->isCompleted())
                ->filter(fn (Appointment $appointment) => $appointment->clientProfile?->email)
                ->filter(fn (Appointment $appointment) => $appointment->canBeManagedOnline())
                ->filter(fn (Appointment $appointment) => $this->matchesSnapshot(
                    $appointment,
                    $userId,
                    $productId,
                    $duration,
                    $mode,
                    $practiceLocationId,
                    $locationFingerprint,
                ))
                ->filter(fn (Appointment $appointment) => $this->availability->isAvailable($appointment, $start))
                ->unique(fn (Appointment $appointment) => Str::lower(trim((string) $appointment->clientProfile?->email)))
                ->values();

            if ($candidates->isEmpty()) {
                return null;
            }

            [$opportunity, $offers] = DB::transaction(function () use (
                $candidates,
                $releasedAppointmentId,
                $userId,
                $productId,
                $practiceLocationId,
                $start,
                $duration,
                $mode,
                $locationFingerprint,
            ) {
                $opportunity = AppointmentEarlierSlotOpportunity::create([
                    'user_id' => $userId,
                    'released_appointment_id' => $releasedAppointmentId,
                    'product_id' => $productId,
                    'practice_location_id' => $mode === 'cabinet' ? $practiceLocationId : null,
                    'location_fingerprint' => $locationFingerprint,
                    'slot_start' => $start,
                    'duration' => $duration,
                    'mode' => $mode,
                    'status' => AppointmentEarlierSlotOpportunity::STATUS_OPEN,
                    'expires_at' => $start,
                ]);

                $offers = $candidates->map(function (Appointment $appointment) use ($opportunity) {
                    $token = Str::random(64);

                    return AppointmentEarlierSlotOffer::create([
                        'opportunity_id' => $opportunity->id,
                        'appointment_id' => $appointment->id,
                        'token' => $token,
                        'token_hash' => hash('sha256', $token),
                        'status' => AppointmentEarlierSlotOffer::STATUS_PENDING,
                        'sent_at' => now(),
                    ]);
                });

                return [$opportunity, $offers];
            }, 3);

            foreach ($offers as $offer) {
                $offer->loadMissing(['appointment.clientProfile', 'appointment.user', 'appointment.product', 'opportunity']);
                $email = $offer->appointment?->clientProfile?->email;
                if (! $email) {
                    continue;
                }

                try {
                    Mail::to($email)->queue((new AppointmentEarlierSlotAvailableMail($offer))->afterCommit());
                } catch (\Throwable $exception) {
                    Log::error('Unable to queue an earlier appointment slot offer.', [
                        'offer_id' => $offer->id,
                        'appointment_id' => $offer->appointment_id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }

            return $opportunity;
        } finally {
            $lock->release();
        }
    }

    public function offerForToken(string $token): ?AppointmentEarlierSlotOffer
    {
        if (strlen($token) !== 64) {
            return null;
        }

        return AppointmentEarlierSlotOffer::query()
            ->with([
                'opportunity.product',
                'opportunity.practiceLocation',
                'appointment.clientProfile',
                'appointment.user',
                'appointment.product',
                'appointment.practiceLocation',
            ])
            ->where('token_hash', hash('sha256', $token))
            ->first();
    }

    public function state(AppointmentEarlierSlotOffer $offer): string
    {
        $offer->loadMissing(['opportunity', 'appointment.user', 'appointment.product']);

        if (
            $offer->status === AppointmentEarlierSlotOffer::STATUS_CLAIMED
            && (int) $offer->opportunity?->claimed_appointment_id === (int) $offer->appointment_id
        ) {
            return self::STATE_CLAIMED;
        }

        if (
            $offer->opportunity?->status === AppointmentEarlierSlotOpportunity::STATUS_CLAIMED
            && (int) $offer->opportunity->claimed_appointment_id !== (int) $offer->appointment_id
        ) {
            return self::STATE_TAKEN;
        }

        if (! $offer->isClaimable() || ! $this->matchesOpportunity($offer->appointment, $offer->opportunity)) {
            return self::STATE_UNAVAILABLE;
        }

        return $this->availability->isAvailable($offer->appointment, $offer->opportunity->slot_start)
            ? self::STATE_AVAILABLE
            : self::STATE_UNAVAILABLE;
    }

    public function claim(string $token): array
    {
        $reference = $this->offerForToken($token);
        if (! $reference) {
            return ['state' => self::STATE_UNAVAILABLE, 'offer' => null];
        }

        $lock = Cache::lock('earlier-slot-claim:'.$reference->opportunity_id, 30);
        if (! $lock->get()) {
            return ['state' => self::STATE_BUSY, 'offer' => $reference];
        }

        try {
            return DB::transaction(function () use ($reference) {
                $offer = AppointmentEarlierSlotOffer::query()->lockForUpdate()->find($reference->id);
                $opportunity = AppointmentEarlierSlotOpportunity::query()->lockForUpdate()->find($reference->opportunity_id);

                if (! $offer || ! $opportunity) {
                    return ['state' => self::STATE_UNAVAILABLE, 'offer' => null];
                }

                if (
                    $offer->status === AppointmentEarlierSlotOffer::STATUS_CLAIMED
                    && (int) $opportunity->claimed_appointment_id === (int) $offer->appointment_id
                ) {
                    $offer->load(['opportunity', 'appointment.clientProfile', 'appointment.user', 'appointment.product']);

                    return ['state' => self::STATE_CLAIMED, 'offer' => $offer, 'changed' => false];
                }

                if (
                    $opportunity->status === AppointmentEarlierSlotOpportunity::STATUS_CLAIMED
                    && (int) $opportunity->claimed_appointment_id !== (int) $offer->appointment_id
                ) {
                    $this->invalidateOffer($offer);

                    return ['state' => self::STATE_TAKEN, 'offer' => $offer];
                }

                $appointment = Appointment::query()
                    ->with(['clientProfile', 'user', 'product', 'practiceLocation'])
                    ->lockForUpdate()
                    ->find($offer->appointment_id);

                if (
                    ! $appointment
                    || ! $offer->isClaimable()
                    || ! $this->matchesOpportunity($appointment, $opportunity)
                ) {
                    $this->invalidateOffer($offer);

                    return ['state' => self::STATE_UNAVAILABLE, 'offer' => $offer];
                }

                if (! $this->availability->isAvailable($appointment, $opportunity->slot_start, true)) {
                    $this->closeOpportunity($opportunity);

                    return ['state' => self::STATE_UNAVAILABLE, 'offer' => $offer];
                }

                $result = $this->lifecycle->reschedule(
                    $appointment,
                    $opportunity->slot_start->copy(),
                    'earlier_slot'
                );

                /** @var Appointment $moved */
                $moved = $result['appointment'];
                $moved->forceFill([
                    'wants_earlier_slot' => false,
                    'earlier_slot_opted_in_at' => null,
                ])->saveQuietly();

                $opportunity->forceFill([
                    'status' => AppointmentEarlierSlotOpportunity::STATUS_CLAIMED,
                    'claimed_appointment_id' => $moved->id,
                    'claimed_at' => now(),
                ])->save();

                AppointmentEarlierSlotOffer::query()
                    ->where('opportunity_id', $opportunity->id)
                    ->where('id', '<>', $offer->id)
                    ->where('status', AppointmentEarlierSlotOffer::STATUS_PENDING)
                    ->update([
                        'status' => AppointmentEarlierSlotOffer::STATUS_INVALIDATED,
                        'invalidated_at' => now(),
                        'updated_at' => now(),
                    ]);

                $offer->forceFill([
                    'status' => AppointmentEarlierSlotOffer::STATUS_CLAIMED,
                    'claimed_at' => now(),
                ])->save();

                $moved->activities()->create([
                    'action' => 'earlier_slot_claimed',
                    'actor_type' => 'client',
                    'metadata' => [
                        'opportunity_id' => $opportunity->id,
                        'offer_id' => $offer->id,
                        'old_start' => $result['old_start']->toIso8601String(),
                        'new_start' => $opportunity->slot_start->toIso8601String(),
                    ],
                ]);

                $offer->setRelation('appointment', $moved);
                $offer->setRelation('opportunity', $opportunity);

                return [
                    'state' => self::STATE_CLAIMED,
                    'offer' => $offer,
                    'appointment' => $moved,
                    'old_start' => $result['old_start'],
                    'changed' => $result['changed'],
                ];
            }, 3);
        } catch (ValidationException $exception) {
            Log::info('Earlier appointment slot claim became unavailable.', [
                'offer_id' => $reference->id,
                'errors' => $exception->errors(),
            ]);

            return ['state' => self::STATE_UNAVAILABLE, 'offer' => $reference];
        } finally {
            $lock->release();
        }
    }

    public function updatePreference(Appointment $appointment, bool $enabled): bool
    {
        $appointment->loadMissing(['clientProfile', 'user']);

        if (
            ! config('appointments.earlier_slots.enabled', false)
            || ! $appointment->clientProfile?->email
            || ! $appointment->canBeManagedOnline()
        ) {
            return false;
        }

        $appointment->forceFill([
            'wants_earlier_slot' => $enabled,
            'earlier_slot_opted_in_at' => $enabled ? now() : null,
        ])->saveQuietly();

        if (! $enabled) {
            AppointmentEarlierSlotOffer::query()
                ->where('appointment_id', $appointment->id)
                ->where('status', AppointmentEarlierSlotOffer::STATUS_PENDING)
                ->update([
                    'status' => AppointmentEarlierSlotOffer::STATUS_INVALIDATED,
                    'invalidated_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        return true;
    }

    public function expireAndPurge(int $limit = 500): array
    {
        if (
            ! Schema::hasTable('appointment_earlier_slot_opportunities')
            || ! Schema::hasTable('appointment_earlier_slot_offers')
        ) {
            return ['expired' => 0, 'purged' => 0];
        }

        $expired = 0;

        AppointmentEarlierSlotOpportunity::query()
            ->where('status', AppointmentEarlierSlotOpportunity::STATUS_OPEN)
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->limit(max(1, $limit))
            ->get()
            ->each(function (AppointmentEarlierSlotOpportunity $opportunity) use (&$expired) {
                DB::transaction(function () use ($opportunity, &$expired) {
                    $changed = AppointmentEarlierSlotOpportunity::query()
                        ->whereKey($opportunity->id)
                        ->where('status', AppointmentEarlierSlotOpportunity::STATUS_OPEN)
                        ->update([
                            'status' => AppointmentEarlierSlotOpportunity::STATUS_EXPIRED,
                            'updated_at' => now(),
                        ]);

                    if ($changed !== 1) {
                        return;
                    }

                    AppointmentEarlierSlotOffer::query()
                        ->where('opportunity_id', $opportunity->id)
                        ->where('status', AppointmentEarlierSlotOffer::STATUS_PENDING)
                        ->update([
                            'status' => AppointmentEarlierSlotOffer::STATUS_INVALIDATED,
                            'invalidated_at' => now(),
                            'updated_at' => now(),
                        ]);
                    $expired++;
                });
            });

        $purgeIds = AppointmentEarlierSlotOpportunity::query()
            ->where('created_at', '<', now()->subDays(config('appointments.earlier_slots.retention_days', 90)))
            ->where('status', '<>', AppointmentEarlierSlotOpportunity::STATUS_OPEN)
            ->orderBy('id')
            ->limit(max(1, $limit))
            ->pluck('id');

        $purged = $purgeIds->isEmpty()
            ? 0
            : AppointmentEarlierSlotOpportunity::query()->whereKey($purgeIds)->delete();

        return ['expired' => $expired, 'purged' => $purged];
    }

    private function matchesOpportunity(
        Appointment $appointment,
        AppointmentEarlierSlotOpportunity $opportunity
    ): bool {
        return $this->matchesSnapshot(
            $appointment,
            (int) $opportunity->user_id,
            (int) $opportunity->product_id,
            (int) $opportunity->duration,
            (string) $opportunity->mode,
            $opportunity->practice_location_id ? (int) $opportunity->practice_location_id : null,
            $opportunity->location_fingerprint,
        ) && $opportunity->slot_start->lt($appointment->appointment_date);
    }

    private function matchesSnapshot(
        Appointment $appointment,
        int $userId,
        int $productId,
        int $duration,
        string $mode,
        ?int $practiceLocationId,
        ?string $locationFingerprint,
    ): bool {
        if (
            (int) $appointment->user_id !== $userId
            || (int) $appointment->product_id !== $productId
            || (int) $appointment->duration !== $duration
            || $this->appointmentMode($appointment) !== $mode
        ) {
            return false;
        }

        if ($mode === 'cabinet') {
            return (int) ($appointment->practice_location_id ?? 0) === (int) ($practiceLocationId ?? 0);
        }

        if (in_array($mode, ['domicile', 'entreprise'], true)) {
            $appointmentFingerprint = AppointmentLocationFingerprint::for($appointment);

            return $appointmentFingerprint
                && $locationFingerprint
                && hash_equals($locationFingerprint, $appointmentFingerprint);
        }

        return true;
    }

    private function appointmentMode(Appointment $appointment): string
    {
        return $this->normalizeMode($appointment->type) ?: $appointment->getResolvedMode();
    }

    private function normalizeMode(?string $mode): ?string
    {
        return in_array($mode, ['cabinet', 'visio', 'domicile', 'entreprise'], true)
            ? $mode
            : null;
    }

    private function normalizeLocationFingerprint(?string $fingerprint): ?string
    {
        $fingerprint = strtolower(trim((string) $fingerprint));

        return preg_match('/^[a-f0-9]{64}$/', $fingerprint) === 1 ? $fingerprint : null;
    }

    private function invalidateOffer(AppointmentEarlierSlotOffer $offer): void
    {
        if ($offer->status !== AppointmentEarlierSlotOffer::STATUS_PENDING) {
            return;
        }

        $offer->forceFill([
            'status' => AppointmentEarlierSlotOffer::STATUS_INVALIDATED,
            'invalidated_at' => now(),
        ])->save();
    }

    private function closeOpportunity(AppointmentEarlierSlotOpportunity $opportunity): void
    {
        $opportunity->forceFill([
            'status' => AppointmentEarlierSlotOpportunity::STATUS_CLOSED,
        ])->save();

        AppointmentEarlierSlotOffer::query()
            ->where('opportunity_id', $opportunity->id)
            ->where('status', AppointmentEarlierSlotOffer::STATUS_PENDING)
            ->update([
                'status' => AppointmentEarlierSlotOffer::STATUS_INVALIDATED,
                'invalidated_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
