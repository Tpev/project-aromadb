<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle Demande d'Information</title>
</head>
<body>
    <h1>Nouvelle Demande d'Information</h1>

    <p><strong>Prénom :</strong> {{ $firstName }}</p>
    <p><strong>Nom :</strong> {{ $lastName }}</p>
    <p><strong>Email :</strong> {{ $email }}</p>
    @if($phone)
        <p><strong>Téléphone :</strong> {{ $phone }}</p>
    @endif
    <hr>
    <p><strong>Message :</strong></p>
    <p>{{ $messageContent }}</p>
</body>
</html>
