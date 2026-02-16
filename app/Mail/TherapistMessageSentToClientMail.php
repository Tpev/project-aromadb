<?php

namespace App\Mail;

use App\Models\ClientProfile;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TherapistMessageSentToClientMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ClientProfile $clientProfile;
    public Message $message;

    public function __construct(ClientProfile $clientProfile, Message $message)
    {
        $this->clientProfile = $clientProfile;
        $this->message = $message;
    }

    public function build()
    {
        $therapistName =
            $this->clientProfile->user->company_name
            ?? $this->clientProfile->user->business_name
            ?? $this->clientProfile->user->name
            ?? 'votre thérapeute';

        return $this->subject("Nouveau message de {$therapistName}")
            ->markdown('emails.therapist_message_sent_to_client');
    }
}
