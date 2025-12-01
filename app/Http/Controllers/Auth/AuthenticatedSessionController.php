<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Authentifier l'utilisateur
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Récupérer l'utilisateur authentifié
            $user = Auth::user();
			if (Auth::user()->license_status === 'inactive') {
				return redirect('/license-tiers/pricing');
			}
            // Redirection conditionnelle basée sur le rôle de l'utilisateur
            if ($user->is_therapist) {
                return redirect()->intended('/dashboard-pro');
            }
			if ($user->is_admin) {
                return redirect()->intended('/admin');
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => __('Les informations d\'identification fournies ne correspondent pas à nos enregistrements.'),
        ])->onlyInput('email');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
    public function createMobile() // no strict : View type, we may return RedirectResponse
    {
        // Si déjà connecté, on redirige proprement
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->is_therapist) {
                return redirect()->route('mobile.dashboard');
            }

            if ($user->is_admin) {
                return redirect()->intended('/admin');
            }

            return redirect()->intended('/dashboard');
        }

        // Sinon on affiche le formulaire mobile
        return view('mobile.auth.login');
    }

    /**
     * MOBILE LOGIN SUBMISSION
     */
    public function storeMobile(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->license_status === 'inactive') {
                return redirect('/license-tiers/pricing');
            }

            // 🔥 Ne plus utiliser "intended" ici pour le mobile,
            // on force le tableau de bord mobile
            if ($user->is_therapist) {
                return redirect()->route('mobile.dashboard');
            }

            if ($user->is_admin) {
                return redirect()->intended('/admin');
            }

            // fallback : espace public mobile
            return redirect('/mobile');
        }

        return back()->withErrors([
            'email' => __('Identifiants incorrects.'),
        ])->onlyInput('email');
    }
}
