<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\User;
use App\Models\Availability;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentCreatedPatientMail;
use App\Mail\AppointmentCreatedTherapistMail;



class AppointmentController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

public function index()
{
    $appointments = Appointment::where('user_id', Auth::id())->with(['clientProfile', 'product'])->get();

    $events = [];

    foreach ($appointments as $appointment) {
        $events[] = [
            'title' => $appointment->clientProfile->first_name . ' ' . $appointment->clientProfile->last_name,
            'start' => $appointment->appointment_date->format('Y-m-d H:i:s'),
            'end' => $appointment->appointment_date->copy()->addMinutes($appointment->duration)->format('Y-m-d H:i:s'),
            'url' => route('appointments.show', $appointment->id),
            'color' => '#854f38',
        ];
    }

    return view('appointments.index', compact('appointments', 'events'));
}


    public function create()
    {
        $clientProfiles = ClientProfile::where('user_id', Auth::id())->get();
        $products = Product::where('user_id', Auth::id())->get();

        return view('appointments.create', compact('clientProfiles', 'products'));
    }

public function store(Request $request)
{
    // Valider la requête
    $request->validate([
        'client_profile_id' => 'required|exists:client_profiles,id',
        'appointment_date' => 'required|date',  // Validation de la date
        'appointment_time' => 'required',      // Validation de l'heure séparément
        'status' => 'required|string',
        'notes' => 'nullable|string',
        'product_id' => 'required|exists:products,id',  // Maintenant requis et lié à une Prestation
    ]);
    $therapistId = Auth::id();

    // Récupérer le produit (Prestation) et sa durée
    $product = Product::findOrFail($request->product_id);
    $duration = $product->duration;

    // Combiner la date et l'heure en un seul datetime
    $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->appointment_date . ' ' . $request->appointment_time);

    // Vérifier la disponibilité en utilisant la durée du produit
    if (!$this->isAvailable($appointmentDateTime, $duration, $therapistId)) {
        return redirect()->back()->withErrors(['appointment_date' => 'Le créneau horaire est déjà réservé ou en dehors des disponibilités.'])->withInput();
    }

    // Enregistrer le rendez-vous
    $appointment = Appointment::create([
        'client_profile_id' => $request->client_profile_id,
        'user_id' => $therapistId,
        'appointment_date' => $appointmentDateTime, // Enregistrer la date et l'heure combinées
        'status' => $request->status,
        'notes' => $request->notes,
        'product_id' => $request->product_id,  // Lier le produit
        'duration' => $duration,  // Ajouter la durée
    ]);

    // Envoyer les notifications par e-mail
    try {
        // Récupérer l'e-mail du patient
        $patientEmail = $appointment->clientProfile->email;

        // Envoyer un e-mail au patient
        if ($patientEmail) {
            Mail::to($patientEmail)->send(new AppointmentCreatedPatientMail($appointment));
        }

        // Récupérer l'e-mail du thérapeute
        $therapistEmail = Auth::user()->email;

        // Envoyer un e-mail au thérapeute
        if ($therapistEmail) {
            Mail::to($therapistEmail)->send(new AppointmentCreatedTherapistMail($appointment));
        }
    } catch (\Exception $e) {
        // Gérer l'exception (par exemple, enregistrer l'erreur)
        Log::error('Erreur lors de l\'envoi des e-mails de notification : ' . $e->getMessage());
    }

    return redirect()->route('appointments.index')->with('success', 'Rendez-vous créé avec succès.');
}


public function show(Appointment $appointment)
{
    // Ensure the appointment belongs to the authenticated user
    $this->authorize('view', $appointment);

    // Check the mode from the linked product
    $mode = null;
    if ($appointment->product) {
        if ($appointment->product->visio) {
            $mode = 'En Visio';
        } elseif ($appointment->product->adomicile) {
            $mode = 'À Domicile';
        } elseif ($appointment->product->dans_le_cabinet) {
            $mode = 'Dans le Cabinet';
        } else {
            $mode = 'Non spécifié';
        }
    } else {
        $mode = 'Non spécifié';
    }

    return view('appointments.show', compact('appointment', 'mode'));
}



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

        // Get available slots for the current appointment date
        $date = $appointmentDateTime->format('Y-m-d');
        $availableSlots = $this->getAvailableSlotsForEdit($date, $duration);

        return view('appointments.edit', compact('appointment', 'clientProfiles', 'products', 'availableSlots'));
    }

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
        return response()->json(['error' => 'Product duration is missing.'], 422);
    }

    $duration = $product->duration;

    // Fetch available slots for the date and duration
    $slots = $this->getAvailableSlotsForEdit($request->date, $duration);

    // Return the available slots as JSON
    return response()->json(['slots' => $slots]);
}



