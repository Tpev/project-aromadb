<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class TherapistSearchController extends Controller
{
    /**
     * Display a listing of therapist results based on search criteria.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
public function index(Request $request)
{
    // Validate the incoming request
    $data = $request->validate([
        'specialty' => 'nullable|string',
        'location'  => 'nullable|string',
    ]);

    // Base query for therapists
    $query = User::query()
        ->where('is_therapist', true)
        ->whereNotNull('slug')
        ->where('slug', '!=', '')
        ->where('visible_annuarire_admin_set', true);

    // Apply location filter if provided (checks both city and region)
    if (!empty($data['location'])) {
        $query->where(function($q) use ($data) {
            $q->where('city_setByAdmin', 'like', '%' . $data['location'] . '%')
              ->orWhere('state_setByAdmin', 'like', '%' . $data['location'] . '%');
        });
    }

    // (Optional) You can add a specialty filter here if needed:
    // if (!empty($data['specialty'])) {
    //     $query->where('services', 'like', '%' . $data['specialty'] . '%');
    // }

    // Get all matching therapists
    $therapists = $query->get();

    // Map input fields to variables expected in the view
    $specialty = $data['specialty'] ?? null;
    // Use 'location' as 'region' for your blade logic
    $region = $data['location'] ?? null;

    return view('results', compact('therapists', 'specialty', 'region'));
}






    /**
     * Display the individual therapist profile.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        $therapist = User::where('slug', $slug)
                         ->where('is_therapist', true)
                         ->firstOrFail();

        return view('therapists.show', compact('therapist'));
    }
	

    /**
     * Filter by specialty only.
     *
     * URL: /practicien-{specialty}
     * Example: /practicien-hypnothérapeute
     */
    public function filterBySpecialty($specialty)
    {
        // Convert hyphens to spaces (e.g. "hypnothérapeute" => "hypnothérapeute")
        $specialtySearch = str_replace('-', ' ', $specialty);

        $therapists = User::query()
            ->where('is_therapist', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('visible_annuarire_admin_set', true)
            ->where('services', 'like', '%' . $specialtySearch . '%')
            ->get();

        return view('results', compact('therapists', 'specialty'));
    }

    /**
     * Filter by region only.
     *
     * URL: /region-{region}
     * Example: /region-ile-de-france
     */
    public function filterByRegion($region)
    {
        // Replace hyphens with spaces and convert to title case to match DB values.
        $regionSearch = str_replace('-', ' ', $region);
        $regionSearch = mb_convert_case($regionSearch, MB_CASE_TITLE, 'UTF-8');

        $therapists = User::query()
            ->where('is_therapist', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('visible_annuarire_admin_set', true)
            ->where(function($q) use ($regionSearch) {
                $q->where('city_setByAdmin', 'like', '%' . $regionSearch . '%')
                  ->orWhere('state_setByAdmin', 'like', '%' . $regionSearch . '%');
            })
            ->get();

        return view('results', compact('therapists', 'region'));
    }

    /**
     * Filter by both specialty and region.
     *
     * URL: /practicien-{specialty}-region-{region}
     * Example: /practicien-hypnothérapeute-region-ile-de-france
     */
    public function filterBySpecialtyRegion($specialty, $region)
    {
        $specialtySearch = str_replace('-', ' ', $specialty);
        $regionSearch = str_replace('-', ' ', $region);
        $regionSearch = mb_convert_case($regionSearch, MB_CASE_TITLE, 'UTF-8');

        $therapists = User::query()
            ->where('is_therapist', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('visible_annuarire_admin_set', true)
            ->where('services', 'like', '%' . $specialtySearch . '%')
            ->where(function($q) use ($regionSearch) {
                $q->where('city_setByAdmin', 'like', '%' . $regionSearch . '%')
                  ->orWhere('state_setByAdmin', 'like', '%' . $regionSearch . '%');
            })
            ->get();

        return view('results', compact('therapists', 'specialty', 'region'));
    }



	
	
}
