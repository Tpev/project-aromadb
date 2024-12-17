<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $conseil->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }

        h1 {
            color: #647a0b;
        }

        a {
            color: #854f38;
            text-decoration: underline;
        }

        img {
            max-width: 100%;
        }

        .content {
            margin-top: 20px;
        }

        .content h2, .content h3 {
            color: #647a0b;
        }

        .content a {
            color: #854f38;
        }
    </style>
</head>
<body>
    <h1>{{ $conseil->name }}</h1>

    @if($conseil->tag)
        <p><strong>Tag :</strong> {{ $conseil->tag }}</p>
    @endif

    @if($conseil->image)
        <div style="margin-top:20px;">
            <img src="{{ asset('storage/' . $conseil->image) }}" alt="{{ $conseil->name }}">
        </div>
    @endif

    <div class="content">
        {!! $conseil->content !!}
    </div>

    @if($conseil->attachment)
        <p style="margin-top:20px;">
            <a href="{{ asset('storage/' . $conseil->attachment) }}" target="_blank">
                Télécharger la pièce jointe (PDF)
            </a>
        </p>
    @endif
</body>
</html>
