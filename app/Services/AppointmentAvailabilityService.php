<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\SpecialAvailability;
use App\Models\Unavailability;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AppointmentAvailabilityService
{
    public function __construct(
        private readonly SharedCabinetSchedulingService $sharedCabinetScheduling,
        private readonly BookingSchedulingPolicy $schedulingPolicy,
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

        $mode = $this->schedulingPolicy->resolvedMode($appointment, $therapist);
        if (
            !$appointment->exists
            && $this->schedulingPolicy->appliesTo($appointment, $therapist)
            && !$this->schedulingPolicy->productSupportsMode($product, $mode)
        ) {
            return false;
        }
        $locationId = $mode === 'cabinet' ? $appointment->practice_location_id : null;
        $end = $start->copy()->addMinutes($duration);

        $availabilities = $this->availabilitiesFor($appointment, $start);
        $insideAvailability = $availabilities->contains(function ($availability) use ($appointment, $therapist, $start, $end, $duration) {
            $availableStart = Carbon::parse($start->toDateString().' '.$availability->start_time);
            $availableEnd = Carbon::parse($start->toDateString().' '.$availability->end_time);

            return $start->gte($availableStart)
                && $end->lte($availableEnd)
                && $this->matchesScheduleGrid($appointment, $therapist, $availability, $start, $duration);
        });

        if (!$insideAvailability || $this->dailyLimitReached($appointment, $start)) {
            return false;
        }

        $candidateRules = $this->schedulingPolicy->valuesForCandidate($appointment, $therapist, $product);
        $searchStart = $start->copy()->subDays(2);
        $searchEnd = $end->copy()->addDays(2);

        $conflicts = Appointment::query()
            ->where('user_id', $appointment->user_id)
            ->whereKeyNot($appointment->id)
            ->notCancelled()
            ->where('appointment_date', '<', $searchEnd);

        $this->whereAppointmentEndsAfter($conflicts, $searchStart);
        $this->ignoreExternalAllDayBlocks($conflicts);

        if ($lockConflicts) {
            $conflicts->lockForUpdate();
        }

        $blockingAppointment = $conflicts->with('user')->get()->contains(
            fn (Appointment $existing): bool => $this->appointmentsConflict(
                $start,
                $duration,
                $candidateRules,
                $existing
            )
        );

        if ($blockingAppointment) {
            return false;
        }

        if ($mode === 'cabinet' && $locationId && $this->sharedCabinetScheduling->hasSharedCabinetConflictForAppointment(
            $appointment,
            $start,
            $candidateRules
        )) {
            return false;
        }

        return !Unavailability::query()
            ->where('user_id', $appointment->user_id)
            ->where('start_date', '<', $end->copy()->addMinutes($candidateRules['buffer_after']))
            ->where('end_date', '>', $start->copy()->subMinutes($candidateRules['preparation']))
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
            foreach ($this->candidateStarts($appointment, $availability, $date, $duration) as $cursor) {
                if ($this->isAvailable($appointment, $cursor)) {
                    $slots[] = [
                        'start' => $cursor->format('H:i'),
                        'end' => $cursor->copy()->addMinutes($duration)->format('H:i'),
                    ];
                }

            }
        }

        return collect($slots)->unique('start')->sortBy('start')->values()->all();
    }

    /** @return array{dates:array<int,string>,next:?array{date:string,time:string}} */
    public function availableDates(Appointment $appointment, Carbon $firstDate, int $days = 90): array
    {
        $days = max(1, min(90, $days));
        $appointment->loadMissing(['user', 'product', 'practiceLocation']);

        $therapist = $appointment->user;
        $product = $appointment->product;
        $duration = (int) ($appointment->duration ?: $product?->duration ?: 0);

        if (! $therapist || ! $product || $duration <= 0) {
            return ['dates' => [], 'next' => null];
        }

        $mode = $this->schedulingPolicy->resolvedMode($appointment, $therapist);
        if (
            ! $appointment->exists
            && $this->schedulingPolicy->appliesTo($appointment, $therapist)
            && ! $this->schedulingPolicy->productSupportsMode($product, $mode)
        ) {
            return ['dates' => [], 'next' => null];
        }

        $firstDate = $firstDate->copy()->startOfDay();
        $endExclusive = $firstDate->copy()->addDays($days);
        $context = $this->availabilityRangeContext($appointment, $firstDate, $endExclusive);
        $dates = [];
        $next = null;

        for ($offset = 0; $offset < $days; $offset++) {
            $date = $firstDate->copy()->addDays($offset);
            $slots = $this->slotsForDateUsingContext($appointment, $date, $duration, $context);

            if ($slots === []) {
                continue;
            }

            $dateString = $date->toDateString();
            $dates[] = $dateString;
            $next ??= ['date' => $dateString, 'time' => $slots[0]['start']];
        }

        return ['dates' => $dates, 'next' => $next];
    }

    /**
     * @param array{
     *   weekly:Collection,
     *   special:Collection,
     *   appointments:Collection,
     *   sharedAppointments:Collection,
     *   unavailabilities:Collection,
     *   candidateRules:array{preparation:int,buffer_after:int}
     * } $context
     */
    private function slotsForDateUsingContext(
        Appointment $appointment,
        Carbon $date,
        int $duration,
        array $context,
    ): array {
        $periods = $context['weekly']
            ->where('day_of_week', $date->dayOfWeekIso - 1)
            ->concat($context['special']->filter(
                fn (SpecialAvailability $availability): bool => $availability->date->isSameDay($date)
            ));
        $slots = [];

        foreach ($periods as $availability) {
            foreach ($this->candidateStarts($appointment, $availability, $date, $duration, $context) as $cursor) {
                if ($this->isCandidateAvailableUsingContext($appointment, $cursor, $duration, $context)) {
                    $slots[] = [
                        'start' => $cursor->format('H:i'),
                        'end' => $cursor->copy()->addMinutes($duration)->format('H:i'),
                    ];
                }
            }
        }

        return collect($slots)->unique('start')->sortBy('start')->values()->all();
    }

    /**
     * @return array{
     *   weekly:Collection,
     *   special:Collection,
     *   appointments:Collection,
     *   sharedAppointments:Collection,
     *   unavailabilities:Collection,
     *   candidateRules:array{preparation:int,buffer_after:int}
     * }
     */
    private function availabilityRangeContext(
        Appointment $appointment,
        Carbon $firstDate,
        Carbon $endExclusive,
    ): array {
        $candidateRules = $this->schedulingPolicy->valuesForCandidate(
            $appointment,
            $appointment->user,
            $appointment->product,
        );
        $appointmentsQuery = Appointment::query()
            ->where('user_id', $appointment->user_id)
            ->whereKeyNot($appointment->id)
            ->notCancelled()
            ->where('appointment_date', '<', $endExclusive->copy()->addDays(2));

        $this->whereAppointmentEndsAfter($appointmentsQuery, $firstDate->copy()->subDays(2));
        $this->ignoreExternalAllDayBlocks($appointmentsQuery);

        $sharedAppointments = collect();
        $mode = $this->schedulingPolicy->resolvedMode($appointment, $appointment->user);
        if ($mode === 'cabinet' && $appointment->practice_location_id) {
            $sharedAppointments = $this->sharedCabinetScheduling->blockingAppointmentsForWindow(
                $appointment,
                $firstDate,
                $endExclusive,
            );
        }

        return [
            'weekly' => $this->weeklyAvailabilityQuery($appointment)->get(),
            'special' => $this->specialAvailabilityQuery($appointment)
                ->whereDate('date', '>=', $firstDate->toDateString())
                ->whereDate('date', '<', $endExclusive->toDateString())
                ->get(),
            'appointments' => $appointmentsQuery->with('user')->get(),
            'sharedAppointments' => $sharedAppointments,
            'unavailabilities' => Unavailability::query()
                ->where('user_id', $appointment->user_id)
                ->where('start_date', '<', $endExclusive->copy()->addMinutes($candidateRules['buffer_after']))
                ->where('end_date', '>', $firstDate->copy()->subMinutes($candidateRules['preparation']))
                ->get(),
            'candidateRules' => $candidateRules,
        ];
    }

    /**
     * @param array{
     *   appointments:Collection,
     *   sharedAppointments:Collection,
     *   unavailabilities:Collection,
     *   candidateRules:array{preparation:int,buffer_after:int}
     * } $context
     */
    private function isCandidateAvailableUsingContext(
        Appointment $appointment,
        Carbon $start,
        int $duration,
        array $context,
    ): bool {
        if (! $start->isFuture()) {
            return false;
        }

        $minimumNotice = max(0, (int) ($appointment->user->minimum_notice_hours ?? 0));
        if ($start->lt(now()->addHours($minimumNotice))) {
            return false;
        }

        if ($this->dailyLimitReachedInCollection($appointment, $start, $context['appointments'])) {
            return false;
        }

        if ($context['appointments']->contains(
            fn (Appointment $existing): bool => $this->appointmentsConflict(
                $start,
                $duration,
                $context['candidateRules'],
                $existing,
            )
        )) {
            return false;
        }

        if ($this->sharedCabinetScheduling->appointmentsConflictWithCandidate(
            $context['sharedAppointments'],
            $start,
            $duration,
            $context['candidateRules'],
        )) {
            return false;
        }

        $end = $start->copy()->addMinutes($duration);

        return ! $context['unavailabilities']->contains(
            fn (Unavailability $unavailability): bool => $unavailability->start_date->lt(
                $end->copy()->addMinutes($context['candidateRules']['buffer_after'])
            ) && $unavailability->end_date->gt(
                $start->copy()->subMinutes($context['candidateRules']['preparation'])
            )
        );
    }

    /** @param Collection<int, Appointment> $appointments */
    private function dailyLimitReachedInCollection(
        Appointment $appointment,
        Carbon $date,
        Collection $appointments,
    ): bool {
        $base = $appointments->filter(fn (Appointment $existing): bool =>
            $existing->appointment_date->isSameDay($date) && ! $existing->external
        );
        $productLimit = max(0, (int) ($appointment->product?->max_per_day ?? 0));

        if ($productLimit > 0 && $base->where('product_id', $appointment->product_id)->count() >= $productLimit) {
            return true;
        }

        $globalLimit = max(0, (int) ($appointment->user?->global_daily_booking_limit ?? 0));

        return $globalLimit > 0 && $base->count() >= $globalLimit;
    }

    private function matchesScheduleGrid(
        Appointment $appointment,
        $therapist,
        $availability,
        Carbon $start,
        int $duration
    ): bool {
        if ($this->schedulingPolicy->mode($therapist) === BookingSchedulingPolicy::MODE_LEGACY) {
            return true;
        }

        return collect($this->candidateStarts($appointment, $availability, $start, $duration))
            ->contains(fn (Carbon $candidate): bool => $candidate->equalTo($start));
    }

    /** @return array<int, Carbon> */
    private function candidateStarts(
        Appointment $appointment,
        $availability,
        Carbon $date,
        int $duration,
        ?array $context = null,
    ): array
    {
        $therapist = $appointment->user;
        $periodStart = Carbon::parse($date->toDateString().' '.$availability->start_time);
        $periodEnd = Carbon::parse($date->toDateString().' '.$availability->end_time);
        $mode = $this->schedulingPolicy->mode($therapist);
        $interval = $mode === BookingSchedulingPolicy::MODE_FIXED
            ? $this->schedulingPolicy->fixedInterval($therapist)
            : 15;

        if ($mode === BookingSchedulingPolicy::MODE_OPTIMIZED) {
            $rules = $this->schedulingPolicy->valuesForCandidate($appointment, $therapist, $appointment->product);
            $interval = max(1, $duration + max($rules['preparation'], $rules['buffer_after']));
        }

        $starts = [];
        for ($cursor = $periodStart->copy(); $cursor->copy()->addMinutes($duration)->lte($periodEnd); $cursor->addMinutes($interval)) {
            $starts[$cursor->format('Y-m-d H:i:s')] = $cursor->copy();
        }

        if ($mode === BookingSchedulingPolicy::MODE_OPTIMIZED) {
            foreach ($this->usefulBoundaryStarts($appointment, $periodStart, $periodEnd, $duration, $context) as $boundary) {
                $starts[$boundary->format('Y-m-d H:i:s')] = $boundary;
            }
        }

        ksort($starts);

        return array_values($starts);
    }

    /** @return array<int, Carbon> */
    private function usefulBoundaryStarts(
        Appointment $appointment,
        Carbon $periodStart,
        Carbon $periodEnd,
        int $duration,
        ?array $context = null,
    ): array
    {
        $candidateRules = $this->schedulingPolicy->valuesForCandidate($appointment, $appointment->user, $appointment->product);
        $boundaries = [];

        $existingAppointments = $context === null
            ? Appointment::query()
                ->with('user')
                ->where('user_id', $appointment->user_id)
                ->whereKeyNot($appointment->id)
                ->notCancelled()
                ->where('appointment_date', '<', $periodEnd)
                ->where('appointment_date', '>=', $periodStart->copy()->subDay())
                ->get()
            : $context['appointments']->filter(fn (Appointment $existing): bool =>
                $existing->appointment_date->lt($periodEnd)
                && $existing->appointment_date->gte($periodStart->copy()->subDay())
            );

        foreach ($existingAppointments as $existing) {
            $existingRules = $this->schedulingPolicy->valuesForAppointment($existing, $existing->user);
            $boundaryBefore = $existing->appointment_date->copy()
                ->subMinutes(max($candidateRules['buffer_after'], $existingRules['preparation']))
                ->subMinutes($duration);
            if ($boundaryBefore->gte($periodStart) && $boundaryBefore->copy()->addMinutes($duration)->lte($periodEnd)) {
                $boundaries[] = $boundaryBefore;
            }

            $boundary = $existing->appointment_date->copy()
                ->addMinutes((int) ($existing->duration ?: 60))
                ->addMinutes(max($existingRules['buffer_after'], $candidateRules['preparation']));
            if ($boundary->gte($periodStart) && $boundary->copy()->addMinutes($duration)->lte($periodEnd)) {
                $boundaries[] = $boundary;
            }
        }

        $unavailabilities = $context === null
            ? Unavailability::query()
                ->where('user_id', $appointment->user_id)
                ->where('start_date', '<', $periodEnd)
                ->where('end_date', '>', $periodStart)
                ->get()
            : $context['unavailabilities']->filter(fn (Unavailability $unavailability): bool =>
                $unavailability->start_date->lt($periodEnd)
                && $unavailability->end_date->gt($periodStart)
            );

        foreach ($unavailabilities as $unavailability) {
            $boundaryBefore = Carbon::parse($unavailability->start_date)
                ->subMinutes($candidateRules['buffer_after'])
                ->subMinutes($duration);
            if ($boundaryBefore->gte($periodStart) && $boundaryBefore->copy()->addMinutes($duration)->lte($periodEnd)) {
                $boundaries[] = $boundaryBefore;
            }

            $boundary = Carbon::parse($unavailability->end_date)->addMinutes($candidateRules['preparation']);
            if ($boundary->gte($periodStart) && $boundary->copy()->addMinutes($duration)->lte($periodEnd)) {
                $boundaries[] = $boundary;
            }
        }

        return $boundaries;
    }

    /** @param array{preparation:int,buffer_after:int} $candidateRules */
    private function appointmentsConflict(Carbon $start, int $duration, array $candidateRules, Appointment $existing): bool
    {
        $existingStart = $existing->appointment_date;
        $existingEnd = $existingStart->copy()->addMinutes((int) ($existing->duration ?: 60));
        $existingRules = $this->schedulingPolicy->valuesForAppointment($existing, $existing->user);

        if ($existingStart->gte($start)) {
            return $existingStart->lt(
                $start->copy()->addMinutes($duration + max($candidateRules['buffer_after'], $existingRules['preparation']))
            );
        }

        return $existingEnd->copy()
            ->addMinutes(max($existingRules['buffer_after'], $candidateRules['preparation']))
            ->gt($start);
    }

    private function availabilitiesFor(Appointment $appointment, Carbon $date)
    {
        return $this->weeklyAvailabilityQuery($appointment)
            ->where('day_of_week', $date->dayOfWeekIso - 1)
            ->get()
            ->concat(
                $this->specialAvailabilityQuery($appointment)
                    ->whereDate('date', $date->toDateString())
                    ->get()
            );
    }

    private function weeklyAvailabilityQuery(Appointment $appointment): Builder
    {
        $productId = (int) $appointment->product_id;
        $mode = $this->schedulingPolicy->resolvedMode($appointment, $appointment->user);
        $locationId = $mode === 'cabinet' ? $appointment->practice_location_id : null;
        $query = Availability::query()
            ->where('user_id', $appointment->user_id)
            ->where(function ($availabilityQuery) use ($productId) {
                $availabilityQuery->where('applies_to_all', true)
                    ->orWhereHas('products', fn ($products) => $products->where('products.id', $productId));
            });

        if ($mode === 'cabinet') {
            $locationId
                ? $query->where('practice_location_id', $locationId)
                : $query->whereRaw('1 = 0');
        }

        return $query;
    }

    private function specialAvailabilityQuery(Appointment $appointment): Builder
    {
        $productId = (int) $appointment->product_id;
        $mode = $this->schedulingPolicy->resolvedMode($appointment, $appointment->user);
        $locationId = $mode === 'cabinet' ? $appointment->practice_location_id : null;
        $query = SpecialAvailability::query()
            ->where('user_id', $appointment->user_id)
            ->where(function ($availabilityQuery) use ($productId) {
                $availabilityQuery->where('applies_to_all', true)
                    ->orWhereHas('products', fn ($products) => $products->where('products.id', $productId));
            });

        if ($mode === 'cabinet') {
            $locationId
                ? $query->where('practice_location_id', $locationId)
                : $query->whereRaw('1 = 0');
        }

        return $query;
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
