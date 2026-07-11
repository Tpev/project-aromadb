<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyPage;
use App\Domain\OfferJourneys\Models\OfferJourneyReusableSection;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfferJourneyReusableSectionController extends Controller
{
    use AuthorizesRequests;

    private const TYPES = ['audience', 'outcomes', 'steps', 'hero_image', 'gallery', 'video', 'testimonials', 'speaker', 'price', 'practical', 'faq'];

    public function store(Request $request, OfferJourney $journey, OfferJourneyPage $page): RedirectResponse
    {
        $this->authorizePage($journey, $page);
        abort_unless((bool) config('offer_journeys.rich_editor_enabled', false), 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(self::TYPES)],
        ]);
        $block = collect($page->draft_content_json['blocks'] ?? [])->firstWhere('type', $data['type']);
        if (! $block) {
            return back()->withErrors(['section' => 'Activez et enregistrez cette section avant de la conserver.']);
        }

        OfferJourneyReusableSection::query()->create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'type' => $data['type'],
            'content_json' => $block['data'] ?? [],
        ]);

        return back()->with('success', 'Section ajoutee a votre bibliotheque.');
    }

    public function apply(Request $request, OfferJourney $journey, OfferJourneyPage $page, OfferJourneyReusableSection $section): RedirectResponse
    {
        $this->authorizePage($journey, $page);
        abort_unless((int) $section->user_id === (int) $request->user()->id, 404);
        $content = $page->draft_content_json ?? [];
        $blocks = collect($content['blocks'] ?? [])->reject(fn (array $block): bool => ($block['type'] ?? null) === $section->type)->values();
        $blocks->push([
            'id' => $section->type,
            'type' => $section->type,
            'position' => $blocks->count(),
            'data' => $section->content_json,
        ]);
        $content['blocks'] = $blocks->values()->all();
        $page->update(['draft_content_json' => $content]);

        return back()->with('success', 'Section appliquee au brouillon. Verifiez-la avant de publier.');
    }

    public function destroy(Request $request, OfferJourneyReusableSection $section): RedirectResponse
    {
        abort_unless((int) $section->user_id === (int) $request->user()->id, 404);
        $section->delete();

        return back()->with('success', 'Section retiree de la bibliotheque.');
    }

    private function authorizePage(OfferJourney $journey, OfferJourneyPage $page): void
    {
        $this->authorize('update', $journey);
        abort_unless((int) $page->offer_journey_id === (int) $journey->id, 404);
    }
}
