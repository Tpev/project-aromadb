<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Se désinscrire · Olithea</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-[#f7f8f3] font-sans text-gray-900 antialiased">
    <main class="mx-auto flex min-h-screen max-w-lg items-center px-4 py-10">
        <section class="w-full rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-[#647a0b]">Olithea</p>
            <h1 class="mt-2 text-2xl font-semibold">Se désinscrire</h1>
            @if(session('success'))
                <div class="mt-5 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
            @else
                <p class="mt-3 leading-7 text-gray-600">Vous ne recevrez plus les emails de suivi marketing envoyés par ce praticien à l'adresse <strong>{{ $contact->email }}</strong>.</p>
                <p class="mt-2 text-sm leading-6 text-gray-500">Les confirmations nécessaires à une réservation, une inscription ou un achat pourront toujours être envoyées.</p>
                <form method="POST" action="{{ $confirmUrl }}" class="mt-6">
                    @csrf
                    <button class="w-full rounded-md bg-[#647a0b] px-4 py-2.5 font-semibold text-white hover:bg-[#526509]">Confirmer la désinscription</button>
                </form>
            @endif
        </section>
    </main>
</body>
</html>
