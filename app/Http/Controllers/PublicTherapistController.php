<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class PublicTherapistController extends Controller
{
    /**
     * Affiche la page publique du thérapeute.
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        // Find the therapist by slug and ensure the user is a therapist
        $therapist = User::where('slug', $slug)
                         ->where('is_therapist', true)
                         ->firstOrFail();
			
        // Pass the therapist data to the view
        return view('public.therapist.show', compact('therapist'));
    }
}
