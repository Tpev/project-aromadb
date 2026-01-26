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
use App\Models\Product;
use App\Models\Availability;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Importing the DB facade
use Illuminate\Support\Str;        // Importing the Str facade
use App\Services\ProfileAvatarService;

class AdminController extends Controller
{
public function updateLicenseProduct(Request $request, User $therapist)
{
    // Full list of allowed licenses
    $allowed = [
        // Legacy
        'Starter Mensuelle',
        'Starter Annuelle',
        'Pro Mensuelle',
        'Pro Annuelle',
        'Essai Gratuit',

        // New system
        'new_free',
        'new_trial',
        'new_starter_mensuelle',
        'new_starter_annuelle',
        'new_pro_mensuelle',
        'new_pro_annuelle',
        'new_premium_mensuelle',
        'new_premium_annuelle',
    ];

    $request->validate([
        'license_product' => 'required|string|in:' . implode(',', $allowed),
    ]);

    $therapist->license_product = $request->license_product;
    $therapist->save();

    return back()->with('success', 'Licence mise à jour avec succès.');
}

	
public function toggleLicense(Request $request, User $therapist)
{
	    // Check if the user is an admin
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }
    $therapist->license_status = $request->input('license_status');
    $therapist->save();

    return back()->with('success', 'License status updated successfully.');
}
	
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
        ->where('user_agent', '!=', '')
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

$sessionsTotal = (clone $pageViewsQuery)
    ->distinct('session_id')
    ->count('session_id');


    $sessionsData = [
        'today' => $sessionsToday,
        'yesterday' => $sessionsYesterday,
        'this_week' => $sessionsThisWeek,
        'last_week' => $sessionsLastWeek,
        'this_month' => $sessionsThisMonth,
        'last_month' => $sessionsLastMonth,
        'total' => $sessionsTotal,
    ];

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
        ->limit(100)
        ->get();

    // Additional KPIs
    $totalClients = ClientProfile::count();
    $totalAppointments = Appointment::count();
    $upcomingAppointments = Appointment::where('appointment_date', '>=', Carbon::now())->count();
    $totalInvoices = Invoice::count();
    $pendingInvoices = Invoice::where('status', 'pending')->count();
    $monthlyRevenue = Invoice::whereMonth('invoice_date', Carbon::now()->month)
        ->where('status', 'paid')
        ->sum('total_amount');

