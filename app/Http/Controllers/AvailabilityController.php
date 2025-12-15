<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\Product;
use App\Models\PracticeLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    /**
     * Affiche une liste des disponibilités de l'utilisateur authentifié.
     */
    public function index()
    {
        if (Auth::user()->license_status === 'inactive') {
            return redirect('/license-tiers/pricing');
        }

        $this->authorize('viewAny', Availability::class);

        $availabilities = Availability::where('user_id', Auth::id())
            ->with(['products', 'practiceLocation'])
            ->get();

        return view('availabilities.index', compact('availabilities'));
    }

    /**
     * Affiche le formulaire pour créer une nouvelle disponibilité.
     */
    public function create()
    {
        $this->authorize('create', Availability::class);

        // Produits + Lieux de l'utilisateur
        $products  = Product::where('user_id', Auth::id())->get();
        $locations = PracticeLocation::where('user_id', Auth::id())
            ->orderByDesc('is_primary')
            ->orderBy('label')
            ->get();

        return view('availabilities.create', compact('products', 'locations'));
    }

    /**
     * Stocke une nouvelle disponibilité dans la base de données.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Availability::class);

        $request->validate([
            'day_of_week'          => 'required|integer|between:0,6',
            'start_time'           => 'required|date_format:H:i',
            'end_time'             => 'required|date_format:H:i|after:start_time',
            'applies_to_all'       => 'sometimes|boolean',
            'products'             => 'required_if:applies_to_all,0|array',
            'products.*'           => 'exists:products,id',
            'practice_location_id' => 'nullable|integer|exists:practice_locations,id',
        ]);

        // Vérifier que le lieu sélectionné appartient bien au user (si fourni)
        if ($request->filled('practice_location_id')) {
            $owns = PracticeLocation::where('id', $request->practice_location_id)
                ->where('user_id', Auth::id())
                ->exists();

            if (!$owns) {
                return redirect()->back()
                    ->withErrors(['practice_location_id' => 'Lieu invalide.'])
                    ->withInput();
            }
        }

        $start = $request->start_time . ':00';
        $end   = $request->end_time . ':00';

        /**
         * Overlap logic (NEW):
         * - same day + overlapping time
         * - AND (location rules):
         *   - if new dispo is global (practice_location_id = null) => it conflicts with ANY existing dispo
         *   - if new dispo is for a specific location => it conflicts with:
         *        a) existing dispos for the SAME location
         *        b) existing global dispos (null)
         *   => but it does NOT conflict with other locations (so overlap across locations is allowed)
         */
        $overlap = Availability::where('user_id', Auth::id())
            ->where('day_of_week', $request->day_of_week)
            ->where(function ($locQ) use ($request) {
                // new dispo = GLOBAL -> no location filter => conflicts with any location
                if (!$request->filled('practice_location_id')) {
                    return;
                }

                // new dispo = specific location -> conflicts with same location OR global
                $locQ->whereNull('practice_location_id')
                    ->orWhere('practice_location_id', $request->practice_location_id);
            })
            ->where(function ($timeQ) use ($start, $end) {
                $timeQ->whereBetween('start_time', [$start, $end])
                    ->orWhereBetween('end_time', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_time', '<=', $start)
                          ->where('end_time', '>=', $end);
                    });
            })
            ->exists();

        if ($overlap) {
            return redirect()->back()
                ->withErrors(['start_time' => 'Les heures se chevauchent avec une disponibilité existante (même lieu ou lieu global).'])
                ->withInput();
        }

        // Create
        $availability = Availability::create([
            'user_id'              => Auth::id(),
            'practice_location_id' => $request->practice_location_id,
            'day_of_week'          => $request->day_of_week,
            'start_time'           => $start,
            'end_time'             => $end,
            'applies_to_all'       => $request->has('applies_to_all'),
        ]);

        if (!$availability->applies_to_all) {
            $availability->products()->sync($request->products ?? []);
        }

        return redirect()->route('availabilities.index')->with('success', 'Disponibilité ajoutée avec succès.');
    }

    /**
     * Affiche le formulaire pour éditer une disponibilité existante.
     */
    public function edit(Availability $availability)
    {
        $this->authorize('update', $availability);

        // Produits + Lieux de l'utilisateur
        $products  = Product::where('user_id', Auth::id())->get();
        $locations = PracticeLocation::where('user_id', Auth::id())
            ->orderByDesc('is_primary')
            ->orderBy('label')
            ->get();

        $selectedProducts = $availability->applies_to_all
            ? []
            : $availability->products->pluck('id')->toArray();

        return view('availabilities.edit', compact('availability', 'products', 'selectedProducts', 'locations'));
    }

    /**
     * Met à jour une disponibilité existante dans la base de données.
     */
    public function update(Request $request, Availability $availability)
    {
        $this->authorize('update', $availability);

        $request->validate([
            'day_of_week'          => 'required|integer|between:0,6',
            'start_time'           => 'required|date_format:H:i',
            'end_time'             => 'required|date_format:H:i|after:start_time',
            'applies_to_all'       => 'sometimes|boolean',
            'products'             => 'required_if:applies_to_all,0|array',
            'products.*'           => 'exists:products,id',
            'practice_location_id' => 'nullable|integer|exists:practice_locations,id',
        ]);

        // Vérifier ownership du lieu (si fourni)
        if ($request->filled('practice_location_id')) {
            $owns = PracticeLocation::where('id', $request->practice_location_id)
                ->where('user_id', Auth::id())
                ->exists();

            if (!$owns) {
                return redirect()->back()
                    ->withErrors(['practice_location_id' => 'Lieu invalide.'])
                    ->withInput();
            }
        }

        $start = $request->start_time . ':00';
        $end   = $request->end_time . ':00';

        // Overlap check (NEW rules + exclude current)
        $overlap = Availability::where('user_id', Auth::id())
            ->where('day_of_week', $request->day_of_week)
            ->where('id', '!=', $availability->id)
            ->where(function ($locQ) use ($request) {
                if (!$request->filled('practice_location_id')) {
                    return;
                }

                $locQ->whereNull('practice_location_id')
                    ->orWhere('practice_location_id', $request->practice_location_id);
            })
            ->where(function ($timeQ) use ($start, $end) {
                $timeQ->whereBetween('start_time', [$start, $end])
                    ->orWhereBetween('end_time', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_time', '<=', $start)
                          ->where('end_time', '>=', $end);
                    });
            })
            ->exists();

        if ($overlap) {
            return redirect()->back()
                ->withErrors(['start_time' => 'Les heures se chevauchent avec une disponibilité existante (même lieu ou lieu global).'])
                ->withInput();
        }

        // Update
        $availability->update([
            'practice_location_id' => $request->practice_location_id,
            'day_of_week'          => $request->day_of_week,
            'start_time'           => $start,
            'end_time'             => $end,
            'applies_to_all'       => $request->has('applies_to_all'),
        ]);

        if (!$availability->applies_to_all) {
            $availability->products()->sync($request->products ?? []);
        } else {
            $availability->products()->detach();
        }

        return redirect()->route('availabilities.index')->with('success', 'Disponibilité mise à jour avec succès.');
    }

    /**
     * Supprime une disponibilité de la base de données.
     */
    public function destroy(Availability $availability)
    {
        $this->authorize('delete', $availability);

        $availability->delete();

        return redirect()->route('availabilities.index')->with('success', 'Disponibilité supprimée avec succès.');
    }
}
