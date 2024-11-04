<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
	 use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    /**
     * Display a listing of the user's events.
     */
// In your EventsController.php
public function index()
{
    $events = Event::with('reservations')
        ->where('user_id', auth()->id())
        ->orderBy('start_date_time', 'asc')
        ->get();

    $currentDateTime = \Carbon\Carbon::now();

    $upcomingEvents = $events->filter(function ($event) use ($currentDateTime) {
        return \Carbon\Carbon::parse($event->start_date_time)->greaterThanOrEqualTo($currentDateTime);
    });

    $pastEvents = $events->filter(function ($event) use ($currentDateTime) {
        return \Carbon\Carbon::parse($event->start_date_time)->lessThan($currentDateTime);
    });

    return view('events.index', compact('upcomingEvents', 'pastEvents'));
}


    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
		$products = Product::where('user_id', auth()->id())->get();
        return view('events.create', compact('products'));
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
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
            'location'           => 'required|string|max:255',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('events', 'public');
        }

        Event::create($data);

        return redirect()->route('events.index')->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        $this->authorize('view', $event);
		$event->load('reservations');
        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        $this->authorize('update', $event);
				$products = Product::where('user_id', auth()->id())->get();

        return view('events.edit', compact('event','products'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $request->validate([
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
            'location'           => 'required|string|max:255',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $data['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($data);

        return redirect()->route('events.index')->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
    }
}
