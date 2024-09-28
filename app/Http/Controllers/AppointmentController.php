<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Availability;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function index()
    {
        // Get all appointments for the authenticated therapist
        $appointments = Appointment::where('user_id', Auth::id())->get();

        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $clientProfiles = ClientProfile::where('user_id', Auth::id())->get();
        $products = Product::where('user_id', Auth::id())->get();

        return view('appointments.create', compact('clientProfiles', 'products'));
    }

    public function store(Request $request)
    {
        // Validate separately
        $request->validate([
            'client_profile_id' => 'required|exists:client_profiles,id',
            'appointment_date' => 'required|date', // Validating date
            'appointment_time' => 'required',     // Validating time separately
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'type' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'product_id' => 'nullable|exists:products,id',
        ]);

        // Combine date and time into a single datetime
        $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->appointment_date . ' ' . $request->appointment_time);

        // Cast duration to integer
        $duration = (int) $request->duration;

        // Check availability before creating the appointment
        if (!$this->isAvailable($appointmentDateTime, $duration)) {
            return redirect()->back()->withErrors(['appointment_date' => 'The appointment time is outside your availability or conflicts with another appointment.'])->withInput();
        }

        // Store the appointment
        Appointment::create([
            'client_profile_id' => $request->client_profile_id,
            'user_id' => Auth::id(),
            'appointment_date' => $appointmentDateTime, // Save the combined date and time
            'status' => $request->status,
            'notes' => $request->notes,
            'type' => $request->type,
            'duration' => $duration,
            'product_id' => $request->product_id,
        ]);

        return redirect()->route('appointments.index')->with('success', 'Appointment created successfully.');
    }

    public function show(Appointment $appointment)
    {
        // Ensure the appointment belongs to the authenticated user
        $this->authorize('view', $appointment);

        return view('appointments.show', compact('appointment'));
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

    private function getAvailableSlotsForEdit($date, $duration)
    {
        $dayOfWeek = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeekIso - 1; // Monday = 0, Sunday = 6
        $availabilities = Availability::where('user_id', Auth::id())->where('day_of_week', $dayOfWeek)->get();

        if ($availabilities->isEmpty()) {
            return [];
        }

        $existingAppointments = Appointment::where('user_id', Auth::id())->whereDate('appointment_date', $date)->get();
        $slots = [];

        foreach ($availabilities as $availability) {
            $startTime = Carbon::createFromFormat('H:i:s', $availability->start_time)->setDate(Carbon::parse($date)->year, Carbon::parse($date)->month, Carbon::parse($date)->day);
            $endTime = Carbon::createFromFormat('H:i:s', $availability->end_time)->setDate(Carbon::parse($date)->year, Carbon::parse($date)->month, Carbon::parse($date)->day);

            while ($startTime->copy()->addMinutes($duration)->lessThanOrEqualTo($endTime)) {
                $slotStart = $startTime->copy();
                $slotEnd = $startTime->copy()->addMinutes($duration);

                // Check for overlapping appointments
                $isBooked = $existingAppointments->contains(function ($appointment) use ($slotStart, $slotEnd) {
                    $appointmentStart = Carbon::parse($appointment->appointment_date);
                    $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->duration);

                    return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
                });

                if (!$isBooked) {
                    $slots[] = [
                        'start' => $slotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                    ];
                }

                $startTime->addMinutes($duration);
            }
        }

        return $slots;
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        // Validate the incoming request
        $request->validate([
            'client_profile_id' => 'required|exists:client_profiles,id',
            'appointment_date' => 'required|date', // Validating date
            'appointment_time' => 'required',     // Validating time separately
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'type' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'product_id' => 'nullable|exists:products,id',
        ]);

        // Combine date and time into a single datetime
        $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->appointment_date . ' ' . $request->appointment_time);

        // Cast duration to integer
        $duration = (int) $request->duration;

        // Check availability before updating the appointment
        if (!$this->isAvailable($appointmentDateTime, $duration, $appointment->id)) {
            return redirect()->back()->withErrors([
                'appointment_date' => 'The appointment time is outside your availability or conflicts with another appointment.'
            ])->withInput();
        }

        // Update the appointment with the validated data
        $appointment->update([
            'client_profile_id' => $request->client_profile_id,
            'appointment_date' => $appointmentDateTime, // Save the combined date and time
            'status' => $request->status,
            'notes' => $request->notes,
            'type' => $request->type,
            'duration' => $duration,
            'product_id' => $request->product_id,
        ]);

        return redirect()->route('appointments.index')->with('success', 'Appointment updated successfully.');
    }

    public function destroy(Appointment $appointment)
    {
        // Ensure the appointment belongs to the authenticated user
        $this->authorize('delete', $appointment);

        // Delete the appointment
        $appointment->delete();

        return redirect()->route('appointments.index')->with('success', 'Appointment deleted successfully.');
    }

    private function isAvailable($appointmentDateTime, $duration, $appointmentId = null)
    {
        // Ensure duration is an integer
        $duration = (int) $duration;

        // Parse appointment datetime
        $appointmentDateTime = Carbon::parse($appointmentDateTime);
        $appointmentEndTime = $appointmentDateTime->copy()->addMinutes($duration);

        // Adjust dayOfWeek to treat Monday as day 0
        $dayOfWeek = ($appointmentDateTime->dayOfWeekIso) - 1;

        // Get therapist's availabilities for the day
        $availabilities = Availability::where('user_id', Auth::id())
            ->where('day_of_week', $dayOfWeek)
            ->get();

        $isWithinAvailability = false;

        foreach ($availabilities as $availability) {
            $availabilityStart = Carbon::createFromFormat('H:i:s', $availability->start_time)
                ->setDate($appointmentDateTime->year, $appointmentDateTime->month, $appointmentDateTime->day);
            $availabilityEnd = Carbon::createFromFormat('H:i:s', $availability->end_time)
                ->setDate($appointmentDateTime->year, $appointmentDateTime->month, $appointmentDateTime->day);

            // Check if appointment is within availability
            if ($appointmentDateTime->gte($availabilityStart) && $appointmentEndTime->lte($availabilityEnd)) {
                $isWithinAvailability = true;
                break;
            }
        }

        if (!$isWithinAvailability) {
            return false;
        }

        // Check for conflicting appointments
        $conflictingAppointments = Appointment::where('user_id', Auth::id())
            ->where('id', '!=', $appointmentId)
            ->where(function ($query) use ($appointmentDateTime, $appointmentEndTime, $duration) {
                $query->where('appointment_date', '<', $appointmentEndTime)
                      ->whereRaw("DATE_ADD(appointment_date, INTERVAL ? MINUTE) > ?", [$duration, $appointmentDateTime]);
            })
            ->exists();

        if ($conflictingAppointments) {
            return false;
        }

        return true;
    }

    public function getAvailableSlots(Request $request)
    {
        Log::info('Fetching available slots for date: ' . $request->date . ', duration: ' . $request->duration);

        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'duration' => 'required|integer|min:1',
        ]);

        $date = Carbon::createFromFormat('Y-m-d', $request->date);
        $dayOfWeek = ($date->dayOfWeekIso) - 1; // Monday = 0, Sunday = 6
        $duration = (int) $request->duration;

        // Fetch user's availabilities for the selected day
        $availabilities = Availability::where('user_id', Auth::id())
            ->where('day_of_week', $dayOfWeek)
            ->get();

        if ($availabilities->isEmpty()) {
            return response()->json(['slots' => []]);
        }

        // Fetch existing appointments on the selected date
        $existingAppointments = Appointment::where('user_id', Auth::id())
            ->whereDate('appointment_date', $date->toDateString())
            ->get();

        $slots = [];

        foreach ($availabilities as $availability) {
            $startTime = Carbon::createFromFormat('H:i:s', $availability->start_time)
                ->setDate($date->year, $date->month, $date->day);
            $endTime = Carbon::createFromFormat('H:i:s', $availability->end_time)
                ->setDate($date->year, $date->month, $date->day);

            while ($startTime->copy()->addMinutes($duration)->lessThanOrEqualTo($endTime)) {
                $slotStart = $startTime->copy();
                $slotEnd = $startTime->copy()->addMinutes($duration);

                // Check for overlapping appointments
                $isBooked = $existingAppointments->contains(function ($appointment) use ($slotStart, $slotEnd) {
                    $appointmentStart = Carbon::parse($appointment->appointment_date);
                    $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->duration);

                    return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
                });

                if (!$isBooked) {
                    $slots[] = [
                        'start' => $slotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                    ];
                }

                $startTime->addMinutes($duration);
            }
        }

        return response()->json(['slots' => $slots]);
    }
}
