<?php

namespace App\Mail;

use App\Models\ClientProfile;
use App\Models\ClientFile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;

class ClientFileUploadedTherapistMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ClientProfile $clientProfile;
    public ClientFile $clientFile;
    public ?string $downloadUrl;

    public function __construct(ClientProfile $clientProfile, ClientFile $clientFile)
    {
        $this->clientProfile = $clientProfile;
        $this->clientFile    = $clientFile;

        // Optional public URL (works if disk is public)
        $this->downloadUrl = Storage::disk('public')->exists($clientFile->file_path)
            ? Storage::disk('public')->url($clientFile->file_path)
            : null;
    }

    public function build()
    {
        return $this->subject('Nouveau document reçu dans l’espace client')
            ->markdown('emails.client_file_uploaded_therapist');
    }
}
