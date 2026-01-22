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
        $name =
            $this->referrer->company_name
            ?: ($this->referrer->first_name
                ? trim($this->referrer->first_name . ' ' . ($this->referrer->last_name ?? ''))
                : null)
            ?: $this->referrer->name
            ?: 'Un thérapeute';

        return $this->subject($name . ' vous invite à rejoindre AromaMade PRO')
            ->markdown('emails.referrals.invite');
    }
}
