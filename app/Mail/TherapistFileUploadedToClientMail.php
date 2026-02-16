<?php

namespace App\Mail;

use App\Models\ClientFile;
use App\Models\ClientProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TherapistFileUploadedToClientMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ClientProfile $clientProfile;
    public ClientFile $clientFile;

    public function __construct(ClientProfile $clientProfile, ClientFile $clientFile)
    {
        $this->clientProfile = $clientProfile;
        $this->clientFile = $clientFile;
    }

    public function build()
    {
        $therapistName =
            $this->clientProfile->user->company_name
            ?? $this->clientProfile->user->business_name
            ?? $this->clientProfile->user->name
            ?? 'votre thérapeute';

        return $this->subject("Nouveau document de {$therapistName}")
            ->markdown('emails.therapist_file_uploaded_to_client');
    }
}
