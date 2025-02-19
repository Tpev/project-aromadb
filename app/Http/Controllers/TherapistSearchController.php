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

    // Start the query for therapists only and filter out those without a slug,
    // and only show those that are set visible by admin.
    $query = User::query()
        ->where('is_therapist', true)
        ->whereNotNull('slug')
        ->where('slug', '!=', '')
        ->where('visible_annuarire_admin_set', true);

    // Filter by specialty if provided.
    // if (!empty($data['specialty'])) {
    //     $query->where('services', 'like', '%' . $data['specialty'] . '%');
    // }

    // Filter by location if provided, checking both city and region.
    if (!empty($data['location'])) {
        $query->where(function($q) use ($data) {
            $q->where('city_setByAdmin', 'like', '%' . $data['location'] . '%')
              ->orWhere('state_setByAdmin', 'like', '%' . $data['location'] . '%');
        });
    }

    // Get all matching therapist users
    $therapists = $query->get();

    return view('results', compact('therapists'));
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
}
