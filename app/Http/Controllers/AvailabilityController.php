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

        // Check overlap (par utilisateur, même règle qu’avant)
        $overlap = Availability::where('user_id', Auth::id())
            ->where('day_of_week', $request->day_of_week)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time . ':00', $request->end_time . ':00'])
                    ->orWhereBetween('end_time', [$request->start_time . ':00', $request->end_time . ':00'])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<=', $request->start_time . ':00')
                          ->where('end_time', '>=', $request->end_time . ':00');
                    });
            })
            ->exists();

        if ($overlap) {
            return redirect()->back()
                ->withErrors(['start_time' => 'Les heures se chevauchent avec une disponibilité existante.'])
                ->withInput();
        }

        // Create
        $availability = Availability::create([
            'user_id'              => Auth::id(),
            'practice_location_id' => $request->practice_location_id, // NEW
            'day_of_week'          => $request->day_of_week,
            'start_time'           => $request->start_time . ':00',
            'end_time'             => $request->end_time . ':00',
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

        // Overlap check (exclure la dispo courante)
        $overlap = Availability::where('user_id', Auth::id())
            ->where('day_of_week', $request->day_of_week)
            ->where('id', '!=', $availability->id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time . ':00', $request->end_time . ':00'])
                    ->orWhereBetween('end_time', [$request->start_time . ':00', $request->end_time . ':00'])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<=', $request->start_time . ':00')
                          ->where('end_time', '>=', $request->end_time . ':00');
                    });
            })
            ->exists();

        if ($overlap) {
            return redirect()->back()
                ->withErrors(['start_time' => 'Les heures se chevauchent avec une disponibilité existante.'])
                ->withInput();
        }

        // Update
        $availability->update([
            'practice_location_id' => $request->practice_location_id, // NEW
            'day_of_week'          => $request->day_of_week,
            'start_time'           => $request->start_time . ':00',
            'end_time'             => $request->end_time . ':00',
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
