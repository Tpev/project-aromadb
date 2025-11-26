{{-- resources/views/mobile/entry.blade.php --}}
<x-mobile-layout title="Bienvenue">
    <div class="min-h-[calc(100vh-4rem)] flex flex-col items-center justify-center px-6 py-10 bg-gradient-to-b from-[#fff9f6] to-[#f4f2ea]">
        <div class="w-full max-w-md space-y-8">

            {{-- Intro --}}
            <div class="text-center space-y-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/80 border border-[#e4e8d5] text-[11px] font-medium text-gray-600">
                    <i class="fas fa-mobile-alt mr-1.5 text-[10px] text-[#647a0b]"></i>
                    {{ __('Version mobile de AromaMade PRO') }}
                </span>

                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-1">
                        {{ __('Bienvenue 👋') }}
                    </h1>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        {{ __('Pour vous accompagner au mieux, dites-nous si vous êtes en train de chercher un praticien ou si vous êtes praticien(ne).') }}
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                {{-- Carte "Client" --}}
                <a href="{{ url('/') }}"
                   class="block w-full bg-white/95 border border-[#e4e8d5] rounded-2xl shadow-sm px-5 py-4
                          flex items-center justify-between gap-3 hover:shadow-md active:scale-[0.99] transition">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#f2f5e6]">
                            <i class="fas fa-user text-xs text-[#647a0b]"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-xs font-semibold uppercase tracking-wide text-[#647a0b]">
                                    {{ __('Espace Client') }}
                                </span>
                            </div>
                            <div class="text-base font-semibold text-gray-900">
                                {{ __('Je cherche un praticien') }}
                            </div>
                            <p class="text-xs text-gray-600 mt-1 leading-snug">
                                {{ __('Prendre un rendez-vous, voir les événements bien-être et découvrir les praticiens proches de chez moi.') }}
                            </p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-300 text-sm"></i>
                </a>

                {{-- Carte "Praticien" (mise en avant) --}}
                <a href="{{ route('dashboard-pro') }}"
                   class="block w-full bg-[#647a0b] text-white rounded-2xl shadow px-5 py-4
                          flex items-center justify-between gap-3 hover:shadow-lg active:scale-[0.99] transition">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 inline-flex items-center justify-center w-8 h-8 rounded-full bg-white/10">
                            <i class="fas fa-leaf text-xs text-white"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-xs font-semibold uppercase tracking-wide text-white/80">
                                    {{ __('Espace Praticien') }}
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-white/15 text-white/90">
                                    {{ __('Recommandé pour les pros') }}
                                </span>
                            </div>
                            <div class="text-base font-semibold">
                                {{ __('Je suis praticien(ne)') }}
                            </div>
                            <p class="text-xs text-white/85 mt-1 leading-snug">
                                {{ __('Accéder à mon agenda, mes fiches clients, mes factures, mes événements et tous mes outils AromaMade PRO.') }}
                            </p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-white/80 text-sm"></i>
                </a>
            </div>

            {{-- Info --}}
            <p class="text-[11px] text-gray-500 text-center leading-relaxed">
                {{ __('Vous pourrez changer d’espace à tout moment depuis le menu de l’application. Pas de panique, rien n’est définitif 😊') }}
            </p>
        </div>
    </div>
</x-mobile-layout>
