<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyPipelineGoal;
use App\Domain\OfferJourneys\Models\OfferJourneyPipelineStage;
use App\Domain\OfferJourneys\Models\OfferJourneySavedFilter;
use App\Domain\OfferJourneys\Models\OfferJourneyTag;
use App\Domain\OfferJourneys\Services\OfferJourneyContactMerger;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OfferJourneyCommercialController extends Controller
{
    use AuthorizesRequests;

    public function saveFilter(Request $request): RedirectResponse
    {
        $this->available();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'filters' => ['required', 'array'],
            'filters.q' => ['nullable', 'string', 'max:120'],
            'filters.status' => ['nullable', Rule::in(['new', 'qualifying', 'contacted', 'converted', 'not_now'])],
            'filters.tag_id' => ['nullable', 'integer'],
            'filters.journey_id' => ['nullable', 'integer'],
        ]);
        OfferJourneySavedFilter::query()->updateOrCreate(
            ['user_id' => $request->user()->id, 'name' => $validated['name']],
            ['filters_json' => array_filter($validated['filters'], fn ($value) => filled($value))]
        );

        return back()->with('success', 'Le filtre a été enregistré.');
    }

    public function deleteFilter(Request $request, OfferJourneySavedFilter $filter): RedirectResponse
    {
        $this->available();
        abort_unless((int) $filter->user_id === (int) $request->user()->id, 404);
        $filter->delete();

        return back()->with('success', 'Le filtre a été supprimé.');
    }

    public function merge(Request $request, OfferJourneyContact $contact, OfferJourneyContactMerger $merger): RedirectResponse
    {
        $this->available();
        $this->authorize('update', $contact);
        $validated = $request->validate(['duplicate_id' => ['required', 'integer']]);
        $duplicate = OfferJourneyContact::query()->whereKey($validated['duplicate_id'])->where('user_id', $request->user()->id)->firstOrFail();
        $merger->merge($contact, $duplicate);

        return redirect()->route('offer-journeys.contacts.show', $contact)->with('success', 'Les fiches ont été fusionnées.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $this->available();
        $validated = $request->validate([
            'contact_ids' => ['required', 'array', 'min:1', 'max:100'],
            'contact_ids.*' => ['integer'],
            'action' => ['required', Rule::in(['move_stage', 'add_tag', 'set_status'])],
            'pipeline_stage_id' => ['nullable', 'integer'],
            'tag_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(['new', 'qualifying', 'contacted', 'converted', 'not_now'])],
            'confirm_count' => ['required', 'integer'],
        ]);
        $contacts = OfferJourneyContact::query()->where('user_id', $request->user()->id)->whereIn('id', $validated['contact_ids'])->get();
        abort_unless($contacts->count() === count(array_unique($validated['contact_ids'])) && $contacts->count() === (int) $validated['confirm_count'], 422);

        DB::transaction(function () use ($validated, $contacts, $request) {
            if ($validated['action'] === 'move_stage') {
                $stage = OfferJourneyPipelineStage::query()->whereKey($validated['pipeline_stage_id'] ?? 0)->where('user_id', $request->user()->id)->firstOrFail();
                $contacts->each->update(['pipeline_stage_id' => $stage->id, 'last_activity_at' => now()]);
            } elseif ($validated['action'] === 'add_tag') {
                $tag = OfferJourneyTag::query()->whereKey($validated['tag_id'] ?? 0)->where('user_id', $request->user()->id)->firstOrFail();
                $contacts->each(fn ($contact) => $contact->tags()->syncWithoutDetaching([$tag->id]));
            } else {
                abort_unless(filled($validated['status'] ?? null), 422);
                $contacts->each->update(['status' => $validated['status'], 'last_activity_at' => now()]);
            }
        });

        $updatedLabel = $contacts->count() === 1
            ? '1 contact a été mis à jour.'
            : $contacts->count().' contacts ont été mis à jour.';

        return back()->with('success', $updatedLabel.' Aucun message n’a été envoyé.');
    }

    public function goal(Request $request): RedirectResponse
    {
        $this->available();
        $validated = $request->validate([
            'journey_id' => ['nullable', Rule::exists('offer_journeys', 'id')->where('user_id', $request->user()->id)],
            'period' => ['required', 'date_format:Y-m'],
            'target_count' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);
        OfferJourneyPipelineGoal::query()->updateOrCreate([
            'user_id' => $request->user()->id,
            'offer_journey_id' => $validated['journey_id'] ?? null,
            'period' => $validated['period'],
        ], ['target_count' => $validated['target_count']]);

        return back()->with('success', 'L’objectif a été enregistré.');
    }

    private function available(): void
    {
        abort_unless(config('offer_journeys.commercial_tools_enabled', false), 404);
    }
}
