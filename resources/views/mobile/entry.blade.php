<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Bienvenue sur AromaMade PRO') }}
        </h2>
    </x-slot>

    <div class="min-h-[calc(100vh-4rem)] flex flex-col items-center justify-center px-6 py-10 bg-[#fff9f6]">
        <div class="w-full max-w-md text-center space-y-8">

            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">
                    {{ __('Vous êtes…') }}
                </h1>
                <p class="text-sm text-gray-600">
                    {{ __('Choisissez votre espace pour continuer sur l’application mobile.') }}
                </p>
            </div>

            {{-- Client --}}
            <a href="{{ url('/') }}"
               class="block w-full bg-white border border-[#e4e8d5] rounded-2xl shadow-sm px-5 py-4 text-left
                      flex items-center justify-between hover:shadow-md transition">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-[#647a0b] mb-1">
                        {{ __('Espace Client') }}
                    </div>
                    <div class="text-base font-semibold text-gray-900">
                        {{ __('Je cherche un praticien') }}
                    </div>
                    <p class="text-xs text-gray-600 mt-1">
                        {{ __('Prendre un rendez-vous, voir les événements, découvrir les praticiens…') }}
                    </p>
                </div>
                <i class="fas fa-arrow-right text-gray-400 text-lg"></i>
            </a>

            {{-- Praticien --}}
            <a href="{{ route('dashboard-pro') }}"
               class="block w-full bg-[#647a0b] text-white rounded-2xl shadow px-5 py-4 text-left
                      flex items-center justify-between hover:brightness-110 transition">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-white/80 mb-1">
                        {{ __('Espace Praticien') }}
                    </div>
                    <div class="text-base font-semibold">
                        {{ __('Je suis praticien(ne)') }}
                    </div>
                    <p class="text-xs text-white/80 mt-1">
                        {{ __('Accéder à mon agenda, mes clients, mes factures et tous mes outils AromaMade PRO.') }}
                    </p>
                </div>
                <i class="fas fa-arrow-right text-white text-lg"></i>
            </a>

            <p class="text-[11px] text-gray-400 mt-6">
                {{ __('Vous pouvez changer d’espace à tout moment depuis le menu de l’application.') }}
            </p>
        </div>
    </div>
</x-app-layout>
