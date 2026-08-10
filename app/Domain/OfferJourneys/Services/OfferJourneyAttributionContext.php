<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Cookie;
use Throwable;

class OfferJourneyAttributionContext
{
    public function __construct(private readonly OfferJourneyCampaignResolver $campaignResolver)
    {
    }

    public function cookie(OfferJourney $journey, Request $request): Cookie
    {
        $attribution = $this->campaignResolver->attribution($journey, $request);
        $payload = Crypt::encryptString(json_encode([
            'journey_id' => $journey->id,
            'journey_version_id' => $journey->published_version_id,
            'utm_source' => $attribution['utm_source'],
            'utm_medium' => $attribution['utm_medium'],
            'utm_campaign' => $attribution['utm_campaign'],
            'issued_at' => now()->timestamp,
        ], JSON_THROW_ON_ERROR));

        return cookie(
            'oj_attribution', $payload, config('offer_journeys.attribution_days', 30) * 1440,
            '/', null, $request->isSecure(), true, false, 'lax'
        );
    }

    public function resolve(int $userId, string $sourceType, ?int $sourceId): ?array
    {
        if (! app()->bound('request')) {
            return null;
        }

        $value = request()->cookie('oj_attribution');
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            $payload = json_decode(Crypt::decryptString($value), true, 8, JSON_THROW_ON_ERROR);
            if (! is_array($payload)
                || (int) ($payload['issued_at'] ?? 0) < now()->subDays(config('offer_journeys.attribution_days', 30))->timestamp) {
                return null;
            }

            $journey = OfferJourney::query()
                ->whereKey((int) ($payload['journey_id'] ?? 0))
                ->where('user_id', $userId)
                ->whereIn('status', ['published', 'paused'])
                ->first();
            if (! $journey) {
                return null;
            }

            $version = $journey->versions()
                ->whereKey((int) ($payload['journey_version_id'] ?? 0))
                ->first();
            $snapshot = $version?->snapshot_json ?? [];
            $attributedSourceType = $version ? ($snapshot['source_type'] ?? null) : $journey->source_type;
            $attributedSourceId = $version ? ($snapshot['source_id'] ?? null) : $journey->source_id;
            if ($attributedSourceType !== $sourceType
                || ($sourceId !== null && (int) $attributedSourceId !== $sourceId)) {
                return null;
            }

            return ['journey' => $journey, 'payload' => $payload];
        } catch (Throwable) {
            return null;
        }
    }
}
