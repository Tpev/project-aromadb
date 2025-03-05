{{-- resources/views/admin/therapists/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Therapist Management</title>
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
            z-index: 1;
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
        /* Table Styles */
        .table-responsive {
            background-color: rgba(42, 42, 60, 0.8);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 40px;
            overflow-x: auto;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }
        .table {
            width: 100%;
            color: #f0f0f0;
            border-collapse: collapse;
        }
        .table thead {
            background: rgba(58, 58, 79, 0.8);
        }
        .table thead th {
            padding: 15px;
            font-size: 1rem;
            text-transform: uppercase;
            position: relative;
            color: #f0f0f0;
            cursor: default;
        }
        .sortable {
            cursor: pointer;
        }
        .table thead th::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -10px;
            transform: translateX(-50%);
            width: 50%;
            height: 2px;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            border-radius: 2px;
        }
        .table tbody tr {
            transition: background-color 0.3s, transform 0.3s;
            border-bottom: 1px solid rgba(58, 58, 79, 0.8);
        }
        .table tbody tr:hover {
            background-color: rgba(58, 58, 79, 0.8);
            transform: scale(1.01);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
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
            border: 2px solid #f0f0f0;
            box-shadow: 0 0 10px rgba(255, 81, 47, 0.5);
        }
        .name-email {
            text-align: left;
        }
        .name {
            font-weight: bold;
            font-size: 1.1rem;
            color: #f0f0f0;
        }
        .email {
            font-size: 0.9rem;
            color: #c0c0c0;
        }
        /* Progress Bar */
        .progress-bar {
            width: 100%;
            background-color: rgba(58, 58, 79, 0.8);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 5px;
            height: 15px;
        }
        .progress {
            height: 100%;
            background: linear-gradient(90deg, #ff512f, #dd2476);
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
            stroke: rgba(58, 58, 79, 0.8);
        }
        .radial-progress circle:last-child {
            stroke: url(#radialGradient);
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
            color: #f0f0f0;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
        }
        /* Action Button */
        .action-btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            color: #fff;
            border-radius: 30px;
            text-decoration: none;
            transition: background 0.3s, transform 0.3s;
            box-shadow: 0 0 10px rgba(255, 81, 47, 0.5);
        }
        .action-btn:hover {
            background: linear-gradient(90deg, #dd2476, #ff512f);
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(255, 81, 47, 0.7);
        }
        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, #ff512f, #dd2476);
            border-radius: 5px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(42, 42, 60, 0.8);
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
                background: rgba(42, 42, 60, 0.8);
                border-radius: 10px;
                padding: 10px;
            }
            .table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }
            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: calc(50% - 30px);
                font-weight: bold;
                text-align: left;
                color: #f0f0f0;
            }
            .therapist-info {
                flex-direction: row;
                align-items: center;
            }
            .name-email {
                text-align: left;
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

    <!-- SVG Gradient Definition (for radial progress) -->
    <svg width="0" height="0">
        <defs>
            <linearGradient id="radialGradient" x1="1" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#ff512f" />
                <stop offset="100%" stop-color="#dd2476" />
            </linearGradient>
        </defs>
    </svg>

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
                        <th class="text-center sortable" id="sortLastLogin">Last Login</th>
                        <th class="text-center sortable" id="sortCreatedAt">Created At</th>
                        <th class="text-center sortable" id="sortDaysSinceSignup">Days Since Sign-up</th>
                        <th class="text-center sortable" id="sortDaysSinceLogin">Days Since Last Login</th>
                        <th class="text-center">Engagement Score</th>
                        <th class="text-center">Info Requests</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($therapists as $therapist)
                        @php
                            // Calculate days differences as absolute positive integers
                            $daysSinceSignup = (int) \Carbon\Carbon::now()->diffInDays($therapist->created_at, true);
                            $daysSinceLogin = $therapist->last_login_at 
                                ? (int) \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($therapist->last_login_at), true)
                                : null;
                        @endphp
                        <tr class="text-center">
                            <td data-label="ID">{{ $therapist->id }}</td>
                            <td data-label="Therapist" class="text-wrap">
                                <div class="therapist-info">
                                    <img src="{{ asset('storage/' . $therapist->profile_picture) }}" alt="Avatar" class="avatar">
                                    <div class="name-email">
                                        <span class="name">{{ $therapist->name }}</span>
                                        <span class="email">{{ $therapist->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Onboarding Score">
                                <div class="progress-bar">
                                    <div class="progress" style="width: {{ ($therapist->onboarding_score / $therapist->onboarding_total) * 100 }}%;"></div>
                                </div>
                                <span>{{ $therapist->onboarding_score }} / {{ $therapist->onboarding_total }}</span>
                            </td>
                            <td data-label="Last Login" class="text-wrap">
                                {{ $therapist->last_login_at ? \Carbon\Carbon::parse($therapist->last_login_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i') : 'Never' }}
                            </td>
                            <td data-label="Created At" class="text-wrap">
                                {{ $therapist->created_at->setTimezone('Europe/Paris')->format('d/m/Y') }}
                            </td>
                            <td data-label="Days Since Sign-up" data-sort="{{ $daysSinceSignup }}">
                                {{ $daysSinceSignup }}
                            </td>
                            <td data-label="Days Since Last Login" data-sort="{{ $daysSinceLogin ?? 99999 }}">
                                @if($daysSinceLogin !== null)
                                    {{ $daysSinceLogin }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td data-label="Engagement Score">
                                <div class="radial-progress" data-percentage="{{ $therapist->engagement_score }}">
                                    <svg viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="45"></circle>
                                        <circle cx="50" cy="50" r="45" style="stroke-dashoffset: {{ 282 - (282 * $therapist->engagement_score) / 100 }};"></circle>
                                    </svg>
                                    <div class="percentage">{{ $therapist->engagement_score }}%</div>
                                </div>
                            </td>
                            
							<!-- NEW CELL FOR INFO REQUESTS COUNT -->
                            <td data-label="Info Requests">
                                {{ $therapist->information_requests_count }}
                            </td>
							
                            <td data-label="Actions">
                                <a href="{{ route('admin.therapists.show', $therapist->id) }}" class="action-btn">View Details</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sorting Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const table = document.getElementById("therapistsTable");
            const sortLastLoginHeader = document.getElementById("sortLastLogin");
            const sortCreatedAtHeader = document.getElementById("sortCreatedAt");
            const sortDaysSinceSignupHeader = document.getElementById("sortDaysSinceSignup");
            const sortDaysSinceLoginHeader = document.getElementById("sortDaysSinceLogin");

            let ascLastLogin = true;
            let ascCreatedAt = true;
            let ascDaysSinceSignup = true;
            let ascDaysSinceLogin = true;

            sortLastLoginHeader.addEventListener("click", function() {
                sortTableByColumn(table, 3, ascLastLogin, parseDate);
                ascLastLogin = !ascLastLogin;
            });
            sortCreatedAtHeader.addEventListener("click", function() {
                sortTableByColumn(table, 4, ascCreatedAt, parseDate);
                ascCreatedAt = !ascCreatedAt;
            });
            sortDaysSinceSignupHeader.addEventListener("click", function() {
                sortTableByColumn(table, 5, ascDaysSinceSignup, parseNumber);
                ascDaysSinceSignup = !ascDaysSinceSignup;
            });
            sortDaysSinceLoginHeader.addEventListener("click", function() {
                sortTableByColumn(table, 6, ascDaysSinceLogin, parseNumber);
                ascDaysSinceLogin = !ascDaysSinceLogin;
            });

            function sortTableByColumn(table, columnIndex, asc = true, parseFn = parseDate) {
                const tbody = table.tBodies[0];
                const rows = Array.from(tbody.querySelectorAll("tr"));
                rows.sort((a, b) => {
                    // Try to read data-sort attribute first, fallback to cell text
                    const aCell = a.querySelectorAll("td")[columnIndex];
                    const bCell = b.querySelectorAll("td")[columnIndex];
                    const aVal = aCell.getAttribute("data-sort") || aCell.textContent.trim();
                    const bVal = bCell.getAttribute("data-sort") || bCell.textContent.trim();
                    return asc ? parseFn(aVal) - parseFn(bVal) : parseFn(bVal) - parseFn(aVal);
                });
                rows.forEach(row => tbody.appendChild(row));
            }

            function parseDate(text) {
                if(text === "Never") {
                    return new Date(0);
                }
                // Supports "d/m/Y" and "d/m/Y H:i"
                const parts = text.split(" ");
                const dateParts = parts[0].split("/").map(num => parseInt(num, 10));
                let hours = 0, minutes = 0;
                if (parts.length > 1) {
                    [hours, minutes] = parts[1].split(":").map(num => parseInt(num, 10));
                }
                return new Date(dateParts[2], dateParts[1] - 1, dateParts[0], hours, minutes);
            }

            function parseNumber(text) {
                return parseInt(text, 10);
            }
        });
    </script>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
