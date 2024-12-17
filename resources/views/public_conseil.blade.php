<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $conseil->name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive viewport -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
            line-height: 1.5;
        }

        h1 {
            color: #647a0b;
            font-size: 1.8rem; /* Larger heading for easier reading on mobile */
            margin-bottom: 1rem;
            text-align: center;
        }

        a {
            color: #854f38;
            text-decoration: underline;
            word-wrap: break-word; /* Ensures long URLs wrap on small screens */
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .content {
            margin-top: 20px;
            font-size: 1rem;
            padding: 0 10px; /* Slight padding for better readability on small screens */
        }

        .content h2, .content h3 {
            color: #647a0b;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .content p {
            margin-bottom: 1rem;
        }

        .content a {
            color: #854f38;
        }

        /* Tag and link section */
        .tag-section,
        .attachment-section {
            padding: 10px;
            text-align: center;
            font-size: 1rem;
        }

        .tag-section p,
        .attachment-section p {
            margin-bottom: 1rem;
        }

        /* Responsive adjustments for smaller screens */
        @media (max-width: 600px) {
            body {
                margin: 10px;
            }

            h1 {
                font-size: 1.5rem;
            }

            .content {
                font-size: 0.95rem;
                padding: 0 5px;
            }
        }
    </style>
</head>
<body>
    <h1>{{ $conseil->name }}</h1>



    @if($conseil->image)
        <div style="margin-top:20px; text-align:center;">
            <img src="{{ asset('storage/' . $conseil->image) }}" alt="{{ $conseil->name }}">
        </div>
    @endif

    <div class="content">
        {!! $conseil->content !!}
    </div>

    @if($conseil->attachment)
        <div class="attachment-section">
            <p>
                <a href="{{ asset('storage/' . $conseil->attachment) }}" target="_blank">
                    Télécharger la pièce jointe (PDF)
                </a>
            </p>
        </div>
    @endif
</body>
</html>
