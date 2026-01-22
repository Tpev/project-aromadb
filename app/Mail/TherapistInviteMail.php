<?php

namespace App\Mail;

use App\Models\ReferralInvite;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TherapistInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ReferralInvite $invite,
        public User $referrer,
        public string $signupUrl,
    ) {}

    public function build()
    {
        return $this->subject('Invitation à rejoindre AromaMade PRO')
            ->markdown('emails.referrals.invite');
    }
}
