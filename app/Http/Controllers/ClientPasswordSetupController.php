<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientPasswordSetupController extends Controller
{
    /* ---------------------------------------------------------
       Show the “choose password” form
    --------------------------------------------------------- */
    public function show(string $token)
    {
        $client = $this->resolveClientFrom($token);

        return view('client.setup-password', [
            'token' => $token,
            'email' => $client->email,
            'name'  => $client->first_name,
        ]);
    }

    /* ---------------------------------------------------------
       Handle form submission & log the client in
    --------------------------------------------------------- */
    public function store(Request $request, string $token)
    {
        $client = $this->resolveClientFrom($token);

        $request->validate([
            'password' => ['required','confirmed','min:8'],
        ]);

        $client->update([
            'password'                  => bcrypt($request->password),
            'password_setup_token_hash' => null,
            'password_setup_expires_at' => null,
        ]);

        Auth::guard('client')->login($client);

        return redirect()->route('client.home')
                         ->with('success', 'Mot de passe enregistré !');
    }

    /* ---------------------------------------------------------
       Private helper that turns a raw token into a ClientProfile
    --------------------------------------------------------- */
    protected function resolveClientFrom(string $token): ClientProfile
    {
        return ClientProfile::where(
                    'password_setup_token_hash',
                    hash('sha256', $token)
               )
               ->where('password_setup_expires_at', '>', now())
               ->firstOrFail();
    }
}
