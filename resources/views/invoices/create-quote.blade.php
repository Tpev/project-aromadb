<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight" style="color:#647a0b;">
            {{ __('Créer un devis') }}
        </h2>
    </x-slot>

    <div class="container-fluid mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Créer un devis') }}</h1>

            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                // Support param ?client_profile_id=...
                $selectedClientId = old('client_profile_id', request('client_profile_id'));
            @endphp

            <form id="quoteCreateForm" action="{{ route('invoices.storeQuote') }}" method="POST">
                @csrf

                <div class="details-box">
                    <label class="details-label" for="client_profile_id">{{ __('Client') }}</label>
                    <select id="client_profile_id" name="client_profile_id" class="form-control" required>
                        <option value="">{{ __('Sélectionnez un client') }}</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ (string)$selectedClientId === (string)$c->id ? 'selected' : '' }}>
                                {{ $c->first_name }} {{ $c->last_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_profile_id')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="details-box">
                    <label class="details-label" for="quote_date">{{ __('Date du devis') }}</label>
                    <input id="quote_date" type="date" name="quote_date" class="form-control"
                           value="{{ old('quote_date', now()->format('Y-m-d')) }}" required>
                    @error('quote_date')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="details-box">
                    <label class="details-label" for="valid_until">{{ __('Valable jusqu’au') }}</label>
                    <input id="valid_until" type="date" name="valid_until" class="form-control"
                           value="{{ old('valid_until') }}">
                    @error('valid_until')<p class="text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="details-box">
                    <label class="details-label" for="notes">{{ __('Notes') }}</label>
                    <textarea id="notes" name="notes" class="form-control">{{ old('notes') }}</textarea>
                </div>

                {{-- Remise globale --}}
                <div class="details-box">
                    <label class="details-label">{{ __('Remise globale') }}</label>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        @php $gType = old('global_discount_type'); @endphp
                        <select id="global_discount_type" name="global_discount_type" class="form-control"
                                style="max-width:180px;" onchange="recomputeAllTotals()">
                            <option value="" {{ $gType==='' || is_null($gType) ? 'selected':'' }}>{{ __('Aucune') }}</option>
                            <option value="percent" {{ $gType==='percent' ? 'selected':'' }}>%</option>
                            <option value="amount" {{ $gType==='amount' ? 'selected':'' }}>€</option>
                        </select>

                        <input id="global_discount_value"
                               type="number"
                               name="global_discount_value"
                               class="form-control"
                               style="max-width:220px;"
                               step="0.01"
                               min="0"
                               value="{{ old('global_discount_value') }}"
                               placeholder="{{ __('Valeur') }}"
                               oninput="recomputeAllTotals()">

                        <div class="text-muted" style="align-self:center; font-size:0.9rem;">
                            {{ __('Appliquée sur le total HT après remises lignes, TVA recalculée au prorata.') }}
                        </div>
                    </div>
                </div>

                {{-- Tableau des articles --}}
                <div class="details-box">
                    <label class="details-label">{{ __('Articles du devis') }}</label>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="quote-items-table">
                            <thead>
                                <tr>
                                    <th style="width:70px">{{ __('Type') }}</th>
                                    <th style="min-width:220px">{{ __('Produit / Article') }}</th>
                                    <th style="min-width:220px">{{ __('Description') }}</th>
                                    <th style="width:90px">{{ __('Qté') }}</th>
                                    <th style="width:120px">{{ __('P.U. HT (€)') }}</th>
                                    <th style="width:90px">{{ __('TVA (%)') }}</th>

                                    <th style="width:120px">{{ __('Remise') }}</th>
                                    <th style="width:110px">{{ __('Valeur') }}</th>
                                    <th style="width:120px">{{ __('Remise HT (€)') }}</th>

                                    <th style="width:120px">{{ __('Total HT (€)') }}</th>
                                    <th style="width:120px">{{ __('Montant TVA (€)') }}</th>
                                    <th style="width:120px">{{ __('Total TTC (€)') }}</th>
                                    <th style="width:80px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="flex gap-2 mt-2 flex-wrap">
                        <button type="button" class="btn-primary" onclick="addProductItem()">
                            {{ __('Ajouter une prestation') }}
                        </button>
                        <button type="button" class="btn-primary" onclick="openInventoryModal()">
                            {{ __('Ajouter depuis l\'inventaire') }}
                        </button>
                        <button type="button" class="btn-primary" onclick="addCustomItem()">
                            {{ __('Ajouter une ligne libre') }}
                        </button>
                    </div>

                    {{-- Totaux dynamiques --}}
                    <div class="totals-box mt-4">
                        <div class="totals-row"><span>{{ __('Sous-total HT (avant remises)') }}</span><strong id="ui_subtotal_ht">0,00 €</strong></div>
                        <div class="totals-row"><span>{{ __('Remises lignes (HT)') }}</span><strong id="ui_line_discounts_ht">0,00 €</strong></div>
                        <div class="totals-row"><span>{{ __('Sous-total HT (après remises lignes)') }}</span><strong id="ui_subtotal_after_lines_ht">0,00 €</strong></div>
                        <div class="totals-row"><span>{{ __('Remise globale (HT)') }}</span><strong id="ui_global_discount_ht">0,00 €</strong></div>
                        <div class="totals-row totals-row-total"><span>{{ __('Total HT') }}</span><strong id="ui_total_ht">0,00 €</strong></div>
                        <div class="totals-row"><span>{{ __('Total TVA') }}</span><strong id="ui_total_tva">0,00 €</strong></div>
                        <div class="totals-row totals-row-total"><span>{{ __('Total TTC') }}</span><strong id="ui_total_ttc">0,00 €</strong></div>
                    </div>
                </div>

                <button type="submit" class="btn-primary mt-4">{{ __('Créer le Devis') }}</button>
                <a href="{{ route('invoices.index', ['type'=>'quote']) }}" class="btn-secondary mt-4">
                    {{ __('Retour à la liste') }}
                </a>
            </form>
        </div>
    </div>

    {{-- Modal inventaire --}}
    <div id="inventoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-xl shadow-lg">
            <h2 class="text-xl font-semibold mb-4 text-[#647a0b]">
                {{ __('Ajouter un article depuis l’inventaire') }}
            </h2>

            <div class="mb-4">
                <label class="block font-semibold">{{ __('Article') }}</label>
                <select id="inventory_item_id" class="form-control">
                    <option value="">{{ __('Sélectionnez un article') }}</option>
                    @foreach($inventoryItems as $item)
                        <option
                            value="{{ $item->id }}"
                            data-name="{{ $item->name }}"
                            data-unit-type="{{ $item->unit_type }}"
                            data-ttc-unit="{{ $item->selling_price }}"
                            data-ttc-per-ml="{{ $item->selling_price_per_ml }}"
                            data-tax="{{ $item->vat_rate_sale }}"
                        >
                            {{ $item->name }} ({{ $item->quantity_in_stock }} {{ $item->unit_type }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-semibold">{{ __('Quantité à facturer') }}</label>
                <input type="number" id="inventory_quantity" class="form-control" min="0.01" step="0.01" value="1">
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" class="btn-secondary" onclick="closeInventoryModal()">
                    {{ __('Annuler') }}
                </button>
                <button type="button" class="btn-primary" onclick="addInventoryItemFromModal()">
                    {{ __('Ajouter') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        let itemIndex = 0;

        function _num(v, fallback = 0) {
            const n = parseFloat(v);
            return Number.isFinite(n) ? n : fallback;
        }
        function _money(n) { return (Math.round((n + Number.EPSILON) * 100) / 100); }
        function moneyFmt(v) {
            const n = (isFinite(v) ? v : 0);
            return n.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
        }

        function computeLineDiscountHt(baseHt, type, value) {
            if (!type || value === null || value === undefined || value === '') return 0;
            const v = Math.max(0, _num(value, 0));
            let d = 0;
            if (type === 'percent') d = baseHt * (v / 100);
            else if (type === 'amount') d = v;
            d = Math.min(d, baseHt);
            return _money(d);
        }

        function computeGlobalDiscountHt(subtotalAfterLinesHt, type, value) {
            if (!type || subtotalAfterLinesHt <= 0) return 0;
            const v = Math.max(0, _num(value, 0));
            let d = 0;
            if (type === 'percent') d = subtotalAfterLinesHt * (v / 100);
            else if (type === 'amount') d = v;
            d = Math.min(d, subtotalAfterLinesHt);
            return _money(d);
        }

        // --- custom "name + details" -> hidden description field
        function syncCustomDescriptions() {
            const rows = Array.from(document.querySelectorAll('#quote-items-table tbody tr'));
            rows.forEach(row => {
                const type = row.querySelector('input[name*="[type]"]')?.value;
                if (type !== 'custom') return;

                const nameEl = row.querySelector('.custom-name');
                const detailsEl = row.querySelector('.custom-details');
                const hidden = row.querySelector('.custom-description-hidden');
                if (!hidden) return;

                const name = (nameEl?.value || '').trim();
                const details = (detailsEl?.value || '').trim();

                if (name && details) hidden.value = `${name} — ${details}`;
                else if (name) hidden.value = name;
                else hidden.value = details;
            });
        }

        function openInventoryModal() {
            const m = document.getElementById('inventoryModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }
        function closeInventoryModal() {
            const m = document.getElementById('inventoryModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
            recomputeAllTotals();
        }

        function addProductItem() {
            const tbody = document.querySelector('#quote-items-table tbody');
            const idx = itemIndex++;
            const row = document.createElement('tr');

            row.innerHTML = `
                <td>Prest.</td>
                <td>
                    <input type="hidden" name="items[${idx}][type]" value="product">
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="">
                    <select name="items[${idx}][product_id]" class="form-control product-select" onchange="onProductChange(this)">
                        <option value="">{{ __('Sélectionnez') }}</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->price }}" data-tax="{{ $p->tax_rate }}">
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="items[${idx}][description]" class="form-control" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][quantity]" class="form-control quantity-input" value="1" min="0.01" step="0.01" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][unit_price]" class="form-control unit-price-input" step="0.01" value="0.00" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][tax_rate]" class="form-control tax-rate-input" step="0.01" readonly value="0.00"></td>

                <td>
                    <select name="items[${idx}][line_discount_type]" class="form-control line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">{{ __('—') }}</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>
                <td><input type="number" name="items[${idx}][line_discount_value]" class="form-control line-discount-value" step="0.01" min="0" value="" oninput="recomputeAllTotals()"></td>
                <td><input type="number" class="form-control line-discount-amt readonly-field" step="0.01" readonly></td>

                <td><input type="number" name="items[${idx}][total_price]" class="form-control total-ht readonly-field" step="0.01" readonly></td>
                <td><input type="number" name="items[${idx}][tax_amount]" class="form-control tax-amt readonly-field" step="0.01" readonly></td>
                <td><input type="number" name="items[${idx}][total_price_with_tax]" class="form-control total-ttc readonly-field" step="0.01" readonly></td>

                <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">×</button></td>
            `;

            tbody.appendChild(row);
            recomputeAllTotals();
        }

        function addCustomItem() {
            const tbody = document.querySelector('#quote-items-table tbody');
            const idx = itemIndex++;
            const row = document.createElement('tr');

            row.innerHTML = `
                <td>Libre</td>
                <td>
                    <input type="hidden" name="items[${idx}][type]" value="custom">
                    <input type="hidden" name="items[${idx}][product_id]" value="">
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="">
                    <input type="text" class="form-control custom-name" placeholder="Ex: Consultation, Atelier…" oninput="recomputeAllTotals()">
                </td>
                <td>
                    <input type="text" class="form-control custom-details" placeholder="Détails (optionnel)" oninput="recomputeAllTotals()">
                    <input type="hidden" name="items[${idx}][description]" class="custom-description-hidden" value="">
                </td>
                <td><input type="number" name="items[${idx}][quantity]" class="form-control quantity-input" value="1" min="0.01" step="0.01" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][unit_price]" class="form-control unit-price-input" step="0.01" value="0.00" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][tax_rate]" class="form-control tax-rate-input" step="0.01" value="0.00" oninput="recomputeAllTotals()"></td>

                <td>
                    <select name="items[${idx}][line_discount_type]" class="form-control line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>
                <td><input type="number" name="items[${idx}][line_discount_value]" class="form-control line-discount-value" step="0.01" min="0" value="" oninput="recomputeAllTotals()"></td>
                <td><input type="number" class="form-control line-discount-amt readonly-field" step="0.01" readonly></td>

                <td><input type="number" name="items[${idx}][total_price]" class="form-control total-ht readonly-field" step="0.01" readonly></td>
                <td><input type="number" name="items[${idx}][tax_amount]" class="form-control tax-amt readonly-field" step="0.01" readonly></td>
                <td><input type="number" name="items[${idx}][total_price_with_tax]" class="form-control total-ttc readonly-field" step="0.01" readonly></td>

                <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">×</button></td>
            `;

            tbody.appendChild(row);
            recomputeAllTotals();
        }

        function onProductChange(sel) {
            const opt = sel.options[sel.selectedIndex];
            const row = sel.closest('tr');

            const priceTtc = _num(opt?.dataset?.price, 0);
            const taxRate  = _num(opt?.dataset?.tax, 0);

            const unitHt = taxRate > 0 ? (priceTtc / (1 + taxRate/100)) : priceTtc;

            row.querySelector('.unit-price-input').value = unitHt.toFixed(2);
            row.querySelector('.tax-rate-input').value   = taxRate.toFixed(2);

            recomputeAllTotals();
        }

        function addInventoryItemFromModal() {
            const sel = document.getElementById('inventory_item_id');
            const opt = sel.options[sel.selectedIndex];
            const qty = _num(document.getElementById('inventory_quantity').value, 1);

            if (!opt.value) return;

            const name = opt.dataset.name || '';
            const unitType = opt.dataset.unitType || '';
            const taxRate  = _num(opt.dataset.tax, 0);

            const ttcUnit  = _num(opt.dataset.ttcUnit, 0);
            const ttcPerMl = _num(opt.dataset.ttcPerMl, 0);

            const isMl = unitType === 'ml';
            const priceTtc = isMl ? ttcPerMl : ttcUnit;

            const unitHt = taxRate > 0 ? (priceTtc / (1 + taxRate/100)) : priceTtc;

            const tbody = document.querySelector('#quote-items-table tbody');
            const idx = itemIndex++;
            const row = document.createElement('tr');

            row.innerHTML = `
                <td>Inv.</td>
                <td>
                    <input type="hidden" name="items[${idx}][type]" value="inventory">
                    <input type="hidden" name="items[${idx}][product_id]" value="">
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="${opt.value}">
                    <div class="form-control readonly-field">${name}</div>
                </td>
                <td><input type="text" name="items[${idx}][description]" class="form-control" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][quantity]" class="form-control quantity-input" min="0.01" step="0.01" value="${qty}" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][unit_price]" class="form-control unit-price-input" step="0.01" value="${unitHt.toFixed(2)}" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][tax_rate]" class="form-control tax-rate-input" step="0.01" value="${taxRate.toFixed(2)}" readonly></td>

                <td>
                    <select name="items[${idx}][line_discount_type]" class="form-control line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>
                <td><input type="number" name="items[${idx}][line_discount_value]" class="form-control line-discount-value" step="0.01" min="0" value="" oninput="recomputeAllTotals()"></td>
                <td><input type="number" class="form-control line-discount-amt readonly-field" step="0.01" readonly></td>

                <td><input type="number" name="items[${idx}][total_price]" class="form-control total-ht readonly-field" step="0.01" readonly></td>
                <td><input type="number" name="items[${idx}][tax_amount]" class="form-control tax-amt readonly-field" step="0.01" readonly></td>
                <td><input type="number" name="items[${idx}][total_price_with_tax]" class="form-control total-ttc readonly-field" step="0.01" readonly></td>

                <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">×</button></td>
            `;

            tbody.appendChild(row);
            closeInventoryModal();
            recomputeAllTotals();
        }

        function recomputeAllTotals() {
            syncCustomDescriptions();

            const rows = Array.from(document.querySelectorAll('#quote-items-table tbody tr'));

            let subtotalHtBefore = 0;
            let lineDiscountsHt  = 0;
            let subtotalAfterLines = 0;

            const lineData = rows.map(row => {
                const qty = Math.max(0, _num(row.querySelector('.quantity-input')?.value, 0));
                const unitHt = Math.max(0, _num(row.querySelector('.unit-price-input')?.value, 0));
                const taxRate = Math.max(0, _num(row.querySelector('.tax-rate-input')?.value, 0));

                const baseHt = qty * unitHt;
                subtotalHtBefore += baseHt;

                const dType = row.querySelector('.line-discount-type')?.value || '';
                const dVal  = _num(row.querySelector('.line-discount-value')?.value, 0);

                const lineDisc = computeLineDiscountHt(baseHt, dType, dVal);
                lineDiscountsHt += lineDisc;

                const afterLinesHt = _money(baseHt - lineDisc);
                subtotalAfterLines += afterLinesHt;

                const discAmtInput = row.querySelector('.line-discount-amt');
                if (discAmtInput) discAmtInput.value = lineDisc.toFixed(2);

                return { row, afterLinesHt, taxRate };
            });

            const gType = document.getElementById('global_discount_type')?.value || '';
            const gVal  = document.getElementById('global_discount_value')?.value || '';
            const globalDiscountHt = computeGlobalDiscountHt(subtotalAfterLines, gType, gVal);

            let totalHt = 0, totalTva = 0, totalTtc = 0;
            const denom = subtotalAfterLines > 0 ? subtotalAfterLines : 0;

            lineData.forEach((ld, i) => {
                const share = denom > 0 ? (ld.afterLinesHt / denom) : 0;

                // allocate global discount prorata (rounding safe by last line correction)
                let alloc = _money(globalDiscountHt * share);
                if (i === lineData.length - 1) {
                    const prevAlloc = lineData.slice(0, i).reduce((s, x) => s + _money(globalDiscountHt * (denom > 0 ? x.afterLinesHt/denom : 0)), 0);
                    alloc = _money(globalDiscountHt - prevAlloc);
                }
                alloc = Math.min(alloc, ld.afterLinesHt);

                const finalHt = _money(ld.afterLinesHt - alloc);
                const tva = _money(finalHt * (ld.taxRate / 100));
                const ttc = _money(finalHt + tva);

                totalHt += finalHt;
                totalTva += tva;
                totalTtc += ttc;

                ld.row.querySelector('.total-ht').value = finalHt.toFixed(2);
                ld.row.querySelector('.tax-amt').value  = tva.toFixed(2);
                ld.row.querySelector('.total-ttc').value= ttc.toFixed(2);
            });

            document.getElementById('ui_subtotal_ht').textContent = moneyFmt(subtotalHtBefore);
            document.getElementById('ui_line_discounts_ht').textContent = moneyFmt(lineDiscountsHt);
            document.getElementById('ui_subtotal_after_lines_ht').textContent = moneyFmt(subtotalAfterLines);
            document.getElementById('ui_global_discount_ht').textContent = moneyFmt(globalDiscountHt);
            document.getElementById('ui_total_ht').textContent = moneyFmt(totalHt);
            document.getElementById('ui_total_tva').textContent = moneyFmt(totalTva);
            document.getElementById('ui_total_ttc').textContent = moneyFmt(totalTtc);
        }

        window.addEventListener('DOMContentLoaded', () => {
            // start with one line
            addProductItem();

            const form = document.getElementById('quoteCreateForm');
            form.addEventListener('submit', () => syncCustomDescriptions());
        });
    </script>

    <style>
        .container-fluid { max-width: 1200px; }
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
            cursor:pointer;
        }
        .btn-secondary:hover { background-color: #854f38; color: #fff; }
        .text-red-500 { color: #e3342f; font-size: 0.875rem; }

        #quote-items-table { width: 100%; margin-bottom: 15px; table-layout: auto; }
        #quote-items-table th, #quote-items-table td { padding: 8px; text-align: left; vertical-align: middle; }
        #quote-items-table th { background-color: #647a0b; color: #fff; white-space: nowrap; }
        #quote-items-table td { border-bottom: 1px solid #ccc; }

        .btn-danger {
            background-color: #e3342f;
            color: #fff;
            padding: 6px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-danger:hover { background-color: #cc1f1a; }

        .readonly-field { background-color: #e9ecef; cursor: not-allowed; }

        .totals-box {
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:10px;
            padding:16px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            max-width:520px;
            margin-left:auto;
        }
        .totals-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px dashed #eee; }
        .totals-row:last-child { border-bottom:none; }
        .totals-row-total { font-size: 1.05rem; }

        @media (max-width: 768px) {
            .details-title { font-size: 1.5rem; }
            .btn-primary, .btn-secondary { width: 100%; text-align: center; margin-bottom: 10px; }
            #quote-items-table th, #quote-items-table td { padding: 6px; }
            .totals-box { max-width: 100%; }
        }
    </style>
</x-app-layout>
