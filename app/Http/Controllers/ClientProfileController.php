<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\Appointment;
use App\Models\SessionNote;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientProfileController extends Controller
{
	use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    /**
     * Display a listing of the client profiles.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all client profiles for the logged-in therapist
        $clientProfiles = ClientProfile::where('user_id', Auth::id())->get();

        return view('client_profiles.index', compact('clientProfiles'));
    }

    /**
     * Show the form for creating a new client profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('client_profiles.create');
    }

    /**
     * Store a newly created client profile in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:15',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:255',
        ]);

        // Create the new client profile
        ClientProfile::create([
            'user_id' => Auth::id(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'birthdate' => $request->birthdate,
            'address' => $request->address,
            'notes' => $request->notes,
        ]);

        return redirect()->route('client_profiles.index')->with('success', 'Client profile created successfully.');
    }

    /**
     * Display the specified client profile.
     *
     * @param  ClientProfile  $clientProfile
     * @return \Illuminate\Http\Response
     */
    public function show(ClientProfile $clientProfile)
    {
        $this->authorize('view', $clientProfile);

        // Get related appointments, session notes, and invoices
        $appointments = $clientProfile->appointments; // Assuming the relation is defined
        $sessionNotes = SessionNote::where('client_profile_id', $clientProfile->id)->get();
        $invoices = Invoice::where('client_profile_id', $clientProfile->id)->get();

        return view('client_profiles.show', compact('clientProfile', 'appointments', 'sessionNotes', 'invoices'));
    }

    /**
     * Show the form for editing the specified client profile.
     *
     * @param  ClientProfile  $clientProfile
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientProfile $clientProfile)
    {
        $this->authorize('update', $clientProfile);

        return view('client_profiles.edit', compact('clientProfile'));
    }

    /**
     * Update the specified client profile in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  ClientProfile  $clientProfile
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClientProfile $clientProfile)
    {
        $this->authorize('update', $clientProfile);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:15',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:255',
        ]);

        // Update the client profile
        $clientProfile->update($request->all());

        return redirect()->route('client_profiles.index')->with('success', 'Client profile updated successfully.');
    }

    /**
     * Remove the specified client profile from storage.
     *
     * @param  ClientProfile  $clientProfile
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClientProfile $clientProfile)
    {
        $this->authorize('delete', $clientProfile);

        // Delete the client profile
        $clientProfile->delete();

        return redirect()->route('client_profiles.index')->with('success', 'Client profile deleted successfully.');
    }
}
