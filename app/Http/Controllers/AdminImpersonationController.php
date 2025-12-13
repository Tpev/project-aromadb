<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminImpersonationController extends Controller
{
    public function start(Request $request, User $user)
    {
        abort_unless(Auth::check() && Auth::user()->is_admin, 403);

        // prevent nesting impersonations
        if (!session()->has('impersonator_id')) {
            session(['impersonator_id' => Auth::id()]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard')->with('success', "Connecté en tant que {$user->name}.");
    }

    public function stop(Request $request)
    {
        abort_unless(session()->has('impersonator_id'), 403);

        $adminId = session()->pull('impersonator_id');

        Auth::loginUsingId($adminId);
        $request->session()->regenerate();

        return redirect('/admin')->with('success', "Retour au compte admin.");
    }
}
