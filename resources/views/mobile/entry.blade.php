{{-- resources/views/mobile/entry.blade.php --}}
<x-mobile-layout title="Bienvenue">
    <div
        class="min-h-screen flex flex-col items-center justify-center px-6 py-10"
        style="background: radial-gradient(circle at top, #fffaf3 0, #f7f4ec 40%, #eee7dc 100%);"
    >
        <div class="w-full max-w-md space-y-10">

            {{-- Intro --}}
            <div class="text-center space-y-3 px-2">
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-medium bg-secondary-50 text-secondary-700"
                >
                    <i class="fas fa-mobile-alt mr-1.5 text-[10px]"></i>
                    {{ __('Application mobile AromaMade PRO') }}
                </span>

                <h1 class="text-[24px] font-extrabold text-gray-900 tracking-tight">
                    {{ __('Bienvenue 👋') }}
                </h1>

                <p class="text-[13px] text-gray-600 leading-relaxed">
                    {{ __('Commencez par nous dire si vous cherchez un praticien ou si vous utilisez AromaMade PRO en tant que thérapeute.') }}
                </p>
            </div>

            <div class="space-y-6">

                {{-- CLIENT – carte principale --}}
                <x-ts-card class="rounded-3xl shadow-lg border border-primary-50 p-0 overflow-hidden">
                    <a
                        href="{{ route('mobile.search.index') }}"
                        class="flex flex-col gap-4 px-4 py-4 active:scale-[0.99] transition-transform duration-100"
                    >
                        <div class="flex items-start gap-4">
                            <div class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-primary-50">
                                <i class="fas fa-user text-sm text-primary-700"></i>
                            </div>

                            <div class="flex-1 space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-semibold uppercase tracking-wide text-primary-700">
                                        {{ __('Espace Client') }}
                                    </span>

                                    <span class="text-[9px] px-2 py-[2px] rounded-full font-medium bg-secondary-600 text-white">
                                        {{ __('Le plus utilisé') }}
                                    </span>
                                </div>

                                <div class="text-[17px] font-semibold text-gray-900">
                                    {{ __('Je cherche un praticien') }}
                                </div>

                                <p class="text-[12px] text-gray-600 leading-snug">
                                    {{ __('Prendre un rendez-vous, découvrir les événements bien-être et trouver un praticien proche de chez vous ou en visio.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-1">
                            <div class="flex items-center gap-1.5 text-[11px] text-gray-500">
                                <i class="fas fa-map-marker-alt text-[10px] text-secondary-500"></i>
                                <span class="break-words">
                                    {{ __('Recherche par lieu, spécialité ou type de soin.') }}
                                </span>
                            </div>

                            {{-- Faux bouton (span) pour éviter le <button> dans un <a> --}}
                            <span
                                class="inline-flex items-center justify-center text-[11px] px-3 py-1 rounded-full bg-primary-600 text-white font-semibold shadow-sm"
                            >
                                {{ __('Continuer') }}
                                <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                            </span>
                        </div>
                    </a>
                </x-ts-card>

                {{-- Séparateur brun --}}
                <div class="flex flex-col items-center gap-2">
                    <span class="text-[10px] text-gray-400 uppercase tracking-[0.14em]">
                        {{ __('Ou thérapeute') }}
                    </span>
                    <span class="block h-[2px] w-20 rounded-full bg-secondary-600"></span>
                </div>

                {{-- PRATICIEN – carte secondaire plus compacte --}}
                <x-ts-card class="rounded-2xl shadow-md bg-primary-600 text-white border-0 px-4 py-3">
                    <a href="{{ route('dashboard-pro') }}" class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="inline-flex items-center justify-center w-8 h-8 rounded-2xl bg-white/15">
                                <i class="fas fa-leaf text-xs text-white"></i>
                            </div>

                            <div class="space-y-0.5">
                                <span class="text-[11px] font-semibold uppercase tracking-wide text-white/90">
                                    {{ __('Espace Praticien') }}
                                </span>

                                <div class="text-[13px] font-semibold">
                                    {{ __('Je suis thérapeute / praticien(ne)') }}
                                </div>
                            </div>
                        </div>

                        <i class="fas fa-chevron-right text-sm text-white/90"></i>
                    </a>
                </x-ts-card>
            </div>

            {{-- Info --}}
            <p class="text-[11px] text-gray-500 text-center leading-relaxed px-4">
                {{ __('Vous pourrez changer d’espace à tout moment depuis le menu de l’application. Explorez librement avant de vous engager.') }}
            </p>
        </div>
    </div>
</x-mobile-layout>
