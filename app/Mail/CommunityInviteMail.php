<?php

namespace App\Mail;

use App\Models\ClientProfile;
use App\Models\CommunityGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommunityInviteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public CommunityGroup $community,
        public ClientProfile $client,
        public string $joinUrl,
        public bool $requiresAccountSetup = false,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation à rejoindre une communauté privée',
        );
    }

    public function content(): Content
    {
        $practitionerName = $this->community->user?->company_name
            ?? $this->community->user?->name
            ?? 'votre praticien';

        return new Content(
            markdown: 'emails.communities.invite',
            with: [
                'clientFirstName' => $this->client->first_name ?: 'Bonjour',
                'communityName' => $this->community->name,
                'communityDescription' => $this->community->description,
                'practitionerName' => $practitionerName,
                'joinUrl' => $this->joinUrl,
                'requiresAccountSetup' => $this->requiresAccountSetup,
            ],
        );
    }
}
