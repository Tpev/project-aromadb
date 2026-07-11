<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyAutomationRun;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneySuppression;
use App\Models\NewsletterOptOut;
use App\Models\User;
use Illuminate\Support\Str;

class OfferJourneyEmailSuppressionService
{
    public function suppress(User $user, string $email, string $type, string $reason, string $source): void
    {
        $normalized = Str::lower(trim($email));
        if (! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $contacts = OfferJourneyContact::query()
            ->where('user_id', $user->id)
            ->where('email_normalized', $normalized)
            ->get();

        if ($contacts->isEmpty()) {
            OfferJourneySuppression::query()->firstOrCreate([
                'user_id' => $user->id,
                'email_normalized' => $normalized,
                'type' => $type,
            ], [
                'reason' => Str::limit($reason, 255),
                'source' => $source,
                'suppressed_at' => now(),
            ]);
        }

        foreach ($contacts as $contact) {
            OfferJourneySuppression::query()->firstOrCreate([
                'user_id' => $user->id,
                'email_normalized' => $normalized,
                'type' => $type,
            ], [
                'offer_journey_contact_id' => $contact->id,
                'reason' => Str::limit($reason, 255),
                'source' => $source,
                'suppressed_at' => now(),
            ]);

            OfferJourneyAutomationRun::query()
                ->where('offer_journey_contact_id', $contact->id)
                ->where('status', 'running')
                ->update([
                    'status' => 'exited',
                    'exit_reason' => 'suppressed',
                    'exited_at' => now(),
                    'next_action_at' => null,
                ]);
        }

        NewsletterOptOut::query()->firstOrCreate([
            'user_id' => $user->id,
            'email' => $normalized,
        ], [
            'reason' => Str::limit($reason, 255),
            'unsubscribed_at' => now(),
        ]);
    }
}
