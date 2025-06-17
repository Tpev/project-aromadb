<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\Appointment;
use App\Models\SessionNote;
use App\Models\Message;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientProfileController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    /**
     * Display a listing of the client profiles.
     *
     * @return \Illuminate\Http\Response
     */


public function uploadDocument(Request $request)
{
$clientProfile = auth('client')->user();

if (!$clientProfile) {
    return response()->json(['success' => false, 'message' => 'Profil client introuvable.'], 404);
}


    $request->validate([
        'document' => 'required|file|max:5120', // 5MB max
    ]);

    $file = $request->file('document');
    $path = $file->store('client_documents/' . auth()->id(), 'public');

    // Optional: save record to a `client_documents` table here
    // e.g., ClientDocument::create([...]);

    return response()->json(['success' => true, 'path' => $path]);
}
	 
    public function index()
    {
		    if (Auth::user()->license_status === 'inactive') {
        return redirect('/license-tiers/pricing');
    }
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
        // Include first_name_billing and last_name_billing in the validation
        $request->validate([
            'first_name'         => 'required|string|max:255',
            'last_name'          => 'required|string|max:255',
            'email'              => 'nullable|email|max:255',
            'phone'              => 'nullable|string|max:15',
            'birthdate'          => 'nullable|date',
            'address'            => 'nullable|string|max:255',
            'notes'              => 'nullable|string',
            'first_name_billing' => 'nullable|string|max:255',
            'last_name_billing'  => 'nullable|string|max:255',
        ]);

        // Create the new client profile, including the new billing fields
        ClientProfile::create([
            'user_id'            => Auth::id(),
            'first_name'         => $request->first_name,
            'last_name'          => $request->last_name,
            'email'              => $request->email,
            'phone'              => $request->phone,
            'birthdate'          => $request->birthdate,
            'address'            => $request->address,
            'notes'              => $request->notes,
            'first_name_billing' => $request->first_name_billing,
            'last_name_billing'  => $request->last_name_billing,
        ]);

        return redirect()->route('client_profiles.index')
                         ->with('success', 'Client profile created successfully.');
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
        $appointments = $clientProfile->appointments; 
        $sessionNotes = SessionNote::where('client_profile_id', $clientProfile->id)->get();
        $invoices     = Invoice::where('client_profile_id', $clientProfile->id)->get();

        // Fetch only the questionnaires belonging to the authenticated therapist
        $responses = Response::with('questionnaire')
            ->where('client_profile_id', $clientProfile->id)
            ->get();
			
		$clientProfile = ClientProfile::findOrFail($clientProfile->id);
		$clientProfile->load('messages');

			
        // Récupérer la dernière demande de témoignage, s'il y en a une
        $testimonialRequest = $clientProfile->testimonialRequests()->latest()->first();

        return view('client_profiles.show', compact(
            'clientProfile',
            'appointments',
            'sessionNotes',
            'invoices',
            'responses',
            'testimonialRequest'
        ));
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

        // Include first_name_billing and last_name_billing in the validation
        $request->validate([
            'first_name'         => 'required|string|max:255',
            'last_name'          => 'required|string|max:255',
            'email'              => 'nullable|email|max:255',
            'phone'              => 'nullable|string|max:15',
            'birthdate'          => 'nullable|date',
            'address'            => 'nullable|string|max:255',
            'notes'              => 'nullable|string',
            'first_name_billing' => 'nullable|string|max:255',
            'last_name_billing'  => 'nullable|string|max:255',
        ]);

        // Update the client profile, including billing fields
        $clientProfile->update([
            'first_name'         => $request->first_name,
            'last_name'          => $request->last_name,
            'email'              => $request->email,
            'phone'              => $request->phone,
            'birthdate'          => $request->birthdate,
            'address'            => $request->address,
            'notes'              => $request->notes,
            'first_name_billing' => $request->first_name_billing,
            'last_name_billing'  => $request->last_name_billing,
        ]);

        return redirect()->route('client_profiles.index')
                         ->with('success', 'Client profile updated successfully.');
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

        return redirect()->route('client_profiles.index')
                         ->with('success', 'Client profile deleted successfully.');
    }
public function home()
{
    $clientProfile = auth('client')->user();

    $appointments = $clientProfile->appointments()
        ->where('appointment_date', '>=', now())
        ->orderBy('appointment_date')
        ->get();

    $invoices = $clientProfile->invoices()->latest()->get();

    $messages = Message::where('client_profile_id', $clientProfile->id)
        ->orderBy('created_at', 'asc')
        ->get();

    return view('client.home', compact('clientProfile', 'appointments', 'invoices', 'messages'));
}


	
}
