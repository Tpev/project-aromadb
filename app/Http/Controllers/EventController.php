<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Models\ClientProfile;
use App\Models\Reservation;

use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmation;
use App\Mail\NewReservationNotification;

class EventController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function index()
    {
        $events = Event::with('reservations')
            ->where('user_id', auth()->id())
            ->orderBy('start_date_time', 'asc')
            ->get();

        $now = now();

        $upcomingEvents = $events->filter(fn ($e) => $e->start_date_time >= $now);
        $pastEvents     = $events->filter(fn ($e) => $e->start_date_time < $now);

        return view('events.index', compact('upcomingEvents', 'pastEvents'));
    }

    public function create()
    {
        $products = Product::where('user_id', auth()->id())->get();
        return view('events.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'start_date_time'    => 'required|date',
            'duration'           => 'required|integer',

            'booking_required'   => 'required|boolean',
            'limited_spot'       => 'required|boolean',
            'number_of_spot'     => 'nullable|integer',

            'associated_product' => 'nullable|exists:products,id',
            'image'              => 'nullable|image',
            'showOnPortail'      => 'required|boolean',

            'location'           => 'nullable|string|max:255',

            // Visio
            'event_type'     => 'required|in:in_person,visio',
            'visio_provider' => 'nullable|in:external,aromamade',
            'visio_url'      => 'nullable|url|max:2000',
        ]);

        $data = $validated;
        $data['user_id'] = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | Format handling
        |--------------------------------------------------------------------------
        */
        if ($data['event_type'] === 'in_person') {
            // Présentiel → cleanup visio fields
            if (empty($data['location'])) {
                return back()
                    ->withErrors(['location' => 'Le lieu est obligatoire pour un événement en présentiel.'])
                    ->withInput();
            }

            $data['visio_provider'] = null;
            $data['visio_url']      = null;
            $data['visio_token']    = null;

        } else {
            // Visio
            $provider = $data['visio_provider'] ?? 'external';

            if ($provider === 'external') {
                if (empty($data['visio_url'])) {
                    return back()
                        ->withErrors(['visio_url' => 'Le lien visio est obligatoire si vous utilisez un lien externe.'])
                        ->withInput();
                }

                $data['visio_token'] = null;

            } else {
                // AromaMade WebRTC room
                $data['visio_url'] = null;

                do {
                    $token = Str::random(32); // SAME AS MeetingController
                } while (Event::where('visio_token', $token)->exists());

                $data['visio_token'] = $token;
            }

            // Display fallback
            $data['location'] = $data['location'] ?: 'En ligne (Visio)';
        }

        /*
        |--------------------------------------------------------------------------
        | Image
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('events', 'public');
        }

        Event::create($data);

        return redirect()
            ->route('events.index')
            ->with('success', 'Événement créé avec succès.');
    }

    public function show(Event $event)
    {
        $this->authorize('view', $event);
        $event->load('reservations');
        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);
        $products = Product::where('user_id', auth()->id())->get();
        return view('events.edit', compact('event', 'products'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'start_date_time'    => 'required|date',
            'duration'           => 'required|integer',

            'booking_required'   => 'required|boolean',
            'limited_spot'       => 'required|boolean',
            'number_of_spot'     => 'nullable|integer',

            'associated_product' => 'nullable|exists:products,id',
            'image'              => 'nullable|image',
            'showOnPortail'      => 'required|boolean',

            'location'           => 'nullable|string|max:255',

            'event_type'     => 'required|in:in_person,visio',
            'visio_provider' => 'nullable|in:external,aromamade',
            'visio_url'      => 'nullable|url|max:2000',
        ]);

        $data = $validated;

        /*
        |--------------------------------------------------------------------------
        | Format handling
        |--------------------------------------------------------------------------
        */
        if ($data['event_type'] === 'in_person') {
            if (empty($data['location'])) {
                return back()
                    ->withErrors(['location' => 'Le lieu est obligatoire pour un événement en présentiel.'])
                    ->withInput();
            }

            $data['visio_provider'] = null;
            $data['visio_url']      = null;
            $data['visio_token']    = null;

        } else {
            $provider = $data['visio_provider'] ?? 'external';

            if ($provider === 'external') {
                if (empty($data['visio_url'])) {
                    return back()
                        ->withErrors(['visio_url' => 'Le lien visio est obligatoire si vous utilisez un lien externe.'])
                        ->withInput();
                }

                $data['visio_token'] = null;

            } else {
                $data['visio_url'] = null;

                // Preserve existing room if already created
                if (empty($event->visio_token)) {
                    do {
                        $token = Str::random(32);
                    } while (Event::where('visio_token', $token)->exists());

                    $data['visio_token'] = $token;
                } else {
                    $data['visio_token'] = $event->visio_token;
                }
            }

            $data['location'] = $data['location'] ?: 'En ligne (Visio)';
        }

        /*
        |--------------------------------------------------------------------------
        | Image update
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }

            $data['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($data);

        return redirect()
            ->route('events.index')
            ->with('success', 'Événement mis à jour avec succès.');
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('success', 'Événement supprimé avec succès.');
    }

    public function addReservationFromClient(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'client_profile_id' => 'required|integer|exists:client_profiles,id',
        ]);

        $client = ClientProfile::where('id', $validated['client_profile_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $email = trim(strtolower($client->email ?? ''));
        if (!$email) {
            return back()->with('error', "Ce client n'a pas d'email. Ajoutez un email au dossier client avant de l'ajouter à l'événement.");
        }

        // ✅ Doublon : même email déjà inscrit sur cet event
        $already = Reservation::where('event_id', $event->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists();

        if ($already) {
            return back()->with('error', "Ce client est déjà inscrit à cet événement.");
        }

        // ✅ Limite de places : on compte toutes les réservations
        if ($event->limited_spot && (int) $event->number_of_spot > 0) {
            $count = Reservation::where('event_id', $event->id)->count();

            if ($count >= (int) $event->number_of_spot) {
                return back()->with('error', "Il n'y a plus de place disponible pour cet événement.");
            }
        }

        $fullName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));

        $reservation = Reservation::create([
            'event_id'  => $event->id,
            'full_name' => $fullName ?: ($client->email ?? 'Participant'),
            'email'     => $client->email,
            'phone'     => $client->phone,
        ]);

        // ✅ Emails (identique au flux public ReservationController@store)
        $event->loadMissing('user');

        Mail::to($reservation->email)->queue(new ReservationConfirmation($reservation));
        if ($event->user?->email) {
            Mail::to($event->user->email)->queue(new NewReservationNotification($reservation));
        }

        return back()->with('success', "Participant ajouté à l'événement. Emails envoyés.");
    }

public function duplicate(Event $event)
{
    $this->authorize('update', $event);

    $products = Product::where('user_id', auth()->id())->get();

    // optional if you want to display count somewhere
    $event->loadCount('reservations');

    return view('events.duplicate', compact('event', 'products'));
}

public function storeDuplicate(Request $request, Event $event)
{
    $this->authorize('update', $event);

    $validated = $request->validate([
        'name'               => 'required|string|max:255',
        'description'        => 'nullable|string',
        'start_date_time'    => 'required|date',
        'duration'           => 'required|integer',

        'booking_required'   => 'required|boolean',
        'limited_spot'       => 'required|boolean',
        'number_of_spot'     => 'nullable|integer',

        'associated_product' => 'nullable|exists:products,id',
        'image'              => 'nullable|image',
        'showOnPortail'      => 'required|boolean',

        'location'           => 'nullable|string|max:255',

        // Visio
        'event_type'     => 'required|in:in_person,visio',
        'visio_provider' => 'nullable|in:external,aromamade',
        'visio_url'      => 'nullable|url|max:2000',

        // duplication options
        'duplicate_participants' => 'nullable|boolean',
        'send_confirmation_to_copied_participants' => 'nullable|boolean',
    ]);

    $data = $validated;
    $data['user_id'] = Auth::id();

    $duplicateParticipants = (bool) ($data['duplicate_participants'] ?? false);
    $sendConfirmation      = (bool) ($data['send_confirmation_to_copied_participants'] ?? false);

    unset($data['duplicate_participants'], $data['send_confirmation_to_copied_participants']);

    /*
    |--------------------------------------------------------------------------
    | Format handling (same as store)
    |--------------------------------------------------------------------------
    */
    if ($data['event_type'] === 'in_person') {
        if (empty($data['location'])) {
            return back()
                ->withErrors(['location' => 'Le lieu est obligatoire pour un événement en présentiel.'])
                ->withInput();
        }

        $data['visio_provider'] = null;
        $data['visio_url']      = null;
        $data['visio_token']    = null;

    } else {
        $provider = $data['visio_provider'] ?? 'external';

        if ($provider === 'external') {
            if (empty($data['visio_url'])) {
                return back()
                    ->withErrors(['visio_url' => 'Le lien visio est obligatoire si vous utilisez un lien externe.'])
                    ->withInput();
            }

            $data['visio_token'] = null;

        } else {
            $data['visio_url'] = null;

            // generate a new token for the duplicated event
            do {
                $token = Str::random(32);
            } while (Event::where('visio_token', $token)->exists());

            $data['visio_token'] = $token;
        }

        $data['location'] = $data['location'] ?: 'En ligne (Visio)';
    }

    /*
    |--------------------------------------------------------------------------
    | Image: if not uploaded, keep original image path
    |--------------------------------------------------------------------------
    */
    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('events', 'public');
    } else {
        $data['image'] = $event->image;
    }

    // Create new event
    $newEvent = Event::create($data);

    /*
    |--------------------------------------------------------------------------
    | Duplicate reservations only upon validation
    |--------------------------------------------------------------------------
    */
    if ($duplicateParticipants) {
        $event->loadMissing('reservations');

        foreach ($event->reservations as $r) {

            $newReservation = Reservation::create([
                'event_id'  => $newEvent->id,
                'full_name' => $r->full_name,
                'email'     => $r->email,
                'phone'     => $r->phone,
            ]);

            // Optional: send confirmation email to copied participants
            if ($sendConfirmation && !empty($newReservation->email)) {
                Mail::to($newReservation->email)->queue(new ReservationConfirmation($newReservation));
            }
        }
    }

    return redirect()
        ->route('events.show', $newEvent->id)
        ->with('success', $duplicateParticipants
            ? ($sendConfirmation
                ? "Événement dupliqué avec succès (participants copiés + emails envoyés)."
                : "Événement dupliqué avec succès (participants copiés).")
            : "Événement dupliqué avec succès.");
}



}
