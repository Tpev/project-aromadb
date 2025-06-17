<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Activation de votre compte</title>
</head>
<body>
    <h2>Bonjour {{ $client->name ?? 'Cher client' }},</h2>

    <p>Bienvenue chez AromaMade Pro. Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>

    <p>
        <a href="{{ $url }}" style="background: #647a0b; padding: 10px 20px; color: white; text-decoration: none; border-radius: 4px;">
            Activer mon compte
        </a>
    </p>

    <p>Si le bouton ne fonctionne pas, copiez-collez ce lien dans votre navigateur :</p>
    <p>{{ $url }}</p>

    <p>Merci,<br>L'équipe AromaMade Pro</p>
</body>
</html>
