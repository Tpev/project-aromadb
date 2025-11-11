<?php

namespace App\Mail;

use App\Models\Emargement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmargementRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Emargement $em) {}

    public function build()
    {
        $url = url("/sign/{$this->em->token}");
        return $this->subject('Signature de votre feuille d’émargement')
            ->markdown('emails.emargement.request', [
                'em'  => $this->em,
                'url' => $url,
            ]);
    }
}
