<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuestionnaireSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $therapistName;
    public $questionnaireTitle;
    public $link;

    /**
     * Create a new message instance.
     *
     * @param string $therapistName
     * @param string $questionnaireTitle
     * @param string $link
     */
    public function __construct(string $therapistName, string $questionnaireTitle, string $link, string $client_profile_name)
    {
        $this->therapistName = $therapistName;
        $this->questionnaireTitle = $questionnaireTitle;
        $this->link = $link;
		$this->client_profile_name = $client_profile_name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Questionnaire envoyé',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.questionnaire_sent', // Specify the Markdown template
            with: [
                'therapistName' => $this->therapistName,
                'questionnaireTitle' => $this->questionnaireTitle,
                'link' => $this->link,
                'client_profile_name' => $this->client_profile_name,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
