<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ClientPasswordSetupController extends Controller
{
    /* ---------------------------------------------------------
       EXISTING: Show the “choose password” form (setup via token)
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
       EXISTING: Handle setup submission & log the client in
    --------------------------------------------------------- */
    public function store(Request $request, string $token)
    {
        $client = $this->resolveClientFrom($token);

        $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
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
       NEW: Show “forgot password” form (email input)
    --------------------------------------------------------- */
    public function forgotForm()
    {
        return view('client.forgot-password');
    }

    /* ---------------------------------------------------------
       NEW: Send reset link email (IMPORTANT: client_profiles broker)
    --------------------------------------------------------- */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // ✅ CRITICAL: use your client broker (not default users)
        $status = Password::broker('client_profiles')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    /* ---------------------------------------------------------
       NEW: Show reset form (token + email + new password)
    --------------------------------------------------------- */
    public function resetForm(Request $request, string $token)
    {
        return view('client.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /* ---------------------------------------------------------
       NEW: Handle reset password submission + login
    --------------------------------------------------------- */
    public function resetStore(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        // ✅ CRITICAL: client_profiles broker
        $status = Password::broker('client_profiles')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($client, $password) {
                $client->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                Auth::guard('client')->login($client);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('client.home')->with('success', 'Mot de passe réinitialisé !')
            : back()->withErrors(['email' => __($status)]);
    }

    /* ---------------------------------------------------------
       Private helper that turns a raw token into a ClientProfile
       (setup-password token flow only)
    --------------------------------------------------------- */
    protected function resolveClientFrom(string $token): ClientProfile
    {
        return ClientProfile::where('password_setup_token_hash', hash('sha256', $token))
            ->where('password_setup_expires_at', '>', now())
            ->firstOrFail();
    }
}
