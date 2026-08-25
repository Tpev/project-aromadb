<?php

namespace App\Support;

use App\Models\User;

class BookingV2Access
{
    public function enabledFor(User|int|null $practitioner): bool
    {
        $practitionerId = $practitioner instanceof User
            ? (int) $practitioner->getKey()
            : (int) $practitioner;

        if (! config('appointments.booking_v2.enabled', false) || $practitionerId <= 0) {
            return false;
        }

        return in_array(
            $practitionerId,
            array_map('intval', config('appointments.booking_v2.allowed_user_ids', [])),
            true
        );
    }
}
