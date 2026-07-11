<?php

namespace App\Support\OfferJourneys;

use App\Models\User;

class OfferJourneyAccess
{
    public function globallyEnabled(): bool
    {
        return (bool) config('offer_journeys.enabled', false);
    }

    public function availableFor(?User $user): bool
    {
        if (! $this->globallyEnabled() || ! $user?->isTherapist()) {
            return false;
        }

        $betaUserIds = config('offer_journeys.beta_user_ids', []);

        return (bool) config('offer_journeys.allow_all_eligible_users', false)
            || in_array((int) $user->id, $betaUserIds, true);
    }

    public function canPublish(?User $user): bool
    {
        return $this->availableFor($user) && $user->canUseFeature('sales_funnels');
    }

    public function publicPagesAvailableFor(?User $user): bool
    {
        return (bool) config('offer_journeys.public_pages_enabled', false)
            && $this->canPublish($user);
    }

    public function automationAvailableFor(?User $user): bool
    {
        return (bool) config('offer_journeys.automation_enabled', false)
            && $this->canPublish($user);
    }

    public function emailAvailableFor(?User $user): bool
    {
        return $this->marketingEmailAvailableFor($user);
    }

    public function transactionalEmailAvailableFor(?User $user): bool
    {
        return (bool) config('offer_journeys.email_enabled', false)
            && $this->automationAvailableFor($user);
    }

    public function marketingEmailAvailableFor(?User $user): bool
    {
        return $this->transactionalEmailAvailableFor($user)
            && ! (bool) config('offer_journeys.pause_all_marketing_emails', true)
            && $this->canPublish($user);
    }

    public function trackingAvailable(): bool
    {
        return $this->globallyEnabled()
            && (bool) config('offer_journeys.tracking_enabled', false);
    }
}
