{{-- resources/views/admin/welcome.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Welcome</title>
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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh; /* Full height to center content vertically */
            text-align: center;
        }

        .welcome-title {
            font-size: 3rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 50px;
            text-transform: uppercase;
            letter-spacing: 3px;
            position: relative;
        }

        .welcome-title::after {
            content: '';
            width: 200px;
            height: 4px;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            display: block;
            margin: 30px auto 0;
            border-radius: 2px;
        }

        .option-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
        }

        .option-card {
            background-color: rgba(42, 42, 60, 0.8);
            backdrop-filter: blur(5px);
            width: 250px;
            height: 250px;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            text-decoration: none;
        }

        .option-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.7);
        }

        .option-card::before {
            content: '';
            position: absolute;
            top: -100%;
            left: -100%;
            width: 300%;
            height: 300%;
            background: linear-gradient(45deg, transparent, transparent, rgba(255, 81, 47, 0.5));
            transition: all 0.5s;
        }

        .option-card:hover::before {
            top: -200%;
            left: -200%;
            background: linear-gradient(45deg, transparent, transparent, rgba(221, 36, 118, 0.5));
        }

        .option-card h3 {
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff;
            text-shadow: 0 0 10px rgba(0, 0, 0, 0.7);
            margin: 0;
        }

        .option-card .icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 3rem;
            color: #ff512f;
            transition: color 0.3s;
        }

        .option-card:hover .icon {
            color: #dd2476;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .welcome-title {
                font-size: 2.5rem;
            }

            .option-card {
                width: 200px;
                height: 200px;
            }

            .option-card h3 {
                font-size: 1.2rem;
            }

            .option-card .icon {
                font-size: 2.5rem;
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

    <!-- Container -->
    <div class="container">
        <h1 class="welcome-title">Welcome, Admin!</h1>

        <div class="option-grid">
            <!-- Session Stats Card -->
            <a href="{{ route('admin.index') }}" class="option-card">
                <i class="icon fas fa-chart-line"></i>
                <h3>Session Stats</h3>
            </a>

            <!-- Therapist Index Card -->
            <a href="{{ route('admin.therapists.index') }}" class="option-card">
                <i class="icon fas fa-user-md"></i>
                <h3>Therapist Index</h3>
            </a>
        </div>
    </div>

    <!-- Include Font Awesome Icons (for the icons) -->
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>

    <!-- Include any necessary scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
