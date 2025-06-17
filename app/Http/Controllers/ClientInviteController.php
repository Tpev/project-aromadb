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
    // 🔐 Manually check therapist ownership
    if ($clientProfile->user_id !== auth()->id()) {
        abort(403, 'Ce profil ne vous appartient pas.');
    }

    if (!$clientProfile->email) {
        return back()->with('error', 'Ce client n’a pas d’adresse email.');
    }

    if ($clientProfile->password) {
        return back()->with('error', 'Ce client a déjà activé son compte.');
    }

    $plainToken = Str::uuid()->toString();

    $clientProfile->update([
        'password_setup_token_hash' => hash('sha256', $plainToken),
        'password_setup_expires_at' => now()->addDays(3),
    ]);

    try {
        Mail::to($clientProfile->email)->send(
            new ClientSetPasswordLink($clientProfile, $plainToken)
        );

        return back()->with('success', 'Invitation envoyée au client.');
    } catch (\Exception $e) {
        return back()->with('error', 'Erreur lors de l’envoi de l’email : ' . $e->getMessage());
    }
}

}
