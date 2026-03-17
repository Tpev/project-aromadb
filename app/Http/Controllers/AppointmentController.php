<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;   
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Meeting;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\User;
use App\Models\Availability;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentCreatedPatientMail;
use App\Mail\AppointmentCreatedTherapistMail;
use App\Mail\AppointmentEditedClientMail;
use App\Models\Unavailability;
use Stripe\StripeClient;
use App\Notifications\AppointmentBooked;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\SpecialAvailability;
use App\Models\PackPurchase;
use App\Mail\AppointmentCancelledByClient;
use App\Services\JitsiJwtService;
use App\Models\BookingLink;
use App\Models\GiftVoucher;
use App\Models\Receipt;
use App\Services\GiftVoucherRedeemService;

class AppointmentController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    /**
     * Display a listing of the appointments.
     */
    /**
     * Display a listing of the appointments.
     */
   public function index(Request $request)
{
    // 1. Redirige les comptes inactifs
    if (Auth::user()->license_status === 'inactive') {
        return redirect('/license-tiers/pricing');
    }

    // 2. Charge tous les rendez-vous du thérapeute
    $allAppointments = Appointment::where('user_id', Auth::id())
        ->with(['clientProfile', 'product'])
        ->orderBy('appointment_date', 'asc') // tri global par date croissante
        ->get();

    $events = [];

    // Date/heure actuelle
    $now = Carbon::now();

    /* -------------------------------------------------------------------------
     | Construction du tableau $events pour FullCalendar
     | ---------------------------------------------------------------------- */
    foreach ($allAppointments as $appointment) {

        $isPast = Carbon::parse($appointment->appointment_date)->isPast();

        // ---------- Titre ----------
        if ($appointment->external) {
            $title = $appointment->notes ?: 'Occupé';
        } else {
            $client = optional($appointment->clientProfile);
            $title  = trim(($client->first_name ?? '').' '.($client->last_name ?? '')) ?: 'Rendez-vous';
        }

        // ---------- Couleur ----------
        $color = $appointment->external
            ? '#999999'
            : ($isPast ? '#854f38' : '#647a0b');

        // ---------- Push dans FullCalendar ----------
        $events[] = [
            'title'     => $title,
            'start'     => $appointment->appointment_date->format('Y-m-d H:i:s'),
            'end'       => $appointment->appointment_date
                                        ->copy()
                                        ->addMinutes($appointment->duration ?? 0)
                                        ->format('Y-m-d H:i:s'),
            'url'       => $appointment->external
                                ? null
                                : route('appointments.show', $appointment->id),
            'color'     => $color,
            'textColor' => $isPast ? '#ffffff' : '#636363',
        ];
    }

    /* -------------------------------------------------------------------------
     | Séparation : rendez-vous à venir / rendez-vous passés
     | ---------------------------------------------------------------------- */

    // RDV à venir : date >= maintenant, triés du plus proche au plus lointain
    $rendezVousAVenir = $allAppointments
        ->filter(fn ($a) => $a->appointment_date >= $now)
        ->sortBy('appointment_date')
        ->values();

    // RDV passés : date < maintenant, triés du plus récent au plus ancien
    $rendezVousPasses = $allAppointments
        ->filter(fn ($a) => $a->appointment_date < $now)
        ->sortByDesc('appointment_date')
        ->values();

    // Pour compatibilité si tu utilisais déjà $appointments dans la vue
    $appointments = $allAppointments;

    // 4. Indisponibilités
    $unavailabilities = Unavailability::where('user_id', Auth::id())
        ->get()
        ->map(function ($unavailability) {
            return [
                'title' => $unavailability->reason ?: 'Indisponible',
                'start' => $unavailability->start_date->format('Y-m-d H:i:s'),
                'end'   => $unavailability->end_date->format('Y-m-d H:i:s'),
                'color' => '#808080',
                'url'   => route('unavailabilities.index'),
            ];
        });

    $events = array_merge($events, $unavailabilities->toArray());

    // 🔥 Choix de la vue en fonction de la route (web vs mobile)
    $view = $request->routeIs('mobile.*')
        ? 'mobile.appointments.index'
        : 'appointments.index';

    return view($view, [
        'appointments'        => $appointments,
        'rendezVousAVenir'    => $rendezVousAVenir,
        'rendezVousPasses'    => $rendezVousPasses,
        'events'              => $events,
    ]);
}




    /**
     * Show the form for creating a new appointment.
     */
    public function create()
    {
        $clientProfiles = ClientProfile::where('user_id', Auth::id())->get();
        $products = Product::where('user_id', Auth::id())->get();

        return view('appointments.create', compact('clientProfiles', 'products'));
    }

public function store(Request $request)
{
    // Base validation
    $rules = [
        'client_profile_id' => 'required',
        'appointment_date'  => 'required|date',
        'appointment_time'  => 'required|date_format:H:i',
        'status'            => 'required|string',
        'notes'             => 'nullable|string',
        'product_id'        => 'required|exists:products,id',

        // ✅ new (optional) - only used for therapist override, ignored otherwise
        'force_availability_override' => 'nullable|boolean',
    ];

    // Si création d'un nouveau client
    if ($request->client_profile_id == 'new') {
        $rules = array_merge($rules, [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'nullable|email|max:255|unique:client_profiles,email',
            'phone'      => 'nullable|string|max:15',
            'birthdate'  => 'nullable|date',
            'address'    => 'nullable|string|max:255',
        ]);
    } else {
        $rules['client_profile_id'] .= '|exists:client_profiles,id';
    }

    $validated = $request->validate($rules);

    $therapistId = Auth::id();
    $therapist   = User::findOrFail($therapistId);

    // Produit & durée
    $product  = Product::findOrFail($request->product_id);
    $duration = (int) $product->duration;

    /**
     * ✅ Mode
     * Your form sends "type" (cabinet/visio/domicile/entreprise).
     * Some older flows might send "mode".
     * We support both WITHOUT breaking anything.
     */
    $uiMode = $request->input('mode');
    if (empty($uiMode)) {
        $uiMode = $request->input('type'); // <-- your blade uses name="type"
    }

    // Mode (respecte l'UI si fournie, sinon déduction)
    $mode = $this->resolveMode($product, $uiMode);

    // Validation conditionnelle du lieu si cabinet
    $locationId = null;
    if ($mode === 'cabinet') {
        // Si l'UI n'envoie pas explicitement le lieu et que le thérapeute n'en a qu'un, auto-sélection (optionnel)
        if (!$request->filled('practice_location_id')) {
            $onlyLoc = $therapist->practiceLocations()->first();
            if ($onlyLoc && $therapist->practiceLocations()->count() === 1) {
                $request->merge(['practice_location_id' => $onlyLoc->id]);
            }
        }

        $request->validate([
            'practice_location_id' => ['required','integer','exists:practice_locations,id'],
        ]);

        $locationId = (int) $request->practice_location_id;

        // Sécurité multi-tenant : le lieu doit appartenir au thérapeute
        $ownsLocation = \App\Models\PracticeLocation::where('id', $locationId)
            ->where('user_id', $therapistId)
            ->exists();

        if (!$ownsLocation) {
            return back()->withErrors(['practice_location_id' => 'Ce cabinet n’appartient pas à votre compte.'])->withInput();
        }
    }

    // Création/assoc client
    if ($request->client_profile_id == 'new') {
        $clientProfile = ClientProfile::create([
            'user_id'    => $therapistId,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'birthdate'  => $request->birthdate,
            'address'    => $request->address,
        ]);
        $clientProfileId = $clientProfile->id;
    } else {
        $clientProfileId = $request->client_profile_id;
        $clientProfile   = ClientProfile::findOrFail($clientProfileId);

        // Sécurité multi-tenant : le client doit appartenir au thérapeute
        if ((int) $clientProfile->user_id !== (int) $therapistId) {
            return back()->withErrors(['client_profile_id' => 'Ce client n’appartient pas à votre compte.'])->withInput();
        }
    }

    // Datetime
    $appointmentDateTime = Carbon::createFromFormat(
        'Y-m-d H:i',
        $request->appointment_date.' '.$request->appointment_time
    );

    // Backfill mode: allow therapists to register past appointments without notifications
    $isBackfillRequested = $request->boolean('backfill_past');
    $isPastAppointment   = $appointmentDateTime->lt(now());

    // Safety: backfill flag is only allowed for past dates
    if ($isBackfillRequested && !$isPastAppointment) {
        return redirect()->back()
            ->withErrors(['appointment_date' => 'Le mode « rendez-vous passé » ne peut être utilisé que pour une date passée.'])
            ->withInput();
    }

    $skipAvailability   = $isPastAppointment || $isBackfillRequested;
    $skipNotifications  = $isPastAppointment || $isBackfillRequested;

    /**
     * ✅ Therapist override: allow bypass for future conflicts/outside dispo
     * This does NOT affect existing flows unless the form sends force_availability_override=1
     */
    $forceOverride = $request->boolean('force_availability_override');
    if ($forceOverride && (Auth::user()?->is_therapist ?? true)) {
        // if you don't have is_therapist column reliably here, keep it permissive since this route is therapist-auth.
        $skipAvailability = true;
    }

    // Vérification disponibilité (passe mode + location)
    if (
        !$skipAvailability &&
        !$this->isAvailable($appointmentDateTime, $duration, $therapistId, $product->id, null, $locationId, $mode)
    ) {
        return redirect()->back()
            ->withErrors(['appointment_time' => 'Le créneau horaire est déjà réservé ou en dehors des disponibilités.'])
            ->withInput();
    }

    // Création du rendez-vous (avec practice_location_id si cabinet)
    $appointment = Appointment::create([
        'client_profile_id'     => $clientProfileId,
        'user_id'               => $therapistId,
        'appointment_date'      => $appointmentDateTime,
        'status'                => $request->status,
        'notes'                 => $request->notes,
        'product_id'            => $request->product_id,
        'duration'              => $duration,
        'practice_location_id'  => $mode === 'cabinet' ? $locationId : null,
        'type'                  => $mode,
    ]);

    /* ============================================================
       ✅ PACK AUTO-CONSUMPTION (création par le thérapeute)
       ============================================================ */
    try {
        $now = Carbon::now();

        $packPurchase = \App\Models\PackPurchase::query()
            ->where('user_id', $therapistId)
            ->where('client_profile_id', $clientProfileId)
            ->where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', $now);
            })
            ->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->id)
                  ->where('quantity_remaining', '>', 0);
            })
            ->orderByRaw('ISNULL(expires_at) ASC')
            ->orderBy('expires_at', 'asc')
            ->orderBy('purchased_at', 'asc')
            ->first();

        if ($packPurchase) {
            $packPurchase->consumeProduct($product->id, 1);
        }
    } catch (\Exception $e) {
        Log::warning('Pack auto-consumption (therapist store) skipped due to error: ' . $e->getMessage());
    }

    // Meeting visio si applicable
    if ($product->visio) {
        $token = Str::random(32);
        $meeting = Meeting::create([
            'name'              => 'Réunion pour '.$clientProfile->first_name.' '.$clientProfile->last_name,
            'start_time'        => $appointmentDateTime,
            'duration'          => $duration,
            'participant_email' => $clientProfile->email,
            'client_profile_id' => $clientProfileId,
            'room_token'        => $token,
            'appointment_id'    => $appointment->id,
        ]);
        $appointment->meeting_link = route('webrtc.room', ['room' => $token]).'#1';
    }

    // Charger relations pour emails
    $appointment->load('clientProfile','user','product');

    // Envoi emails (skip if backfill/past)
    if (!$skipNotifications) {
        $therapistEmail = $appointment->user->email;
        $patientEmail   = $appointment->clientProfile->email;
        if (!empty($patientEmail))  { Mail::to($patientEmail)->queue(new AppointmentCreatedPatientMail($appointment)); }
        if (!empty($therapistEmail)){ Mail::to($therapistEmail)->queue(new AppointmentCreatedTherapistMail($appointment)); }
    }

    return redirect()->route('appointments.index')->with('success', 'Rendez-vous créé avec succès.');
}




