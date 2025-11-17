<?php

namespace App\Policies;

use App\Models\SpecialAvailability;
use App\Models\User;

class SpecialAvailabilityPolicy
{
    /**
     * Voir la liste.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_therapist ?? true; // adapte selon ton app
    }

    /**
     * Voir une dispo ponctuelle.
     */
    public function view(User $user, SpecialAvailability $specialAvailability): bool
    {
        return $specialAvailability->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_therapist ?? true;
    }

    public function update(User $user, SpecialAvailability $specialAvailability): bool
    {
        return $specialAvailability->user_id === $user->id;
    }

    public function delete(User $user, SpecialAvailability $specialAvailability): bool
    {
        return $specialAvailability->user_id === $user->id;
    }
}
