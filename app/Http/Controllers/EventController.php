<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
}
