<?php

namespace App\Mail;

use App\Mail\Concerns\RepliesToPractitioner;
use App\Models\Document;
use App\Models\DocumentSigning;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentSignRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, RepliesToPractitioner, SerializesModels;

    public function __construct(
        public Document $document,
        public DocumentSigning $signing,
        public ?string $clientName = null
    ) {}

    public function build()
    {
        $this->document->loadMissing('owner');
        $url = route('documents.sign.form', $this->signing->token);

        return $this->applyPractitionerReplyTo($this->document->owner)
            ->subject('Signature de document – ' . ($this->document->original_name ?? 'Document'))
            ->markdown('emails.documents.sign-request', [
                'document'   => $this->document,
                'signing'    => $this->signing,
                'url'        => $url,
                'clientName' => $this->clientName,
            ]);
    }
}