return view('admin.index', compact(
    'users',
    'pageViews',
    'sessionsToday',
    'sessionsYesterday',
    'sessionsThisWeek',
    'sessionsLastWeek',
    'sessionsThisMonth',
    'sessionsLastMonth',
    'sessionsTotal',
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


public function indexTherapists()
{
    // Check if the user is an admin
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }

    // Get all therapists and count information requests in one query
    $therapists = User::where('is_therapist', true)
                      ->withCount('informationRequests')
                      ->get();

    foreach ($therapists as $therapist) {
        $score = 0;
        $total = 11; // 9 existing + 2 new criteria

        // Slug
        if ($therapist->slug) {
            $score++;
        }

        // Stripe setup
        if ($therapist->stripe_account_id) {
            $score++;
        }

        // Online booking
        if ($therapist->accept_online_appointments) {
            $score++;
        }

        // Prestation (Product)
        if ($therapist->products()->exists()) {
            $score++;
        }

        // Disponibilité
        if ($therapist->availabilities()->exists()) {
            $score++;
        }

        // Appointment
        if ($therapist->appointments()->exists()) {
            $score++;
        }

        // Invoice
        if ($therapist->invoices()->where('type', 'invoice')->exists()) {
            $score++;
        }

        // Quote (NEW)
        if ($therapist->invoices()->where('type', 'quote')->exists()) {
            $score++;
        }

        // ClientProfile
        if ($therapist->clientProfiles()->exists()) {
            $score++;
        }

        // Event
        if ($therapist->events()->exists()) {
            $score++;
        }

        // Inventory Items (NEW)
        if ($therapist->inventoryItems()->exists()) {
            $score++;
        }

        $therapist->onboarding_score = $score;
        $therapist->onboarding_total = $total;
    }

    return view('admin.therapists.index', compact('therapists'));
}



    /**
     * Display detailed information about a specific therapist.
     */
public function showTherapist($id)
{
    // Check if the user is an admin
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }

    // Find the therapist
    $therapist = User::where('is_therapist', true)->findOrFail($id);

    // Eager load relationships
    $therapist->load([
        'products',
        'availabilities',
        'appointments',
        'invoices',
        'clientProfiles',
        'events',
        'inventoryItems' // NEW
    ]);

    // Calculate onboarding score
    $score = 0;
    $total = 11; // 9 original + 2 new

    // Slug
    if ($therapist->slug) {
        $score++;
    }

    // Stripe setup
    if ($therapist->stripe_account_id) {
        $score++;
    }

    // Online booking
    if ($therapist->accept_online_appointments) {
        $score++;
    }

    // Prestation
    if ($therapist->products()->exists()) {
        $score++;
    }

    // Disponibilité
    if ($therapist->availabilities()->exists()) {
        $score++;
    }

    // Appointment
    if ($therapist->appointments()->exists()) {
        $score++;
    }

    // Invoice
    if ($therapist->invoices()->where('type', 'invoice')->exists()) {
        $score++;
    }

    // Quote (NEW)
    if ($therapist->invoices()->where('type', 'quote')->exists()) {
        $score++;
    }

    // ClientProfile
    if ($therapist->clientProfiles()->exists()) {
        $score++;
    }

    // Event
    if ($therapist->events()->exists()) {
        $score++;
    }

    // Inventory Items (NEW)
    if ($therapist->inventoryItems()->exists()) {
        $score++;
    }

    $therapist->onboarding_score = $score;
    $therapist->onboarding_total = $total;

    // Weekly usage statistics (this month actually)
    $startOfMonth = Carbon::now()->startOfMonth();
    $endOfMonth = Carbon::now()->endOfMonth();

    $appointmentsThisWeek = $therapist->appointments()
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->count();

    $invoicesThisWeek = $therapist->invoices()
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->count();

    $clientProfilesThisWeek = $therapist->clientProfiles()
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->count();

    $eventsThisWeek = $therapist->events()
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->count();

$counts = [
    'products' => $therapist->products()->count(),
    'availabilities' => $therapist->availabilities()->count(),
    'appointments' => $therapist->appointments()->count(),
    'invoices' => $therapist->invoices()->count(),
    'quotes' => $therapist->invoices()->where('type', 'quote')->count(),
    'clientProfiles' => $therapist->clientProfiles()->count(),
    'events' => $therapist->events()->count(),
    'inventoryItems' => $therapist->inventoryItems()->count(),
];

$lastTimestamps = [
    'products' => optional($therapist->products()->latest()->first())->created_at,
    'availabilities' => optional($therapist->availabilities()->latest()->first())->created_at,
    'appointments' => optional($therapist->appointments()->latest()->first())->created_at,
    'invoices' => optional($therapist->invoices()->latest()->first())->created_at,
    'quotes' => optional($therapist->invoices()->where('type', 'quote')->latest()->first())->created_at,
    'clientProfiles' => optional($therapist->clientProfiles()->latest()->first())->created_at,
    'events' => optional($therapist->events()->latest()->first())->created_at,
    'inventoryItems' => optional($therapist->inventoryItems()->latest()->first())->created_at,
];



return view('admin.therapists.show', compact(
    'therapist',
    'appointmentsThisWeek',
    'invoicesThisWeek',
    'clientProfilesThisWeek',
    'eventsThisWeek',
    'counts',
    'lastTimestamps'
));


}

	
	    public function welcome()
    {
        // Check if the user is an admin
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        return view('admin.welcome');
    }
public function updateTherapistPicture(Request $request, User $therapist)
{
    abort_unless(auth()->user()?->isAdmin(), 403);

    $request->validate([
        'profile_picture' => 'required|mimes:jpeg,png,jpg,gif,svg,heic|max:3048',
    ]);
    \Log::info('updateTherapistPicture hit', [
        'therapist_id' => $therapist->id,
        'has_file' => $request->hasFile('profile_picture'),
    ]);
    $path320 = \App\Services\ProfileAvatarService::store(
        $request->file('profile_picture'),
        $therapist->getKey() // int guaranteed
    );

    $therapist->profile_picture = $path320;
    $therapist->save();

    return back()->with('success', 'Profile picture updated successfully!');
}



