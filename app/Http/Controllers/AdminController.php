<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PageViewLog;
use Carbon\Carbon;
use App\Models\UserLicense;
use App\Models\LicenseHistory;
use App\Models\LicenseTier;


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
            'Exabot', 'facebot', 'ia_archiver', 'MJ12bot', 'AsyncHttp', 'python'
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
        $sessionsToday = (clone $pageViewsQuery)
            ->whereDate('viewed_at', '=', $today)
            ->distinct('session_id')
            ->count('session_id');

        $sessionsYesterday = (clone $pageViewsQuery)
            ->whereDate('viewed_at', '=', $yesterday)
            ->distinct('session_id')
            ->count('session_id');

        $sessionsThisWeek = (clone $pageViewsQuery)
            ->where('viewed_at', '>=', $startOfWeek)
            ->distinct('session_id')
            ->count('session_id');

        $sessionsLastWeek = (clone $pageViewsQuery)
            ->whereBetween('viewed_at', [$startOfLastWeek, $startOfLastWeek->copy()->endOfWeek()])
            ->distinct('session_id')
            ->count('session_id');

        $sessionsThisMonth = (clone $pageViewsQuery)
            ->where('viewed_at', '>=', $startOfMonth)
            ->distinct('session_id')
            ->count('session_id');

        $sessionsLastMonth = (clone $pageViewsQuery)
            ->whereBetween('viewed_at', [$startOfLastMonth, $startOfLastMonth->copy()->endOfMonth()])
            ->distinct('session_id')
            ->count('session_id');

        // Add sessionsTotal
        $sessionsTotal = (clone $pageViewsQuery)
            ->distinct('session_id')
            ->count('session_id');

        // Get the page view stats: group by session_id and url, and retrieve the necessary fields
        $pageViews = $pageViewsQuery
            ->select(
                'url',
                'session_id',
                'ip_address',
                'referrer',
                'user_agent',
                \DB::raw('COUNT(*) as view_count'),
                \DB::raw('MAX(viewed_at) as last_viewed_at')
            )
            ->groupBy('url', 'session_id', 'ip_address', 'referrer', 'user_agent')
            ->orderByDesc('last_viewed_at')
            ->limit(100) // Limit the results to the last 100 entries
            ->get();

        // Eager load related models
        $users = User::with(['appointments', 'clientProfiles', 'questionnaires'])->get();

        // Pass the counts to the view
        return view('admin.index', compact(
            'users',
            'pageViews',
            'sessionsToday',
            'sessionsYesterday',
            'sessionsThisWeek',
            'sessionsLastWeek',
            'sessionsThisMonth',
            'sessionsLastMonth',
            'sessionsTotal' // Include sessionsTotal here
        ));
    }
}

	
public function showLicenseManagement()
{
	        // Check if the user is an admin
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access');
        }
    // Get all therapists
    $therapists = User::where('is_therapist', true)->get();

    // Get all available licenses
    $availableLicenses = LicenseTier::all();

    return view('admin.licenses.index', compact('therapists', 'availableLicenses'));
}


    /**
     * Assign a license manually to a therapist.
     */
public function assignLicense(Request $request, $therapistId)
{
	        // Check if the user is an admin
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access');
        }
    // Validate the request
    $request->validate([
        'license_tier_name' => 'required|exists:license_tiers,name',
    ]);

    // Find the therapist
    $therapist = User::findOrFail($therapistId);

    // Find the license tier by name
    $licenseTier = LicenseTier::where('name', $request->license_tier_name)->firstOrFail();

    // Calculate expiration date: now + duration_days from the license tier
    $expirationDate = now()->addDays($licenseTier->duration_days);

    // Update or create a license for the therapist
    $license = UserLicense::updateOrCreate(
        ['user_id' => $therapist->id],
        [
            'license_tier_id' => $licenseTier->id,
            'start_date' => now(),
            'expiration_date' => $expirationDate,
        ]
    );

    // Log the license history
    LicenseHistory::create([
        'user_id' => $therapist->id,
        'license_tier_id' => $licenseTier->id,
        'assigned_by' => auth()->user()->id, // The admin assigning the license
        'expires_at' => $expirationDate,
		'start_date' => now(),
    ]);

    return redirect()->route('admin.license')->with('success', 'License assigned successfully!');
}


}
