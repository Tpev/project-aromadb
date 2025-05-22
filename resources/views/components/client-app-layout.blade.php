<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Espace Client' }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex flex-col">
<header class="bg-lime-700 text-white p-4">
    <div class="container mx-auto flex justify-between">
        <span class="font-bold">Mon Espace Client</span>
        @auth('client')
            <form method="POST" action="{{ route('client.logout') }}">
                @csrf <button>Déconnexion</button>
            </form>
        @endauth
    </div>
</header>

<main class="flex-1 container mx-auto p-6">
    @if(session('success'))
        <p class="mb-4 text-green-700 font-semibold">{{ session('success') }}</p>
    @endif
    {{ $slot }}
</main>

<footer class="bg-gray-200 text-center p-4 text-sm">
    &copy; {{ date('Y') }} Votre Cabinet
</footer>
</body>
</html>
