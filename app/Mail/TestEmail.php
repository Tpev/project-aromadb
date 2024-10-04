<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = 'E-mail de Test';

    /**
     * Crée une nouvelle instance du message.
     *
     * @return void
     */
    public function __construct()
    {
        // Vous pouvez passer des données au constructeur si nécessaire
    }

    /**
     * Construire le message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.test');
    }
}
