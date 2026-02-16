<?php

namespace App\Mail;

use App\Models\ClientProfile;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClientMessageReceivedTherapistMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ClientProfile $clientProfile;
    public Message $message;

    public function __construct(ClientProfile $clientProfile, Message $message)
    {
        $this->clientProfile = $clientProfile;
        $this->message       = $message;
    }

    public function build()
    {
        return $this->subject('Nouveau message reçu dans l’espace client')
            ->markdown('emails.client_message_received_therapist');
    }
}
