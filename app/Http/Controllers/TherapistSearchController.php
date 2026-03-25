<?php

namespace App\Http\Controllers;

use App\Services\TherapistDirectorySearchService;
use Illuminate\Http\Request;
use App\Models\User;

class TherapistSearchController extends Controller
{
    /**
     * Main search endpoint (POST from form).
     */
    public function index(Request $request, TherapistDirectorySearchService $directorySearch)
    {
        $data = $request->validate([
            'name'      => 'nullable|string',
            'specialty' => 'nullable|string',
            'location'  => 'nullable|string',
        ]);

        $therapists = $directorySearch->search($data);

        $specialty = $data['specialty'] ?? null;
        $region    = $data['location'] ?? null;

        return view('results', compact('therapists', 'specialty', 'region'));
    }

    /**
     * Public profile.
     */
    public function show($slug)
    {
        $therapist = User::where('slug', $slug)
            ->where('is_therapist', true)
            ->firstOrFail();

        return view('therapists.show', compact('therapist'));
    }

    /**
     * /practicien-{specialty}
     */
    public function filterBySpecialty($specialty, TherapistDirectorySearchService $directorySearch)
    {
        $specialty = str_replace('-', ' ', $specialty);
        $therapists = $directorySearch->search(['specialty' => $specialty]);

        return view('results', compact('therapists', 'specialty'));
    }

    /**
     * /region-{region}
     */
    public function filterByRegion($region, TherapistDirectorySearchService $directorySearch)
    {
        $regionRaw     = str_replace('-', ' ', $region);
        $therapists = $directorySearch->search(['location' => $regionRaw]);

        $region = $regionRaw; // for display
        return view('results', compact('therapists', 'region'));
    }

    /**
     * /practicien-{specialty}-region-{region}
     */
    public function filterBySpecialtyRegion($specialty, $region, TherapistDirectorySearchService $directorySearch)
    {
        $specialtySearchRaw   = str_replace('-', ' ', $specialty);
        $regionRaw     = str_replace('-', ' ', $region);
        $therapists = $directorySearch->search([
            'specialty' => $specialtySearchRaw,
            'location' => $regionRaw,
        ]);

        $specialty = $specialtySearchRaw;
        $region    = $regionRaw;

        return view('results', compact('therapists', 'specialty', 'region'));
    }
}
