<?php

namespace App\Mail;

use App\Mail\Concerns\RepliesToPractitioner;
use App\Models\DocumentSigning;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentSignatureLinkMail extends Mailable
{
    use Queueable, RepliesToPractitioner, SerializesModels;

    public function __construct(public DocumentSigning $signing) {}

    public function build()
    {
        $this->signing->loadMissing('document.owner');
        $url = route('documents.sign.form', $this->signing->token);

        return $this->applyPractitionerReplyTo($this->signing->document?->owner)
            ->subject('Signature de votre document')
            ->view('emails.documents.signature_link', [
                'url' => $url,
                'doc' => $this->signing->document,
            ]);
    }
}
