{{-- resources/views/admin/therapists/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-green-600 leading-tight">
            {{ __('Therapist Details') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <!-- Therapist Details -->
        <h1 class="page-title">Therapist Details: {{ $therapist->name }}</h1>

        <!-- Onboarding Score -->
        <h2>Onboarding Score</h2>
        <div class="stat-box">
            <p>{{ $therapist->onboarding_score }} / {{ $therapist->onboarding_total }}</p>
        </div>

        <!-- Weekly Usage Statistics -->
        <h2 class="mt-5">Weekly Usage Statistics</h2>
        <div class="stat-grid">
            <div class="stat-box">
                <h4>Appointments This Week</h4>
                <p>{{ $appointmentsThisWeek }}</p>
            </div>
            <div class="stat-box">
                <h4>Invoices This Week</h4>
                <p>{{ $invoicesThisWeek }}</p>
            </div>
            <div class="stat-box">
                <h4>Client Profiles This Week</h4>
                <p>{{ $clientProfilesThisWeek }}</p>
            </div>
            <div class="stat-box">
                <h4>Events This Week</h4>
                <p>{{ $eventsThisWeek }}</p>
            </div>
        </div>

        <!-- Therapist Info -->
        <h2 class="mt-5">Details</h2>
        <ul>
            <li><strong>Email:</strong> {{ $therapist->email }}</li>
            <li><strong>Slug:</strong> {{ $therapist->slug ?? 'Not set' }}</li>
            <li><strong>Stripe Account ID:</strong> {{ $therapist->stripe_account_id ?? 'Not set' }}</li>
            <li><strong>Accepts Online Booking:</strong> {{ $therapist->accepts_online_booking ? 'Yes' : 'No' }}</li>
        </ul>
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

        .mt-5 {
            margin-top: 2rem;
        }
    </style>
</x-app-layout>
