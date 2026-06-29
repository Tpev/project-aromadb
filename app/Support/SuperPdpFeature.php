<?php

namespace App\Support;

use App\Models\User;

class SuperPdpFeature
{
    public static function enabledFor(?User $user): bool
    {
        if (! $user || ! $user->isTherapist()) {
            return false;
        }

        if (config('services.super_pdp.environment', 'sandbox') !== 'sandbox') {
            return false;
        }

        $allowedEmails = config('services.super_pdp.allowed_emails', []);

        return in_array(strtolower((string) $user->email), $allowedEmails, true);
    }

    public static function abortUnlessEnabledFor(?User $user): void
    {
        abort_unless(self::enabledFor($user), 404);
    }
}