public function show(Appointment $appointment, JitsiJwtService $jitsi)
{
    $this->authorize('view', $appointment);

    // Determine the mode based on the linked product
    $mode = 'Non spécifié';
    if ($appointment->product) {
        if ($appointment->product->visio) {
            $mode = 'En visio';
        } elseif ($appointment->product->adomicile) {
            $mode = 'À domicile';
        } elseif ($appointment->product->dans_le_cabinet) {
            $mode = 'Au cabinet';
        }
    }

    $meetingLink = null;
    $meetingLinkPatient = null;

    if ($appointment->meeting) {
        $room = $appointment->meeting->room_token;

        // IMPORTANT: Jitsi domain (your hosted meet)
        $jitsiBase = rtrim(config('services.jitsi.base_url', 'https://visio.aromamade.com'), '/');

        // Therapist JWT (moderator)
        $therapistJwt = $jitsi->makeJwtForTherapist([
            'room' => $room,
            'appointment' => $appointment,
        ]);

        // Client JWT (non-moderator)
        $clientJwt = $jitsi->makeJwtForClient([
            'room' => $room,
            'appointment' => $appointment,
        ]);

        // Build final URLs
        $meetingLink = "{$jitsiBase}/{$room}?jwt={$therapistJwt}";
        $meetingLinkPatient = "{$jitsiBase}/{$room}?jwt={$clientJwt}";
    }

    // If request comes from /mobile/... → use mobile view
    if (request()->routeIs('mobile.appointments.*') || request()->is('mobile/*')) {
        return view('mobile.appointments.show1', compact(
            'appointment',
            'mode',
            'meetingLink',
            'meetingLinkPatient'
        ));
    }

    return view('appointments.show', compact(
        'appointment',
        'mode',
        'meetingLink',
        'meetingLinkPatient'
    ));
}



    /**
     * Show the form for editing the specified appointment.
     */
    public function edit(Appointment $appointment)
    {
        // Ensure the appointment belongs to the authenticated user
        $this->authorize('update', $appointment);

        // Get client profiles for the logged-in therapist
        $clientProfiles = ClientProfile::where('user_id', Auth::id())->get();

        // Get available products
        $products = Product::where('user_id', Auth::id())->get();

        // Get the duration from the appointment
        $duration = (int) $appointment->duration;

        // Combine appointment date and time
        $appointmentDateTime = Carbon::parse($appointment->appointment_date);
        $therapistId = Auth::id();

        // Get available slots for the current appointment date and product linkage
        $date = $appointmentDateTime->format('Y-m-d');
        $productId = $appointment->product_id;
        $availableSlots = $this->getAvailableSlotsForEdit($date, $duration, $therapistId, $productId, $appointment->id);

        return view('appointments.edit', compact('appointment', 'clientProfiles', 'products', 'availableSlots'));
    }

    /**
     * Update the specified appointment in storage.
     */
 public function update(Request $request, Appointment $appointment)
{
    // Autorisation
    $this->authorize('update', $appointment);

    // Validation de base
    $request->validate([
        'client_profile_id' => 'required|exists:client_profiles,id',
        'appointment_date'  => 'required|date',
        'appointment_time'  => 'required|date_format:H:i',
        'status'            => 'required|string',
        'notes'             => 'nullable|string',
        'product_id'        => 'required|exists:products,id',
        'force_availability_override' => 'nullable|boolean',
        // 'mode' facultatif (UI)
        // 'practice_location_id' validé plus bas si nécessaire
    ]);

    $therapistId = Auth::id();
    $therapist   = User::findOrFail($therapistId);

    // Produit & durée
    $product  = Product::findOrFail($request->product_id);
    $duration = (int) $product->duration;

    // Mode (UI prioritaire): support both "mode" and "type" like create flow
    $uiMode = $request->input('mode');
    if (empty($uiMode)) {
        $uiMode = $request->input('type');
    }
    $mode = $this->resolveMode($product, $uiMode);

    // Validation conditionnelle du lieu si cabinet
    $locationId = null;
    if ($mode === 'cabinet') {
        // Si pas de lieu envoyé et un seul lieu existe, auto-sélection (optionnel)
        if (!$request->filled('practice_location_id')) {
            $onlyLoc = $therapist->practiceLocations()->first();
            if ($onlyLoc && $therapist->practiceLocations()->count() === 1) {
                $request->merge(['practice_location_id' => $onlyLoc->id]);
            }
        }

        $request->validate([
            'practice_location_id' => ['required','integer','exists:practice_locations,id'],
        ]);

        $locationId = (int) $request->practice_location_id;

        // Le lieu doit appartenir au thérapeute
        $ownsLocation = \App\Models\PracticeLocation::where('id', $locationId)
            ->where('user_id', $therapistId)
            ->exists();

        if (!$ownsLocation) {
            return back()->withErrors(['practice_location_id' => 'Ce cabinet n’appartient pas à votre compte.'])->withInput();
        }
    }

    // DateTime
    $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->appointment_date.' '.$request->appointment_time);

    // Therapist can intentionally bypass availability checks (same behavior as create flow).
    $forceOverride = $request->boolean('force_availability_override');

    // Vérification disponibilité (exclure le RDV en cours d’édition, passer mode + location)
    if (
        !$forceOverride &&
        !$this->isAvailable($appointmentDateTime, $duration, $therapistId, $product->id, $appointment->id, $locationId, $mode)
    ) {
        return redirect()->back()
            ->withErrors(['appointment_time' => 'Le créneau horaire est déjà réservé ou en dehors des disponibilités.'])
            ->withInput();
    }

    // Mise à jour du rendez-vous (avec practice_location_id si cabinet)
    $appointment->update([
        'client_profile_id'     => $request->client_profile_id,
        'user_id'               => $therapistId,
        'appointment_date'      => $appointmentDateTime,
        'status'                => $request->status,
        'notes'                 => $request->notes,
        'product_id'            => $request->product_id,
        'duration'              => $duration,
        'practice_location_id'  => $mode === 'cabinet' ? $locationId : null,
    ]);

    // Email de mise à jour au client si email présent
    if ($appointment->clientProfile && $appointment->clientProfile->email) {
        Mail::to($appointment->clientProfile->email)->queue(new AppointmentEditedClientMail($appointment));
    }

    return redirect()->route('appointments.index')->with('success', 'Rendez-vous mis à jour avec succès.');
}


    /**
     * Fetch available time slots based on date and product linkage.
     */
    public function getAvailableSlots(Request $request)
    {
        // Log incoming request for debugging
        \Log::info('Fetching available slots for date: ' . $request->date . ', product_id: ' . $request->product_id);

        // Validate request parameters
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'product_id' => 'required|exists:products,id',
        ]);

        // Get the selected product and its duration
        $product = Product::findOrFail($request->product_id);

        if (!$product->duration) {
            \Log::error('Product duration is missing for product: ' . $product->id);
            return response()->json(['error' => 'La durée du produit est manquante.'], 422);
        }

        $duration = $product->duration;
        $therapistId = Auth::id(); // Ensure we're checking availability for the logged-in therapist

        // Fetch available slots for the date, duration, and product linkage
        $slots = $this->getAvailableSlotsForEdit($request->date, $duration, $therapistId, $product->id);

        // Return the available slots as JSON
        return response()->json(['slots' => $slots]);
    }

    /**
     * Helper method to determine if a slot is available.
     *
     * @param Carbon $appointmentDateTime
     * @param int $duration
     * @param int $therapistId
     * @param int $productId
     * @param int|null $excludeAppointmentId
     * @return bool
     */
private function isAvailable(
    $appointmentDateTime,
    $duration,
    $therapistId,
    $productId,
    $excludeAppointmentId = null,
    $locationId = null,
    $mode = null
) {
    // Normalise
    $duration = (int) $duration;
    $start    = Carbon::parse($appointmentDateTime);
    $end      = $start->copy()->addMinutes($duration);

    // Buffer time between appointments (in minutes)
    $therapist      = User::findOrFail((int) $therapistId);
    $bufferMinutes  = (int) ($therapist->buffer_time_between_appointments ?? 0);
    $bufferedStart  = $bufferMinutes > 0 ? $start->copy()->subMinutes($bufferMinutes) : $start;
    $bufferedEnd    = $bufferMinutes > 0 ? $end->copy()->addMinutes($bufferMinutes)   : $end;

    // 1) Récup produit & mode
    $product = Product::findOrFail((int) $productId);

    if (!in_array($mode, ['cabinet','visio','domicile','entreprise'], true)) {
        // Déduction simple si le mode n'est pas fourni
        $modes = [];
        if ($product->dans_le_cabinet) $modes[] = 'cabinet';
        if ($product->visio)           $modes[] = 'visio';
        if ($product->adomicile)       $modes[] = 'domicile';
    if (!empty($product->en_entreprise)) $modes[] = 'entreprise';
        if (!empty($product->en_entreprise)) $modes[] = 'entreprise';
        $mode = count($modes) === 1 ? $modes[0] : 'cabinet';
    }

    // Cabinet: si multi-lieux, on attend un locationId (mais on reste permissif si vide)
    if ($mode === 'cabinet' && empty($locationId)) {
        // Si tu veux être strict, tu peux faire `return false;` ici
    }

    // 2) Jour de semaine (0..6)
    $dayOfWeek0 = $start->dayOfWeekIso - 1;

    // 3a) Weekly
    $weeklyQuery = Availability::where('user_id', (int) $therapistId)
        ->where('day_of_week', $dayOfWeek0)
        ->where(function ($query) use ($productId) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function ($q) use ($productId) {
                      $q->where('products.id', $productId);
                  });
        });

    if ($mode === 'cabinet' && $locationId) {
        $weeklyQuery->where('practice_location_id', (int) $locationId);
    }

    $weeklyAvailabilities = $weeklyQuery->get();

    // 3b) Specials
    $dateStr = $start->toDateString();

    $specialQuery = SpecialAvailability::where('user_id', (int) $therapistId)
        ->whereDate('date', $dateStr)
        ->where(function ($query) use ($productId) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function ($q) use ($productId) {
                      $q->where('products.id', $productId);
                  });
        });

    if ($mode === 'cabinet' && $locationId) {
        $specialQuery->where('practice_location_id', (int) $locationId);
    }

    $specialAvailabilities = $specialQuery->get();

    // 3c) Merge
    $availabilities = $weeklyAvailabilities->concat($specialAvailabilities);

    if ($availabilities->isEmpty()) {
        return false;
    }

    // 4) Vérifier que le créneau tombe DANS au moins une disponibilité
    $insideAvailability = false;
    foreach ($availabilities as $a) {
        $aStart = Carbon::createFromFormat('H:i:s', $a->start_time)
            ->setDate($start->year, $start->month, $start->day);
        $aEnd = Carbon::createFromFormat('H:i:s', $a->end_time)
            ->setDate($start->year, $start->month, $start->day);

        if ($start->gte($aStart) && $end->lte($aEnd)) {
            $insideAvailability = true;
            break;
        }
    }
    if (!$insideAvailability) {
        return false;
    }

    // 5) Conflits avec d'autres rendez-vous (global par thérapeute, avec buffer)
	$conflictingAppointments = Appointment::where('user_id', (int) $therapistId);

	// ✅ ignore external all-day multi-day events
	$conflictingAppointments = $this->applyBlockingAppointmentsFilter($conflictingAppointments);

	// ✅ overlap check (use COALESCE for safety)
	$conflictingAppointments->where(function ($q) use ($bufferedStart, $bufferedEnd) {
		$q->where('appointment_date', '<', $bufferedEnd)
		  ->whereRaw("DATE_ADD(appointment_date, INTERVAL COALESCE(duration,60) MINUTE) > ?", [$bufferedStart]);
	});


    if ($excludeAppointmentId) {
        $conflictingAppointments->where('id', '!=', $excludeAppointmentId);
    }

    if ($conflictingAppointments->exists()) {
        return false;
    }

    // 6) Conflits avec indisponibilités (multi-day) – pas de buffer ici
    $hasUnavailability = Unavailability::where('user_id', (int) $therapistId)
        ->where(function ($q) use ($start, $end) {
            $q->where('start_date', '<', $end)
              ->where('end_date',   '>', $start);
        })
        ->exists();

    if ($hasUnavailability) {
        return false;
    }

    return true;
}

private function countDailyAppointmentsForLimit(int $therapistId, Carbon $date, ?int $productId = null): int
{
    $query = Appointment::query()
        ->where('user_id', $therapistId)
        ->whereDate('appointment_date', $date->toDateString())
        ->where(function ($q) {
            $q->whereNull('external')
              ->orWhere('external', false);
        });

    $query = $this->applyBlockingAppointmentsFilter($query);

    if ($productId !== null) {
        $query->where('product_id', $productId);
    }

    return $query->count();
}

private function getDailyBookingLimitError(User $therapist, Product $product, Carbon $appointmentDateTime): ?string
{
    $dateLabel = $appointmentDateTime->format('d/m/Y');

    $productLimit = (int) ($product->max_per_day ?? 0);
    if ($productLimit > 0) {
        $productCount = $this->countDailyAppointmentsForLimit((int) $therapist->id, $appointmentDateTime, (int) $product->id);
        if ($productCount >= $productLimit) {
            return "Le nombre maximum de rendez-vous pour cette prestation est atteint le {$dateLabel}.";
        }
    }

    $globalLimit = (int) ($therapist->global_daily_booking_limit ?? 0);
    if ($globalLimit > 0) {
        $globalCount = $this->countDailyAppointmentsForLimit((int) $therapist->id, $appointmentDateTime);
        if ($globalCount >= $globalLimit) {
            return "Le nombre maximum global de rendez-vous est atteint le {$dateLabel}.";
        }
    }

    return null;
}


  

    /**
     * Helper method to fetch available slots for editing and creation.
     *
     * @param string $date
     * @param int $duration
     * @param int $therapistId
     * @param int $productId
     * @param int|null $excludeAppointmentId
     * @return array
     */
private function getAvailableSlotsForEdit($date, $duration, $therapistId, $productId, $excludeAppointmentId = null)
{
    // 1) Setup & helpers
    $product    = Product::findOrFail((int) $productId);
    $carbonDate = Carbon::createFromFormat('Y-m-d', $date);
    $dayOfWeek0 = $carbonDate->dayOfWeekIso - 1; // Monday=0..Sunday=6

    $therapist     = User::findOrFail((int) $therapistId);
    $bufferMinutes = (int) ($therapist->buffer_time_between_appointments ?? 0);

    // Déduire les modes possibles
    $modes = [];
    if ($product->dans_le_cabinet) $modes[] = 'cabinet';
    if ($product->visio)           $modes[] = 'visio';
    if ($product->adomicile)       $modes[] = 'domicile';
    if (!empty($product->en_entreprise)) $modes[] = 'entreprise';
        if (!empty($product->en_entreprise)) $modes[] = 'entreprise';

    $locationId = null;
    $mode       = null;

    // Si on édite un RDV existant : en déduire mode + lieu
    if ($excludeAppointmentId) {
        $editingAppointment = Appointment::find($excludeAppointmentId);
        if ($editingAppointment) {
            if (!is_null($editingAppointment->practice_location_id)) {
                // "cabinet"
                $mode       = 'cabinet';
                $locationId = (int) $editingAppointment->practice_location_id;
            } else {
                // Pas de lieu → déduire du produit
                if (count($modes) === 1) {
                    $mode = $modes[0];
                } else {
                    $mode = in_array('visio', $modes, true) ? 'visio'
                         : (in_array('domicile', $modes, true) ? 'domicile' : 'cabinet');
                }
            }
        }
    }

    // Si toujours pas défini (création ou cas limite) → déduire du produit
    if (!$mode) {
        if (count($modes) === 1) {
            $mode = $modes[0];
        } else {
            $mode = 'cabinet';
        }
    }

    // 2) Dispos récurrentes
    $availabilitiesQuery = Availability::where('user_id', (int) $therapistId)
        ->where('day_of_week', $dayOfWeek0)
        ->where(function ($query) use ($productId) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function ($q) use ($productId) {
                      $q->where('products.id', $productId);
                  });
        });

    if ($mode === 'cabinet' && $locationId) {
        $availabilitiesQuery->where('practice_location_id', $locationId);
    }

    $availabilities = $availabilitiesQuery->get();

    // 2b) Dispos ponctuelles pour ce jour
    $specialQuery = SpecialAvailability::where('user_id', (int) $therapistId)
        ->whereDate('date', $date)
        ->where(function ($query) use ($productId) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function ($q) use ($productId) {
                      $q->where('products.id', $productId);
                  });
        });

    if ($mode === 'cabinet' && $locationId) {
        $specialQuery->where('practice_location_id', $locationId);
    }

    $specialAvailabilities = $specialQuery->get();

    // Si rien ni en récurrent ni en ponctuel => aucun slot
    if ($availabilities->isEmpty() && $specialAvailabilities->isEmpty()) {
        return [];
    }

    // Normaliser toutes les fenêtres de dispo (récurrentes + ponctuelles)
    $windows = [];

    foreach ($availabilities as $a) {
        $windows[] = [
            'start_time' => $a->start_time,
            'end_time'   => $a->end_time,
        ];
    }
    foreach ($specialAvailabilities as $sa) {
        $windows[] = [
            'start_time' => $sa->start_time,
            'end_time'   => $sa->end_time,
        ];
    }

    // 3) RDV existants pour ce jour (hors RDV en cours d'édition)
$existingAppointmentsQuery = Appointment::where('user_id', (int) $therapistId)
    ->whereDate('appointment_date', $date)
    ->when($excludeAppointmentId, function ($query) use ($excludeAppointmentId) {
        return $query->where('id', '!=', $excludeAppointmentId);
    });

$existingAppointmentsQuery = $this->applyBlockingAppointmentsFilter($existingAppointmentsQuery);