public function update(Request $request, Appointment $appointment)
{
    // Validate the request
    $request->validate([
        'client_profile_id' => 'required|exists:client_profiles,id',
        'appointment_date' => 'required|date',  // Validate date
        'appointment_time' => 'required|date_format:H:i',  // Validate time
        'status' => 'required|string',
        'notes' => 'nullable|string',
        'product_id' => 'required|exists:products,id',  // Now required and linked to a Prestation
    ]);

    // Get the product (Prestation) and its duration
    $product = Product::findOrFail($request->product_id);
    $duration = $product->duration;
	

    // Combine date and time into a single datetime
    $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->appointment_date . ' ' . $request->appointment_time);

    // Check availability using the product's duration
    if (!$this->isAvailable($appointmentDateTime, $duration, $therapistId)) {
        return redirect()->back()->withErrors([
            'appointment_date' => 'The selected time is outside of your availability or conflicts with another appointment.'
        ])->withInput();
    }

    // Update the appointment
    $appointment->update([
        'client_profile_id' => $request->client_profile_id,
        'user_id' => Auth::id(),
        'appointment_date' => $appointmentDateTime, // Save the combined date and time
        'status' => $request->status,
        'notes' => $request->notes,
        'product_id' => $request->product_id,  // Link the product
        'duration' => $duration,  // Update duration based on the product
    ]);

    return redirect()->route('appointments.index')->with('success', 'Rendez-vous mis à jour avec succès.');
}


    public function destroy(Appointment $appointment)
    {
        // Ensure the appointment belongs to the authenticated user
        $this->authorize('delete', $appointment);

        // Delete the appointment
        $appointment->delete();

        return redirect()->route('appointments.index')->with('success', 'Appointment deleted successfully.');
    }



	
	
public function createPatient($therapistId)
{
    // Validate the therapist_id parameter
    if (!User::where('id', $therapistId)->where('accept_online_appointments', true)->exists()) {
        return redirect()->back()->withErrors(['therapist_id' => 'Thérapeute invalide ou ne prend pas de rendez-vous en ligne.']);
    }

    // Fetch therapist details
    $therapist = User::findOrFail($therapistId);

    // Optionally, fetch products if applicable
    $products = Product::where('user_id', $therapistId)->get();

    return view('appointments.createPatient', compact('therapist', 'products'));
}

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
        'appointment_time' => 'required|date_format:H:i', // Ensure time is in correct format
        'product_id' => 'nullable|exists:products,id', // Ensure valid prestation
        'notes' => 'nullable|string',
        'type' => 'nullable|string',
    ], $messages);

    // Retrieve the therapist
    $therapist = User::findOrFail($request->therapist_id);

    // Combine date and time into a single datetime
    $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->appointment_date . ' ' . $request->appointment_time);

    // Validate the product (prestation) and get its duration if selected
    if ($request->filled('product_id')) {
        $product = Product::findOrFail($request->product_id);
        $duration = $product->duration; // Get the duration from the product
    } else {
        return redirect()->back()->withErrors([
            'product_id' => 'Le produit sélectionné est requis.',
        ])->withInput(); // If no product is selected, redirect back with an error.
    }

    // Check therapist's availability
    if (!$this->isAvailable($appointmentDateTime, $duration, $therapist->id)) {
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
        'status' => 'pending', // Default status
        'notes' => $request->notes,
        'type' => $request->type,
        'duration' => $duration, // Use the duration from the product
        'product_id' => $request->product_id,
    ]);

    // Redirect to the confirmation page using the token
    return redirect()->route('appointments.showPatient', $appointment->token)
                     ->with('success', 'Votre rendez-vous a été réservé avec succès.');
}


private function isAvailable($appointmentDateTime, $duration, $therapistId, $excludeAppointmentId = null)
{
    // Convertir la durée en entier
    $duration = (int) $duration;

    // Convertir la date et l'heure du rendez-vous en objet Carbon
    $appointmentStartTime = Carbon::parse($appointmentDateTime);
    $appointmentEndTime = $appointmentStartTime->copy()->addMinutes($duration);

    // Récupérer les disponibilités du thérapeute pour le jour de la semaine
    $dayOfWeek = $appointmentStartTime->dayOfWeekIso - 1; // Lundi = 0

    $availabilities = Availability::where('user_id', $therapistId)
        ->where('day_of_week', $dayOfWeek)
        ->get();

    if ($availabilities->isEmpty()) {
        return false; // Pas de disponibilité ce jour-là
    }

    $isWithinAvailability = false;

    foreach ($availabilities as $availability) {
        $availabilityStart = Carbon::createFromFormat('H:i:s', $availability->start_time)
            ->setDate($appointmentStartTime->year, $appointmentStartTime->month, $appointmentStartTime->day);
        $availabilityEnd = Carbon::createFromFormat('H:i:s', $availability->end_time)
            ->setDate($appointmentStartTime->year, $appointmentStartTime->month, $appointmentStartTime->day);

        // Vérifier si le rendez-vous est dans les disponibilités du thérapeute
        if ($appointmentStartTime->gte($availabilityStart) && $appointmentEndTime->lte($availabilityEnd)) {
            $isWithinAvailability = true;
            break;
        }
    }

    if (!$isWithinAvailability) {
        return false; // Le rendez-vous est en dehors des disponibilités
    }

    // Vérifier les conflits avec les rendez-vous existants
    $conflictingAppointments = Appointment::where('user_id', $therapistId)
        ->where(function ($query) use ($appointmentStartTime, $appointmentEndTime) {
            $query->where(function ($subQuery) use ($appointmentStartTime, $appointmentEndTime) {
                $subQuery->where('appointment_date', '<', $appointmentEndTime)
                         ->whereRaw("DATE_ADD(appointment_date, INTERVAL duration MINUTE) > ?", [$appointmentStartTime]);
            });
        });

    // Exclure le rendez-vous actuel lors de la mise à jour
    if ($excludeAppointmentId) {
        $conflictingAppointments->where('id', '!=', $excludeAppointmentId);
    }

    $conflictExists = $conflictingAppointments->exists();

    return !$conflictExists; // Retourne vrai s'il n'y a pas de conflits
}


