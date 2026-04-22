<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Espace Client - AromaMade Pro' }}</title>
    <meta name="description" content="Espace securise pour les clients AromaMade Pro : messagerie, documents, rendez-vous, factures et communautes privees.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-gray-800 antialiased">

    <header class="bg-lime-700 text-white shadow">
        <div class="container mx-auto flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-lg font-semibold tracking-wide">AromaMade Pro - Espace Client</h1>

            @auth('client')
                <div class="flex items-center gap-5">
                    <nav class="flex items-center gap-4 text-sm font-medium">
                        <a href="{{ route('client.home') }}" class="hover:underline {{ request()->routeIs('client.home') ? 'underline underline-offset-4' : '' }}">Accueil</a>
                        <a href="{{ route('client.communities.index') }}" class="hover:underline {{ request()->routeIs('client.communities.*') ? 'underline underline-offset-4' : '' }}">Communautes</a>
                    </nav>

                    <form method="POST" action="{{ route('client.logout') }}">
                        @csrf
                        <button type="submit" class="text-sm hover:underline">Deconnexion</button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="flex-1 container mx-auto p-6 space-y-4">
        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="bg-gray-100 text-center py-4 text-sm text-gray-500 mt-10 border-t">
        &copy; {{ date('Y') }} AromaMade Pro - Espace Client securise
    </footer>

</body>
</html>
