<?php

namespace App\Http\Controllers;

use App\Mail\TestimonialRequestMail;
use App\Models\ClientProfile;
use App\Models\TestimonialRequest;
use App\Rules\GoogleReviewUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class TestimonialRequestController extends Controller
{
    public function sendRequest(Request $request, $clientProfileId): RedirectResponse
    {
        $clientProfile = ClientProfile::findOrFail($clientProfileId);
        $therapist = $request->user();
        Gate::authorize('requestTestimonial', $clientProfile);
        abort_unless($therapist->canUseFeature('review'), 403);

        $validated = $request->validateWithBag('reviewRequest', [
            'destination' => ['sometimes', 'required', Rule::in(['internal', 'google'])],
            'google_review_url' => ['exclude_unless:destination,google', 'required', 'string', 'max:2048', new GoogleReviewUrl],
            'remember_google_review_url' => ['sometimes', 'boolean'],
        ], [
            'google_review_url.required' => 'Ajoutez votre lien de demande d’avis Google.',
            'google_review_url.max' => 'Le lien Google ne doit pas dépasser 2 048 caractères.',
            'destination.in' => 'Choisissez Olithea ou Google.',
        ]);
        $destination = $validated['destination'] ?? 'internal';

        // Serialize requests for this client, including simultaneous double clicks.
        // Do not retry this transaction: email delivery is an external side effect.
        DB::transaction(function () use ($clientProfile, $therapist, $validated, $destination) {
            $client = ClientProfile::whereKey($clientProfile->id)
                ->where('user_id', $therapist->id)
                ->lockForUpdate()
                ->firstOrFail();

            $emailValidator = Validator::make(['email' => $client->email], [
                'email' => ['required', 'email'],
            ], [
                'email.required' => 'Ajoutez une adresse email à la fiche client avant d’envoyer une demande.',
                'email.email' => 'Corrigez l’adresse email de la fiche client avant d’envoyer une demande.',
            ]);

            if ($emailValidator->fails()) {
                throw (new ValidationException($emailValidator))->errorBag('reviewRequest');
            }

            if ($client->testimonialRequests()->forDestination($destination)->exists()) {
                throw ValidationException::withMessages([
                    'destination' => 'Une demande a déjà été envoyée à ce client pour cette destination.',
                ])->errorBag('reviewRequest');
            }

            $testimonialRequest = TestimonialRequest::create([
                'therapist_id' => $therapist->id,
                'client_profile_id' => $client->id,
                'token' => Str::random(32),
                'status' => 'pending',
                'destination' => $destination,
                // Snapshot the URL: future profile edits must not change this request.
                'google_review_url' => $destination === 'google' ? $validated['google_review_url'] : null,
            ]);

            if ($destination === 'google' && ($validated['remember_google_review_url'] ?? false)) {
                $therapist->update(['google_review_url' => $validated['google_review_url']]);
            }

            $testimonialRequest->setRelation('therapist', $therapist)->setRelation('clientProfile', $client);

            try {
                Mail::to($client->email)->send(new TestimonialRequestMail($testimonialRequest));
            } catch (Throwable $exception) {
                report($exception);

                // Roll back the request and remembered URL so the therapist can retry.
                throw ValidationException::withMessages([
                    'send' => 'L’envoi de l’email a échoué. Votre demande n’a pas été enregistrée. Veuillez réessayer.',
                ])->errorBag('reviewRequest');
            }
        });

        $message = $destination === 'google'
            ? 'Demande d’avis Google envoyée avec succès.'
            : 'Demande de témoignage envoyée avec succès.';

        return back()->with('success', $message)->with('review_request_success', $message);
    }
}
