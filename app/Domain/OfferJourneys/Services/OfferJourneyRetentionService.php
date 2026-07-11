<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyConsent;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyDeliverabilityEvent;
use App\Domain\OfferJourneys\Models\OfferJourneyEvent;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfferJourneyRetentionService
{
    public function plan(?int $userId = null): array
    {
        return [
            'contacts' => $this->contactQuery($userId)->count(),
            'analytics_events' => $this->eventQuery($userId)->count(),
            'message_deliveries' => $this->deliveryQuery($userId)->count(),
            'deliverability_events' => $this->deliverabilityQuery($userId)->count(),
            'consent_evidence' => $this->consentQuery($userId)->count(),
        ];
    }

    public function apply(?int $userId = null, int $limit = 500, bool $dryRun = false): array
    {
        $plan = $this->plan($userId);
        if ($dryRun || ! (bool) config('offer_journeys.retention.enabled', false)) {
            return $plan + ['applied' => false];
        }

        $limit = max(1, min(5000, $limit));
        $processed = [
            'contacts' => $this->anonymizeContacts($userId, $limit),
            'analytics_events' => $this->anonymizeEvents($userId, $limit),
            'message_deliveries' => $this->anonymizeDeliveries($userId, $limit),
            'deliverability_events' => $this->anonymizeDeliverability($userId, $limit),
            'consent_evidence' => $this->expireConsentEvidence($userId, $limit),
            'applied' => true,
        ];

        return $processed;
    }

    private function anonymizeContacts(?int $userId, int $limit): int
    {
        $ids = $this->contactQuery($userId)->limit($limit)->pluck('id');
        $count = 0;
        foreach ($ids as $id) {
            DB::transaction(function () use ($id, &$count): void {
                $contact = OfferJourneyContact::query()->lockForUpdate()->find($id);
                if (! $contact || $contact->client_profile_id) {
                    return;
                }

                $email = $contact->email_normalized;
                if ($email) {
                    app(OfferJourneyEmailSuppressionService::class)->suppress(
                        $contact->user,
                        $email,
                        'retention',
                        'Expiration de la duree de conservation marketing',
                        'retention_policy'
                    );
                }
                $contact->consents()->where('status', 'granted')->update(['status' => 'withdrawn', 'withdrawn_at' => now()]);
                $contact->tags()->detach();
                $contact->tasks()->where('status', 'open')->update(['status' => 'cancelled']);
                $contact->update([
                    'email' => null,
                    'email_normalized' => null,
                    'first_name' => null,
                    'last_name' => null,
                    'phone' => null,
                    'phone_normalized' => null,
                    'contact_preference' => null,
                    'city' => null,
                    'postal_code' => null,
                    'metadata' => null,
                    'status' => 'anonymized',
                ]);
                $count++;
            });
        }

        return $count;
    }

    private function anonymizeEvents(?int $userId, int $limit): int
    {
        $ids = $this->eventQuery($userId)->limit($limit)->pluck('offer_journey_events.id');

        return OfferJourneyEvent::query()->whereKey($ids)->update([
            'offer_journey_contact_id' => null,
            'session_id' => null,
            'url' => null,
            'referer' => null,
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
            'metadata' => null,
        ]);
    }

    private function anonymizeDeliveries(?int $userId, int $limit): int
    {
        $ids = $this->deliveryQuery($userId)->limit($limit)->pluck('id');
        $count = 0;
        OfferJourneyMessageDelivery::query()->whereKey($ids)->each(function (OfferJourneyMessageDelivery $delivery) use (&$count): void {
            $delivery->update([
                'recipient_email' => 'anonymized+'.substr(hash('sha256', $delivery->id.'|'.$delivery->recipient_email), 0, 20).'@invalid.local',
                'subject' => 'Message anonymise selon la politique de conservation',
                'failure_reason' => null,
                'metadata' => null,
            ]);
            $count++;
        });

        return $count;
    }

    private function anonymizeDeliverability(?int $userId, int $limit): int
    {
        $ids = $this->deliverabilityQuery($userId)->limit($limit)->pluck('id');

        return OfferJourneyDeliverabilityEvent::query()->whereKey($ids)->update([
            'recipient_email' => null,
            'diagnostic' => null,
            'metadata' => null,
        ]);
    }

    private function expireConsentEvidence(?int $userId, int $limit): int
    {
        $ids = $this->consentQuery($userId)->limit($limit)->pluck('offer_journey_consents.id');

        return OfferJourneyConsent::query()->whereKey($ids)->update([
            'ip_hash' => null,
            'user_agent_summary' => null,
            'context_json' => null,
            'text_snapshot' => 'Preuve detaillee expiree selon la politique de conservation.',
        ]);
    }

    private function contactQuery(?int $userId): Builder
    {
        $cutoff = now()->subDays(max(1, (int) config('offer_journeys.retention.contact_days', 1095)));

        return OfferJourneyContact::query()
            ->whereNull('client_profile_id')
            ->whereNull('converted_at')
            ->whereNotIn('status', ['anonymized', 'deleted'])
            ->where(fn (Builder $query) => $query
                ->where('last_activity_at', '<', $cutoff)
                ->orWhere(fn (Builder $query) => $query->whereNull('last_activity_at')->where('created_at', '<', $cutoff)))
            ->when($userId, fn (Builder $query) => $query->where('user_id', $userId));
    }

    private function eventQuery(?int $userId): Builder
    {
        return OfferJourneyEvent::query()
            ->where('occurred_at', '<', now()->subDays(max(1, (int) config('offer_journeys.retention.analytics_days', 395))))
            ->where(fn (Builder $query) => $query->whereNotNull('session_id')->orWhereNotNull('url')->orWhereNotNull('offer_journey_contact_id'))
            ->when($userId, fn (Builder $query) => $query->whereHas('journey', fn (Builder $journey) => $journey->where('user_id', $userId)));
    }

    private function deliveryQuery(?int $userId): Builder
    {
        return OfferJourneyMessageDelivery::query()
            ->where('created_at', '<', now()->subDays(max(1, (int) config('offer_journeys.retention.delivery_days', 395))))
            ->where('recipient_email', 'not like', 'anonymized+%@invalid.local')
            ->when($userId, fn (Builder $query) => $query->where('user_id', $userId));
    }

    private function deliverabilityQuery(?int $userId): Builder
    {
        return OfferJourneyDeliverabilityEvent::query()
            ->where('occurred_at', '<', now()->subDays(max(1, (int) config('offer_journeys.retention.delivery_days', 395))))
            ->where(fn (Builder $query) => $query->whereNotNull('recipient_email')->orWhereNotNull('diagnostic')->orWhereNotNull('metadata'))
            ->when($userId, fn (Builder $query) => $query->where('user_id', $userId));
    }

    private function consentQuery(?int $userId): Builder
    {
        return OfferJourneyConsent::query()
            ->join('offer_journey_contacts', 'offer_journey_contacts.id', '=', 'offer_journey_consents.offer_journey_contact_id')
            ->where('offer_journey_consents.created_at', '<', now()->subDays(max(1, (int) config('offer_journeys.retention.consent_evidence_days', 1825))))
            ->where(fn (Builder $query) => $query->whereNotNull('offer_journey_consents.ip_hash')->orWhereNotNull('offer_journey_consents.context_json'))
            ->when($userId, fn (Builder $query) => $query->where('offer_journey_contacts.user_id', $userId));
    }
}
