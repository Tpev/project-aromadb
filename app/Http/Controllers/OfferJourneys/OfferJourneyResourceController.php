<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyPage;
use App\Domain\OfferJourneys\Models\OfferJourneyPageVersion;
use App\Domain\OfferJourneys\Services\OfferJourneyResourceStorage;
use App\Http\Controllers\Controller;
use App\Support\OfferJourneys\OfferJourneyAccess;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OfferJourneyResourceController extends Controller
{
    public function download(OfferJourneyPageVersion $pageVersion, OfferJourneyResourceStorage $storage): StreamedResponse
    {
        $pageVersion->load('version.journey.user');
        $journey = $pageVersion->version?->journey;
        abort_unless($journey
            && $journey->status === 'published'
            && app(OfferJourneyAccess::class)->publicPagesAvailableFor($journey->user), 404);
        $resource = $pageVersion->content_json['resource_file'] ?? null;
        abort_unless($storage->exists($resource), 404);

        return Storage::disk('local')->download(
            $resource['path'],
            $resource['original_name'] ?? 'ressource-olithea',
            ['Content-Type' => 'application/octet-stream', 'X-Content-Type-Options' => 'nosniff']
        );
    }

    public function preview(OfferJourney $journey, OfferJourneyPage $page, OfferJourneyResourceStorage $storage): StreamedResponse
    {
        abort_unless((int) $page->offer_journey_id === (int) $journey->id, 404);
        $journey->load('user');
        abort_unless(app(OfferJourneyAccess::class)->canPublish($journey->user), 404);
        $resource = ($page->draft_content_json ?? [])['resource_file'] ?? null;
        abort_unless($storage->exists($resource), 404);

        return Storage::disk('local')->download(
            $resource['path'],
            $resource['original_name'] ?? 'ressource-olithea',
            ['Content-Type' => 'application/octet-stream', 'X-Content-Type-Options' => 'nosniff']
        );
    }
}
