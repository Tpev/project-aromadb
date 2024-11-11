{{-- resources/views/admin/therapists/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-green-600 leading-tight">
            {{ __('Therapist Management') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <!-- Therapist Onboarding Scores -->
        <h1 class="page-title">Therapist Onboarding Scores</h1>

        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover mx-auto" id="therapistsTable" aria-label="Therapist Onboarding Scores">
                <thead>
                    <tr>
                        <th class="text-center">Therapist ID</th>
                        <th class="text-center">Name</th>
                        <th class="text-center">Email</th>
                        <th class="text-center">Onboarding Score</th>
                        <th class="text-center">Last Login</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($therapists as $therapist)
                        <tr class="text-center">
                            <td>{{ $therapist->id }}</td>
                            <td class="text-wrap">{{ $therapist->name }}</td>
                            <td class="text-wrap">{{ $therapist->email }}</td>
                            <td>{{ $therapist->onboarding_score }} / {{ $therapist->onboarding_total }}</td>
							<td {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i') : 'Never' }} </td>
                            <td>
                                <a href="{{ route('admin.therapists.show', $therapist->id) }}" class="text-blue-600 hover:text-blue-800">View Details</a>
                            </td>
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
    </style>
</x-app-layout>
