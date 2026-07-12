<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourneyTag;
use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Support\OfferJourneys\OfferJourneyAccess;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OfferJourneyClientTagController extends Controller
{
    use AuthorizesRequests;

    public function attach(Request $request, ClientProfile $clientProfile): RedirectResponse
    {
        $this->ensureEnabled();
        $this->authorize('update', $clientProfile);

        $validated = $request->validate([
            'tag_id' => [
                'required',
                'integer',
                Rule::exists('offer_journey_tags', 'id')->where('user_id', $request->user()->id),
            ],
        ]);

        $clientProfile->marketingTags()->syncWithoutDetaching([(int) $validated['tag_id']]);

        return back()->with('success', 'L’étiquette a été ajoutée à la fiche client. Elle ne vaut pas consentement marketing.');
    }

    public function detach(Request $request, ClientProfile $clientProfile, OfferJourneyTag $tag): RedirectResponse
    {
        $this->ensureEnabled();
        $this->authorize('update', $clientProfile);
        abort_unless((int) $tag->user_id === (int) $request->user()->id, 404);

        $clientProfile->marketingTags()->detach($tag->id);

        return back()->with('success', 'L’étiquette a été retirée de la fiche client.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $this->ensureEnabled();
        $this->authorize('viewAny', ClientProfile::class);

        $validated = $request->validate([
            'client_ids' => ['required', 'array', 'min:1', 'max:250'],
            'client_ids.*' => [
                'integer',
                Rule::exists('client_profiles', 'id')->where('user_id', $request->user()->id),
            ],
            'tag_id' => [
                'required',
                'integer',
                Rule::exists('offer_journey_tags', 'id')->where('user_id', $request->user()->id),
            ],
            'action' => ['required', Rule::in(['attach', 'detach'])],
        ]);

        $clients = ClientProfile::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($validated['client_ids'])
            ->get();

        DB::transaction(function () use ($clients, $validated) {
            foreach ($clients as $client) {
                if ($validated['action'] === 'attach') {
                    $client->marketingTags()->syncWithoutDetaching([(int) $validated['tag_id']]);
                } else {
                    $client->marketingTags()->detach((int) $validated['tag_id']);
                }
            }
        });

        return back()->with('success', sprintf(
            'L’étiquette a été %s pour %d fiche(s) client.',
            $validated['action'] === 'attach' ? 'ajoutée' : 'retirée',
            $clients->count()
        ));
    }

    private function ensureEnabled(): void
    {
        abort_unless(
            config('offer_journeys.client_tags_enabled', false)
                && app(OfferJourneyAccess::class)->canPublish(request()->user()),
            404
        );
    }
}
