<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\GoogleTokenFile;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class GoogleCalendarController extends Controller
{
    /* ---------------------------------------------------------------------
     | 1.  Client Google pré-configuré
     * -------------------------------------------------------------------- */
    private function client(): GoogleClient
    {
        $cfg = config('google-oauth');   // lire client_id, secret, redirect_uri

        $client = new GoogleClient();
        $client->setClientId($cfg['client_id']);
        $client->setClientSecret($cfg['client_secret']);
        $client->setRedirectUri($cfg['redirect_uri']);
        $client->setAccessType('offline');          // ↔ refresh_token
        $client->setPrompt('consent');              // affiche l’écran à chaque fois
        $client->setScopes(['https://www.googleapis.com/auth/calendar']);

        return $client;
    }

    /* ---------------------------------------------------------------------
     | 2.  Lancer l’écran de consentement
     * -------------------------------------------------------------------- */
    public function redirect()
    {
        return Redirect::to($this->client()->createAuthUrl());
    }

    /* ---------------------------------------------------------------------
     | 3.  Callback : échange le code ↔ token, puis stocke
     * -------------------------------------------------------------------- */
    public function callback(Request $request)
    {
        if (!$request->has('code')) {
            return back()->withErrors('Code OAuth manquant.');
        }

        $client = $this->client();
        $token  = $client->fetchAccessTokenWithAuthCode($request->code);

        if (isset($token['error'])) {
            return back()->withErrors('Erreur Google : '.$token['error_description']);
        }

        /** @var User $user */
        $user = Auth::user();
        $user->update([
            'google_access_token'     => json_encode($token),
            'google_refresh_token'    => $token['refresh_token'] ?? $user->google_refresh_token,
            'google_token_expires_at' => Carbon::now()->addSeconds($token['expires_in']),
        ]);

        // ⬇️  Stocke aussi le token dans un fichier dédié (Spatie l’exigera)
        GoogleTokenFile::put($user->id, $token);

        return redirect()
            ->route('profile.editCompanyInfo')
            ->with('success', 'Google Agenda connecté !');
    }

    /* ---------------------------------------------------------------------
     | 4.  Déconnexion : purge BDD + supprime fichier token
     * -------------------------------------------------------------------- */
    public function disconnect()
    {
        $user = Auth::user();

        $user->update([
            'google_access_token'     => null,
            'google_refresh_token'    => null,
            'google_token_expires_at' => null,
        ]);

        GoogleTokenFile::forget($user->id);

        return back()->with('success', 'Google Agenda déconnecté.');
    }
}
