<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Domain\OfferJourneys\Models\OfferJourneySegment;
use App\Domain\OfferJourneys\Models\OfferJourneySuppression;
use App\Models\NewsletterOptOut;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfferJourneyCampaignAudience
{
    public function __construct(private readonly OfferJourneySegmentQuery $segmentQuery)
    {
    }

    public function queryForCampaign(OfferJourneyMessageCampaign $campaign): Builder
    {
        $query = OfferJourneyContact::query()->where('user_id', $campaign->user_id);

        if ($campaign->audience_type === 'segment' && $campaign->segment) {
            return $this->segmentQuery->apply($query, $campaign->segment);
        }

        $journeyIds = $campaign->journeys->pluck('id');

        return $query->whereHas('entries', fn (Builder $entries) => $entries->whereIn('offer_journey_id', $journeyIds));
    }

    public function queryForSegment(int $userId, OfferJourneySegment $segment): Builder
    {
        abort_unless((int) $segment->user_id === $userId, 404);

        return $this->segmentQuery->apply(
            OfferJourneyContact::query()->where('user_id', $userId),
            $segment
        );
    }

    public function queryForJourneys(int $userId, array $journeyIds): Builder
    {
        return OfferJourneyContact::query()
            ->where('user_id', $userId)
            ->whereHas('entries', fn (Builder $entries) => $entries->whereIn('offer_journey_id', $journeyIds));
    }

    /**
     * Resolve candidates once so preview and sending use the same exclusion rules.
     *
     * @return array{eligible: Collection, summary: array<string, int>}
     */
    public function resolve(Builder $query, int $userId, CarbonInterface $frequencySince): array
    {
        $contacts = $query
            ->with([
                'consents',
                'entries.journey',
                'messageDeliveries' => fn ($deliveries) => $deliveries
                    ->where('category', 'marketing')
                    ->where('is_test', false)
                    ->whereNotNull('sent_at')
                    ->where('sent_at', '>=', $frequencySince),
            ])
            ->get()
            ->unique(fn (OfferJourneyContact $contact) => $contact->email_normalized ?: 'contact:'.$contact->id)
            ->values();

        $emails = $contacts->map(fn (OfferJourneyContact $contact) => $this->email($contact))->filter()->unique()->values();
        $suppressions = OfferJourneySuppression::query()
            ->where('user_id', $userId)
            ->whereIn('email_normalized', $emails)
            ->get()
            ->groupBy('email_normalized');
        $optOuts = NewsletterOptOut::query()
            ->where('user_id', $userId)
            ->whereIn(DB::raw('LOWER(email)'), $emails)
            ->get()
            ->mapWithKeys(fn (NewsletterOptOut $optOut) => [Str::lower(trim($optOut->email)) => true]);

        $summary = [
            'matching' => $contacts->count(),
            'eligible' => 0,
            'invalid_email' => 0,
            'no_consent' => 0,
            'unsubscribed' => 0,
            'suppressed' => 0,
            'bounce_or_complaint' => 0,
            'frequency_limited' => 0,
        ];
        $eligible = collect();

        foreach ($contacts as $contact) {
            $email = $this->email($contact);
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $summary['invalid_email']++;
                continue;
            }
            if (! $contact->consents->contains(fn ($consent) => $consent->purpose === 'marketing_follow_up'
                && $consent->status === 'granted' && $consent->withdrawn_at === null)) {
                $summary['no_consent']++;
                continue;
            }
            if ($optOuts->has($email)) {
                $summary['unsubscribed']++;
                continue;
            }
            if ($suppressions->has($email)) {
                $summary['suppressed']++;
                if ($suppressions[$email]->contains(fn ($suppression) => Str::contains((string) $suppression->type, ['bounce', 'complaint', 'reject']))) {
                    $summary['bounce_or_complaint']++;
                }
                continue;
            }
            if ($contact->messageDeliveries->isNotEmpty()) {
                $summary['frequency_limited']++;
                continue;
            }

            $summary['eligible']++;
            $eligible->push($contact);
        }

        return compact('eligible', 'summary');
    }

    private function email(OfferJourneyContact $contact): string
    {
        return Str::lower(trim((string) ($contact->email_normalized ?: $contact->email)));
    }
}
