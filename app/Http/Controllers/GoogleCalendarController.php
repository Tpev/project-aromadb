<?php

namespace App\Http\Controllers;

use Google\Client as GoogleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;

class GoogleCalendarController extends Controller
{
    /** Configure un client Google prêt à l’emploi. */
private function client(): \Google\Client
{
    $cfg = config('google-oauth');          // ← nouvelle config

    $client = new \Google\Client();
    $client->setClientId($cfg['client_id']);
    $client->setClientSecret($cfg['client_secret']);
    $client->setRedirectUri($cfg['redirect_uri']);
    $client->setAccessType('offline');
    $client->setPrompt('consent');
    $client->setScopes(['https://www.googleapis.com/auth/calendar']);

    return $client;
}


    /** 1️⃣ Lance l’écran de consentement Google. */
    public function redirect()
    {
        return redirect($this->client()->createAuthUrl());
    }

    /** 2️⃣ Reçoit le ?code, échange contre token et stocke. */
    public function callback(Request $request)
    {
        if (!$request->has('code')) {
            return redirect()->route('settings')->withErrors('Code OAuth manquant.');
        }

        $client = $this->client();
        $token  = $client->fetchAccessTokenWithAuthCode($request->code);

        if (isset($token['error'])) {
            return redirect()->route('settings')->withErrors('Erreur Google : '.$token['error_description']);
        }

        /** @var User $user */
        $user = Auth::user();
        $user->google_access_token     = json_encode($token);
        $user->google_refresh_token    = $token['refresh_token'] ?? $user->google_refresh_token;
        $user->google_token_expires_at = Carbon::now()->addSeconds($token['expires_in']);
        $user->save();

        return redirect()->route('profile.editCompanyInfo')->with('success', 'Google Agenda connecté !');
    }

    /** Permet de déconnecter et de purger les jetons. */
    public function disconnect()
    {
        $user = Auth::user();
        $user->update([
            'google_access_token'      => null,
            'google_refresh_token'     => null,
            'google_token_expires_at'  => null,
        ]);

        return back()->with('success', 'Google Agenda déconnecté.');
    }
}
