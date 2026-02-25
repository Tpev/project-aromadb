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
use App\Models\Unavailability;
use Carbon\Carbon;

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
        'block_calendar'     => 'nullable|boolean',

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

        // ✅ NEW: Payment
        'collect_payment' => 'nullable|boolean',
        'price'           => 'nullable|numeric|min:0',
        'tax_rate'        => 'nullable|numeric|min:0|max:100',
    ]);

    $validated['description'] = $this->sanitizeEventDescription($validated['description'] ?? null);

    $data = $validated;
    $data['user_id'] = Auth::id();

    /*
    |--------------------------------------------------------------------------
    | Payment handling (NEW)
    |--------------------------------------------------------------------------
    */
    $data['collect_payment'] = $request->boolean('collect_payment');
	// ✅ tax_rate must never be null (DB constraint)
	$data['tax_rate'] = isset($data['tax_rate']) && $data['tax_rate'] !== ''
		? (float) $data['tax_rate']
		: 0;
    if ($data['collect_payment']) {
        // must have booking
        if (!$request->boolean('booking_required')) {
            return back()
                ->withErrors(['collect_payment' => 'Le paiement nécessite d’activer les réservations.'])
                ->withInput();
        }

        // price must be > 0
        if (empty($data['price']) || (float) $data['price'] <= 0) {
            return back()
                ->withErrors(['price' => 'Veuillez renseigner un prix TTC (> 0) pour un événement payant.'])
                ->withInput();
        }

        // must have Stripe Connect
        $user = Auth::user();
        if (empty($user->stripe_account_id)) {
            return back()
                ->withErrors(['collect_payment' => 'Pour activer le paiement, veuillez d’abord connecter votre compte Stripe (Stripe Connect).'])
                ->withInput();
        }
    } else {
        // keep DB clean
        $data['price'] = null;
        $data['tax_rate'] = 0;
    }

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

    if ($request->boolean('block_calendar')) {
        $start = Carbon::parse($data['start_date_time']);
        $end   = (clone $start)->addMinutes((int) $data['duration']);

        Unavailability::create([
            'user_id'    => Auth::id(),
            'start_date' => $start,
            'end_date'   => $end,
            'reason'     => "Événement : " . $data['name'],
        ]);
    }

    return redirect()
        ->route('events.index')
        ->with('success', 'Événement créé avec succès.');
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

        // ✅ NEW: Payment
        'collect_payment' => 'nullable|boolean',
        'price'           => 'nullable|numeric|min:0',
        'tax_rate'        => 'nullable|numeric|min:0|max:100',
    ]);

    $validated['description'] = $this->sanitizeEventDescription($validated['description'] ?? null);

    $data = $validated;

    /*
    |--------------------------------------------------------------------------
    | Payment handling (NEW)
    |--------------------------------------------------------------------------
    */
    $data['collect_payment'] = $request->boolean('collect_payment');
	// ✅ tax_rate must never be null (DB constraint)
	$data['tax_rate'] = isset($data['tax_rate']) && $data['tax_rate'] !== ''
		? (float) $data['tax_rate']
		: 0;
    if ($data['collect_payment']) {
        if (!$request->boolean('booking_required')) {
            return back()
                ->withErrors(['collect_payment' => 'Le paiement nécessite d’activer les réservations.'])
                ->withInput();
        }

        if (empty($data['price']) || (float) $data['price'] <= 0) {
            return back()
                ->withErrors(['price' => 'Veuillez renseigner un prix TTC (> 0) pour un événement payant.'])
                ->withInput();
        }

        $user = Auth::user();
        if (empty($user->stripe_account_id)) {
            return back()
                ->withErrors(['collect_payment' => 'Pour activer le paiement, veuillez d’abord connecter votre compte Stripe (Stripe Connect).'])
                ->withInput();
        }
    } else {
        $data['price'] = null;
        $data['tax_rate'] = 0;
    }

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
$validated['description'] = $this->sanitizeEventDescription($validated['description'] ?? null);

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

/**
 * Accepts either plain text or Quill HTML and returns a safe string.
 * - Plain text: returned as-is
 * - HTML: strips scripts/iframes + unsafe attributes, keeps basic formatting
 */
private function sanitizeEventDescription(?string $value): ?string
{
    if ($value === null) return null;

    $value = trim($value);
    if ($value === '') return null;

    // Treat empty Quill content as null
    $normalized = preg_replace('/\s+/', '', strtolower($value));
    if ($normalized === '<p><br></p>' || $normalized === '<div><br></div>') {
        return null;
    }

    // If no tags detected -> plain text
    $looksHtml = preg_match('/<\/?[a-z][\s\S]*>/i', $value) === 1;
    if (!$looksHtml) {
        return $value;
    }

    // Remove dangerous blocks
    $value = preg_replace('#<(script|style|iframe|object|embed|form)[^>]*>.*?</\1>#is', '', $value);
    $value = preg_replace('#<(script|style|iframe|object|embed|form)[^>]*/?>#is', '', $value);

    // Allow only basic tags used by Quill
    $allowed = '<p><br><strong><b><em><i><u><s><blockquote><h1><h2><h3><h4><ul><ol><li><a><span>';

    $clean = strip_tags($value, $allowed);

    // Remove dangerous attributes (on* handlers, style)
    $clean = preg_replace('/\son\w+="[^"]*"/i', '', $clean);
    $clean = preg_replace("/\son\w+='[^']*'/i", '', $clean);
    $clean = preg_replace('/\sstyle="[^"]*"/i', '', $clean);
    $clean = preg_replace("/\sstyle='[^']*'/i", '', $clean);

    // Only allow safe href protocols
    $clean = preg_replace_callback('/<a\s+[^>]*href=("|\')([^"\']+)\1[^>]*>/i', function ($m) {
        $quote = $m[1];
        $href  = trim($m[2]);

        if (!preg_match('#^(https?://|mailto:|tel:)#i', $href)) {
            // remove href entirely if unsafe
            return preg_replace('/\shref=("|\')[^"\']+\1/i', '', $m[0]);
        }

        // enforce rel/target for safety
        $tag = $m[0];
        if (!preg_match('/\brel=/i', $tag)) {
            $tag = rtrim($tag, '>') . ' rel="noopener noreferrer">';
        }
        if (!preg_match('/\btarget=/i', $tag)) {
            $tag = rtrim($tag, '>') . ' target="_blank">';
        }
        return $tag;
    }, $clean);

    // If it becomes empty, null it
    if (trim(strip_tags($clean)) === '') return null;

    return $clean;
}
public function show(Event $event)
{
    // Same authorization level as edit/update in your controller
    $this->authorize('update', $event);

    // The show blade expects reservations + related data
    $event->load([
        'user',
        'associatedProduct',
        'reservations' => function ($q) {
            $q->orderBy('created_at', 'desc');
        },
    ]);

    return view('events.show', compact('event'));
}

}
