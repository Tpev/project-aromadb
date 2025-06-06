<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class AdminNewUserNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
	public $user; 
    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
       $this->user = $user;
    }



public function build()
{
    return $this->subject('Nouvel Utilisateur')
                ->markdown('emails.admin_new_user_notification')
                ->with([
                    'user' => $this->user,
                ]);
}

}
