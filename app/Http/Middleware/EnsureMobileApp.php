<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMobileApp
{
    public function handle(Request $request, Closure $next)
    {
        $ua = $request->header('User-Agent', '');

        if (! str_contains($ua, 'AromaMadeMobile')) {
            abort(404); // or redirect('/') if you prefer
        }

        return $next($request);
    }
}
