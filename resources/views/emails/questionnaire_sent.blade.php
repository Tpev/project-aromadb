<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Questionnaire Envoyé</title>
</head>
<body>
    <h1>Bonjour, {{ $client_profile_name }},</h1>
    <p>Vous avez reçu un nouveau questionnaire intitulé : <strong>{{ $questionnaireTitle }}</strong>.</p>
    <p>Ce questionnaire a été envoyé par : <strong>{{ $therapistName }}</strong>.</p>
    <p>Pour remplir le questionnaire, cliquez sur le lien suivant :</p>
    <p><a href="{{ $link }}">{{ $link }}</a></p>
    <p>Merci !</p>
</body>
</html>
