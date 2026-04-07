<?php

namespace App\Services;

use App\Models\PracticeLocation;
use App\Models\PracticeLocationInvite;
use App\Models\PracticeLocationMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CabinetAccessService
{
    public function enabled(): bool
    {
        return (bool) config('features.shared_cabinets_v1', false);
    }

    public function accessibleLocationsQuery(User $user): Builder
    {
        return PracticeLocation::query()
            ->where(function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);

                if ($this->enabled()) {
                    $query->orWhereHas('memberships', function (Builder $memberQuery) use ($user) {
                        $memberQuery->where('user_id', $user->id)
                            ->whereNotNull('accepted_at')
                            ->whereHas('practiceLocation', function (Builder $locationQuery) {
                                $locationQuery->where('is_shared', true);
                            });
                    });
                }
            })
            ->distinct();
    }

    public function accessibleLocations(User $user): Collection
    {
        return $this->accessibleLocationsQuery($user)
            ->with(['owner', 'memberships.user', 'pendingInvites.invitedUser'])
            ->orderByDesc('is_primary')
            ->orderBy('label')
            ->get();
    }

    public function canAccessLocation(User $user, PracticeLocation|int|null $location): bool
    {
        if (!$location) {
            return false;
        }

        $location = $location instanceof PracticeLocation
            ? $location
            : PracticeLocation::query()->find($location);

        if (!$location) {
            return false;
        }

        if ((int) $location->user_id === (int) $user->id) {
            return true;
        }

        if (!$this->enabled() || !$location->is_shared) {
            return false;
        }

        return $location->memberships()
            ->where('user_id', $user->id)
            ->whereNotNull('accepted_at')
            ->exists();
    }

    public function canManageLocation(User $user, PracticeLocation $location): bool
    {
        return (int) $location->user_id === (int) $user->id;
    }

    public function activeMemberUserIds(PracticeLocation $location): array
    {
        if (!$this->enabled() || !$location->is_shared) {
            return [(int) $location->user_id];
        }

        $memberIds = $location->memberships()
            ->whereNotNull('accepted_at')
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $memberIds[] = (int) $location->user_id;

        return array_values(array_unique($memberIds));
    }

    public function ensureOwnerMembership(PracticeLocation $location): PracticeLocationMember
    {
        return PracticeLocationMember::query()->updateOrCreate(
            [
                'practice_location_id' => $location->id,
                'user_id' => $location->user_id,
            ],
            [
                'role' => 'owner',
                'accepted_at' => now(),
                'added_by_user_id' => $location->user_id,
            ]
        );
    }

    public function cancelPendingInvites(PracticeLocation $location): void
    {
        PracticeLocationInvite::query()
            ->where('practice_location_id', $location->id)
            ->where('status', PracticeLocationInvite::STATUS_PENDING)
            ->update(['status' => PracticeLocationInvite::STATUS_CANCELLED]);
    }
}
