<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyPipelineStage;
use App\Domain\OfferJourneys\Services\OfferJourneyPipeline;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Domain\OfferJourneys\Models\OfferJourneyPipelineGoal;
use App\Domain\OfferJourneys\Models\OfferJourney;

class OfferJourneyPipelineController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, OfferJourneyPipeline $pipeline): View
    {
        $this->authorize('viewAny', OfferJourneyContact::class);
        $pipeline->ensureDefaults($request->user());

        $stages = OfferJourneyPipelineStage::query()
            ->where('user_id', $request->user()->id)
            ->withCount('contacts')
            ->with(['contacts' => fn ($query) => $query->orderByDesc('last_activity_at')->limit(50)])
            ->orderBy('position')
            ->get();

        $commercialToolsEnabled = (bool) config('offer_journeys.commercial_tools_enabled', false);
        $period = now()->format('Y-m');
        $goals = $commercialToolsEnabled
            ? OfferJourneyPipelineGoal::query()->where('user_id', $request->user()->id)->where('period', $period)->get()
            : collect();
        $journeys = $commercialToolsEnabled
            ? OfferJourney::query()->ownedBy($request->user())->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('offer-journeys.practitioner.contacts.pipeline', compact('stages', 'commercialToolsEnabled', 'period', 'goals', 'journeys'));
    }

    public function move(Request $request, OfferJourneyContact $contact): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $contact);
        $validated = $request->validate([
            'pipeline_stage_id' => ['required', 'integer'],
            'reason' => ['nullable', 'string', 'max:255'],
            'next_action_at' => ['nullable', 'date', 'after_or_equal:today'],
        ]);
        $stage = OfferJourneyPipelineStage::query()
            ->whereKey($validated['pipeline_stage_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($stage->system_key === 'not_now' && blank($validated['reason'] ?? null)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Indiquez un motif de report.'], 422)
                : back()->withErrors(['reason' => 'Indiquez un motif de report.']);
        }

        $contact->update([
            'pipeline_stage_id' => $stage->id,
            'pipeline_outcome_reason' => $validated['reason'] ?? null,
            'next_action_at' => $validated['next_action_at'] ?? null,
            'last_activity_at' => now(),
        ]);
        $contact->activities()->create([
            'type' => 'pipeline_moved',
            'title' => 'Déplacé vers « '.$stage->name.' »',
            'metadata' => array_filter(['reason' => $validated['reason'] ?? null, 'next_action_at' => $validated['next_action_at'] ?? null]),
            'occurred_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Le contact a été déplacé.', 'stage' => $stage->name]);
        }

        return back()->with('success', 'Le contact a été déplacé.');
    }
}
