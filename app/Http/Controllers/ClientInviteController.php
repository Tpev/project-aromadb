<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientProfile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClientSetPasswordLink;
class ClientInviteController extends Controller

{
    public function store(ClientProfile $clientProfile)
    {
        //$this->authorize('view', $clientProfile);      // therapist owns it

        $plain = Str::uuid()->toString();
        $clientProfile->update([
            'password_setup_token_hash' => hash('sha256',$plain),
            'password_setup_expires_at' => now()->addDays(3),
        ]);

        Mail::to($clientProfile->email)->queue(
            new ClientSetPasswordLink($clientProfile, $plain)
        );

        return back()->with('success','Invitation envoyée au client.');
    }
}

