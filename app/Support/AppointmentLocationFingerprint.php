<?php

namespace App\Support;

use App\Models\Appointment;
use Illuminate\Support\Str;

final class AppointmentLocationFingerprint
{
    public static function for(Appointment $appointment): ?string
    {
        $appointment->loadMissing('clientProfile');

        $address = trim((string) ($appointment->address ?: $appointment->clientProfile?->address));
        if ($address === '') {
            return null;
        }

        $normalized = Str::of($address)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->toString();

        return $normalized === '' ? null : hash('sha256', $normalized);
    }
}
