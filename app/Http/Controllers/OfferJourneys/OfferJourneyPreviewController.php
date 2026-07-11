<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Services\OfferJourneySourceResolver;
use App\Http\Controllers\Controller;
use App\Support\OfferJourneys\OfferJourneyAccess;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class OfferJourneyPreviewController extends Controller
{
    use AuthorizesRequests;

    public function create(OfferJourney $journey): RedirectResponse
    {
        $this->authorize('view', $journey);
        $page = $journey->pages()->orderBy('position')->firstOrFail();

        return redirect()->away($this->previewUrl($journey, $page->slug));
    }

    public function show(Request $request, OfferJourney $journey, OfferJourneySourceResolver $sourceResolver, ?string $pageSlug = null): View
    {
        $journey->load(['user', 'pages.form.fields', 'transitions']);
        abort_unless(app(OfferJourneyAccess::class)->canPublish($journey->user), 404);
        $page = $pageSlug ? $journey->pages->firstWhere('slug', $pageSlug) : $journey->pages->sortBy('position')->first();
        abort_unless($page, 404);
        $content = $page->draft_content_json ?? [];
        if ($page->form) {
            $content['_form'] = [
                ...$page->form->only(['submit_label', 'success_message', 'privacy_text', 'marketing_consent_mode']),
                'fields' => $page->form->fields->map->only(['name', 'label', 'type', 'is_required', 'options_json', 'position', 'purpose'])->values()->all(),
            ];
        }
        $transition = $journey->transitions
            ->where('from_page_id', $page->id)
            ->where('is_active', true)
            ->sortBy('priority')
            ->first();
        $targetPage = ! empty($transition?->to_page_id) ? $journey->pages->firstWhere('id', $transition->to_page_id) : null;
        $sourceAction = $sourceResolver->sourceAvailable($journey, $journey->user)
            ? $sourceResolver->publicActionUrl($journey, $journey->user)
            : null;
        $resourceAction = ! empty($content['resource_file'])
            ? URL::temporarySignedRoute(
                'offer-journeys.resources.preview',
                now()->addMinutes((int) config('offer_journeys.resource_link_minutes', 30)),
                ['journey' => $journey, 'page' => $page]
            )
            : ($content['resource_url'] ?? null);
        $primaryActionUrl = $targetPage
            ? $this->previewUrl($journey, $targetPage->slug)
            : ($resourceAction ?: $sourceAction);

        return view('offer-journeys.public.show', [
            'therapist' => $journey->user,
            'journey' => $journey,
            'page' => $page,
            'content' => $content,
            'actionUrl' => $sourceAction,
            'sourceAvailable' => (bool) $sourceAction,
            'hasPublicAction' => filled($primaryActionUrl),
            'primaryActionUrl' => $primaryActionUrl,
            'isPreview' => true,
        ]);
    }

    private function previewUrl(OfferJourney $journey, string $pageSlug): string
    {
        return URL::temporarySignedRoute('offer-journeys.preview.show', now()->addHour(), [
            'journey' => $journey,
            'pageSlug' => $pageSlug,
        ]);
    }
}
