<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestimonialRequest;
use App\Models\ClientProfile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestimonialRequestMail;

class TestimonialRequestController extends Controller
{
    /**
     * Envoyer une demande de témoignage à un client.
     */
    public function sendRequest(Request $request, $clientProfileId)
    {
        $clientProfile = ClientProfile::findOrFail($clientProfileId);

        // Générer un token unique
        $token = Str::random(32);

        // Créer une demande de témoignage
        $testimonialRequest = TestimonialRequest::create([
            'therapist_id' => auth()->user()->id,
            'client_profile_id' => $clientProfile->id,
            'token' => $token,
            'status' => 'pending',
        ]);

        // Envoyer l'email au client
        Mail::to($clientProfile->email)->send(new TestimonialRequestMail($testimonialRequest));

        return redirect()->back()->with('success', 'Demande de témoignage envoyée avec succès.');
    }
}
