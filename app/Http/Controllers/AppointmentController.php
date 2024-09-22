<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        // Get client profiles for the logged-in therapist
        $clientProfiles = ClientProfile::where('user_id', Auth::id())->get();

        return view('appointments.create', compact('clientProfiles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_profile_id' => 'required|exists:client_profiles,id',
            'appointment_date' => 'required|date',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        Appointment::create([
            'client_profile_id' => $request->client_profile_id,
            'user_id' => Auth::id(),
            'appointment_date' => $request->appointment_date,
            'status' => $request->status,
            'notes' => $request->notes,
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

        return view('appointments.edit', compact('appointment', 'clientProfiles'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        // Ensure the appointment belongs to the authenticated user
        $this->authorize('update', $appointment);

        $request->validate([
            'client_profile_id' => 'required|exists:client_profiles,id',
            'appointment_date' => 'required|date',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Update the appointment
        $appointment->update($request->all());

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
}
