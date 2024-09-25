<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PageViewLog;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        // Check if the user is an admin
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        // Get the list of users
        $users = User::all();

        // Define common bot user agents
        $botUserAgents = [
            'bot', 'crawl', 'spider', 'slurp', 'mediapartners', 'Googlebot',
            'Bingbot', 'Baiduspider', 'DuckDuckBot', 'YandexBot', 'Sogou',
            'Exabot', 'facebot', 'ia_archiver', 'MJ12bot'
        ];

        // Base query excluding null and empty user agents, and bot user agents
        $pageViewsQuery = PageViewLog::whereNotNull('user_agent')
            ->where('user_agent', '!=', '')  // Exclude empty user agents
            ->where(function ($query) use ($botUserAgents) {
                foreach ($botUserAgents as $bot) {
                    $query->where('user_agent', 'NOT LIKE', "%$bot%");
                }
            });

        // Get current timestamps
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Perform the date filtering and count unique sessions (distinct session_id) for each period
        $sessionsToday = (clone $pageViewsQuery)->whereDate('viewed_at', '=', $today)->distinct('session_id')->count('session_id');
        $sessionsYesterday = (clone $pageViewsQuery)->whereDate('viewed_at', '=', $yesterday)->distinct('session_id')->count('session_id');
        $sessionsThisWeek = (clone $pageViewsQuery)->where('viewed_at', '>=', $startOfWeek)->distinct('session_id')->count('session_id');
        $sessionsLastWeek = (clone $pageViewsQuery)->whereBetween('viewed_at', [$startOfLastWeek, $startOfLastWeek->copy()->endOfWeek()])->distinct('session_id')->count('session_id');
        $sessionsThisMonth = (clone $pageViewsQuery)->where('viewed_at', '>=', $startOfMonth)->distinct('session_id')->count('session_id');
        $sessionsLastMonth = (clone $pageViewsQuery)->whereBetween('viewed_at', [$startOfLastMonth, $startOfLastMonth->copy()->endOfMonth()])->distinct('session_id')->count('session_id');

        // Get the page view stats: group by session_id and url, and retrieve the necessary fields
        $pageViews = $pageViewsQuery
            ->select('url', 'session_id', 'ip_address', 'referrer', 'user_agent', \DB::raw('COUNT(*) as view_count'), \DB::raw('MAX(viewed_at) as last_viewed_at'))
            ->groupBy('url', 'session_id', 'ip_address', 'referrer', 'user_agent')
            ->orderByDesc('last_viewed_at')
            ->get();

        // Pass the counts to the view
        return view('admin.index', compact('users', 'pageViews', 'sessionsToday', 'sessionsYesterday', 'sessionsThisWeek', 'sessionsLastWeek', 'sessionsThisMonth', 'sessionsLastMonth'));
    }
}
