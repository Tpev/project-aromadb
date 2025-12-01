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
          crossorigin="anonymous" />

    {{-- TallStackUI --}}
    <tallstackui:script />

    {{-- Styles --}}
    @livewireStyles
    @vite([
        'resources/css/app.css',
        'resources/css/mobile.css',
        'resources/js/mobile.js',
    ])
</head>

<body class="bg-[#fff9f6] font-sans antialiased">
    <div class="min-h-screen flex flex-col pb-16">
        {{-- HEADER --}}
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

        {{-- MAIN CONTENT --}}
        <main class="flex-1 flex flex-col">
            {{ $slot }}
        </main>
    </div>

{{-- ─────────────────────────────── --}}
{{-- Bottom Navigation (only therapists logged in) --}}
{{-- ─────────────────────────────── --}}
@auth
    @if(auth()->user()->is_therapist)
        <nav class="fixed bottom-0 left-0 w-full bg-white/95 border-t border-[#e4e8d5] shadow-md z-50">
            <div class="max-w-lg mx-auto grid grid-cols-5 text-center py-2">

                {{-- HOME --}}
                <a href="{{ route('mobile.dashboard') }}"
                   class="flex flex-col items-center text-[11px]
                        {{ request()->routeIs('mobile.dashboard') ? 'text-[#647a0b]' : 'text-gray-500' }}">
                    <i class="fas fa-home text-lg mb-1"></i>
                    <span class="font-medium">Accueil</span>
                </a>

                {{-- AGENDA --}}
                <a href="{{ url('/mobile/pro/agenda') }}"
                   class="flex flex-col items-center text-[11px]
                        {{ request()->is('mobile/pro/agenda*') ? 'text-[#647a0b]' : 'text-gray-500' }}">
                    <i class="fas fa-calendar-alt text-lg mb-1"></i>
                    <span class="font-medium">Agenda</span>
                </a>

                {{-- CLIENTS --}}
                <a href="{{ url('/mobile/pro/clients') }}"
                   class="flex flex-col items-center text-[11px]
                        {{ request()->is('mobile/pro/clients*') ? 'text-[#647a0b]' : 'text-gray-500' }}">
                    <i class="fas fa-user-friends text-lg mb-1"></i>
                    <span class="font-medium">Clients</span>
                </a>

                {{-- FACTURES --}}
                <a href="{{ url('/mobile/pro/invoices') }}"
                   class="flex flex-col items-center text-[11px]
                        {{ request()->is('mobile/pro/invoices*') ? 'text-[#647a0b]' : 'text-gray-500' }}">
                    <i class="fas fa-file-invoice text-lg mb-1"></i>
                    <span class="font-medium">Factures</span>
                </a>

                {{-- MENU --}}
                <a href="{{ url('/mobile/pro/more') }}"
                   class="flex flex-col items-center text-[11px]
                        {{ request()->is('mobile/pro/more*') ? 'text-[#647a0b]' : 'text-gray-500' }}">
                    <i class="fas fa-bars text-lg mb-1"></i>
                    <span class="font-medium">Menu</span>
                </a>

            </div>
        </nav>
    @endif
@endauth


    @livewireScripts
</body>
</html>
