<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\ClientProfile;

class ClientPasswordResetController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.client-passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(
            ['email' => 'required|email|exists:client_profiles,email'],
            [
                'email.required' => 'Veuillez renseigner votre adresse e-mail.',
                'email.email' => 'Veuillez renseigner une adresse e-mail valide.',
                'email.exists' => 'Aucun espace client ne correspond à cette adresse e-mail.',
            ],
            [
                'email' => 'adresse e-mail',
            ]
        );

$status = Password::broker('client_profiles')->sendResetLink(
    $request->only('email')
);

   

        return back()->with('status', __($status));
    }

    public function showResetForm(Request $request, $token)
    {
        return view('auth.client-passwords.reset', [
            'token' => $token,
            'email' => $request->query('email')
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate(
            [
                'token' => 'required',
                'email' => 'required|email|exists:client_profiles,email',
                'password' => 'required|min:8|confirmed',
            ],
            [
                'token.required' => 'Le lien de réinitialisation est invalide ou incomplet.',
                'email.required' => 'Veuillez renseigner votre adresse e-mail.',
                'email.email' => 'Veuillez renseigner une adresse e-mail valide.',
                'email.exists' => 'Aucun espace client ne correspond à cette adresse e-mail.',
                'password.required' => 'Veuillez choisir un mot de passe.',
                'password.min' => 'Votre mot de passe doit contenir au moins 8 caractères.',
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
        ])->setRememberToken(Str::random(60));

        $client->save();
    }
);


        return $status === Password::PASSWORD_RESET
            ? redirect()->route('client.login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