$existingAppointments = $existingAppointmentsQuery->get();


    // 4) Indisponibilités couvrant ce jour
    $unavailabilities = Unavailability::where('user_id', (int) $therapistId)
        ->where(function ($query) use ($date) {
            $query->whereDate('start_date', '<=', $date)
                  ->whereDate('end_date', '>=', $date);
        })
        ->get();

    // 5) Construire les slots
    $slots    = [];
    $duration = (int) $duration;

    foreach ($windows as $window) {
        $availabilityStart = Carbon::createFromFormat('H:i:s', $window['start_time'])
            ->setDateFrom($carbonDate);
        $availabilityEnd = Carbon::createFromFormat('H:i:s', $window['end_time'])
            ->setDateFrom($carbonDate);

        // Step de 15 minutes
        while ($availabilityStart->copy()->addMinutes($duration)->lessThanOrEqualTo($availabilityEnd)) {
            $slotStart = $availabilityStart->copy();
            $slotEnd   = $availabilityStart->copy()->addMinutes($duration);

            // Conflit RDV avec buffer ?
            $isBooked = $existingAppointments->contains(function ($appointment) use ($slotStart, $slotEnd, $bufferMinutes) {
                $appointmentStart = Carbon::parse($appointment->appointment_date);
                $appointmentEnd   = $appointmentStart->copy()->addMinutes($appointment->duration);

                if ($bufferMinutes > 0) {
                    $appointmentStart = $appointmentStart->copy()->subMinutes($bufferMinutes);
                    $appointmentEnd   = $appointmentEnd->copy()->addMinutes($bufferMinutes);
                }

                return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
            });

            // Conflit indispo (sans buffer)
            $isUnavailable = $unavailabilities->contains(function ($unavailability) use ($slotStart, $slotEnd) {
                $unavailabilityStart = Carbon::parse($unavailability->start_date);
                $unavailabilityEnd   = Carbon::parse($unavailability->end_date);
                return $slotStart->lt($unavailabilityEnd) && $slotEnd->gt($unavailabilityStart);
            });

            if (!$isBooked && !$isUnavailable) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end'   => $slotEnd->format('H:i'),
                ];
            }

            $availabilityStart->addMinutes(15);
        }
    }

    return $slots;
}




/**
 * Show the form for creating a new appointment for a patient.
 */
public function createPatient($therapistId)
{
    // Validate that the therapist exists and accepts online appointments
    $therapistExists = User::where('id', $therapistId)
                           ->where('accept_online_appointments', true)
                           ->exists();

    if (!$therapistExists) {
        return redirect()->back()->withErrors([
            'therapist_id' => 'Thérapeute invalide ou ne prend pas de rendez-vous en ligne.'
        ]);
    }

    // Retrieve the therapist's details
    $therapist = User::findOrFail($therapistId);

    // Retrieve and order the therapist's products by display_order in ascending order
    $products = Product::where('user_id', $therapistId)
                       ->orderBy('display_order', 'asc') // Change to 'desc' for descending order
                       ->get();

    // Return the view with the therapist and ordered products
    return view('appointments.createPatient', compact('therapist', 'products'));
}


public function storePatient(Request $request)
{
    // Messages d'erreur personnalisés
    $messages = [
        'therapist_id.required'   => 'Le thérapeute est requis.',
        'therapist_id.exists'     => 'Le thérapeute sélectionné est invalide.',
        'first_name.required'     => 'Le prénom est requis.',
        'last_name.required'      => 'Le nom est requis.',
        'email.email'             => 'Veuillez fournir une adresse e-mail valide.',
        'phone.max'               => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
        'appointment_date.required' => 'La date du rendez-vous est requise.',
        'appointment_time.required' => 'L’heure du rendez-vous est requise.',
        'product_id.exists'       => 'Le produit sélectionné est invalide.',
    ];

    // Validation de base
    $request->validate([
        'therapist_id'     => 'required|exists:users,id',
        'first_name'       => 'required|string|max:255',
        'last_name'        => 'required|string|max:255',
        'email'            => 'nullable|email|max:255',
        'phone'            => 'nullable|string|max:20',
        'address'          => 'nullable|string',
        'birthdate'        => 'nullable|date',
        'appointment_date' => 'required|date',
        'appointment_time' => 'required|date_format:H:i',
        'product_id'       => 'required|exists:products,id',
        'notes'            => 'nullable|string',
        'gift_voucher_code' => 'nullable|string|max:64',
        // 'type' may come from the form; if not we will infer it from product flags
        'type'             => 'nullable|string',
        // practice_location_id is validated conditionally (see below)
        'practice_location_id' => 'nullable|integer',
    ], $messages);

    // Produit & thérapeute
    $product   = Product::findOrFail($request->product_id);
    $therapist = User::findOrFail($request->therapist_id);
    $voucherForBooking = $this->resolveGiftVoucherForBooking($therapist, $request->input('gift_voucher_code'));
    $totalAmountCents = $this->computeBookableAmountCents($product);
    $voucherPlannedCents = $voucherForBooking
        ? min((int) $voucherForBooking->remaining_amount_cents, $totalAmountCents)
        : 0;

    // Production safety: for Stripe online collection we only allow full-coverage vouchers.
    // Partial voucher + online payment creates race conditions on remaining balance.
    if (!empty($product->collect_payment)
        && !empty($therapist->stripe_account_id)
        && $voucherForBooking
        && $voucherPlannedCents > 0
        && $voucherPlannedCents < $totalAmountCents) {
        return back()->withErrors([
            'gift_voucher_code' => 'Pour les paiements en ligne, le bon cadeau doit couvrir la totalité du montant.',
        ])->withInput();
    }

    // Inférer le "type" (mode) à partir du produit si non fourni
    // NB: dans votre config, chaque "mode" correspond généralement à un produit distinct
    $mode = $request->input('type');
    if (!$mode) {
        if (!empty($product->dans_le_cabinet)) {
            $mode = 'cabinet';
        } elseif (!empty($product->visio) || !empty($product->en_visio)) {
            $mode = 'visio';
        } elseif (!empty($product->adomicile)) {
            $mode = 'domicile';
        } elseif (!empty($product->en_entreprise)) {
            $mode = 'entreprise';
        } else {
            $mode = 'autre';
        }
    }

    // Si le produit nécessite une adresse (domicile), exiger l'adresse
    if ($mode === 'domicile' || $mode === 'entreprise' || !empty($product->adomicile) || !empty($product->en_entreprise)) {
        $request->validate([
            'address' => 'required|string|max:255',
        ], $messages);
    }

    // Si le mode est au cabinet, EXIGER un practice_location_id appartenant à ce thérapeute
    $practiceLocationId = $request->input('practice_location_id');
    if ($mode === 'cabinet') {
        $request->validate([
            'practice_location_id' => [
                'required',
                Rule::exists('practice_locations', 'id')->where(fn ($q) =>
                    $q->where('user_id', $therapist->id)
                ),
            ],
        ], [
            'practice_location_id.required' => 'Veuillez sélectionner un cabinet.',
            'practice_location_id.exists'   => 'Le cabinet sélectionné est invalide.',
        ]);
    } else {
        // pour visio/domicile, on ignore le cabinet éventuel envoyé
        $practiceLocationId = null;
    }

    // Combiner la date et l'heure
    $appointmentDateTime = Carbon::createFromFormat(
        'Y-m-d H:i',
        $request->appointment_date . ' ' . $request->appointment_time
    );

    $dailyLimitError = $this->getDailyBookingLimitError($therapist, $product, $appointmentDateTime);
    if ($dailyLimitError) {
        return back()->withErrors([
            'appointment_date' => $dailyLimitError,
        ])->withInput();
    }

    // Valider la disponibilité du thérapeute (on passe le practice_location_id pour le cabinet)
    if (!$this->isAvailable($appointmentDateTime, $product->duration, $therapist->id, $product->id, null, $practiceLocationId, $mode)) {
        return back()->withErrors([
            'appointment_date' => 'Le créneau horaire est indisponible ou entre en conflit avec un autre rendez-vous.',
        ])->withInput();
    }

    // Créer / retrouver le ClientProfile (lié au thérapeute)
    $clientProfile = ClientProfile::firstOrCreate(
        [
            'email'   => $request->email,
            'user_id' => $therapist->id,
        ],
        [
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'phone'      => $request->phone,
            'address'    => $request->address,
            'birthdate'  => $request->birthdate,
            'notes'      => $request->notes,
        ]
    );

    if (in_array($mode, ['domicile', 'entreprise'], true) && $request->filled('address')) {
        if ((string) $clientProfile->address !== (string) $request->address) {
            $clientProfile->address = $request->address;
            $clientProfile->save();
        }
    }

    // Créer le rendez-vous (statut 'pending' si paiement, sinon on confirmera plus bas)
    $appointment = Appointment::create([
        'client_profile_id'     => $clientProfile->id,
        'user_id'               => $therapist->id,
        'practice_location_id'  => $practiceLocationId,   // ← ENREGISTRÉ ICI POUR LE CABINET
        'appointment_date'      => $appointmentDateTime,
        'status'                => 'pending',
        'notes'                 => $request->notes,
        'type'                  => $mode,                 // ← on stocke le mode
        'duration'              => $product->duration,
        'product_id'            => $product->id,
        'gift_voucher_id'       => $voucherForBooking?->id,
        'gift_voucher_amount_cents' => $voucherPlannedCents > 0 ? $voucherPlannedCents : null,
    ]);

    // Si visio : créer une réunion + lien
    if ($mode === 'visio' || !empty($product->visio) || !empty($product->en_visio)) {
        $token = Str::random(32);
        $meeting = Meeting::create([
            'name'              => 'Réunion pour ' . $appointment->clientProfile->first_name . ' ' . $appointment->clientProfile->last_name,
            'start_time'        => $appointmentDateTime,
            'duration'          => $product->duration,
            'participant_email' => $appointment->clientProfile->email,
            'client_profile_id' => $clientProfile->id,
            'room_token'        => $token,
            'appointment_id'    => $appointment->id,
        ]);
        // $connectionLink = route('webrtc.room', ['room' => $token]) . '#1'; // si vous en avez besoin
    }

    // Charger pour notif
    $appointment->load('clientProfile', 'user', 'product', 'practiceLocation');

    // Notification au thérapeute (try/catch pour robustesse)
    try {
        $therapist->notify(new AppointmentBooked($appointment));
    } catch (\Exception $e) {
        Log::error('Failed to send appointment notification: ' . $e->getMessage());
    }

    /* ============================================================
       ✅ PACK AUTO-CONSUMPTION (ne casse pas le flow Stripe existant)
       - Si le client a un pack actif non expiré incluant cette prestation
         avec des crédits restants => on consomme 1 crédit et on confirme
         sans paiement.
       ============================================================ */
    try {
        // On ne tente que si on a un email (sinon on ne peut pas "matcher" un client réel)
        // (si ton système autorise des clientProfiles sans email, tu peux enlever ce guard)
        if (!empty($clientProfile->email)) {
            $now = Carbon::now();

            // Cherche un pack "le plus urgent à consommer" (expire bientôt, FIFO)
            $packPurchase = \App\Models\PackPurchase::query()
                ->where('user_id', $therapist->id)
                ->where('client_profile_id', $clientProfile->id)
                ->where('status', 'active')
                ->where(function ($q) use ($now) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', $now);
                })
                ->whereHas('items', function ($q) use ($product) {
                    $q->where('product_id', $product->id)
                      ->where('quantity_remaining', '>', 0);
                })
                // Non-null expires_at d'abord (priorité aux packs qui expirent), puis date la plus proche
                ->orderByRaw('ISNULL(expires_at) ASC')
                ->orderBy('expires_at', 'asc')
                ->orderBy('purchased_at', 'asc')
                ->first();

            if ($packPurchase) {
                // Consomme 1 crédit (ta méthode fait déjà transaction + locks + expired/exhausted)
                $packPurchase->consumeProduct($product->id, 1);

                // Confirmer sans paiement
                $appointment->update([
                    'status' => 'confirmed',
                    // Optionnel : garde une trace dans les notes (ne casse rien)
                    // 'notes' => trim(($appointment->notes ?? '') . "\n[Pack] Crédit utilisé (PackPurchase #{$packPurchase->id})"),
                ]);

                // Emails (même logique que "pas de paiement requis")
                try {
                    if ($appointment->clientProfile->email) {
                        Mail::to($appointment->clientProfile->email)->queue(new AppointmentCreatedPatientMail($appointment));
                    }
                    if ($therapist->email) {
                        Mail::to($therapist->email)->queue(new AppointmentCreatedTherapistMail($appointment));
                    }
                } catch (\Exception $e) {
                    Log::error("Erreur envoi emails (pack auto-consumption) : " . $e->getMessage());
                }

                return redirect()->route('appointments.showPatient', $appointment->token)
                    ->with('success', 'Votre rendez-vous a été réservé avec succès. Votre pack a été utilisé automatiquement.');
            }
        }
    } catch (\Exception $e) {
        // IMPORTANT: ne pas casser la réservation si un souci pack (on log et on continue le flow normal)
        Log::warning('Pack auto-consumption skipped due to error: ' . $e->getMessage());
    }

    /* ---------------------- Paiement Stripe si requis ---------------------- */
    if (!empty($product->collect_payment)) {
        $payableAmountCents = max(0, $totalAmountCents - $voucherPlannedCents);

        if ($payableAmountCents === 0) {
            $appointment->update(['status' => 'confirmed']);

            if ($voucherForBooking && $voucherPlannedCents > 0) {
                try {
                    app(GiftVoucherRedeemService::class)->redeem(
                        $voucherForBooking,
                        $voucherPlannedCents,
                        'Utilisation bon cadeau lors de la réservation',
                        $therapist->id,
                        $appointment->id,
                        null,
                        'booking_online',
                        'applied'
                    );
                } catch (\Throwable $e) {
                    Log::error('Gift voucher redemption failed on full-cover booking', [
                        'appointment_id' => $appointment->id,
                        'voucher_id' => $voucherForBooking->id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->rollbackFailedBooking($appointment, $bookingLink ?? null);
                    return back()->withErrors([
                        'gift_voucher_code' => 'Le bon cadeau n’a pas pu être appliqué. Veuillez réessayer.',
                    ])->withInput();
                }
            }

            try {
                if ($appointment->clientProfile->email) {
                    Mail::to($appointment->clientProfile->email)->queue(new AppointmentCreatedPatientMail($appointment));
                }
                if ($therapist->email) {
                    Mail::to($therapist->email)->queue(new AppointmentCreatedTherapistMail($appointment));
                }
            } catch (\Exception $e) {
                Log::error("Erreur envoi emails : " . $e->getMessage());
            }

            return redirect()->route('appointments.showPatient', $appointment->token)
                ->with('success', 'Votre rendez-vous a été réservé avec succès. Le bon cadeau a été appliqué.');
        }

        if ($therapist->stripe_account_id) {
            $stripeSecretKey = config('services.stripe.secret');
            $stripe = new StripeClient($stripeSecretKey);

            try {
                $session = $stripe->checkout->sessions->create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency'     => 'eur',
                            'product_data' => ['name' => $product->name],
                            'unit_amount'  => (int) $payableAmountCents,
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => route('appointments.success') . '?session_id={CHECKOUT_SESSION_ID}&account_id=' . $therapist->stripe_account_id,
                    'cancel_url'  => route('appointments.cancel') . '?appointment_id=' . $appointment->id,
                    'payment_intent_data' => [
                        'metadata' => [
                            'appointment_id' => $appointment->id,
                            'patient_email'  => $appointment->clientProfile->email,
                            'gift_voucher_id' => $voucherForBooking?->id,
                            'gift_voucher_amount_cents' => $voucherPlannedCents,
                        ],
                    ],
                ], [
                    'stripe_account' => $therapist->stripe_account_id,
                ]);

                $appointment->stripe_session_id = $session->id;
                $appointment->save();

                return redirect($session->url);

            } catch (\Exception $e) {
                Log::error('Stripe Checkout creation failed: ' . $e->getMessage());
                return back()->withErrors(['payment' => 'Erreur lors de la création de la session de paiement. Veuillez réessayer.'])
                             ->withInput();
            }
        } else {
            // Pas de Stripe connecté : on confirme directement
            Log::warning("Thérapeute {$therapist->id} sans compte Stripe. Confirmation sans paiement.");
            $appointment->update(['status' => 'confirmed']);

            if ($voucherForBooking && $voucherPlannedCents > 0) {
                try {
                    app(GiftVoucherRedeemService::class)->redeem(
                        $voucherForBooking,
                        $voucherPlannedCents,
                        'Utilisation bon cadeau (reste à régler hors ligne)',
                        $therapist->id,
                        $appointment->id,
                        null,
                        'booking_offline',
                        'applied'
                    );
                } catch (\Throwable $e) {
                    Log::error('Gift voucher redemption failed when therapist has no Stripe account', [
                        'appointment_id' => $appointment->id,
                        'voucher_id' => $voucherForBooking->id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->rollbackFailedBooking($appointment, $bookingLink ?? null);
                    return back()->withErrors([
                        'gift_voucher_code' => 'Le bon cadeau n’a pas pu être appliqué. Veuillez réessayer.',
                    ])->withInput();
                }
            }

            try {
                if ($appointment->clientProfile->email) {
                    Mail::to($appointment->clientProfile->email)->queue(new AppointmentCreatedPatientMail($appointment));
                }
                if ($therapist->email) {
                    Mail::to($therapist->email)->queue(new AppointmentCreatedTherapistMail($appointment));
                }
            } catch (\Exception $e) {
                Log::error("Erreur envoi emails : " . $e->getMessage());
            }

            return redirect()->route('appointments.showPatient', $appointment->token)
                             ->with('success', 'Votre rendez-vous a été réservé avec succès.');
        }
    }

    /* ---------------------- Pas de paiement requis ---------------------- */
    $appointment->update(['status' => 'confirmed']);

    if ($voucherForBooking && $voucherPlannedCents > 0) {
        try {
            app(GiftVoucherRedeemService::class)->redeem(
                $voucherForBooking,
                $voucherPlannedCents,
                'Utilisation bon cadeau (réservation sans paiement en ligne)',
                $therapist->id,
                $appointment->id,
                null,
                'booking_offline',
                'applied'
            );
        } catch (\Throwable $e) {
            Log::error('Gift voucher redemption failed on no-payment booking', [
                'appointment_id' => $appointment->id,
                'voucher_id' => $voucherForBooking->id,
                'error' => $e->getMessage(),
            ]);
            $this->rollbackFailedBooking($appointment, $bookingLink ?? null);
            return back()->withErrors([
                'gift_voucher_code' => 'Le bon cadeau n’a pas pu être appliqué. Veuillez réessayer.',
            ])->withInput();
        }
    }

    try {
        if ($appointment->clientProfile->email) {
            Mail::to($appointment->clientProfile->email)->queue(new AppointmentCreatedPatientMail($appointment));
        }
        if ($therapist->email) {
            Mail::to($therapist->email)->queue(new AppointmentCreatedTherapistMail($appointment));
        }
    } catch (\Exception $e) {
        Log::error("Erreur envoi emails : " . $e->getMessage());
    }

    return redirect()->route('appointments.showPatient', $appointment->token)
                     ->with('success', 'Votre rendez-vous a été réservé avec succès.');
}





    /**
     * Display the specified appointment for a patient using token.
     */
    public function showPatient($token)
    {
        // Retrieve the appointment by token instead of ID
        $appointment = Appointment::where('token', $token)
            ->with(['clientProfile', 'user', 'product'])
            ->firstOrFail();

        return view('appointments.show_patient', compact('appointment'));
    }

    /**
     * Download the ICS file for the specified appointment.
     */
    public function downloadICS($token)
    {
        // Retrieve the appointment by the token
        $appointment = Appointment::where('token', $token)
            ->with(['clientProfile', 'user'])
            ->firstOrFail();

        // Prepare the ICS file contents
        $icsContent = $this->generateICS($appointment);

        // Generate the filename
        $fileName = 'appointment_' . $appointment->id . '.ics';

        // Return the ICS file as a download response
        return response($icsContent)
            ->header('Content-Type', 'text/calendar')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Generate ICS content for the appointment.
     */
    private function generateICS($appointment)
    {
        $start = $appointment->appointment_date->format('Ymd\THis');
        $end = $appointment->appointment_date->copy()->addMinutes($appointment->duration)->format('Ymd\THis');

        $description = $appointment->notes ?? 'Aucune note ajoutée';
        $therapist = $appointment->user->company_name ?? $appointment->user->name;

        $icsContent = "BEGIN:VCALENDAR\r\n";
        $icsContent .= "VERSION:2.0\r\n";
        $icsContent .= "PRODID:-//YourApp//NONSGML v1.0//EN\r\n";
        $icsContent .= "CALSCALE:GREGORIAN\r\n";
        $icsContent .= "METHOD:PUBLISH\r\n";
        $icsContent .= "BEGIN:VEVENT\r\n";
        $icsContent .= "UID:" . uniqid() . "\r\n";
        $icsContent .= "DTSTART:$start\r\n";
        $icsContent .= "DTEND:$end\r\n";
        $icsContent .= "SUMMARY:Rendez-vous avec $therapist\r\n";
        $icsContent .= "DESCRIPTION:$description\r\n";
        $icsContent .= "LOCATION:En ligne ou au cabinet\r\n";
        $icsContent .= "STATUS:CONFIRMED\r\n";
        $icsContent .= "END:VEVENT\r\n";
        $icsContent .= "END:VCALENDAR\r\n";

        return $icsContent;
    }

public function getAvailableSlotsForPatient(Request $request)
{
    // 1) Basic validation (core fields)
    $request->validate([
        'therapist_id' => 'required|exists:users,id',
        'date'         => 'required|date_format:Y-m-d',
        'product_id'   => 'required|exists:products,id',
        // 'mode' and 'location_id' handled below after we resolve the mode
    ]);

    $therapistId = (int) $request->therapist_id;
    $product     = Product::findOrFail((int) $request->product_id);
    $duration    = (int) ($product->duration ?? 0);

    // 2) Resolve mode
    $requestedMode = $request->input('mode');
    $mode = in_array($requestedMode, ['cabinet','visio','domicile','entreprise'], true)
        ? $requestedMode
        : (function() use ($product) {
            $modes = [];
            if ($product->dans_le_cabinet) $modes[] = 'cabinet';
            if ($product->visio)           $modes[] = 'visio';
            if ($product->adomicile)       $modes[] = 'domicile';
    if (!empty($product->en_entreprise)) $modes[] = 'entreprise';
        if (!empty($product->en_entreprise)) $modes[] = 'entreprise';
            if (count($modes) === 1) return $modes[0];
            return 'cabinet';
        })();

    // 3) If cabinet mode, validate location_id and ensure it belongs to the therapist
    $locationId = null;
    if ($mode === 'cabinet') {
        $request->validate([
            'location_id' => ['required','integer','exists:practice_locations,id'],
        ]);
        $locationId = (int) $request->location_id;

        $ownsLocation = \App\Models\PracticeLocation::where('id', $locationId)
            ->where('user_id', $therapistId)
            ->exists();
        if (!$ownsLocation) {
            return response()->json(['slots' => [], 'message' => 'Invalid location for this therapist.'], 422);
        }
    }

    // 4) Minimum notice + buffer
    $therapist             = User::findOrFail($therapistId);
    $minimumNoticeHours    = (int) ($therapist->minimum_notice_hours ?? 0);
    $bufferMinutes         = (int) ($therapist->buffer_time_between_appointments ?? 0);
    $now                   = Carbon::now();
    $minimumNoticeDateTime = $now->copy()->addHours($minimumNoticeHours);

    // 5) Day + date
    $date       = Carbon::createFromFormat('Y-m-d', $request->date);
    $dayOfWeek0 = $date->dayOfWeekIso - 1; // 0..6
    $dateStr    = $date->format('Y-m-d');

    $dailyLimitError = $this->getDailyBookingLimitError($therapist, $product, $date);
    if ($dailyLimitError) {
        return response()->json([
            'slots' => [],
            'message' => $dailyLimitError,
        ]);
    }

    // 6a) Weekly
    $weeklyQuery = Availability::where('user_id', $therapistId)
        ->where('day_of_week', $dayOfWeek0)
        ->where(function ($query) use ($product) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function ($q) use ($product) {
                      $q->where('products.id', $product->id);
                  });
        });

    if ($mode === 'cabinet' && $locationId) {
        $weeklyQuery->where('practice_location_id', $locationId);
    }

    $weeklyAvailabilities = $weeklyQuery->get();

    // 6b) Specials
    $specialQuery = SpecialAvailability::where('user_id', $therapistId)
        ->whereDate('date', $dateStr)
        ->where(function ($query) use ($product) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function ($q) use ($product) {
                      $q->where('products.id', $product->id);
                  });
        });

    if ($mode === 'cabinet' && $locationId) {
        $specialQuery->where('practice_location_id', $locationId);
    }

    $specialAvailabilities = $specialQuery->get();

    $availabilities = $weeklyAvailabilities->concat($specialAvailabilities);

    if ($availabilities->isEmpty()) {
        return response()->json(['slots' => []]);
    }

