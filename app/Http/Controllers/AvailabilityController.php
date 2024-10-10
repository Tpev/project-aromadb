<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    /**
     * Affiche une liste des disponibilités de l'utilisateur authentifié.
     */
    public function index()
    {
        $availabilities = Availability::where('user_id', Auth::id())->with('products')->get();

        return view('availabilities.index', compact('availabilities'));
    }

    /**
     * Affiche le formulaire pour créer une nouvelle disponibilité.
     */
    public function create()
    {
        // Fetch all products belonging to the authenticated user
        $products = Product::where('user_id', Auth::id())->get();

        return view('availabilities.create', compact('products'));
    }

    /**
     * Stocke une nouvelle disponibilité dans la base de données.
     */
    public function store(Request $request)
    {
        $request->validate([
            'day_of_week' => 'required|integer|between:0,6', // Adjusted based on your existing mapping
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'applies_to_all' => 'sometimes|boolean',
            'products' => 'required_if:applies_to_all,0|array',
            'products.*' => 'exists:products,id',
        ]);

        // Check for overlapping availabilities
        $overlap = Availability::where('user_id', Auth::id())
            ->where('day_of_week', $request->day_of_week)
            ->where(function($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time . ':00', $request->end_time . ':00'])
                      ->orWhereBetween('end_time', [$request->start_time . ':00', $request->end_time . ':00'])
                      ->orWhere(function($q) use ($request) {
                          $q->where('start_time', '<=', $request->start_time . ':00')
                            ->where('end_time', '>=', $request->end_time . ':00');
                      });
            })
            ->exists();

        if ($overlap) {
            return redirect()->back()->withErrors(['start_time' => 'Les heures se chevauchent avec une disponibilité existante.'])->withInput();
        }

        // Create the Availability
        $availability = Availability::create([
            'user_id' => Auth::id(),
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time . ':00', // Append seconds
            'end_time' => $request->end_time . ':00',     // Append seconds
            'applies_to_all' => $request->has('applies_to_all'),
        ]);

        // If not applying to all products, associate selected products
        if (!$availability->applies_to_all) {
            $availability->products()->sync($request->products);
        }

        return redirect()->route('availabilities.index')->with('success', 'Disponibilité ajoutée avec succès.');
    }

    /**
     * Affiche le formulaire pour éditer une disponibilité existante.
     */
    public function edit(Availability $availability)
    {
        // Fetch all products belonging to the authenticated user
        $products = Product::where('user_id', Auth::id())->get();

        // Get IDs of associated products if not applying to all
        $selectedProducts = $availability->applies_to_all ? [] : $availability->products->pluck('id')->toArray();

        return view('availabilities.edit', compact('availability', 'products', 'selectedProducts'));
    }

    /**
     * Met à jour une disponibilité existante dans la base de données.
     */
    public function update(Request $request, Availability $availability)
    {
        $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'applies_to_all' => 'sometimes|boolean',
            'products' => 'required_if:applies_to_all,0|array',
            'products.*' => 'exists:products,id',
        ]);

        // Check for overlapping availabilities excluding the current one
        $overlap = Availability::where('user_id', Auth::id())
            ->where('day_of_week', $request->day_of_week)
            ->where('id', '!=', $availability->id)
            ->where(function($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time . ':00', $request->end_time . ':00'])
                      ->orWhereBetween('end_time', [$request->start_time . ':00', $request->end_time . ':00'])
                      ->orWhere(function($q) use ($request) {
                          $q->where('start_time', '<=', $request->start_time . ':00')
                            ->where('end_time', '>=', $request->end_time . ':00');
                      });
            })
            ->exists();

        if ($overlap) {
            return redirect()->back()->withErrors(['start_time' => 'Les heures se chevauchent avec une disponibilité existante.'])->withInput();
        }

        // Update the Availability
        $availability->update([
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time . ':00', // Append seconds
            'end_time' => $request->end_time . ':00',     // Append seconds
            'applies_to_all' => $request->has('applies_to_all'),
        ]);

        // Update product associations
        if (!$availability->applies_to_all) {
            $availability->products()->sync($request->products);
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
        $availability->delete();

        return redirect()->route('availabilities.index')->with('success', 'Disponibilité supprimée avec succès.');
    }
}
