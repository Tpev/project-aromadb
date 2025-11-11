<?php

namespace App\Mail;

use App\Models\DocumentSigning;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentSignatureLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DocumentSigning $signing) {}

    public function build()
    {
        $url = route('documents.sign.form', $this->signing->token);

        return $this->subject('Signature de votre document')
            ->view('emails.documents.signature_link', [
                'url' => $url,
                'doc' => $this->signing->document,
            ]);
    }
}
