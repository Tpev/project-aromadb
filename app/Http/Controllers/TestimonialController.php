<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TestimonialController extends Controller
{
    /**
     * Afficher le formulaire de soumission du témoignage.
     */
    public function showSubmitForm($token)
    {
        $testimonialRequest = TestimonialRequest::where('token', $token)
                                                ->where('status', 'pending')
                                                ->firstOrFail();

        return view('testimonials.submit', compact('testimonialRequest'));
    }

    /**
     * Gérer la soumission du témoignage.
     */
    public function submit(Request $request, $token)
    {
        $testimonialRequest = TestimonialRequest::where('token', $token)
                                                ->where('status', 'pending')
                                                ->firstOrFail();

        // Valider les données
        $validator = Validator::make($request->all(), [
            'testimonial' => 'required|string|min:10|max:1500',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation échouée lors de la soumission d\'un témoignage.', [
                'token' => $token,
                'errors' => $validator->errors(),
            ]);
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }

        // Créer le témoignage
        $testimonial = Testimonial::create([
            'testimonial_request_id' => $testimonialRequest->id,
            'therapist_id' => $testimonialRequest->therapist_id,
            'client_profile_id' => $testimonialRequest->client_profile_id,
            'testimonial' => $request->input('testimonial'),
        ]);

        // Mettre à jour le statut de la demande
        $testimonialRequest->update(['status' => 'completed']);

        // Récupérer le thérapeute associé
        $therapist = $testimonialRequest->therapist;

        // Ajouter un log
        Log::info('Témoignage soumis', [
            'testimonial_id' => $testimonial->id,
            'testimonial_request_id' => $testimonialRequest->id,
            'therapist_id' => $testimonial->therapist_id,
            'client_profile_id' => $testimonial->client_profile_id,
        ]);

        // Retourner la vue de remerciement avec le thérapeute
        return view('testimonials.thankyou', compact('therapist'));
    }

    /**
     * Afficher la page de remerciement après la soumission du témoignage.
     */
    public function thankYou()
    {
        // Cette méthode peut être vide ou rediriger vers une autre vue si nécessaire
    }
}
