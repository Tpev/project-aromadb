<?php

namespace App\Http\Controllers;

use App\Models\SpecialAvailability;
use App\Models\Product;
use App\Models\PracticeLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SpecialAvailabilityController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    /**
     * Liste des disponibilités ponctuelles.
     */
    public function index()
    {
        if (Auth::user()->license_status === 'inactive') {
            return redirect('/license-tiers/pricing');
        }

        $this->authorize('viewAny', SpecialAvailability::class);

        $specialAvailabilities = SpecialAvailability::where('user_id', Auth::id())
            ->with(['products', 'practiceLocation'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view('special_availabilities.index', compact('specialAvailabilities'));
    }

    /**
     * Formulaire pour créer des disponibilités ponctuelles (multi-dates).
     */
    public function create()
    {
        $this->authorize('create', SpecialAvailability::class);

        $products  = Product::where('user_id', Auth::id())->get();
        $locations = PracticeLocation::where('user_id', Auth::id())
            ->orderByDesc('is_primary')
            ->orderBy('label')
            ->get();

        return view('special_availabilities.create', compact('products', 'locations'));
    }

    /**
     * Enregistre plusieurs créneaux ponctuels d'un coup (multi-dates mêmes heures).
     */
    public function store(Request $request)
    {
        $this->authorize('create', SpecialAvailability::class);

        $request->validate([
            'dates'               => 'required|string',       // ex: "2025-11-17,2025-11-24"
            'start_time'          => 'required|date_format:H:i',
            'end_time'            => 'required|date_format:H:i|after:start_time',
            'applies_to_all'      => 'sometimes|boolean',
            'products'            => 'required_if:applies_to_all,0|array',
            'products.*'          => 'exists:products,id',
            'practice_location_id'=> 'nullable|integer|exists:practice_locations,id',
        ]);

        // Vérifier que le lieu appartient bien à l'utilisateur
        if ($request->filled('practice_location_id')) {
            $owns = PracticeLocation::where('id', $request->practice_location_id)
                ->where('user_id', Auth::id())
                ->exists();

            if (!$owns) {
                return back()
                    ->withErrors(['practice_location_id' => 'Lieu invalide.'])
                    ->withInput();
            }
        }

        // Parser les dates (séparées par virgule)
        $rawDates = explode(',', $request->dates);
        $dates = [];

        foreach ($rawDates as $rawDate) {
            $rawDate = trim($rawDate);
            if (!$rawDate) {
                continue;
            }

            try {
                $date = Carbon::createFromFormat('Y-m-d', $rawDate);
            } catch (\Exception $e) {
                return back()
                    ->withErrors(['dates' => "La date '{$rawDate}' est invalide."])
                    ->withInput();
            }

            $dates[] = $date;
        }

        if (empty($dates)) {
            return back()
                ->withErrors(['dates' => 'Veuillez sélectionner au moins une date.'])
                ->withInput();
        }

        // Vérifier les chevauchements pour chaque date
        $userId = Auth::id();
        $start  = $request->start_time . ':00';
        $end    = $request->end_time . ':00';

        foreach ($dates as $date) {
            $overlap = SpecialAvailability::where('user_id', $userId)
                ->whereDate('date', $date->toDateString())
                ->where(function ($query) use ($start, $end, $request) {
                    if ($request->filled('practice_location_id')) {
                        $query->where('practice_location_id', $request->practice_location_id);
                    }

                    $query->where(function ($q) use ($start, $end) {
                        $q->whereBetween('start_time', [$start, $end])
                          ->orWhereBetween('end_time', [$start, $end])
                          ->orWhere(function ($qq) use ($start, $end) {
                              $qq->where('start_time', '<=', $start)
                                 ->where('end_time', '>=', $end);
                          });
                    });
                })
                ->exists();

            if ($overlap) {
                return back()
                    ->withErrors([
                        'start_time' => "Les heures se chevauchent déjà avec une disponibilité ponctuelle existante le " . $date->format('d/m/Y') . ".",
                    ])
                    ->withInput();
            }
        }

        // Création de toutes les lignes en transaction
        DB::transaction(function () use ($dates, $userId, $request, $start, $end) {
            foreach ($dates as $date) {
                $special = SpecialAvailability::create([
                    'user_id'              => $userId,
                    'date'                 => $date->toDateString(),
                    'start_time'           => $start,
                    'end_time'             => $end,
                    'applies_to_all'       => $request->has('applies_to_all'),
                    'practice_location_id' => $request->practice_location_id,
                ]);

                if (!$special->applies_to_all) {
                    $special->products()->sync($request->products ?? []);
                }
            }
        });

        return redirect()
            ->route('special-availabilities.index')
            ->with('success', 'Disponibilités ponctuelles ajoutées avec succès.');
    }

    /**
     * Édition d’un seul créneau ponctuel.
     */
    public function edit(SpecialAvailability $specialAvailability)
    {
        $this->authorize('update', $specialAvailability);

        $products  = Product::where('user_id', Auth::id())->get();
        $locations = PracticeLocation::where('user_id', Auth::id())
            ->orderByDesc('is_primary')
            ->orderBy('label')
            ->get();

        $selectedProducts = $specialAvailability->applies_to_all
            ? []
            : $specialAvailability->products->pluck('id')->toArray();

        return view('special_availabilities.edit', compact(
            'specialAvailability',
            'products',
            'locations',
            'selectedProducts'
        ));
    }

    /**
     * Mise à jour d’un créneau ponctuel (une seule date).
     */
    public function update(Request $request, SpecialAvailability $specialAvailability)
    {
        $this->authorize('update', $specialAvailability);

        $request->validate([
            'date'                => 'required|date_format:Y-m-d',
            'start_time'          => 'required|date_format:H:i',
            'end_time'            => 'required|date_format:H:i|after:start_time',
            'applies_to_all'      => 'sometimes|boolean',
            'products'            => 'required_if:applies_to_all,0|array',
            'products.*'          => 'exists:products,id',
            'practice_location_id'=> 'nullable|integer|exists:practice_locations,id',
        ]);

        if ($request->filled('practice_location_id')) {
            $owns = PracticeLocation::where('id', $request->practice_location_id)
                ->where('user_id', Auth::id())
                ->exists();

            if (!$owns) {
                return back()
                    ->withErrors(['practice_location_id' => 'Lieu invalide.'])
                    ->withInput();
            }
        }

        $userId = Auth::id();
        $start  = $request->start_time . ':00';
        $end    = $request->end_time . ':00';

        $overlap = SpecialAvailability::where('user_id', $userId)
            ->whereDate('date', $request->date)
            ->where('id', '!=', $specialAvailability->id)
            ->where(function ($query) use ($start, $end, $request) {
                if ($request->filled('practice_location_id')) {
                    $query->where('practice_location_id', $request->practice_location_id);
                }

                $query->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_time', [$start, $end])
                      ->orWhereBetween('end_time', [$start, $end])
                      ->orWhere(function ($qq) use ($start, $end) {
                          $qq->where('start_time', '<=', $start)
                             ->where('end_time', '>=', $end);
                      });
                });
            })
            ->exists();

        if ($overlap) {
            return back()
                ->withErrors([
                    'start_time' => 'Les heures se chevauchent avec une autre disponibilité ponctuelle.',
                ])
                ->withInput();
        }

        $specialAvailability->update([
            'date'                 => $request->date,
            'start_time'           => $start,
            'end_time'             => $end,
            'applies_to_all'       => $request->has('applies_to_all'),
            'practice_location_id' => $request->practice_location_id,
        ]);

        if (!$specialAvailability->applies_to_all) {
            $specialAvailability->products()->sync($request->products ?? []);
        } else {
            $specialAvailability->products()->detach();
        }

        return redirect()
            ->route('special-availabilities.index')
            ->with('success', 'Disponibilité ponctuelle mise à jour avec succès.');
    }

    /**
     * Suppression d’un créneau ponctuel.
     */
    public function destroy(SpecialAvailability $specialAvailability)
    {
        $this->authorize('delete', $specialAvailability);

        $specialAvailability->delete();

        return redirect()
            ->route('special-availabilities.index')
            ->with('success', 'Disponibilité ponctuelle supprimée avec succès.');
    }
}
