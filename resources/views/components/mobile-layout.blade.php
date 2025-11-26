@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        {{ $title ? $title . ' | AromaMade PRO' : 'AromaMade PRO' }}
    </title>

    {{-- Same assets as your main app layout --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#fff9f6] font-sans antialiased">
    <div class="min-h-screen flex flex-col">

        {{-- Minimal mobile header --}}
        <header class="px-4 pt-4 pb-3 flex items-center justify-between bg-white/90 border-b border-[#e4e8d5]">
            <div class="flex items-center gap-2">
                {{-- You can swap this for your logo component --}}
                <div class="w-8 h-8 rounded-full bg-[#647a0b] flex items-center justify-center text-white text-sm font-bold">
                    A
                </div>
                <div class="flex flex-col leading-tight">
                    <span class="text-sm font-semibold text-gray-900">AromaMade PRO</span>
                    <span class="text-[11px] text-gray-500">Espace mobile</span>
                </div>
            </div>

            {{-- (Optionnel) un petit bouton "Fermer" pour plus tard, si besoin --}}
            {{-- <button class="text-xs text-gray-500">Fermer</button> --}}
        </header>

        {{-- Main content --}}
        <main class="flex-1 flex flex-col">
            {{ $slot }}
        </main>

        {{-- Pas de footer global ici, tu peux ajouter un bottom-nav mobile plus tard si tu veux --}}
    </div>
</body>
</html>
