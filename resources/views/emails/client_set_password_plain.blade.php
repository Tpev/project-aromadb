<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Activation de votre compte</title>
</head>
<body>
    <h2>Bonjour{{ $client->first_name ? ' '.$client->first_name : '' }},</h2>

    <p>
        <strong>{{ $practitionerName }}</strong> vous invite à activer votre espace client sécurisé sur Olithéa.
    </p>

    <p>
        Cet espace vous permettra de retrouver les éléments que votre praticien met à votre disposition,
        par exemple vos rendez-vous, documents, questionnaires ou messages.
    </p>

    <p>Pour choisir votre mot de passe et activer votre espace, cliquez sur le bouton ci-dessous :</p>

    <p>
        <a href="{{ $url }}" style="background: #647a0b; padding: 10px 20px; color: white; text-decoration: none; border-radius: 4px;">
            Activer mon compte
        </a>
    </p>

    <p>Si le bouton ne fonctionne pas, copiez-collez ce lien dans votre navigateur :</p>
    <p>{{ $url }}</p>

    @if($client->password_setup_expires_at)
        <p>
            Ce lien est personnel et valable jusqu’au
            <strong>{{ $client->password_setup_expires_at->format('d/m/Y à H:i') }}</strong>.
        </p>
    @else
        <p>Ce lien est personnel et valable pendant 3 jours.</p>
    @endif

    <p>
        Si vous n’attendiez pas cette invitation, vous pouvez ignorer cet e-mail ou contacter directement
        {{ $practitionerName }}.
    </p>

    <p>Bien cordialement,<br>{{ $practitionerName }} via Olithéa</p>
</body>
</html>
