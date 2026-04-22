<?php

namespace App\Services;

use App\Mail\DigitalTrainingAccessMail;
use App\Models\ClientProfile;
use App\Models\DigitalTraining;
use App\Models\DigitalTrainingEnrollment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DigitalTrainingEnrollmentService
{
    public function create(
        DigitalTraining $training,
        ?ClientProfile $clientProfile = null,
        ?string $participantName = null,
        ?string $participantEmail = null,
        string $source = DigitalTrainingEnrollment::SOURCE_MANUAL,
        bool $sendAccessEmail = false,
        bool $emailCommunicationConsent = false
    ): DigitalTrainingEnrollment {
        $name = $clientProfile
            ? $this->normalizeName(trim(($clientProfile->last_name ?? '') . ' ' . ($clientProfile->first_name ?? '')))
            : $this->normalizeName($participantName);
        $email = $clientProfile?->email ?? trim((string) $participantEmail);

        $enrollment = DigitalTrainingEnrollment::create([
            'digital_training_id' => $training->id,
            'client_profile_id' => $clientProfile?->id,
            'participant_name' => $name !== '' ? $name : null,
            'participant_email' => $email,
            'access_token' => (string) Str::uuid(),
            'token_expires_at' => now()->addMonths(6),
            'source' => $source,
            'email_communication_consent' => $emailCommunicationConsent,
            'email_communication_consent_at' => $emailCommunicationConsent ? now() : null,
        ]);

        if ($sendAccessEmail && $email !== '') {
            Mail::to($email)->send(new DigitalTrainingAccessMail($enrollment->load('training.user')));
        }

        return $enrollment;
    }

    protected function normalizeName(?string $participantName): string
    {
        return trim(preg_replace('/\s+/', ' ', (string) $participantName) ?? '');
    }
}