public function updateTherapistSettings(Request $request, $id)
{
    // Check if the user is an admin
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }

    // Find the therapist by ID
    $therapist = User::where('is_therapist', true)->findOrFail($id);

    // Since checkboxes only send a value when checked, use has() to determine their state.
    $therapist->verified = $request->has('verified');
    $therapist->visible_annuarire_admin_set = $request->has('visible_annuarire_admin_set');

    $therapist->save();

    return redirect()->route('admin.therapists.show', $id)
                     ->with('success', 'Settings updated successfully!');
}
public function updateTherapistAddress(Request $request, $id)
{
    // Check if the user is an admin
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }

    // Find the therapist by ID
    $therapist = User::where('is_therapist', true)->findOrFail($id);

    // Validate the incoming request
    $data = $request->validate([
        'street_address_setByAdmin' => 'nullable|string|max:255',
        'address_line2_setByAdmin'  => 'nullable|string|max:255',
        'city_setByAdmin'           => 'nullable|string|max:100',
        'state_setByAdmin'          => 'nullable|string|max:100',
        'postal_code_setByAdmin'    => 'nullable|string|max:20',
        'country_setByAdmin'        => 'nullable|string|max:100',
        'latitude_setByAdmin'       => 'nullable|numeric',
        'longitude_setByAdmin'      => 'nullable|numeric',
    ]);

    // Update the therapist with the validated address fields
    $therapist->update($data);

    return redirect()->route('admin.therapists.show', $id)
                     ->with('success', 'Address updated successfully!');
}

public function editLesson($id)
{
	    // Check if the user is an admin
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }
    // Assumes the admin check is already handled in this controller.
    $lesson = \App\Models\Lesson::findOrFail($id);
    return view('admin.lesson.edit', compact('lesson'));
}

public function updateLesson(Request $request, $id)
{
	    // Check if the user is an admin
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }
    $request->validate([
        'content' => 'required',
    ]);

    $lesson = \App\Models\Lesson::findOrFail($id);
    $lesson->update([
        'content' => $request->input('content'),
    ]);

    return redirect()->back()->with('success', 'Le contenu de la leçon a été mis à jour avec succès.');
}