$existingAppointmentsQuery = Appointment::where('user_id', $therapistId)
    ->whereDate('appointment_date', $request->date);

$existingAppointmentsQuery = $this->applyBlockingAppointmentsFilter($existingAppointmentsQuery);

$existingAppointments = $existingAppointmentsQuery->get();


    // 8) Unavailabilities (support multi-day spans)
    $unavailabilities = Unavailability::where('user_id', $therapistId)
        ->where(function ($query) use ($request) {
            $query->whereDate('start_date', '<=', $request->date)
                  ->whereDate('end_date', '>=', $request->date);
        })
        ->get();

    // 9) Build slots (15-min step)
    $slots = [];

    foreach ($availabilities as $availability) {
        $availabilityStart = Carbon::createFromFormat('H:i:s', $availability->start_time)
            ->setDateFrom($date);
        $availabilityEnd = Carbon::createFromFormat('H:i:s', $availability->end_time)
            ->setDateFrom($date);

        while ($availabilityStart->copy()->addMinutes($duration)->lessThanOrEqualTo($availabilityEnd)) {
            $slotStart = $availabilityStart->copy();
            $slotEnd   = $availabilityStart->copy()->addMinutes($duration);

            // Enforce minimum notice
            if ($slotStart->lt($minimumNoticeDateTime)) {
                $availabilityStart->addMinutes(15);
                continue;
            }

            // Check overlap with existing appointments (with buffer)
            $isBooked = $existingAppointments->contains(function ($appointment) use ($slotStart, $slotEnd, $bufferMinutes) {
                $appointmentStart = Carbon::parse($appointment->appointment_date);
                $appointmentEnd   = $appointmentStart->copy()->addMinutes($appointment->duration);

                if ($bufferMinutes > 0) {
                    $appointmentStart = $appointmentStart->copy()->subMinutes($bufferMinutes);
                    $appointmentEnd   = $appointmentEnd->copy()->addMinutes($bufferMinutes);
                }

                return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
            });

            // Check overlap with unavailabilities (no buffer)
            $isUnavailable = $unavailabilities->contains(function ($unavailability) use ($slotStart, $slotEnd) {
                $unavailabilityStart = Carbon::parse($unavailability->start_date);
                $unavailabilityEnd   = Carbon::parse($unavailability->end_date);
                return $slotStart->lt($unavailabilityEnd) && $slotEnd->gt($unavailabilityStart);
            });

            if (!$isBooked && !$isUnavailable) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end'   => $slotEnd->format('H:i'),
                ];
            }

            $availabilityStart->addMinutes(15);
        }
    }

    // Optionnel : trier et dédupliquer les slots
    $slots = collect($slots)
        ->unique(fn($s) => $s['start'].'-'.$s['end'])
        ->sortBy('start')
        ->values()
        ->all();

    return response()->json(['slots' => $slots]);
}



public function availableConcreteDatesPatient(Request $request)
{
    $request->validate([
        'therapist_id' => 'required|exists:users,id',
        'product_id'   => 'required|exists:products,id',
        'mode'         => 'nullable|string|in:cabinet,visio,domicile,entreprise',
        'location_id'  => 'nullable|integer',
        'days'         => 'nullable|integer|min:1|max:90',
    ]);

    $therapistId = (int) $request->therapist_id;
    $product     = Product::findOrFail((int) $request->product_id);

    // Resolve mode like slots endpoint does
    $mode = $this->resolvePatientMode($product, $request->input('mode'));

    // Cabinet requires location
    $locationId = null;
    if ($mode === 'cabinet') {
        $request->validate([
            'location_id' => ['required','integer','exists:practice_locations,id'],
        ]);

        $locationId = (int) $request->location_id;

        $ownsLocation = \App\Models\PracticeLocation::where('id', $locationId)
            ->where('user_id', $therapistId)
            ->exists();

        if (!$ownsLocation) {
            return response()->json([
                'dates' => [],
                'next'  => null,
                'message' => 'Invalid location for this therapist.',
            ], 422);
        }
    }

    $days  = (int) $request->input('days', 90);
    $today = Carbon::today();

    $dates = [];
    $next  = null;

    for ($i = 0; $i < $days; $i++) {
        $date      = $today->copy()->addDays($i);
        $dayOfWeek = $date->dayOfWeekIso - 1; // 0..6
        $dateStr   = $date->format('Y-m-d');

        // Quick pre-check: does the therapist have any availability rule that day?
        $weeklyQuery = Availability::where('user_id', $therapistId)
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($q) use ($product) {
                $q->where('applies_to_all', true)
                  ->orWhereHas('products', function ($qq) use ($product) {
                      $qq->where('products.id', $product->id);
                  });
            });

        if ($mode === 'cabinet' && $locationId) {
            $weeklyQuery->where('practice_location_id', $locationId);
        }

        $specialQuery = SpecialAvailability::where('user_id', $therapistId)
            ->whereDate('date', $dateStr)
            ->where(function ($q) use ($product) {
                $q->where('applies_to_all', true)
                  ->orWhereHas('products', function ($qq) use ($product) {
                      $qq->where('products.id', $product->id);
                  });
            });

        if ($mode === 'cabinet' && $locationId) {
            $specialQuery->where('practice_location_id', $locationId);
        }

        $hasAnyAvailabilityRule = $weeklyQuery->exists() || $specialQuery->exists();
        if (!$hasAnyAvailabilityRule) {
            continue;
        }

        // Real “concrete” check: does at least one slot exist for the product duration?
        $slots = $this->computePatientSlotsForDate(
            therapistId: $therapistId,
            product: $product,
            dateStr: $dateStr,
            mode: $mode,
            locationId: $locationId
        );

        if (!empty($slots)) {
            $dates[] = $dateStr;

            // First concrete slot = next slot
            if ($next === null) {
                $next = [
                    'date' => $dateStr,
                    'time' => $slots[0]['start'], // slots already sorted
                ];
            }
        }
    }

    return response()->json([
        'dates' => $dates,
        'next'  => $next,
    ]);
}

/**
 * Reuse the SAME rules as getAvailableSlotsForPatient(),
 * but callable for "date scanning" without duplicating request logic.
 */
