<?php

namespace App\Policies;

use App\Models\Availability;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AvailabilityPolicy
{
    use HandlesAuthorization;

    /**
     * Let admins pass everything (optional).
     */
    public function before(User $user, $ability)
    {
        if (property_exists($user, 'is_admin') && $user->is_admin) {
            return true;
        }
    }

    /**
     * Can the user list their availabilities?
     */
    public function viewAny(User $user): bool
    {
        // adjust if you want all authenticated users
        return (bool) ($user->is_therapist ?? false);
    }

    /**
     * Can the user see a specific availability?
     */
    public function view(User $user, Availability $availability): bool
    {
        return $availability->user_id === $user->id;
    }

    /**
     * Can the user get to the "create" page and store a new availability?
     */
    public function create(User $user): bool
    {
        // adjust if you want to allow any logged-in user
        return (bool) ($user->is_therapist ?? false);
    }

    /**
     * Can the user update an availability?
     */
    public function update(User $user, Availability $availability): bool
    {
        return $availability->user_id === $user->id;
    }

    /**
     * Can the user delete an availability?
     */
    public function delete(User $user, Availability $availability): bool
    {
        return $availability->user_id === $user->id;
    }
}
