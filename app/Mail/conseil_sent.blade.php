<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau Conseil Disponible</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h1 style="color: #647a0b;">Bonjour {{ $clientProfile->first_name }} {{ $clientProfile->last_name }},</h1>

    <p>Votre thérapeute vous a envoyé un nouveau conseil : <strong>{{ $conseil->name }}</strong>.</p>

    @if($conseil->tag)
    <p><strong>Tag :</strong> {{ $conseil->tag }}</p>
    @endif

    <p>Pour accéder à ce conseil, veuillez cliquer sur le lien ci-dessous :</p>

    <p><a href="{{ $link }}" style="color: #854f38; text-decoration: underline;">Voir le Conseil</a></p>

    <p>Ce lien est unique et ne devrait pas être partagé.</p>

    <p>Si vous avez des questions, n'hésitez pas à contacter votre thérapeute.</p>

    <p>Bien à vous,</p>
    <p>Votre thérapeute</p>
</body>
</html>