public function updateFeatured(Request $request, User $therapist)
{
    // Admin gate
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }

    // Validate inputs (featured_until is optional)
    $data = $request->validate([
        'is_featured'      => ['nullable','boolean'],
        'featured_until'   => ['nullable','date'], // you can add 'after:now' if you prefer
        'featured_weight'  => ['nullable','integer','between:0,100'],
    ]);

    // Normalize checkbox (when unchecked, it's absent)
    $therapist->is_featured = $request->has('is_featured');

    // Optional extras
    if (array_key_exists('featured_until', $data)) {
        $therapist->featured_until = $data['featured_until'];
    }
    if (array_key_exists('featured_weight', $data)) {
        $therapist->featured_weight = $data['featured_weight'] ?? 0;
    }

    $therapist->save();

    // (Optional) bust home cache if you cache featured section
    // Cache::forget('home.featuredTherapists');

    return back()->with('success', 'Featured settings updated successfully.');
}
public function weeklyUsage()
{
    // Admin gate (same logic as your other pages)
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }

    // Default last 12 weeks (including current week)
    $weeksBack = (int) request('weeks', 12);
    if ($weeksBack < 4) $weeksBack = 4;
    if ($weeksBack > 104) $weeksBack = 104;

    $tz = 'Europe/Paris';
    $end = Carbon::now($tz)->endOfDay();
    $start = Carbon::now($tz)->startOfWeek()->subWeeks($weeksBack - 1)->startOfDay();

    // Helper: ISO yearweek key (MySQL/MariaDB)
    // YEARWEEK(date, 3) => ISO week (Mon-Sun)
    $yearWeekExpr = "YEARWEEK(created_at, 3)";

    // IMPORTANT:
    // - Adjust therapist/user owner columns if needed.
    //   I’m assuming:
    //   appointments.therapist_id exists
    //   invoices.therapist_id exists
    //
    // If your schema uses user_id, replace therapist_id -> user_id.

    $appointments = DB::table('appointments')
        ->whereBetween('created_at', [$start, $end])
        ->selectRaw("$yearWeekExpr as yw")
        ->selectRaw("COUNT(*) as appointments_count")
        ->selectRaw("COUNT(DISTINCT therapist_id) as appointments_users")
        ->groupBy('yw')
        ->pluck(DB::raw("JSON_OBJECT('appointments_count', appointments_count, 'appointments_users', appointments_users)"), 'yw');

    $invoices = DB::table('invoices')
        ->whereBetween('created_at', [$start, $end])
        ->where('type', 'invoice')
        ->selectRaw("$yearWeekExpr as yw")
        ->selectRaw("COUNT(*) as invoices_count")
        ->selectRaw("COUNT(DISTINCT therapist_id) as invoices_users")
        ->groupBy('yw')
        ->pluck(DB::raw("JSON_OBJECT('invoices_count', invoices_count, 'invoices_users', invoices_users)"), 'yw');

    $quotes = DB::table('invoices')
        ->whereBetween('created_at', [$start, $end])
        ->where('type', 'quote')
        ->selectRaw("$yearWeekExpr as yw")
        ->selectRaw("COUNT(*) as quotes_count")
        ->selectRaw("COUNT(DISTINCT therapist_id) as quotes_users")
        ->groupBy('yw')
        ->pluck(DB::raw("JSON_OBJECT('quotes_count', quotes_count, 'quotes_users', quotes_users)"), 'yw');

    // Build a continuous list of weeks (so empty weeks still show)
    $weeks = [];
    $cursor = $start->copy()->startOfWeek();
    while ($cursor->lte($end)) {
        $weekStart = $cursor->copy();
        $weekEnd = $cursor->copy()->endOfWeek();

        // ISO yearweek key for matching the SQL YEARWEEK(...,3)
        // We compute it in PHP reliably:
        $isoYear = (int) $weekStart->isoWeekYear;
        $isoWeek = (int) $weekStart->isoWeek;
        $yw = (int) sprintf('%d%02d', $isoYear, $isoWeek);

        $a = isset($appointments[$yw]) ? json_decode($appointments[$yw], true) : [];
        $i = isset($invoices[$yw]) ? json_decode($invoices[$yw], true) : [];
        $q = isset($quotes[$yw]) ? json_decode($quotes[$yw], true) : [];

        $appointmentsCount = (int) ($a['appointments_count'] ?? 0);
        $appointmentsUsers = (int) ($a['appointments_users'] ?? 0);

        $invoicesCount = (int) ($i['invoices_count'] ?? 0);
        $invoicesUsers = (int) ($i['invoices_users'] ?? 0);

        $quotesCount = (int) ($q['quotes_count'] ?? 0);
        $quotesUsers = (int) ($q['quotes_users'] ?? 0);

        // Active therapists this week (union distinct therapist_id across tracked tables)
        $activeUsers = DB::query()
            ->fromSub(
                DB::table('appointments')
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->selectRaw("DISTINCT therapist_id as tid")
                    ->union(
                        DB::table('invoices')
                            ->whereBetween('created_at', [$weekStart, $weekEnd])
                            ->selectRaw("DISTINCT therapist_id as tid")
                    ),
                'u'
            )
            ->count('tid');

        $weeks[] = [
            'yw' => $yw,
            'week_start' => $weekStart->copy(),
            'week_end' => $weekEnd->copy(),
            'appointments_count' => $appointmentsCount,
            'appointments_users' => $appointmentsUsers,
            'invoices_count' => $invoicesCount,
            'invoices_users' => $invoicesUsers,
            'quotes_count' => $quotesCount,
            'quotes_users' => $quotesUsers,
            'active_users' => (int) $activeUsers,
        ];

        $cursor->addWeek();
    }

    // Totals on the range
    $totals = [
        'appointments' => array_sum(array_column($weeks, 'appointments_count')),
        'invoices'     => array_sum(array_column($weeks, 'invoices_count')),
        'quotes'       => array_sum(array_column($weeks, 'quotes_count')),
    ];

    return view('admin.usage.weekly', compact('weeks', 'weeksBack', 'start', 'end', 'totals'));
}
}
