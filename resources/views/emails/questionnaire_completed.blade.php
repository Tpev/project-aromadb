<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Questionnaire Complété') }}</title>
</head>
<body>
    <h1>{{ __('Questionnaire Complété') }}</h1>
    <p>{{ __('Le questionnaire a été rempli par le client.') }}</p>

    <h2>{{ __('Détails du Questionnaire') }}</h2>
    <p><strong>{{ __('Titre') }}:</strong> {{ $response->questionnaire->title }}</p>
    <p><strong>{{ __('Client') }}:</strong> {{ $response->clientProfile->first_name }} {{ $response->clientProfile->last_name }}</p>

    <h3>{{ __('Réponses') }}</h3>
    <ul>
        @foreach (json_decode($response->answers, true) as $questionId => $answer)
            <li>{{ $questionId }}: {{ $answer }}</li>
        @endforeach
    </ul>

    <p>{{ __('Merci!') }}</p>
</body>
</html>
