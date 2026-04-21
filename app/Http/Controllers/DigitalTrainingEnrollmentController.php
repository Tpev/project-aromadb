<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\DigitalTraining;
use App\Models\DigitalTrainingEnrollment;
use App\Services\DigitalTrainingEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DigitalTrainingEnrollmentController extends Controller
{
    public function __construct(private DigitalTrainingEnrollmentService $enrollments)
    {
    }

    public function index(DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        $training = $digitalTraining->load(['enrollments.clientProfile']);

        $clientProfiles = ClientProfile::where('user_id', Auth::id())
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('digital-trainings.enrollments.index', compact('training', 'clientProfiles'));
    }

    public function store(Request $request, DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        $data = $request->validate([
            'client_profile_id' => 'nullable|exists:client_profiles,id',
            'participant_name' => 'nullable|string|max:255',
            'participant_email' => 'required|email|max:255',
        ]);

        $clientProfile = null;
        if (! empty($data['client_profile_id'])) {
            $clientProfile = ClientProfile::where('user_id', Auth::id())
                ->where('id', $data['client_profile_id'])
                ->firstOrFail();
        }

        $this->enrollments->create(
            training: $digitalTraining,
            clientProfile: $clientProfile,
            participantName: $data['participant_name'] ?? null,
            participantEmail: $data['participant_email'] ?? null,
            source: DigitalTrainingEnrollment::SOURCE_MANUAL,
            sendAccessEmail: true,
        );

        return redirect()
            ->route('digital-trainings.enrollments.index', $digitalTraining)
            ->with('success', 'Accès créé et invitation envoyée au client.');
    }

    public function storeFreeAccess(Request $request, DigitalTraining $digitalTraining)
    {
        if ($digitalTraining->status !== 'published' || ! $digitalTraining->hasPublicFreeAccessGate()) {
            abort(404);
        }

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $participantName = trim($data['first_name'] . ' ' . $data['last_name']);

        $enrollment = $this->enrollments->create(
            training: $digitalTraining,
            clientProfile: null,
            participantName: $participantName,
            participantEmail: $data['email'],
            source: DigitalTrainingEnrollment::SOURCE_FREE_GATE,
            sendAccessEmail: false,
        );

        return redirect()->route('digital-trainings.access.show', $enrollment->access_token);
    }

    public function destroy(DigitalTraining $digitalTraining, DigitalTrainingEnrollment $enrollment)
    {
        $this->authorizeOwner($digitalTraining);

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
