<?php

namespace App\Mail;

use App\Models\Document;
use App\Models\DocumentSigning;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentSignRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Document $document,
        public DocumentSigning $signing,
        public ?string $clientName = null
    ) {}

    public function build()
    {
        $url = route('documents.sign.form', $this->signing->token);

        return $this->subject('Signature de document – ' . ($this->document->original_name ?? 'Document'))
            ->markdown('emails.documents.sign-request', [
                'document'   => $this->document,
                'signing'    => $this->signing,
                'url'        => $url,
                'clientName' => $this->clientName,
            ]);
    }
}
