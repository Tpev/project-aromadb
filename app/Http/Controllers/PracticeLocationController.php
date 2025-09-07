<?php

namespace App\Http\Controllers;

use App\Models\PracticeLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PracticeLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = PracticeLocation::where('user_id', Auth::id())
            ->orderByDesc('is_primary')
            ->orderBy('label')
            ->get();

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
    public function store(Request $request)
    {
        $data = $request->validate([
            'label'         => ['required','string','max:255'],
            'address_line1' => ['required','string','max:255'],
            'address_line2' => ['nullable','string','max:255'],
            'postal_code'   => ['nullable','string','max:20'],
            'city'          => ['nullable','string','max:255'],
            'country'       => ['required','string','size:2'],
            'is_primary'    => ['nullable','boolean'],
        ]);

        $data['user_id'] = Auth::id();
        $data['is_primary'] = (bool)($data['is_primary'] ?? false);

        // Si défini comme principal → mettre les autres à false
        if ($data['is_primary']) {
            PracticeLocation::where('user_id', Auth::id())->update(['is_primary' => false]);
        }

        PracticeLocation::create($data);

        return redirect()->route('practice-locations.index')
            ->with('success', 'Cabinet créé avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PracticeLocation $practice_location)
    {
        $this->authorizeLocation($practice_location);

        return view('practice_locations.edit', ['location' => $practice_location]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PracticeLocation $practice_location)
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
        ]);

        $data['is_primary'] = (bool)($data['is_primary'] ?? false);

        if ($data['is_primary']) {
            PracticeLocation::where('user_id', Auth::id())
                ->where('id', '!=', $practice_location->id)
                ->update(['is_primary' => false]);
        }

        $practice_location->update($data);

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
        if ($location->user_id !== Auth::id()) {
            abort(403, 'Accès refusé');
        }
    }
}
