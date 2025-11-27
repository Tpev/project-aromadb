@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ? $title . ' | AromaMade PRO' : 'AromaMade PRO' }}</title>

    {{-- Icons --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- ✔ TallStackUI MUST come BEFORE Vite --}}
    <tallstackui:script />

    {{-- Styles --}}
    @livewireStyles

    {{-- ✔ Vite comes after --}}
    @vite([
    'resources/css/app.css',
    'resources/css/mobile.css',   // <= nouveau
    'resources/js/mobile.js',
])


</head>

<body class="bg-[#fff9f6] font-sans antialiased">
    <div class="min-h-screen flex flex-col">
        <header class="px-4 pt-4 pb-3 flex items-center justify-between bg-white/90 border-b border-[#e4e8d5]">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-[#647a0b] flex items-center justify-center text-white text-sm font-bold">
                    A
                </div>
                <div class="flex flex-col leading-tight">
                    <span class="text-sm font-semibold text-gray-900">AromaMade PRO</span>
                    <span class="text-[11px] text-gray-500">Espace mobile</span>
                </div>
            </div>
        </header>

        <main class="flex-1 flex flex-col">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
