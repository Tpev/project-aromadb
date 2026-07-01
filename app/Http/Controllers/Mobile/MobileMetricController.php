<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\Metric;
use App\Models\MetricEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileMetricController extends Controller
{
    public function index(ClientProfile $clientProfile)
    {
        $this->ensureOwnsClient($clientProfile);

        $metrics = $clientProfile->metrics()
            ->with(['entries' => fn ($query) => $query->orderByDesc('entry_date')->orderByDesc('id')])
            ->orderBy('name')
            ->get();

        return view('mobile.metrics.index', compact('clientProfile', 'metrics'));
    }

    public function create(ClientProfile $clientProfile)
    {
        $this->ensureOwnsClient($clientProfile);

        return view('mobile.metrics.form', [
            'clientProfile' => $clientProfile,
            'metric' => new Metric(),
            'action' => route('mobile.metrics.store', $clientProfile),
            'method' => 'POST',
            'title' => 'Nouvelle mesure',
            'submitLabel' => 'Creer',
        ]);
    }

    public function store(Request $request, ClientProfile $clientProfile)
    {
        $this->ensureOwnsClient($clientProfile);

        $metric = $clientProfile->metrics()->create($this->validatedMetric($request));

        return redirect()
            ->route('mobile.metrics.show', [$clientProfile, $metric])
            ->with('success', 'Mesure creee.');
    }

    public function show(ClientProfile $clientProfile, Metric $metric)
    {
        $this->ensureMetricBelongsToClient($clientProfile, $metric);

        $entries = $metric->entries()
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->get();

        return view('mobile.metrics.show', compact('clientProfile', 'metric', 'entries'));
    }

    public function edit(ClientProfile $clientProfile, Metric $metric)
    {
        $this->ensureMetricBelongsToClient($clientProfile, $metric);

        return view('mobile.metrics.form', [
            'clientProfile' => $clientProfile,
            'metric' => $metric,
            'action' => route('mobile.metrics.update', [$clientProfile, $metric]),
            'method' => 'PUT',
            'title' => 'Modifier la mesure',
            'submitLabel' => 'Enregistrer',
        ]);
    }

    public function update(Request $request, ClientProfile $clientProfile, Metric $metric)
    {
        $this->ensureMetricBelongsToClient($clientProfile, $metric);

        $metric->update($this->validatedMetric($request));

        return redirect()
            ->route('mobile.metrics.show', [$clientProfile, $metric])
            ->with('success', 'Mesure mise a jour.');
    }

    public function destroy(ClientProfile $clientProfile, Metric $metric)
    {
        $this->ensureMetricBelongsToClient($clientProfile, $metric);

        $metric->delete();

        return redirect()
            ->route('mobile.metrics.index', $clientProfile)
            ->with('success', 'Mesure supprimee.');
    }

    public function createEntry(ClientProfile $clientProfile, Metric $metric)
    {
        $this->ensureMetricBelongsToClient($clientProfile, $metric);

        return view('mobile.metrics.entry-form', [
            'clientProfile' => $clientProfile,
            'metric' => $metric,
            'metricEntry' => new MetricEntry(['entry_date' => now()->toDateString()]),
            'action' => route('mobile.metrics.entries.store', [$clientProfile, $metric]),
            'method' => 'POST',
            'title' => 'Nouvelle valeur',
            'submitLabel' => 'Ajouter',
        ]);
    }

    public function storeEntry(Request $request, ClientProfile $clientProfile, Metric $metric)
    {
        $this->ensureMetricBelongsToClient($clientProfile, $metric);

        $metric->entries()->create($this->validatedEntry($request));

        return redirect()
            ->route('mobile.metrics.show', [$clientProfile, $metric])
            ->with('success', 'Valeur ajoutee.');
    }

    public function editEntry(ClientProfile $clientProfile, Metric $metric, MetricEntry $metricEntry)
    {
        $this->ensureEntryBelongsToMetric($clientProfile, $metric, $metricEntry);

        return view('mobile.metrics.entry-form', [
            'clientProfile' => $clientProfile,
            'metric' => $metric,
            'metricEntry' => $metricEntry,
            'action' => route('mobile.metrics.entries.update', [$clientProfile, $metric, $metricEntry]),
            'method' => 'PUT',
            'title' => 'Modifier la valeur',
            'submitLabel' => 'Enregistrer',
        ]);
    }

    public function updateEntry(Request $request, ClientProfile $clientProfile, Metric $metric, MetricEntry $metricEntry)
    {
        $this->ensureEntryBelongsToMetric($clientProfile, $metric, $metricEntry);

        $metricEntry->update($this->validatedEntry($request));

        return redirect()
            ->route('mobile.metrics.show', [$clientProfile, $metric])
            ->with('success', 'Valeur mise a jour.');
    }

    public function destroyEntry(ClientProfile $clientProfile, Metric $metric, MetricEntry $metricEntry)
    {
        $this->ensureEntryBelongsToMetric($clientProfile, $metric, $metricEntry);

        $metricEntry->delete();

        return redirect()
            ->route('mobile.metrics.show', [$clientProfile, $metric])
            ->with('success', 'Valeur supprimee.');
    }

    protected function validatedMetric(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'goal' => ['nullable', 'numeric'],
        ]);
    }

    protected function validatedEntry(Request $request): array
    {
        return $request->validate([
            'entry_date' => ['required', 'date'],
            'value' => ['required', 'numeric'],
        ]);
    }

    protected function ensureOwnsClient(ClientProfile $clientProfile): void
    {
        abort_unless((int) $clientProfile->user_id === (int) Auth::id(), 403);
    }

    protected function ensureMetricBelongsToClient(ClientProfile $clientProfile, Metric $metric): void
    {
        $this->ensureOwnsClient($clientProfile);
        abort_unless((int) $metric->client_profile_id === (int) $clientProfile->id, 403);
    }

    protected function ensureEntryBelongsToMetric(ClientProfile $clientProfile, Metric $metric, MetricEntry $metricEntry): void
    {
        $this->ensureMetricBelongsToClient($clientProfile, $metric);
        abort_unless((int) $metricEntry->metric_id === (int) $metric->id, 403);
    }
}
