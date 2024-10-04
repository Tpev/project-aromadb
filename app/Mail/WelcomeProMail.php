<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeProMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user; // Déclarez la variable $user comme propriété publique

    /**
     * Crée une nouvelle instance du message.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user; // Assignez l'utilisateur à la propriété $user
    }

    /**
     * Construit le message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Bienvenue chez ' . config('app.name') . ' !') // Définir le sujet de l'e-mail
                    ->markdown('emails.welcome_pro');
    }
}
