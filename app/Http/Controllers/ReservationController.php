<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmation;
use App\Mail\NewReservationNotification;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class ReservationController extends Controller
{
    /**
     * Store a new reservation.
     */
public function store(Request $request, $eventId)
{
    // Retrieve the event along with its user (therapist)
    $event = Event::with(['user', 'reservations'])->findOrFail($eventId);

    // Simple honeypot check (bots often fill hidden fields)
    if ($request->filled('website')) {
        return back()
            ->with('error', __('Votre soumission n’a pas été acceptée.'))
            ->withInput();
    }

    // Validate the request (includes NoCaptcha)
    $request->validate([
        'full_name'             => 'required|string|max:255',
        'email'                 => 'required|email|max:255',
        'phone'                 => 'nullable|string|max:20',
        'g-recaptcha-response'  => 'required|captcha',
    ], [
        'g-recaptcha-response.required' => __('Veuillez confirmer que vous n’êtes pas un robot.'),
        'g-recaptcha-response.captcha'  => __('La vérification reCAPTCHA a échoué, veuillez réessayer.'),
    ]);

    // Check if the event requires booking
    if (!$event->booking_required) {
        return redirect()->back()->with('error', __('Cet événement n\'accepte pas les réservations.'));
    }

    // Check if the event has limited spots (count only active-ish reservations)
    if ($event->limited_spot) {
        $currentReservations = $event->reservations()
            ->whereIn('status', ['confirmed', 'pending_payment', 'paid'])
            ->count();

        if ($currentReservations >= (int) $event->number_of_spot) {
            return redirect()->back()->with('error', __('Cet événement est complet.'));
        }
    }

    // ✅ PAID EVENT FLOW
    if (!empty($event->collect_payment)) {

        // Safety checks
        if (empty($event->user->stripe_account_id)) {
            return redirect()->back()->with('error', __("Le thérapeute n'a pas configuré Stripe pour encaisser en ligne."));
        }

        $base = (float) ($event->price ?? 0);
        if ($base <= 0) {
            return redirect()->back()->with('error', __("Cet événement est indiqué comme payant mais aucun prix valide n'a été défini."));
        }

        $taxRate = (float) ($event->tax_rate ?? 0);

        // IMPORTANT: If your event->price is already final TTC, set $total = $base.
        // Here we follow your existing logic: add tax_rate on top.
        $total = $base;
        if ($taxRate > 0) {
            $total = $total + ($total * $taxRate / 100);
        }

        // Create a pending reservation BEFORE redirecting to Stripe
        $reservation = Reservation::create([
            'event_id'   => $event->id,
            'full_name'  => $request->full_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'status'     => 'pending_payment',
            'amount_ttc' => $total,   // ✅ store the exact charged amount
            'currency'   => 'eur',
        ]);

        $stripe = new StripeClient(config('services.stripe.secret'));

        try {
            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'mode' => 'payment',

                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $event->name,
                        ],
                        'unit_amount' => (int) round($total * 100),
                    ],
                    'quantity' => 1,
                ]],

                'success_url' => route('reservations.payment_success')
                    . '?session_id={CHECKOUT_SESSION_ID}'
                    . '&account_id=' . $event->user->stripe_account_id,

                'cancel_url' => route('reservations.payment_cancel')
                    . '?reservation_id=' . $reservation->id,

                // Metadata is on PaymentIntent (easy to retrieve on success)
                'payment_intent_data' => [
                    'metadata' => [
                        'reservation_id' => $reservation->id,
                        'event_id'       => $event->id,
                        'email'          => $reservation->email,
                    ],
                ],
            ], [
                'stripe_account' => $event->user->stripe_account_id,
            ]);

            $reservation->stripe_session_id = $session->id;
            $reservation->save();

            return redirect($session->url);

        } catch (\Exception $e) {
            Log::error('Stripe Checkout creation failed (event reservation): '.$e->getMessage(), [
                'event_id' => $event->id,
                'reservation_id' => $reservation->id,
            ]);

            $reservation->status = 'canceled';
            $reservation->save();

            return redirect()->back()
                ->with('error', __("Erreur lors de la création de la session de paiement. Veuillez réessayer."));
        }
    }

    // ✅ FREE EVENT FLOW
    $reservation = Reservation::create([
        'event_id'  => $event->id,
        'full_name' => $request->full_name,
        'email'     => $request->email,
        'phone'     => $request->phone,
        'status'    => 'confirmed',
    ]);

    // Send confirmation email to client
    Mail::to($reservation->email)->queue(new ReservationConfirmation($reservation));

    // Send notification email to therapist
    Mail::to($event->user->email)->queue(new NewReservationNotification($reservation));

    // Redirect to the success page
    return redirect()->route('reservations.success', $event->id);
}

    /**
     * Show the reservation form.
     */
