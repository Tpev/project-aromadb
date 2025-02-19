{{-- resources/views/admin/therapists/show.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Therapist Details</title>
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

        /* Therapist Info Card */
        .therapist-info-card {
            display: flex;
            align-items: center;
            background-color: #2a2a3c; /* Solid background */
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        }

        .avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f0f0f0;
            box-shadow: 0 0 15px rgba(255, 81, 47, 0.5);
            margin-right: 30px;
        }

        .therapist-info-card .info h2 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #f0f0f0;
        }

        .therapist-info-card .info p {
            font-size: 1rem;
            margin-bottom: 5px;
            color: #c0c0c0;
        }

        /* Section Titles */
        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            text-align: center;
        }

        .section-title::after {
            content: '';
            width: 100px;
            height: 2px;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            display: block;
            margin: 10px auto 0;
            border-radius: 2px;
        }

        /* Onboarding Checklist */
        .checklist {
            list-style: none;
            padding-left: 0;
            margin-bottom: 40px;
            background-color: #2a2a3c; /* Solid background */
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        }

        .checklist li {
            font-size: 1.2rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            color: #f0f0f0;
        }

        .checkmark, .crossmark {
            font-size: 1.5rem;
            font-weight: bold;
            margin-right: 15px;
        }

        .checkmark {
            color: #28a745;
        }

        .crossmark {
            color: #dc3545;
        }

        /* Radial Progress */
        .onboarding-score, .engagement-score {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }

        .radial-progress {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }

        .radial-progress svg {
            transform: rotate(-90deg);
            width: 100%;
            height: 100%;
        }

        .radial-progress circle {
            fill: none;
            stroke-width: 15;
        }

        .radial-progress circle:first-child {
            stroke: rgba(58, 58, 79, 0.8);
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
            font-size: 1.5rem;
            font-weight: bold;
            color: #f0f0f0;
        }

        /* Stat Grid */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .therapist-info-card {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .therapist-info-card .avatar-large {
                margin-right: 0;
                margin-bottom: 20px;
            }

            .stat-grid {
                grid-template-columns: 1fr;
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

    <!-- SVG Gradient Definition (for radial progress and other elements) -->
    <svg width="0" height="0">
        <defs>
            <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#ff512f" />
                <stop offset="100%" stop-color="#dd2476" />
            </linearGradient>
        </defs>
    </svg>

    <div class="container mt-5">
        <!-- Therapist Details -->
        <h1 class="page-title">Therapist Details: {{ $therapist->name }}</h1>

        <!-- Therapist Info -->
        <div class="therapist-info-card">
            <img src="{{ asset('storage/' . $therapist->profile_picture) }}" alt="Avatar" class="avatar-large">
            <div class="info">
                <h2>{{ $therapist->name }}</h2>
                <p><strong>Email:</strong> {{ $therapist->email }}</p>
                <p><strong>Slug:</strong> {{ $therapist->slug ?? 'Not set' }}</p>
                <p><strong>Stripe Account ID:</strong> {{ $therapist->stripe_account_id ?? 'Not set' }}</p>
                <p><strong>Accepts Online Booking:</strong> {{ $therapist->accepts_online_booking ? 'Yes' : 'No' }}</p>
            </div>
        </div>

        @if(session('success'))
            <div style="color: green; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Form to update the therapist's profile picture -->
        <form action="{{ route('admin.therapists.updatePicture', $therapist->id) }}" method="POST" enctype="multipart/form-data" style="margin-bottom:40px;">
            @csrf
            @method('PUT')
            <label for="profile_picture">Change Profile Picture:</label><br><br>
            <input type="file" name="profile_picture" required><br><br>
            <button type="submit" style="padding:10px 20px; background:#28a745; color:#fff; border:none; border-radius:5px; cursor:pointer;">Update Picture</button>
        </form>

        <!-- New Form to Update Admin Settings (Verified and Visible Annuaire Admin Set) -->
        <form action="{{ route('admin.therapists.updateSettings', $therapist->id) }}" method="POST" style="margin-bottom:40px;">
            @csrf
            @method('PUT')
            <div style="margin-bottom: 15px;">
                <label for="verified" style="font-size: 1.2rem; color: #f0f0f0;">Verified:</label>
                <input type="checkbox" name="verified" id="verified" value="1" {{ $therapist->verified ? 'checked' : '' }}>
            </div>
            <div style="margin-bottom: 15px;">
                <label for="visible_annuarire_admin_set" style="font-size: 1.2rem; color: #f0f0f0;">Visible in Admin Annuaire:</label>
                <input type="checkbox" name="visible_annuarire_admin_set" id="visible_annuarire_admin_set" value="1" {{ $therapist->visible_annuarire_admin_set ? 'checked' : '' }}>
            </div>
            <button type="submit" style="padding:10px 20px; background:#28a745; color:#fff; border:none; border-radius:5px; cursor:pointer;">Update Settings</button>
        </form>

        <!-- Onboarding Checklist -->
        <h2 class="section-title">Onboarding Checklist</h2>
        <ul class="checklist">
            @foreach([
                'slug' => 'Has a Slug',
                'stripe_account_id' => 'Has set up Stripe',
                'accept_online_appointments' => 'Accepts Online Booking',
                'products' => 'Has created a Prestation',
                'availabilities' => 'Has created a Disponibilité',
                'appointments' => 'Has created an Appointment',
                'invoices' => 'Has created an Invoice',
                'clientProfiles' => 'Has created a Client Profile',
                'events' => 'Has created an Event',
            ] as $attribute => $description)
                <li>
                    @if($attribute === 'accept_online_appointments')
                        @if($therapist->accept_online_appointments)
                            <span class="checkmark">&#10003;</span> {{ $description }}
                        @else
                            <span class="crossmark">&#10007;</span> {{ $description }}
                        @endif
                    @elseif(in_array($attribute, ['slug', 'stripe_account_id']))
                        @if($therapist->$attribute)
                            <span class="checkmark">&#10003;</span> {{ $description }}
                        @else
                            <span class="crossmark">&#10007;</span> {{ $description }}
                        @endif
                    @else
                        @if($therapist->$attribute()->exists())
                            <span class="checkmark">&#10003;</span> {{ $description }}
                        @else
                            <span class="crossmark">&#10007;</span> {{ $description }}
                        @endif
                    @endif
                </li>
            @endforeach
        </ul>

        <!-- Onboarding Score -->
        <h2 class="section-title">Onboarding Score</h2>
        <div class="onboarding-score">
            <div class="radial-progress" data-percentage="{{ ($therapist->onboarding_score / $therapist->onboarding_total) * 100 }}">
                <svg viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="45"></circle>
                    <circle cx="50" cy="50" r="45" style="stroke-dashoffset: {{ 282 - (282 * ($therapist->onboarding_score / $therapist->onboarding_total)) }};"></circle>
                </svg>
                <div class="percentage">{{ round(($therapist->onboarding_score / $therapist->onboarding_total) * 100) }}%</div>
            </div>
        </div>

        <!-- Monthly Usage Statistics -->
        <h2 class="section-title">Monthly Usage Statistics</h2>
        <div class="stat-grid">
            <div class="stat-box">
                <h4>Appointments This Month</h4>
                <p>{{ $appointmentsThisWeek }}</p>
            </div>
            <div class="stat-box">
                <h4>Invoices This Month</h4>
                <p>{{ $invoicesThisWeek }}</p>
            </div>
            <div class="stat-box">
                <h4>Client Profiles This Month</h4>
                <p>{{ $clientProfilesThisWeek }}</p>
            </div>
            <div class="stat-box">
                <h4>Events This Month</h4>
                <p>{{ $eventsThisWeek }}</p>
            </div>
        </div>
    </div>

    <!-- Include any necessary scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