private function computePatientSlotsForDate(int $therapistId, Product $product, string $dateStr, string $mode, ?int $locationId): array
{
    $duration = (int) ($product->duration ?? 0);
    if ($duration <= 0) return [];

    $therapist             = User::findOrFail($therapistId);
    $minimumNoticeHours    = (int) ($therapist->minimum_notice_hours ?? 0);
    $bufferMinutes         = (int) ($therapist->buffer_time_between_appointments ?? 0);
    $now                   = Carbon::now();
    $minimumNoticeDateTime = $now->copy()->addHours($minimumNoticeHours);

    $date       = Carbon::createFromFormat('Y-m-d', $dateStr);
    $dayOfWeek0 = $date->dayOfWeekIso - 1;

    if ($this->getDailyBookingLimitError($therapist, $product, $date)) {
        return [];
    }

    // Weekly
    $weeklyQuery = Availability::where('user_id', $therapistId)
        ->where('day_of_week', $dayOfWeek0)
        ->where(function ($query) use ($product) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function ($q) use ($product) {
                      $q->where('products.id', $product->id);
                  });
        });

    if ($mode === 'cabinet' && $locationId) {
        $weeklyQuery->where('practice_location_id', $locationId);
    }

    $weeklyAvailabilities = $weeklyQuery->get();

    // Specials
    $specialQuery = SpecialAvailability::where('user_id', $therapistId)
        ->whereDate('date', $dateStr)
        ->where(function ($query) use ($product) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function ($q) use ($product) {
                      $q->where('products.id', $product->id);
                  });
        });

    if ($mode === 'cabinet' && $locationId) {
        $specialQuery->where('practice_location_id', $locationId);
    }

    $specialAvailabilities = $specialQuery->get();

    $availabilities = $weeklyAvailabilities->concat($specialAvailabilities);
    if ($availabilities->isEmpty()) return [];

    // Existing appointments (apply your blocking filter like slots endpoint)
    $existingAppointmentsQuery = Appointment::where('user_id', $therapistId)
        ->whereDate('appointment_date', $dateStr);

    $existingAppointmentsQuery = $this->applyBlockingAppointmentsFilter($existingAppointmentsQuery);
    $existingAppointments      = $existingAppointmentsQuery->get();

    // Unavailabilities (multi-day spans)
    $unavailabilities = Unavailability::where('user_id', $therapistId)
        ->where(function ($query) use ($dateStr) {
            $query->whereDate('start_date', '<=', $dateStr)
                  ->whereDate('end_date', '>=', $dateStr);
        })
        ->get();

    $slots = [];

    foreach ($availabilities as $availability) {
        $availabilityStart = Carbon::createFromFormat('H:i:s', $availability->start_time)->setDateFrom($date);
        $availabilityEnd   = Carbon::createFromFormat('H:i:s', $availability->end_time)->setDateFrom($date);

        while ($availabilityStart->copy()->addMinutes($duration)->lessThanOrEqualTo($availabilityEnd)) {
            $slotStart = $availabilityStart->copy();
            $slotEnd   = $availabilityStart->copy()->addMinutes($duration);

            if ($slotStart->lt($minimumNoticeDateTime)) {
                $availabilityStart->addMinutes(15);
                continue;
            }

            $isBooked = $existingAppointments->contains(function ($appointment) use ($slotStart, $slotEnd, $bufferMinutes) {
                $appointmentStart = Carbon::parse($appointment->appointment_date);
                $appointmentEnd   = $appointmentStart->copy()->addMinutes($appointment->duration);

                if ($bufferMinutes > 0) {
                    $appointmentStart = $appointmentStart->copy()->subMinutes($bufferMinutes);
                    $appointmentEnd   = $appointmentEnd->copy()->addMinutes($bufferMinutes);
                }

                return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
            });

            $isUnavailable = $unavailabilities->contains(function ($unavailability) use ($slotStart, $slotEnd) {
                $unavailabilityStart = Carbon::parse($unavailability->start_date);
                $unavailabilityEnd   = Carbon::parse($unavailability->end_date);
                return $slotStart->lt($unavailabilityEnd) && $slotEnd->gt($unavailabilityStart);
            });

            if (!$isBooked && !$isUnavailable) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end'   => $slotEnd->format('H:i'),
                ];
            }

            $availabilityStart->addMinutes(15);
        }
    }

    return collect($slots)
        ->unique(fn($s) => $s['start'].'-'.$s['end'])
        ->sortBy('start')
        ->values()
        ->all();
}





public function getAvailableDates(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
    ]);

    $productId   = $request->product_id;
    $therapistId = Auth::id();

    // Jours provenant des dispos récurrentes
    $availableDays = Availability::where('user_id', $therapistId)
        ->where(function($query) use ($productId) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function($q) use ($productId) {
                      $q->where('products.id', $productId);
                  });
        })
        ->pluck('day_of_week')
        ->toArray();

    // Jours provenant des dispos ponctuelles (dates futures ou d'aujourd'hui)
    $specialDays = SpecialAvailability::where('user_id', $therapistId)
        ->whereDate('date', '>=', Carbon::today()->toDateString())
        ->where(function($query) use ($productId) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function($q) use ($productId) {
                      $q->where('products.id', $productId);
                  });
        })
        ->get()
        ->map(function($sa) {
            $date = Carbon::parse($sa->date);
            return $date->dayOfWeekIso - 1; // 0..6
        })
        ->toArray();

    $availableDays = array_values(array_unique(array_merge($availableDays, $specialDays)));

    return response()->json(['available_days' => $availableDays]);
}



public function availableDatesPatient(Request $request)
{
    $request->validate([
        'product_id'   => 'required|exists:products,id',
        'therapist_id' => 'required|exists:users,id',
    ]);

    $productId   = $request->product_id;
    $therapistId = $request->therapist_id;

    // Jours dispo via récurrents
    $availableDays = Availability::where('user_id', $therapistId)
        ->where(function($query) use ($productId) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function($q) use ($productId) {
                      $q->where('products.id', $productId);
                  });
        })
        ->pluck('day_of_week')
        ->toArray();

    // Jours dispo via dispo ponctuelles
    $specialDays = SpecialAvailability::where('user_id', $therapistId)
        ->whereDate('date', '>=', Carbon::today()->toDateString())
        ->where(function($query) use ($productId) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function($q) use ($productId) {
                      $q->where('products.id', $productId);
                  });
        })
        ->get()
        ->map(function($sa) {
            $date = Carbon::parse($sa->date);
            return $date->dayOfWeekIso - 1; // 0..6
        })
        ->toArray();

    $availableDays = array_values(array_unique(array_merge($availableDays, $specialDays)));

    return response()->json(['available_days' => $availableDays]);
}





    // Show the form for creating unavailability
    public function createUnavailability()
    {
        return view('unavailabilities.create');
    }

    // Store a newly created unavailability
    public function storeUnavailability(Request $request)
    {
        [$validated, $startDateTime, $endDateTime] = $this->validatedUnavailabilityPayload($request);

        Unavailability::create([
            'user_id' => Auth::id(),
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
            'reason' => $validated['reason'] ?? null,
        ]);

        return redirect()
            ->route('unavailabilities.index')
            ->with('success', 'Indisponibilité ajoutée avec succès.');
    }

    // Display all unavailability periods
    public function indexUnavailability()
    {
        $unavailabilities = Unavailability::where('user_id', Auth::id())
            ->orderBy('start_date', 'asc')
            ->get();

        return view('unavailabilities.index', compact('unavailabilities'));
    }

    // Show edit form for one unavailability
    public function editUnavailability($id)
    {
        $unavailability = Unavailability::findOrFail($id);

        if ((int) Auth::id() !== (int) $unavailability->user_id) {
            return redirect()
                ->route('unavailabilities.index')
                ->with('error', 'Vous n\'êtes pas autorisé à modifier cette indisponibilité.');
        }

        return view('unavailabilities.edit', compact('unavailability'));
    }

    // Update one unavailability
    public function updateUnavailability(Request $request, $id)
    {
        $unavailability = Unavailability::findOrFail($id);

        if ((int) Auth::id() !== (int) $unavailability->user_id) {
            return redirect()
                ->route('unavailabilities.index')
                ->with('error', 'Vous n\'êtes pas autorisé à modifier cette indisponibilité.');
        }

        [$validated, $startDateTime, $endDateTime] = $this->validatedUnavailabilityPayload($request);

        $unavailability->update([
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
            'reason' => $validated['reason'] ?? null,
        ]);

        return redirect()
            ->route('unavailabilities.index')
            ->with('success', 'Indisponibilité mise à jour avec succès.');
    }

    public function destroyUnavailability($id)
    {
        $unavailability = Unavailability::findOrFail($id);

        \Log::info('Attempting to authorize delete for unavailability', [
            'user_id' => Auth::id(),
            'unavailability_user_id' => $unavailability->user_id,
        ]);

        if ((int) Auth::id() !== (int) $unavailability->user_id) {
            return redirect()
                ->route('unavailabilities.index')
                ->with('error', 'Vous n\'êtes pas autorisé à supprimer cette indisponibilité.');
        }

        $unavailability->delete();

        return redirect()
            ->route('unavailabilities.index')
            ->with('success', 'Indisponibilité supprimée avec succès.');
    }

    private function validatedUnavailabilityPayload(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'end_time' => 'required|date_format:H:i',
            'reason' => 'nullable|string|max:255',
        ]);

        $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $validated['start_date'].' '.$validated['start_time']);
        $endDateTime = Carbon::createFromFormat('Y-m-d H:i', $validated['end_date'].' '.$validated['end_time']);

        if ($endDateTime->lessThanOrEqualTo($startDateTime)) {
            throw ValidationException::withMessages([
                'end_time' => 'La date et l\'heure de fin doivent être après la date et l\'heure de début.',
            ]);
        }

        return [$validated, $startDateTime, $endDateTime];
    }

public function markAsCompleted(Appointment $appointment)
{
    // Ensure the appointment belongs to the authenticated user
    $this->authorize('update', $appointment);

    // Update the status to 'Complété'
    $appointment->status = 'Complété';
    $appointment->save();

    return redirect()->route('appointments.show', $appointment->id)->with('success', 'Le rendez-vous a été marqué comme complété.');
}

public function markAsCompletedIndex(Appointment $appointment)
{
    // Ensure the appointment belongs to the authenticated user
    $this->authorize('update', $appointment);

    // Update the status to 'Complété'
    $appointment->status = 'Complété';
    $appointment->save();

    return redirect()->route('appointments.index')->with('success', 'Le rendez-vous a été marqué comme complété.');
}


public function success(Request $request)
{
    // Récupérer les paramètres de la requête
    $session_id = $request->get('session_id');
    $account_id = $request->get('account_id'); // Récupérer account_id

    // Vérifier la présence des paramètres requis
    if (!$session_id) {
        Log::error('ID de session manquant dans la requête.');
        return redirect()->route('welcome')->withErrors('ID de session manquant.');
    }

    if (!$account_id) {
        Log::error('ID de compte manquant dans la requête.');
        return redirect()->route('welcome')->withErrors('ID de compte manquant.');
    }

    // Initialiser Stripe
    $stripeSecretKey = config('services.stripe.secret');
    $stripe = new StripeClient($stripeSecretKey);

    // Ajouter des logs pour le débogage
    Log::info('Attempting to retrieve Stripe session', [
        'session_id' => $session_id,
        'account_id' => $account_id,
    ]);

    try {
        // Récupérer la session Stripe Checkout en spécifiant le compte connecté
        $session = $stripe->checkout->sessions->retrieve($session_id, [], [
            'stripe_account' => $account_id,
        ]);

        Log::info('Stripe session retrieved successfully', [
            'session_id' => $session->id,
        ]);

        // Récupérer le PaymentIntent en spécifiant le compte connecté
        $paymentIntent = $stripe->paymentIntents->retrieve($session->payment_intent, [], [
            'stripe_account' => $account_id,
        ]);

        Log::info('PaymentIntent retrieved successfully', [
            'payment_intent_id' => $paymentIntent->id,
        ]);

        // Vérifier et logguer les métadonnées
        Log::info('PaymentIntent metadata', [
            'metadata' => $paymentIntent->metadata->toArray(),
        ]);

        // Obtenir les métadonnées
        $appointment_id = $paymentIntent->metadata['appointment_id'] ?? null;
        // Ou
        // $appointment_id = $paymentIntent->metadata->get('appointment_id', null);

        Log::info('Retrieved appointment_id from PaymentIntent metadata', [
            'appointment_id' => $appointment_id,
        ]);

        // Mettre à jour le statut de la réservation
        if ($appointment_id) {
            $appointment = Appointment::find($appointment_id);
            if ($appointment) {
                $voucherAppliedCents = $this->applyGiftVoucherFromStripeMetadata($appointment, $paymentIntent->metadata->toArray());

                $appointment->status = 'Payée';
                $appointment->save();

                Log::info('Appointment status updated to paid', [
                    'appointment_id' => $appointment_id,
                ]);

                // Créer la facture
                $invoice = $this->createInvoiceFromAppointment($appointment);

                if ($voucherAppliedCents > 0) {
                    $this->recordGiftVoucherPaymentOnInvoice($invoice, $appointment, $voucherAppliedCents);
                }

                Log::info('Invoice created successfully', [
                    'invoice_id' => $invoice->id,
                    'appointment_id' => $appointment_id,
                ]);

                // Envoyer les emails après le paiement réussi
                try {
                    // Email au patient
                    $patientEmail = $appointment->clientProfile->email;
                    if ($patientEmail) {
                        Mail::to($patientEmail)->queue(new AppointmentCreatedPatientMail($appointment, $invoice));
                        Log::info('Email de confirmation envoyé au patient', [
                            'patient_email' => $patientEmail,
                        ]);
                    }

                    // Email au thérapeute
                    $therapistEmail = $appointment->user->email;
                    if ($therapistEmail) {
                        Mail::to($therapistEmail)->queue(new AppointmentCreatedTherapistMail($appointment, $invoice));
                        Log::info('Email de confirmation envoyé au thérapeute', [
                            'therapist_email' => $therapistEmail,
                        ]);
                    }

                } catch (\Exception $e) {
                    Log::error('Erreur lors de l\'envoi des e-mails après paiement : ' . $e->getMessage());
                    // Vous pouvez également informer l'utilisateur que les emails n'ont pas pu être envoyés
                }
            } else {
                // Si l'appointment n'est pas trouvée
                Log::warning('Rendez-vous non trouvé avec l\'ID : ' . $appointment_id);
                return redirect()->route('welcome')->withErrors('Rendez-vous non trouvé.');
            }
        } else {
            Log::error('Appointment ID est null dans les métadonnées.');
            return redirect()->route('welcome')->withErrors('ID de rendez-vous manquant dans les informations de paiement.');
        }

   
         // Rediriger vers la confirmation du rendez-vous
        return redirect()->route('appointments.showPatient', $appointment->token)
                         ->with('success', 'Paiement réussi ! Votre rendez-vous est confirmé.');
    } catch (\Exception $e) {
        Log::error('Erreur lors de la gestion du paiement : ' . $e->getMessage());

        // Rediriger avec un message d'erreur générique
        return redirect()->route('welcome')->withErrors('Erreur lors de la récupération des informations de paiement : ' . $e->getMessage());
    }
}







    /**
     * Gérer l'annulation du paiement.
     */