public function create($eventId)
{
    $event = Event::with(['reservations', 'user'])->findOrFail($eventId);

    // Check if the event requires booking
    if (!$event->booking_required) {
        return redirect()
            ->route('events.show', $event->id)
            ->with('error', __('Cet événement n\'accepte pas les réservations.'));
    }

    // Check if the event has spots available
    if ($event->limited_spot) {

        // ✅ Must match store(): count only active-ish reservations
        $currentReservations = $event->reservations()
            ->whereIn('status', ['confirmed', 'pending_payment', 'paid'])
            ->count();

        if ($currentReservations >= (int) $event->number_of_spot) {
            return redirect()
                ->route('events.show', $event->id)
                ->with('error', __('Cet événement est complet.'));
        }
    }

    return view('reservations.create', compact('event'));
}

    /**
     * Success page after reservation creation.
     */
    public function success($eventId)
    {
        $event = Event::with('user')->findOrFail($eventId);
        return view('reservations.success', compact('event'));
    }

    /**
     * Delete a reservation (only event owner).
     */
    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);

        // Check if the authenticated user is the owner of the event
        if (auth()->id() !== $reservation->event->user_id) {
            return redirect()->back()->with('error', __('Vous n\'êtes pas autorisé à supprimer cette réservation.'));
        }

        $reservation->delete();

        return redirect()->back()->with('success', __('La réservation a été supprimée avec succès.'));
    }
public function paymentSuccess(Request $request)
{
    $session_id = $request->get('session_id');
    $account_id = $request->get('account_id');

    if (!$session_id || !$account_id) {
        return redirect()->route('welcome')->with('error', "Paramètres Stripe manquants.");
    }

    $stripe = new StripeClient(config('services.stripe.secret'));

    try {
        // Retrieve session
        $session = $stripe->checkout->sessions->retrieve($session_id, [], [
            'stripe_account' => $account_id,
        ]);

        // ✅ Ensure session is paid (Stripe can redirect even if not fully paid in some flows)
        // For mode=payment, typical success means paid, but still better to check.
        if (!isset($session->payment_status) || $session->payment_status !== 'paid') {
            return redirect()->route('welcome')->with('error', "Le paiement n'a pas été confirmé.");
        }

        // Retrieve payment intent
        $paymentIntent = $stripe->paymentIntents->retrieve($session->payment_intent, [], [
            'stripe_account' => $account_id,
        ]);

        $reservationId = $paymentIntent->metadata['reservation_id'] ?? null;

        if (!$reservationId) {
            return redirect()->route('welcome')->with('error', "Réservation introuvable (metadata manquante).");
        }

        $reservation = Reservation::with(['event.user'])->find($reservationId);

        if (!$reservation) {
            return redirect()->route('welcome')->with('error', "Réservation introuvable.");
        }

        // Idempotent
        if ($reservation->status === 'paid') {
            return redirect()->route('reservations.success', $reservation->event->id);
        }

        // ✅ Mark paid
        $reservation->status = 'paid';
        $reservation->stripe_payment_intent_id = $paymentIntent->id;

        // Optional safety: ensure amount matches expectation (only if amount_ttc is set)
        // $paidAmount = (int) ($session->amount_total ?? 0); // cents
        // if ($reservation->amount_ttc !== null && $paidAmount > 0) {
        //     $expected = (int) round(((float)$reservation->amount_ttc) * 100);
        //     if ($paidAmount !== $expected) {
        //         Log::warning('Reservation amount mismatch', [
        //             'reservation_id' => $reservation->id,
        //             'expected' => $expected,
        //             'paid' => $paidAmount,
        //         ]);
        //     }
        // }

        $reservation->save();

        // Emails AFTER payment
        Mail::to($reservation->email)->queue(new ReservationConfirmation($reservation));
        Mail::to($reservation->event->user->email)->queue(new NewReservationNotification($reservation));

        return redirect()->route('reservations.success', $reservation->event->id);

    } catch (\Exception $e) {
        Log::error('Stripe payment success handler failed (event reservation): '.$e->getMessage(), [
            'session_id' => $session_id,
            'account_id' => $account_id,
        ]);

        return redirect()->route('welcome')->with('error', "Erreur de validation du paiement.");
    }
}

public function paymentCancel(Request $request)
{
    $reservationId = $request->get('reservation_id');

    if ($reservationId) {
        $reservation = Reservation::find($reservationId);
        if ($reservation && $reservation->status === 'pending_payment') {
            $reservation->status = 'canceled';
            $reservation->save();
        }
    }

    return redirect()->route('welcome')->with('error', "Paiement annulé.");
}
}
