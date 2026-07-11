<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyCampaignLink;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class OfferJourneyCampaignController extends Controller
{
    use AuthorizesRequests;

    public function show(OfferJourney $journey): View
    {
        $this->authorize('view', $journey);
        $journey->load(['user', 'campaignLinks' => fn ($query) => $query->latest()]);

        return view('offer-journeys.practitioner.share', [
            'journey' => $journey,
            'canonicalUrl' => $this->publicUrl($journey),
        ]);
    }

    public function store(Request $request, OfferJourney $journey): RedirectResponse
    {
        $this->authorize('update', $journey);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'channel' => ['required', Rule::in(['instagram', 'google_business', 'newsletter', 'email', 'facebook', 'qr', 'partner', 'direct'])],
            'utm_campaign' => ['nullable', 'string', 'max:120'],
            'utm_content' => ['nullable', 'string', 'max:120'],
        ]);

        $journey->campaignLinks()->create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'code' => Str::lower(Str::random(12)),
            'channel' => $validated['channel'],
            'utm_source' => $validated['channel'],
            'utm_medium' => $validated['channel'] === 'qr' ? 'offline' : 'social',
            'utm_campaign' => ($validated['utm_campaign'] ?? null) ?: Str::slug($journey->name),
            'utm_content' => $validated['utm_content'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Le lien de campagne a été créé.');
    }

    public function destroy(OfferJourney $journey, OfferJourneyCampaignLink $campaign): RedirectResponse
    {
        $this->authorize('update', $journey);
        abort_unless((int) $campaign->offer_journey_id === (int) $journey->id, 404);
        $campaign->update(['is_active' => false]);

        return back()->with('success', 'Le lien a été désactivé.');
    }

    public function qr(OfferJourney $journey): Response
    {
        $this->authorize('view', $journey);

        return response(
            QrCode::format('svg')->size(640)->margin(2)->generate($this->publicUrl($journey)),
            200,
            ['Content-Type' => 'image/svg+xml', 'Content-Disposition' => 'inline; filename="parcours-'.$journey->slug.'.svg"']
        );
    }

    private function publicUrl(OfferJourney $journey): string
    {
        return route('offer-journeys.public.show', [
            'therapist' => $journey->user,
            'journeySlug' => $journey->slug,
        ]);
    }
}
