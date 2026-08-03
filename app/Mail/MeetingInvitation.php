<?php

namespace App\Mail;

use App\Mail\Concerns\RepliesToPractitioner;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MeetingInvitation extends Mailable
{
    use Queueable, RepliesToPractitioner, SerializesModels;

    public $link;

    public function __construct($link, public ?User $practitioner = null)
    {
        $this->link = $link;
    }

    public function build()
    {
        return $this->applyPractitionerReplyTo($this->practitioner)
                    ->subject('Invitation à une réunion')
                    ->view('emails.meeting_invitation');
    }
}
