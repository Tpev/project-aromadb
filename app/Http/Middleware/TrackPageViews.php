<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\PageViewLog;
use Carbon\Carbon;

class TrackPageViews
{
    public function handle($request, Closure $next)
    {
        // Log the current page view with additional details
        PageViewLog::create([
            'url' => $request->path(),
            'viewed_at' => Carbon::now(),
            'ip_address' => $request->ip(),                // Get the IP address
            'session_id' => $request->session()->getId(),  // Get the session ID
            'referrer' => $request->headers->get('referer') // Get the referrer
        ]);

        return $next($request);
    }
}
