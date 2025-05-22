<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ClientAuthController extends Controller
{
    public function showLogin()  { return view('client.login'); }

    public function login(Request $r)
    {
        $cred = $r->validate(['email'=>'required|email','password'=>'required']);
        if (Auth::guard('client')->attempt($cred,$r->boolean('remember'))) {
            $r->session()->regenerate();
            return redirect()->intended('/client/home');
        }
        return back()->withErrors(['email'=>'Identifiants incorrects.']);
    }

    public function logout(Request $r)
    {
        Auth::guard('client')->logout();
        $r->session()->invalidate(); $r->session()->regenerateToken();
        return redirect('/client/login');
    }
}
