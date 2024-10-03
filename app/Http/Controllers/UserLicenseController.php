<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLicense;
use App\Models\LicenseTier;
use App\Models\LicenseHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserLicenseController extends Controller
{
    /**
     * Display a listing of the user's active licenses.
     */
    public function index()
    {
        $licenses = UserLicense::with('user', 'licenseTier')->get();
        return view('user-licenses.index', compact('licenses'));
    }

    /**
     * Show the form for assigning a new license to a user.
     */
    public function create()
    {
        $users = User::where('is_therapist', true)->get();
        $tiers = LicenseTier::all();
        return view('user-licenses.create', compact('users', 'tiers'));
    }

    /**
     * Store a newly assigned license to a user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'license_tier_id' => 'required|exists:license_tiers,id',
        ]);

        // Get the selected license tier and calculate expiration date
        $tier = LicenseTier::findOrFail($request->license_tier_id);
        $startDate = Carbon::now();
        $expirationDate = $startDate->copy()->addDays($tier->duration_days);

        // Create User License
        $userLicense = UserLicense::create([
            'user_id' => $request->user_id,
            'license_tier_id' => $request->license_tier_id,
            'start_date' => $startDate,
            'expiration_date' => $expirationDate,
        ]);

        // Add to License History
        LicenseHistory::create([
            'user_id' => $request->user_id,
            'license_tier_id' => $request->license_tier_id,
            'start_date' => $startDate,
            'end_date' => $expirationDate,
            'status' => 'active',
        ]);

        return redirect()->route('user-licenses.index')->with('success', 'License assigned successfully.');
    }

    /**
     * Renew an existing license.
     */
    public function renew(UserLicense $userLicense)
    {
        $tier = $userLicense->licenseTier;
        $newExpirationDate = $userLicense->expiration_date->copy()->addDays($tier->duration_days);

        // Update User License expiration date
        $userLicense->update([
            'expiration_date' => $newExpirationDate,
        ]);

        // Add to License History
        LicenseHistory::create([
            'user_id' => $userLicense->user_id,
            'license_tier_id' => $userLicense->license_tier_id,
            'start_date' => Carbon::now(),
            'end_date' => $newExpirationDate,
            'status' => 'renewed',
        ]);

        return redirect()->route('user-licenses.index')->with('success', 'License renewed successfully.');
    }

    /**
     * Show the available licenses for upgrade.
     */
public function showUpgradePage()
{
    // Récupérer toutes les licences disponibles, en excluant la licence d'essai
    $availableLicenses = LicenseTier::where('is_trial', false)->get();

    // Filtrer les licences mensuelles et annuelles en fonction du nom
    $mensuelLicenses = $availableLicenses->filter(function($license) {
        return strpos(strtolower($license->name), 'mensuelle') !== false;
    });

    $annuelLicenses = $availableLicenses->filter(function($license) {
        return strpos(strtolower($license->name), 'annuelle') !== false;
    });

    // Combiner toutes les licences pour le tableau de comparaison
    $allLicenses = $availableLicenses;

    // Retourner la vue avec les licences filtrées
    return view('upgrade.license', compact('mensuelLicenses', 'annuelLicenses', 'allLicenses'));
}




    /**
     * Process the license upgrade for a user.
     */
    public function processLicenseUpgrade(Request $request)
    {
        $request->validate([
            'license_tier_id' => 'required|exists:license_tiers,id',
        ]);

        // Fetch the selected license tier
        $licenseTier = LicenseTier::findOrFail($request->license_tier_id);

        // Calculate the expiration date based on the license tier's duration
        $expirationDate = now()->addDays($licenseTier->duration_days);

        // Get the authenticated user
        $user = Auth::user();

        // Create or update the user's license
        UserLicense::updateOrCreate(
            ['user_id' => $user->id],
            [
                'license_tier_id' => $licenseTier->id,
                'start_date' => now(),
                'expiration_date' => $expirationDate,
            ]
        );

        // Log license history
        LicenseHistory::create([
            'user_id' => $user->id,
            'license_tier_id' => $licenseTier->id,
            'start_date' => now(),
            'end_date' => $expirationDate,
            'status' => 'upgraded',
        ]);

        // Redirect back to the dashboard with a success message
        return redirect()->route('dashboard')->with('success', 'Votre licence a été mise à jour avec succès.');
    }
}
