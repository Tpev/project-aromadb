<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Marketing Emails</title>
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

        /* Table Styles */
        .table-responsive {
            background-color: rgba(42, 42, 60, 0.8); /* Semi-transparent background */
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
            background: rgba(58, 58, 79, 0.8); /* Semi-transparent */
        }

        .table thead th {
            padding: 15px;
            font-size: 1rem;
            text-transform: uppercase;
            position: relative;
            color: #f0f0f0;
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
            background-color: rgba(58, 58, 79, 0.8); /* Semi-transparent */
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
            stroke: rgba(58, 58, 79, 0.8); /* Semi-transparent */
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
            background: rgba(42, 42, 60, 0.8); /* Semi-transparent */
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
                background: rgba(42, 42, 60, 0.8); /* Semi-transparent */
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
    <video autoplay muted loop id="bg-video">
        <source src="/images/bg01.mp4" type="video/mp4">
        Your browser does not support HTML5 video.
    </video>

    <div class="container mt-5">
        <h1 class="page-title">Marketing Emails</h1>
        
        <!-- Upload Button -->
        <a href="{{ route('admin.marketing.upload.form') }}" class="upload-btn">Upload New Emails</a>
        
        <div class="table-responsive mx-auto">
            <table class="table mx-auto">
                <thead>
                    <tr>
                        <th>Firstname</th>
                        <th>Lastname</th>
                        <th>Email</th>
                        <th>Tags</th>
                        <th>Last Emailed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($emails as $email)
                        <tr>
                            <td data-label="Firstname">{{ $email->firstname }}</td>
                            <td data-label="Lastname">{{ $email->lastname }}</td>
                            <td data-label="Email">{{ $email->email }}</td>
                            <td data-label="Tags">{{ $email->tags }}</td>
                            <td data-label="Last Emailed">{{ $email->last_emailed_at ? \Carbon\Carbon::parse($email->last_emailed_at)->format('d/m/Y H:i') : 'Never' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
