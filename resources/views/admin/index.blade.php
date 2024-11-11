{{-- resources/views/admin/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <!-- Include necessary meta tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Include the 'Montserrat' font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Include your main CSS file -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Custom Styles -->
    <style>
        /* Full-Screen Background Video */
        #bg-video {
            position: fixed;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit: cover;
            filter: brightness(50%) blur(2px);
        }

        /* General Styles */
        body {
            background: transparent;
            color: #f0f0f0;
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 15px;
            position: relative;
            z-index: 1; /* Ensure content is above the video */
        }

        .mt-5 {
            margin-top: 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 40px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
        }

        .page-title::after {
            content: '';
            width: 150px;
            height: 3px;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            display: block;
            margin: 20px auto 0;
            border-radius: 2px;
        }

        /* Stat Grid */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .stat-box {
            background-color: transparent; /* Make transparent */
            padding: 20px;
            text-align: center;
            border-radius: 12px;
            transition: transform 0.2s;
        }

        .stat-box:hover {
            transform: translateY(-5px);
        }

        .stat-box h4 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #f0f0f0;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
        }

        .stat-box p {
            font-size: 2rem;
            font-weight: bold;
            color: #ff512f;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
        }

        /* Table Styles */
        .table-responsive {
            background-color: rgba(42, 42, 60, 0.8); /* Semi-transparent */
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            margin-bottom: 40px;
            overflow-x: auto;
            backdrop-filter: blur(5px);
        }

        .table {
            width: 100%;
            max-width: 100%;
            table-layout: auto;
            word-wrap: break-word;
            color: #f0f0f0;
        }

        .table thead {
            background-color: rgba(58, 58, 79, 0.8); /* Semi-transparent */
            color: #f0f0f0;
        }

        .table tbody tr {
            transition: background-color 0.3s, color 0.3s;
        }

        .table tbody tr:hover {
            background-color: rgba(58, 58, 79, 0.8);
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
</head>
<body>
    <!-- Full-Screen Background Video -->
    <video autoplay muted loop id="bg-video">
        <source src="/images/bg01.mp4" type="video/mp4">
        Your browser does not support HTML5 video.
    </video>

    <!-- Container -->
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

    <!-- Include any necessary scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
