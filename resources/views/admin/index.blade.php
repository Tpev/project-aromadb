<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-green-600 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
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
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
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
                <th class="text-center">Views</th>
                <th class="text-center">Last Viewed At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pageViews as $pageView)
                <tr class="text-center">
                    <td>{{ $pageView->url }}</td>
                    <td>{{ $pageView->session_id }}</td>
                    <td>{{ $pageView->referrer }}</td>
                    <td>{{ $pageView->ip_address }}</td>
                    <td>{{ $pageView->view_count }}</td>
                    <td>{{ \Carbon\Carbon::parse($pageView->last_viewed_at)->format('d/m/Y H:i') }}</td>
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
