@php
    $fullName = trim(($clientProfile->first_name ?? '') . ' ' . ($clientProfile->last_name ?? '')) ?: 'Client sans nom';
    $backUrl = $metric->exists
        ? route('mobile.metrics.show', [$clientProfile, $metric])
        : route('mobile.metrics.index', $clientProfile);
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4">
            <a href="{{ $backUrl }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Suivi des mesures
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
            <p class="mt-1 break-words text-sm leading-snug text-gray-600">{{ $fullName }}</p>
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <div class="font-semibold">A corriger</div>
                <ul class="mt-1 list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-900">Parametres</h2>

            <div class="mt-3 space-y-3">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Nom de la mesure</span>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $metric->name) }}"
                           required
                           maxlength="255"
                           placeholder="Douleur, sommeil, poids..."
                           class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Objectif optionnel</span>
                    <input type="number"
                           name="goal"
                           value="{{ old('goal', $metric->goal) }}"
                           step="0.01"
                           inputmode="decimal"
                           placeholder="Ex. 7"
                           class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                </label>
            </div>
        </section>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ $backUrl }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-sm font-semibold text-gray-700">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] text-sm font-semibold text-white">
                    {{ $submitLabel }}
                </button>
            </div>
        </div>
    </form>
</x-mobile-layout>
