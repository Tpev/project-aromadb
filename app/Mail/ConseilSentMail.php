<?php

namespace App\Mail;

use App\Models\ClientProfile;
use App\Models\Conseil;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConseilSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $clientProfile;
    public $conseil;
    public $link;
    public $therapistName;

    /**
     * Create a new message instance.
     *
     * @param ClientProfile $clientProfile
     * @param Conseil $conseil
     * @param string $link
     */
    public function __construct(ClientProfile $clientProfile, Conseil $conseil, $link)
    {
        $this->clientProfile = $clientProfile;
        $this->conseil = $conseil;
        $this->link = $link;
        
        // Assuming the Conseil has a relation to User (therapist)
        // i.e. $conseil->user->name or business_name is available.
        // Adjust as needed if your user model fields differ.
        $this->therapistName = $conseil->user->name ?? 'Votre thérapeute';
    }

    /**
     * Build the message.
     *
     * @return ConseilSentMail
     */
    public function build()
    {
        return $this->subject('Nouveau Conseil Disponible')
                    ->markdown('emails.conseil_sent_markdown');
    }
}
