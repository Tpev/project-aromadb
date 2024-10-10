<?php

namespace App\Mail;

use App\Models\Response; // Import the Response model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuestionnaireCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Response $response; // Store the response instance

    /**
     * Create a new message instance.
     *
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response; // Assign the response to the class property
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Questionnaire Complété',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.questionnaire_completed', // Use a proper view for the email
            with: [
                'response' => $this->response, // Pass the response to the view
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
        return []; // Add any attachments if necessary
    }
}
