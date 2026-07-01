@props(['title' => null, 'showNav' => true])

@php($client = auth('client')->user())

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#647a0b">

    <title>{{ $title ? $title . ' | Espace client AromaMade' : 'Espace client AromaMade' }}</title>

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
    <div class="min-h-screen flex flex-col {{ $showNav && $client ? 'pb-24' : '' }}">
        <header class="sticky top-0 z-40 border-b border-[#e4e8d5] bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-lg items-center justify-between px-4 py-3">
                <a href="{{ $client ? route('mobile.client.home') : route('mobile.client.login') }}"
                   class="flex min-w-0 items-center gap-2">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#647a0b] text-sm font-bold text-white">
                        C
                    </span>
                    <span class="min-w-0 leading-tight">
                        <span class="block truncate text-sm font-semibold text-gray-900">Espace client</span>
                        <span class="block truncate text-[11px] text-gray-500">AromaMade mobile</span>
                    </span>
                </a>

                @if($client)
                    <form method="POST" action="{{ route('mobile.client.logout') }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-gray-500"
                                aria-label="Se deconnecter">
                            <i class="fas fa-sign-out-alt text-xs"></i>
                        </button>
                    </form>
                @endif
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>
    </div>

    @if($showNav && $client)
        <nav class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 shadow-md backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-5 px-1 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 text-center">
                <a href="{{ route('mobile.client.home') }}"
                   class="flex flex-col items-center text-[11px] {{ request()->routeIs('mobile.client.home') ? 'text-[#647a0b]' : 'text-gray-500' }}">
                    <i class="fas fa-home mb-1 text-lg"></i>
                    <span class="font-medium">Accueil</span>
                </a>

                <a href="{{ route('mobile.client.messages.index') }}"
                   class="flex flex-col items-center text-[11px] {{ request()->routeIs('mobile.client.messages.*') ? 'text-[#647a0b]' : 'text-gray-500' }}">
                    <i class="fas fa-comments mb-1 text-lg"></i>
                    <span class="font-medium">Messages</span>
                </a>

                <a href="{{ route('mobile.client.communities.index') }}"
                   class="flex flex-col items-center text-[11px] {{ request()->routeIs('mobile.client.communities.*') ? 'text-[#647a0b]' : 'text-gray-500' }}">
                    <i class="fas fa-users mb-1 text-lg"></i>
                    <span class="font-medium">Groupes</span>
                </a>

                <a href="{{ route('mobile.client.home') }}#documents"
                   class="flex flex-col items-center text-[11px] text-gray-500">
                    <i class="fas fa-folder-open mb-1 text-lg"></i>
                    <span class="font-medium">Docs</span>
                </a>

                <form method="POST" action="{{ route('mobile.client.logout') }}" class="contents">
                    @csrf
                    <button type="submit" class="flex flex-col items-center text-[11px] text-gray-500">
                        <i class="fas fa-sign-out-alt mb-1 text-lg"></i>
                        <span class="font-medium">Sortie</span>
                    </button>
                </form>
            </div>
        </nav>
    @endif

    @livewireScripts
    @stack('scripts')
</body>
</html>
