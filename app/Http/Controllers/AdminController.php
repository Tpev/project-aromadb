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
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }

    $weeksBack = (int) request('weeks', 12);
    if ($weeksBack < 4) $weeksBack = 4;
    if ($weeksBack > 104) $weeksBack = 104;

    $tz = 'Europe/Paris';
    $end = \Carbon\Carbon::now($tz)->endOfDay();
    $start = \Carbon\Carbon::now($tz)->startOfWeek()->subWeeks($weeksBack - 1)->startOfDay();

    // Helper: aggregate a table by ISO yearweek on a date column
    $weeklyAgg = function (
        string $table,
        string $dateCol = 'created_at',
        ?string $userCol = 'user_id',
        ?callable $modify = null
    ) use ($start, $end) {
        $yearWeekExpr = "YEARWEEK($dateCol, 3)";

        $q = DB::table($table)
            ->whereNotNull($dateCol)
            ->whereBetween($dateCol, [$start, $end])
            ->selectRaw("$yearWeekExpr as yw")
            ->selectRaw("COUNT(*) as c");

        if ($userCol) {
            $q->selectRaw("COUNT(DISTINCT $userCol) as u");
        } else {
            $q->selectRaw("0 as u");
        }

        if ($modify) {
            $modify($q);
        }

        $rows = $q->groupBy('yw')->get();

        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r->yw] = [
                'count' => (int) $r->c,
                'users' => (int) $r->u,
            ];
        }
        return $out;
    };

    // Helper: meetings are owned via appointments.user_id (join)
    $weeklyMeetingsAgg = function () use ($start, $end) {
        $yearWeekExpr = "YEARWEEK(meetings.created_at, 3)";

        $rows = DB::table('meetings')
            ->join('appointments', 'appointments.id', '=', 'meetings.appointment_id')
            ->whereBetween('meetings.created_at', [$start, $end])
            ->selectRaw("$yearWeekExpr as yw")
            ->selectRaw("COUNT(*) as c")
            ->selectRaw("COUNT(DISTINCT appointments.user_id) as u")
            ->groupBy('yw')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r->yw] = [
                'count' => (int) $r->c,
                'users' => (int) $r->u,
            ];
        }
        return $out;
    };

    // --- Series (per metric)
    $series = [];

    // Agenda
    $series['appointments_created'] = $weeklyAgg('appointments', 'created_at', 'user_id');
    $series['meetings_created']     = $weeklyMeetingsAgg();

    // Billing
    $series['invoices_created'] = $weeklyAgg('invoices', 'created_at', 'user_id', function ($q) {
        $q->where('type', 'invoice');
    });
    $series['quotes_created'] = $weeklyAgg('invoices', 'created_at', 'user_id', function ($q) {
        $q->where('type', 'quote');
    });
    $series['corporate_clients_created'] = $weeklyAgg('corporate_clients', 'created_at', 'user_id');

    // Questionnaires
    $series['questionnaires_created'] = $weeklyAgg('questionnaires', 'created_at', 'user_id');

    // Packs
    $series['pack_products_created'] = $weeklyAgg('pack_products', 'created_at', 'user_id');

    // Newsletters (3 angles)
    $series['newsletters_created']   = $weeklyAgg('newsletters', 'created_at', 'user_id');
    $series['newsletters_scheduled'] = $weeklyAgg('newsletters', 'scheduled_at', 'user_id');
    $series['newsletters_sent']      = $weeklyAgg('newsletters', 'sent_at', 'user_id');

    // Referrals (multiple milestone timestamps)
    $series['referrals_invited'] = $weeklyAgg('referral_invites', 'created_at', 'referrer_user_id');
    $series['referrals_opened']  = $weeklyAgg('referral_invites', 'opened_at', 'referrer_user_id');
    $series['referrals_signed']  = $weeklyAgg('referral_invites', 'signed_up_at', 'referrer_user_id');
    $series['referrals_paid']    = $weeklyAgg('referral_invites', 'paid_at', 'referrer_user_id');
    $series['referrals_rewarded']= $weeklyAgg('referral_invites', 'reward_granted_at', 'referrer_user_id');

    // Documents (no user_id in your model => totals only)
    $series['doc_signings_created'] = $weeklyAgg('document_signings', 'created_at', null);
    $series['doc_signings_completed'] = $weeklyAgg('document_signings', 'updated_at', null, function ($q) {
        $q->where('status', 'completed');
    });

    // Build continuous list of weeks so empty weeks show
    $weeks = [];
    $cursor = $start->copy()->startOfWeek();

    while ($cursor->lte($end)) {
        $weekStart = $cursor->copy()->startOfWeek();
        $weekEnd = $cursor->copy()->endOfWeek();

        $isoYear = (int) $weekStart->isoWeekYear;
        $isoWeek = (int) $weekStart->isoWeek;
        $yw = (int) sprintf('%d%02d', $isoYear, $isoWeek);

        // Pull metrics for that week
        $row = [
            'yw' => $yw,
            'week_start' => $weekStart->copy(),
            'week_end' => $weekEnd->copy(),
        ];

        foreach ($series as $key => $data) {
            $row[$key.'_count'] = (int) (($data[$yw]['count'] ?? 0));
            $row[$key.'_users'] = (int) (($data[$yw]['users'] ?? 0));
        }

        // "Active users" across core creation actions (cheap approximation)
        // (appointments_created OR invoices_created OR newsletters_sent OR pack_products_created OR questionnaires_created)
        $active = [];
        $coreKeys = ['appointments_created','invoices_created','newsletters_sent','pack_products_created','questionnaires_created','corporate_clients_created'];
        foreach ($coreKeys as $k) {
            $active[] = $series[$k] ?? [];
        }
        // We compute active users by union of user_ids for the week (few queries, acceptable for 12–26 weeks)
        $activeUsers = DB::query()
            ->fromSub(
                DB::table('appointments')
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->selectRaw("DISTINCT user_id as uid")
                    ->union(
                        DB::table('invoices')
                            ->whereBetween('created_at', [$weekStart, $weekEnd])
                            ->selectRaw("DISTINCT user_id as uid")
                    )
                    ->union(
                        DB::table('newsletters')
                            ->whereBetween('sent_at', [$weekStart, $weekEnd])
                            ->whereNotNull('sent_at')
                            ->selectRaw("DISTINCT user_id as uid")
                    )
                    ->union(
                        DB::table('pack_products')
                            ->whereBetween('created_at', [$weekStart, $weekEnd])
                            ->selectRaw("DISTINCT user_id as uid")
                    )
                    ->union(
                        DB::table('questionnaires')
                            ->whereBetween('created_at', [$weekStart, $weekEnd])
                            ->selectRaw("DISTINCT user_id as uid")
                    ),
                'u'
            )
            ->count('uid');

        $row['active_users'] = (int) $activeUsers;

        $weeks[] = $row;
        $cursor->addWeek();
    }

    // Totals (simple)
    $totals = [
        'appointments' => array_sum(array_column($weeks, 'appointments_created_count')),
        'meetings'     => array_sum(array_column($weeks, 'meetings_created_count')),
        'invoices'     => array_sum(array_column($weeks, 'invoices_created_count')),
        'quotes'       => array_sum(array_column($weeks, 'quotes_created_count')),
        'corporates'   => array_sum(array_column($weeks, 'corporate_clients_created_count')),
        'news_sent'    => array_sum(array_column($weeks, 'newsletters_sent_count')),
        'packs'        => array_sum(array_column($weeks, 'pack_products_created_count')),
        'quest'        => array_sum(array_column($weeks, 'questionnaires_created_count')),
        'ref_inv'      => array_sum(array_column($weeks, 'referrals_invited_count')),
    ];

    return view('admin.usage.weekly', compact('weeks', 'weeksBack', 'start', 'end', 'totals'));
}


}