public function cancel(Request $request)
{
    // Récupérer l'ID du rendez-vous depuis les paramètres de la requête
    $appointment_id = $request->get('appointment_id');

    if ($appointment_id) {
        $appointment = Appointment::find($appointment_id);
        if ($appointment) {
            // Récupérer le thérapeute associé au rendez-vous
            $therapist = $appointment->user;

            if ($therapist) {
                // Supprimer le rendez-vous
                $appointment->delete();

                Log::info('Appointment deleted due to cancellation', [
                    'appointment_id' => $appointment_id,
                    'therapist_id' => $therapist->id,
                ]);

                // Rediriger vers le profil du thérapeute avec un message d'erreur
                return redirect()->route('therapist.show', $therapist->slug)->with('error', 'Le paiement a été annulé et votre rendez-vous a été supprimé.');
            } else {
                Log::warning('Therapist not found for appointment', [
                    'appointment_id' => $appointment_id,
                ]);

                // Rediriger avec un message d'erreur si le thérapeute n'est pas trouvé
                return redirect()->route('welcome')->withErrors('Thérapeute non trouvé.');
            }
        } else {
            Log::warning('Attempted to delete non-existent appointment', [
                'appointment_id' => $appointment_id,
            ]);

            // Rediriger avec un message d'erreur si le rendez-vous n'existe pas
            return redirect()->route('welcome')->withErrors('Rendez-vous introuvable.');
        }
    } else {
        Log::warning('No appointment_id provided on cancellation.');

        // Rediriger avec un message d'erreur si aucun appointment_id n'est fourni
        return redirect()->route('welcome')->withErrors('ID de rendez-vous manquant.');
    }
}



    /**
     * Gérer les Webhooks Stripe (optionnel mais recommandé).
     */
  public function handleWebhook(Request $request)
{
    $payload = $request->getContent();
    $sig_header = $request->header('Stripe-Signature');
    $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, $endpoint_secret
        );
    } catch(\UnexpectedValueException $e) {
        // Invalid payload
        return response('Invalid payload', 400);
    } catch(\Stripe\Exception\SignatureVerificationException $e) {
        // Invalid signature
        return response('Invalid signature', 400);
    }

    // Handle the event
    switch ($event->type) {
        case 'checkout.session.completed':
            $session = $event->data->object;
            $this->handleCheckoutSessionCompleted($session);
            break;
        // ... handle other event types
        default:
            Log::info('Received unknown event type ' . $event->type);
    }

    return response('Webhook handled', 200);
}

/**
 * Traitement après la complétion de la session de paiement.
 */
protected function handleCheckoutSessionCompleted($session)
{
    // Récupérer les métadonnées
    $appointment_id = $session->metadata->appointment_id;

    // Mettre à jour le statut de la réservation
    $appointment = Appointment::find($appointment_id);
    if ($appointment && $appointment->status !== 'paid') {
        $appointment->status = 'paid';
        $appointment->save();

        // Envoyer les emails après le paiement réussi
        try {
            // Email au patient
            $patientEmail = $appointment->clientProfile->email;
            if ($patientEmail) {
                Mail::to($patientEmail)->queue(new AppointmentCreatedPatientMail($appointment));
            }

            // Email au thérapeute
            $therapistEmail = $appointment->user->email;
            if ($therapistEmail) {
                Mail::to($therapistEmail)->queue(new AppointmentCreatedTherapistMail($appointment));
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des e-mails après paiement via webhook : ' . $e->getMessage());
        }
    }
}


protected function createInvoiceFromAppointment(Appointment $appointment)
{
    // Récupérer le thérapeute (User)
    $therapist = $appointment->user;

    // Récupérer le client (ClientProfile)
    $client = $appointment->clientProfile;

    // Récupérer le produit (Product)
    $product = $appointment->product;

    // Définir les dates de la facture
    $invoiceDate = now();
    $dueDate = $invoiceDate->copy()->addDays(30); // Par exemple, 30 jours après la date de facturation

    // Calculer les montants
    $unitPrice = $product->price;
    $quantity = 1; // Puisqu'il s'agit d'un rendez-vous unique
    $totalPrice = $unitPrice * $quantity;
    $taxAmount = ($totalPrice * $product->tax_rate) / 100;
    $totalPriceWithTax = $totalPrice + $taxAmount;

    // Déterminer le numéro de facture
    $lastInvoice = Invoice::where('user_id', $therapist->id)
                          ->orderBy('invoice_number', 'desc')
                          ->first();
    $nextInvoiceNumber = $lastInvoice ? $lastInvoice->invoice_number + 1 : 1;

    // Créer la facture
    $invoice = Invoice::create([
        'client_profile_id' => $client->id,
        'user_id' => $therapist->id,
        'invoice_date' => $invoiceDate,
        'due_date' => $dueDate,
        'total_amount' => $totalPrice,
        'total_tax_amount' => $taxAmount,
        'total_amount_with_tax' => $totalPriceWithTax,
        'status' => 'Payée',
        'notes' => $appointment->notes, // Vous pouvez personnaliser cela selon vos besoins
        'invoice_number' => $nextInvoiceNumber,
        'appointment_id' => $appointment->id, // Assurez-vous que la colonne existe dans la table invoices
    ]);

    // Créer l'élément de facture
    $invoice->items()->create([
        'product_id' => $product->id,
        'description' => $product->name,
        'quantity' => $quantity,
        'unit_price' => $unitPrice,
        'tax_rate' => $product->tax_rate,
        'tax_amount' => $taxAmount,
        'total_price' => $totalPrice,
        'total_price_with_tax' => $totalPriceWithTax,
    ]);

    return $invoice;
}

private function resolveGiftVoucherForBooking(User $therapist, ?string $voucherCode): ?GiftVoucher
{
    $code = strtoupper(trim((string) $voucherCode));
    if ($code === '') {
        return null;
    }

    $voucher = GiftVoucher::where('user_id', $therapist->id)
        ->where('code', $code)
        ->first();

    if (! $voucher) {
        throw ValidationException::withMessages([
            'gift_voucher_code' => 'Ce bon cadeau est introuvable.',
        ]);
    }

    if (! $voucher->isUsable()) {
        throw ValidationException::withMessages([
            'gift_voucher_code' => 'Ce bon cadeau n’est pas utilisable (expiré, désactivé ou épuisé).',
        ]);
    }

    return $voucher;
}

private function computeBookableAmountCents(Product $product): int
{
    $price = (float) ($product->price ?? 0);
    $taxRate = (float) ($product->tax_rate ?? 0);
    $amount = $price + ($price * $taxRate / 100);

    return (int) round($amount * 100);
}

private function applyGiftVoucherFromStripeMetadata(Appointment $appointment, array $metadata): int
{
    $voucherId = isset($metadata['gift_voucher_id']) ? (int) $metadata['gift_voucher_id'] : 0;
    $plannedCents = isset($metadata['gift_voucher_amount_cents']) ? (int) $metadata['gift_voucher_amount_cents'] : 0;

    if ($voucherId <= 0 || $plannedCents <= 0) {
        return 0;
    }

    $alreadyApplied = \App\Models\GiftVoucherRedemption::where('appointment_id', $appointment->id)
        ->where('source', 'booking_online')
        ->where('status', 'applied')
        ->sum('amount_cents');

    if ($alreadyApplied > 0) {
        $appointment->gift_voucher_id = $voucherId;
        $appointment->gift_voucher_amount_cents = $alreadyApplied;
        $appointment->save();
        return (int) $alreadyApplied;
    }

    $voucher = GiftVoucher::where('id', $voucherId)
        ->where('user_id', $appointment->user_id)
        ->first();

    if (! $voucher || ! $voucher->isUsable()) {
        return 0;
    }

    $appliedCents = min($plannedCents, (int) $voucher->remaining_amount_cents);
    if ($appliedCents <= 0) {
        return 0;
    }

    app(GiftVoucherRedeemService::class)->redeem(
        $voucher,
        $appliedCents,
        'Utilisation bon cadeau après paiement Stripe',
        (int) $appointment->user_id,
        (int) $appointment->id,
        null,
        'booking_online',
        'applied'
    );

    $appointment->gift_voucher_id = $voucher->id;
    $appointment->gift_voucher_amount_cents = $appliedCents;
    $appointment->save();

    return $appliedCents;
}

private function recordGiftVoucherPaymentOnInvoice(Invoice $invoice, Appointment $appointment, int $appliedCents): void
{
    if ($appliedCents <= 0) {
        return;
    }

    $already = Receipt::where('invoice_id', $invoice->id)
        ->where('source', 'manual')
        ->where('note', 'like', 'Paiement bon cadeau%')
        ->exists();

    if ($already) {
        return;
    }

    $amount = round($appliedCents / 100, 2);
    $clientName = trim(
        (string) optional($appointment->clientProfile)->first_name . ' ' .
        (string) optional($appointment->clientProfile)->last_name
    );

    Receipt::create([
        'user_id' => $appointment->user_id,
        'invoice_id' => $invoice->id,
        'invoice_number' => (string) $invoice->invoice_number,
        'encaissement_date' => now()->toDateString(),
        'client_name' => $clientName ?: 'Client',
        'nature' => 'service',
        'amount_ht' => $amount,
        'amount_ttc' => $amount,
        'payment_method' => 'other',
        'direction' => 'credit',
        'source' => 'manual',
        'note' => 'Paiement bon cadeau ' . ($appointment->giftVoucher?->code ?? ''),
    ]);
}


private function resolveMode(\App\Models\Product $product, ?string $requested = null): string
{
    // Si l'UI envoie 'mode', on respecte
    if (in_array($requested, ['cabinet','visio','domicile','entreprise'], true)) {
        return $requested;
    }

    // Sinon on essaie de déduire (cas simple: un seul mode actif)
    $modes = [];
    if ($product->dans_le_cabinet) $modes[] = 'cabinet';
    if ($product->visio)           $modes[] = 'visio';
    if ($product->adomicile)       $modes[] = 'domicile';
    if (!empty($product->en_entreprise)) $modes[] = 'entreprise';

    if (count($modes) === 1) {
        return $modes[0];
    }

    // Ambigu : par défaut "cabinet" (l'UI devrait envoyer le mode dans ce cas)
    return 'cabinet';
}

// app/Http/Controllers/AppointmentController.php

public function destroy(Appointment $appointment)
{
    // Make sure the current user owns this appointment
    $this->authorize('delete', $appointment); // keep/remove if you use policies

    // Optional: clean up related records (safe if relation doesn't exist)
    optional($appointment->meeting)->delete();
    // optional($appointment->invoice)->delete(); // usually you KEEP invoices

    // Triggers Appointment::deleted -> removeFromGoogle()
    $appointment->delete();

    return redirect()
        ->route('appointments.index')
        ->with('success', 'Le rendez-vous a été supprimé.');
}


/**
 * Resolve booking mode for patient side based on product flags + requested mode.
 */
private function resolvePatientMode(\App\Models\Product $product, ?string $requested): string
{
    if (in_array($requested, ['cabinet', 'visio', 'domicile', 'entreprise'], true)) {
        return $requested;
    }

    $modes = [];
    if ($product->dans_le_cabinet) {
        $modes[] = 'cabinet';
    }
    if ($product->visio || $product->en_visio) {
        $modes[] = 'visio';
    }
    if ($product->adomicile) {
        $modes[] = 'domicile';
    }
    if (!empty($product->en_entreprise)) {
        $modes[] = 'entreprise';
    }

    if (count($modes) === 1) {
        return $modes[0];
    }

    // Ambiguous product: default to cabinet
    return 'cabinet';
}

/**
 * Core logic to compute available slots for patient booking on a given date.
 * Used both by getAvailableSlotsForPatient() and by the date precomputation endpoint.
 */
private function computeSlotsForPatient(
    int $therapistId,
    \App\Models\Product $product,
    \Carbon\Carbon $date,
    string $mode,
    ?int $locationId = null
): array {
    $duration = (int) ($product->duration ?? 0);
    if ($duration <= 0) {
        return [];
    }

    // Minimum notice + buffer
    $therapist             = \App\Models\User::findOrFail($therapistId);
    $minimumNoticeHours    = (int) ($therapist->minimum_notice_hours ?? 0);
    $bufferMinutes         = (int) ($therapist->buffer_time_between_appointments ?? 0);
    $now                   = Carbon::now();
    $minimumNoticeDateTime = $now->copy()->addHours($minimumNoticeHours);

    // Day-of-week in your schema (0 = Monday … 6 = Sunday)
    $dayOfWeek0 = $date->dayOfWeekIso - 1;

    // Availabilities (filtered by product linkage, and optionally by cabinet location)
    $availabilitiesQuery = Availability::where('user_id', $therapistId)
        ->where('day_of_week', $dayOfWeek0)
        ->where(function ($query) use ($product) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function ($q) use ($product) {
                      $q->where('products.id', $product->id);
                  });
        });

    if ($mode === 'cabinet' && $locationId) {
        $availabilitiesQuery->where('practice_location_id', $locationId);
    }

    $availabilities = $availabilitiesQuery->get();
    if ($availabilities->isEmpty()) {
        return [];
    }

$existingAppointmentsQuery = Appointment::where('user_id', $therapistId)
    ->whereDate('appointment_date', $date->format('Y-m-d'));

$existingAppointmentsQuery = $this->applyBlockingAppointmentsFilter($existingAppointmentsQuery);

$existingAppointments = $existingAppointmentsQuery->get();


    // Unavailabilities covering that day
    $unavailabilities = Unavailability::where('user_id', $therapistId)
        ->where(function ($query) use ($date) {
            $query->whereDate('start_date', '<=', $date->format('Y-m-d'))
                  ->whereDate('end_date', '>=', $date->format('Y-m-d'));
        })
        ->get();

    $slots = [];

    foreach ($availabilities as $availability) {
        $availabilityStart = Carbon::createFromFormat('H:i:s', $availability->start_time)
            ->setDateFrom($date);
        $availabilityEnd = Carbon::createFromFormat('H:i:s', $availability->end_time)
            ->setDateFrom($date);

        // Step by 15 minutes
        while ($availabilityStart->copy()->addMinutes($duration)->lessThanOrEqualTo($availabilityEnd)) {
            $slotStart = $availabilityStart->copy();
            $slotEnd   = $availabilityStart->copy()->addMinutes($duration);

            // Respect minimum notice
            if ($slotStart->lt($minimumNoticeDateTime)) {
                $availabilityStart->addMinutes(15);
                continue;
            }

            // Conflicts with existing appointments (with buffer)
            $isBooked = $existingAppointments->contains(function ($appointment) use ($slotStart, $slotEnd, $bufferMinutes) {
                $appointmentStart = Carbon::parse($appointment->appointment_date);
                $appointmentEnd   = $appointmentStart->copy()->addMinutes($appointment->duration);

                if ($bufferMinutes > 0) {
                    $appointmentStart = $appointmentStart->copy()->subMinutes($bufferMinutes);
                    $appointmentEnd   = $appointmentEnd->copy()->addMinutes($bufferMinutes);
                }

                return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
            });

            // Conflicts with unavailabilities (no buffer)
            $isUnavailable = $unavailabilities->contains(function ($unavailability) use ($slotStart, $slotEnd) {
                $unavailabilityStart = Carbon::parse($unavailability->start_date);
                $unavailabilityEnd   = Carbon::parse($unavailability->end_date);
                return $slotStart->lt($unavailabilityEnd) && $slotEnd->gt($unavailabilityStart);
            });

            if (!$isBooked && !$isUnavailable) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end'   => $slotEnd->format('H:i'),
                ];
            }

            $availabilityStart->addMinutes(15);
        }
    }

    return $slots;
}

