<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\SpecialAvailability;
use App\Models\Unavailability;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class AppointmentAvailabilityService
{
    public function __construct(
        private readonly SharedCabinetSchedulingService $sharedCabinetScheduling,
    ) {
    }

    public function assertAvailable(Appointment $appointment, Carbon $start, bool $lockConflicts = false): void
    {
        if (!$this->isAvailable($appointment, $start, $lockConflicts)) {
            throw ValidationException::withMessages([
                'appointment_time' => 'Ce créneau n’est plus disponible. Choisissez un autre horaire.',
            ]);
        }
    }

    public function isAvailable(Appointment $appointment, Carbon $start, bool $lockConflicts = false): bool
    {
        $appointment->loadMissing(['user', 'product', 'practiceLocation']);

        $therapist = $appointment->user;
        $product = $appointment->product;
        $duration = (int) ($appointment->duration ?: $product?->duration ?: 0);

        if (!$therapist || !$product || $duration <= 0 || !$start->isFuture()) {
            return false;
        }

        $minimumNotice = max(0, (int) ($therapist->minimum_notice_hours ?? 0));
        if ($start->lt(now()->addHours($minimumNotice))) {
            return false;
        }

        $mode = $appointment->getResolvedMode();
        $locationId = $mode === 'cabinet' ? $appointment->practice_location_id : null;
        $end = $start->copy()->addMinutes($duration);

        $availabilities = $this->availabilitiesFor($appointment, $start);
        $insideAvailability = $availabilities->contains(function ($availability) use ($start, $end) {
            $availableStart = Carbon::parse($start->toDateString().' '.$availability->start_time);
            $availableEnd = Carbon::parse($start->toDateString().' '.$availability->end_time);

            return $start->gte($availableStart) && $end->lte($availableEnd);
        });

        if (!$insideAvailability || $this->dailyLimitReached($appointment, $start)) {
            return false;
        }

        $buffer = max(0, (int) ($therapist->buffer_time_between_appointments ?? 0));
        $bufferedStart = $start->copy()->subMinutes($buffer);
        $bufferedEnd = $end->copy()->addMinutes($buffer);

        $conflicts = Appointment::query()
            ->where('user_id', $appointment->user_id)
            ->whereKeyNot($appointment->id)
            ->notCancelled()
            ->where('appointment_date', '<', $bufferedEnd);

        $this->whereAppointmentEndsAfter($conflicts, $bufferedStart);
        $this->ignoreExternalAllDayBlocks($conflicts);

        if ($lockConflicts) {
            $conflicts->lockForUpdate();
        }

        if ($conflicts->exists()) {
            return false;
        }

        if ($mode === 'cabinet' && $locationId && $this->sharedCabinetScheduling->hasSharedCabinetConflict(
            $start,
            $duration,
            (int) $locationId,
            (int) $appointment->id
        )) {
            return false;
        }

        return !Unavailability::query()
            ->where('user_id', $appointment->user_id)
            ->where('start_date', '<', $end)
            ->where('end_date', '>', $start)
            ->exists();
    }

    public function slotsForDate(Appointment $appointment, Carbon $date): array
    {
        $appointment->loadMissing(['user', 'product']);
        $duration = (int) ($appointment->duration ?: $appointment->product?->duration ?: 0);

        if ($duration <= 0) {
            return [];
        }

        $slots = [];
        foreach ($this->availabilitiesFor($appointment, $date) as $availability) {
            $cursor = Carbon::parse($date->toDateString().' '.$availability->start_time);
            $end = Carbon::parse($date->toDateString().' '.$availability->end_time);

            while ($cursor->copy()->addMinutes($duration)->lte($end)) {
                if ($this->isAvailable($appointment, $cursor)) {
                    $slots[] = [
                        'start' => $cursor->format('H:i'),
                        'end' => $cursor->copy()->addMinutes($duration)->format('H:i'),
                    ];
                }

                $cursor->addMinutes(15);
            }
        }

        return collect($slots)->unique('start')->sortBy('start')->values()->all();
    }

    private function availabilitiesFor(Appointment $appointment, Carbon $date)
    {
        $productId = (int) $appointment->product_id;
        $mode = $appointment->getResolvedMode();
        $locationId = $mode === 'cabinet' ? $appointment->practice_location_id : null;

        $weekly = Availability::query()
            ->where('user_id', $appointment->user_id)
            ->where('day_of_week', $date->dayOfWeekIso - 1)
            ->where(function ($query) use ($productId) {
                $query->where('applies_to_all', true)
                    ->orWhereHas('products', fn ($products) => $products->where('products.id', $productId));
            });

        $special = SpecialAvailability::query()
            ->where('user_id', $appointment->user_id)
            ->whereDate('date', $date->toDateString())
            ->where(function ($query) use ($productId) {
                $query->where('applies_to_all', true)
                    ->orWhereHas('products', fn ($products) => $products->where('products.id', $productId));
            });

        if ($mode === 'cabinet' && $locationId) {
            $weekly->where('practice_location_id', $locationId);
            $special->where('practice_location_id', $locationId);
        }

        return $weekly->get()->concat($special->get());
    }

    private function dailyLimitReached(Appointment $appointment, Carbon $date): bool
    {
        $base = Appointment::query()
            ->where('user_id', $appointment->user_id)
            ->whereDate('appointment_date', $date->toDateString())
            ->whereKeyNot($appointment->id)
            ->where(function ($query) {
                $query->whereNull('external')->orWhere('external', false);
            })
            ->notCancelled();

        $productLimit = max(0, (int) ($appointment->product?->max_per_day ?? 0));
        if ($productLimit > 0 && (clone $base)->where('product_id', $appointment->product_id)->count() >= $productLimit) {
            return true;
        }

        $globalLimit = max(0, (int) ($appointment->user?->global_daily_booking_limit ?? 0));

        return $globalLimit > 0 && $base->count() >= $globalLimit;
    }

    private function whereAppointmentEndsAfter(Builder $query, Carbon $start): void
    {
        if ($query->getConnection()->getDriverName() === 'sqlite') {
            $query->whereRaw("datetime(appointment_date, '+' || COALESCE(duration, 60) || ' minutes') > ?", [$start]);
            return;
        }

        $query->whereRaw('DATE_ADD(appointment_date, INTERVAL COALESCE(duration, 60) MINUTE) > ?', [$start]);
    }

    private function ignoreExternalAllDayBlocks(Builder $query): void
    {
        $driver = $query->getConnection()->getDriverName();
        $condition = $driver === 'sqlite'
            ? "NOT (external = 1 AND time(appointment_date) = '00:00:00' AND COALESCE(duration, 0) >= 2880 AND (COALESCE(duration, 0) % 1440) = 0)"
            : 'NOT (external = 1 AND TIME(appointment_date) = "00:00:00" AND COALESCE(duration, 0) >= 2880 AND MOD(COALESCE(duration, 0), 1440) = 0)';

        $query->whereRaw($condition);
    }
}
