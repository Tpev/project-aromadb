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

class AdminController extends Controller
{
	
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

        // For each therapist, calculate onboarding score
        foreach ($therapists as $therapist) {
            // Initialize score
            $score = 0;
            $total = 9; // 3 criteria + 6 items

            // Onboarding criteria:

            // Has a Slug
            if ($therapist->slug) {
                $score++;
            }

            // Has set up Stripe (has 'stripe_account_id')
            if ($therapist->stripe_account_id) {
                $score++;
            }

            // Accepts booking online (assuming 'accepts_online_booking' property)
            if ($therapist->accept_online_appointments) {
                $score++;
            }

            // Has created one or more of the following:

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
            if ($therapist->invoices()->exists()) {
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

            // Store the score in the therapist object
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
        'events'
    ]);
		


        // Calculate onboarding score
        $score = 0;
        $total = 9; // 3 criteria + 6 items

        // Onboarding criteria:

        // Has a Slug
        if ($therapist->slug) {
            $score++;
        }

        // Has set up Stripe (has 'stripe_account_id')
        if ($therapist->stripe_account_id) {
            $score++;
        }

        // Accepts booking online (assuming 'accepts_online_booking' property)
        if ($therapist->accept_online_appointments) {
            $score++;
        }

        // Has created one or more of the following:

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
        if ($therapist->invoices()->exists()) {
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

        $therapist->onboarding_score = $score;
        $therapist->onboarding_total = $total;

        // Weekly usage statistics
        $startOfWeek = Carbon::now()->startOfMonth();
        $endOfWeek = Carbon::now()->endOfMonth();

        $appointmentsThisWeek = $therapist->appointments()
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        $invoicesThisWeek = $therapist->invoices()
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        $clientProfilesThisWeek = $therapist->clientProfiles()
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        $eventsThisWeek = $therapist->events()
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        return view('admin.therapists.show', compact(
            'therapist',
            'appointmentsThisWeek',
            'invoicesThisWeek',
            'clientProfilesThisWeek',
            'eventsThisWeek'
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
	public function updateTherapistPicture(Request $request, $id)
{
    // Check if the user is an admin
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }

    // Find the therapist by ID
    $therapist = User::where('is_therapist', true)->findOrFail($id);

    // Validate the incoming request
    $request->validate([
        'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    // If a file is uploaded, store it
    if ($request->hasFile('profile_picture')) {
        // Store the file in 'public/therapists'
        $path = $request->file('profile_picture')->store('public/therapists');
        
        // Remove 'public/' from the path for storage in DB if desired
        $therapist->profile_picture = str_replace('public/', '', $path);
        $therapist->save();
    }

    return redirect()->route('admin.therapists.show', $id)->with('success', 'Profile picture updated successfully!');
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


}
