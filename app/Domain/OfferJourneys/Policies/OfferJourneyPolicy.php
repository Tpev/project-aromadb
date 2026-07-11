<?php

namespace App\Domain\OfferJourneys\Policies;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Models\User;
use App\Support\OfferJourneys\OfferJourneyAccess;

class OfferJourneyPolicy
{
    public function viewAny(User $user): bool
    {
        return app(OfferJourneyAccess::class)->availableFor($user);
    }

    public function view(User $user, OfferJourney $journey): bool
    {
        return $this->viewAny($user) && (int) $journey->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return app(OfferJourneyAccess::class)->canPublish($user);
    }

    public function update(User $user, OfferJourney $journey): bool
    {
        return $this->view($user, $journey) && app(OfferJourneyAccess::class)->canPublish($user);
    }

    public function publish(User $user, OfferJourney $journey): bool
    {
        return $this->update($user, $journey);
    }

    public function delete(User $user, OfferJourney $journey): bool
    {
        return $this->update($user, $journey);
    }
}
