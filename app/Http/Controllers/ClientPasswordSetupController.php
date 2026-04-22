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
    public function show(string $token)
    {
        $client = $this->resolveClientFrom($token);

        return view('client.setup-password', [
            'token' => $token,
            'email' => $client->email,
            'name' => $client->first_name,
            'redirect' => request()->query('redirect'),
        ]);
    }

    public function store(Request $request, string $token)
    {
        $client = $this->resolveClientFrom($token);

        $request->validate(
            [
                'password' => ['required', 'confirmed', 'min:8'],
                'redirect' => ['nullable', 'string', 'max:2000'],
            ],
            [
                'password.required' => 'Veuillez choisir un mot de passe.',
                'password.min' => 'Votre mot de passe doit contenir au moins 8 caracteres.',
                'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            ],
            [
                'password' => 'mot de passe',
            ]
        );

        $client->update([
            'password' => bcrypt($request->password),
            'password_setup_token_hash' => null,
            'password_setup_expires_at' => null,
        ]);

        Auth::guard('client')->login($client);

        $redirectTo = $request->filled('redirect')
            ? $request->string('redirect')->toString()
            : route('client.home');

        return redirect()->to($redirectTo)
            ->with('success', 'Mot de passe enregistre !');
    }

    public function forgotForm()
    {
        return view('client.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(
            [
                'email' => ['required', 'email'],
            ],
            [
                'email.required' => 'Veuillez renseigner votre adresse e-mail.',
                'email.email' => 'Veuillez renseigner une adresse e-mail valide.',
            ],
            [
                'email' => 'adresse e-mail',
            ]
        );

        $status = Password::broker('client_profiles')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function resetForm(Request $request, string $token)
    {
        return view('client.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetStore(Request $request)
    {
        $request->validate(
            [
                'token' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required', 'confirmed', 'min:8'],
            ],
            [
                'token.required' => 'Le lien de reinitialisation est invalide ou incomplet.',
                'email.required' => 'Veuillez renseigner votre adresse e-mail.',
                'email.email' => 'Veuillez renseigner une adresse e-mail valide.',
                'password.required' => 'Veuillez choisir un mot de passe.',
                'password.min' => 'Votre mot de passe doit contenir au moins 8 caracteres.',
                'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            ],
            [
                'email' => 'adresse e-mail',
                'password' => 'mot de passe',
            ]
        );

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
            ? redirect()->route('client.home')->with('success', 'Mot de passe reinitialise !')
            : back()->withErrors(['email' => __($status)]);
    }

    protected function resolveClientFrom(string $token): ClientProfile
    {
        return ClientProfile::where('password_setup_token_hash', hash('sha256', $token))
            ->where('password_setup_expires_at', '>', now())
            ->firstOrFail();
    }
}
