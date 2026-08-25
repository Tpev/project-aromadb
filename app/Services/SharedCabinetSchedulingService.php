<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\PracticeLocation;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SharedCabinetSchedulingService
{
    public function __construct(
        private readonly CabinetAccessService $cabinetAccessService,
        private readonly BookingSchedulingPolicy $schedulingPolicy,
    ) {
    }

    /** @param array{preparation:int,buffer_after:int} $candidateRules */
    public function hasSharedCabinetConflictForAppointment(
        Appointment $appointment,
        CarbonInterface $start,
        array $candidateRules
    ): bool {
        $duration = (int) ($appointment->duration ?: $appointment->product?->duration ?: 0);
        $end = $start->copy()->addMinutes($duration);

        return $this->appointmentsConflictWithCandidate(
            $this->blockingAppointmentsForWindow($appointment, $start, $end),
            $start,
            $duration,
            $candidateRules,
        );
    }

    /** @return Collection<int, Appointment> */
    public function blockingAppointmentsForWindow(
        Appointment $appointment,
        CarbonInterface $start,
        CarbonInterface $end,
    ): Collection {
        $locationId = (int) $appointment->practice_location_id;
        $location = PracticeLocation::query()->find($locationId);

        if (! $location || ! $location->is_shared || ! $this->cabinetAccessService->enabled()) {
            return collect();
        }

        $memberIds = $this->cabinetAccessService->activeMemberUserIds($location);
        if ($memberIds === []) {
            return collect();
        }

        return Appointment::query()
            ->with('user')
            ->whereIn('user_id', $memberIds)
            ->where('practice_location_id', $locationId)
            ->whereKeyNot($appointment->id)
            ->where(function (Builder $statusQuery) {
                $this->applyBlockingAppointmentsFilter($statusQuery);
            })
            ->where('appointment_date', '<', $end->copy()->addDays(2))
            ->where('appointment_date', '>=', $start->copy()->subDays(2))
            ->get();
    }

    /**
     * @param Collection<int, Appointment> $appointments
     * @param array{preparation:int,buffer_after:int} $candidateRules
     */
    public function appointmentsConflictWithCandidate(
        Collection $appointments,
        CarbonInterface $start,
        int $duration,
        array $candidateRules,
    ): bool {
        $end = $start->copy()->addMinutes($duration);

        return $appointments->contains(function (Appointment $existing) use ($start, $end, $candidateRules): bool {
                $existingStart = $existing->appointment_date;
                $existingEnd = $existingStart->copy()->addMinutes((int) ($existing->duration ?: 60));
                $existingRules = $this->schedulingPolicy->valuesForAppointment($existing, $existing->user);

                if ($existingStart->gte($start)) {
                    return $existingStart->lt($end->copy()->addMinutes(
                        max($candidateRules['buffer_after'], $existingRules['preparation'])
                    ));
                }

                return $existingEnd->copy()->addMinutes(
                    max($existingRules['buffer_after'], $candidateRules['preparation'])
                )->gt($start);
            });
    }

    public function shouldApplySharedConstraint(?string $mode, ?int $practiceLocationId): bool
    {
        if ($mode !== 'cabinet' || !$practiceLocationId || !$this->cabinetAccessService->enabled()) {
            return false;
        }

        $location = PracticeLocation::query()->find($practiceLocationId);

        return (bool) ($location?->is_shared);
    }

    public function hasSharedCabinetConflict(
        CarbonInterface $start,
        int $durationMinutes,
        int $practiceLocationId,
        ?int $excludeAppointmentId = null
    ): bool {
        $location = PracticeLocation::query()->find($practiceLocationId);

        if (!$location || !$location->is_shared || !$this->cabinetAccessService->enabled()) {
            return false;
        }

        $end = $start->copy()->addMinutes($durationMinutes);
        $memberIds = $this->cabinetAccessService->activeMemberUserIds($location);

        if (empty($memberIds)) {
            return false;
        }

        $query = Appointment::query()
            ->whereIn('user_id', $memberIds)
            ->where('practice_location_id', $practiceLocationId)
            ->where(function (Builder $statusQuery) {
                $this->applyBlockingAppointmentsFilter($statusQuery);
            })
            ->where('appointment_date', '<', $end->format('Y-m-d H:i:s'))
            ->whereRaw('DATE_ADD(appointment_date, INTERVAL duration MINUTE) > ?', [$start->format('Y-m-d H:i:s')]);

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return $query->exists();
    }

    public function applySharedCabinetConflictFilter(
        Builder $query,
        CarbonInterface $start,
        CarbonInterface $end,
        int $practiceLocationId,
        ?int $excludeAppointmentId = null
    ): Builder {
        $location = PracticeLocation::query()->find($practiceLocationId);

        if (!$location || !$location->is_shared || !$this->cabinetAccessService->enabled()) {
            return $query->whereRaw('1 = 0');
        }

        $memberIds = $this->cabinetAccessService->activeMemberUserIds($location);

        $query->whereIn('user_id', $memberIds)
            ->where('practice_location_id', $practiceLocationId)
            ->where(function (Builder $statusQuery) {
                $this->applyBlockingAppointmentsFilter($statusQuery);
            })
            ->where('appointment_date', '<', $end->format('Y-m-d H:i:s'))
            ->whereRaw('DATE_ADD(appointment_date, INTERVAL duration MINUTE) > ?', [$start->format('Y-m-d H:i:s')]);

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return $query;
    }

    private function applyBlockingAppointmentsFilter(Builder $query): void
    {
        $query->where(function (Builder $statusQuery) {
            $statusQuery->whereNull('status')
                ->orWhereNotIn('status', Appointment::CANCELLED_STATUSES);
        });

        $driver = app('db')->connection()->getDriverName();
        $portableCondition = $driver === 'sqlite'
            ? "NOT (external = 1 AND time(appointment_date) = '00:00:00' AND COALESCE(duration,0) >= 2880 AND (COALESCE(duration,0) % 1440) = 0)"
            : 'NOT (external = 1 AND TIME(appointment_date) = "00:00:00" AND COALESCE(duration,0) >= 2880 AND MOD(COALESCE(duration,0),1440) = 0)';

        $query->whereRaw($portableCondition);
    }
}
