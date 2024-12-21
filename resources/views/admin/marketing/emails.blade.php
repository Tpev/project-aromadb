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

        /* Button Styles */
        .upload-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            color: #fff;
            border-radius: 30px;
            text-decoration: none;
            text-align: center;
            transition: background 0.3s, transform 0.3s;
            box-shadow: 0 0 10px rgba(255, 81, 47, 0.5);
        }

        .upload-btn:hover {
            background: linear-gradient(90deg, #dd2476, #ff512f);
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(255, 81, 47, 0.7);
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