public function getAvailableSlotsForTherapist(Request $request)
{
    $request->validate([
        'date'       => 'required|date_format:Y-m-d',
        'product_id' => 'required|exists:products,id',
        'mode'       => 'nullable|string|in:cabinet,visio,domicile,entreprise',
        'location_id'=> 'nullable|integer',
        'include_conflicts' => 'nullable|boolean',
    ]);

    // Security: therapist creating for himself only
    $therapistId = Auth::id();

    $product  = Product::findOrFail((int) $request->product_id);
    $duration = (int) ($product->duration ?? 0);

    $mode = $this->resolvePatientMode($product, $request->input('mode'));
    $locationId = null;

    if ($mode === 'cabinet') {
        $request->validate([
            'location_id' => ['required','integer','exists:practice_locations,id'],
        ]);

        $locationId = (int) $request->location_id;

        $ownsLocation = \App\Models\PracticeLocation::where('id', $locationId)
            ->where('user_id', $therapistId)
            ->exists();

        if (!$ownsLocation) {
            return response()->json(['slots' => [], 'message' => 'Invalid location.'], 422);
        }
    }

    $date = Carbon::createFromFormat('Y-m-d', $request->date);

    // ✅ New: let therapist UI request conflict-annotated slots
    $includeConflicts = (bool) $request->input('include_conflicts', false);

    $slots = $this->computeSlotsForTherapist(
        $therapistId,
        $product,
        $date,
        $mode,
        $locationId,
        $includeConflicts
    );

    return response()->json(['slots' => $slots]);
}


private function computeSlotsForTherapist(
    int $therapistId,
    \App\Models\Product $product,
    \Carbon\Carbon $date,
    string $mode,
    ?int $locationId = null,
    bool $includeConflicts = false
): array {
    $duration = (int) ($product->duration ?? 0);
    if ($duration <= 0) return [];

    $therapist     = \App\Models\User::findOrFail($therapistId);
    $bufferMinutes = (int) ($therapist->buffer_time_between_appointments ?? 0);
    $now           = Carbon::now();

    $dayOfWeek0 = $date->dayOfWeekIso - 1;

    $weeklyQuery = Availability::where('user_id', $therapistId)
        ->where('day_of_week', $dayOfWeek0)
        ->where(function ($q) use ($product) {
            $q->where('applies_to_all', true)
              ->orWhereHas('products', fn($qq) => $qq->where('products.id', $product->id));
        });

    if ($mode === 'cabinet' && $locationId) {
        $weeklyQuery->where('practice_location_id', $locationId);
    }

    $specialQuery = SpecialAvailability::where('user_id', $therapistId)
        ->whereDate('date', $date->format('Y-m-d'))
        ->where(function ($q) use ($product) {
            $q->where('applies_to_all', true)
              ->orWhereHas('products', fn($qq) => $qq->where('products.id', $product->id));
        });

    if ($mode === 'cabinet' && $locationId) {
        $specialQuery->where('practice_location_id', $locationId);
    }

    $availabilities = $weeklyQuery->get()->concat($specialQuery->get());
    if ($availabilities->isEmpty()) return [];

    $existingAppointmentsQuery = Appointment::where('user_id', $therapistId)
        ->whereDate('appointment_date', $date->format('Y-m-d'));

    $existingAppointmentsQuery = $this->applyBlockingAppointmentsFilter($existingAppointmentsQuery);
    $existingAppointments = $existingAppointmentsQuery->get();

    $unavailabilities = Unavailability::where('user_id', $therapistId)
        ->where(function ($q) use ($date) {
            $d = $date->format('Y-m-d');
            $q->whereDate('start_date', '<=', $d)
              ->whereDate('end_date',   '>=', $d);
        })
        ->get();

    $slots = [];

    foreach ($availabilities as $availability) {
        $availabilityStart = Carbon::createFromFormat('H:i:s', $availability->start_time)->setDateFrom($date);
        $availabilityEnd   = Carbon::createFromFormat('H:i:s', $availability->end_time)->setDateFrom($date);

        while ($availabilityStart->copy()->addMinutes($duration)->lessThanOrEqualTo($availabilityEnd)) {
            $slotStart = $availabilityStart->copy();
            $slotEnd   = $availabilityStart->copy()->addMinutes($duration);

            // Therapist-side: allow “last-minute”, but don’t show slots in the past
            if ($slotStart->lt($now)) {
                $availabilityStart->addMinutes(15);
                continue;
            }

            // Detect overlapping appointment (internal vs external)
            $overlapAppointment = $existingAppointments->first(function ($appointment) use ($slotStart, $slotEnd, $bufferMinutes) {
                $appointmentStart = Carbon::parse($appointment->appointment_date);
                $appointmentEnd   = $appointmentStart->copy()->addMinutes((int) $appointment->duration);

                if ($bufferMinutes > 0) {
                    $appointmentStart = $appointmentStart->copy()->subMinutes($bufferMinutes);
                    $appointmentEnd   = $appointmentEnd->copy()->addMinutes($bufferMinutes);
                }

                return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
            });

            $isUnavailable = $unavailabilities->contains(function ($unavailability) use ($slotStart, $slotEnd) {
                $unavailabilityStart = Carbon::parse($unavailability->start_date);
                $unavailabilityEnd   = Carbon::parse($unavailability->end_date);
                return $slotStart->lt($unavailabilityEnd) && $slotEnd->gt($unavailabilityStart);
            });

            if ($includeConflicts) {
                $conflicts = [];
                $explanations = [];

                if ($overlapAppointment) {
                    $isExternal = (bool) ($overlapAppointment->external ?? false);
                    $conflicts[] = $isExternal ? 'overlap_external' : 'overlap_internal';
                    $explanations[] = $isExternal
                        ? 'Conflit avec un agenda externe'
                        : 'Conflit avec un autre rendez-vous';
                }

                if ($isUnavailable) {
                    $conflicts[] = 'temporary_unavailability';
                    $explanations[] = 'Indisponibilité temporaire';
                }

                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end'   => $slotEnd->format('H:i'),
                    'has_conflict' => !empty($conflicts),
                    'conflicts' => $conflicts,
                    'explanations' => $explanations,
                ];
            } else {
                // Original behavior: only return truly free slots
                if (!$overlapAppointment && !$isUnavailable) {
                    $slots[] = ['start' => $slotStart->format('H:i'), 'end' => $slotEnd->format('H:i')];
                }
            }

            $availabilityStart->addMinutes(15);
        }
    }

    return collect($slots)
        ->unique(fn($s) => ($s['start'] ?? '').'-'.($s['end'] ?? ''))
        ->sortBy('start')
        ->values()
        ->all();
}

public function availableConcreteDatesTherapist(Request $request)
{
    $request->validate([
        'product_id'   => 'required|exists:products,id',
        'mode'         => 'nullable|string|in:cabinet,visio,domicile,entreprise',
        'location_id'  => 'nullable|integer',
        'days'         => 'nullable|integer|min:1|max:90',
    ]);

    // Therapist creating for himself only
    $therapistId = (int) Auth::id();
    $product     = Product::findOrFail((int) $request->product_id);

    // Reuse your resolver (works fine)
    $mode = $this->resolvePatientMode($product, $request->input('mode'));

    $locationId = null;
    if ($mode === 'cabinet') {
        $request->validate([
            'location_id' => ['required','integer','exists:practice_locations,id'],
        ]);

        $locationId = (int) $request->location_id;

        $ownsLocation = \App\Models\PracticeLocation::where('id', $locationId)
            ->where('user_id', $therapistId)
            ->exists();

        if (!$ownsLocation) {
            return response()->json([
                'dates' => [],
                'message' => 'Invalid location for this therapist.',
            ], 422);
        }
    }

    $days   = (int) $request->input('days', 90);
    $today  = Carbon::today();
    $dates  = [];

    for ($i = 0; $i < $days; $i++) {
        $dateObj   = $today->copy()->addDays($i);
        $dateStr   = $dateObj->format('Y-m-d');

        // IMPORTANT: compute slots therapist-side (no minimum notice)
        $slots = $this->computeSlotsForTherapist(
            $therapistId,
            $product,
            $dateObj,
            $mode,
            $locationId
        );

        if (!empty($slots)) {
            $dates[] = $dateStr;
        }
    }

    return response()->json(['dates' => $dates]);
}

private function applyBlockingAppointmentsFilter($query)
{
    // 1) Cancelled appointments should NOT block new appointments.
    // Keep NULL statuses blocking (often used for external blocks / legacy).
    $query->where(function ($q) {
        $q->whereNull('status')
          ->orWhereNotIn('status', ['cancelled', 'canceled', 'Annulée', 'Annulee']);
    });

    // 2) Exclude external "all-day multi-day" blocks (Google all-day spanning multiple days)
    // Keep everything else blocking.
    return $query->whereRaw(
        'NOT (external = 1 AND TIME(appointment_date) = "00:00:00" AND COALESCE(duration,0) >= 2880 AND MOD(COALESCE(duration,0),1440) = 0)'
    );
}


public function cancelFromMagicLink(Request $request, string $token)
{
    $appointment = Appointment::where('token', $token)->firstOrFail();

    // already cancelled?
    if (in_array($appointment->status, ['cancelled'], true)) {
        return redirect()
            ->route('appointment.confirmation', $token)
            ->with('success', __('Ce rendez-vous est déjà annulé.'));
    }

    // don’t allow cancellation for past appointments
    if ($appointment->appointment_date && $appointment->appointment_date->isPast()) {
        return redirect()
            ->route('appointment.confirmation', $token)
            ->with('error', __('Ce rendez-vous est déjà passé et ne peut plus être annulé.'));
    }

    // cancellation cutoff (therapist setting)
    $cutoffHours = max(0, (int) ($appointment->user?->cancellation_notice_hours ?? 0));

    if ($cutoffHours > 0 && $appointment->appointment_date) {
        $latestCancelAt = $appointment->appointment_date->copy()->subHours($cutoffHours);

        if (now()->greaterThan($latestCancelAt)) {
            return redirect()
                ->route('appointment.confirmation', $token)
                ->with('error', __('L’annulation en ligne n’est plus possible à moins de :hours heure(s) du rendez-vous. Merci de contacter votre thérapeute.', [
                    'hours' => $cutoffHours
                ]));
        }
    }

    // cancel
    $appointment->status = 'cancelled';
    $appointment->save();

    // email therapist
    $therapistEmail = $appointment->user?->company_email ?: $appointment->user?->email;
    if ($therapistEmail) {
        Mail::to($therapistEmail)->send(new AppointmentCancelledByClient($appointment));
    }

return redirect()->route('appointments.showPatient', ['token' => $token]);
}

