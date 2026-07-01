@php
    $fieldValue = fn (string $field, mixed $default = null) => old($field, $inventoryItem->{$field} ?? $default);
    $unitType = old('unit_type', $inventoryItem->unit_type ?? 'unit');
    $quantityInStock = $fieldValue('quantity_in_stock', $unitType === 'unit' ? 0 : 1);
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <a href="{{ route('mobile.inventory.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                    <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                    Stock
                </a>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Ajoutez un article, un flacon ou une matiere consommable.
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
                        <span class="text-sm font-medium text-gray-700">Reference</span>
                        <input type="text"
                               name="reference"
                               value="{{ $fieldValue('reference') }}"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Marque</span>
                            <input type="text"
                                   name="brand"
                                   value="{{ $fieldValue('brand') }}"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Type</span>
                            <select name="unit_type"
                                    data-unit-type
                                    class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                                <option value="unit" {{ $unitType === 'unit' ? 'selected' : '' }}>Unite</option>
                                <option value="ml" {{ $unitType === 'ml' ? 'selected' : '' }}>Millilitres</option>
                                <option value="drop" {{ $unitType === 'drop' ? 'selected' : '' }}>Gouttes</option>
                                <option value="gramme" {{ $unitType === 'gramme' ? 'selected' : '' }}>Grammes</option>
                            </select>
                        </label>
                    </div>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Description</span>
                        <textarea name="description"
                                  rows="3"
                                  class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ $fieldValue('description') }}</textarea>
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Stock</h2>

                <div class="mt-3 space-y-3">
                    <div data-type-section="unit">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Quantite en stock</span>
                            <input type="number"
                                   name="quantity_in_stock"
                                   value="{{ $quantityInStock }}"
                                   min="0"
                                   step="1"
                                   inputmode="numeric"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>

                    <div data-type-section="liquid" class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Contenant</span>
                                <input type="number"
                                       name="quantity_per_unit"
                                       value="{{ $fieldValue('quantity_per_unit') }}"
                                       step="0.01"
                                       min="0"
                                       inputmode="decimal"
                                       placeholder="Ex: 100"
                                       class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Restant</span>
                                <input type="number"
                                       name="quantity_remaining"
                                       value="{{ $fieldValue('quantity_remaining') }}"
                                       step="0.01"
                                       min="0"
                                       inputmode="decimal"
                                       placeholder="Ex: 80"
                                       class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            </label>
                        </div>

                        <div data-type-section="drop">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Gouttes par ml</span>
                                <input type="number"
                                       name="drop_to_ml_ratio"
                                       value="{{ $fieldValue('drop_to_ml_ratio', 20) }}"
                                       step="1"
                                       min="1"
                                       inputmode="numeric"
                                       class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            </label>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Prix</h2>

                <div class="mt-3 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Achat EUR</span>
                            <input type="number"
                                   name="price"
                                   value="{{ $fieldValue('price', 0) }}"
                                   step="0.01"
                                   min="0"
                                   inputmode="decimal"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Vente EUR</span>
                            <input type="number"
                                   name="selling_price"
                                   value="{{ $fieldValue('selling_price', 0) }}"
                                   step="0.01"
                                   min="0"
                                   inputmode="decimal"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>

                    <div data-type-section="liquid" class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Achat / unite</span>
                            <input type="number"
                                   name="price_per_ml"
                                   value="{{ $fieldValue('price_per_ml') }}"
                                   step="0.0001"
                                   min="0"
                                   inputmode="decimal"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Vente / unite</span>
                            <input type="number"
                                   name="selling_price_per_ml"
                                   value="{{ $fieldValue('selling_price_per_ml') }}"
                                   step="0.0001"
                                   min="0"
                                   inputmode="decimal"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">TVA achat %</span>
                            <input type="number"
                                   name="vat_rate_purchase"
                                   value="{{ $fieldValue('vat_rate_purchase', 0) }}"
                                   step="0.01"
                                   min="0"
                                   inputmode="decimal"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">TVA vente %</span>
                            <input type="number"
                                   name="vat_rate_sale"
                                   value="{{ $fieldValue('vat_rate_sale', 0) }}"
                                   step="0.01"
                                   min="0"
                                   inputmode="decimal"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>
                </div>
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ route('mobile.inventory.index') }}"
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

    @push('scripts')
        <script>
            (() => {
                const select = document.querySelector('[data-unit-type]');
                const sections = document.querySelectorAll('[data-type-section]');

                if (!select) {
                    return;
                }

                const syncSections = () => {
                    const value = select.value;

                    sections.forEach((section) => {
                        const type = section.dataset.typeSection;
                        const visible = type === value
                            || (type === 'liquid' && ['ml', 'drop', 'gramme'].includes(value));

                        section.classList.toggle('hidden', !visible);
                    });
                };

                select.addEventListener('change', syncSections);
                syncSections();
            })();
        </script>
    @endpush
</x-mobile-layout>
