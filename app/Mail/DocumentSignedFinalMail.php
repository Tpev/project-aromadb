<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DocumentSignedFinalMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Document $document,
        public ?string $clientName = null
    ) {}

    public function build()
    {
        $downloadUrl = $this->publicDownloadUrl();

        $mail = $this->subject('Votre document signé')
            ->markdown('emails.documents.signed-final', [
                'document'    => $this->document,
                'clientName'  => $this->clientName,
                'downloadUrl' => $downloadUrl,
            ]);

        // Joindre le PDF final si dispo
        if (!empty($this->document->final_pdf_path) &&
            Storage::disk('public')->exists($this->document->final_pdf_path)) {
            $mail->attach(Storage::disk('public')->path($this->document->final_pdf_path), [
                'as'   => ($this->document->original_name ?? 'document-signe').'.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }

    protected function publicDownloadUrl(): ?string
    {
        if (!empty($this->document->final_pdf_path) &&
            Storage::disk('public')->exists($this->document->final_pdf_path)) {
            return asset('storage/'.$this->document->final_pdf_path);
        }
        return null;
    }
}
