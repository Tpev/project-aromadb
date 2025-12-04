<?php

namespace App\Http\Controllers;

use App\Models\NewsletterRecipient;
use App\Models\NewsletterOptOut;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NewsletterUnsubscribeController extends Controller
{
    /**
     * Affiche la page de confirmation de désabonnement.
     */
    public function show(string $token)
    {
        $recipient = NewsletterRecipient::where('unsubscribe_token', $token)->first();

        if (!$recipient || !$recipient->newsletter) {
            return view('newsletters.unsubscribe_invalid');
        }

        $newsletter = $recipient->newsletter;
        $therapist  = $newsletter->user; // supposé : relation user() sur Newsletter

        // Si déjà désabonné, on affiche un message "déjà fait"
        if ($recipient->unsubscribed_at) {
            return view('newsletters.unsubscribe_already', [
                'recipient' => $recipient,
                'newsletter' => $newsletter,
                'therapist'  => $therapist,
            ]);
        }

        return view('newsletters.unsubscribe_confirm', [
            'recipient' => $recipient,
            'newsletter' => $newsletter,
            'therapist'  => $therapist,
        ]);
    }

    /**
     * Enregistre le désabonnement et affiche un message de confirmation.
     */
    public function confirm(Request $request, string $token)
    {
        $recipient = NewsletterRecipient::where('unsubscribe_token', $token)->first();

        if (!$recipient || !$recipient->newsletter) {
            return view('newsletters.unsubscribe_invalid');
        }

        $newsletter = $recipient->newsletter;
        $therapist  = $newsletter->user;

        $data = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        // Marque le recipient comme désabonné
        $recipient->status          = 'unsubscribed';
        $recipient->unsubscribed_at = Carbon::now();
        $recipient->save();

        // Crée / met à jour un opt-out global par thérapeute + email
        NewsletterOptOut::updateOrCreate(
            [
                'user_id' => $therapist->id,
                'email'   => $recipient->email,
            ],
            [
                'newsletter_recipient_id' => $recipient->id,
                'reason'                  => $data['reason'] ?? null,
                'unsubscribed_at'         => Carbon::now(),
            ]
        );

        return view('newsletters.unsubscribe_done', [
            'recipient' => $recipient,
            'newsletter' => $newsletter,
            'therapist'  => $therapist,
        ]);
    }
}
