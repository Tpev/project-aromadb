<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\PracticeLocation;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class SharedCabinetSchedulingService
{
    public function __construct(
        private readonly CabinetAccessService $cabinetAccessService,
    ) {
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
                ->orWhereNotIn('status', ['Annulé', 'Annule', 'cancelled', 'canceled', 'Annulée', 'Annulee']);
        });

        $driver = app('db')->connection()->getDriverName();
        $portableCondition = $driver === 'sqlite'
            ? "NOT (external = 1 AND time(appointment_date) = '00:00:00' AND COALESCE(duration,0) >= 2880 AND (COALESCE(duration,0) % 1440) = 0)"
            : 'NOT (external = 1 AND TIME(appointment_date) = "00:00:00" AND COALESCE(duration,0) >= 2880 AND MOD(COALESCE(duration,0),1440) = 0)';

        $query->whereRaw($portableCondition);
    }
}
