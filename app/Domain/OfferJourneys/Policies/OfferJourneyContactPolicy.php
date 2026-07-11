<?php

namespace App\Domain\OfferJourneys\Policies;

use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Models\User;
use App\Support\OfferJourneys\OfferJourneyAccess;

class OfferJourneyContactPolicy
{
    public function viewAny(User $user): bool
    {
        return app(OfferJourneyAccess::class)->availableFor($user);
    }

    public function view(User $user, OfferJourneyContact $contact): bool
    {
        return $this->viewAny($user) && (int) $contact->user_id === (int) $user->id;
    }

    public function update(User $user, OfferJourneyContact $contact): bool
    {
        return $this->view($user, $contact) && app(OfferJourneyAccess::class)->canPublish($user);
    }

    public function delete(User $user, OfferJourneyContact $contact): bool
    {
        return $this->update($user, $contact);
    }
}
