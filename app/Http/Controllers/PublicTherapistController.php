<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Event;
use Carbon\Carbon;


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
    // Trouver le thérapeute par slug et s'assurer que l'utilisateur est un thérapeute
    $therapist = User::where('slug', $slug)
                     ->where('is_therapist', true)
                     ->firstOrFail();

	        // Incrémenter le compteur de vues
        $therapist->increment('view_count');	
    // Charger les témoignages paginés
    $testimonials = $therapist->testimonials()->paginate(5); // 5 témoignages par page
	 $prestations = $therapist->products()->orderBy('display_order')->get();
// Fetch upcoming events for the therapist
    // Fetch upcoming events for the therapist that are set to be shown on the portal
    $events = Event::where('user_id', $therapist->id)
        ->where('start_date_time', '>=', Carbon::now())
        ->where('showOnPortail', true)
        ->orderBy('start_date_time', 'asc')
        ->with('associatedProduct') // Eager load the associated product
        ->get();
    // Passer les données au vue
    return view('public.therapist.show', compact('therapist', 'testimonials','prestations','events'));
}
}
