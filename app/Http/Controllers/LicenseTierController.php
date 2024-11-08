<?php

namespace App\Http\Controllers;

use App\Models\LicenseTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LicenseTierController extends Controller
{
    /**
     * Display a listing of the license tiers.
     */
    public function index()
    {
        $tiers = LicenseTier::all();
        return view('license-tiers.index', compact('tiers'));
    }

    /**
     * Show the form for creating a new license tier.
     */
    public function create()
    {
        return view('license-tiers.create');
    }

    /**
     * Store a newly created license tier in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'is_trial' => 'boolean',
            'trial_duration_days' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'features' => 'required|array',
        ]);

        LicenseTier::create($request->all());
        return redirect()->route('license-tiers.index')->with('success', 'License tier created successfully.');
    }

    /**
     * Show the form for editing the specified license tier.
     */
    public function edit(LicenseTier $licenseTier)
    {
        return view('license-tiers.edit', compact('licenseTier'));
    }

    /**
     * Update the specified license tier in storage.
     */
    public function update(Request $request, LicenseTier $licenseTier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'is_trial' => 'boolean',
            'trial_duration_days' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'features' => 'required|array',
        ]);

        $licenseTier->update($request->all());
        return redirect()->route('license-tiers.index')->with('success', 'License tier updated successfully.');
    }

    /**
     * Remove the specified license tier from storage.
     */
    public function destroy(LicenseTier $licenseTier)
    {
        $licenseTier->delete();
        return redirect()->route('license-tiers.index')->with('success', 'License tier deleted successfully.');
    }
	public function pricing()
    {
        // Vérifier si l'utilisateur est authentifié
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors('Veuillez vous connecter pour accéder à cette page.');
        }

        // Optionnel : Vérifier si l'utilisateur a un rôle spécifique
        // Par exemple, si vous utilisez Spatie Laravel Permission
        /*
        if (!Auth::user()->hasRole('therapist')) {
            Log::warning('Utilisateur non autorisé tenté d\'accéder à la page des tarifs Stripe.', [
                'user_id' => Auth::id(),
                'roles' => Auth::user()->getRoleNames(),
            ]);
            return redirect()->route('welcome')->withErrors('Accès non autorisé.');
        }
        */
        // Récupérer l'e-mail de l'utilisateur connecté
        $customer_email = Auth::user()->email;

        // Passer l'e-mail à la vue
        return view('license-tiers.pricing', compact('customer_email'));
    }
}
