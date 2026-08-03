<?php

namespace App\Mail;

use App\Mail\Concerns\RepliesToPractitioner;
use App\Models\Newsletter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, RepliesToPractitioner, SerializesModels;

    public function __construct(
        public Newsletter $newsletter,
        public object $client,
        public ?string $unsubscribeUrl,
        public bool $isTest = false,
    ) {
    }

    public function build(): self
    {
        $this->newsletter->loadMissing('user');

        return $this->applyPractitionerReplyTo($this->newsletter->user)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject(($this->isTest ? '[TEST] ' : '').$this->newsletter->subject)
            ->view('emails.newsletter', [
                'newsletter' => $this->newsletter,
                'client' => $this->client,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ]);
    }
}
