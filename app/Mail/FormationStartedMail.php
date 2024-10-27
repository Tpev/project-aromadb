<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
// app/Mail/FormationStartedMail.php


class FormationStartedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this->subject('📢 Nouveau départ ! Un utilisateur a commencé la formation')
                    ->view('emails.formation_started');
    }
}
