{{-- resources/views/admin/therapists/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-100 dark:text-green-600 leading-tight">
            {{ __('Therapist Management') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <!-- Therapist Onboarding Scores -->
        <h1 class="page-title">Therapist Onboarding Scores</h1>

        <div class="table-responsive mx-auto">
            <table class="table mx-auto" id="therapistsTable" aria-label="Therapist Onboarding Scores">
                <thead>
                    <tr>
                        <th class="text-center">ID</th>
                        <th class="text-center">Therapist</th>
                        <th class="text-center">Onboarding Score</th>
                        <th class="text-center">Last Login</th>
                        <th class="text-center">Engagement Score</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($therapists as $therapist)
                        <tr class="text-center">
                            <td>{{ $therapist->id }}</td>
                            <td class="text-wrap">
                                <div class="therapist-info">
                                    <img src="{{ $therapist->profile_picture ?? '/images/default-avatar.png' }}" alt="Avatar" class="avatar">
                                    <div class="name-email">
                                        <span class="name">{{ $therapist->name }}</span>
                                        <span class="email">{{ $therapist->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress" style="width: {{ ($therapist->onboarding_score / $therapist->onboarding_total) * 100 }}%;"></div>
                                </div>
                                <span>{{ $therapist->onboarding_score }} / {{ $therapist->onboarding_total }}</span>
                            </td>
                            <td class="text-wrap">
                                {{ $therapist->last_login_at ? \Carbon\Carbon::parse($therapist->last_login_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i') : 'Never' }}
                            </td>
                            <td>
                                <div class="radial-progress" data-percentage="{{ $therapist->engagement_score }}">
                                    <svg viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="45"></circle>
                                        <circle cx="50" cy="50" r="45" style="stroke-dashoffset: {{ 282 - (282 * $therapist->engagement_score) / 100 }};"></circle>
                                    </svg>
                                    <div class="percentage">{{ $therapist->engagement_score }}%</div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.therapists.show', $therapist->id) }}" class="action-btn">View Details</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        /* General Styles */
        body {
            background: radial-gradient(circle at center, #1a2a6c, #b21f1f, #fdbb2d);
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 15px;
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
            background: linear-gradient(90deg, #00f260, #0575e6);
            display: block;
            margin: 20px auto 0;
        }

        /* Table Styles */
        .table-responsive {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 40px;
            overflow-x: auto;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
        }

        .table {
            width: 100%;
            color: #fff;
            border-collapse: collapse;
        }

        .table thead {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        .table thead th {
            padding: 15px;
            font-size: 1rem;
            text-transform: uppercase;
            position: relative;
        }

        .table thead th::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -10px;
            transform: translateX(-50%);
            width: 50%;
            height: 2px;
            background: linear-gradient(90deg, #00c6ff, #0072ff);
        }

        .table tbody tr {
            transition: background-color 0.3s, transform 0.3s;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: scale(1.01);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            position: relative;
        }

        /* Therapist Info */
        .therapist-info {
            display: flex;
            align-items: center;
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
        }

        .name-email {
            text-align: left;
        }

        .name {
            font-weight: bold;
            font-size: 1.1rem;
        }

        .email {
            font-size: 0.9rem;
            color: #ddd;
        }

        /* Progress Bar */
        .progress-bar {
            width: 100%;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 5px;
            height: 15px;
        }

        .progress {
            height: 100%;
            background: linear-gradient(90deg, #ff8a00, #e52e71);
            border-radius: 10px;
        }

        /* Radial Progress */
        .radial-progress {
            position: relative;
            width: 60px;
            height: 60px;
            margin: 0 auto;
        }

        .radial-progress svg {
            transform: rotate(-90deg);
            width: 100%;
            height: 100%;
        }

        .radial-progress circle {
            fill: none;
            stroke-width: 10;
        }

        .radial-progress circle:first-child {
            stroke: rgba(255, 255, 255, 0.1);
        }

        .radial-progress circle:last-child {
            stroke: url(#gradient);
            stroke-dasharray: 282;
            stroke-dashoffset: 282;
            transition: stroke-dashoffset 1s ease-out;
        }

        .radial-progress .percentage {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.9rem;
            font-weight: bold;
        }

        /* Action Button */
        .action-btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(90deg, #0575e6, #021b79);
            color: #fff;
            border-radius: 30px;
            text-decoration: none;
            transition: background 0.3s, transform 0.3s;
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
        }

        .action-btn:hover {
            background: linear-gradient(90deg, #021b79, #0575e6);
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.7);
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, #00f260, #0575e6);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        /* SVG Gradient Definition */
        svg defs {
            position: absolute;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }

            .table thead {
                display: none;
            }

            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }

            .table tr {
                margin-bottom: 15px;
            }

            .table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }

            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 45%;
                padding-left: 15px;
                font-weight: bold;
                text-align: left;
            }

            .therapist-info {
                flex-direction: column;
                align-items: flex-end;
            }

            .name-email {
                text-align: right;
            }
        }
    </style>
</x-app-layout>
