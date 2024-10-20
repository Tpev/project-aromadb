{{-- resources/views/admin/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-green-600 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <!-- Session Statistics -->
        <h1 class="page-title">Session Statistics</h1>

        <div class="stat-grid">
            <!-- Today & Yesterday -->
            <div class="stat-group">
                <div class="stat-box">
                    <h4>Sessions Today</h4>
                    <p>{{ $sessionsToday }}</p>
                </div>
                <div class="stat-box">
                    <h4>Sessions Yesterday</h4>
                    <p>{{ $sessionsYesterday }}</p>
                </div>
            </div>

            <!-- This Week & Last Week -->
            <div class="stat-group">
                <div class="stat-box">
                    <h4>Sessions This Week</h4>
                    <p>{{ $sessionsThisWeek }}</p>
                </div>
                <div class="stat-box">
                    <h4>Sessions Last Week</h4>
                    <p>{{ $sessionsLastWeek }}</p>
                </div>
            </div>

            <!-- This Month & Last Month -->
            <div class="stat-group">
                <div class="stat-box">
                    <h4>Sessions This Month</h4>
                    <p>{{ $sessionsThisMonth }}</p>
                </div>
                <div class="stat-box">
                    <h4>Sessions Last Month</h4>
                    <p>{{ $sessionsLastMonth }}</p>
                </div>
            </div>

            <!-- Total Sessions & Total Clients -->
            <div class="stat-group">
                <div class="stat-box">
                    <h4>Total Sessions</h4>
                    <p>{{ $sessionsTotal }}</p>
                </div>
                <div class="stat-box">
                    <h4>Total Clients</h4>
                    <p>{{ $totalClients }}</p>
                </div>
            </div>

            <!-- Total Appointments & Upcoming Appointments -->
            <div class="stat-group">
                <div class="stat-box">
                    <h4>Total Appointments</h4>
                    <p>{{ $totalAppointments }}</p>
                </div>
                <div class="stat-box">
                    <h4>Upcoming Appointments</h4>
                    <p>{{ $upcomingAppointments }}</p>
                </div>
            </div>

            <!-- Total Invoices, Pending Invoices & Monthly Revenue -->
            <div class="stat-group">
                <div class="stat-box">
                    <h4>Total Invoices</h4>
                    <p>{{ $totalInvoices }}</p>
                </div>
                <div class="stat-box">
                    <h4>Pending Invoices</h4>
                    <p>{{ $pendingInvoices }}</p>
                </div>
                <div class="stat-box">
                    <h4>Monthly Revenue (€)</h4>
                    <p>{{ number_format($monthlyRevenue, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Traffic Source Breakdown -->
        <h1 class="page-title mt-5">Traffic Source Breakdown</h1>

        <div class="stat-grid">
            @foreach($trafficSourcesData as $timeFrame => $sources)
                <div class="stat-box">
                    <h4>{{ ucfirst(str_replace('_', ' ', $timeFrame)) }}</h4>
                    <ul class="list-disc list-inside">
                        @foreach($sources as $source => $count)
                            <li><strong>{{ $source }}:</strong> {{ $count }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <!-- Users Table -->
        <h1 class="page-title">Liste des Utilisateurs</h1>

        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover mx-auto" id="usersTable" aria-label="Liste des Utilisateurs">
                <thead>
                    <tr>
                        <th class="text-center">User ID</th>
                        <th class="text-center">Nom</th>
                        <th class="text-center">Email</th>
                        <th class="text-center">PRO</th>
                        <th class="text-center">RDV</th>
                        <th class="text-center">Client</th>
                        <th class="text-center">Questi</th>
                        <th class="text-center">Last Login</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr class="text-center">
                            <td title="{{ $user->id }}">{{ $user->id }}</td>
                            <td title="{{ $user->name }}" class="text-wrap">{{ $user->name }}</td>
                            <td title="{{ $user->email }}" class="text-wrap">{{ $user->email }}</td>
                            <td title="{{ $user->is_therapist ? 'Yes' : 'No' }}" class="text-wrap">{{ $user->is_therapist ? 'Yes' : 'No' }}</td>
                            <td title="{{ $user->appointments->count() }}">{{ $user->appointments->count() }}</td>
                            <td title="{{ $user->clientProfiles->count() }}">{{ $user->clientProfiles->count() }}</td>
                            <td title="{{ $user->questionnaires->count() }}">{{ $user->questionnaires->count() }}</td>
                            <td title="{{ $user->last_login }}">
                                {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i') : 'Never' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Page Views Table -->
        <h1 class="page-title mt-5">Page Views Grouped by Session ID</h1>

        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover mx-auto" id="pageViewsTable" aria-label="Page Views Grouped by Session ID">
                <thead>
                    <tr>
                        <th class="text-center">Page URL</th>
                        <th class="text-center">Session ID</th>
                        <th class="text-center">Referrer</th>
                        <th class="text-center">IP</th>
                        <th class="text-center">User-Agent</th>
                        <th class="text-center">Last Viewed At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pageViews as $pageView)
                        <tr class="text-center">
                            <td title="{{ $pageView->url }}" class="text-wrap">{{ $pageView->url }}</td>
                            <td title="{{ $pageView->session_id }}" class="text-wrap">{{ $pageView->session_id }}</td>
                            <td title="{{ $pageView->referrer ?? 'N/A' }}" class="text-wrap">{{ $pageView->referrer ?? 'N/A' }}</td>
                            <td title="{{ $pageView->ip_address }}" class="text-wrap">{{ $pageView->ip_address }}</td>
                            <td title="{{ $pageView->user_agent }}" class="text-wrap">{{ $pageView->user_agent }}</td>
                            <td>{{ \Carbon\Carbon::parse($pageView->last_viewed_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .stat-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 20px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-box h4 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #4a5568;
        }

        .stat-box p {
            font-size: 2rem;
            font-weight: bold;
            color: #2d3748;
        }

        .table-responsive {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
            overflow-x: auto;
        }

        .table {
            width: 100%;
            max-width: 100%;
            table-layout: auto;
            word-wrap: break-word;
        }

        .table thead {
            background-color: #16a34a;
            color: #ffffff;
        }

        .table tbody tr {
            transition: background-color 0.3s, color 0.3s;
        }

        .table tbody tr:hover {
            background-color: #16a34a;
            color: #ffffff;
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
            padding: 12px 8px;
        }

        .text-wrap {
            white-space: normal;
            word-wrap: break-word;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 20px;
            text-align: left;
        }

        ul {
            padding-left: 20px;
            text-align: left;
        }

        @media (max-width: 768px) {
            .stat-group {
                flex-direction: column;
            }
        }
    </style>
</x-app-layout>
