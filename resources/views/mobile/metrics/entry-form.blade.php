@php
    $fullName = trim(($clientProfile->first_name ?? '') . ' ' . ($clientProfile->last_name ?? '')) ?: 'Client sans nom';
    $entryDate = old('entry_date', $metricEntry->entry_date ?? now()->toDateString());
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4">
            <a href="{{ route('mobile.metrics.show', [$clientProfile, $metric]) }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                {{ $metric->name }}
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
            <h2 class="text-sm font-semibold text-gray-900">Valeur</h2>

            <div class="mt-3 space-y-3">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Date</span>
                    <input type="date"
                           name="entry_date"
                           value="{{ $entryDate }}"
                           required
                           class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Valeur mesuree</span>
                    <input type="number"
                           name="value"
                           value="{{ old('value', $metricEntry->value) }}"
                           step="0.01"
                           required
                           inputmode="decimal"
                           class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                </label>
            </div>
        </section>

        @if($metricEntry->exists)
            <section class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 shadow-sm">
                <button type="submit"
                        form="deleteMetricEntryForm"
                        class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-white px-3 text-sm font-semibold text-red-700 ring-1 ring-red-200"
                        onclick="return confirm('Supprimer cette valeur ?');">
                    Supprimer cette valeur
                </button>
            </section>
        @endif

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ route('mobile.metrics.show', [$clientProfile, $metric]) }}"
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

    @if($metricEntry->exists)
        <form id="deleteMetricEntryForm"
              method="POST"
              action="{{ route('mobile.metrics.entries.destroy', [$clientProfile, $metric, $metricEntry]) }}">
            @csrf
            @method('DELETE')
        </form>
    @endif
</x-mobile-layout>
