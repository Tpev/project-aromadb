<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Event;
use App\Models\InformationRequest;
use App\Mail\InformationRequestMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class MobileTherapistController extends Controller
{
    /**
     * Affiche la page publique mobile du thérapeute.
     *
     * GET /mobile/therapeute/{slug}
     */
    public function show(string $slug)
    {
        // Trouver le thérapeute par slug et s'assurer que l'utilisateur est un thérapeute
        $therapist = User::where('slug', $slug)
            ->where('is_therapist', true)
            ->firstOrFail();

        // Incrémenter le compteur de vues
        $therapist->increment('view_count');

        // Témoignages paginés (on garde la même logique que la version web)
        $testimonials = $therapist->testimonials()
            ->orderByRaw('COALESCE(external_created_at, created_at) DESC')
            ->paginate(5);

        // Prestations (produits) du thérapeute
        $prestations = $therapist->products()
            ->orderBy('display_order')
            ->get();

        // Événements à venir à afficher sur le portail
        $events = Event::where('user_id', $therapist->id)
            ->where('start_date_time', '>=', Carbon::now())
            ->where('showOnPortail', true)
            ->orderBy('start_date_time', 'asc')
            ->with('associatedProduct') // eager load
            ->get();

        // Vue mobile dédiée
        return view('mobile.therapists.show', compact(
            'therapist',
            'testimonials',
            'prestations',
            'events'
        ));
    }

    /**
     * Gère l’envoi d’une demande d’information depuis la fiche mobile.
     *
     * POST /mobile/therapeute/{slug}/information
     */
    public function sendInformationRequest(Request $request, string $slug)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email',
            'phone'      => ['nullable','regex:/^[0-9\-\+\(\)\s]+$/','min:8'],
            'message'    => 'required|string|max:2000',
            'terms'      => 'accepted', // si tu gardes la case CGU/Privacy sur le mobile
        ]);

        // Retrieve therapist
        $therapist = User::where('slug', $slug)
            ->where('is_therapist', true)
            ->firstOrFail();

        // 1) Stocker la demande en BDD
        InformationRequest::create([
            'therapist_id' => $therapist->id,
            'first_name'   => $request->first_name,
            'last_name'    => $request->last_name,
            'email'        => $request->email,
            'phone'        => $request->phone,
            'message'      => $request->message,
        ]);

        // 2) Envoyer l’email au thérapeute
        Mail::to($therapist->email)->send(
            new InformationRequestMail(
                $request->first_name,
                $request->last_name,
                $request->email,
                $request->phone,
                $request->message
            )
        );

        return redirect()->back()->with('success', 'Votre demande a bien été envoyée !');
    }
}
