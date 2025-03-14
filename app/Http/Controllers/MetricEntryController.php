<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\Metric;
use App\Models\MetricEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MetricEntryController extends Controller
{
    // List all entries for a given metric (if needed)
    public function index(ClientProfile $clientProfile, Metric $metric)
    {
        $this->checkOwnership($clientProfile, $metric);

        $entries = $metric->entries()->orderBy('entry_date', 'desc')->get();
        return view('metric_entries.index', compact('clientProfile', 'metric', 'entries'));
    }

    // Show form for creating a new entry for a metric
    public function create(ClientProfile $clientProfile, Metric $metric)
    {
        $this->checkOwnership($clientProfile, $metric);

        return view('metric_entries.create', compact('clientProfile','metric'));
    }

    // Store a new entry
    public function store(Request $request, ClientProfile $clientProfile, Metric $metric)
    {
        $this->checkOwnership($clientProfile, $metric);

        $data = $request->validate([
            'entry_date' => 'required|date',
            'value'      => 'required|numeric',
        ]);

        $data['metric_id'] = $metric->id;
        MetricEntry::create($data);

        return redirect()
            ->route('client_profiles.metrics.show', [$clientProfile, $metric])
            ->with('success', 'Nouvelle entrée ajoutée avec succès !');
    }

    // Show a single metric entry (optional)
    public function show(ClientProfile $clientProfile, Metric $metric, MetricEntry $metricEntry)
    {
        $this->checkOwnership($clientProfile, $metric, $metricEntry);

        return view('metric_entries.show', compact('clientProfile','metric','metricEntry'));
    }

    // Show form to edit an existing entry
    public function edit(ClientProfile $clientProfile, Metric $metric, MetricEntry $metricEntry)
    {
        $this->checkOwnership($clientProfile, $metric, $metricEntry);

        return view('metric_entries.edit', compact('clientProfile', 'metric', 'metricEntry'));
    }

    // Update an existing entry
    public function update(Request $request, ClientProfile $clientProfile, Metric $metric, MetricEntry $metricEntry)
    {
        $this->checkOwnership($clientProfile, $metric, $metricEntry);

        $data = $request->validate([
            'entry_date' => 'required|date',
            'value'      => 'required|numeric',
        ]);

        $metricEntry->update($data);

        return redirect()
            ->route('client_profiles.metrics.show', [$clientProfile, $metric])
            ->with('success', 'Entrée mise à jour avec succès !');
    }

    // Delete an entry
    public function destroy(ClientProfile $clientProfile, Metric $metric, MetricEntry $metricEntry)
    {
        $this->checkOwnership($clientProfile, $metric, $metricEntry);

        $metricEntry->delete();

        return redirect()
            ->route('client_profiles.metrics.show', [$clientProfile, $metric])
            ->with('success', 'Entrée supprimée avec succès !');
    }

    /**
     * Check if the authenticated user owns the given ClientProfile
     * and that the Metric + (optionally) MetricEntry belong together.
     *
     * @param  ClientProfile $clientProfile
     * @param  Metric        $metric
     * @param  MetricEntry|null $metricEntry
     * @return void
     */
    private function checkOwnership(
        ClientProfile $clientProfile,
        Metric $metric,
        MetricEntry $metricEntry = null
    ) {
        // 1) Confirm the user owns this ClientProfile
        if ($clientProfile->user_id !== Auth::id()) {
            abort(403, 'Accès refusé.');
        }

        // 2) Confirm the Metric belongs to this ClientProfile
        if ($metric->client_profile_id !== $clientProfile->id) {
            abort(403, 'Accès refusé. Paramètres de mesure invalides.');
        }

        // 3) If a MetricEntry was provided, confirm it belongs to this Metric
        if ($metricEntry && $metricEntry->metric_id !== $metric->id) {
            abort(403, 'Accès refusé. Paramètres d’entrée invalides.');
        }
    }
}
