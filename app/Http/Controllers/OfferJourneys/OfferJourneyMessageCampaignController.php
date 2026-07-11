<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OfferJourneyMessageCampaignController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(config('offer_journeys.campaigns_enabled', false), 404);
        $campaigns = OfferJourneyMessageCampaign::query()
            ->where('user_id', $request->user()->id)
            ->with('journeys:id,name')
            ->orderByDesc('scheduled_at')
            ->paginate(20);
        $journeys = OfferJourney::query()->ownedBy($request->user())->published()->orderBy('name')->get(['id', 'name']);
        $week = OfferJourneyMessageCampaign::query()->where('user_id', $request->user()->id)
            ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])->orderBy('scheduled_at')->get();

        return view('offer-journeys.practitioner.message-campaigns.index', compact('campaigns', 'journeys', 'week'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(config('offer_journeys.campaigns_enabled', false), 404);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:6000'],
            'scheduled_at' => ['required', 'date', 'after:now', 'before:'.now()->addYear()->toDateTimeString()],
            'journey_ids' => ['required', 'array', 'min:1', 'max:20'],
            'journey_ids.*' => [Rule::exists('offer_journeys', 'id')->where('user_id', $request->user()->id)->where('status', 'published')],
        ]);

        $campaign = OfferJourneyMessageCampaign::query()->create([
            'user_id' => $request->user()->id,
            'created_by_user_id' => $request->user()->id,
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'status' => 'scheduled',
            'scheduled_at' => $validated['scheduled_at'],
        ]);
        $campaign->journeys()->sync($validated['journey_ids']);

        return back()->with('success', 'La campagne est programmée. Les consentements et exclusions seront revérifiés au moment de l’envoi.');
    }

    public function cancel(Request $request, OfferJourneyMessageCampaign $campaign): RedirectResponse
    {
        abort_unless(config('offer_journeys.campaigns_enabled', false), 404);
        abort_unless((int) $campaign->user_id === (int) $request->user()->id, 404);
        abort_unless(in_array($campaign->status, ['draft', 'scheduled'], true), 422);
        $campaign->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return back()->with('success', 'La campagne a été annulée.');
    }
}
