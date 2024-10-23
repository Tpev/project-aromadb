<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;   
use App\Models\Appointment;
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

class AppointmentController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    /**
     * Display a listing of the appointments.
     */
public function index()
{
    // Fetch appointments for the authenticated user
    $appointments = Appointment::where('user_id', Auth::id())
        ->with(['clientProfile', 'product'])
        ->get();

    $events = [];
    
    // Fetch appointment events
    foreach ($appointments as $appointment) {
        $events[] = [
            'title' => $appointment->clientProfile->first_name . ' ' . $appointment->clientProfile->last_name,
            'start' => $appointment->appointment_date->format('Y-m-d H:i:s'),
            'end' => $appointment->appointment_date->copy()->addMinutes($appointment->duration)->format('Y-m-d H:i:s'),
            'url' => route('appointments.show', $appointment->id),
            'color' => '#854f38',
        ];
    }

    // Fetch unavailability periods for the logged-in user
    $unavailabilities = Unavailability::where('user_id', Auth::id())
        ->get()
        ->map(function ($unavailability) {
            return [
                'title' => $unavailability->reason ?? 'Indisponible', // Use reason if exists, otherwise "Indisponible"
                'start' => $unavailability->start_date->format('Y-m-d H:i:s'), // Format the start_date
                'end' => $unavailability->end_date->format('Y-m-d H:i:s'), // Format the end_date
                'color' => 'grey', // Set the color for the unavailability
				'url' => route('unavailabilities.index'),
            ];
        });

    // Merge unavailability events into the events array
    $events = array_merge($events, $unavailabilities->toArray());

    return view('appointments.index', compact('appointments', 'events'));
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
    // Define base validation rules
    $rules = [
        'client_profile_id' => 'required',
        'appointment_date' => 'required|date',
        'appointment_time' => 'required|date_format:H:i',
        'status' => 'required|string',
        'notes' => 'nullable|string',
        'product_id' => 'required|exists:products,id',
    ];

    // Check if the user selected "Créer un nouveau client"
    if ($request->client_profile_id == 'new') {
        // Add validation rules for new client fields
        $rules = array_merge($rules, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:client_profiles,email',
            'phone' => 'nullable|string|max:15',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:255',
        ]);
    } else {
        // Ensure the client_profile_id exists in the database
        $rules['client_profile_id'] .= '|exists:client_profiles,id';
    }

    // Validate the request
    $validatedData = $request->validate($rules);

    $therapistId = Auth::id();

    // Handle new client creation if needed
    if ($request->client_profile_id == 'new') {
        // Create the new client profile
        $clientProfile = ClientProfile::create([
            'user_id' => $therapistId,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'birthdate' => $request->birthdate,
            'address' => $request->address,
            // Include 'notes' if applicable
            // 'notes' => $request->notes,
        ]);

        $clientProfileId = $clientProfile->id;
    } else {
        $clientProfileId = $request->client_profile_id;
    }

    // Fetch the selected product and its duration
    $product = Product::findOrFail($request->product_id);
    $duration = $product->duration;

    // Combine date and time
    $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->appointment_date . ' ' . $request->appointment_time);

    // Check availability considering the product linkage
    if (!$this->isAvailable($appointmentDateTime, $duration, $therapistId, $product->id)) {
        return redirect()->back()->withErrors(['appointment_time' => 'Le créneau horaire est déjà réservé ou en dehors des disponibilités.'])->withInput();
    }

    // Create the appointment
    $appointment = Appointment::create([
        'client_profile_id' => $clientProfileId,
        'user_id' => $therapistId,
        'appointment_date' => $appointmentDateTime,
        'status' => $request->status,
        'notes' => $request->notes,
        'product_id' => $request->product_id,
        'duration' => $duration,
    ]);

    // Check if the product allows video calls
    if ($product->visio) {
        // Generate a secure token for the room
        $token = Str::random(32);

        // Create the meeting and link it to the appointment
        $meeting = Meeting::create([
            'name' => 'Réunion pour ' . $appointment->clientProfile->first_name . ' ' . $appointment->clientProfile->last_name,
            'start_time' => $appointmentDateTime,
            'duration' => $duration,
            'participant_email' => $appointment->clientProfile->email,
            'client_profile_id' => $clientProfileId,
            'room_token' => $token,
            'appointment_id' => $appointment->id, // Link the meeting to the appointment
        ]);

        // Create the connection link using the meeting token
        $connectionLink = route('webrtc.room', ['room' => $token]) . '#1'; // Append #1 for the initiator

        // Optionally, send an email or notification with the connection link
    }

    // Redirect to the appointments index with a success message
    return redirect()->route('appointments.index')->with('success', 'Rendez-vous créé avec succès.');
}


    /**
     * Display the specified appointment.
     */
