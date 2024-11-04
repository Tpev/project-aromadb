<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmation;
use App\Mail\NewReservationNotification;



class ReservationController extends Controller
{
    /**
     * Store a new reservation.
     */
public function store(Request $request, $eventId)
{
    // Retrieve the event along with its user (therapist)
    $event = Event::with('user')->findOrFail($eventId);

    // Validate the request
    $request->validate([
        'full_name' => 'required|string|max:255',
        'email'     => 'required|email|max:255',
        'phone'     => 'nullable|string|max:20',
    ]);

    // Check if the event requires booking
    if (!$event->booking_required) {
        return redirect()->back()->with('error', __('Cet événement n\'accepte pas les réservations.'));
    }

    // Check if the event has limited spots
    if ($event->limited_spot) {
        $currentReservations = $event->reservations()->count();
        if ($currentReservations >= $event->number_of_spot) {
            return redirect()->back()->with('error', __('Cet événement est complet.'));
        }
    }

    // Create the reservation and assign it to $reservation
    $reservation = Reservation::create([
        'event_id'  => $event->id,
        'full_name' => $request->full_name,
        'email'     => $request->email,
        'phone'     => $request->phone,
    ]);

    // Send confirmation email to client
    Mail::to($reservation->email)->queue(new ReservationConfirmation($reservation));

    // Send notification email to therapist
    Mail::to($event->user->email)->queue(new NewReservationNotification($reservation));

    // Redirect to the success page
    return redirect()->route('reservations.success', $event->id);
}

	    public function create($eventId)
    {
        $event = Event::findOrFail($eventId);

        // Check if the event requires booking
        if (!$event->booking_required) {
            return redirect()->route('events.show', $event->id)->with('error', __('Cet événement n\'accepte pas les réservations.'));
        }

        // Check if the event has spots available
        if ($event->limited_spot) {
            $currentReservations = $event->reservations()->count();
            if ($currentReservations >= $event->number_of_spot) {
                return redirect()->route('events.show', $event->id)->with('error', __('Cet événement est complet.'));
            }
        }

        return view('reservations.create', compact('event'));
    }
	public function success($eventId)
{
    $event = Event::with('user')->findOrFail($eventId);
    return view('reservations.success', compact('event'));
}

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

}
