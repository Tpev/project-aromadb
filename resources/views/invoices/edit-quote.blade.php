<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight" style="color:#647a0b;">
            {{ __('Modifier le devis') }} - #{{ $quote->quote_number ?? $quote->id }}
        </h2>
    </x-slot>

    <div class="am-wrap">
        <div class="am-card">
            @if(session('success'))
                <div class="am-flash am-flash--success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="am-flash am-flash--error">{{ session('error') }}</div>
            @endif

            <div class="am-head">
                <h1 class="am-title">{{ __('Modifier le Devis n°') }}{{ $quote->quote_number ?? $quote->id }}</h1>
            </div>

            @if ($errors->any())
                <div class="am-alert">
                    <div class="am-alert-title">{{ __('Erreurs') }}</div>
                    <ul class="am-alert-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="quoteEditForm" action="{{ route('invoices.updateQuote', $quote->id) }}" method="POST" class="am-form">
                @csrf
                @method('PUT')

                @php
                    $gType = old('global_discount_type', $quote->global_discount_type);
                    $gVal  = old('global_discount_value', $quote->global_discount_value);
                @endphp

                {{-- Top fields --}}
                <div class="am-grid">
                    <div class="am-field am-col-6">
                        <label class="am-label">{{ __('Client') }}</label>
                        <select name="client_profile_id" class="am-input" required>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ old('client_profile_id', $quote->client_profile_id)==$c->id?'selected':'' }}>
                                    {{ $c->first_name }} {{ $c->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_profile_id')<p class="am-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="am-field am-col-3">
                        <label class="am-label">{{ __('Date du Devis') }}</label>
                        <input type="date" name="quote_date" class="am-input"
                               value="{{ old('quote_date', optional($quote->invoice_date)->format('Y-m-d')) }}" required>
                        @error('quote_date')<p class="am-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="am-field am-col-3">
                        <label class="am-label">{{ __('Valable jusqu’au') }}</label>
                        <input type="date" name="valid_until" class="am-input"
                               value="{{ old('valid_until', optional($quote->due_date)->format('Y-m-d')) }}">
                        @error('valid_until')<p class="am-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="am-field am-col-12">
                        <label class="am-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="am-input am-textarea" rows="3">{{ old('notes', $quote->notes) }}</textarea>
                    </div>
                </div>

                {{-- Global discount --}}
                <div class="am-section">
                    <div class="am-section-head">
                        <div>
                            <div class="am-section-title">{{ __('Remise globale') }}</div>
                            <div class="am-muted">{{ __('Appliquée sur le total HT après remises lignes, TVA recalculée au prorata.') }}</div>
                        </div>
                    </div>

                    <div class="am-discount-row">
                        <div class="am-field">
                            <label class="am-label" for="global_discount_type">{{ __('Type') }}</label>
                            <select id="global_discount_type" name="global_discount_type" class="am-input" onchange="recomputeAllTotals()">
                                <option value="" {{ $gType==='' || is_null($gType) ? 'selected':'' }}>{{ __('Aucune') }}</option>
                                <option value="percent" {{ $gType==='percent' ? 'selected':'' }}>%</option>
                                <option value="amount" {{ $gType==='amount' ? 'selected':'' }}>€</option>
                            </select>
                        </div>

                        <div class="am-field">
                            <label class="am-label" for="global_discount_value">{{ __('Valeur') }}</label>
                            <input id="global_discount_value"
                                   type="number"
                                   name="global_discount_value"
                                   class="am-input"
                                   step="0.01"
                                   min="0"
                                   value="{{ $gVal }}"
                                   inputmode="decimal"
                                   oninput="recomputeAllTotals()">
                        </div>

                        <div class="am-chip">
                            <span class="am-chip-dot"></span>
                            <span>{{ __('TVA au prorata') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div class="am-section">
                    <div class="am-section-head am-section-head--row">
                        <div>
                            <div class="am-section-title">{{ __('Articles du devis') }}</div>
                            <div class="am-muted">{{ __('Vous pouvez modifier les lignes existantes ou en ajouter de nouvelles.') }}</div>
                        </div>

                        <div class="am-actions">
                            <button type="button" class="am-btn am-btn-primary" onclick="addProductItem()">{{ __('Ajouter une prestation') }}</button>
                            <button type="button" class="am-btn am-btn-primary" onclick="openInventoryModal()">{{ __('Ajouter depuis l\'inventaire') }}</button>
                            <button type="button" class="am-btn am-btn-primary" onclick="addCustomItem()">{{ __('Ajouter une ligne libre') }}</button>
                        </div>
                    </div>

                    <div class="am-table-wrap">
                        <table class="am-table" id="quote-items-table">
                            <thead>
                                <tr>
                                    <th class="am-th am-th--type">{{ __('Type') }}</th>
                                    <th class="am-th am-th--product">{{ __('Produit / Article') }}</th>
                                    <th class="am-th am-th--desc">{{ __('Description') }}</th>
                                    <th class="am-th am-th--qty">{{ __('Qté') }}</th>
                                    <th class="am-th am-th--unit">{{ __('P.U. HT') }}</th>
                                    <th class="am-th am-th--tax">{{ __('TVA') }}</th>

                                    <th class="am-th am-th--discType">{{ __('Remise') }}</th>
                                    <th class="am-th am-th--discVal">{{ __('Valeur') }}</th>
                                    <th class="am-th am-th--discAmt">{{ __('Remise HT') }}</th>

                                    <th class="am-th am-th--totalHt">{{ __('Total HT') }}</th>
                                    <th class="am-th am-th--taxAmt">{{ __('Montant TVA') }}</th>
                                    <th class="am-th am-th--totalTtc">{{ __('Total TTC') }}</th>
                                    <th class="am-th am-th--act">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quote->items as $i => $item)
                                    @php
                                        // custom split for existing
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

                                        $ldType = old("items.$i.line_discount_type", $item->line_discount_type);
                                        $ldVal  = old("items.$i.line_discount_value", $item->line_discount_value);
                                    @endphp

                                    <tr>
                                        <td class="am-td am-td--type">
                                            @if($item->type === 'inventory')
                                                <span class="am-pill am-pill--inv">Inv.</span>
                                            @elseif($item->type === 'product')
                                                <span class="am-pill">Prest.</span>
                                            @else
                                                <span class="am-pill am-pill--alt">Libre</span>
                                            @endif
                                            <input type="hidden" name="items[{{ $i }}][type]" value="{{ $item->type }}">
                                        </td>

                                        <td class="am-td am-td--product">
                                            @if($item->type === 'product')
                                                <input type="hidden" name="items[{{ $i }}][inventory_item_id]" value="">
                                                <select name="items[{{ $i }}][product_id]" class="am-input product-select" onchange="onProductChange(this)">
                                                    <option value="">{{ __('Sélectionnez') }}</option>
                                                    @foreach($products as $p)
                                                        <option value="{{ $p->id }}"
                                                            data-price="{{ $p->price }}"
                                                            data-tax="{{ $p->tax_rate }}"
                                                            {{ (int)$item->product_id === (int)$p->id ? 'selected' : '' }}>
                                                            {{ $p->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @elseif($item->type === 'inventory')
                                                <input type="hidden" name="items[{{ $i }}][product_id]" value="">
                                                <input type="hidden" name="items[{{ $i }}][inventory_item_id]" value="{{ $item->inventory_item_id }}">
                                                <div class="am-input am-readonly">
                                                    {{ $item->inventoryItem->name ?? __('Article inventaire') }}
                                                </div>
                                            @else
                                                <input type="hidden" name="items[{{ $i }}][product_id]" value="">
                                                <input type="hidden" name="items[{{ $i }}][inventory_item_id]" value="">
                                                <input type="text"
                                                    class="am-input custom-name"
                                                    value="{{ old("items.$i.custom_name", trim($cName)) }}"
                                                    placeholder="Ex: Consultation, Atelier…"
                                                    oninput="recomputeAllTotals()">
                                            @endif
                                        </td>

                                        <td class="am-td am-td--desc">
                                            @if($item->type === 'custom')
                                                <input type="text"
                                                    class="am-input custom-details"
                                                    value="{{ old("items.$i.custom_details", trim($cDetails)) }}"
                                                    placeholder="{{ __('Détails (optionnel)') }}"
                                                    oninput="recomputeAllTotals()">
                                                <input type="hidden"
                                                    name="items[{{ $i }}][description]"
                                                    class="custom-description-hidden"
                                                    value="{{ $rawDesc }}">
                                            @else
                                                <input type="text"
                                                    name="items[{{ $i }}][description]"
                                                    class="am-input"
                                                    value="{{ $rawDesc }}"
                                                    placeholder="{{ __('Détails (optionnel)') }}"
                                                    oninput="recomputeAllTotals()">
                                            @endif
                                        </td>

                                        <td class="am-td am-td--qty">
                                            <input type="number"
                                                name="items[{{ $i }}][quantity]"
                                                class="am-input quantity-input am-num"
                                                min="0.01" step="0.01"
                                                inputmode="decimal"
                                                value="{{ old("items.$i.quantity", $item->quantity) }}"
                                                oninput="recomputeAllTotals()">
                                        </td>

                                        <td class="am-td am-td--unit">
                                            <input type="number"
                                                name="items[{{ $i }}][unit_price]"
                                                class="am-input unit-price-input am-num"
                                                step="0.01"
                                                inputmode="decimal"
                                                value="{{ old("items.$i.unit_price", $item->unit_price) }}"
                                                oninput="recomputeAllTotals()">
                                        </td>

                                        <td class="am-td am-td--tax">
                                            <input type="number"
                                                name="items[{{ $i }}][tax_rate]"
                                                class="am-input tax-rate-input am-num"
                                                step="0.01"
                                                inputmode="decimal"
                                                value="{{ old("items.$i.tax_rate", $item->tax_rate) }}"
                                                @if($item->type !== 'custom') readonly @endif
                                                oninput="recomputeAllTotals()">
                                        </td>

                                        <td class="am-td am-td--discType">
                                            <select name="items[{{ $i }}][line_discount_type]" class="am-input line-discount-type" onchange="recomputeAllTotals()">
                                                <option value="" {{ $ldType==='' || is_null($ldType) ? 'selected':'' }}>—</option>
                                                <option value="percent" {{ $ldType==='percent'?'selected':'' }}>%</option>
                                                <option value="amount" {{ $ldType==='amount'?'selected':'' }}>€</option>
                                            </select>
                                        </td>

                                        <td class="am-td am-td--discVal">
                                            <input type="number"
                                                name="items[{{ $i }}][line_discount_value]"
                                                class="am-input line-discount-value am-num"
                                                step="0.01" min="0"
                                                inputmode="decimal"
                                                value="{{ $ldVal }}"
                                                oninput="recomputeAllTotals()">
                                        </td>

                                        <td class="am-td am-td--discAmt">
                                            <input type="number" class="am-input line-discount-amt am-num am-readonly" step="0.01" readonly>
                                        </td>

                                        <td class="am-td am-td--totalHt">
                                            <input type="number"
                                                name="items[{{ $i }}][total_price]"
                                                class="am-input total-ht am-num am-readonly"
                                                step="0.01"
                                                value="{{ old("items.$i.total_price", $item->total_price) }}"
                                                readonly>
                                        </td>

                                        <td class="am-td am-td--taxAmt">
                                            <input type="number"
                                                name="items[{{ $i }}][tax_amount]"
                                                class="am-input tax-amt am-num am-readonly"
                                                step="0.01"
                                                value="{{ old("items.$i.tax_amount", $item->tax_amount) }}"
                                                readonly>
                                        </td>

                                        <td class="am-td am-td--totalTtc">
                                            <input type="number"
                                                name="items[{{ $i }}][total_price_with_tax]"
                                                class="am-input total-ttc am-num am-readonly"
                                                step="0.01"
                                                value="{{ old("items.$i.total_price_with_tax", $item->total_price_with_tax) }}"
                                                readonly>
                                        </td>

                                        <td class="am-td am-td--act">
                                            <button type="button" class="am-btn am-btn-danger am-icon" onclick="removeRow(this)" aria-label="{{ __('Supprimer') }}">×</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals --}}
                    <div class="am-totals">
                        <div class="am-totals-row"><span>{{ __('Sous-total HT (avant remises)') }}</span><strong id="ui_subtotal_ht">0,00 €</strong></div>
                        <div class="am-totals-row"><span>{{ __('Remises lignes (HT)') }}</span><strong id="ui_line_discounts_ht">0,00 €</strong></div>
                        <div class="am-totals-row"><span>{{ __('Sous-total HT (après remises lignes)') }}</span><strong id="ui_subtotal_after_lines_ht">0,00 €</strong></div>
                        <div class="am-totals-row"><span>{{ __('Remise globale (HT)') }}</span><strong id="ui_global_discount_ht">0,00 €</strong></div>
                        <div class="am-totals-row am-totals-row--total"><span>{{ __('Total HT') }}</span><strong id="ui_total_ht">0,00 €</strong></div>
                        <div class="am-totals-row"><span>{{ __('Total TVA') }}</span><strong id="ui_total_tva">0,00 €</strong></div>
                        <div class="am-totals-row am-totals-row--total"><span>{{ __('Total TTC') }}</span><strong id="ui_total_ttc">0,00 €</strong></div>
                    </div>
                </div>

                <div class="am-footer">
                    <button type="submit" class="am-btn am-btn-primary am-btn-lg">{{ __('Mettre à jour le Devis') }}</button>
                    <a href="{{ route('invoices.showQuote',$quote->id) }}" class="am-btn am-btn-secondary am-btn-lg">{{ __('Annuler') }}</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal inventaire --}}
    <div id="inventoryModal" class="am-modal hidden" aria-hidden="true">
        <div class="am-modal-backdrop" onclick="closeInventoryModal()"></div>
        <div class="am-modal-panel" role="dialog" aria-modal="true" aria-labelledby="amModalTitle">
            <div class="am-modal-head">
                <h2 id="amModalTitle" class="am-modal-title">{{ __('Ajouter depuis l’inventaire') }}</h2>
                <button type="button" class="am-x" onclick="closeInventoryModal()" aria-label="{{ __('Fermer') }}">×</button>
            </div>

            <div class="am-modal-body">
                <div class="am-field">
                    <label class="am-label">{{ __('Article') }}</label>
                    <select id="inventory_item_id" class="am-input">
                        <option value="">{{ __('Sélectionnez un article') }}</option>
                        @foreach($inventoryItems as $inv)
                            <option value="{{ $inv->id }}"
                                    data-name="{{ $inv->name }}"
                                    data-unit-type="{{ $inv->unit_type }}"
                                    data-ttc-unit="{{ $inv->selling_price }}"
                                    data-ttc-per-ml="{{ $inv->selling_price_per_ml }}"
                                    data-tax="{{ $inv->vat_rate_sale }}">
                                {{ $inv->name }} ({{ $inv->unit_type }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="am-field">
                    <label class="am-label">{{ __('Quantité') }}</label>
                    <input id="inventory_quantity" type="number" class="am-input" min="0.01" step="0.01" value="1" inputmode="decimal">
                </div>
            </div>

            <div class="am-modal-foot">
                <button type="button" class="am-btn am-btn-secondary" onclick="closeInventoryModal()">{{ __('Annuler') }}</button>
                <button type="button" class="am-btn am-btn-primary" onclick="addInventoryItemFromModal()">{{ __('Ajouter') }}</button>
            </div>
        </div>
    </div>

    <script>
        let itemIndex = {{ (int) $quote->items->count() }};

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

        function openInventoryModal() {
            const m = document.getElementById('inventoryModal');
            m.classList.remove('hidden');
            m.setAttribute('aria-hidden', 'false');
        }
        function closeInventoryModal() {
            const m = document.getElementById('inventoryModal');
            m.classList.add('hidden');
            m.setAttribute('aria-hidden', 'true');
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
                <td class="am-td am-td--type">
                    <span class="am-pill">Prest.</span>
                    <input type="hidden" name="items[${idx}][type]" value="product">
                </td>
                <td class="am-td am-td--product">
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="">
                    <select name="items[${idx}][product_id]" class="am-input product-select" onchange="onProductChange(this)">
                        <option value="">{{ __('Sélectionnez') }}</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->price }}" data-tax="{{ $p->tax_rate }}">
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="am-td am-td--desc">
                    <input type="text" name="items[${idx}][description]" class="am-input" placeholder="{{ __('Détails (optionnel)') }}" oninput="recomputeAllTotals()">
                </td>
                <td class="am-td am-td--qty">
                    <input type="number" name="items[${idx}][quantity]" class="am-input quantity-input am-num"
                           value="1" min="0.01" step="0.01" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>
                <td class="am-td am-td--unit">
                    <input type="number" name="items[${idx}][unit_price]" class="am-input unit-price-input am-num"
                           step="0.01" value="0.00" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>
                <td class="am-td am-td--tax">
                    <input type="number" name="items[${idx}][tax_rate]" class="am-input tax-rate-input am-num"
                           step="0.01" readonly value="0.00">
                </td>

                <td class="am-td am-td--discType">
                    <select name="items[${idx}][line_discount_type]" class="am-input line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>
                <td class="am-td am-td--discVal">
                    <input type="number" name="items[${idx}][line_discount_value]" class="am-input line-discount-value am-num"
                           step="0.01" min="0" value="" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>
                <td class="am-td am-td--discAmt">
                    <input type="number" class="am-input line-discount-amt am-num am-readonly" step="0.01" readonly>
                </td>

                <td class="am-td am-td--totalHt">
                    <input type="number" name="items[${idx}][total_price]" class="am-input total-ht am-num am-readonly" step="0.01" readonly>
                </td>
                <td class="am-td am-td--taxAmt">
                    <input type="number" name="items[${idx}][tax_amount]" class="am-input tax-amt am-num am-readonly" step="0.01" readonly>
                </td>
                <td class="am-td am-td--totalTtc">
                    <input type="number" name="items[${idx}][total_price_with_tax]" class="am-input total-ttc am-num am-readonly" step="0.01" readonly>
                </td>

                <td class="am-td am-td--act">
                    <button type="button" class="am-btn am-btn-danger am-icon" onclick="removeRow(this)" aria-label="{{ __('Supprimer') }}">×</button>
                </td>
            `;

            tbody.appendChild(row);
            recomputeAllTotals();
        }

        function addCustomItem() {
            const tbody = document.querySelector('#quote-items-table tbody');
            const idx = itemIndex++;
            const row = document.createElement('tr');

            row.innerHTML = `
                <td class="am-td am-td--type">
                    <span class="am-pill am-pill--alt">Libre</span>
                    <input type="hidden" name="items[${idx}][type]" value="custom">
                </td>
                <td class="am-td am-td--product">
                    <input type="hidden" name="items[${idx}][product_id]" value="">
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="">
                    <input type="text" class="am-input custom-name" placeholder="Ex: Consultation, Atelier…" oninput="recomputeAllTotals()">
                </td>
                <td class="am-td am-td--desc">
                    <input type="text" class="am-input custom-details" placeholder="{{ __('Détails (optionnel)') }}" oninput="recomputeAllTotals()">
                    <input type="hidden" name="items[${idx}][description]" class="custom-description-hidden" value="">
                </td>
                <td class="am-td am-td--qty">
                    <input type="number" name="items[${idx}][quantity]" class="am-input quantity-input am-num"
                           value="1" min="0.01" step="0.01" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>
                <td class="am-td am-td--unit">
                    <input type="number" name="items[${idx}][unit_price]" class="am-input unit-price-input am-num"
                           step="0.01" value="0.00" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>
                <td class="am-td am-td--tax">
                    <input type="number" name="items[${idx}][tax_rate]" class="am-input tax-rate-input am-num"
                           step="0.01" value="0.00" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--discType">
                    <select name="items[${idx}][line_discount_type]" class="am-input line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>
                <td class="am-td am-td--discVal">
                    <input type="number" name="items[${idx}][line_discount_value]" class="am-input line-discount-value am-num"
                           step="0.01" min="0" value="" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>
                <td class="am-td am-td--discAmt">
                    <input type="number" class="am-input line-discount-amt am-num am-readonly" step="0.01" readonly>
                </td>

                <td class="am-td am-td--totalHt">
                    <input type="number" name="items[${idx}][total_price]" class="am-input total-ht am-num am-readonly" step="0.01" readonly>
                </td>
                <td class="am-td am-td--taxAmt">
                    <input type="number" name="items[${idx}][tax_amount]" class="am-input tax-amt am-num am-readonly" step="0.01" readonly>
                </td>
                <td class="am-td am-td--totalTtc">
                    <input type="number" name="items[${idx}][total_price_with_tax]" class="am-input total-ttc am-num am-readonly" step="0.01" readonly>
                </td>

                <td class="am-td am-td--act">
                    <button type="button" class="am-btn am-btn-danger am-icon" onclick="removeRow(this)" aria-label="{{ __('Supprimer') }}">×</button>
                </td>
            `;

            tbody.appendChild(row);
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
                <td class="am-td am-td--type">
                    <span class="am-pill am-pill--inv">Inv.</span>
                    <input type="hidden" name="items[${idx}][type]" value="inventory">
                </td>
                <td class="am-td am-td--product">
                    <input type="hidden" name="items[${idx}][product_id]" value="">
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="${opt.value}">
                    <div class="am-input am-readonly">${name}</div>
                </td>
                <td class="am-td am-td--desc">
                    <input type="text" name="items[${idx}][description]" class="am-input" placeholder="{{ __('Détails (optionnel)') }}" oninput="recomputeAllTotals()">
                </td>
                <td class="am-td am-td--qty">
                    <input type="number" name="items[${idx}][quantity]" class="am-input quantity-input am-num"
                           value="${qty}" min="0.01" step="0.01" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>
                <td class="am-td am-td--unit">
                    <input type="number" name="items[${idx}][unit_price]" class="am-input unit-price-input am-num"
                           value="${unitHt.toFixed(2)}" step="0.01" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>
                <td class="am-td am-td--tax">
                    <input type="number" name="items[${idx}][tax_rate]" class="am-input tax-rate-input am-num"
                           value="${taxRate.toFixed(2)}" step="0.01" readonly>
                </td>

                <td class="am-td am-td--discType">
                    <select name="items[${idx}][line_discount_type]" class="am-input line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>
                <td class="am-td am-td--discVal">
                    <input type="number" name="items[${idx}][line_discount_value]" class="am-input line-discount-value am-num"
                           step="0.01" min="0" value="" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>
                <td class="am-td am-td--discAmt">
                    <input type="number" class="am-input line-discount-amt am-num am-readonly" step="0.01" readonly>
                </td>

                <td class="am-td am-td--totalHt">
                    <input type="number" name="items[${idx}][total_price]" class="am-input total-ht am-num am-readonly" step="0.01" readonly>
                </td>
                <td class="am-td am-td--taxAmt">
                    <input type="number" name="items[${idx}][tax_amount]" class="am-input tax-amt am-num am-readonly" step="0.01" readonly>
                </td>
                <td class="am-td am-td--totalTtc">
                    <input type="number" name="items[${idx}][total_price_with_tax]" class="am-input total-ttc am-num am-readonly" step="0.01" readonly>
                </td>

                <td class="am-td am-td--act">
                    <button type="button" class="am-btn am-btn-danger am-icon" onclick="removeRow(this)" aria-label="{{ __('Supprimer') }}">×</button>
                </td>
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

            let allocated = 0;
            lineData.forEach((ld, i) => {
                let alloc = 0;
                if (denom > 0 && globalDiscountHt > 0) {
                    if (i === lineData.length - 1) alloc = _money(globalDiscountHt - allocated);
                    else alloc = _money(globalDiscountHt * (ld.afterLinesHt / denom));
                }
                allocated = _money(allocated + alloc);
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
            recomputeAllTotals();
            document.getElementById('quoteEditForm').addEventListener('submit', () => syncCustomDescriptions());
        });
    </script>

    <style>
        /* Same responsive/full-width system as create page */
        .am-wrap{ width:100%; padding:18px 16px 36px; }
        .am-card{
            width:100%;
            max-width:1400px;
            margin:0 auto;
            background:#f9f9f9;
            border-radius:14px;
            padding:18px;
            box-shadow:0 10px 25px rgba(0,0,0,.06);
            border:1px solid rgba(0,0,0,.04);
        }
        .am-head{ display:flex; justify-content:center; }
        .am-title{
            font-size:1.9rem;
            font-weight:800;
            color:#647a0b;
            margin:6px 0 14px;
            text-align:center;
        }

        .am-flash{
            background:#fff;
            border:1px solid #e5e7eb;
            border-left:5px solid #647a0b;
            border-radius:12px;
            padding:10px 12px;
            margin-bottom:12px;
            font-weight:700;
        }
        .am-flash--success{ border-left-color:#16a34a; color:#166534; }
        .am-flash--error{ border-left-color:#dc2626; color:#7f1d1d; }

        .am-alert{
            background:#fff;
            border:1px solid #f1c0c0;
            border-left:5px solid #e3342f;
            border-radius:12px;
            padding:12px 14px;
            margin-bottom:14px;
        }
        .am-alert-title{ font-weight:800; color:#b91c1c; margin-bottom:6px; }
        .am-alert-list{ margin:0; padding-left:18px; color:#7f1d1d; }

        .am-form{ width:100%; }
        .am-grid{ display:flex; flex-wrap:wrap; gap:14px; }
        .am-col-12{ flex:0 0 100%; }
        .am-col-6{ flex:1 1 520px; min-width:280px; }
        .am-col-3{ flex:1 1 240px; min-width:220px; }
        .am-field{ width:100%; }

        .am-label{
            font-weight:800;
            color:#647a0b;
            display:block;
            margin-bottom:6px;
            font-size:.95rem;
        }
        .am-input{
            width:100%;
            padding:10px 11px;
            border:1px solid #d1d5db;
            border-radius:10px;
            background:#fff;
            outline:none;
            transition:box-shadow .15s, border-color .15s;
            font-size:.95rem;
        }
        .am-input:focus{
            border-color:rgba(100,122,11,.55);
            box-shadow:0 0 0 3px rgba(100,122,11,.15);
        }
        .am-textarea{ resize:vertical; }
        .am-error{ color:#e3342f; font-size:.875rem; margin-top:6px; }

        .am-section{
            margin-top:14px;
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:14px;
            padding:14px;
            box-shadow:0 2px 12px rgba(0,0,0,.04);
        }
        .am-section-head{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:14px;
            margin-bottom:10px;
        }
        .am-section-head--row{ align-items:center; flex-wrap:wrap; }
        .am-section-title{ font-weight:900; color:#111827; font-size:1.05rem; margin-bottom:2px; }
        .am-muted{ color:#6b7280; font-size:.9rem; }

        .am-discount-row{ display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; }
        .am-chip{
            display:inline-flex;
            align-items:center;
            gap:8px;
            background:#f7f7f7;
            border:1px solid #e5e7eb;
            border-radius:999px;
            padding:10px 12px;
            font-size:.9rem;
            color:#374151;
        }
        .am-chip-dot{ width:10px; height:10px; border-radius:999px; background:#647a0b; display:inline-block; }

        .am-actions{ display:flex; gap:10px; flex-wrap:wrap; justify-content:flex-end; }

        .am-btn{
            border:none;
            border-radius:12px;
            padding:10px 14px;
            font-weight:800;
            cursor:pointer;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            line-height:1;
            transition:transform .04s ease, opacity .15s ease;
            user-select:none;
            white-space:nowrap;
        }
        .am-btn:active{ transform:translateY(1px); }
        .am-btn-primary{ background:#647a0b; color:#fff; }
        .am-btn-primary:hover{ opacity:.92; }
        .am-btn-secondary{ background:transparent; color:#854f38; border:1px solid #854f38; }
        .am-btn-secondary:hover{ background:#854f38; color:#fff; }
        .am-btn-danger{ background:#e3342f; color:#fff; }
        .am-btn-danger:hover{ opacity:.92; }
        .am-btn-lg{ padding:12px 16px; border-radius:14px; }
        .am-icon{ padding:8px 12px; border-radius:12px; }

        .am-table-wrap{
            width:100%;
            overflow:auto;
            border-radius:12px;
            border:1px solid #e5e7eb;
        }
        .am-table{
            width:100%;
            border-collapse:separate;
            border-spacing:0;
            min-width:1120px;
            background:#fff;
        }
        .am-th{
            position:sticky;
            top:0;
            background:#647a0b;
            color:#fff;
            text-align:left;
            font-weight:900;
            padding:10px 10px;
            font-size:.9rem;
            white-space:nowrap;
            z-index:1;
            border-bottom:1px solid rgba(255,255,255,.18);
        }
        .am-td{
            padding:10px;
            border-bottom:1px solid #f1f5f9;
            vertical-align:middle;
            background:#fff;
        }

        .am-th--type, .am-td--type{ width:84px; }
        .am-th--product, .am-td--product{ min-width:260px; }
        .am-th--desc, .am-td--desc{ min-width:260px; }
        .am-th--qty, .am-td--qty{ width:110px; }
        .am-th--unit, .am-td--unit{ width:140px; }
        .am-th--tax, .am-td--tax{ width:110px; }
        .am-th--discType, .am-td--discType{ width:130px; }
        .am-th--discVal, .am-td--discVal{ width:120px; }
        .am-th--discAmt, .am-td--discAmt{ width:140px; }
        .am-th--totalHt, .am-td--totalHt{ width:140px; }
        .am-th--taxAmt, .am-td--taxAmt{ width:150px; }
        .am-th--totalTtc, .am-td--totalTtc{ width:140px; }
        .am-th--act, .am-td--act{ width:86px; text-align:center; }

        .am-table .am-input{ border-radius:10px; padding:9px 10px; }
        .am-num{ text-align:right; font-variant-numeric:tabular-nums; min-width:92px; }
        .am-td--qty .am-input{ min-width:96px; }

        .am-readonly{ background:#f3f4f6 !important; cursor:not-allowed; }

        .am-pill{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:6px 10px;
            border-radius:999px;
            font-weight:900;
            font-size:.82rem;
            background:rgba(100,122,11,.12);
            color:#374151;
            border:1px solid rgba(100,122,11,.25);
            white-space:nowrap;
        }
        .am-pill--alt{ background:rgba(133,79,56,.12); border-color:rgba(133,79,56,.25); }
        .am-pill--inv{ background:rgba(17,24,39,.08); border-color:rgba(17,24,39,.18); }

        .am-totals{
            margin-top:12px;
            margin-left:auto;
            max-width:560px;
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:14px;
            padding:12px 14px;
        }
        .am-totals-row{
            display:flex;
            justify-content:space-between;
            gap:10px;
            padding:8px 0;
            border-bottom:1px dashed #eef2f7;
            font-size:.98rem;
        }
        .am-totals-row:last-child{ border-bottom:none; }
        .am-totals-row--total{ font-size:1.06rem; }

        .am-footer{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
            justify-content:flex-end;
            margin-top:14px;
        }

        .am-modal{
            position:fixed;
            inset:0;
            z-index:50;
            display:grid;
            place-items:center;
            padding:16px;
        }
        .am-modal.hidden{ display:none; }
        .am-modal-backdrop{ position:absolute; inset:0; background:rgba(0,0,0,.55); }
        .am-modal-panel{
            position:relative;
            width:100%;
            max-width:560px;
            background:#fff;
            border-radius:16px;
            box-shadow:0 20px 60px rgba(0,0,0,.25);
            border:1px solid rgba(0,0,0,.06);
            overflow:hidden;
        }
        .am-modal-head{
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:14px 14px 10px;
            border-bottom:1px solid #eef2f7;
        }
        .am-modal-title{ font-weight:900; font-size:1.05rem; color:#647a0b; }
        .am-x{
            width:36px; height:36px;
            border-radius:10px;
            border:1px solid #e5e7eb;
            background:#fff;
            font-size:20px;
            line-height:1;
            cursor:pointer;
        }
        .am-modal-body{ padding:14px; }
        .am-modal-foot{
            display:flex;
            justify-content:flex-end;
            gap:10px;
            padding:12px 14px 14px;
            border-top:1px solid #eef2f7;
        }

        @media (max-width:768px){
            .am-card{ padding:14px; }
            .am-title{ font-size:1.5rem; }
            .am-actions{ justify-content:stretch; }
            .am-actions .am-btn{ flex:1 1 auto; width:100%; }
            .am-footer{ justify-content:stretch; }
            .am-footer .am-btn{ width:100%; }
            .am-totals{ max-width:100%; }
        }

        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button{ opacity:.6; }
    </style>
</x-app-layout>
