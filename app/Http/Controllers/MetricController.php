<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\Metric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MetricController extends Controller
{
    public function index(ClientProfile $clientProfile)
    {
        // Ownership check
        if ($clientProfile->user_id !== Auth::id()) {
            abort(403, 'Accès refusé.');
        }

        $metrics = $clientProfile->metrics;
        return view('metrics.index', compact('metrics', 'clientProfile'));
    }

    public function create(ClientProfile $clientProfile)
    {
        if ($clientProfile->user_id !== Auth::id()) {
            abort(403, 'Accès refusé.');
        }

        return view('metrics.create', compact('clientProfile'));
    }

    public function store(Request $request, ClientProfile $clientProfile)
    {
        if ($clientProfile->user_id !== Auth::id()) {
            abort(403, 'Accès refusé.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'goal' => 'nullable|numeric',
        ]);

        $metric = $clientProfile->metrics()->create($data);

        return redirect()
            ->route('client_profiles.metrics.index', $clientProfile)
            ->with('success', 'Mesure créée avec succès !');
    }

    public function show(ClientProfile $clientProfile, Metric $metric)
    {
        if ($clientProfile->user_id !== Auth::id()) {
            abort(403, 'Accès refusé.');
        }
        if ($metric->client_profile_id !== $clientProfile->id) {
            abort(403, 'Paramètres de mesure invalides.');
        }

        $entries = $metric->entries;
        return view('metrics.show', compact('clientProfile', 'metric', 'entries'));
    }

    /**
     * Show the form for editing a specific metric.
     */
    public function edit(ClientProfile $clientProfile, Metric $metric)
    {
        // Check ownership
        if ($clientProfile->user_id !== Auth::id()) {
            abort(403, 'Accès refusé.');
        }
        // Confirm that the metric belongs to this clientProfile
        if ($metric->client_profile_id !== $clientProfile->id) {
            abort(403, 'Paramètres de mesure invalides.');
        }

        return view('metrics.edit', compact('clientProfile', 'metric'));
    }

    /**
     * Update the specified metric in storage.
     */
    public function update(Request $request, ClientProfile $clientProfile, Metric $metric)
    {
        if ($clientProfile->user_id !== Auth::id()) {
            abort(403, 'Accès refusé.');
        }
        if ($metric->client_profile_id !== $clientProfile->id) {
            abort(403, 'Paramètres de mesure invalides.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'goal' => 'nullable|numeric',
        ]);

        $metric->update($data);

        return redirect()
            ->route('client_profiles.metrics.index', $clientProfile)
            ->with('success', 'Mesure mise à jour avec succès !');
    }

    public function destroy(ClientProfile $clientProfile, Metric $metric)
    {
        if ($clientProfile->user_id !== Auth::id()) {
            abort(403, 'Accès refusé.');
        }
        if ($metric->client_profile_id !== $clientProfile->id) {
            abort(403, 'Paramètres de mesure invalides.');
        }

        $metric->delete();

        return redirect()
            ->route('client_profiles.metrics.index', $clientProfile)
            ->with('success', 'Mesure supprimée avec succès !');
    }
}
