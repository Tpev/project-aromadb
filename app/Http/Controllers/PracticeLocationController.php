<?php

namespace App\Http\Controllers;

use App\Models\PracticeLocation;
use App\Services\CabinetAccessService;
use App\Services\FrenchAddressGeocodingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PracticeLocationController extends Controller
{
    public function __construct(
        private readonly CabinetAccessService $cabinetAccessService,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = $this->cabinetAccessService->accessibleLocations(Auth::user());

        if (request()->routeIs('mobile.*') || request()->is('mobile/*')) {
            $locations->loadCount(['appointments', 'availabilities']);

            return view('mobile.practice-locations.index', compact('locations'));
        }

        return view('practice_locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (request()->routeIs('mobile.*') || request()->is('mobile/*')) {
            return view('mobile.practice-locations.form', [
                'title' => 'Nouveau lieu',
                'location' => new PracticeLocation([
                    'country' => 'FR',
                    'is_primary' => ! PracticeLocation::where('user_id', Auth::id())->exists(),
                ]),
                'action' => route('mobile.practice-locations.store'),
                'method' => 'POST',
                'submitLabel' => 'Creer',
                'sharedCabinetsEnabled' => $this->cabinetAccessService->enabled(),
            ]);
        }

        return view('practice_locations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, FrenchAddressGeocodingService $geocodingService)
    {
        $data = $request->validate([
            'label'         => ['required','string','max:255'],
            'address_line1' => ['required','string','max:255'],
            'address_line2' => ['nullable','string','max:255'],
            'postal_code'   => ['nullable','string','max:20'],
            'city'          => ['nullable','string','max:255'],
            'country'       => ['required','string','size:2'],
            'is_primary'    => ['nullable','boolean'],
            'is_shared'     => ['nullable','boolean'],
        ]);

        $data['user_id'] = Auth::id();
        $data['is_primary'] = (bool)($data['is_primary'] ?? false);

        if (Schema::hasColumn('practice_locations', 'is_shared')) {
            $data['is_shared'] = $this->cabinetAccessService->enabled() && (bool) ($data['is_shared'] ?? false);

            if (Schema::hasColumn('practice_locations', 'shared_enabled_at')) {
                $data['shared_enabled_at'] = $data['is_shared'] ? now() : null;
            }
        } else {
            unset($data['is_shared']);
        }

        // Si défini comme principal → mettre les autres à false
        if ($data['is_primary']) {
            PracticeLocation::where('user_id', Auth::id())->update(['is_primary' => false]);
        }

        $coordinates = $geocodingService->geocodeAddressParts([
            $data['address_line1'] ?? null,
            $data['address_line2'] ?? null,
            trim(($data['postal_code'] ?? '') . ' ' . ($data['city'] ?? '')),
            $data['country'] ?? null,
        ], false);

        if (Schema::hasColumn('practice_locations', 'latitude')) {
            $data['latitude'] = $coordinates['latitude'] ?? null;
        }

        if (Schema::hasColumn('practice_locations', 'longitude')) {
            $data['longitude'] = $coordinates['longitude'] ?? null;
        }

        $location = PracticeLocation::create($data);

        if ($data['is_shared'] ?? false) {
            $this->cabinetAccessService->ensureOwnerMembership($location);
        }

        if ($request->routeIs('mobile.*') || $request->is('mobile/*')) {
            return redirect()->route('mobile.practice-locations.index')
                ->with('success', 'Lieu cree avec succes.');
        }

        return redirect()->route('practice-locations.index')
            ->with('success', 'Cabinet créé avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PracticeLocation $practice_location)
    {
        $this->authorizeLocation($practice_location);

        $relations = ['owner'];

        if ($this->cabinetAccessService->enabled()) {
            $relations[] = 'memberships.user';
            $relations[] = 'pendingInvites.invitedUser';
        }

        $location = $practice_location->load($relations);

        if (request()->routeIs('mobile.*') || request()->is('mobile/*')) {
            return view('mobile.practice-locations.form', [
                'title' => 'Modifier le lieu',
                'location' => $location,
                'action' => route('mobile.practice-locations.update', $location),
                'method' => 'PUT',
                'submitLabel' => 'Enregistrer',
                'sharedCabinetsEnabled' => $this->cabinetAccessService->enabled(),
            ]);
        }

        return view('practice_locations.edit', compact('location'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PracticeLocation $practice_location, FrenchAddressGeocodingService $geocodingService)
    {
        $this->authorizeLocation($practice_location);

        $data = $request->validate([
            'label'         => ['required','string','max:255'],
            'address_line1' => ['required','string','max:255'],
            'address_line2' => ['nullable','string','max:255'],
            'postal_code'   => ['nullable','string','max:20'],
            'city'          => ['nullable','string','max:255'],
            'country'       => ['required','string','size:2'],
            'is_primary'    => ['nullable','boolean'],
            'is_shared'     => ['nullable','boolean'],
        ]);

        $data['is_primary'] = (bool)($data['is_primary'] ?? false);

        if (Schema::hasColumn('practice_locations', 'is_shared')) {
            $data['is_shared'] = $this->cabinetAccessService->enabled() && (bool) ($data['is_shared'] ?? false);

            if (Schema::hasColumn('practice_locations', 'shared_enabled_at')) {
                $data['shared_enabled_at'] = $data['is_shared']
                    ? ($practice_location->shared_enabled_at ?? now())
                    : null;
            }
        } else {
            unset($data['is_shared']);
        }

        if ($data['is_primary']) {
            PracticeLocation::where('user_id', Auth::id())
                ->where('id', '!=', $practice_location->id)
                ->update(['is_primary' => false]);
        }

        $coordinates = $geocodingService->geocodeAddressParts([
            $data['address_line1'] ?? null,
            $data['address_line2'] ?? null,
            trim(($data['postal_code'] ?? '') . ' ' . ($data['city'] ?? '')),
            $data['country'] ?? null,
        ], false);

        if (Schema::hasColumn('practice_locations', 'latitude')) {
            $data['latitude'] = $coordinates['latitude'] ?? null;
        }

        if (Schema::hasColumn('practice_locations', 'longitude')) {
            $data['longitude'] = $coordinates['longitude'] ?? null;
        }

        $practice_location->update($data);

        if ($data['is_shared'] ?? false) {
            $this->cabinetAccessService->ensureOwnerMembership($practice_location);
        } else {
            $this->cabinetAccessService->cancelPendingInvites($practice_location);
        }

        if ($request->routeIs('mobile.*') || $request->is('mobile/*')) {
            return redirect()->route('mobile.practice-locations.index')
                ->with('success', 'Lieu mis a jour avec succes.');
        }

        return redirect()->route('practice-locations.index')
            ->with('success', 'Cabinet mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PracticeLocation $practice_location)
    {
        $this->authorizeLocation($practice_location);

        $practice_location->delete();

        if (request()->routeIs('mobile.*') || request()->is('mobile/*')) {
            return redirect()->route('mobile.practice-locations.index')
                ->with('success', 'Lieu supprime avec succes.');
        }

        return redirect()->route('practice-locations.index')
            ->with('success', 'Cabinet supprimé avec succès.');
    }

    /**
     * Vérifie que le cabinet appartient au user connecté.
     */
    private function authorizeLocation(PracticeLocation $location): void
    {
        if (!$this->cabinetAccessService->canManageLocation(Auth::user(), $location)) {
            abort(403, 'Accès refusé');
        }
    }
}
