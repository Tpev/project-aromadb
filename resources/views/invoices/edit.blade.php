<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Modifier la facture') }} - #{{ $invoice->invoice_number }}
        </h2>
    </x-slot>

    <div class="container-fluid mt-5">
        <div class="details-container mx-auto p-4">
            @if ($errors->any())
                <div class="mb-4 text-red-500">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="invoiceEditForm" action="{{ route('invoices.update', $invoice) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Client, dates, notes --}}
                <div class="input-section">
                    <div class="details-box">
                        <label class="details-label" for="client_profile_id">{{ __('Client') }}</label>
                        <select id="client_profile_id" name="client_profile_id" class="form-control" required>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_profile_id', $invoice->client_profile_id) == $client->id ? 'selected' : '' }}>
                                    {{ $client->first_name }} {{ $client->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_profile_id')<p class="text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="details-box">
                        <label class="details-label" for="invoice_date">{{ __('Date de Facture') }}</label>
                        <input type="date" id="invoice_date" name="invoice_date" class="form-control"
                               value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required>
                        @error('invoice_date')<p class="text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="details-box">
                        <label class="details-label" for="due_date">{{ __('Date d\'échéance') }}</label>
                        <input type="date" id="due_date" name="due_date" class="form-control"
                               value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}">
                        @error('due_date')<p class="text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="details-box">
                        <label class="details-label" for="notes">{{ __('Notes') }}</label>
                        <textarea id="notes" name="notes" class="form-control">{{ old('notes', $invoice->notes) }}</textarea>
                        @error('notes')<p class="text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Articles --}}
                <div class="details-box">
                    <label class="details-label">{{ __('Articles de la facture') }}</label>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="invoice-items-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Produit/Inventaire') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Quantité') }}</th>
                                    <th>{{ __('P.U. HT (€)') }}</th>
                                    <th>{{ __('TVA (%)') }}</th>
                                    <th>{{ __('Remise') }}</th>
                                    <th>{{ __('Valeur remise') }}</th>
                                    <th>{{ __('Montant Taxe (€)') }}</th>
                                    <th>{{ __('Total TTC (€)') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $i => $item)
                                    @php
                                        // For existing custom lines, attempt to split "name — details"
                                        $rawDesc = old("items.$i.description", $item->description ?? '');
                                        $cName = $rawDesc;
                                        $cDetails = '';
                                        if (is_string($rawDesc)) {
                                            if (str_contains($rawDesc, ' — ')) {
                                                [$cName, $cDetails] = array_pad(explode(' — ', $rawDesc, 2), 2, '');
                                            } elseif (str_contains($rawDesc, ' - ')) {
                                                [$cName, $cDetails] = array_pad(explode(' - ', $rawDesc, 2), 2, '');
                                            }
                                        }
                                    @endphp

                                    <tr>
                                        <input type="hidden" name="items[{{ $i }}][type]" value="{{ $item->type }}">

                                        <td class="align-middle text-center">
                                            {{ $item->type === 'custom' ? __('Libre') : ucfirst($item->type) }}
                                        </td>

                                        <td>
                                            @if($item->type === 'product')
                                                <select name="items[{{ $i }}][product_id]" class="form-control product-select" onchange="updateItem(this)" data-preload="true">
                                                    <option value="">{{ __('Sélectionnez un produit') }}</option>
                                                    @foreach($products as $prod)
                                                        <option value="{{ $prod->id }}"
                                                                data-price="{{ $prod->price }}"
                                                                data-tax-rate="{{ $prod->tax_rate }}"
                                                            {{ $item->product_id == $prod->id ? 'selected' : '' }}>
                                                            {{ $prod->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="items[{{ $i }}][inventory_item_id]" value="">
                                            @elseif($item->type === 'inventory')
                                                <select name="items[{{ $i }}][inventory_item_id]" class="form-control inventory-select" onchange="updateInventoryItem(this)" data-preload="true">
                                                    <option value="">{{ __('Sélectionnez un article') }}</option>
                                                    @foreach($inventoryItems as $inv)
                                                        <option value="{{ $inv->id }}"
                                                                data-unit-type="{{ $inv->unit_type }}"
                                                                data-price-ml="{{ $inv->selling_price_per_ml }}"
                                                                data-price-unit="{{ $inv->selling_price }}"
                                                                data-tax-rate="{{ $inv->vat_rate_sale }}"
                                                            {{ $item->inventory_item_id == $inv->id ? 'selected' : '' }}>
                                                            {{ $inv->name }} ({{ $inv->unit_type }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="items[{{ $i }}][product_id]" value="">
                                            @else
                                                {{-- Custom: allow editing name --}}
                                                <input type="hidden" name="items[{{ $i }}][product_id]" value="">
                                                <input type="hidden" name="items[{{ $i }}][inventory_item_id]" value="">
                                                <input type="text"
                                                       class="form-control custom-name"
                                                       value="{{ old("items.$i.custom_name", trim($cName)) }}"
                                                       placeholder="Ex: Consultation, Atelier, Prestation…"
                                                       oninput="recomputeAllTotals()">
                                            @endif
                                        </td>

                                        <td>
                                            @if($item->type === 'custom')
                                                <input type="text"
                                                       class="form-control custom-details"
                                                       value="{{ old("items.$i.custom_details", trim($cDetails)) }}"
                                                       placeholder="Détails (optionnel)"
                                                       oninput="recomputeAllTotals()">

                                                {{-- Real saved field --}}
                                                <input type="hidden"
                                                       name="items[{{ $i }}][description]"
                                                       class="form-control description-input custom-description-hidden"
                                                       value="{{ $rawDesc }}">
                                            @else
                                                <input type="text" name="items[{{ $i }}][description]"
                                                       class="form-control description-input"
                                                       value="{{ $rawDesc }}"
                                                       oninput="recomputeAllTotals()">
                                            @endif
                                        </td>

                                        <td>
                                            <input type="number" name="items[{{ $i }}][quantity]"
                                                   class="form-control quantity-input"
                                                   min="0.01" step="0.01"
                                                   value="{{ old("items.$i.quantity", $item->quantity) }}"
                                                   oninput="recomputeAllTotals()">
                                        </td>

                                        <td>
                                            <input type="number" name="items[{{ $i }}][unit_price]"
                                                   class="form-control unit-price-input"
                                                   step="0.01" min="0"
                                                   value="{{ old("items.$i.unit_price", $item->unit_price) }}"
                                                   oninput="recomputeAllTotals()">
                                        </td>

                                        <td>
                                            <input type="number" name="items[{{ $i }}][tax_rate]"
                                                   class="form-control tax-rate-input"
                                                   step="0.01"
                                                   value="{{ old("items.$i.tax_rate", $item->tax_rate) }}"
                                                   @if($item->type !== 'custom') readonly @endif
                                                   oninput="recomputeAllTotals()">
                                        </td>

                                        <td>
                                            @php $ldt = old("items.$i.line_discount_type", $item->line_discount_type); @endphp
                                            <select name="items[{{ $i }}][line_discount_type]"
                                                    class="form-control line-discount-type"
                                                    onchange="recomputeAllTotals()">
                                                <option value="" {{ $ldt ? '' : 'selected' }}>—</option>
                                                <option value="percent" {{ $ldt === 'percent' ? 'selected' : '' }}>%</option>
                                                <option value="amount" {{ $ldt === 'amount' ? 'selected' : '' }}>€</option>
                                            </select>
                                        </td>

                                        <td>
                                            <input type="number" step="0.01" min="0"
                                                   name="items[{{ $i }}][line_discount_value]"
                                                   class="form-control line-discount-value"
                                                   value="{{ old("items.$i.line_discount_value", $item->line_discount_value) }}"
                                                   oninput="recomputeAllTotals()">
                                        </td>

                                        <td>
                                            <input type="number" name="items[{{ $i }}][tax_amount]"
                                                   class="form-control tax-amount-input"
                                                   readonly
                                                   value="{{ old("items.$i.tax_amount", $item->tax_amount) }}">
                                        </td>

                                        <td>
                                            <input type="number" name="items[{{ $i }}][total_price_with_tax]"
                                                   class="form-control total-price-with-tax-input readonly-field"
                                                   readonly
                                                   value="{{ old("items.$i.total_price_with_tax", $item->total_price_with_tax) }}">
                                        </td>

                                        <td>
                                            <button type="button" class="btn btn-danger" onclick="removeItem(this)">-</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 flex gap-2 flex-wrap">
                        <button type="button" class="btn-primary" onclick="addProductItem()">{{ __('Ajouter Produit') }}</button>
                        <button type="button" class="btn-primary" onclick="addInventoryItem()">{{ __('Ajouter Inventaire') }}</button>
                        <button type="button" class="btn-primary" onclick="addCustomItem()">{{ __('Ajouter une ligne libre') }}</button>
                    </div>
                </div>

                {{-- Global discount + totals --}}
                <div class="mt-4 p-4 bg-white rounded-lg border" style="border-color: rgba(100,122,11,0.25);">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="details-label">{{ __('Remise globale') }}</label>
                            @php $gdt = old('global_discount_type', $invoice->global_discount_type); @endphp
                            <select id="global_discount_type" name="global_discount_type" class="form-control" onchange="recomputeAllTotals()">
                                <option value="" {{ $gdt ? '' : 'selected' }}>{{ __('Aucune') }}</option>
                                <option value="percent" {{ $gdt === 'percent' ? 'selected' : '' }}>%</option>
                                <option value="amount" {{ $gdt === 'amount' ? 'selected' : '' }}>€</option>
                            </select>
                        </div>
                        <div>
                            <label class="details-label">{{ __('Valeur') }}</label>
                            <input id="global_discount_value" type="number" step="0.01" min="0" name="global_discount_value" class="form-control"
                                   value="{{ old('global_discount_value', $invoice->global_discount_value) }}" oninput="recomputeAllTotals()">
                        </div>
                        <div class="text-sm text-slate-500 flex items-end">
                            {{ __('La remise globale est répartie au prorata des lignes pour conserver une TVA correcte.') }}
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
                        <div class="flex justify-between"><span>{{ __('Sous-total HT') }}</span><strong><span id="ui_subtotal_ht">0.00</span> €</strong></div>
                        <div class="flex justify-between"><span>{{ __('Total remises ligne (HT)') }}</span><strong>-<span id="ui_line_discounts_ht">0.00</span> €</strong></div>
                        <div class="flex justify-between"><span>{{ __('Remise globale (HT)') }}</span><strong>-<span id="ui_global_discount_ht">0.00</span> €</strong></div>

                        <div class="flex justify-between"><span>{{ __('Total HT') }}</span><strong><span id="ui_total_ht">0.00</span> €</strong></div>
                        <div class="flex justify-between"><span>{{ __('Total TVA') }}</span><strong><span id="ui_total_tva">0.00</span> €</strong></div>
                        <div class="flex justify-between"><span>{{ __('Total TTC') }}</span><strong><span id="ui_total_ttc">0.00</span> €</strong></div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn-primary">{{ __('Mettre à jour la Facture') }}</button>
                    <a href="{{ route('invoices.show', $invoice) }}" class="btn-secondary">{{ __('Annuler') }}</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        let itemIndex = {{ $invoice->items->count() }};

        // utils
        function _num(v, fallback = 0) {
            const n = parseFloat(v);
            return Number.isFinite(n) ? n : fallback;
        }
        function _clamp(n, min, max) { return Math.max(min, Math.min(max, n)); }
        function _money(n) { return (Math.round((n + Number.EPSILON) * 100) / 100); }
        function escapeHtml(str) {
            return String(str).replace(/[&<>"']/g, s => ({
                '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
            }[s]));
        }

        function computeLineDiscountHt(baseHt, type, value) {
            if (!type || value === null || value === undefined || value === '') return 0;
            const v = _num(value, 0);
            let d = 0;
            if (type === 'percent') d = baseHt * (v / 100);
            else if (type === 'amount') d = v;
            return _money(_clamp(d, 0, baseHt));
        }
        function computeGlobalDiscountHt(subtotalHt, type, value) {
            if (!type || value === null || value === undefined || value === '' || subtotalHt <= 0) return 0;
            const v = _num(value, 0);
            let d = 0;
            if (type === 'percent') d = subtotalHt * (v / 100);
            else if (type === 'amount') d = v;
            return _money(_clamp(d, 0, subtotalHt));
        }

        // custom name/details -> description
        function syncCustomDescriptions() {
            const rows = Array.from(document.querySelectorAll('#invoice-items-table tbody tr'));
            rows.forEach(row => {
                const type = row.querySelector('input[name*="[type]"]')?.value;
                if (type !== 'custom') return;

                const nameEl = row.querySelector('.custom-name');
                const detailsEl = row.querySelector('.custom-details');
                const hiddenDescEl = row.querySelector('.custom-description-hidden');

                if (!hiddenDescEl) return;

                const name = (nameEl?.value || '').trim();
                const details = (detailsEl?.value || '').trim();

                if (name && details) hiddenDescEl.value = `${name} — ${details}`;
                else if (name) hiddenDescEl.value = name;
                else hiddenDescEl.value = details;
            });
        }

        function recomputeAllTotals() {
            syncCustomDescriptions();

            const rows = Array.from(document.querySelectorAll('#invoice-items-table tbody tr'));
            const lines = [];

            let subtotalHt = 0;
            let lineDiscountsHt = 0;

            for (const row of rows) {
                const qtyEl  = row.querySelector('.quantity-input');
                const unitEl = row.querySelector('.unit-price-input');
                const taxEl  = row.querySelector('.tax-rate-input');

                if (!qtyEl || !unitEl || !taxEl) continue;

                const qty = _num(qtyEl.value, 1);
                const unitHt = _num(unitEl.value, 0);
                const taxRate = _num(taxEl.value, 0);

                const baseHt = unitHt * qty;

                const discTypeEl = row.querySelector('.line-discount-type');
                const discValEl  = row.querySelector('.line-discount-value');

                const discType = discTypeEl ? discTypeEl.value : '';
                const discVal  = discValEl ? discValEl.value : '';

                const lineDiscHt = computeLineDiscountHt(baseHt, discType, discVal);
                const netHtAfterLine = _money(baseHt - lineDiscHt);

                subtotalHt += netHtAfterLine;
                lineDiscountsHt += lineDiscHt;

                lines.push({ row, taxRate, netHtAfterLine, globalAllocHt: 0 });
            }

            const gType = document.getElementById('global_discount_type')?.value || '';
            const gVal  = document.getElementById('global_discount_value')?.value || '';
            const globalDiscountHt = computeGlobalDiscountHt(subtotalHt, gType, gVal);

            let running = 0;
            for (let i = 0; i < lines.length; i++) {
                const l = lines[i];
                let alloc = 0;
                if (subtotalHt > 0 && globalDiscountHt > 0) {
                    if (i === lines.length - 1) alloc = _money(globalDiscountHt - running);
                    else alloc = _money(globalDiscountHt * (l.netHtAfterLine / subtotalHt));
                }
                running = _money(running + alloc);
                l.globalAllocHt = _money(_clamp(alloc, 0, l.netHtAfterLine));
            }

            let totalHt = 0, totalTva = 0, totalTtc = 0;

            for (const l of lines) {
                const netHtFinal = _money(l.netHtAfterLine - l.globalAllocHt);
                const taxAmt = _money(netHtFinal * (l.taxRate / 100));
                const ttc = _money(netHtFinal + taxAmt);

                const taxAmtEl = l.row.querySelector('.tax-amount-input');
                const ttcEl = l.row.querySelector('.total-price-with-tax-input');
                if (taxAmtEl) taxAmtEl.value = taxAmt.toFixed(2);
                if (ttcEl) ttcEl.value = ttc.toFixed(2);

                totalHt += netHtFinal;
                totalTva += taxAmt;
                totalTtc += ttc;
            }

            const setText = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = _money(val).toFixed(2);
            };

            setText('ui_subtotal_ht', subtotalHt);
            setText('ui_line_discounts_ht', lineDiscountsHt);
            setText('ui_global_discount_ht', globalDiscountHt);
            setText('ui_total_ht', totalHt);
            setText('ui_total_tva', totalTva);
            setText('ui_total_ttc', totalTtc);
        }

        function removeItem(btn) {
            const tr = btn?.closest?.('tr');
            if (tr) tr.remove();
            recomputeAllTotals();
        }

        function addProductItem() {
            const tbody = document.querySelector('#invoice-items-table tbody');
            const idx = itemIndex++;
            const row = document.createElement('tr');

            row.innerHTML = `
                <input type="hidden" name="items[${idx}][type]" value="product">

                <td class="align-middle text-center">{{ __('Product') }}</td>

                <td>
                    <select name="items[${idx}][product_id]" class="form-control product-select" onchange="updateItem(this)">
                        <option value="">{{ __('Sélectionnez un produit') }}</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}"
                                    data-price="{{ $prod->price }}"
                                    data-tax-rate="{{ $prod->tax_rate }}">
                                {{ $prod->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="">
                </td>

                <td><input type="text" name="items[${idx}][description]" class="form-control description-input" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][quantity]" class="form-control quantity-input" value="1" min="0.01" step="0.01" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][unit_price]" class="form-control unit-price-input" step="0.01" min="0" value="0.00" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][tax_rate]" class="form-control tax-rate-input" step="0.01" readonly value="0.00"></td>

                <td>
                    <select name="items[${idx}][line_discount_type]" class="form-control line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="items[${idx}][line_discount_value]" class="form-control line-discount-value" value="" oninput="recomputeAllTotals()">
                </td>

                <td><input type="number" name="items[${idx}][tax_amount]" class="form-control tax-amount-input" readonly value="0.00"></td>
                <td><input type="number" name="items[${idx}][total_price_with_tax]" class="form-control total-price-with-tax-input readonly-field" readonly value="0.00"></td>
                <td><button type="button" class="btn btn-danger" onclick="removeItem(this)">-</button></td>
            `;

            tbody.append(row);
            recomputeAllTotals();
        }

        function addInventoryItem() {
            const tbody = document.querySelector('#invoice-items-table tbody');
            const idx = itemIndex++;
            const row = document.createElement('tr');

            row.innerHTML = `
                <input type="hidden" name="items[${idx}][type]" value="inventory">

                <td class="align-middle text-center">{{ __('Inventory') }}</td>

                <td>
                    <select name="items[${idx}][inventory_item_id]" class="form-control inventory-select" onchange="updateInventoryItem(this)">
                        <option value="">{{ __('Sélectionnez un article') }}</option>
                        @foreach($inventoryItems as $inv)
                            <option value="{{ $inv->id }}"
                                    data-unit-type="{{ $inv->unit_type }}"
                                    data-price-ml="{{ $inv->selling_price_per_ml }}"
                                    data-price-unit="{{ $inv->selling_price }}"
                                    data-tax-rate="{{ $inv->vat_rate_sale }}">
                                {{ $inv->name }} ({{ $inv->unit_type }})
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="items[${idx}][product_id]" value="">
                </td>

                <td><input type="text" name="items[${idx}][description]" class="form-control description-input" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][quantity]" class="form-control quantity-input" value="1" min="0.01" step="0.01" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][unit_price]" class="form-control unit-price-input" step="0.01" min="0" readonly value="0.00"></td>
                <td><input type="number" name="items[${idx}][tax_rate]" class="form-control tax-rate-input" step="0.01" readonly value="0.00"></td>

                <td>
                    <select name="items[${idx}][line_discount_type]" class="form-control line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="items[${idx}][line_discount_value]" class="form-control line-discount-value" value="" oninput="recomputeAllTotals()">
                </td>

                <td><input type="number" name="items[${idx}][tax_amount]" class="form-control tax-amount-input" readonly value="0.00"></td>
                <td><input type="number" name="items[${idx}][total_price_with_tax]" class="form-control total-price-with-tax-input readonly-field" readonly value="0.00"></td>
                <td><button type="button" class="btn btn-danger" onclick="removeItem(this)">-</button></td>
            `;

            tbody.append(row);
            recomputeAllTotals();
        }

        function addCustomItem() {
            const tbody = document.querySelector('#invoice-items-table tbody');
            const idx = itemIndex++;
            const row = document.createElement('tr');

            row.innerHTML = `
                <input type="hidden" name="items[${idx}][type]" value="custom">

                <td class="align-middle text-center">{{ __('Libre') }}</td>

                <td>
                    <input type="hidden" name="items[${idx}][product_id]" value="">
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="">
                    <input type="text" class="form-control custom-name" placeholder="Ex: Consultation, Atelier…" oninput="recomputeAllTotals()">
                </td>

                <td>
                    <input type="text" class="form-control custom-details" placeholder="Détails (optionnel)" oninput="recomputeAllTotals()">
                    <input type="hidden" name="items[${idx}][description]" class="custom-description-hidden" value="">
                </td>

                <td><input type="number" name="items[${idx}][quantity]" class="form-control quantity-input" value="1" min="0.01" step="0.01" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][unit_price]" class="form-control unit-price-input" value="0.00" step="0.01" min="0" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][tax_rate]" class="form-control tax-rate-input" value="0.00" step="0.01" min="0" oninput="recomputeAllTotals()"></td>

                <td>
                    <select name="items[${idx}][line_discount_type]" class="form-control line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="items[${idx}][line_discount_value]" class="form-control line-discount-value" value="" oninput="recomputeAllTotals()">
                </td>

                <td><input type="number" name="items[${idx}][tax_amount]" class="form-control tax-amount-input" readonly value="0.00"></td>
                <td><input type="number" name="items[${idx}][total_price_with_tax]" class="form-control total-price-with-tax-input readonly-field" readonly value="0.00"></td>
                <td><button type="button" class="btn btn-danger" onclick="removeItem(this)">-</button></td>
            `;

            tbody.append(row);
            recomputeAllTotals();
        }

        function updateItem(el) {
            const row = el.closest('tr');
            if (!row) return;

            if (el.classList.contains('product-select')) {
                const opt = el.options[el.selectedIndex];
                const price = _num(opt?.dataset?.price, 0);
                const taxRate = _num(opt?.dataset?.taxRate, 0);

                const unitEl = row.querySelector('.unit-price-input');
                const taxEl  = row.querySelector('.tax-rate-input');

                if (unitEl) unitEl.value = price.toFixed(2);
                if (taxEl)  taxEl.value  = taxRate.toFixed(2);
            }

            recomputeAllTotals();
        }

        function updateInventoryItem(el) {
            const row = el.closest('tr');
            const sel = row.querySelector('.inventory-select');
            const opt = sel?.options?.[sel.selectedIndex];
            if (!opt) { recomputeAllTotals(); return; }

            const unitType = opt.dataset.unitType || 'unit';
            const priceTtc = unitType === 'unit'
                ? _num(opt.dataset.priceUnit, 0)
                : _num(opt.dataset.priceMl, 0);

            const taxRate = _num(opt.dataset.taxRate, 0);
            const unitHt = taxRate > 0 ? (priceTtc / (1 + taxRate / 100)) : priceTtc;

            const unitEl = row.querySelector('.unit-price-input');
            const taxEl  = row.querySelector('.tax-rate-input');
            if (unitEl) unitEl.value = unitHt.toFixed(2);
            if (taxEl)  taxEl.value  = taxRate.toFixed(2);

            recomputeAllTotals();
        }

        window.onload = () => {
            document.querySelectorAll('.product-select[data-preload="true"]').forEach(sel => updateItem(sel));
            document.querySelectorAll('.inventory-select[data-preload="true"]').forEach(sel => updateInventoryItem(sel));
            recomputeAllTotals();

            const form = document.getElementById('invoiceEditForm');
            if (form) {
                form.addEventListener('submit', () => {
                    syncCustomDescriptions();
                });
            }
        };
    </script>

    <style>
        .container-fluid { max-width: 1200px; }
        .input-section { max-width: 600px; margin-bottom: 30px; }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .details-title {
            font-size: 2rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        .details-box { margin-bottom: 15px; }

        .details-label {
            font-weight: bold;
            color: #647a0b;
            display: block;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }
        .btn-primary:hover { background-color: #854f38; }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            padding: 10px 20px;
            border: 1px solid #854f38;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-secondary:hover { background-color: #854f38; color: #fff; }

        .text-red-500 { color: #e3342f; font-size: 0.875rem; }

        #invoice-items-table { width: 100%; margin-bottom: 15px; table-layout: auto; }
        #invoice-items-table th, #invoice-items-table td { padding: 8px; text-align: left; }
        #invoice-items-table th { background-color: #647a0b; color: #fff; white-space: nowrap; }
        #invoice-items-table td { border-bottom: 1px solid #ccc; }
        #invoice-items-table td input, #invoice-items-table td select { width: 100%; }

        .btn-danger {
            background-color: #e3342f;
            color: #fff;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-danger:hover { background-color: #cc1f1a; }

        .readonly-field { background-color: #e9ecef; cursor: not-allowed; }

        @media (max-width: 768px) {
            .details-title { font-size: 1.5rem; }
            .btn-primary, .btn-secondary { width: 100%; text-align: center; margin-bottom: 10px; }
            #invoice-items-table th, #invoice-items-table td { padding: 6px; }
        }
    </style>
</x-app-layout>
