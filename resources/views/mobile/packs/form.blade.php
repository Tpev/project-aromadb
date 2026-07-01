@php
    $fieldValue = fn (string $field, mixed $default = null) => old($field, $pack->{$field} ?? $default);
    $boolValue = fn (string $field, bool $default = false) => filter_var(old($field, $pack->{$field} ?? $default), FILTER_VALIDATE_BOOLEAN);
    $selectedInstallments = array_map(
        'intval',
        old('allowed_installments', is_array($pack->allowed_installments ?? null) ? $pack->allowed_installments : [])
    );
    $initialItems = collect(old(
        'items',
        $pack->exists
            ? $pack->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ])->values()->all()
            : [['product_id' => '', 'quantity' => 1]]
    ))->values();
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4">
            <a href="{{ $pack->exists ? route('mobile.packs.show', $pack) : route('mobile.packs.index') }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Packs
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm leading-snug text-gray-600">
                Creez un forfait de prestations avec credits a suivre.
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

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Informations</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Nom du pack</span>
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
                            <span class="text-sm font-medium text-gray-700">Prix HT</span>
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
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Publication</h2>

                <div class="mt-3 divide-y divide-gray-100">
                    @foreach([
                        'is_active' => ['label' => 'Pack actif', 'default' => true],
                        'visible_in_portal' => ['label' => 'Visible sur le portail', 'default' => true],
                        'price_visible_in_portal' => ['label' => 'Afficher le prix', 'default' => true],
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
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Contenu du pack</h2>
                        <p class="mt-1 text-xs leading-snug text-gray-500">Une ligne = une prestation et ses credits.</p>
                    </div>
                    <button type="button"
                            id="addPackItem"
                            class="inline-flex h-9 shrink-0 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-xs font-semibold text-white">
                        Ajouter
                    </button>
                </div>

                @if($products->isEmpty())
                    <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                        Aucune prestation disponible. Creez d abord une prestation.
                    </div>
                @endif

                <div id="packItems" class="mt-3 space-y-2"></div>

                <template id="packItemTemplate">
                    <div class="pack-item rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                        <div class="grid grid-cols-[1fr_88px] gap-2">
                            <label class="block min-w-0">
                                <span class="text-sm font-medium text-gray-700">Prestation</span>
                                <select data-field="product_id"
                                        required
                                        class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                                    <option value="">Choisir</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Credits</span>
                                <input type="number"
                                       data-field="quantity"
                                       value="1"
                                       min="1"
                                       max="999"
                                       required
                                       inputmode="numeric"
                                       class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            </label>
                        </div>

                        <button type="button"
                                data-remove-pack-item
                                class="mt-2 inline-flex h-9 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-xs font-semibold text-red-600">
                            Retirer
                        </button>
                    </div>
                </template>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <label class="flex items-start justify-between gap-4 rounded-lg bg-[#f7f8f1] px-3 py-3">
                    <span>
                        <span class="block text-sm font-semibold text-gray-900">Paiement en plusieurs fois</span>
                        <span class="mt-0.5 block text-xs leading-snug text-gray-500">Autoriser des echeances client sur le portail.</span>
                    </span>
                    <span>
                        <input type="hidden" name="installments_enabled" value="0">
                        <input type="checkbox"
                               name="installments_enabled"
                               value="1"
                               class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                               {{ $boolValue('installments_enabled', false) ? 'checked' : '' }}>
                    </span>
                </label>

                <div class="mt-3 grid grid-cols-4 gap-2">
                    @for($i = 2; $i <= 12; $i++)
                        <label class="inline-flex h-10 items-center justify-center gap-1 rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700 has-[:checked]:border-[#647a0b] has-[:checked]:bg-[#647a0b]/10 has-[:checked]:text-[#647a0b]">
                            <input type="checkbox"
                                   name="allowed_installments[]"
                                   value="{{ $i }}"
                                   class="h-3.5 w-3.5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ in_array($i, $selectedInstallments, true) ? 'checked' : '' }}>
                            {{ $i }}x
                        </label>
                    @endfor
                </div>
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ $pack->exists ? route('mobile.packs.show', $pack) : route('mobile.packs.index') }}"
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
                const container = document.getElementById('packItems');
                const template = document.getElementById('packItemTemplate');
                const addButton = document.getElementById('addPackItem');
                const initialItems = @json($initialItems);

                if (!container || !template || !addButton) {
                    return;
                }

                const renumber = () => {
                    Array.from(container.querySelectorAll('.pack-item')).forEach((row, index) => {
                        row.querySelectorAll('[data-field]').forEach((field) => {
                            field.name = `items[${index}][${field.dataset.field}]`;
                        });
                    });
                };

                const addRow = (item = {}) => {
                    const fragment = template.content.cloneNode(true);
                    const row = fragment.querySelector('.pack-item');
                    const product = row.querySelector('[data-field="product_id"]');
                    const quantity = row.querySelector('[data-field="quantity"]');

                    product.value = item.product_id || '';
                    quantity.value = item.quantity || 1;

                    row.querySelector('[data-remove-pack-item]').addEventListener('click', () => {
                        row.remove();
                        if (!container.querySelector('.pack-item')) {
                            addRow();
                        }
                        renumber();
                    });

                    container.appendChild(fragment);
                    renumber();
                };

                addButton.addEventListener('click', () => addRow());

                if (initialItems.length) {
                    initialItems.forEach((item) => addRow(item));
                } else {
                    addRow();
                }
            })();
        </script>
    @endpush
</x-mobile-layout>
