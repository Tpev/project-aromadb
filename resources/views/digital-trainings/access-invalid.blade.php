{{-- resources/views/digital-trainings/access-invalid.blade.php --}}
@php
    $brandGreen = '#647a0b';

    $title = 'Lien d’accès invalide';
    $message = 'Le lien que vous avez utilisé ne semble pas valide.';

    if ($reason === 'expired') {
        $title   = 'Lien d’accès expiré';
        $message = 'Le lien d’accès à la formation a expiré. Vous pouvez contacter votre thérapeute pour obtenir un nouvel accès.';
    } elseif ($reason === 'no_training') {
        $title   = 'Formation introuvable';
        $message = 'La formation associée à ce lien n’existe plus ou a été supprimée.';
    }
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} - {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background-color: #f3f4f6;
            color: #0f172a;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.05);
            padding: 24px 24px 20px;
            max-width: 420px;
            width: 100%;
            text-align: center;
        }
        .icon {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: #fef3c7;
            color: #92400e;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 17px;
            margin: 0 0 8px;
        }
        p {
            font-size: 13px;
            color: #4b5563;
            margin: 0 0 16px;
        }
        .brand {
            font-size: 11px;
            color: #6b7280;
            margin-top: 10px;
        }
        .btn {
            border-radius: 999px;
            padding: 7px 14px;
            font-size: 13px;
            border: 1px solid #d1d5db;
            background: white;
            cursor: pointer;
            color: #374151;
            text-decoration: none;
        }
        .btn-primary {
            background: {{ $brandGreen }};
            color: white;
            border-color: {{ $brandGreen }};
        }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">
        !
    </div>
    <h1>{{ $title }}</h1>
    <p>{{ $message }}</p>

    <p class="brand">
        {{ config('app.name') }}
    </p>
</div>
</body>
</html>
