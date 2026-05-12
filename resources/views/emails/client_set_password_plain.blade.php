<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Activation de votre compte</title>
</head>
<body>
    <h2>Bonjour {{ $client->first_name ?? 'Cher client' }},</h2>

    <p>Bienvenue chez Olithea Pro. Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>

    <p>
        <a href="{{ $url }}" style="background: #6B4A3A; padding: 10px 20px; color: white; text-decoration: none; border-radius: 4px;">
            Activer mon compte
        </a>
    </p>

    <p>Si le bouton ne fonctionne pas, copiez-collez ce lien dans votre navigateur :</p>
    <p>{{ $url }}</p>

    <p>Merci,<br>L'équipe Olithea Pro</p>
</body>
</html>
