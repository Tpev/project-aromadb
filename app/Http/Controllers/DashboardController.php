<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PracticeLocation;
use App\Models\Availability;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord pour les thérapeutes.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->license_status === 'inactive') {
            return redirect('/license-tiers/pricing');
        }

        $userId    = $user->id;
        $therapist = $user;

        /* =========================================================
         *                         KPIs
         * ======================================================= */
        $totalClients = ClientProfile::where('user_id', $userId)->count();

$upcomingAppointments = Appointment::where('user_id', $userId)
    ->where('appointment_date', '>=', now())
    ->where(function ($q) {
        $q->where('external', false)
          ->orWhereNull('external');
    })
    ->count();


        $totalInvoices = Invoice::where('user_id', $userId)->count();

        $pendingInvoices = Invoice::where('user_id', $userId)
            ->where('status', 'En Attente')
            ->count();

        $monthlyRevenue = Invoice::where('user_id', $userId)
            ->whereMonth('invoice_date', Carbon::now()->month)
            ->sum('total_amount');

        // Graphiques
        $allMonths = range(1, 12);

        $appointmentsPerMonth = Appointment::where('user_id', $userId)
            ->select(DB::raw('MONTH(appointment_date) as month'), DB::raw('COUNT(*) as count'))
            ->whereYear('appointment_date', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $appointmentsPerMonth = array_replace(array_fill_keys($allMonths, 0), $appointmentsPerMonth);

        $monthlyRevenueData = Invoice::where('user_id', $userId)
            ->select(DB::raw('MONTH(invoice_date) as month'), DB::raw('SUM(total_amount) as revenue'))
            ->whereYear('invoice_date', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('revenue', 'month')
            ->toArray();

        $monthlyRevenueData = array_replace(array_fill_keys($allMonths, 0), $monthlyRevenueData);

        $months = collect(range(1, 12))->mapWithKeys(function ($month) {
            return [$month => Carbon::create()->month($month)->translatedFormat('F')];
        })->toArray();

// Prochains rendez-vous (internes uniquement)
$recentAppointments = Appointment::query()
    ->where('user_id', $userId)
    ->where('appointment_date', '>=', now())   // seulement à partir de maintenant
    ->where(function ($q) {
        $q->where('external', false)
          ->orWhereNull('external');          // garde les anciens en null pour compat
    })
    ->orderBy('appointment_date', 'asc')      // le plus proche en premier
    ->limit(5)
    ->with('clientProfile')
    ->get();


        // Dernières factures
        $recentInvoices = Invoice::where('user_id', $userId)
            ->orderBy('invoice_date', 'desc')
            ->take(5)
            ->get();

        /* =========================================================
         *                ONBOARDING – ÉTAPE 1 (Profil)
         *  Basé sur les champs remplis dans updateCompanyInfo()
         * ======================================================= */
        $step1Checks = [
            'company_name'        => !empty(trim((string) $user->company_name)),
            'company_address'     => !empty(trim((string) $user->company_address)),
            'company_email'       => !empty(trim((string) $user->company_email)),
            'company_phone'       => !empty(trim((string) $user->company_phone)),
            'about'               => !empty(trim((string) $user->about)),
            'profile_description' => !empty(trim((string) $user->profile_description)),
            'services'            => (function ($services) {
                if (is_array($services)) {
                    return count($services) > 0;
                }
                return !empty($services);
            })($user->services),
        ];

        $step1Total      = count($step1Checks);
        $step1DoneCount  = collect($step1Checks)->filter(fn ($v) => $v === true)->count();
        $step1Completion = $step1Total > 0
            ? (int) round(($step1DoneCount / $step1Total) * 100)
            : 0;

        /* =========================================================
         *                ONBOARDING – ÉTAPE 2 (Réservations)
         *  3 checks : lieu, disponibilités, prestation réservable
         * ======================================================= */
        $hasLocation = PracticeLocation::where('user_id', $userId)->exists();
        $hasAvailabilities = Availability::where('user_id', $userId)->exists();
        $hasBookableProduct = Product::where('user_id', $userId)
            ->where('can_be_booked_online', true)   // adapte au besoin
            ->exists();

        $step2Checks = [
            'location'       => $hasLocation,
            'availabilities' => $hasAvailabilities,
            'bookable'       => $hasBookableProduct,
        ];

        $step2Total      = count($step2Checks);
        $step2DoneCount  = collect($step2Checks)->filter(fn ($v) => $v === true)->count();
        $step2Completion = $step2Total > 0
            ? (int) round(($step2DoneCount / $step2Total) * 100)
            : 0;

        /* =========================================================
         *                ONBOARDING – ÉTAPE 3 (Fonctionnalités)
         *                Optionnelle & skippable
         * ======================================================= */
        $skipStep3 = (bool) $user->skip_step3_onboarding;

        $hasInvoice = Invoice::where('user_id', $userId)->exists();

        $hasQuestionnaire = false;
        if (class_exists(\App\Models\Questionnaire::class)) {
            $hasQuestionnaire = \App\Models\Questionnaire::where('user_id', $userId)->exists();
        } elseif (class_exists(\App\Models\QuestionnaireTemplate::class)) {
            $hasQuestionnaire = \App\Models\QuestionnaireTemplate::where('user_id', $userId)->exists();
        }

        $hasVisio = Appointment::where('user_id', $userId)
            ->where('type', 'visio')
            ->exists();

        $step3Checks = [
            'invoice'       => $hasInvoice,
            'questionnaire' => $hasQuestionnaire,
            'meeting'       => $hasVisio,
        ];

        $step3Total      = count($step3Checks);
        $step3DoneCount  = collect($step3Checks)->filter(fn ($v) => $v === true)->count();
        $step3Completion = $step3Total > 0
            ? (int) round(($step3DoneCount / $step3Total) * 100)
            : 0;

        $effectiveStep3ForGlobal = $skipStep3 ? 100 : $step3Completion;

        /* =========================================================
         *                ONBOARDING – ÉTAPE 4 (Parrainage)
         *                Optionnelle & skippable
         * ======================================================= */
        $skipStep4   = (bool) $user->skip_step4_onboarding;
        $referralDone = (bool) $user->referral_onboarding_completed;

        $step4Checks = [
            'referral' => $referralDone,
        ];

        $step4Total      = count($step4Checks);
        $step4DoneCount  = collect($step4Checks)->filter(fn ($v) => $v === true)->count();
        $step4Completion = $step4Total > 0
            ? (int) round(($step4DoneCount / $step4Total) * 100)
            : 0;

        $effectiveStep4ForGlobal = $skipStep4 ? 100 : $step4Completion;

        /* =========================================================
         *                Global onboarding
         * ======================================================= */
        $globalCompletion = (int) round((
            $step1Completion +
            $step2Completion +
            $effectiveStep3ForGlobal +
            $effectiveStep4ForGlobal
        ) / 4);

        $onboardingCompleted =
            $step1Completion === 100 &&
            $step2Completion === 100 &&
            ($step3Completion === 100 || $skipStep3) &&
            ($step4Completion === 100 || $skipStep4);

        return view('dashboard-pro', compact(
            'totalClients',
            'upcomingAppointments',
            'totalInvoices',
            'pendingInvoices',
            'monthlyRevenue',
            'appointmentsPerMonth',
            'monthlyRevenueData',
            'months',
            'recentAppointments',
            'recentInvoices',
            'therapist',
            // Onboarding
            'onboardingCompleted',
            'globalCompletion',
            'step1Checks',
            'step2Checks',
            'step3Checks',
            'step4Checks',
            'step1Completion',
            'step2Completion',
            'step3Completion',
            'step4Completion',
            'skipStep3',
            'skipStep4'
        ));
    }

    public function generateQrCode()
    {
        $therapist = auth()->user();

        if (!$therapist->slug) {
            return response()->json(['error' => 'Slug not found'], 400);
        }

        $url = route('therapist.show', ['slug' => $therapist->slug]);

        try {
            $qrCode        = QrCode::format('png')->size(200)->generate($url);
            $qrCodeBase64  = base64_encode($qrCode);
            $qrCodeDataUrl = 'data:image/png;base64,' . $qrCodeBase64;

            return response()->json(['qrCode' => $qrCodeDataUrl]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la génération du QR Code'], 500);
        }
    }

    public function skipStep3()
    {
        $user = Auth::user();
        $user->skip_step3_onboarding = true;
        $user->save();

        return redirect()->route('dashboard-pro');
    }

    public function skipStep4()
    {
        $user = Auth::user();
        $user->skip_step4_onboarding = true;
        $user->save();

        return redirect()->route('dashboard-pro');
    }

    /**
     * À appeler quand un parrainage est effectivement validé (ou bouton "marquer comme fait")
     */
    public function markReferralOnboardingDone()
    {
        $user = Auth::user();
        $user->referral_onboarding_completed = true;
        $user->save();

        return redirect()->route('dashboard-pro');
    }
}
