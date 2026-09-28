<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmation;
use App\Mail\NewReservationNotification;
use App\Support\EventSocialImage;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use App\Services\EventReservationService;
use App\Jobs\ExpireEventReservationCheckout;
use Illuminate\Support\Facades\URL;

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
            ->active()
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
        $reservation = app(EventReservationService::class)->reserve($event, [
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

                'cancel_url' => URL::signedRoute('reservations.payment_cancel', ['reservation_id' => $reservation->id]),

                'metadata' => ['reservation_id' => (string) $reservation->id, 'event_id' => (string) $event->id],

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

            if ($reservation->fresh()->cancelled_at) {
                ExpireEventReservationCheckout::dispatch($reservation->id);
                return redirect()->route('events.reserve.create', $event)
                    ->with('error', 'Cette réservation a été annulée.');
            }

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
    $reservation = app(EventReservationService::class)->reserve($event, [
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
public function create($eventId, EventSocialImage $eventSocialImage)
{
    $event = Event::with(['reservations', 'user'])->findOrFail($eventId);
    $canReserve = true;
    $reservationStatusMessage = null;

    // Check if the event requires booking
    if (!$event->booking_required) {
        return redirect()->route('events.public.show', $event);
    }

    // Check if the event has spots available
    if ($canReserve && $event->limited_spot) {

        // ✅ Must match store(): count only active-ish reservations
        $currentReservations = $event->reservations()
            ->active()
            ->count();

        if ($currentReservations >= (int) $event->number_of_spot) {
            $canReserve = false;
            $reservationStatusMessage = __('Cet événement est complet.');
        }
    }

    return view('reservations.create', [
        'event' => $event,
        'canReserve' => $canReserve,
        'reservationStatusMessage' => $reservationStatusMessage,
        'socialImage' => $eventSocialImage->for($event),
    ]);
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
     * Cancel attendance while retaining payment and reservation history.
     */
    public function destroy(Request $request, $id, EventReservationService $reservations)
    {
        $reservation = Reservation::findOrFail($id);

        $reservation = $reservations->cancel($reservation, $request->user());
        $message = 'Réservation annulée. La place est à nouveau disponible.';
        if ($reservation->status === 'paid') {
            $message .= ' Le paiement est conservé ; aucun remboursement automatique n’a été effectué.';
        }

        return redirect()->back()->with('success', $message);
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

        $confirmed = app(\App\Services\EventPaymentService::class)->confirm(
            $reservation->id,
            (string) $account_id,
            (int) ($session->amount_total ?? 0),
            (string) ($session->currency ?? ''),
            (string) $paymentIntent->id,
            (string) $session->id,
        );
        if (!$confirmed) {
            return redirect()->route('welcome')->with('error', 'Le paiement ne correspond pas à cette réservation. Contactez le praticien.');
        }

        if ($reservation->fresh()->cancelled_at) {
            return redirect()->route('events.reserve.create', $reservation->event)
                ->with('error', 'Votre paiement a été reçu, mais cette réservation a été annulée. Contactez le praticien pour le remboursement.');
        }

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
    // Old unsigned return URLs may still be open in a browser, but must not mutate a reservation.
    if (! $request->hasValidSignature()) {
        return redirect()->route('welcome')->with('error', 'Paiement interrompu.');
    }
    $reservationId = $request->get('reservation_id');

    if ($reservationId) {
        \Illuminate\Support\Facades\DB::transaction(function () use ($reservationId) {
            $reservation = Reservation::lockForUpdate()->find($reservationId);
            if ($reservation && $reservation->status === 'pending_payment') {
                $reservation->status = 'canceled';
                $reservation->save();
            }
        });
    }

    return redirect()->route('welcome')->with('error', "Paiement annulé.");
}
}
