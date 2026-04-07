<?php

namespace App\Mail;

use App\Models\PracticeLocationInvite;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PracticeLocationInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PracticeLocationInvite $invite,
        public User $inviter,
        public string $inviteUrl,
    ) {
    }

    public function build()
    {
        $inviterName =
            $this->inviter->company_name
            ?: trim(($this->inviter->first_name ?? '') . ' ' . ($this->inviter->last_name ?? ''))
            ?: $this->inviter->name
            ?: 'Un thérapeute';

        return $this->subject($inviterName . ' vous invite à rejoindre un cabinet partagé')
            ->markdown('emails.practice_locations.invite');
    }
}
