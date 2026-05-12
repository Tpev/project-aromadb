@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ? $title . ' | Olithea PRO' : 'Olithea PRO' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/olithea-mark-cropped.png') }}">

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

<body class="bg-brand-background font-sans text-brand-text antialiased">
    <div class="min-h-screen flex flex-col pb-16">
        {{-- HEADER --}}
        <header class="px-4 pt-4 pb-3 flex items-center justify-between bg-white/90 border-b border-brand-border">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-primary-50 border border-brand-border flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('images/brand/olithea-mark-cropped.png') }}" alt="" class="h-7 w-7 object-contain">
                </div>
                <div class="flex flex-col leading-tight">
                    <span class="text-sm font-semibold text-brand-text-strong">Olithea PRO</span>
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
        <nav class="fixed bottom-0 left-0 w-full bg-white/95 border-t border-brand-border shadow-md z-50">
            <div class="max-w-lg mx-auto grid grid-cols-5 text-center py-2">

                {{-- HOME --}}
                <a href="{{ route('mobile.dashboard') }}"
                   class="flex flex-col items-center text-[11px]
                        {{ request()->routeIs('mobile.dashboard') ? 'text-primary-700' : 'text-gray-500' }}">
                    <i class="fas fa-home text-lg mb-1"></i>
                    <span class="font-medium">Accueil</span>
                </a>

                {{-- AGENDA --}}
                <a href="{{ url('/mobile/rendez-vous') }}"
                   class="flex flex-col items-center text-[11px]
                        {{ request()->is('/mobile/rendez-vous') ? 'text-primary-700' : 'text-gray-500' }}">
                    <i class="fas fa-calendar-alt text-lg mb-1"></i>
                    <span class="font-medium">RDV</span>
                </a>

                {{-- CLIENTS --}}
                <a href="{{ url('/mobile/clients') }}"
                   class="flex flex-col items-center text-[11px]
                        {{ request()->is('mobile/pro/clients*') ? 'text-primary-700' : 'text-gray-500' }}">
                    <i class="fas fa-user-friends text-lg mb-1"></i>
                    <span class="font-medium">Clients</span>
                </a>

                {{-- FACTURES --}}
                <a href="{{ url('/mobile/invoices') }}"
                   class="flex flex-col items-center text-[11px]
                        {{ request()->is('mobile/pro/invoices*') ? 'text-primary-700' : 'text-gray-500' }}">
                    <i class="fas fa-file-invoice text-lg mb-1"></i>
                    <span class="font-medium">Factures</span>
                </a>

                {{-- MENU --}}
                <a href="{{ url('/mobile/pro/more') }}"
                   class="flex flex-col items-center text-[11px]
                        {{ request()->is('mobile/pro/more*') ? 'text-primary-700' : 'text-gray-500' }}">
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
