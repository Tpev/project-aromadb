<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\InformationRequestMail;
use App\Models\InformationRequest;

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
    $testimonials = Testimonial::where('therapist_id', $therapist->id)
    ->orderByRaw('COALESCE(external_created_at, created_at) DESC')
    ->paginate(5);
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


public function sendInformationRequest(Request $request, $slug)
{
    $request->validate([
        'first_name' => 'required|string|max:100',
        'last_name'  => 'required|string|max:100',
        'email'      => 'required|email',
        'phone'      => ['nullable','regex:/^[0-9\-\+\(\)\s]+$/','min:8'],
        'message'    => 'required|string|max:2000',
    ]);

    // Retrieve therapist
    $therapist = User::where('slug', $slug)
                     ->where('is_therapist', true)
                     ->firstOrFail();

    // 1) Store the request in DB
    InformationRequest::create([
        'therapist_id' => $therapist->id,
        'first_name'   => $request->first_name,
        'last_name'    => $request->last_name,
        'email'        => $request->email,
        'phone'        => $request->phone,
        'message'      => $request->message,
    ]);

    // 2) Send the email
    Mail::to($therapist->email)->send(
        new InformationRequestMail(
            $request->first_name,
            $request->last_name,
            $request->email,
            $request->phone,
            $request->message
        )
    );

    // (Optional) Send a confirmation email to the user as well

    return redirect()->back()->with('success', 'Votre demande a bien été envoyée !');
}


}
