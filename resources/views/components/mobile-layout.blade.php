@props(['title' => null, 'hideNav' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#647a0b">

    <title>{{ $title ? $title . ' | AromaMade PRO' : 'AromaMade PRO' }}</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
          crossorigin="anonymous" />

    <tallstackui:script />

    @livewireStyles
    @vite([
        'resources/css/app.css',
        'resources/css/mobile.css',
        'resources/js/mobile.js',
    ])
    @stack('head')
</head>

<body class="mobile-theme bg-[#fff9f6] font-sans antialiased">
    <div class="min-h-screen flex flex-col pb-20">
        <header class="sticky top-0 z-40 bg-white/95 border-b border-[#e4e8d5] backdrop-blur">
            <div class="mx-auto flex max-w-lg items-center justify-between px-4 py-3">
                <a href="{{ auth()->check() && auth()->user()->is_therapist ? route('mobile.dashboard') : route('mobile.entry') }}"
                   class="flex min-w-0 items-center gap-2">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#647a0b] text-sm font-bold text-white">
                        A
                    </span>
                    <span class="min-w-0 leading-tight">
                        <span class="block truncate text-sm font-semibold text-gray-900">AromaMade PRO</span>
                        <span class="block truncate text-[11px] text-gray-500">Espace mobile</span>
                    </span>
                </a>

                @auth
                    <form method="POST" action="{{ route('mobile.logout') }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-gray-500"
                                aria-label="{{ __('Se deconnecter') }}">
                            <i class="fas fa-sign-out-alt text-xs"></i>
                        </button>
                    </form>
                @endauth
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>
    </div>

    @auth
        @if(!$hideNav && auth()->user()->is_therapist)
            <nav class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 shadow-md backdrop-blur">
                <div class="mx-auto grid max-w-lg grid-cols-5 px-1 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 text-center">
                    <a href="{{ route('mobile.dashboard') }}"
                       class="flex flex-col items-center text-[11px] {{ request()->routeIs('mobile.dashboard') ? 'text-[#647a0b]' : 'text-gray-500' }}">
                        <i class="fas fa-home mb-1 text-lg"></i>
                        <span class="font-medium">Accueil</span>
                    </a>

                    <a href="{{ route('mobile.appointments.index') }}"
                       class="flex flex-col items-center text-[11px] {{ request()->routeIs('mobile.appointments.*') ? 'text-[#647a0b]' : 'text-gray-500' }}">
                        <i class="fas fa-calendar-alt mb-1 text-lg"></i>
                        <span class="font-medium">RDV</span>
                    </a>

                    <a href="{{ route('mobile.clients.index') }}"
                       class="flex flex-col items-center text-[11px] {{ request()->routeIs('mobile.clients.*') ? 'text-[#647a0b]' : 'text-gray-500' }}">
                        <i class="fas fa-user-friends mb-1 text-lg"></i>
                        <span class="font-medium">Clients</span>
                    </a>

                    <a href="{{ route('mobile.invoices.index') }}"
                       class="flex flex-col items-center text-[11px] {{ request()->routeIs('mobile.invoices.*') ? 'text-[#647a0b]' : 'text-gray-500' }}">
                        <i class="fas fa-file-invoice mb-1 text-lg"></i>
                        <span class="font-medium">Factures</span>
                    </a>

                    <a href="{{ route('mobile.menu') }}"
                       class="flex flex-col items-center text-[11px] {{ request()->routeIs('mobile.menu') ? 'text-[#647a0b]' : 'text-gray-500' }}">
                        <i class="fas fa-bars mb-1 text-lg"></i>
                        <span class="font-medium">Menu</span>
                    </a>
                </div>
            </nav>
        @endif
    @endauth

    @livewireScripts
    @stack('scripts')
</body>
</html>
