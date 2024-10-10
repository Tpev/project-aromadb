<?php

namespace App\Policies;

use App\Models\Availability;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AvailabilityPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the availability.
     */
    public function update(User $user, Availability $availability)
    {
        return $user->id === $availability->user_id;
    }

    /**
     * Determine whether the user can delete the availability.
     */
    public function delete(User $user, Availability $availability)
    {
        return $user->id === $availability->user_id;
    }
		public function viewAny(User $user)
{
    // Only allow users who are therapists
    return $user->is_therapist;
}
}
