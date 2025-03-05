<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InformationRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $firstName;
    public $lastName;
    public $email;
    public $phone;
    public $messageContent;

    /**
     * Create a new message instance.
     */
    public function __construct($firstName, $lastName, $email, $phone, $messageContent)
    {
        $this->firstName      = $firstName;
        $this->lastName       = $lastName;
        $this->email          = $email;
        $this->phone          = $phone;
        $this->messageContent = $messageContent;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject('Nouvelle Demande d\'Information')
            ->view('emails.therapist-request');
    }
}
