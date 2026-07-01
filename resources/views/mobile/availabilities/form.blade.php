@php
    $selectedProducts = collect((array) old('products', $selectedProducts ?? []))
        ->map(fn ($id) => (string) $id)
        ->all();
    $selectedLocation = old('practice_location_id', $availability->practice_location_id);
    $appliesToAll = (bool) old('applies_to_all', (bool) ($availability->applies_to_all ?? true));
    $dayLabels = [
        0 => 'Lundi',
        1 => 'Mardi',
        2 => 'Mercredi',
        3 => 'Jeudi',
        4 => 'Vendredi',
        5 => 'Samedi',
        6 => 'Dimanche',
    ];
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4">
            <a href="{{ route('mobile.availabilities.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Disponibilites
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm leading-snug text-gray-600">
                Creneau hebdomadaire pour vos reservations.
            </p>
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

        @if($products->isEmpty())
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                Ajoutez au moins une prestation avant de cibler ce creneau.
            </div>
        @endif

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Jour et horaire</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Jour</span>
                        <select name="day_of_week"
                                required
                                class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            @foreach($dayLabels as $value => $label)
                                <option value="{{ $value }}" @selected((string) old('day_of_week', $availability->day_of_week) === (string) $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Debut</span>
                            <input type="time"
                                   name="start_time"
                                   value="{{ old('start_time', substr((string) $availability->start_time, 0, 5)) }}"
                                   required
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Fin</span>
                            <input type="time"
                                   name="end_time"
                                   value="{{ old('end_time', substr((string) $availability->end_time, 0, 5)) }}"
                                   required
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Lieu</h2>

                <label class="mt-3 block">
                    <span class="text-sm font-medium text-gray-700">Cabinet</span>
                    <select name="practice_location_id"
                            class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        <option value="">Aucun lieu specifique</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ (string) $selectedLocation === (string) $location->id ? 'selected' : '' }}>
                                {{ $location->label ?: 'Cabinet' }}
                                @if($location->city)
                                    - {{ $location->city }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </label>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Prestations</h2>

                <label class="mt-3 flex items-center justify-between gap-4 rounded-lg bg-[#f7f8f1] px-3 py-3">
                    <span class="text-sm font-medium text-gray-700">Toutes les prestations</span>
                    <span>
                        <input type="hidden" name="applies_to_all" value="0">
                        <input type="checkbox"
                               name="applies_to_all"
                               id="mobileAvailabilityAll"
                               value="1"
                               class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                               {{ $appliesToAll ? 'checked' : '' }}>
                    </span>
                </label>

                <div id="mobileAvailabilityProducts" class="mt-3 space-y-2">
                    @foreach($products as $product)
                        <label class="flex min-h-11 items-center gap-3 rounded-lg border border-[#e4e8d5] bg-white px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox"
                                   name="products[]"
                                   value="{{ $product->id }}"
                                   class="h-4 w-4 shrink-0 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ in_array((string) $product->id, $selectedProducts, true) ? 'checked' : '' }}>
                            <span class="min-w-0">
                                <span class="block truncate font-medium">{{ $product->name }}</span>
                                <span class="block text-xs text-gray-500">{{ $product->getConsultationModes() }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ route('mobile.availabilities.index') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-sm font-semibold text-gray-700">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] text-sm font-semibold text-white disabled:bg-gray-300"
                        @disabled($products->isEmpty() && !$appliesToAll)>
                    {{ $submitLabel }}
                </button>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('mobileAvailabilityAll');
            const productSection = document.getElementById('mobileAvailabilityProducts');

            const syncProducts = () => {
                productSection?.classList.toggle('hidden', toggle?.checked ?? false);
            };

            toggle?.addEventListener('change', syncProducts);
            syncProducts();
        });
    </script>
</x-mobile-layout>
