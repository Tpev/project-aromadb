@php
    $selectedMode = old('mode', $mode ?? 'dans_le_cabinet');
    $boolValue = fn (string $field, bool $default = false) => old($field, (bool) ($product->{$field} ?? $default));
    $fieldValue = fn (string $field, mixed $default = null) => old($field, $product->{$field} ?? $default);
    $questionnaireEnabled = (bool) old('booking_questionnaire_enabled', (bool) ($product->booking_questionnaire_enabled ?? false));
    $selectedQuestionnaire = old('booking_questionnaire_id', $product->booking_questionnaire_id ?? null);
    $selectedFrequency = old('booking_questionnaire_frequency', $product->booking_questionnaire_frequency ?? \App\Models\Product::BOOKING_QUESTIONNAIRE_FIRST_TIME_ONLY);
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <a href="{{ $product->exists ? route('mobile.products.show', $product) : route('mobile.products.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                    <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                    Prestations
                </a>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Les champs essentiels pour gerer une prestation depuis le mobile.
                </p>
            </div>
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

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Informations</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Nom</span>
                        <input type="text"
                               name="name"
                               value="{{ $fieldValue('name') }}"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Description</span>
                        <textarea name="description"
                                  rows="3"
                                  class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ $fieldValue('description') }}</textarea>
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Prix EUR</span>
                            <input type="number"
                                   name="price"
                                   value="{{ $fieldValue('price', 0) }}"
                                   step="0.01"
                                   min="0"
                                   required
                                   inputmode="decimal"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">TVA %</span>
                            <input type="number"
                                   name="tax_rate"
                                   value="{{ $fieldValue('tax_rate', 0) }}"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   required
                                   inputmode="decimal"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Duree min</span>
                            <input type="number"
                                   name="duration"
                                   value="{{ $fieldValue('duration') }}"
                                   min="1"
                                   inputmode="numeric"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Max / jour</span>
                            <input type="number"
                                   name="max_per_day"
                                   value="{{ $fieldValue('max_per_day') }}"
                                   min="1"
                                   inputmode="numeric"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Mode</h2>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    @foreach([
                        'dans_le_cabinet' => ['label' => 'Cabinet', 'icon' => 'fa-map-marker-alt'],
                        'visio' => ['label' => 'Visio', 'icon' => 'fa-video'],
                        'adomicile' => ['label' => 'Domicile', 'icon' => 'fa-home'],
                        'en_entreprise' => ['label' => 'Entreprise', 'icon' => 'fa-building'],
                    ] as $value => $option)
                        <label class="flex h-12 items-center gap-2 rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 has-[:checked]:border-[#647a0b] has-[:checked]:bg-[#647a0b]/10 has-[:checked]:text-[#647a0b]">
                            <input type="radio"
                                   name="mode"
                                   value="{{ $value }}"
                                   class="h-4 w-4 shrink-0 border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ $selectedMode === $value ? 'checked' : '' }}>
                            <span class="flex min-w-0 items-center gap-2">
                                <i class="fas {{ $option['icon'] }} shrink-0 text-xs"></i>
                                {{ $option['label'] }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Publication</h2>
                <div class="mt-3 divide-y divide-gray-100">
                    @foreach([
                        'visible_in_portal' => ['label' => 'Visible sur le portail', 'default' => true],
                        'price_visible_in_portal' => ['label' => 'Afficher le prix', 'default' => true],
                        'can_be_booked_online' => ['label' => 'Reservation en ligne', 'default' => true],
                        'collect_payment' => ['label' => 'Paiement a la reservation', 'default' => false],
                        'requires_emargement' => ['label' => 'Emargement requis', 'default' => false],
                    ] as $field => $option)
                        <label class="flex items-center justify-between gap-4 py-3">
                            <span class="text-sm font-medium text-gray-700">{{ $option['label'] }}</span>
                            <span>
                                <input type="hidden" name="{{ $field }}" value="0">
                                <input type="checkbox"
                                       name="{{ $field }}"
                                       value="1"
                                       class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                       {{ $boolValue($field, $option['default']) ? 'checked' : '' }}>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Options avancees</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Ordre d affichage</span>
                        <input type="number"
                               name="display_order"
                               value="{{ $fieldValue('display_order', 0) }}"
                               min="0"
                               inputmode="numeric"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="flex items-center justify-between gap-4 rounded-lg bg-[#f7f8f1] px-3 py-3">
                        <span class="text-sm font-medium text-gray-700">Questionnaire automatique</span>
                        <span>
                            <input type="hidden" name="booking_questionnaire_enabled" value="0">
                            <input type="checkbox"
                                   name="booking_questionnaire_enabled"
                                   value="1"
                                   class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ $questionnaireEnabled ? 'checked' : '' }}>
                        </span>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Questionnaire</span>
                        <select name="booking_questionnaire_id"
                                class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            <option value="">Aucun</option>
                            @foreach($questionnaires as $questionnaire)
                                <option value="{{ $questionnaire->id }}" {{ (string) $selectedQuestionnaire === (string) $questionnaire->id ? 'selected' : '' }}>
                                    {{ $questionnaire->title }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Frequence</span>
                        <select name="booking_questionnaire_frequency"
                                class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            <option value="{{ \App\Models\Product::BOOKING_QUESTIONNAIRE_FIRST_TIME_ONLY }}" {{ $selectedFrequency === \App\Models\Product::BOOKING_QUESTIONNAIRE_FIRST_TIME_ONLY ? 'selected' : '' }}>
                                Premier rendez-vous seulement
                            </option>
                            <option value="{{ \App\Models\Product::BOOKING_QUESTIONNAIRE_EVERY_BOOKING }}" {{ $selectedFrequency === \App\Models\Product::BOOKING_QUESTIONNAIRE_EVERY_BOOKING ? 'selected' : '' }}>
                                A chaque reservation
                            </option>
                        </select>
                    </label>
                </div>
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ $product->exists ? route('mobile.products.show', $product) : route('mobile.products.index') }}"
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
