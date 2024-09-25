<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\PageViewLog;
use Carbon\Carbon;

class TrackPageViews
{
    public function handle($request, Closure $next)
    {

        // Get the client IP address
        $ipAddress = $request->ip(); // This should work in most cases
        
        // Optional: if you have a proxy/load balancer, use this to get real IP
        // $ipAddress = $request->header('X-Forwarded-For') ?? $request->ip();

        // Get the User-Agent string
      
		 

        // Log the current page view with IP address, session, referrer, and user-agent
        PageViewLog::create([
            'url' => $request->path(),
            'session_id' => $request->session()->getId(),
            'ip_address' => $ipAddress,
            'referrer' => $request->headers->get('referer'),  // Track the referrer
            'user_agent' => $request->userAgent(),                       // Track the User-Agent
            'viewed_at' => Carbon::now(),
        ]);

        return $next($request);
    }
}
