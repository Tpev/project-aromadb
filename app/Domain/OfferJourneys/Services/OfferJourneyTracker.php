<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyPage;
use App\Domain\OfferJourneys\Models\OfferJourneyVersion;
use App\Support\OfferJourneys\OfferJourneyAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class OfferJourneyTracker
{
    public function __construct(private readonly OfferJourneyCampaignResolver $campaignResolver)
    {
    }

    public function recordPageView(
        OfferJourney $journey,
        OfferJourneyVersion $version,
        OfferJourneyPage $page,
        Request $request
    ): void {
        if (! app(OfferJourneyAccess::class)->trackingAvailable()) {
            return;
        }

        try {
            $attribution = $this->campaignResolver->attribution($journey, $request);
            $journey->events()->create([
                'offer_journey_version_id' => $version->id,
                'offer_journey_page_id' => $page->id,
                'offer_journey_campaign_link_id' => $attribution['campaign']?->id,
                'session_id' => $this->sessionId($request),
                'event_name' => 'page_viewed',
                'url' => Str::limit($request->fullUrl(), 2000, ''),
                'referer' => Str::limit((string) $request->headers->get('referer'), 2000, ''),
                'utm_source' => $attribution['utm_source'],
                'utm_medium' => $attribution['utm_medium'],
                'utm_campaign' => $attribution['utm_campaign'],
                'metadata' => ['device' => $request->userAgent() && str_contains(strtolower($request->userAgent()), 'mobile') ? 'mobile' : 'desktop'],
                'is_test' => false,
                'is_bot' => $this->looksLikeBot($request),
                'occurred_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Offer journey tracking failed without blocking the public page.', [
                'journey_id' => $journey->id,
                'page_id' => $page->id,
                'exception' => $exception::class,
            ]);
        }
    }

    public function recordPrimaryAction(
        OfferJourney $journey,
        OfferJourneyVersion $version,
        OfferJourneyPage $page,
        Request $request
    ): void {
        if (! app(OfferJourneyAccess::class)->trackingAvailable()) {
            return;
        }

        try {
            $attribution = $this->campaignResolver->attribution($journey, $request);
            $journey->events()->create([
                'offer_journey_version_id' => $version->id,
                'offer_journey_page_id' => $page->id,
                'offer_journey_campaign_link_id' => $attribution['campaign']?->id,
                'session_id' => $this->sessionId($request),
                'event_name' => 'primary_cta_clicked',
                'url' => Str::limit($request->fullUrl(), 2000, ''),
                'referer' => Str::limit((string) $request->headers->get('referer'), 2000, ''),
                'utm_source' => $attribution['utm_source'],
                'utm_medium' => $attribution['utm_medium'],
                'utm_campaign' => $attribution['utm_campaign'],
                'metadata' => [],
                'is_test' => false,
                'is_bot' => $this->looksLikeBot($request),
                'occurred_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Offer journey CTA tracking failed without blocking the destination.', [
                'journey_id' => $journey->id,
                'page_id' => $page->id,
                'exception' => $exception::class,
            ]);
        }
    }

    private function sessionId(Request $request): string
    {
        $existing = $request->attributes->get('offer_journey_visitor_id')
            ?: $request->cookie('oj_visitor');

        return is_string($existing) && preg_match('/^[a-zA-Z0-9-]{20,64}$/', $existing)
            ? $existing
            : (string) Str::uuid();
    }

    private function looksLikeBot(Request $request): bool
    {
        return (bool) preg_match('/bot|crawler|spider|preview/i', (string) $request->userAgent());
    }
}
