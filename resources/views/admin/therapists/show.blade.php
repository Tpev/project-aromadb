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
    .styled-summary-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1.5rem;
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        font-size: 0.95rem;
        color: #000; /* Default text color to black */
    }

    .styled-summary-table th,
    .styled-summary-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .styled-summary-table thead {
        background-color: #f8fafc;
        font-weight: bold;
        color: #000;
        text-align: left;
    }

    .styled-summary-table tbody tr:hover {
        background-color: #f1f5f9;
    }

    .text-right { text-align: right; }

    .text-sm {
        font-size: 0.85rem;
        color: #000 !important; /* Force black for small text */
    }

    .text-gray-500 { color: #000 !important; }

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

        .mt-5 { margin-top: 2rem; }

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

        .checkmark { color: #28a745; }
        .crossmark { color: #dc3545; }

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

        .stat-box:hover { transform: translateY(-5px); }

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

        /* Basic Form Styles */
        form div { margin-bottom: 15px; }
        form label {
            font-size: 1.2rem;
            color: #f0f0f0;
            display: block;
            margin-bottom: 5px;
        }
        form input[type="text"],
        form select {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: none;
        }
        form button {
            padding: 10px 20px;
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        /* ---- Impersonation UI ---- */
        .impersonation-actions {
            margin-top: 14px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .imp-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.12);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            color: #fff;
            background: rgba(100, 122, 11, 0.85); /* your green-ish */
            transition: transform .08s ease, opacity .2s ease;
        }
        .imp-btn:hover { opacity: .92; transform: translateY(-1px); }
        .imp-btn.secondary {
            background: rgba(255,255,255,0.12);
        }
        .imp-note {
            margin-top: 10px;
            font-size: 0.95rem;
            color: #cbd5e1;
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

            .stat-grid { grid-template-columns: 1fr; }

            .impersonation-actions {
                justify-content: center;
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

        <!-- Therapist Info Card -->
        <div class="therapist-info-card">
            @php
                $avatarUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($therapist->profile_picture);
                $ver       = $therapist->updated_at?->timestamp ?? time(); // fallback if null
            @endphp

            <img src="{{ $avatarUrl }}?v={{ $ver }}" alt="Avatar" class="avatar-large">

            <div class="info" style="width:100%;">
                <h2>{{ $therapist->name }}</h2>
                <p><strong>Email:</strong> {{ $therapist->email }}</p>
                <p><strong>Slug:</strong> {{ $therapist->slug ?? 'Not set' }}</p>
                <p><strong>Stripe Account ID:</strong> {{ $therapist->stripe_account_id ?? 'Not set' }}</p>
                <p><strong>Accepts Online Booking:</strong> {{ $therapist->accepts_online_booking ? 'Yes' : 'No' }}</p>

                {{-- ✅ NEW: Impersonation buttons --}}
                <div class="impersonation-actions">
                    @if(session()->has('impersonator_id'))
                        <form method="POST" action="{{ route('admin.impersonate.stop') }}" style="margin:0;">
                            @csrf
                            <button type="submit" class="imp-btn secondary">
                                Revenir à mon compte admin
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.impersonate.start', $therapist->id) }}" style="margin:0;">
                            @csrf
                            <button type="submit" class="imp-btn">
                                Se connecter en tant que {{ $therapist->first_name ?? $therapist->name }}
                            </button>
                        </form>
                    @endif
                </div>

                @if(session()->has('impersonator_id'))
                    <div class="imp-note">
                        Vous êtes actuellement connecté en tant que <strong>{{ auth()->user()->name }}</strong>.
                    </div>
                @endif
            </div>
        </div>

        <h2 class="section-title">Contenus créés par ce thérapeute</h2>
        <table class="styled-summary-table">
            <thead>
                <tr>
                    <th>Élément</th>
                    <th>Nombre</th>
                    <th>Dernière création</th>
                </tr>
            </thead>
            <tbody>
                @foreach([
                    'products' => 'Prestations',
                    'availabilities' => 'Disponibilités',
                    'appointments' => 'Rendez-vous',
                    'invoices' => 'Factures',
                    'quotes' => 'Devis',
                    'clientProfiles' => 'Profils clients',
                    'events' => 'Événements',
                    'inventoryItems' => 'Articles d\'inventaire'
                ] as $key => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="text-right">{{ $counts[$key] }}</td>
                        <td class="text-right text-sm text-gray-500">
                            {{ $lastTimestamps[$key] ? $lastTimestamps[$key]->format('d/m/Y H:i') : '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if(session('success'))
            <div style="color: green; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        {{-- Toggle Featured --}}
        <form action="{{ route('admin.therapists.updateFeatured', $therapist->id) }}" method="POST" style="margin-bottom:40px;">
            @csrf
            @method('PUT')
            <h2 class="section-title">Mise en avant (Featured)</h2>

            <div>
                <label for="is_featured">Mettre à la une :</label>
                <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ $therapist->is_featured ? 'checked' : '' }}>
            </div>

            <div style="margin-top:10px;">
                <label for="featured_until">Jusqu’au (optionnel) :</label>
                <input type="datetime-local" name="featured_until" id="featured_until"
                       value="{{ $therapist->featured_until ? $therapist->featured_until->format('Y-m-d\TH:i') : '' }}">
            </div>

            <div style="margin-top:10px;">
                <label for="featured_weight">Poids (0–100, optionnel) :</label>
                <input type="number" min="0" max="100" name="featured_weight" id="featured_weight"
                       value="{{ old('featured_weight', $therapist->featured_weight ?? 0) }}">
            </div>

            <button type="submit" style="margin-top:12px;">Mettre à jour</button>
        </form>

        <!-- Form to update the therapist's profile picture -->
        <form action="{{ route('admin.therapists.updatePicture', $therapist->id) }}" method="POST" enctype="multipart/form-data" style="margin-bottom:40px;">
            @csrf
            @method('PUT')
            <label for="profile_picture">Change Profile Picture:</label>
            <input type="file" name="profile_picture" id="profile_picture" required>
            <button type="submit">Update Picture</button>
        </form>

        <!-- Form to update Admin Settings (Verified & Visible Annuaire) -->
        <form action="{{ route('admin.therapists.updateSettings', $therapist->id) }}" method="POST" style="margin-bottom:40px;">
            @csrf
            @method('PUT')
            <div>
                <label for="verified">Verified:</label>
                <input type="checkbox" name="verified" id="verified" value="1" {{ $therapist->verified ? 'checked' : '' }}>
            </div>
            <div>
                <label for="visible_annuarire_admin_set">Visible in Admin Annuaire:</label>
                <input type="checkbox" name="visible_annuarire_admin_set" id="visible_annuarire_admin_set" value="1" {{ $therapist->visible_annuarire_admin_set ? 'checked' : '' }}>
            </div>
            <button type="submit">Update Settings</button>
        </form>

        <form action="{{ route('admin.therapists.toggleLicense', $therapist->id) }}" method="POST" style="margin-bottom: 40px;">
            @csrf
            @method('PUT')
            <label for="license_status">Statut de la licence :</label>
            <select name="license_status" id="license_status" onchange="this.form.submit()">
                <option value="active" {{ $therapist->license_status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $therapist->license_status === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </form>

        <!-- Update License Product -->
        <form action="{{ route('admin.therapists.updateLicenseProduct', $therapist->id) }}"
              method="POST"
              style="margin-bottom:40px;">
            @csrf
            @method('PUT')

            <h2 class="section-title">Changer l’abonnement</h2>

            <div>
                <label for="license_product">Type de Licence :</label>
                <select name="license_product" id="license_product">
                    {{-- Legacy Plans --}}
                    <optgroup label="Anciennes formules (legacy)">
                        <option value="Essai Gratuit" {{ $therapist->license_product === 'Essai Gratuit' ? 'selected' : '' }}>Essai Gratuit (legacy)</option>
                        <option value="Starter Mensuelle" {{ $therapist->license_product === 'Starter Mensuelle' ? 'selected' : '' }}>Starter Mensuelle (legacy)</option>
                        <option value="Starter Annuelle" {{ $therapist->license_product === 'Starter Annuelle' ? 'selected' : '' }}>Starter Annuelle (legacy)</option>
                        <option value="Pro Mensuelle" {{ $therapist->license_product === 'Pro Mensuelle' ? 'selected' : '' }}>Pro Mensuelle (legacy)</option>
                        <option value="Pro Annuelle" {{ $therapist->license_product === 'Pro Annuelle' ? 'selected' : '' }}>Pro Annuelle (legacy)</option>
                    </optgroup>

                    <optgroup label="Nouvelles formules – Gratuit / Essai">
                        <option value="new_free" {{ $therapist->license_product === 'new_free' ? 'selected' : '' }}>Gratuit (new_free)</option>
                        <option value="new_trial" {{ $therapist->license_product === 'new_trial' ? 'selected' : '' }}>Essai (new_trial)</option>
                    </optgroup>

                    <optgroup label="Starter">
                        <option value="new_starter_mensuelle" {{ $therapist->license_product === 'new_starter_mensuelle' ? 'selected' : '' }}>Starter Mensuelle (new)</option>
                        <option value="new_starter_annuelle" {{ $therapist->license_product === 'new_starter_annuelle' ? 'selected' : '' }}>Starter Annuelle (new)</option>
                    </optgroup>

                    <optgroup label="PRO">
                        <option value="new_pro_mensuelle" {{ $therapist->license_product === 'new_pro_mensuelle' ? 'selected' : '' }}>PRO Mensuelle (new)</option>
                        <option value="new_pro_annuelle" {{ $therapist->license_product === 'new_pro_annuelle' ? 'selected' : '' }}>PRO Annuelle (new)</option>
                    </optgroup>

                    <optgroup label="Premium">
                        <option value="new_premium_mensuelle" {{ $therapist->license_product === 'new_premium_mensuelle' ? 'selected' : '' }}>Premium Mensuelle (new)</option>
                        <option value="new_premium_annuelle" {{ $therapist->license_product === 'new_premium_annuelle' ? 'selected' : '' }}>Premium Annuelle (new)</option>
                    </optgroup>
                </select>
            </div>

            <button type="submit">Mettre à jour</button>
        </form>

        <!-- New Form to Update Address Fields (Set By Admin) -->
        <form action="{{ route('admin.therapists.updateAddress', $therapist->id) }}" method="POST" style="margin-bottom:40px;">
            @csrf
            @method('PUT')
            <h2 class="section-title">Update Address Information</h2>
            <div>
                <label for="street_address_setByAdmin">Street Address:</label>
                <input type="text" name="street_address_setByAdmin" id="street_address_setByAdmin" value="{{ old('street_address_setByAdmin', $therapist->street_address_setByAdmin) }}">
            </div>
            <div>
                <label for="address_line2_setByAdmin">Address Line 2:</label>
                <input type="text" name="address_line2_setByAdmin" id="address_line2_setByAdmin" value="{{ old('address_line2_setByAdmin', $therapist->address_line2_setByAdmin) }}">
            </div>
            <div>
                <label for="city_setByAdmin">City:</label>
                <input type="text" name="city_setByAdmin" id="city_setByAdmin" value="{{ old('city_setByAdmin', $therapist->city_setByAdmin) }}">
            </div>
            <div>
                <label for="state_setByAdmin">Region:</label>
                <select name="state_setByAdmin" id="state_setByAdmin">
                    <option value="">Select Region</option>
                    @php
                        $regions = [
                            "Auvergne-Rhône-Alpes",
                            "Bourgogne-Franche-Comté",
                            "Bretagne",
                            "Centre-Val de Loire",
                            "Corse",
                            "Grand Est",
                            "Hauts-de-France",
                            "Ile-de-France",
                            "Normandie",
                            "Nouvelle-Aquitaine",
                            "Occitanie",
                            "Pays de la Loire",
                            "Provence Alpes Côte d’Azur",
                        ];
                    @endphp
                    @foreach($regions as $region)
                        <option value="{{ $region }}" {{ (old('state_setByAdmin', $therapist->state_setByAdmin) == $region) ? 'selected' : '' }}>
                            {{ $region }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="postal_code_setByAdmin">Postal Code:</label>
                <input type="text" name="postal_code_setByAdmin" id="postal_code_setByAdmin" value="{{ old('postal_code_setByAdmin', $therapist->postal_code_setByAdmin) }}">
            </div>
            <div>
                <label for="country_setByAdmin">Country:</label>
                <input type="text" name="country_setByAdmin" id="country_setByAdmin" value="{{ old('country_setByAdmin', $therapist->country_setByAdmin) }}">
            </div>
            <div>
                <label for="latitude_setByAdmin">Latitude:</label>
                <input type="text" name="latitude_setByAdmin" id="latitude_setByAdmin" value="{{ old('latitude_setByAdmin', $therapist->latitude_setByAdmin) }}">
            </div>
            <div>
                <label for="longitude_setByAdmin">Longitude:</label>
                <input type="text" name="longitude_setByAdmin" id="longitude_setByAdmin" value="{{ old('longitude_setByAdmin', $therapist->longitude_setByAdmin) }}">
            </div>
            <button type="submit">Update Address</button>
        </form>
{{-- =========================
     SECTION: Parrainage
========================= --}}
@php
    // --- Who referred THIS therapist? (signup via code)
    $referrerUser = null;
    if (!empty($therapist->referred_by_user_id)) {
        $referrerUser = \App\Models\User::find($therapist->referred_by_user_id);
    }

    // --- Stats this therapist generated (as referrer)
    $invitesSent = \App\Models\ReferralInvite::where('referrer_user_id', $therapist->id)->count();
    $invitesOpened = \App\Models\ReferralInvite::where('referrer_user_id', $therapist->id)->where('status', 'opened')->count();
    $invitesSignedUp = \App\Models\ReferralInvite::where('referrer_user_id', $therapist->id)->where('status', 'signed_up')->count();
    $invitesPaid = \App\Models\ReferralInvite::where('referrer_user_id', $therapist->id)->where('status', 'paid')->count();
    $invitesExpired = \App\Models\ReferralInvite::where('referrer_user_id', $therapist->id)->where('status', 'expired')->count();

    // Users who signed up with this therapist as referrer (via ?ref=CODE or invite attribution)
    $referredUsersCount = \App\Models\User::where('referred_by_user_id', $therapist->id)->count();

    // Paid conversions tracked on users (if you set referral_converted_at)
    $referredUsersPaidCount = \App\Models\User::where('referred_by_user_id', $therapist->id)
        ->whereNotNull('referral_converted_at')
        ->count();

    // Referral code owned by this therapist
    $myReferralCode = \App\Models\ReferralCode::where('user_id', $therapist->id)->first();

    // Latest referred users (for quick admin view)
    $latestReferredUsers = \App\Models\User::where('referred_by_user_id', $therapist->id)
        ->orderByDesc('id')
        ->limit(8)
        ->get(['id','name','email','created_at','license_product','license_status','referral_code_used','referral_attributed_at']);

    // Latest invites sent by this therapist
    $latestInvites = \App\Models\ReferralInvite::where('referrer_user_id', $therapist->id)
        ->orderByDesc('id')
        ->limit(8)
        ->get();
@endphp

<h2 class="section-title">Parrainage</h2>

<div class="stat-grid">
    <div class="stat-box">
        <h4>Invitations envoyées</h4>
        <p>{{ $invitesSent }}</p>
    </div>
    <div class="stat-box">
        <h4>Invitations ouvertes</h4>
        <p>{{ $invitesOpened }}</p>
    </div>
    <div class="stat-box">
        <h4>Inscrits (attribués)</h4>
        <p>{{ $referredUsersCount }}</p>
    </div>
    <div class="stat-box">
        <h4>Payants (attribués)</h4>
        <p>{{ $referredUsersPaidCount }}</p>
    </div>
</div>

<table class="styled-summary-table">
    <thead>
        <tr>
            <th>Élément</th>
            <th>Détail</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Recruté via parrainage ?</td>
            <td class="text-right">
                @if($therapist->referred_by_user_id)
                    Oui ✅
                @else
                    Non —
                @endif
            </td>
        </tr>

        <tr>
            <td>Parrain (User ID)</td>
            <td class="text-right">
                {{ $therapist->referred_by_user_id ?? '—' }}
            </td>
        </tr>

        <tr>
            <td>Parrain (email)</td>
            <td class="text-right">
                {{ $referrerUser?->email ?? '—' }}
            </td>
        </tr>

        <tr>
            <td>Code utilisé à l’inscription</td>
            <td class="text-right">
                <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">
                    {{ $therapist->referral_code_used ?? '—' }}
                </span>
            </td>
        </tr>

        <tr>
            <td>Attribution (date)</td>
            <td class="text-right">
                {{ $therapist->referral_attributed_at ? \Carbon\Carbon::parse($therapist->referral_attributed_at)->format('d/m/Y H:i') : '—' }}
            </td>
        </tr>

        <tr>
            <td>Code de parrainage du thérapeute</td>
            <td class="text-right">
                @if($myReferralCode)
                    <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">
                        {{ $myReferralCode->code }}
                    </span>
                @else
                    —
                @endif
            </td>
        </tr>

        <tr>
            <td>Invitations (détail)</td>
            <td class="text-right">
                Ouvertes: {{ $invitesOpened }} • Inscrits: {{ $invitesSignedUp }} • Payants: {{ $invitesPaid }} • Expirées: {{ $invitesExpired }}
            </td>
        </tr>
    </tbody>
</table>

{{-- Latest referred users --}}
@if($latestReferredUsers->isNotEmpty())
    <h2 class="section-title" style="margin-top:30px;">Derniers inscrits attribués</h2>
    <table class="styled-summary-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Inscription</th>
                <th>Licence</th>
                <th>Statut</th>
                <th>Code utilisé</th>
            </tr>
        </thead>
        <tbody>
            @foreach($latestReferredUsers as $u)
                <tr>
                    <td>#{{ $u->id }} — {{ $u->name }}</td>
                    <td class="text-right">{{ $u->email }}</td>
                    <td class="text-right">{{ $u->created_at?->format('d/m/Y') ?? '—' }}</td>
                    <td class="text-right">{{ $u->license_product ?? '—' }}</td>
                    <td class="text-right">{{ $u->license_status ?? '—' }}</td>
                    <td class="text-right">
                        <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">
                            {{ $u->referral_code_used ?? '—' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- Latest invites --}}
@if($latestInvites->isNotEmpty())
    <h2 class="section-title" style="margin-top:30px;">Dernières invitations envoyées</h2>
    <table class="styled-summary-table">
        <thead>
            <tr>
                <th>Email</th>
                <th>Statut</th>
                <th>Créée</th>
                <th>Ouverte</th>
                <th>Inscription</th>
                <th>Paiement</th>
            </tr>
        </thead>
        <tbody>
            @foreach($latestInvites as $inv)
                <tr>
                    <td>{{ $inv->email }}</td>
                    <td class="text-right">{{ $inv->status }}</td>
                    <td class="text-right">{{ $inv->created_at?->format('d/m/Y') ?? '—' }}</td>
                    <td class="text-right">{{ $inv->opened_at?->format('d/m/Y') ?? '—' }}</td>
                    <td class="text-right">{{ $inv->signed_up_at?->format('d/m/Y') ?? '—' }}</td>
                    <td class="text-right">{{ $inv->paid_at?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

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
                'quote' => 'Has created a Quote',
                'clientProfiles' => 'Has created a Client Profile',
                'events' => 'Has created an Event',
                'inventoryItems' => 'Has created an Inventory Item',
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
                    @elseif($attribute === 'quote')
                        @if($therapist->invoices()->where('type', 'quote')->exists())
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
                    <circle cx="50" cy="50" r="45" class="background-circle"></circle>
                    <circle cx="50" cy="50" r="45" class="progress-circle"
                        style="stroke-dashoffset: {{ 282 - (282 * ($therapist->onboarding_score / $therapist->onboarding_total)) }};"></circle>
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
