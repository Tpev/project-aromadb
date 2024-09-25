<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-green-600 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <h1 class="page-title">Session Statistics</h1>

        <!-- Row for This Week's KPIs -->
        <div class="stat-grid">
            <div class="stat-box">
                <h4>Sessions Today</h4>
                <p>{{ $sessionsToday }}</p>
            </div>
            <div class="stat-box">
                <h4>Sessions This Week</h4>
                <p>{{ $sessionsThisWeek }}</p>
            </div>
            <div class="stat-box">
                <h4>Sessions This Month</h4> <!-- Restored "This Month" statistic -->
                <p>{{ $sessionsThisMonth }}</p>
            </div>
        </div>

        <!-- Row for Last Period's KPIs -->
        <div class="stat-grid">
		            <div class="stat-box">
                <h4>Sessions Yesterday</h4>
                <p>{{ $sessionsYesterday }}</p>
            </div>
            <div class="stat-box">
			
                <h4>Sessions Last Week</h4>
                <p>{{ $sessionsLastWeek }}</p>
            </div>
            <div class="stat-box">
                <h4>Sessions Last Month</h4>
                <p>{{ $sessionsLastMonth }}</p>
            </div>
        </div>

        <h1 class="page-title">Liste des Utilisateurs</h1>

        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover mx-auto" id="usersTable">
                <thead>
                    <tr>
                        <th class="text-center">User ID</th>
                        <th class="text-center">Nom</th>
                        <th class="text-center">Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr class="text-center">
                            <td title="{{ $user->id }}">{{ $user->id }}</td>
                            <td title="{{ $user->name }}" class="text-wrap">{{ $user->name }}</td>
                            <td title="{{ $user->email }}" class="text-wrap">{{ $user->email }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h1 class="page-title mt-5">Page Views Grouped by Session ID</h1>

        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover mx-auto" id="pageViewsTable">
                <thead>
                    <tr>
                        <th class="text-center">Page URL</th>
                        <th class="text-center">Session ID</th>
                        <th class="text-center">Referrer</th>
                        <th class="text-center">IP</th>
                        <th class="text-center">User-Agent</th>
                        <th class="text-center">Views</th>
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
                            <td>{{ $pageView->view_count }}</td>
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
            text-align: center;
        }

        .stat-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .stat-box {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
            flex: 1 1 calc(25% - 20px); /* Each box takes 25% of the row with space between */
            margin-right: 10px;
            margin-left: 10px;
        }

        .stat-box:last-child {
            margin-right: 0; /* Ensure last box doesn't have extra margin */
        }

        .table-responsive {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            display: flex;
            justify-content: center;
        }

        .table {
            width: 100%;
            max-width: 1000px;
            table-layout: fixed; /* Prevents table from expanding based on content */
            word-wrap: break-word; /* Forces text to wrap in table cells */
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
            overflow: hidden;
            text-overflow: ellipsis; /* Adds "..." when text overflows */
            white-space: nowrap; /* Prevents text from wrapping to a new line */
        }

        .text-wrap {
            white-space: normal; /* Allows wrapping for columns where text is long */
            word-wrap: break-word; /* Breaks long words if necessary */
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: #333333;
            margin-bottom: 20px;
        }

        ul {
            padding-left: 15px;
            text-align: left;
        }
    </style>

</x-app-layout>
