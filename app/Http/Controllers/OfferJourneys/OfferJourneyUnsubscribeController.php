<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneySuppression;
use App\Http\Controllers\Controller;
use App\Models\NewsletterOptOut;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class OfferJourneyUnsubscribeController extends Controller
{
    public function show(OfferJourneyContact $contact): View
    {
        return view('offer-journeys.public.unsubscribe', [
            'contact' => $contact,
            'confirmUrl' => URL::temporarySignedRoute(
                'offer-journeys.unsubscribe.confirm',
                now()->addDays(30),
                ['contact' => $contact]
            ),
        ]);
    }

    public function confirm(Request $request, OfferJourneyContact $contact): RedirectResponse
    {
        DB::transaction(function () use ($contact) {
            OfferJourneySuppression::query()->updateOrCreate([
                'user_id' => $contact->user_id,
                'email_normalized' => $contact->email_normalized,
                'type' => 'unsubscribe',
            ], [
                'offer_journey_contact_id' => $contact->id,
                'reason' => 'Désinscription depuis un email de parcours',
                'source' => 'offer_journey_unsubscribe',
                'suppressed_at' => now(),
            ]);

            $contact->consents()
                ->where('purpose', 'marketing_follow_up')
                ->where('status', 'granted')
                ->update(['status' => 'withdrawn', 'withdrawn_at' => now()]);

            NewsletterOptOut::query()->updateOrCreate([
                'user_id' => $contact->user_id,
                'email' => $contact->email_normalized,
            ], [
                'reason' => 'Désinscription depuis un parcours d’offre',
                'unsubscribed_at' => now(),
            ]);
        });

        return redirect()->to(URL::temporarySignedRoute(
            'offer-journeys.unsubscribe.show',
            now()->addMinutes(10),
            ['contact' => $contact]
        ))->with('success', 'Votre désinscription a été prise en compte.');
    }
}
