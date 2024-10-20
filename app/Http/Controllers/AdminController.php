<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PageViewLog;
use Carbon\Carbon;
use App\Models\UserLicense;
use App\Models\LicenseHistory;
use App\Models\LicenseTier;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with session KPIs.
     */
    public function index()
    {
        // Check if the user is an admin
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        // Get the list of users with related models
        $users = User::with(['appointments', 'clientProfiles', 'questionnaires'])->get();

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

        // Define current timestamps
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek = (clone $startOfLastWeek)->endOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = (clone $startOfLastMonth)->endOfMonth();

        // Define time frames with their respective query conditions
        $timeFrames = [
            'today' => function ($query) use ($today) {
                $query->whereDate('viewed_at', '=', $today);
            },
            'yesterday' => function ($query) use ($yesterday) {
                $query->whereDate('viewed_at', '=', $yesterday);
            },
            'this_week' => function ($query) use ($startOfWeek) {
                $query->where('viewed_at', '>=', $startOfWeek);
            },
            'last_week' => function ($query) use ($startOfLastWeek, $endOfLastWeek) {
                $query->whereBetween('viewed_at', [$startOfLastWeek, $endOfLastWeek]);
            },
            'this_month' => function ($query) use ($startOfMonth) {
                $query->where('viewed_at', '>=', $startOfMonth);
            },
            'last_month' => function ($query) use ($startOfLastMonth, $endOfLastMonth) {
                $query->whereBetween('viewed_at', [$startOfLastMonth, $endOfLastMonth]);
            },
            'total' => function ($query) {
                // No date filter for total
            },
        ];

        // Initialize an array to hold session counts per time frame and source
        $sessionsData = [];

        foreach ($timeFrames as $label => $filter) {
            // Clone the base query for each time frame
            $query = (clone $pageViewsQuery);

            // Apply the time frame filter if any
            if (is_callable($filter)) {
                $filter($query);
            }

            // Fetch distinct session_id and their referrers
            $sessions = $query->select('session_id', 'referrer')
                ->distinct('session_id')
                ->get();

            // Initialize traffic source counters
            $trafficSources = [
                'Social Media' => 0,
                'Organic' => 0,
                'Paid' => 0,
                'Direct' => 0,
                'Other' => 0,
            ];

            // Categorize each session and increment counters
            foreach ($sessions as $session) {
                $source = $this->categorizeReferrer($session->referrer);
                if (array_key_exists($source, $trafficSources)) {
                    $trafficSources[$source]++;
                } else {
                    $trafficSources['Other']++;
                }
            }

            // Total sessions for this time frame
            $totalSessions = $sessions->count();

            // Assign to sessionsData
            $sessionsData[$label] = [
                'total' => $totalSessions,
                'sources' => $trafficSources,
            ];
        }

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

        // Additional KPIs (Replace with your actual logic)
        $totalClients = User::count(); // Example: Total number of clients
        $upcomingAppointments = User::withCount(['appointments' => function ($query) {
            $query->where('appointment_date', '>=', Carbon::now());
        }])->where('is_therapist', true)->sum('appointments_count'); // Example: Total upcoming appointments
        $totalInvoices = \App\Models\Invoice::count(); // Example: Total invoices issued
        $pendingInvoices = \App\Models\Invoice::where('status', 'pending')->count(); // Example: Pending invoices
        $monthlyRevenue = \App\Models\Invoice::whereMonth('invoice_date', Carbon::now()->month)
            ->where('status', 'paid')
            ->sum('total_amount'); // Example: Revenue for the current month

        // Prepare data for charts
        $appointmentsPerMonth = \App\Models\Appointment::select(
                \DB::raw('MONTH(appointment_date) as month'),
                \DB::raw('COUNT(*) as count')
            )
            ->whereYear('appointment_date', Carbon::now()->year)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $monthlyRevenueData = \App\Models\Invoice::select(
                \DB::raw('MONTH(invoice_date) as month'),
                \DB::raw('SUM(total_amount) as revenue')
            )
            ->whereYear('invoice_date', Carbon::now()->year)
            ->where('status', 'paid')
            ->groupBy('month')
            ->pluck('revenue', 'month')
            ->toArray();

        // Define month labels (e.g., January, February, etc.)
        $months = Carbon::create()->months()->mapWithKeys(function ($month) {
            return [$month => ucfirst($month)];
        })->toArray();

        // Pass all data to the view
        return view('admin.dashboard', compact(
            'users',
            'pageViews',
            'totalClients',
            'upcomingAppointments',
            'totalInvoices',
            'pendingInvoices',
            'monthlyRevenue',
            'appointmentsPerMonth',
            'monthlyRevenueData',
            'months',
            'sessionsData'
        ));
    }

    /**
     * Categorize a referrer into a traffic source category.
     *
     * @param string|null $referrer
     * @return string
     */
    private function categorizeReferrer($referrer)
    {
        if (!$referrer) {
            return 'Direct';
        }

        $referrer = strtolower($referrer);

        // Check for Social Media sources
        if (Str::contains($referrer, ['facebook.com', 'instagram.com', 'whatsapp.com'])) {
            return 'Social Media';
        }

        // Check for Google sources
        if (Str::contains($referrer, 'google.com')) {
            if (Str::contains($referrer, ['gclid=', 'gad_source='])) {
                return 'Paid';
            } else {
                return 'Organic';
            }
        }

        // If none of the above, categorize as Other
        return 'Other';
    }

    /**
     * Show the license management dashboard.
     */
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
