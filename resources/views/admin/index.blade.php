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
                        <th class="text-center">Login Count</th> <!-- New column for login count -->
                        <th class="text-center">Last Login</th> <!-- New column for last login date -->
                        <th class="text-center">Nombre de Favoris</th> <!-- New column for number of favorites -->
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr class="text-center">
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->login_count }}</td> <!-- Show login count -->
                            <td>{{ $user->last_login_at ? $user->last_login_at : 'Never' }}</td> <!-- Show last login date -->
                            <td>{{ $user->favorites->count() }}</td> <!-- Show count of user's favorites -->
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
    </style>

</x-app-layout>
