<?php

namespace App\Http\Controllers;

use App\Models\DigitalTraining;
use App\Models\DigitalTrainingEnrollment;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\DigitalTrainingAccessMail;

class DigitalTrainingEnrollmentController extends Controller
{
    public function index(DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        $training = $digitalTraining->load(['enrollments.clientProfile']);

        // All client profiles of this therapist (for select box)
        $clientProfiles = ClientProfile::where('user_id', Auth::id())
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('digital-trainings.enrollments.index', compact('training', 'clientProfiles'));
    }

    public function store(Request $request, DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        // Either an existing client_profile_id OR free-form name/email
        $data = $request->validate([
            'client_profile_id' => 'nullable|exists:client_profiles,id',
            'participant_name'  => 'nullable|string|max:255',
            'participant_email' => 'required|email|max:255',
        ]);

        $clientProfile = null;
        if (!empty($data['client_profile_id'])) {
            $clientProfile = ClientProfile::where('user_id', Auth::id())
                ->where('id', $data['client_profile_id'])
                ->firstOrFail();
        }

        $name  = $clientProfile?->full_name ?? $data['participant_name'] ?? null;
        $email = $clientProfile?->email ?? $data['participant_email'];

        // Generate unique token
        $token = Str::uuid()->toString();

        $enrollment = DigitalTrainingEnrollment::create([
            'digital_training_id' => $digitalTraining->id,
            'client_profile_id'   => $clientProfile?->id,
            'participant_name'    => $name,
            'participant_email'   => $email,
            'access_token'        => $token,
            'token_expires_at'    => now()->addMonths(6), // ex: 6 months validity
            'source'              => 'manual',
        ]);

        // Send email with access link
        Mail::to($email)->send(new DigitalTrainingAccessMail($enrollment));

        return redirect()
            ->route('digital-trainings.enrollments.index', $digitalTraining)
            ->with('success', 'Accès créé et invitation envoyée au client.');
    }

    public function destroy(DigitalTraining $digitalTraining, DigitalTrainingEnrollment $enrollment)
    {
        $this->authorizeOwner($digitalTraining);

        // Sécurité : s’assurer que l’accès appartient bien à cette formation
        if ($enrollment->digital_training_id !== $digitalTraining->id) {
            abort(404);
        }

        $enrollment->delete();

        return redirect()
            ->route('digital-trainings.enrollments.index', $digitalTraining)
            ->with('success', 'L’accès du participant a été révoqué.');
    }

    protected function authorizeOwner(DigitalTraining $training): void
    {
        if ($training->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
