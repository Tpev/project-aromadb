<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

// app/Http/Controllers/PublicTherapistController.php

public function show($slug)
{
    // Trouver le thérapeute par slug et s'assurer que l'utilisateur est un thérapeute
    $therapist = User::where('slug', $slug)
                     ->where('is_therapist', true)
                     ->firstOrFail();

    // Charger les témoignages paginés
    $testimonials = $therapist->testimonials()->paginate(5); // 5 témoignages par page

    // Passer les données au vue
    return view('public.therapist.show', compact('therapist', 'testimonials'));
}