public function showPatient($token)
{
    // Retrieve the appointment by token instead of ID
    $appointment = Appointment::where('token', $token)->with(['clientProfile', 'user', 'product'])->firstOrFail();

    return view('appointments.show_patient', compact('appointment'));
}

   public function downloadICS($token)
    {
        // Retrieve the appointment by the token
        $appointment = Appointment::where('token', $token)->with(['clientProfile', 'user'])->firstOrFail();

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

private function getAvailableSlotsForEdit($date, $duration)
{
    $dayOfWeek = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeekIso - 1; // Monday = 0
    $availabilities = Availability::where('user_id', Auth::id())->where('day_of_week', $dayOfWeek)->get();

    if ($availabilities->isEmpty()) {
        return [];
    }

    // Fetch existing appointments for the date
    $existingAppointments = Appointment::where('user_id', Auth::id())
        ->whereDate('appointment_date', $date)
        ->get();

    $slots = [];

    foreach ($availabilities as $availability) {
        $startTime = Carbon::createFromFormat('H:i:s', $availability->start_time)
            ->setDate(Carbon::parse($date)->year, Carbon::parse($date)->month, Carbon::parse($date)->day);
        $endTime = Carbon::createFromFormat('H:i:s', $availability->end_time)
            ->setDate(Carbon::parse($date)->year, Carbon::parse($date)->month, Carbon::parse($date)->day);

        // Check for available slots every 15 minutes
        while ($startTime->copy()->addMinutes($duration)->lessThanOrEqualTo($endTime)) {
            $slotStart = $startTime->copy();
            $slotEnd = $startTime->copy()->addMinutes($duration);

            // Check for overlapping appointments
            $isBooked = $existingAppointments->contains(function ($appointment) use ($slotStart, $slotEnd) {
                $appointmentStart = Carbon::parse($appointment->appointment_date);
                $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->duration);

                // Check if the new slot overlaps with any existing appointment
                return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
            });

            // If the slot is not booked, add it to the available slots
            if (!$isBooked) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                ];
            }

            // Move to the next potential slot (increment by 15 minutes)
            $startTime->addMinutes(15);
        }
    }

    return $slots;
}

public function getAvailableSlotsForPatient(Request $request)
{
    // Validate the request
    $request->validate([
        'therapist_id' => 'required|exists:users,id',
        'date' => 'required|date_format:Y-m-d',
        'duration' => 'required|integer|min:1',  // Validate the duration being passed
    ]);

    // Fetch therapist's availabilities
    $therapistId = $request->therapist_id;
    $duration = (int) $request->duration;  // Cast duration to integer
    $dayOfWeek = Carbon::createFromFormat('Y-m-d', $request->date)->dayOfWeekIso - 1; // Monday = 0

    // Fetch therapist's availabilities for the selected day
    $availabilities = Availability::where('user_id', $therapistId)
        ->where('day_of_week', $dayOfWeek)
        ->get();

    if ($availabilities->isEmpty()) {
        return response()->json(['slots' => []]);
    }

    // Fetch existing appointments for the therapist on the selected date
    $existingAppointments = Appointment::where('user_id', $therapistId)
        ->whereDate('appointment_date', $request->date)
        ->get();

    $slots = [];

    foreach ($availabilities as $availability) {
        $startTime = Carbon::createFromFormat('H:i:s', $availability->start_time)
            ->setDate(Carbon::parse($request->date)->year, Carbon::parse($request->date)->month, Carbon::parse($request->date)->day);
        $endTime = Carbon::createFromFormat('H:i:s', $availability->end_time)
            ->setDate(Carbon::parse($request->date)->year, Carbon::parse($request->date)->month, Carbon::parse($request->date)->day);

        // Check for available slots every 15 minutes
        while ($startTime->copy()->addMinutes($duration)->lessThanOrEqualTo($endTime)) {
            $slotStart = $startTime->copy();
            $slotEnd = $startTime->copy()->addMinutes($duration);

            // Check for overlapping appointments
            $isBooked = $existingAppointments->contains(function ($appointment) use ($slotStart, $slotEnd) {
                $appointmentStart = Carbon::parse($appointment->appointment_date);
                $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->duration);

                // Check if the new slot overlaps with any existing appointment
                return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
            });

            // If the slot is not booked, add it to the available slots
            if (!$isBooked) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                ];
            }

            // Move to the next potential slot (increment by 15 minutes)
            $startTime->addMinutes(15);
        }
    }

    return response()->json(['slots' => $slots]);
}



}
