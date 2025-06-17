<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Espace Client – AromaMade Pro' }}</title>
    <meta name="description" content="Espace sécurisé pour les clients AromaMade Pro : messagerie, documents, rendez-vous, et factures.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-gray-800 antialiased">

    <!-- Header -->
    <header class="bg-lime-700 text-white shadow">
        <div class="container mx-auto flex justify-between items-center p-4">
            <h1 class="text-lg font-semibold tracking-wide">AromaMade Pro – Espace Client</h1>
            @auth('client')
                <form method="POST" action="{{ route('client.logout') }}" class="ml-4">
                    @csrf
                    <button type="submit" class="text-white hover:underline text-sm">Déconnexion</button>
                </form>
            @endauth
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 container mx-auto p-6 space-y-4">
        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-gray-100 text-center py-4 text-sm text-gray-500 mt-10 border-t">
        &copy; {{ date('Y') }} AromaMade Pro — Espace Client Sécurisé
    </footer>

</body>
</html>
