<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyCampaignLink;
use Illuminate\Http\Request;

class OfferJourneyCampaignResolver
{
    public function resolve(OfferJourney $journey, Request $request): ?OfferJourneyCampaignLink
    {
        $code = $request->query('oj_campaign');

        if (! is_string($code) || ! preg_match('/^[a-z0-9]{12}$/', $code)) {
            return null;
        }

        return OfferJourneyCampaignLink::query()
            ->where('offer_journey_id', $journey->id)
            ->where('code', $code)
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->first();
    }

    public function attribution(OfferJourney $journey, Request $request): array
    {
        $campaign = $this->resolve($journey, $request);

        return [
            'campaign' => $campaign,
            'utm_source' => $campaign?->utm_source ?: $request->query('utm_source'),
            'utm_medium' => $campaign?->utm_medium ?: $request->query('utm_medium'),
            'utm_campaign' => $campaign?->utm_campaign ?: $request->query('utm_campaign'),
        ];
    }
}
