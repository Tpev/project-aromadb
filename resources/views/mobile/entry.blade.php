{{-- resources/views/mobile/entry.blade.php --}}
<x-mobile-layout title="Bienvenue">
    <div class="min-h-[calc(100vh-4rem)] flex flex-col items-center justify-center px-6 py-10 bg-gradient-to-b from-[#fff9f6] via-[#f7f4ec] to-[#f0ede3]">
        <div class="w-full max-w-md space-y-9">

            {{-- Intro --}}
            <div class="text-center space-y-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/90 border border-[#e4e8d5] text-[11px] font-medium text-gray-600 shadow-sm">
                    <i class="fas fa-mobile-alt mr-1.5 text-[10px] text-[#647a0b]"></i>
                    {{ __('Application mobile AromaMade PRO') }}
                </span>

                <div class="space-y-2">
                    <h1 class="text-[26px] font-extrabold text-gray-900 tracking-tight">
                        {{ __('Bienvenue 👋') }}
                    </h1>
                    <p class="text-sm text-gray-600 leading-relaxed px-2">
                        {{ __('Commencez par nous dire si vous cherchez un praticien ou si vous utilisez AromaMade PRO en tant que thérapeute.') }}
                    </p>
                </div>
            </div>

            <div class="space-y-5">

                {{-- Carte "Client" – principale, plus grande --}}
                <a href="{{ url('/') }}"
                   class="block w-full bg-white rounded-3xl shadow-md shadow-[#d8d4c6]/60 border border-[#e4e8d5]
                          px-5 py-5 flex flex-col gap-4 active:scale-[0.985] transition-transform duration-150">
                    <div class="flex items-start gap-4">
                        <div class="mt-0.5 inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-[#f2f7e8] shadow-inner">
                            <i class="fas fa-user text-sm text-[#647a0b]"></i>
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-semibold uppercase tracking-wide text-[#647a0b]">
                                    {{ __('Espace Client') }}
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#f2f7e8] text-[#4b5c07] font-medium">
                                    {{ __('Le plus fréquent') }}
                                </span>
                            </div>
                            <div class="text-[17px] font-semibold text-gray-900">
                                {{ __('Je cherche un praticien') }}
                            </div>
                            <p class="text-xs text-gray-600 mt-1 leading-snug">
                                {{ __('Prendre un rendez-vous, découvrir les événements bien-être, et trouver un praticien proche de chez vous ou en visio.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-1">
                        <div class="flex items-center gap-1.5 text-[11px] text-gray-500">
                            <i class="fas fa-map-marker-alt text-[10px] text-[#a78b5a]"></i>
                            <span>{{ __('Recherche par lieu, spécialité, ou type de soin.') }}</span>
                        </div>
                        <div class="inline-flex items-center text-[11px] font-semibold text-[#647a0b]">
                            {{ __('Continuer') }}
                            <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                        </div>
                    </div>
                </a>

                {{-- Séparateur subtil --}}
                <div class="flex items-center justify-center gap-2">
                    <span class="h-px w-8 bg-[#d8d4c6]"></span>
                    <span class="text-[10px] text-gray-400 uppercase tracking-[0.14em]">
                        {{ __('Ou thérapeute') }}
                    </span>
                    <span class="h-px w-8 bg-[#d8d4c6]"></span>
                </div>

                {{-- Carte "Praticien" – secondaire, plus compacte --}}
                <a href="{{ route('dashboard-pro') }}"
                   class="block w-full bg-[#647a0b] text-white rounded-2xl shadow-sm px-4 py-3.5
                          flex items-center justify-between gap-3 active:scale-[0.985] transition-transform duration-150">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 inline-flex items-center justify-center w-8 h-8 rounded-2xl bg-white/10">
                            <i class="fas fa-leaf text-xs text-white"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-[11px] font-semibold uppercase tracking-wide text-white/85">
                                    {{ __('Espace Praticien') }}
                                </span>
                            </div>
                            <div class="text-[14px] font-semibold">
                                {{ __('Je suis thérapeute / praticien(ne)') }}
                            </div>
                            <p class="text-[11px] text-white/85 mt-0.5 leading-snug">
                                {{ __('Accéder à mon agenda, mes fiches clients, ma facturation et mes événements.') }}
                            </p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-white/80 text-sm"></i>
                </a>
            </div>

            {{-- Info --}}
            <p class="text-[11px] text-gray-500 text-center leading-relaxed px-4">
                {{ __('Vous pourrez changer d’espace à tout moment depuis le menu de l’application. Rien n’est définitif, explorez librement.') }}
            </p>
        </div>
    </div>
</x-mobile-layout>
