<?php

namespace App\Mail;

use App\Mail\Concerns\RepliesToPractitioner;
use App\Models\ClientProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ClientSetPasswordLink extends Mailable
{
    use Queueable, RepliesToPractitioner, SerializesModels;

    public ClientProfile $client;
    public string $token;

    public function __construct(ClientProfile $client, string $token)
    {
        $client->loadMissing('user');
        $this->client = $client;
        $this->token  = $token;
    }

    public function build()
    {
        $data = [
            'url'    => url("/client/setup/{$this->token}"),
            'client' => $this->client,
        ];

        // Optional: Log the rendered email content (for dev only)
        if (app()->isLocal()) {
            $rendered = view('emails.client_set_password_plain', $data)->render();
            Log::info("Email preview for {$this->client->email}:\n" . $rendered);
        }

        return $this->applyPractitionerReplyTo($this->client->user)
            ->subject('Activez votre espace client')
            ->view('emails.client_set_password_plain', $data);
    }
}
