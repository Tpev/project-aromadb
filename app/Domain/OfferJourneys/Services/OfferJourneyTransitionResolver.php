<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyPageVersion;

class OfferJourneyTransitionResolver
{
    public function resolve(OfferJourney $journey, OfferJourneyPageVersion $page, array $context = []): ?array
    {
        $transitions = collect($journey->publishedVersion?->snapshot_json['transitions'] ?? [])
            ->where('from_page_id', $page->offer_journey_page_id)
            ->where('is_active', true)
            ->sortBy('priority');

        $matched = $transitions->first(function (array $transition) use ($context) {
            if ($transition['is_fallback'] ?? false) {
                return false;
            }

            return $this->matches($transition['condition_json'] ?? null, $context);
        }) ?: $transitions->first(fn (array $transition) => (bool) ($transition['is_fallback'] ?? false));

        return $matched ?: null;
    }

    public function nextPageSlug(OfferJourney $journey, OfferJourneyPageVersion $page, array $context = []): ?string
    {
        $transition = $this->resolve($journey, $page, $context);

        return ! empty($transition['to_page_id'])
            ? $journey->publishedVersion?->pages->firstWhere('offer_journey_page_id', $transition['to_page_id'])?->slug
            : null;
    }

    private function matches(?array $condition, array $context): bool
    {
        if (! $condition || ($condition['type'] ?? 'always') === 'always') {
            return true;
        }

        if (($condition['type'] ?? null) === 'marketing_consent') {
            return (bool) ($context['marketing_consent'] ?? false);
        }

        return false;
    }
}
