<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\Appointment;
use App\Models\SessionNote;
use App\Models\Message;
use App\Models\Invoice;
use App\Models\Response;
use App\Models\CorporateClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\PackProduct;
use App\Models\PackPurchase;

class ClientProfileController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    /**
     * Upload a document from the client portal.
     */
    public function uploadDocument(Request $request)
    {
        $clientProfile = auth('client')->user();

        if (!$clientProfile) {
            return response()->json(['success' => false, 'message' => 'Profil client introuvable.'], 404);
        }

        $request->validate([
            'document' => 'required|file|max:204800', // 200MB max
        ]);

        $file = $request->file('document');
        $path = $file->store('client_documents/' . auth()->id(), 'public');

        // Optional: save record to a `client_documents` table here

        return response()->json(['success' => true, 'path' => $path]);
    }

    /**
     * Display a listing of the client profiles.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->license_status === 'inactive') {
            return redirect('/license-tiers/pricing');
        }

        $userId = Auth::id();

        // Get all client profiles for the logged-in therapist
        $clientProfiles = ClientProfile::where('user_id', $userId)
            ->with('company')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // If request comes from /mobile/... → use mobile view
        if (request()->routeIs('mobile.*') || request()->is('mobile/*')) {
            return view('mobile.clients.index', compact('clientProfiles'));
        }

        // Default: desktop/web view
        return view('client_profiles.index', compact('clientProfiles'));
    }

    /**
     * Show the form for creating a new client profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();

        $companies = CorporateClient::where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        // Optionnel: pré-remplir via ?company_id= dans l’URL
        $selectedCompanyId = request('company_id');

        return view('client_profiles.create', compact('companies', 'selectedCompanyId'));
    }

    /**
     * Store a newly created client profile in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userId = Auth::id();

        // Include first_name_billing, last_name_billing & company_id in the validation
        $data = $request->validate([
            'first_name'         => 'required|string|max:255',
            'last_name'          => 'required|string|max:255',
            'email'              => 'nullable|email|max:255',
            'phone'              => 'nullable|string|max:15',
            'birthdate'          => 'nullable|date',
            'address'            => 'nullable|string|max:255',
            'notes'              => 'nullable|string',
            'first_name_billing' => 'nullable|string|max:255',
            'last_name_billing'  => 'nullable|string|max:255',

            // 👉 lien vers entreprise (facultatif, et uniquement parmi les entreprises du thérapeute)
            'company_id'         => [
                'nullable',
                Rule::exists('corporate_clients', 'id')
                    ->where('user_id', $userId),
            ],
        ]);

        $data['user_id'] = $userId;

        ClientProfile::create($data);

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

    // -----------------------------
    // Appointments (desktop + mobile)
    // -----------------------------
    $appointmentsQuery = $clientProfile->appointments()->with('product');

    $allAppointments = $appointmentsQuery
        ->orderByDesc('appointment_date')
        ->get();

    $mobileAppointments = $allAppointments->take(5);

    // -----------------------------
    // Session notes + invoices
    // -----------------------------
    $sessionNotes = SessionNote::where('client_profile_id', $clientProfile->id)->get();
    $invoices     = Invoice::where('client_profile_id', $clientProfile->id)->get();

    // -----------------------------
    // Questionnaires responses (therapist-owned questionnaires assumed by existing logic)
    // -----------------------------
    $responses = Response::with('questionnaire')
        ->where('client_profile_id', $clientProfile->id)
        ->get();

    // -----------------------------
    // Reload client with messages + company (keep your existing behavior)
    // -----------------------------
    $clientProfile = ClientProfile::findOrFail($clientProfile->id);
    $clientProfile->load(['messages', 'company']);

    // -----------------------------
    // Testimonial
    // -----------------------------
    $testimonialRequest = $clientProfile->testimonialRequests()->latest()->first();

    // -----------------------------
    // PACKS (Forfaits) — NEW
    // -----------------------------
    $packPurchases = PackPurchase::with(['pack', 'items.product'])
        ->where('client_profile_id', $clientProfile->id)
        ->latest()
        ->get();

    $availablePacks = PackProduct::where('user_id', auth()->id())
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    // -----------------------------
    // Mobile view
    // -----------------------------
    if (request()->routeIs('mobile.*') || request()->is('mobile/*')) {
        return view('mobile.clients.show', [
            'clientProfile' => $clientProfile,
            'appointments'  => $mobileAppointments,

            // packs (optional for mobile, harmless if not used)
            'packPurchases' => $packPurchases,
        ]);
    }

    // -----------------------------
    // Desktop/web view
    // -----------------------------
    return view('client_profiles.show', compact(
        'clientProfile',
        'allAppointments',
        'sessionNotes',
        'invoices',
        'responses',
        'testimonialRequest',
        'packPurchases',
        'availablePacks',
    ))->with([
        // keep compatibility with existing blade expecting `$appointments`
        'appointments' => $allAppointments,
    ]);
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

        $user = Auth::user();

        // sécurité : on s’assure que ce client appartient au thérapeute connecté
        abort_if($clientProfile->user_id !== $user->id, 403);

        $companies = CorporateClient::where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        return view('client_profiles.edit', compact('clientProfile', 'companies'));
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

        $userId = Auth::id();

        // Include first_name_billing, last_name_billing & company_id in the validation
        $data = $request->validate([
            'first_name'         => 'required|string|max:255',
            'last_name'          => 'required|string|max:255',
            'email'              => 'nullable|email|max:255',
            'phone'              => 'nullable|string|max:15',
            'birthdate'          => 'nullable|date',
            'address'            => 'nullable|string|max:255',
            'notes'              => 'nullable|string',
            'first_name_billing' => 'nullable|string|max:255',
            'last_name_billing'  => 'nullable|string|max:255',

            'company_id'         => [
                'nullable',
                Rule::exists('corporate_clients', 'id')
                    ->where('user_id', $userId),
            ],
        ]);

        // Update the client profile, including billing fields & company link
        $clientProfile->update($data);

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

    /**
     * Client portal home.
     */
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
	
	    /**
     * Créer rapidement un profil client à partir d'une réservation d'événement.
     * Appelé en AJAX depuis la page show d'un événement.
     */
    public function storeFromReservation(Request $request, Event $event, Reservation $reservation)
    {
        $user = Auth::user();

        // Sécurité : l’événement doit appartenir au thérapeute connecté
        if (!$user || $user->id !== $event->user_id) {
            abort(403);
        }

        // Sécurité : la réservation doit bien être liée à cet événement
        if ((int) $reservation->event_id !== (int) $event->id) {
            abort(404);
        }

        // Si aucun email, on ne peut pas faire le match automatique
        if (!$reservation->email) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Impossible de créer le profil : email manquant sur la réservation.',
            ], 422);
        }

        $normalizedEmail = strtolower(trim($reservation->email));

        // Vérifier si un client existe déjà avec cet email pour ce thérapeute
        $existing = ClientProfile::where('user_id', $user->id)
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if ($existing) {
            return response()->json([
                'status'              => 'exists',
                'message'             => 'Un profil client existe déjà pour cet email.',
                'client_profile_id'   => $existing->id,
                'client_profile_url'  => route('client_profiles.show', $existing),
            ]);
        }

        // Découper le nom complet en prénom / nom (simple heuristique)
        $fullName = trim($reservation->full_name ?? '');
        if ($fullName === '') {
            $firstName = 'Client';
            $lastName  = 'Événement';
        } else {
            $parts = preg_split('/\s+/', $fullName);
            $firstName = array_shift($parts);
            $lastName  = implode(' ', $parts) ?: '';
        }

        // Créer le profil minimal
        $client = ClientProfile::create([
            'user_id'    => $user->id,
            'first_name' => $firstName,
            'last_name'  => $lastName ?: '-',
            'email'      => $reservation->email,
            'phone'      => $reservation->phone,
        ]);

        return response()->json([
            'status'              => 'created',
            'message'             => 'Profil client créé avec succès.',
            'client_profile_id'   => $client->id,
            'client_profile_url'  => route('client_profiles.show', $client),
        ], 201);
    }

}
