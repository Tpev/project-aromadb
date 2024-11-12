<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data; // Données du formulaire

    /**
     * Créer une nouvelle instance du message.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Construire le message.
     */
    public function build()
    {
        return $this->subject($this->data['subject'])
                    ->view('emails.contact');
    }
}