public function storeByToken(Request $request, string $token)
{
    // 0) Resolve booking link by token
    $bookingLink = \App\Models\BookingLink::where('token', $token)->first();
    if (!$bookingLink || !$bookingLink->canBeUsed()) {
        abort(404); // safer: don't leak whether token exists/expired
    }

    // Force therapist_id from the booking link (prevents spoofing)
    $request->merge([
        'therapist_id' => $bookingLink->user_id,
    ]);

    // Messages d'erreur personnalisés
    $messages = [
        'therapist_id.required'   => 'Le thérapeute est requis.',
        'therapist_id.exists'     => 'Le thérapeute sélectionné est invalide.',
        'first_name.required'     => 'Le prénom est requis.',
        'last_name.required'      => 'Le nom est requis.',
        'email.email'             => 'Veuillez fournir une adresse e-mail valide.',
        'phone.max'               => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
        'appointment_date.required' => 'La date du rendez-vous est requise.',
        'appointment_time.required' => 'L’heure du rendez-vous est requise.',
        'product_id.exists'       => 'Le produit sélectionné est invalide.',
    ];

    // Validation de base
    $request->validate([
        'therapist_id'     => 'required|exists:users,id',
        'first_name'       => 'required|string|max:255',
        'last_name'        => 'required|string|max:255',
        'email'            => 'nullable|email|max:255',
        'phone'            => 'nullable|string|max:20',
        'address'          => 'nullable|string',
        'birthdate'        => 'nullable|date',
        'appointment_date' => 'required|date',
        'appointment_time' => 'required|date_format:H:i',
        'product_id'       => 'required|exists:products,id',
        'notes'            => 'nullable|string',
        'gift_voucher_code' => 'nullable|string|max:64',
        // 'type' may come from the form; if not we will infer it from product flags
        'type'             => 'nullable|string',
        // practice_location_id is validated conditionally (see below)
        'practice_location_id' => 'nullable|integer',
    ], $messages);

    // Produit & thérapeute
    $product   = Product::findOrFail($request->product_id);
    $therapist = User::findOrFail($request->therapist_id);

    // ✅ SECURITY: ensure this product is allowed by the booking link
    if (!$bookingLink->allowsProduct((int) $product->id)) {
        return back()->withErrors([
            'product_id' => 'Cette prestation n’est pas disponible via ce lien partenaire.',
        ])->withInput();
    }

    $voucherForBooking = $this->resolveGiftVoucherForBooking($therapist, $request->input('gift_voucher_code'));
    $totalAmountCents = $this->computeBookableAmountCents($product);
    $voucherPlannedCents = $voucherForBooking
        ? min((int) $voucherForBooking->remaining_amount_cents, $totalAmountCents)
        : 0;

    // Production safety: for Stripe online collection we only allow full-coverage vouchers.
    // Partial voucher + online payment creates race conditions on remaining balance.
    if (!empty($product->collect_payment)
        && !empty($therapist->stripe_account_id)
        && $voucherForBooking
        && $voucherPlannedCents > 0
        && $voucherPlannedCents < $totalAmountCents) {
        return back()->withErrors([
            'gift_voucher_code' => 'Pour les paiements en ligne, le bon cadeau doit couvrir la totalité du montant.',
        ])->withInput();
    }

    // Inférer le "type" (mode) à partir du produit si non fourni
    // NB: dans votre config, chaque "mode" correspond généralement à un produit distinct
    $mode = $request->input('type');
    if (!$mode) {
        if (!empty($product->dans_le_cabinet)) {
            $mode = 'cabinet';
        } elseif (!empty($product->visio) || !empty($product->en_visio)) {
            $mode = 'visio';
        } elseif (!empty($product->adomicile)) {
            $mode = 'domicile';
        } elseif (!empty($product->en_entreprise)) {
            $mode = 'entreprise';
        } else {
            $mode = 'autre';
        }
    }

    // Si le produit nécessite une adresse (domicile), exiger l'adresse
    if ($mode === 'domicile' || $mode === 'entreprise' || !empty($product->adomicile) || !empty($product->en_entreprise)) {
        $request->validate([
            'address' => 'required|string|max:255',
        ], $messages);
    }

    // Si le mode est au cabinet, EXIGER un practice_location_id appartenant à ce thérapeute
    $practiceLocationId = $request->input('practice_location_id');
    if ($mode === 'cabinet') {
        $request->validate([
            'practice_location_id' => [
                'required',
                Rule::exists('practice_locations', 'id')->where(fn ($q) =>
                    $q->where('user_id', $therapist->id)
                ),
            ],
        ], [
            'practice_location_id.required' => 'Veuillez sélectionner un cabinet.',
            'practice_location_id.exists'   => 'Le cabinet sélectionné est invalide.',
        ]);
    } else {
        // pour visio/domicile, on ignore le cabinet éventuel envoyé
        $practiceLocationId = null;
    }

    // Combiner la date et l'heure
    $appointmentDateTime = Carbon::createFromFormat(
        'Y-m-d H:i',
        $request->appointment_date . ' ' . $request->appointment_time
    );

    $dailyLimitError = $this->getDailyBookingLimitError($therapist, $product, $appointmentDateTime);
    if ($dailyLimitError) {
        return back()->withErrors([
            'appointment_date' => $dailyLimitError,
        ])->withInput();
    }

    // Valider la disponibilité du thérapeute (on passe le practice_location_id pour le cabinet)
    if (!$this->isAvailable($appointmentDateTime, $product->duration, $therapist->id, $product->id, null, $practiceLocationId, $mode)) {
        return back()->withErrors([
            'appointment_date' => 'Le créneau horaire est indisponible ou entre en conflit avec un autre rendez-vous.',
        ])->withInput();
    }

    // Créer / retrouver le ClientProfile (lié au thérapeute)
    $clientProfile = ClientProfile::firstOrCreate(
        [
            'email'   => $request->email,
            'user_id' => $therapist->id,
        ],
        [
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'phone'      => $request->phone,
            'address'    => $request->address,
            'birthdate'  => $request->birthdate,
            'notes'      => $request->notes,
        ]
    );

    if (in_array($mode, ['domicile', 'entreprise'], true) && $request->filled('address')) {
        if ((string) $clientProfile->address !== (string) $request->address) {
            $clientProfile->address = $request->address;
            $clientProfile->save();
        }
    }

    // Créer le rendez-vous (statut 'pending' si paiement, sinon on confirmera plus bas)
    $appointment = Appointment::create([
        'client_profile_id'     => $clientProfile->id,
        'user_id'               => $therapist->id,
        'practice_location_id'  => $practiceLocationId,   // ← ENREGISTRÉ ICI POUR LE CABINET
        'appointment_date'      => $appointmentDateTime,
        'status'                => 'pending',
        'notes'                 => $request->notes,
        'type'                  => $mode,                 // ← on stocke le mode
        'duration'              => $product->duration,
        'product_id'            => $product->id,
        'gift_voucher_id'       => $voucherForBooking?->id,
        'gift_voucher_amount_cents' => $voucherPlannedCents > 0 ? $voucherPlannedCents : null,

        // ✅ partner tracking
        'booking_link_id'       => $bookingLink->id,
    ]);

    // ✅ increment uses after successful appointment creation
    $bookingLink->incrementUse();

    // Si visio : créer une réunion + lien
    if ($mode === 'visio' || !empty($product->visio) || !empty($product->en_visio)) {
        $roomToken = Str::random(32);
        $meeting = Meeting::create([
            'name'              => 'Réunion pour ' . $appointment->clientProfile->first_name . ' ' . $appointment->clientProfile->last_name,
            'start_time'        => $appointmentDateTime,
            'duration'          => $product->duration,
            'participant_email' => $appointment->clientProfile->email,
            'client_profile_id' => $clientProfile->id,
            'room_token'        => $roomToken,
            'appointment_id'    => $appointment->id,
        ]);
        // $connectionLink = route('webrtc.room', ['room' => $roomToken]) . '#1'; // si vous en avez besoin
    }

    // Charger pour notif
    $appointment->load('clientProfile', 'user', 'product', 'practiceLocation');

    // Notification au thérapeute (try/catch pour robustesse)
    try {
        $therapist->notify(new AppointmentBooked($appointment));
    } catch (\Exception $e) {
        Log::error('Failed to send appointment notification: ' . $e->getMessage());
    }

    /* ============================================================
       ✅ PACK AUTO-CONSUMPTION (ne casse pas le flow Stripe existant)
       - Si le client a un pack actif non expiré incluant cette prestation
         avec des crédits restants => on consomme 1 crédit et on confirme
         sans paiement.
       ============================================================ */
    try {
        // On ne tente que si on a un email (sinon on ne peut pas "matcher" un client réel)
        // (si ton système autorise des clientProfiles sans email, tu peux enlever ce guard)
        if (!empty($clientProfile->email)) {
            $now = Carbon::now();

            // Cherche un pack "le plus urgent à consommer" (expire bientôt, FIFO)
            $packPurchase = \App\Models\PackPurchase::query()
                ->where('user_id', $therapist->id)
                ->where('client_profile_id', $clientProfile->id)
                ->where('status', 'active')
                ->where(function ($q) use ($now) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', $now);
                })
                ->whereHas('items', function ($q) use ($product) {
                    $q->where('product_id', $product->id)
                      ->where('quantity_remaining', '>', 0);
                })
                // Non-null expires_at d'abord (priorité aux packs qui expirent), puis date la plus proche
                ->orderByRaw('ISNULL(expires_at) ASC')
                ->orderBy('expires_at', 'asc')
                ->orderBy('purchased_at', 'asc')
                ->first();

            if ($packPurchase) {
                // Consomme 1 crédit (ta méthode fait déjà transaction + locks + expired/exhausted)
                $packPurchase->consumeProduct($product->id, 1);

                // Confirmer sans paiement
                $appointment->update([
                    'status' => 'confirmed',
                    // Optionnel : garde une trace dans les notes (ne casse rien)
                    // 'notes' => trim(($appointment->notes ?? '') . "\n[Pack] Crédit utilisé (PackPurchase #{$packPurchase->id})"),
                ]);

                // Emails (même logique que "pas de paiement requis")
                try {
                    if ($appointment->clientProfile->email) {
                        Mail::to($appointment->clientProfile->email)->queue(new AppointmentCreatedPatientMail($appointment));
                    }
                    if ($therapist->email) {
                        Mail::to($therapist->email)->queue(new AppointmentCreatedTherapistMail($appointment));
                    }
                } catch (\Exception $e) {
                    Log::error("Erreur envoi emails (pack auto-consumption) : " . $e->getMessage());
                }

                return redirect()->route('appointments.showPatient', $appointment->token)
                    ->with('success', 'Votre rendez-vous a été réservé avec succès. Votre pack a été utilisé automatiquement.');
            }
        }
    } catch (\Exception $e) {
        // IMPORTANT: ne pas casser la réservation si un souci pack (on log et on continue le flow normal)
        Log::warning('Pack auto-consumption skipped due to error: ' . $e->getMessage());
    }

    /* ---------------------- Paiement Stripe si requis ---------------------- */
    if (!empty($product->collect_payment)) {
        $payableAmountCents = max(0, $totalAmountCents - $voucherPlannedCents);

        if ($payableAmountCents === 0) {
            $appointment->update(['status' => 'confirmed']);

            if ($voucherForBooking && $voucherPlannedCents > 0) {
                try {
                    app(GiftVoucherRedeemService::class)->redeem(
                        $voucherForBooking,
                        $voucherPlannedCents,
                        'Utilisation bon cadeau lors de la réservation',
                        $therapist->id,
                        $appointment->id,
                        null,
                        'booking_online',
                        'applied'
                    );
                } catch (\Throwable $e) {
                    Log::error('Gift voucher redemption failed on full-cover booking', [
                        'appointment_id' => $appointment->id,
                        'voucher_id' => $voucherForBooking->id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->rollbackFailedBooking($appointment, $bookingLink ?? null);
                    return back()->withErrors([
                        'gift_voucher_code' => 'Le bon cadeau n’a pas pu être appliqué. Veuillez réessayer.',
                    ])->withInput();
                }
            }

            try {
                if ($appointment->clientProfile->email) {
                    Mail::to($appointment->clientProfile->email)->queue(new AppointmentCreatedPatientMail($appointment));
                }
                if ($therapist->email) {
                    Mail::to($therapist->email)->queue(new AppointmentCreatedTherapistMail($appointment));
                }
            } catch (\Exception $e) {
                Log::error("Erreur envoi emails : " . $e->getMessage());
            }

            return redirect()->route('appointments.showPatient', $appointment->token)
                ->with('success', 'Votre rendez-vous a été réservé avec succès. Le bon cadeau a été appliqué.');
        }

        if ($therapist->stripe_account_id) {
            $stripeSecretKey = config('services.stripe.secret');
            $stripe = new StripeClient($stripeSecretKey);

            try {
                $session = $stripe->checkout->sessions->create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency'     => 'eur',
                            'product_data' => ['name' => $product->name],
                            'unit_amount'  => (int) $payableAmountCents,
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => route('appointments.success') . '?session_id={CHECKOUT_SESSION_ID}&account_id=' . $therapist->stripe_account_id,
                    'cancel_url'  => route('appointments.cancel') . '?appointment_id=' . $appointment->id,
                    'payment_intent_data' => [
                        'metadata' => [
                            'appointment_id' => $appointment->id,
                            'patient_email'  => $appointment->clientProfile->email,
                            'gift_voucher_id' => $voucherForBooking?->id,
                            'gift_voucher_amount_cents' => $voucherPlannedCents,
                        ],
                    ],
                ], [
                    'stripe_account' => $therapist->stripe_account_id,
                ]);

                $appointment->stripe_session_id = $session->id;
                $appointment->save();

                return redirect($session->url);

            } catch (\Exception $e) {
                Log::error('Stripe Checkout creation failed: ' . $e->getMessage());
                return back()->withErrors(['payment' => 'Erreur lors de la création de la session de paiement. Veuillez réessayer.'])
                             ->withInput();
            }
        } else {
            // Pas de Stripe connecté : on confirme directement
            Log::warning("Thérapeute {$therapist->id} sans compte Stripe. Confirmation sans paiement.");
            $appointment->update(['status' => 'confirmed']);

            if ($voucherForBooking && $voucherPlannedCents > 0) {
                try {
                    app(GiftVoucherRedeemService::class)->redeem(
                        $voucherForBooking,
                        $voucherPlannedCents,
                        'Utilisation bon cadeau (reste à régler hors ligne)',
                        $therapist->id,
                        $appointment->id,
                        null,
                        'booking_offline',
                        'applied'
                    );
                } catch (\Throwable $e) {
                    Log::error('Gift voucher redemption failed when therapist has no Stripe account', [
                        'appointment_id' => $appointment->id,
                        'voucher_id' => $voucherForBooking->id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->rollbackFailedBooking($appointment, $bookingLink ?? null);
                    return back()->withErrors([
                        'gift_voucher_code' => 'Le bon cadeau n’a pas pu être appliqué. Veuillez réessayer.',
                    ])->withInput();
                }
            }

            try {
                if ($appointment->clientProfile->email) {
                    Mail::to($appointment->clientProfile->email)->queue(new AppointmentCreatedPatientMail($appointment));
                }
                if ($therapist->email) {
                    Mail::to($therapist->email)->queue(new AppointmentCreatedTherapistMail($appointment));
                }
            } catch (\Exception $e) {
                Log::error("Erreur envoi emails : " . $e->getMessage());
            }

            return redirect()->route('appointments.showPatient', $appointment->token)
                             ->with('success', 'Votre rendez-vous a été réservé avec succès.');
        }
    }

    /* ---------------------- Pas de paiement requis ---------------------- */
    $appointment->update(['status' => 'confirmed']);

    if ($voucherForBooking && $voucherPlannedCents > 0) {
        try {
            app(GiftVoucherRedeemService::class)->redeem(
                $voucherForBooking,
                $voucherPlannedCents,
                'Utilisation bon cadeau (réservation sans paiement en ligne)',
                $therapist->id,
                $appointment->id,
                null,
                'booking_offline',
                'applied'
            );
        } catch (\Throwable $e) {
            Log::error('Gift voucher redemption failed on no-payment booking', [
                'appointment_id' => $appointment->id,
                'voucher_id' => $voucherForBooking->id,
                'error' => $e->getMessage(),
            ]);
            $this->rollbackFailedBooking($appointment, $bookingLink ?? null);
            return back()->withErrors([
                'gift_voucher_code' => 'Le bon cadeau n’a pas pu être appliqué. Veuillez réessayer.',
            ])->withInput();
        }
    }

    try {
        if ($appointment->clientProfile->email) {
            Mail::to($appointment->clientProfile->email)->queue(new AppointmentCreatedPatientMail($appointment));
        }
        if ($therapist->email) {
            Mail::to($therapist->email)->queue(new AppointmentCreatedTherapistMail($appointment));
        }
    } catch (\Exception $e) {
        Log::error("Erreur envoi emails : " . $e->getMessage());
    }

    return redirect()->route('appointments.showPatient', $appointment->token)
                     ->with('success', 'Votre rendez-vous a été réservé avec succès.');
}
public function createByToken(string $token)
{
    // 1) Resolve booking link
    $bookingLink = \App\Models\BookingLink::where('token', $token)->first();

    if (!$bookingLink || !$bookingLink->canBeUsed()) {
        abort(404);
    }

    // 2) Therapist
    $therapist = \App\Models\User::findOrFail($bookingLink->user_id);

    // 3) Allowed product ids
    $allowedIds = $bookingLink->allowed_product_ids ?? [];
    if (!is_array($allowedIds)) {
        $allowedIds = [];
    }
    if (empty($allowedIds)) {
        abort(404);
    }

    // 4) Load allowed products only (strict)
    $products = \App\Models\Product::query()
        ->where('user_id', $therapist->id)
        ->whereIn('id', $allowedIds)
        ->where('can_be_booked_online', true)
        ->orderBy('display_order', 'asc')
        ->orderBy('created_at', 'desc')
        ->get();

    if ($products->isEmpty()) {
        abort(404);
    }

    // 5) Practice locations (safe ordering)
    $practiceLocations = \App\Models\PracticeLocation::query()
        ->where('user_id', $therapist->id)
        ->orderBy('id', 'asc')
        ->get();

    /**
     * 6) Build the same "prestation group + variants" data your partner blade expects
     * - productsByName: ["Massage californien" => [Product, Product...], ...]
     * - catalog: [{ name: "...", variants: [{id, name, price, duration, visio, adomicile, dans_le_cabinet}, ...] }, ...]
     */
    $productsByName = $products->groupBy('name');

    $catalog = $productsByName->map(function ($items, $name) {
        $variants = $items->values()->map(function ($p) {
            return [
                'id' => (int) $p->id,
                'name' => (string) $p->name,
                'price' => is_null($p->price) ? null : (float) $p->price,
                'duration' => is_null($p->duration) ? null : (int) $p->duration,

                // Flags used by the JS to infer mode
                'visio' => (bool) ($p->visio ?? $p->en_visio ?? false),
                'adomicile' => (bool) ($p->adomicile ?? false),
                'dans_le_cabinet' => (bool) ($p->dans_le_cabinet ?? false),
            ];
        })->toArray();

        return [
            'name' => (string) $name,
            'variants' => $variants,
        ];
    })->values()->toArray();

    // 7) Render partner booking blade
    return view('appointments.create_patient_partner', [
        'bookingLink'       => $bookingLink,
        'therapist'         => $therapist,
        'products'          => $products,
        'productsByName'    => $productsByName,
        'catalog'           => $catalog,
        'practiceLocations' => $practiceLocations,
    ]);
}

private function rollbackFailedBooking(Appointment $appointment, $bookingLink = null): void
{
    try {
        \App\Models\Meeting::query()
            ->where('appointment_id', $appointment->id)
            ->delete();

        $appointment->delete();

        if ($bookingLink && isset($bookingLink->id)) {
            \App\Models\BookingLink::query()
                ->whereKey($bookingLink->id)
                ->where('uses_count', '>', 0)
                ->decrement('uses_count');
        }
    } catch (\Throwable $rollbackError) {
        Log::critical('Failed to rollback appointment after voucher redemption error', [
            'appointment_id' => $appointment->id,
            'booking_link_id' => $bookingLink->id ?? null,
            'error' => $rollbackError->getMessage(),
        ]);
    }
}


}
