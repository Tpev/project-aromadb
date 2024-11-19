<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord pour les thérapeutes.
     */
    public function index()
    {
        $userId = Auth::id();
		$therapist = Auth::user();
		//dd($therapist);

        // KPIs
        $totalClients = ClientProfile::where('user_id', $userId)->count();
        $upcomingAppointments = Appointment::where('user_id', $userId)
            ->where('appointment_date', '>=', Carbon::now())
            ->count();
        $totalInvoices = Invoice::where('user_id', $userId)->count();
        $pendingInvoices = Invoice::where('user_id', $userId)->where('status', 'En Attente')->count();
        $monthlyRevenue = Invoice::where('user_id', $userId)
            ->whereMonth('invoice_date', Carbon::now()->month)
            ->sum('total_amount');

        // Fill missing months with 0 for Appointments and Revenue Data
        $allMonths = range(1, 12);  // Represents months from January (1) to December (12)

        // Appointments per Month
        $appointmentsPerMonth = Appointment::where('user_id', $userId)
            ->select(DB::raw('MONTH(appointment_date) as month'), DB::raw('COUNT(*) as count'))
            ->whereYear('appointment_date', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Ensure all months are present in the appointments data
        $appointmentsPerMonth = array_replace(array_fill_keys($allMonths, 0), $appointmentsPerMonth);

        // Monthly Revenue Data
        $monthlyRevenueData = Invoice::where('user_id', $userId)
            ->select(DB::raw('MONTH(invoice_date) as month'), DB::raw('SUM(total_amount) as revenue'))
            ->whereYear('invoice_date', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('revenue', 'month')
            ->toArray();

        // Ensure all months are present in the revenue data
        $monthlyRevenueData = array_replace(array_fill_keys($allMonths, 0), $monthlyRevenueData);

        // Format month numbers to names in French
        $months = collect(range(1, 12))->mapWithKeys(function($month) {
            return [$month => Carbon::create()->month($month)->translatedFormat('F')];
        })->toArray();

        // Derniers Rendez-vous
        $recentAppointments = Appointment::where('user_id', $userId)
            ->where('appointment_date', '>=', Carbon::now()->subDays(30))
            ->orderBy('appointment_date', 'desc')
            ->take(5)
            ->get();

        // Dernières Factures
        $recentInvoices = Invoice::where('user_id', $userId)
            ->orderBy('invoice_date', 'desc')
            ->take(5)
            ->get();



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
			'therapist'
	
        ));
    }
}
