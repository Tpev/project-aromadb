<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyAutomationRun;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Domain\OfferJourneys\Models\OfferJourneySuppression;
use App\Models\NewsletterOptOut;
use App\Support\OfferJourneys\OfferJourneyAccess;
use Illuminate\Support\Str;

class OfferJourneyMessageGuard
{
    public function __construct(private readonly OfferJourneySendingPolicy $sendingPolicy)
    {
    }

    public function reason(OfferJourneyAutomationRun $run, string $category): ?string
    {
        $automation = $run->automation;
        $journey = $automation->journey;
        $contact = $run->contact;
        $access = app(OfferJourneyAccess::class);

        if (in_array($contact->status, ['not_now', 'anonymized', 'deleted'], true)) {
            return 'contact_inactive';
        }

        if (in_array($journey->objective, ['appointment', 'event', 'training', 'gift_voucher'], true)
            && ! app(OfferJourneySourceResolver::class)->sourceAvailable($journey, $journey->user)) {
            return 'source_unavailable';
        }

        if ($journey->conversions()->where('offer_journey_contact_id', $contact->id)->where('status', 'confirmed')->exists()) {
            return 'converted';
        }

        if ($journey->status === 'archived' || $automation->status === 'archived') {
            return 'archived';
        }

        if ($journey->status !== 'published' || $automation->status !== 'active' || $run->version->status !== 'published') {
            return 'temporarily_paused';
        }

        $emailAvailable = $category === 'transactional'
            ? $access->transactionalEmailAvailableFor($journey->user)
            : $access->marketingEmailAvailableFor($journey->user);

        if (! $emailAvailable) {
            return 'temporarily_disabled';
        }

        if ($policyReason = $this->sendingPolicy->blockingReason($journey->user, $category)) {
            return $policyReason;
        }

        if (! filter_var($contact->email, FILTER_VALIDATE_EMAIL)) {
            return 'invalid_email';
        }

        $purpose = $category === 'transactional' ? 'requested_response' : 'marketing_follow_up';
        $hasConsent = $contact->consents()
            ->where('purpose', $purpose)
            ->where('status', 'granted')
            ->whereNull('withdrawn_at')
            ->exists();

        if (! $hasConsent) {
            return 'missing_consent';
        }

        if ($category === 'marketing') {
            $normalizedEmail = Str::lower(trim((string) $contact->email));
            $suppressed = OfferJourneySuppression::query()
                ->where('user_id', $journey->user_id)
                ->where('email_normalized', $normalizedEmail)
                ->exists()
                || NewsletterOptOut::query()
                    ->where('user_id', $journey->user_id)
                    ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
                    ->exists();

            if ($suppressed) {
                return 'unsubscribed';
            }

        }

        return null;
    }
}
