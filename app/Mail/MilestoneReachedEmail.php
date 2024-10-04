<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MilestoneReachedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $sessionsTotal;
    public $milestone;

    public function __construct($sessionsTotal, $milestone)
    {
        $this->sessionsTotal = $sessionsTotal;
        $this->milestone = $milestone;
    }

    public function build()
    {
        return $this->subject("Milestone Reached: {$this->milestone} Sessions")
                    ->markdown('emails.milestone_reached');
    }
}
