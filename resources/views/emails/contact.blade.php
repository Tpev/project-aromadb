{{-- resources/views/emails/contact.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau Message de Contact</title>
</head>
<body>
    <h2>Nouveau message de {{ $data['name'] }}</h2>

    <p><strong>Nom :</strong> {{ $data['name'] }}</p>
    <p><strong>Email :</strong> {{ $data['email'] }}</p>
    <p><strong>Sujet :</strong> {{ $data['subject'] }}</p>
    <p><strong>Message :</strong></p>
    <p>{!! nl2br(e($data['message'])) !!}</p>
</body>
</html>
