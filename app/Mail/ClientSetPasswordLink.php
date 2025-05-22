<?php

namespace App\Mail;

use App\Models\ClientProfile;   // ← correct namespace here
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientSetPasswordLink extends Mailable
{
    use Queueable, SerializesModels;

    public ClientProfile $client;   // strongly typed if you like
    public string $token;

    public function __construct(ClientProfile $client, string $token)  // ← correct type-hint
    {
        $this->client = $client;
        $this->token  = $token;
    }

    public function build()
    {
        return $this->subject('Activez votre espace client')
            ->markdown('emails.client_set_password', [
                'url'    => url("/client/setup/{$this->token}"),
                'client' => $this->client,
            ]);
    }
}
