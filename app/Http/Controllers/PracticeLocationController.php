<?php

namespace App\Http\Controllers;

use App\Models\PracticeLocation;
use App\Services\CabinetAccessService;
use App\Services\FrenchAddressGeocodingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view('practice_locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
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
        $data['is_shared'] = $this->cabinetAccessService->enabled() && (bool) ($data['is_shared'] ?? false);
        $data['shared_enabled_at'] = $data['is_shared'] ? now() : null;

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

        $data['latitude'] = $coordinates['latitude'] ?? null;
        $data['longitude'] = $coordinates['longitude'] ?? null;

        $location = PracticeLocation::create($data);

        if ($data['is_shared']) {
            $this->cabinetAccessService->ensureOwnerMembership($location);
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

        $location = $practice_location->load([
            'owner',
            'memberships.user',
            'pendingInvites.invitedUser',
        ]);

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
        $data['is_shared'] = $this->cabinetAccessService->enabled() && (bool) ($data['is_shared'] ?? false);
        $data['shared_enabled_at'] = $data['is_shared']
            ? ($practice_location->shared_enabled_at ?? now())
            : null;

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

        $data['latitude'] = $coordinates['latitude'] ?? null;
        $data['longitude'] = $coordinates['longitude'] ?? null;

        $practice_location->update($data);

        if ($practice_location->is_shared) {
            $this->cabinetAccessService->ensureOwnerMembership($practice_location);
        } else {
            $this->cabinetAccessService->cancelPendingInvites($practice_location);
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
