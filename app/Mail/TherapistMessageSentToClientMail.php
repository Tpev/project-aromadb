<?php

namespace App\Mail;

use App\Mail\Concerns\RepliesToPractitioner;
use App\Models\ClientProfile;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TherapistMessageSentToClientMail extends Mailable implements ShouldQueue
{
    use Queueable, RepliesToPractitioner, SerializesModels;

    public ClientProfile $clientProfile;
    public Message $message;

    public function __construct(ClientProfile $clientProfile, Message $message)
    {
        $clientProfile->loadMissing('user');
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

        return $this->applyPractitionerReplyTo($this->clientProfile->user)
            ->subject("Nouveau message de {$therapistName}")
            ->markdown('emails.therapist_message_sent_to_client');
    }
}
