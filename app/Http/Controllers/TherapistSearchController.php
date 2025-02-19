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

        // Start the query for therapists only
        $query = User::query()->where('is_therapist', true);

        // Filter by specialty if provided.
        // Assuming that the 'services' field holds the specialties.
    //    if (!empty($data['specialty'])) {
    //        $query->where('services', 'like', '%' . $data['specialty'] . '%');
    //    }

        // Filter by location if provided.
        // For example, using 'company_address' field.
    //    if (!empty($data['location'])) {
     //       $query->where('company_address', 'like', '%' . $data['location'] . '%');
      //  }

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
