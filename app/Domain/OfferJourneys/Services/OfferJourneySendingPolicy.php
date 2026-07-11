<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyDeliverabilityEvent;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Domain\OfferJourneys\Models\OfferJourneySenderControl;
use App\Models\User;

class OfferJourneySendingPolicy
{
    public function blockingReason(User $user, string $category): ?string
    {
        $control = OfferJourneySenderControl::query()->where('user_id', $user->id)->first();
        if ($control?->all_email_paused) {
            return 'sender_paused';
        }
        if ($category === 'marketing' && $control?->marketing_paused) {
            return 'marketing_paused';
        }

        if ($category !== 'marketing') {
            return null;
        }

        $sentThisMonth = OfferJourneyMessageDelivery::query()
            ->where('user_id', $user->id)
            ->where('category', 'marketing')
            ->whereNotNull('sent_at')
            ->where('sent_at', '>=', now()->startOfMonth())
            ->count();

        $globalLimit = (int) config('offer_journeys.limits.monthly_marketing_emails', 2000);
        if ($sentThisMonth >= $globalLimit) {
            return 'monthly_quota';
        }
        if ($sentThisMonth >= $this->monthlyLimit($user)) {
            return 'progressive_quota';
        }

        $sentLastThirtyDays = OfferJourneyMessageDelivery::query()
            ->where('user_id', $user->id)
            ->where('is_test', false)
            ->whereNotNull('sent_at')
            ->where('sent_at', '>=', now()->subDays(30))
            ->count();

        if ($sentLastThirtyDays >= (int) config('offer_journeys.deliverability.minimum_volume_for_reputation', 20)) {
            $complaints = $this->eventCount($user, 'complaint');
            $permanentBounces = OfferJourneyDeliverabilityEvent::query()
                ->where('user_id', $user->id)
                ->where('event_type', 'bounce')
                ->where('event_subtype', 'permanent')
                ->where('occurred_at', '>=', now()->subDays(30))
                ->count();

            if (($complaints / $sentLastThirtyDays) >= (float) config('offer_journeys.deliverability.max_complaint_rate', 0.001)) {
                return 'complaint_rate';
            }
            if (($permanentBounces / $sentLastThirtyDays) >= (float) config('offer_journeys.deliverability.max_bounce_rate', 0.05)) {
                return 'bounce_rate';
            }
        }

        return null;
    }

    public function monthlyLimit(User $user): int
    {
        $accountAgeDays = max(0, $user->created_at?->diffInDays(now()) ?? 0);
        $tiers = collect(config('offer_journeys.deliverability.progressive_monthly_limits', []))
            ->sortBy('minimum_account_age_days');
        $globalLimit = (int) config('offer_journeys.limits.monthly_marketing_emails', 2000);
        $tierLimit = $globalLimit;

        foreach ($tiers as $tier) {
            if ($accountAgeDays >= (int) ($tier['minimum_account_age_days'] ?? 0)) {
                $tierLimit = max(0, (int) ($tier['limit'] ?? $tierLimit));
            }
        }

        return min($globalLimit, $tierLimit);
    }

    private function eventCount(User $user, string $type): int
    {
        return OfferJourneyDeliverabilityEvent::query()
            ->where('user_id', $user->id)
            ->where('event_type', $type)
            ->where('occurred_at', '>=', now()->subDays(30))
            ->count();
    }
}
