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
            overflow: hidden; /* For typing effect */
            white-space: nowrap; /* Prevent text wrapping */
            border-right: .15em solid orange; /* Cursor effect */
            margin: 0 auto; /* Center the text */
        }

        /* Typing Animation */
        @keyframes typing {
            from { width: 0; }
            to { width: 100%; }
        }

        @keyframes blink-caret {
            from, to { border-color: transparent; }
            50% { border-color: orange; }
        }

        .typing-animation {
            display: inline-block;
            animation: typing 4s steps(30, end), blink-caret .75s step-end infinite;
            width: 0;
        }

        /* Rest of your styles remain the same */

        /* Option Grid and Cards */
        .option-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
        }

        .option-card {
            width: 250px;
            height: 250px;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
            transition: transform 0.3s, box-shadow 0.3s;
            perspective: 1000px;
        }

        .option-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.7);
        }

        .option-card .card-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 20px;
            backface-visibility: hidden;
            transform-style: preserve-3d;
            transform: rotateY(0deg);
            transition: transform 0.6s;
        }

        .option-card:hover .card-content {
            transform: rotateY(360deg);
        }

        .option-card .card-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(-45deg, #ff512f, #dd2476, #ff512f, #dd2476);
            background-size: 400% 400%;
            animation: gradientBG 10s ease infinite;
            border-radius: 20px;
            filter: brightness(0.7);
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
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
            z-index: 2;
        }

        .option-card .icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 3rem;
            color: #fff;
            transition: transform 0.3s;
            z-index: 2;
        }

        .option-card:hover .icon {
            transform: rotate(360deg);
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
        <h1 class="welcome-title">
            <span class="typing-animation">Welcome, Admin!</span>
        </h1>

        <div class="option-grid">
            <!-- Session Stats Card -->
            <a href="{{ route('admin.index') }}" class="option-card">
                <div class="card-content">
                    <div class="card-bg"></div>
                    <i class="icon fas fa-chart-line"></i>
                    <h3>Session Stats</h3>
                </div>
            </a>

            <!-- Therapist Index Card -->
            <a href="{{ route('admin.therapists.index') }}" class="option-card">
                <div class="card-content">
                    <div class="card-bg"></div>
                    <i class="icon fas fa-user-md"></i>
                    <h3>Therapist Index</h3>
                </div>
            </a>

            <!-- Additional Option Card (Example) -->
            <a href="{{ route('admin.license') }}" class="option-card">
                <div class="card-content">
                    <div class="card-bg"></div>
                    <i class="icon fas fa-id-card"></i>
                    <h3>License Management</h3>
                </div>
            </a>
        </div>
    </div>

    <!-- Include Font Awesome Icons (for the icons) -->
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>

    <!-- Include any necessary scripts -->
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- Include Tilt.js for 3D tilt effect -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>

    <script>
        // Initialize Vanilla Tilt on option cards
        VanillaTilt.init(document.querySelectorAll(".option-card"), {
            max: 15,
            speed: 400,
            glare: true,
            "max-glare": 0.2,
        });
    </script>
</body>
</html>
