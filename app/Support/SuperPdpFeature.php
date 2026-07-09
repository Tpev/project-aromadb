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

        $environment = strtolower((string) config('services.super_pdp.environment', 'sandbox'));

        if (in_array($environment, ['production', 'prod', 'live'], true)) {
            return true;
        }

        if ($environment === 'sandbox') {
            $allowedEmails = config('services.super_pdp.allowed_emails', []);

            return in_array(strtolower((string) $user->email), $allowedEmails, true);
        }

        return false;
    }

    public static function abortUnlessEnabledFor(?User $user): void
    {
        abort_unless(self::enabledFor($user), 404);
    }
}
