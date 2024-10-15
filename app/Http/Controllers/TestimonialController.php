<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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
            'testimonial' => 'required|string|min:10|max:1000',
        ]);

        if ($validator->fails()) {
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

        return redirect()->route('testimonials.thankyou');
    }

    /**
     * Afficher la page de remerciement après la soumission du témoignage.
     */
    public function thankYou()
    {
        return view('testimonials.thankyou');
    }
}
