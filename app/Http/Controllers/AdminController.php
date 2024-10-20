<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PageViewLog;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\ClientProfile;
use App\Models\Questionnaire;
use App\Models\LicenseTier;
use App\Models\UserLicense;
use App\Models\LicenseHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Importing the DB facade
use Illuminate\Support\Str;        // Importing the Str facade

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with session KPIs and traffic source breakdowns.
     */
    public function index()
    {
        // Check if the user is an admin
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        // Fetch all users with related models
        $users = User::with(['appointments', 'clientProfiles', 'questionnaires'])->get();

        // Define common bot user agents to exclude from page views
        $botUserAgents = [
            'bot', 'crawl', 'spider', 'slurp', 'mediapartners', 'googlebot',
            'bingbot', 'baiduspider', 'duckduckbot', 'yandexbot', 'sogou',
            'exabot', 'facebot', 'ia_archiver', 'mj12bot', 'asynchttp', 'python'
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
            ->whereBetween('viewed_at', [$startOfLastWeek, $endOfLastWeek])
            ->distinct('session_id')
            ->count('session_id');

        $sessionsThisMonth = (clone $pageViewsQuery)
            ->where('viewed_at', '>=', $startOfMonth)
            ->distinct('session_id')
            ->count('session_id');

        $sessionsLastMonth = (clone $pageViewsQuery)
            ->whereBetween('viewed_at', [$startOfLastMonth, $endOfLastMonth])
            ->distinct('session_id')
            ->count('session_id');

        // Total sessions across all time frames
        $sessionsTotal = (clone $pageViewsQuery)
            ->distinct('session_id')
            ->count('session_id');

        // Initialize an array to hold session counts per time frame
        $sessionsData = [
            'today' => $sessionsToday,
            'yesterday' => $sessionsYesterday,
            'this_week' => $sessionsThisWeek,
            'last_week' => $sessionsLastWeek,
            'this_month' => $sessionsThisMonth,
            'last_month' => $sessionsLastMonth,
            'total' => $sessionsTotal,
        ];

        /**
         * Categorize the traffic source based on all referrers in a session.
         *
         * @param string $sessionId
         * @return string
         */
        $categorizeSessionTraffic = function ($sessionId) use ($pageViewsQuery) {
            // Retrieve all referrers for the given session
            $referrers = PageViewLog::where('session_id', $sessionId)
                ->pluck('referrer')
                ->filter() // Remove null or empty referrers
                ->map(function ($referrer) {
                    return strtolower($referrer);
                })
                ->unique()
                ->toArray();

            // Initialize flags
            $isPaid = false;
            $isSocialMedia = false;
            $isOrganic = false;
            $isDirect = true; // Assume direct unless a referrer is found

            // Check for Paid traffic indicators
            foreach ($referrers as $referrer) {
                if (Str::contains($referrer, ['gclid=', 'gad_source='])) {
                    $isPaid = true;
                    break; // Paid takes precedence
                }
            }

            // If not Paid, check for Social Media sources
            if (!$isPaid) {
                foreach ($referrers as $referrer) {
                    if (Str::contains($referrer, ['facebook.com', 'instagram.com', 'whatsapp.com'])) {
                        $isSocialMedia = true;
                        break;
                    }
                }
            }

            // If not Paid or Social Media, check for Organic Google traffic
            if (!$isPaid && !$isSocialMedia) {
                foreach ($referrers as $referrer) {
                    if (Str::contains($referrer, 'google.com')) {
                        $isOrganic = true;
                        break;
                    }
                }
            }

            // Determine if the session is Direct (no referrers)
            if (!empty($referrers)) {
                $isDirect = false;
            }

            // Determine the traffic source based on flags
            if ($isPaid) {
                return 'Paid';
            } elseif ($isSocialMedia) {
                return 'Social Media';
            } elseif ($isOrganic) {
                return 'Organic';
            } elseif ($isDirect) {
                return 'Direct';
            } else {
                return 'Other';
            }
        };

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

        // Initialize an array to hold traffic source counts per time frame
        $trafficSourcesData = [];

        foreach ($timeFrames as $label => $filter) {
            // Clone the base query for each time frame
            $query = (clone $pageViewsQuery);

            // Apply the time frame filter if any
            if (is_callable($filter)) {
                $filter($query);
            }

            // Fetch distinct session_ids within the time frame
            $sessions = $query->select('session_id')
                ->distinct('session_id')
                ->pluck('session_id')
                ->toArray();

            // Initialize traffic source counters
            $trafficSources = [
                'Paid' => 0,
                'Social Media' => 0,
                'Organic' => 0,
                'Direct' => 0,
                'Other' => 0,
            ];

            // Categorize each session based on all its referrers
            foreach ($sessions as $sessionId) {
                $source = $categorizeSessionTraffic($sessionId);
                if (array_key_exists($source, $trafficSources)) {
                    $trafficSources[$source]++;
                } else {
                    $trafficSources['Other']++;
                }
            }

            // Assign to trafficSourcesData
            $trafficSourcesData[$label] = $trafficSources;
        }

        // Get the page view stats: group by session_id and url, and retrieve the necessary fields
        $pageViews = $pageViewsQuery
            ->select(
                'url',
                'session_id',
                'ip_address',
                'referrer',
                'user_agent',
                DB::raw('COUNT(*) as view_count'),
                DB::raw('MAX(viewed_at) as last_viewed_at')
            )
            ->groupBy('url', 'session_id', 'ip_address', 'referrer', 'user_agent')
            ->orderByDesc('last_viewed_at')
            ->limit(100) // Limit the results to the last 100 entries
            ->get();

        // Additional KPIs
        $totalClients = ClientProfile::count(); // Total number of client profiles
        $totalAppointments = Appointment::count(); // Total number of appointments
        $upcomingAppointments = Appointment::where('appointment_date', '>=', Carbon::now())->count(); // Upcoming appointments
        $totalInvoices = Invoice::count(); // Total invoices issued
        $pendingInvoices = Invoice::where('status', 'pending')->count(); // Pending invoices
        $monthlyRevenue = Invoice::whereMonth('invoice_date', Carbon::now()->month)
            ->where('status', 'paid')
            ->sum('total_amount'); // Revenue for the current month

        // Pass all data to the view
        return view('admin.dashboard', compact(
            'users',
            'pageViews',
            'sessionsToday',
            'sessionsYesterday',
            'sessionsThisWeek',
            'sessionsLastWeek',
            'sessionsThisMonth',
            'sessionsLastMonth',
            'sessionsTotal',
            'trafficSourcesData',
            'totalClients',
            'totalAppointments',
            'upcomingAppointments',
            'totalInvoices',
            'pendingInvoices',
            'monthlyRevenue'
        ));
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
