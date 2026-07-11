<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyPage;
use App\Domain\OfferJourneys\Services\OfferJourneyWritingAssistant;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferJourneyWritingAssistantController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, OfferJourney $journey, OfferJourneyPage $page, OfferJourneyWritingAssistant $assistant): JsonResponse
    {
        abort_unless((bool) config('offer_journeys.writing_assistant_enabled', false), 404);
        $this->authorize('update', $journey);
        abort_unless((int) $page->offer_journey_id === (int) $journey->id, 404);
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:1600'],
            'cta_label' => ['nullable', 'string', 'max:80'],
        ]);

        return response()->json($assistant->review(
            (string) ($data['title'] ?? ''),
            (string) ($data['summary'] ?? ''),
            (string) ($data['cta_label'] ?? ''),
            $journey->objective
        ));
    }
}
