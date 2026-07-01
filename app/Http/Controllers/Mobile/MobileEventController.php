<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Mail\NewReservationNotification;
use App\Mail\ReservationConfirmation;
use App\Models\ClientProfile;
use App\Models\Event;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\Unavailability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MobileEventController extends Controller
{
    public function index()
    {
        $events = Event::query()
            ->withCount('reservations')
            ->where('user_id', Auth::id())
            ->orderBy('start_date_time')
            ->get();

        $now = now();

        return view('mobile.events.index', [
            'events' => $events,
            'upcomingEvents' => $events->filter(fn (Event $event) => Carbon::parse($event->start_date_time)->gte($now)),
            'pastEvents' => $events->filter(fn (Event $event) => Carbon::parse($event->start_date_time)->lt($now)),
            'canCreateEvent' => $this->canUseEvents(),
        ]);
    }

    public function create()
    {
        abort_unless($this->canUseEvents(), 403);

        return view('mobile.events.form', [
            'event' => new Event([
                'event_type' => 'in_person',
                'visio_provider' => 'external',
                'booking_required' => true,
                'limited_spot' => false,
                'showOnPortail' => true,
                'duration' => 60,
                'tax_rate' => 0,
            ]),
            'products' => $this->productsForUser(),
            'title' => 'Nouvel evenement',
            'action' => route('mobile.events.store'),
            'method' => 'POST',
            'submitLabel' => 'Creer',
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->canUseEvents(), 403);

        $data = $this->eventPayload($request);
        $blockCalendar = $request->boolean('block_calendar');
        $data['user_id'] = Auth::id();

        $event = Event::create($data);

        if ($blockCalendar) {
            $start = Carbon::parse($event->start_date_time);

            Unavailability::create([
                'user_id' => Auth::id(),
                'start_date' => $start,
                'end_date' => (clone $start)->addMinutes((int) $event->duration),
                'reason' => 'Evenement : ' . $event->name,
            ]);
        }

        return redirect()
            ->route('mobile.events.show', $event)
            ->with('success', 'Evenement cree.');
    }

    public function show(Event $event)
    {
        $this->authorizeOwner($event);

        $event->load([
            'associatedProduct',
            'reservations' => fn ($query) => $query->latest(),
        ])->loadCount('reservations');

        $clients = ClientProfile::query()
            ->where('user_id', Auth::id())
            ->whereNotNull('email')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('mobile.events.show', compact('event', 'clients'));
    }

    public function edit(Event $event)
    {
        $this->authorizeOwner($event);

        return view('mobile.events.form', [
            'event' => $event,
            'products' => $this->productsForUser(),
            'title' => 'Modifier l evenement',
            'action' => route('mobile.events.update', $event),
            'method' => 'PUT',
            'submitLabel' => 'Enregistrer',
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeOwner($event);

        $event->update($this->eventPayload($request, $event));

        return redirect()
            ->route('mobile.events.show', $event)
            ->with('success', 'Evenement mis a jour.');
    }

    public function destroy(Event $event)
    {
        $this->authorizeOwner($event);

        $event->delete();

        return redirect()
            ->route('mobile.events.index')
            ->with('success', 'Evenement supprime.');
    }

    public function addClient(Request $request, Event $event)
    {
        $this->authorizeOwner($event);

        $validated = $request->validate([
            'client_profile_id' => ['required', 'integer', 'exists:client_profiles,id'],
        ]);

        $client = ClientProfile::query()
            ->where('id', $validated['client_profile_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $email = strtolower(trim((string) $client->email));
        if ($email === '') {
            return back()->with('error', 'Ce client n a pas d email.');
        }

        $alreadyRegistered = Reservation::query()
            ->where('event_id', $event->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists();

        if ($alreadyRegistered) {
            return back()->with('error', 'Ce client est deja inscrit.');
        }

        if ($event->limited_spot && (int) $event->number_of_spot > 0) {
            $reservationCount = Reservation::query()
                ->where('event_id', $event->id)
                ->count();

            if ($reservationCount >= (int) $event->number_of_spot) {
                return back()->with('error', 'Il n y a plus de place disponible.');
            }
        }

        $fullName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
        $reservation = Reservation::create([
            'event_id' => $event->id,
            'full_name' => $fullName ?: $email,
            'email' => $client->email,
            'phone' => $client->phone,
        ]);

        $event->loadMissing('user');

        Mail::to($reservation->email)->queue(new ReservationConfirmation($reservation));
        if ($event->user?->email) {
            Mail::to($event->user->email)->queue(new NewReservationNotification($reservation));
        }

        return back()->with('success', 'Participant ajoute.');
    }

    protected function eventPayload(Request $request, ?Event $event = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date_time' => ['required', 'date'],
            'duration' => ['required', 'integer', 'min:1'],
            'booking_required' => ['required', 'boolean'],
            'limited_spot' => ['required', 'boolean'],
            'number_of_spot' => ['nullable', 'integer', 'min:1'],
            'associated_product' => ['nullable', 'integer', 'exists:products,id'],
            'showOnPortail' => ['required', 'boolean'],
            'location' => ['nullable', 'string', 'max:255'],
            'event_type' => ['required', 'string', 'in:in_person,visio'],
            'visio_provider' => ['nullable', 'string', 'in:external,aromamade'],
            'visio_url' => ['nullable', 'url', 'max:2000'],
            'collect_payment' => ['nullable', 'boolean'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        if (! empty($validated['associated_product'])) {
            $ownsProduct = Product::query()
                ->where('id', $validated['associated_product'])
                ->where('user_id', Auth::id())
                ->exists();

            if (! $ownsProduct) {
                throw ValidationException::withMessages([
                    'associated_product' => 'Cette prestation ne vous appartient pas.',
                ]);
            }
        }

        $data = $validated;
        $data['description'] = $this->sanitizeDescription($data['description'] ?? null);
        $data['booking_required'] = $request->boolean('booking_required');
        $data['limited_spot'] = $request->boolean('limited_spot');
        $data['showOnPortail'] = $request->boolean('showOnPortail');
        $data['collect_payment'] = $request->boolean('collect_payment');
        $data['tax_rate'] = isset($data['tax_rate']) && $data['tax_rate'] !== '' ? (float) $data['tax_rate'] : 0;

        if (! $data['limited_spot']) {
            $data['number_of_spot'] = null;
        } elseif (empty($data['number_of_spot'])) {
            throw ValidationException::withMessages([
                'number_of_spot' => 'Indiquez le nombre de places.',
            ]);
        }

        if ($data['collect_payment']) {
            if (! $data['booking_required']) {
                throw ValidationException::withMessages([
                    'collect_payment' => 'Le paiement necessite les reservations.',
                ]);
            }

            if (empty($data['price']) || (float) $data['price'] <= 0) {
                throw ValidationException::withMessages([
                    'price' => 'Indiquez un prix TTC superieur a 0.',
                ]);
            }

            if (empty(Auth::user()->stripe_account_id)) {
                throw ValidationException::withMessages([
                    'collect_payment' => 'Connectez Stripe avant d activer le paiement.',
                ]);
            }
        } else {
            $data['price'] = null;
            $data['tax_rate'] = 0;
        }

        if ($data['event_type'] === 'in_person') {
            if (empty($data['location'])) {
                throw ValidationException::withMessages([
                    'location' => 'Le lieu est obligatoire pour un evenement en presentiel.',
                ]);
            }

            $data['visio_provider'] = null;
            $data['visio_url'] = null;
            $data['visio_token'] = null;
        } else {
            $provider = $data['visio_provider'] ?? 'external';
            $data['visio_provider'] = $provider;

            if ($provider === 'external') {
                if (empty($data['visio_url'])) {
                    throw ValidationException::withMessages([
                        'visio_url' => 'Le lien visio est obligatoire.',
                    ]);
                }

                $data['visio_token'] = null;
            } else {
                $data['visio_url'] = null;
                $data['visio_token'] = $event?->visio_token ?: $this->newVisioToken();
            }

            $data['location'] = $data['location'] ?: 'En ligne (Visio)';
        }

        return $data;
    }

    protected function newVisioToken(): string
    {
        do {
            $token = Str::random(32);
        } while (Event::where('visio_token', $token)->exists());

        return $token;
    }

    protected function sanitizeDescription(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $normalized = preg_replace('/\s+/', '', strtolower($value));
        if ($normalized === '<p><br></p>' || $normalized === '<div><br></div>') {
            return null;
        }

        $looksHtml = preg_match('/<\/?[a-z][\s\S]*>/i', $value) === 1;
        if (! $looksHtml) {
            return $value;
        }

        $value = preg_replace('#<(script|style|iframe|object|embed|form)[^>]*>.*?</\1>#is', '', $value);
        $value = preg_replace('#<(script|style|iframe|object|embed|form)[^>]*/?>#is', '', $value);
        $clean = strip_tags($value, '<p><br><strong><b><em><i><u><s><blockquote><h1><h2><h3><h4><ul><ol><li><a><span>');
        $clean = preg_replace('/\son\w+="[^"]*"/i', '', $clean);
        $clean = preg_replace("/\son\w+='[^']*'/i", '', $clean);
        $clean = preg_replace('/\sstyle="[^"]*"/i', '', $clean);
        $clean = preg_replace("/\sstyle='[^']*'/i", '', $clean);

        return trim(strip_tags($clean)) === '' ? null : $clean;
    }

    protected function productsForUser()
    {
        return Product::query()
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
    }

    protected function authorizeOwner(Event $event): void
    {
        abort_unless((int) $event->user_id === (int) Auth::id(), 403);
    }

    protected function canUseEvents(): bool
    {
        return (bool) Auth::user()?->canUseFeature('events');
    }
}