public function show(Appointment $appointment)
{
    // Ensure the appointment belongs to the authenticated user
    $this->authorize('view', $appointment);

    // Determine the mode based on the linked product
    $mode = 'Non spécifié';
    if ($appointment->product) {
        if ($appointment->product->visio) {
            $mode = 'En Visio';
        } elseif ($appointment->product->adomicile) {
            $mode = 'À Domicile';
        } elseif ($appointment->product->dans_le_cabinet) {
            $mode = 'Dans le Cabinet';
        }
    }

    // Get the associated meeting if it exists
    $meetingLink = null;
    if ($appointment->meeting) {
        $meetingLink = route('webrtc.room', ['room' => $appointment->meeting->room_token]) . '#1'; // Construct the link
    }
	
	$meetingLinkPatient = null;
    if ($appointment->meeting) {
        $meetingLinkPatient = route('webrtc.room', ['room' => $appointment->meeting->room_token]); // Construct the link
    }
	
    return view('appointments.show', compact('appointment', 'mode', 'meetingLink','meetingLinkPatient'));
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
        // Validate the request
        $request->validate([
            'client_profile_id' => 'required|exists:client_profiles,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'product_id' => 'required|exists:products,id',
        ]);

        // Fetch the selected product and its duration
        $product = Product::findOrFail($request->product_id);
        $duration = $product->duration;

        // Combine date and time
        $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->appointment_date . ' ' . $request->appointment_time);
        $therapistId = Auth::id();

        // Check availability considering the product linkage and exclude the current appointment
        if (!$this->isAvailable($appointmentDateTime, $duration, $therapistId, $product->id, $appointment->id)) {
            return redirect()->back()->withErrors(['appointment_time' => 'Le créneau horaire est déjà réservé ou en dehors des disponibilités.'])->withInput();
        }

        // Update the appointment
        $appointment->update([
            'client_profile_id' => $request->client_profile_id,
            'user_id' => $therapistId,
            'appointment_date' => $appointmentDateTime,
            'status' => $request->status,
            'notes' => $request->notes,
            'product_id' => $request->product_id,
            'duration' => $duration,
        ]);

        // queue email notification to the client about the update
        if ($appointment->clientProfile && $appointment->clientProfile->email) {
            Mail::to($appointment->clientProfile->email)
                ->queue(new AppointmentEditedClientMail($appointment));
        }

        return redirect()->route('appointments.index')->with('success', 'Rendez-vous mis à jour avec succès.');
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function destroy(Appointment $appointment)
    {
        // Ensure the appointment belongs to the authenticated user
        $this->authorize('delete', $appointment);

        // Delete the appointment
        $appointment->delete();

        return redirect()->route('appointments.index')->with('success', 'Rendez-vous supprimé avec succès.');
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
    private function isAvailable($appointmentDateTime, $duration, $therapistId, $productId, $excludeAppointmentId = null)
    {
        // Convert duration to integer
        $duration = (int) $duration;

        // Combine date and time
        $appointmentStartTime = Carbon::parse($appointmentDateTime);
        $appointmentEndTime = $appointmentStartTime->copy()->addMinutes($duration);

        // Get the day of the week (Monday = 0)
        $dayOfWeek = $appointmentStartTime->dayOfWeekIso - 1;

        // Fetch availabilities linked to the product
        $availabilities = Availability::where('user_id', $therapistId)
            ->where('day_of_week', $dayOfWeek)
            ->where(function($query) use ($productId) {
                $query->where('applies_to_all', true)
                      ->orWhereHas('products', function($q) use ($productId) {
                          $q->where('products.id', $productId);
                      });
            })
            ->get();

        if ($availabilities->isEmpty()) {
            return false; // No availability linked to the product on this day
        }

        $isWithinAvailability = false;

        foreach ($availabilities as $availability) {
            $availabilityStart = Carbon::createFromFormat('H:i:s', $availability->start_time)
                ->setDate($appointmentStartTime->year, $appointmentStartTime->month, $appointmentStartTime->day);
            $availabilityEnd = Carbon::createFromFormat('H:i:s', $availability->end_time)
                ->setDate($appointmentStartTime->year, $appointmentStartTime->month, $appointmentStartTime->day);

            // Check if the appointment is within the availability
            if ($appointmentStartTime->gte($availabilityStart) && $appointmentEndTime->lte($availabilityEnd)) {
                $isWithinAvailability = true;
                break;
            }
        }

        if (!$isWithinAvailability) {
            return false; // Appointment is outside availability
        }

        // Check for conflicting appointments
        $conflictingAppointments = Appointment::where('user_id', $therapistId)
            ->where(function ($query) use ($appointmentStartTime, $appointmentEndTime) {
                $query->where(function ($subQuery) use ($appointmentStartTime, $appointmentEndTime) {
                    $subQuery->where('appointment_date', '<', $appointmentEndTime)
                             ->whereRaw("DATE_ADD(appointment_date, INTERVAL duration MINUTE) > ?", [$appointmentStartTime]);
                });
            });

        // Exclude the current appointment if updating
        if ($excludeAppointmentId) {
            $conflictingAppointments->where('id', '!=', $excludeAppointmentId);
        }

        $conflictExists = $conflictingAppointments->exists();

        return !$conflictExists; // True if no conflict
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
    $dayOfWeek = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeekIso - 1; // Monday = 0
    $availabilities = Availability::where('user_id', $therapistId)
        ->where('day_of_week', $dayOfWeek)
        ->where(function($query) use ($productId) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function($q) use ($productId) {
                      $q->where('products.id', $productId);
                  });
        })
        ->get();

    if ($availabilities->isEmpty()) {
        return [];
    }

    // Fetch existing appointments for the date
    $existingAppointments = Appointment::where('user_id', $therapistId)
        ->whereDate('appointment_date', $date)
        ->when($excludeAppointmentId, function ($query) use ($excludeAppointmentId) {
            return $query->where('id', '!=', $excludeAppointmentId);
        })
        ->get();

    // Fetch unavailability periods for the therapist on the selected date
    $unavailabilities = Unavailability::where('user_id', $therapistId)
        ->whereDate('start_date', $date)
        ->get();

    $slots = [];

    foreach ($availabilities as $availability) {
        $availabilityStart = Carbon::createFromFormat('H:i:s', $availability->start_time)
            ->setDate(Carbon::parse($date)->year, Carbon::parse($date)->month, Carbon::parse($date)->day);
        $availabilityEnd = Carbon::createFromFormat('H:i:s', $availability->end_time)
            ->setDate(Carbon::parse($date)->year, Carbon::parse($date)->month, Carbon::parse($date)->day);

        // Check for available slots every 15 minutes
        while ($availabilityStart->copy()->addMinutes($duration)->lessThanOrEqualTo($availabilityEnd)) {
            $slotStart = $availabilityStart->copy();
            $slotEnd = $availabilityStart->copy()->addMinutes($duration);

            // Check for overlapping appointments
            $isBooked = $existingAppointments->contains(function ($appointment) use ($slotStart, $slotEnd) {
                $appointmentStart = Carbon::parse($appointment->appointment_date);
                $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->duration);

                // Check if the new slot overlaps with any existing appointment
                return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
            });

            // Check for overlapping unavailability
            $isUnavailable = $unavailabilities->contains(function ($unavailability) use ($slotStart, $slotEnd) {
                $unavailabilityStart = Carbon::parse($unavailability->start_date);
                $unavailabilityEnd = Carbon::parse($unavailability->end_date);

                // Check if the new slot overlaps with any unavailability
                return $slotStart->lt($unavailabilityEnd) && $slotEnd->gt($unavailabilityStart);
            });

            // If the slot is not booked and not unavailable, add it to the available slots
            if (!$isBooked && !$isUnavailable) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                ];
            }

            // Move to the next potential slot (increment by 15 minutes)
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
        // Validate the therapist_id parameter
        if (!User::where('id', $therapistId)->where('accept_online_appointments', true)->exists()) {
            return redirect()->back()->withErrors(['therapist_id' => 'Thérapeute invalide ou ne prend pas de rendez-vous en ligne.']);
        }

        // Fetch therapist details
        $therapist = User::findOrFail($therapistId);

        // Fetch products associated with the therapist
        $products = Product::where('user_id', $therapistId)->get();

        return view('appointments.createPatient', compact('therapist', 'products'));
    }

    /**
     * Store a newly created appointment from a patient.
     */
    public function storePatient(Request $request)
    {
        // Custom error messages
        $messages = [
            'therapist_id.required' => 'Le thérapeute est requis.',
            'therapist_id.exists' => 'Le thérapeute sélectionné est invalide.',
            'first_name.required' => 'Le prénom est requis.',
            'last_name.required' => 'Le nom est requis.',
            'email.email' => 'Veuillez fournir une adresse e-mail valide.',
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'appointment_date.required' => 'La date du rendez-vous est requise.',
            'appointment_time.required' => 'L’heure du rendez-vous est requise.',
            'product_id.exists' => 'Le produit sélectionné est invalide.',
        ];

        // Validate the incoming request with custom error messages
        $request->validate([
            'therapist_id' => 'required|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'birthdate' => 'nullable|date',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'product_id' => 'required|exists:products,id',
            'notes' => 'nullable|string',
            'type' => 'nullable|string',
        ], $messages);

        // Retrieve the therapist
        $therapist = User::findOrFail($request->therapist_id);

        // Combine date and time
        $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->appointment_date . ' ' . $request->appointment_time);

        // Validate the product (prestation) and get its duration
        $product = Product::findOrFail($request->product_id);
        $duration = $product->duration;

        // Check therapist's availability considering the product linkage
        if (!$this->isAvailable($appointmentDateTime, $duration, $therapist->id, $product->id)) {
            return redirect()->back()->withErrors([
                'appointment_date' => 'Le créneau horaire est indisponible ou entre en conflit avec un autre rendez-vous.',
            ])->withInput();
        }

        // Create or find the ClientProfile
        $clientProfile = ClientProfile::firstOrCreate(
            [
                'email' => $request->email,
                'user_id' => $therapist->id,
            ],
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'birthdate' => $request->birthdate,
                'notes' => $request->notes,
            ]
        );

        // Create the appointment
        $appointment = Appointment::create([
            'client_profile_id' => $clientProfile->id,
            'user_id' => $therapist->id, // Assign to the therapist
            'appointment_date' => $appointmentDateTime,
            'status' => 'Programmé', // Default status
            'notes' => $request->notes,
            'type' => $request->type,
            'duration' => $duration,
            'product_id' => $request->product_id,
        ]);


  // Check if the product allows video calls
    if ($product->visio) {
        // Generate a secure token for the room
        $token = Str::random(32);

    // Create the meeting and link it to the appointment
    $meeting = Meeting::create([
        'name' => 'Réunion pour ' . $appointment->clientProfile->name, // Adjust as necessary
        'start_time' => $appointmentDateTime,
        'duration' => $duration,
        'participant_email' => $appointment->clientProfile->email,
        'client_profile_id' => $request->client_profile_id,
        'room_token' => $token,
        'appointment_id' => $appointment->id, // Link the meeting to the appointment
    ]);

        // Create the connection link using the meeting token
        $connectionLink = route('webrtc.room', ['room' => $token]) . '#1'; // Append #1 for the initiator
	}


        // queue email notifications
        try {
            // Patient email
            $patientEmail = $appointment->clientProfile->email;
            if ($patientEmail) {
                Mail::to($patientEmail)->queue(new AppointmentCreatedPatientMail($appointment));
            }

            // Therapist email
            $therapistEmail = Auth::user()->email;
            if ($therapistEmail) {
                Mail::to($therapistEmail)->queue(new AppointmentCreatedTherapistMail($appointment));
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des e-mails de notification : ' . $e->getMessage());
        }

        // Redirect to the confirmation page using the token
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

    /**
     * Helper method to fetch available slots for a patient booking.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
public function getAvailableSlotsForPatient(Request $request)
{
    // Validate the request
    $request->validate([
        'therapist_id' => 'required|exists:users,id',
        'date' => 'required|date_format:Y-m-d',
        'product_id' => 'required|exists:products,id',
    ]);

    $therapistId = $request->therapist_id;
    $productId = $request->product_id;
    $duration = Product::findOrFail($productId)->duration;

    // Get the therapist's minimum_notice_hours
    $therapist = User::findOrFail($therapistId);
    $minimumNoticeHours = $therapist->minimum_notice_hours ?? 0;

    // Calculate the earliest allowable booking time
    $currentDateTime = Carbon::now();
    $minimumNoticeDateTime = $currentDateTime->copy()->addHours($minimumNoticeHours);

    // Get the day of the week
    $dayOfWeek = Carbon::createFromFormat('Y-m-d', $request->date)->dayOfWeekIso - 1; // Monday = 0

    // Fetch availabilities considering 'applies_to_all' and product linkage
    $availabilities = Availability::where('user_id', $therapistId)
        ->where('day_of_week', $dayOfWeek)
        ->where(function($query) use ($productId) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function($q) use ($productId) {
                      $q->where('products.id', $productId);
                  });
        })
        ->get();

    if ($availabilities->isEmpty()) {
        return response()->json(['slots' => []]);
    }

    // Fetch existing appointments and unavailabilities for the therapist on the selected date
    $existingAppointments = Appointment::where('user_id', $therapistId)
        ->whereDate('appointment_date', $request->date)
        ->get();

    $unavailabilities = Unavailability::where('user_id', $therapistId)
        ->whereDate('start_date', $request->date)
        ->get();

    $slots = [];

    foreach ($availabilities as $availability) {
        $availabilityStart = Carbon::createFromFormat('H:i:s', $availability->start_time)
            ->setDate(Carbon::parse($request->date)->year, Carbon::parse($request->date)->month, Carbon::parse($request->date)->day);
        $availabilityEnd = Carbon::createFromFormat('H:i:s', $availability->end_time)
            ->setDate(Carbon::parse($request->date)->year, Carbon::parse($request->date)->month, Carbon::parse($request->date)->day);

        // Check for available slots every 15 minutes
        while ($availabilityStart->copy()->addMinutes($duration)->lessThanOrEqualTo($availabilityEnd)) {
            $slotStart = $availabilityStart->copy();
            $slotEnd = $availabilityStart->copy()->addMinutes($duration);

            // Check if the slotStart is at least minimum_notice_hours from now
            if ($slotStart->lt($minimumNoticeDateTime)) {
                // Skip this slot as it's within the minimum notice period
                $availabilityStart->addMinutes(15);
                continue;
            }

            // Check for overlapping appointments
            $isBooked = $existingAppointments->contains(function ($appointment) use ($slotStart, $slotEnd) {
                $appointmentStart = Carbon::parse($appointment->appointment_date);
                $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->duration);

                return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
            });

            // Check for overlapping unavailability
            $isUnavailable = $unavailabilities->contains(function ($unavailability) use ($slotStart, $slotEnd) {
                $unavailabilityStart = Carbon::parse($unavailability->start_date);
                $unavailabilityEnd = Carbon::parse($unavailability->end_date);

                return $slotStart->lt($unavailabilityEnd) && $slotEnd->gt($unavailabilityStart);
            });

            // If the slot is not booked and not unavailable, add it to the available slots
            if (!$isBooked && !$isUnavailable) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                ];
            }

            // Move to the next potential slot (increment by 15 minutes)
            $availabilityStart->addMinutes(15);
        }
    }

    return response()->json(['slots' => $slots]);
}





public function getAvailableDates(Request $request)
{
    // Validate the request
    $request->validate([
        'product_id' => 'required|exists:products,id',
    ]);

    $productId = $request->product_id;
    $therapistId = Auth::id();

    // Fetch available days considering 'applies_to_all' and product linkage
    $availableDays = Availability::where('user_id', $therapistId)
        ->where(function($query) use ($productId) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function($q) use ($productId) {
                      $q->where('products.id', $productId);
                  });
        })
        ->pluck('day_of_week')
        ->unique()
        ->toArray();

    return response()->json(['available_days' => $availableDays]);
}


public function availableDatesPatient(Request $request)
{
    // Validate the request
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'therapist_id' => 'required|exists:users,id',
    ]);

    $productId = $request->product_id;
    $therapistId = $request->therapist_id;

    // Fetch available days considering 'applies_to_all' and product linkage
    $availableDays = Availability::where('user_id', $therapistId)
        ->where(function($query) use ($productId) {
            $query->where('applies_to_all', true)
                  ->orWhereHas('products', function($q) use ($productId) {
                      $q->where('products.id', $productId);
                  });
        })
        ->pluck('day_of_week')
        ->unique()
        ->toArray();

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
    $request->validate([
        'start_date' => 'required|date',
        'start_time' => 'required|date_format:H:i',
        'end_date' => 'required|date|after:start_date',
        'end_time' => 'required|date_format:H:i',
        'reason' => 'nullable|string|max:255',
    ]);

    // Merge date and time into a single DateTime object
    $startDateTime = Carbon::parse($request->start_date . ' ' . $request->start_time);
    $endDateTime = Carbon::parse($request->end_date . ' ' . $request->end_time);

    // Create and store unavailability
    $unavailability = new Unavailability();
    $unavailability->user_id = Auth::id();
    $unavailability->start_date = $startDateTime; // Save as datetime
    $unavailability->end_date = $endDateTime; // Save as datetime
    $unavailability->reason = $request->reason;
    $unavailability->save();

    return redirect()->route('unavailabilities.index')->with('success', 'Indisponibilité ajoutée avec succès.');
}


    // Display all unavailability periods
    public function indexUnavailability()
    {
        $unavailabilities = Unavailability::where('user_id', Auth::id())->get(); // Fetch user's unavailability

        return view('unavailabilities.index', compact('unavailabilities'));
    }

public function destroyUnavailability($id)
{
    // Find the unavailability record or fail if it doesn't exist
    $unavailability = Unavailability::findOrFail($id);

    // Log the attempt for debugging purposes
    \Log::info('Attempting to authorize delete for unavailability', [
        'user_id' => Auth::id(),
        'unavailability_user_id' => $unavailability->user_id,
    ]);

    // Check if the authenticated user is the owner of the unavailability
    if (Auth::id() === $unavailability->user_id) {
        $unavailability->delete(); // Delete the unavailability

        return redirect()->route('unavailabilities.index')->with('success', 'Indisponibilité supprimée avec succès.');
    } else {
        // If the user is not authorized to delete this unavailability
        return redirect()->route('unavailabilities.index')->with('error', 'Vous n\'êtes pas autorisé à supprimer cette indisponibilité.');
    }
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


}
