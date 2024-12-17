<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\Conseil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ConseilSentMail;

class ClientConseilController extends Controller
{
    /**
     * Show the form for sending a conseil to a client.
     */
    public function sendForm(ClientProfile $clientProfile)
    {
        // Ensure authorization if needed
        // $this->authorize('view', $clientProfile);

        // Get all conseils owned by the currently authenticated therapist
        $conseils = Conseil::where('user_id', Auth::id())->get();

        return view('client_profiles.send_conseil', compact('clientProfile', 'conseils'));
    }

    /**
     * Handle sending a conseil to a client and send an email with a tokenized link.
     */
    public function send(Request $request, ClientProfile $clientProfile)
    {
        $data = $request->validate([
            'conseil_id' => 'required|exists:conseils,id',
        ]);

        $conseil = Conseil::where('id', $data['conseil_id'])
                    ->where('user_id', Auth::id())
                    ->firstOrFail();

        // Generate a unique token
        $token = Str::random(64);

        // Attach conseil to client with current timestamp and token
        $clientProfile->conseilsSent()->attach($conseil->id, [
            'sent_at' => now(),
            'token' => $token
        ]);

     $link = route('public.conseil.view', [
    // No route parameters here
]) . '?clientProfile=' . $clientProfile->id . '&conseil=' . $conseil->id . '&token=' . $token;

        // Send the email to the client if they have an email
        if ($clientProfile->email) {
            Mail::to($clientProfile->email)->send(new ConseilSentMail($clientProfile, $conseil, $link));
        }

        return redirect()->route('client_profiles.show', $clientProfile->id)->with('success', 'Conseil envoyé avec succès. Un email contenant le lien a été envoyé au client.');
    }

public function viewConseil(Request $request)
{
    $clientProfileId = $request->query('clientProfile');
    $conseilId = $request->query('conseil');
    $token = $request->query('token');

    // Validate that these query parameters exist
    if (!$clientProfileId || !$conseilId || !$token) {
        abort(404, 'Paramètres manquants.');
    }

    $clientProfile = \App\Models\ClientProfile::find($clientProfileId);
    $conseil = \App\Models\Conseil::find($conseilId);

    if (!$clientProfile || !$conseil) {
        abort(404, 'Client ou conseil introuvable.');
    }

    // Check that the conseil is actually sent to this client and the token matches
    $record = $clientProfile->conseilsSent()
                ->where('conseil_id', $conseil->id)
                ->wherePivot('token', $token)
                ->first();

    if (!$record) {
        abort(403, 'Lien invalide ou expiré.');
    }

    // Display the conseil
    return view('public_conseil', compact('clientProfile', 'conseil'));
}

}
