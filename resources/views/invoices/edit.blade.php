<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Modifier la facture') }} - #{{ $invoice->invoice_number }}
        </h2>
    </x-slot>

    <div class="am-wrap">
        <div class="am-card">
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

            <form id="invoiceEditForm" action="{{ route('invoices.update', $invoice) }}" method="POST" class="am-form">
                @csrf
                @method('PUT')

                {{-- Client, dates, notes --}}
                <div class="am-grid">
                    <div class="am-field am-col-6">
                        <label class="am-label">{{ __('Facturer à') }}</label>

                        @php
                            $billTo = old('bill_to', !empty($invoice->corporate_client_id) ? 'corporate' : 'client');
                            $corporateClients = $corporateClients ?? collect();
                        @endphp

                        <div class="flex items-center gap-4 mb-2">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="bill_to" value="client" {{ $billTo === 'client' ? 'checked' : '' }}>
                                <span>{{ __('Particulier') }}</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="bill_to" value="corporate" {{ $billTo === 'corporate' ? 'checked' : '' }}>
                                <span>{{ __('Entreprise') }}</span>
                            </label>
                        </div>

                        <div id="billto-client-wrap">
                            <label class="am-label" for="client_profile_id">{{ __('Client') }}</label>
                            <select id="client_profile_id" name="client_profile_id" class="am-input">
                                <option value="">{{ __('— Sélectionner —') }}</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}"
                                        {{ old('client_profile_id', $invoice->client_profile_id) == $client->id ? 'selected' : '' }}>
                                        {{ $client->first_name }} {{ $client->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_profile_id')<p class="am-err">{{ $message }}</p>@enderror
                        </div>

                        <div id="billto-corporate-wrap" class="mt-3">
                            <label class="am-label" for="corporate_client_id">{{ __('Entreprise') }}</label>
                            <select id="corporate_client_id" name="corporate_client_id" class="am-input">
                                <option value="">{{ __('— Sélectionner —') }}</option>
                                @foreach($corporateClients as $corp)
                                    <option value="{{ $corp->id }}"
                                        {{ old('corporate_client_id', $invoice->corporate_client_id) == $corp->id ? 'selected' : '' }}>
                                        {{ $corp->trade_name ?: $corp->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('corporate_client_id')<p class="am-err">{{ $message }}</p>@enderror
                        </div>

                        <script>
                            (function () {
                                const radios = document.querySelectorAll('input[name="bill_to"]');
                                const wrapClient = document.getElementById('billto-client-wrap');
                                const wrapCorp   = document.getElementById('billto-corporate-wrap');
                                const selClient  = document.getElementById('client_profile_id');
                                const selCorp    = document.getElementById('corporate_client_id');

                                function sync() {
                                    const v = document.querySelector('input[name="bill_to"]:checked')?.value || 'client';
                                    const isCorp = (v === 'corporate');

                                    wrapClient.style.display = isCorp ? 'none' : '';
                                    wrapCorp.style.display   = isCorp ? '' : 'none';

                                    if (selClient) selClient.required = !isCorp;
                                    if (selCorp)   selCorp.required   = isCorp;

                                    if (isCorp) {
                                        if (selClient) selClient.value = '';
                                    } else {
                                        if (selCorp) selCorp.value = '';
                                    }
                                }

                                radios.forEach(r => r.addEventListener('change', sync));
                                sync();
                            })();
                        </script>
                    </div>

                    <div class="am-field am-col-3">
                        <label class="am-label" for="invoice_date">{{ __('Date de facture') }}</label>
                        <input type="date" id="invoice_date" name="invoice_date" class="am-input"
                               value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required>
                        @error('invoice_date')<p class="am-err">{{ $message }}</p>@enderror
                    </div>

                    <div class="am-field am-col-3">
                        <label class="am-label" for="due_date">{{ __('Date d’échéance') }}</label>
                        <input type="date" id="due_date" name="due_date" class="am-input"
                               value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}">
                        @error('due_date')<p class="am-err">{{ $message }}</p>@enderror
                    </div>

                    <div class="am-field am-col-12">
                        <label class="am-label" for="notes">{{ __('Notes') }}</label>
                        <textarea id="notes" name="notes" class="am-input am-textarea" rows="3">{{ old('notes', $invoice->notes) }}</textarea>
                        @error('notes')<p class="am-err">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Articles --}}
                <div class="am-section">
                    <div class="am-section-head am-section-head--row">
                        <div>
                            <div class="am-section-title">{{ __('Articles de la facture') }}</div>
                            <div class="am-muted">{{ __('Modifiez les lignes existantes ou ajoutez-en de nouvelles.') }}</div>
                        </div>

                        <div class="am-actions">
                            <button type="button" class="am-btn am-btn-primary" onclick="addProductItem()">{{ __('Ajouter une prestation') }}</button>
                            <button type="button" class="am-btn am-btn-primary" onclick="openInventoryModal()">{{ __('Ajouter depuis l\'inventaire') }}</button>
                            <button type="button" class="am-btn am-btn-primary" onclick="openPackModal()">{{ __('Ajouter un pack') }}</button>
                            <button type="button" class="am-btn am-btn-primary" onclick="addCustomItem()">{{ __('Ajouter une ligne libre') }}</button>
                        </div>
                    </div>

                    <div class="am-table-wrap">
                        <table class="am-table" id="invoice-items-table">
                            <thead>
                                <tr>
                                    <th class="am-th am-th--type">{{ __('Type') }}</th>
                                    <th class="am-th am-th--product">{{ __('Produit / Article') }}</th>
                                    <th class="am-th am-th--desc">{{ __('Description') }}</th>
                                    <th class="am-th am-th--qty">{{ __('Quantité') }}</th>
                                    <th class="am-th am-th--unit">{{ __('P.U. HT') }}</th>
                                    <th class="am-th am-th--tax">{{ __('TVA') }}</th>
                                    <th class="am-th am-th--discType">{{ __('Remise') }}</th>
                                    <th class="am-th am-th--discVal">{{ __('Valeur remise') }}</th>
                                    <th class="am-th am-th--taxAmt">{{ __('Montant TVA') }}</th>
                                    <th class="am-th am-th--totalTtc">{{ __('Total TTC') }}</th>
                                    <th class="am-th am-th--act">{{ __('Action') }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($invoice->items as $i => $item)
                                    @php
                                        $ldt = old("items.$i.line_discount_type", $item->line_discount_type);

                                        // ✅ Custom: prefer label + description (new schema),
                                        // but keep backward compatibility for older invoices that had "Nom — Détails" in description.
                                        $rawLabel = old("items.$i.label", $item->label ?? '');
                                        $rawDesc  = old("items.$i.description", $item->description ?? '');

                                        $cName = trim((string)$rawLabel);
                                        $cDetails = trim((string)$rawDesc);

                                        if ($item->type === 'custom' && $cName === '' && is_string($rawDesc)) {
                                            $legacy = trim($rawDesc);
                                            if (str_contains($legacy, ' — ')) {
                                                [$left, $right] = array_map('trim', explode(' — ', $legacy, 2));
                                                if ($left !== '' && $right !== '') { $cName = $left; $cDetails = $right; }
                                            } elseif (str_contains($legacy, ' - ')) {
                                                [$left, $right] = array_map('trim', explode(' - ', $legacy, 2));
                                                if ($left !== '' && $right !== '') { $cName = $left; $cDetails = $right; }
                                            }
                                        }
                                    @endphp

                                    <tr>
                                        <td class="am-td am-td--type">
                                            @if($item->type === 'product')
                                                <span class="am-pill">Prest.</span>
                                            @elseif($item->type === 'inventory')
                                                <span class="am-pill am-pill--inv">Inv.</span>
                                            @else
                                                <span class="am-pill am-pill--alt">Libre</span>
                                            @endif

                                            <input type="hidden" name="items[{{ $i }}][type]" value="{{ $item->type }}">
                                        </td>

                                        <td class="am-td am-td--product">
                                            @if($item->type === 'product')
                                                <select name="items[{{ $i }}][product_id]"
                                                        class="am-input product-select"
                                                        onchange="updateItem(this)"
                                                        data-preload="true">
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
                                                <input type="hidden" name="items[{{ $i }}][label]" value="">
                                            @elseif($item->type === 'inventory')
                                                <select name="items[{{ $i }}][inventory_item_id]"
                                                        class="am-input inventory-select"
                                                        onchange="updateInventoryItem(this)"
                                                        data-preload="true">
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
                                                <input type="hidden" name="items[{{ $i }}][label]" value="">
                                            @else
                                                {{-- ✅ Custom: label is the real stored field now --}}
                                                <input type="hidden" name="items[{{ $i }}][product_id]" value="">
                                                <input type="hidden" name="items[{{ $i }}][inventory_item_id]" value="">

                                                <input type="text"
                                                       name="items[{{ $i }}][label]"
                                                       class="am-input custom-name"
                                                       value="{{ $cName }}"
                                                       placeholder="Ex: Consultation, Atelier, Prestation…"
                                                       oninput="recomputeAllTotals()">
                                            @endif
                                        </td>

                                        <td class="am-td am-td--desc">
                                            @if($item->type === 'custom')
                                                <input type="text"
                                                       name="items[{{ $i }}][description]"
                                                       class="am-input custom-details"
                                                       value="{{ $cDetails }}"
                                                       placeholder="{{ __('Détails (optionnel)') }}"
                                                       oninput="recomputeAllTotals()">
                                            @else
                                                <input type="text"
                                                       name="items[{{ $i }}][description]"
                                                       class="am-input description-input"
                                                       value="{{ old("items.$i.description", $item->description ?? '') }}"
                                                       oninput="recomputeAllTotals()">
                                            @endif
                                        </td>

                                        <td class="am-td am-td--qty">
                                            <input type="number"
                                                   name="items[{{ $i }}][quantity]"
                                                   class="am-input am-num quantity-input"
                                                   min="0.01" step="0.01" inputmode="decimal"
                                                   value="{{ old("items.$i.quantity", $item->quantity) }}"
                                                   oninput="recomputeAllTotals()">
                                        </td>

                                        <td class="am-td am-td--unit">
                                            <input type="number"
                                                   name="items[{{ $i }}][unit_price]"
                                                   class="am-input am-num unit-price-input"
                                                   step="0.01" min="0" inputmode="decimal"
                                                   value="{{ old("items.$i.unit_price", $item->unit_price) }}"
                                                   oninput="recomputeAllTotals()">
                                        </td>

                                        <td class="am-td am-td--tax">
                                            <input type="number"
                                                   name="items[{{ $i }}][tax_rate]"
                                                   class="am-input am-num tax-rate-input @if($item->type !== 'custom') am-readonly @endif"
                                                   step="0.01" inputmode="decimal"
                                                   value="{{ old("items.$i.tax_rate", $item->tax_rate) }}"
                                                   @if($item->type !== 'custom') readonly @endif
                                                   oninput="recomputeAllTotals()">
                                        </td>

                                        <td class="am-td am-td--discType">
                                            <select name="items[{{ $i }}][line_discount_type]"
                                                    class="am-input line-discount-type"
                                                    onchange="recomputeAllTotals()">
                                                <option value="" {{ $ldt ? '' : 'selected' }}>—</option>
                                                <option value="percent" {{ $ldt === 'percent' ? 'selected' : '' }}>%</option>
                                                <option value="amount" {{ $ldt === 'amount' ? 'selected' : '' }}>€</option>
                                            </select>
                                        </td>

                                        <td class="am-td am-td--discVal">
                                            <input type="number" step="0.01" min="0"
                                                   name="items[{{ $i }}][line_discount_value]"
                                                   class="am-input am-num line-discount-value"
                                                   value="{{ old("items.$i.line_discount_value", $item->line_discount_value) }}"
                                                   inputmode="decimal"
                                                   oninput="recomputeAllTotals()">
                                        </td>

                                        <td class="am-td am-td--taxAmt">
                                            <input type="number"
                                                   name="items[{{ $i }}][tax_amount]"
                                                   class="am-input am-num am-readonly tax-amount-input"
                                                   readonly
                                                   value="{{ old("items.$i.tax_amount", $item->tax_amount) }}">
                                        </td>

                                        <td class="am-td am-td--totalTtc">
                                            <input type="number"
                                                   name="items[{{ $i }}][total_price_with_tax]"
                                                   class="am-input am-num am-readonly total-price-with-tax-input"
                                                   readonly
                                                   value="{{ old("items.$i.total_price_with_tax", $item->total_price_with_tax) }}">
                                        </td>

                                        <td class="am-td am-td--act">
                                            <button type="button" class="am-btn am-btn-danger am-icon" onclick="removeItem(this)" aria-label="{{ __('Supprimer') }}">×</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Global discount + totals --}}
                <div class="am-section">
                    <div class="am-section-head">
                        <div>
                            <div class="am-section-title">{{ __('Remise globale & Totaux') }}</div>
                            <div class="am-muted">{{ __('La remise globale est répartie au prorata des lignes pour conserver une TVA correcte.') }}</div>
                        </div>
                    </div>

                    <div class="am-discount-row">
                        <div class="am-field" style="min-width:220px;">
                            <label class="am-label">{{ __('Remise globale') }}</label>
                            @php $gdt = old('global_discount_type', $invoice->global_discount_type); @endphp
                            <select id="global_discount_type" name="global_discount_type" class="am-input" onchange="recomputeAllTotals()">
                                <option value="" {{ $gdt ? '' : 'selected' }}>{{ __('Aucune') }}</option>
                                <option value="percent" {{ $gdt === 'percent' ? 'selected' : '' }}>%</option>
                                <option value="amount" {{ $gdt === 'amount' ? 'selected' : '' }}>€</option>
                            </select>
                        </div>

                        <div class="am-field" style="min-width:220px;">
                            <label class="am-label">{{ __('Valeur') }}</label>
                            <input id="global_discount_value" type="number" step="0.01" min="0"
                                   name="global_discount_value" class="am-input"
                                   value="{{ old('global_discount_value', $invoice->global_discount_value) }}"
                                   inputmode="decimal"
                                   oninput="recomputeAllTotals()">
                        </div>

                        <div class="am-chip">
                            <span class="am-chip-dot"></span>
                            <span>{{ __('TVA au prorata') }}</span>
                        </div>
                    </div>

                    <div class="am-totals am-totals--wide">
                        <div class="am-totals-row"><span>{{ __('Sous-total HT') }}</span><strong><span id="ui_subtotal_ht">0.00</span> €</strong></div>
                        <div class="am-totals-row"><span>{{ __('Total remises ligne (HT)') }}</span><strong>-<span id="ui_line_discounts_ht">0.00</span> €</strong></div>
                        <div class="am-totals-row"><span>{{ __('Remise globale (HT)') }}</span><strong>-<span id="ui_global_discount_ht">0.00</span> €</strong></div>

                        <div class="am-totals-row am-totals-row--total"><span>{{ __('Total HT') }}</span><strong><span id="ui_total_ht">0.00</span> €</strong></div>
                        <div class="am-totals-row"><span>{{ __('Total TVA') }}</span><strong><span id="ui_total_tva">0.00</span> €</strong></div>
                        <div class="am-totals-row am-totals-row--total"><span>{{ __('Total TTC') }}</span><strong><span id="ui_total_ttc">0.00</span> €</strong></div>
                    </div>
                </div>

                <div class="am-footer">
                    <button type="submit" class="am-btn am-btn-primary am-btn-lg">{{ __('Mettre à jour la facture') }}</button>
                    <a href="{{ route('invoices.show', $invoice) }}" class="am-btn am-btn-secondary am-btn-lg">{{ __('Annuler') }}</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal inventaire (EDIT) --}}
    <div id="inventoryModal" class="am-modal hidden" aria-hidden="true">
        <div class="am-modal-backdrop" onclick="closeInventoryModal()"></div>
        <div class="am-modal-panel" role="dialog" aria-modal="true" aria-labelledby="amInvTitle">
            <div class="am-modal-head">
                <h2 id="amInvTitle" class="am-modal-title">{{ __('Ajouter un article depuis l’inventaire') }}</h2>
                <button type="button" class="am-x" onclick="closeInventoryModal()" aria-label="{{ __('Fermer') }}">×</button>
            </div>

            <div class="am-modal-body">
                <div class="am-field">
                    <label class="am-label">{{ __('Article') }}</label>
                    <select id="inventory_item_id" class="am-input">
                        <option value="">{{ __('Sélectionnez un article') }}</option>
                        @foreach($inventoryItems as $inv)
                            <option
                                value="{{ $inv->id }}"
                                data-name="{{ $inv->name }}"
                                data-unit-type="{{ $inv->unit_type }}"
                                data-ttc-unit="{{ $inv->selling_price }}"
                                data-ttc-per-ml="{{ $inv->selling_price_per_ml }}"
                                data-tax="{{ $inv->vat_rate_sale }}"
                            >
                                {{ $inv->name }} ({{ $inv->unit_type }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="am-field">
                    <label class="am-label">{{ __('Quantité à facturer') }}</label>
                    <input type="number" id="inventory_quantity" class="am-input" min="1" value="1" inputmode="decimal">
                </div>
            </div>

            <div class="am-modal-foot">
                <button type="button" class="am-btn am-btn-secondary" onclick="closeInventoryModal()">{{ __('Annuler') }}</button>
                <button type="button" class="am-btn am-btn-primary" onclick="addInventoryItemFromModal()">{{ __('Ajouter') }}</button>
            </div>
        </div>
    </div>

    {{-- Modal packs (EDIT) --}}
    <div id="packModal" class="am-modal hidden" aria-hidden="true">
        <div class="am-modal-backdrop" onclick="closePackModal()"></div>
        <div class="am-modal-panel" role="dialog" aria-modal="true" aria-labelledby="amPackTitle">
            <div class="am-modal-head">
                <h2 id="amPackTitle" class="am-modal-title">{{ __('Ajouter un pack') }}</h2>
                <button type="button" class="am-x" onclick="closePackModal()" aria-label="{{ __('Fermer') }}">×</button>
            </div>

            <div class="am-modal-body">
                <div class="am-field">
                    <label class="am-label">{{ __('Pack') }}</label>
                    <select id="pack_product_id" class="am-input">
                        <option value="">{{ __('Sélectionnez un pack') }}</option>
                        @foreach($packProducts ?? [] as $pack)
                            <option
                                value="{{ $pack->id }}"
                                data-name="{{ $pack->name }}"
                                data-price="{{ $pack->price ?? 0 }}"
                                data-tax="{{ $pack->tax_rate ?? 0 }}"
                            >
                                {{ $pack->name }}
                            </option>
                        @endforeach
                    </select>

                    <div class="am-muted" style="margin-top:8px; font-size:.88rem;">
                        {{ __('Le pack sera ajouté comme une ligne personnalisée (facturation).') }}
                    </div>
                </div>
            </div>

            <div class="am-modal-foot">
                <button type="button" class="am-btn am-btn-secondary" onclick="closePackModal()">{{ __('Annuler') }}</button>
                <button type="button" class="am-btn am-btn-primary" onclick="addPackItemFromModal()">{{ __('Ajouter') }}</button>
            </div>
        </div>
    </div>

    <script>
        let itemIndex = {{ $invoice->items->count() }};

        // ---------- utils ----------
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

        // ---------- totals ----------
        function recomputeAllTotals() {
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
                const ttcEl    = l.row.querySelector('.total-price-with-tax-input');
                if (taxAmtEl) taxAmtEl.value = taxAmt.toFixed(2);
                if (ttcEl)    ttcEl.value = ttc.toFixed(2);

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

        // ---------- row helpers ----------
        function removeItem(btn) {
            const tr = btn?.closest?.('tr');
            if (tr) tr.remove();
            recomputeAllTotals();
        }

        // ---------- add rows (edit) ----------
        function addProductItem() {
            const tbody = document.querySelector('#invoice-items-table tbody');
            const idx = itemIndex++;
            const row = document.createElement('tr');

            row.innerHTML = `
                <td class="am-td am-td--type">
                    <span class="am-pill">Prest.</span>
                    <input type="hidden" name="items[${idx}][type]" value="product">
                </td>

                <td class="am-td am-td--product">
                    <select name="items[${idx}][product_id]" class="am-input product-select" onchange="updateItem(this)">
                        <option value="">{{ __('Sélectionnez un produit') }}</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" data-price="{{ $prod->price }}" data-tax-rate="{{ $prod->tax_rate }}">
                                {{ $prod->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="">
                    <input type="hidden" name="items[${idx}][label]" value="">
                </td>

                <td class="am-td am-td--desc">
                    <input type="text" name="items[${idx}][description]" class="am-input description-input" placeholder="{{ __('Détails (optionnel)') }}" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--qty">
                    <input type="number" name="items[${idx}][quantity]" class="am-input am-num quantity-input"
                           value="1" min="0.01" step="0.01" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--unit">
                    <input type="number" name="items[${idx}][unit_price]" class="am-input am-num unit-price-input"
                           step="0.01" min="0" inputmode="decimal" value="0.00" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--tax">
                    <input type="number" name="items[${idx}][tax_rate]" class="am-input am-num tax-rate-input am-readonly"
                           step="0.01" inputmode="decimal" readonly value="0.00">
                </td>

                <td class="am-td am-td--discType">
                    <select name="items[${idx}][line_discount_type]" class="am-input line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>

                <td class="am-td am-td--discVal">
                    <input type="number" step="0.01" min="0" name="items[${idx}][line_discount_value]"
                           class="am-input am-num line-discount-value" value="" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--taxAmt">
                    <input type="number" name="items[${idx}][tax_amount]" class="am-input am-num am-readonly tax-amount-input" readonly value="0.00">
                </td>

                <td class="am-td am-td--totalTtc">
                    <input type="number" name="items[${idx}][total_price_with_tax]" class="am-input am-num am-readonly total-price-with-tax-input" readonly value="0.00">
                </td>

                <td class="am-td am-td--act">
                    <button type="button" class="am-btn am-btn-danger am-icon" onclick="removeItem(this)" aria-label="{{ __('Supprimer') }}">×</button>
                </td>
            `;

            tbody.append(row);
            recomputeAllTotals();
        }

        function addCustomItem() {
            const tbody = document.querySelector('#invoice-items-table tbody');
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
                    <input type="text" name="items[${idx}][label]" class="am-input custom-name"
                           placeholder="Ex: Consultation, Atelier…" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--desc">
                    <input type="text" name="items[${idx}][description]" class="am-input custom-details"
                           placeholder="{{ __('Détails (optionnel)') }}" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--qty">
                    <input type="number" name="items[${idx}][quantity]" class="am-input am-num quantity-input"
                           value="1" min="0.01" step="0.01" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--unit">
                    <input type="number" name="items[${idx}][unit_price]" class="am-input am-num unit-price-input"
                           value="0.00" step="0.01" min="0" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--tax">
                    <input type="number" name="items[${idx}][tax_rate]" class="am-input am-num tax-rate-input"
                           value="0.00" step="0.01" min="0" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--discType">
                    <select name="items[${idx}][line_discount_type]" class="am-input line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>

                <td class="am-td am-td--discVal">
                    <input type="number" step="0.01" min="0" name="items[${idx}][line_discount_value]"
                           class="am-input am-num line-discount-value" value="" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--taxAmt">
                    <input type="number" name="items[${idx}][tax_amount]" class="am-input am-num am-readonly tax-amount-input" readonly value="0.00">
                </td>

                <td class="am-td am-td--totalTtc">
                    <input type="number" name="items[${idx}][total_price_with_tax]" class="am-input am-num am-readonly total-price-with-tax-input" readonly value="0.00">
                </td>

                <td class="am-td am-td--act">
                    <button type="button" class="am-btn am-btn-danger am-icon" onclick="removeItem(this)" aria-label="{{ __('Supprimer') }}">×</button>
                </td>
            `;

            tbody.append(row);
            recomputeAllTotals();
        }

        // inventory modal
        function openInventoryModal() {
            const m = document.getElementById('inventoryModal');
            m.classList.remove('hidden');
            m.setAttribute('aria-hidden','false');
        }
        function closeInventoryModal() {
            const m = document.getElementById('inventoryModal');
            m.classList.add('hidden');
            m.setAttribute('aria-hidden','true');
        }

        function addInventoryItemFromModal() {
            const sel = document.getElementById('inventory_item_id');
            const opt = sel.options[sel.selectedIndex];
            const qty = _num(document.getElementById('inventory_quantity').value, 1);
            if (!opt.value) return;

            const ttcUnit = _num(opt.dataset.ttcUnit, 0);
            const ttcMl   = _num(opt.dataset.ttcPerMl, 0);
            const tax     = _num(opt.dataset.tax, 0);
            const unitType = opt.dataset.unitType || 'unit';

            const ttc = (unitType === 'ml') ? ttcMl : ttcUnit;
            const ht  = tax > 0 ? (ttc / (1 + tax/100)) : ttc;

            const tbody = document.querySelector('#invoice-items-table tbody');
            const idx = itemIndex++;
            const row = document.createElement('tr');

            row.innerHTML = `
                <td class="am-td am-td--type">
                    <span class="am-pill am-pill--inv">Inv.</span>
                    <input type="hidden" name="items[${idx}][type]" value="inventory">
                </td>

                <td class="am-td am-td--product">
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="${opt.value}">
                    <input type="hidden" name="items[${idx}][product_id]" value="">
                    <input type="hidden" name="items[${idx}][label]" value="">
                    <div class="am-input am-readonly">${escapeHtml(opt.text)}</div>
                </td>

                <td class="am-td am-td--desc">
                    <input type="text" name="items[${idx}][description]" class="am-input am-readonly description-input"
                           value="${escapeHtml(opt.dataset.name || '')}" readonly>
                </td>

                <td class="am-td am-td--qty">
                    <input type="number" name="items[${idx}][quantity]" class="am-input am-num am-readonly quantity-input" value="${qty}" readonly>
                </td>

                <td class="am-td am-td--unit">
                    <input type="number" name="items[${idx}][unit_price]" class="am-input am-num am-readonly unit-price-input" value="${ht.toFixed(2)}" readonly>
                </td>

                <td class="am-td am-td--tax">
                    <input type="number" name="items[${idx}][tax_rate]" class="am-input am-num am-readonly tax-rate-input" value="${tax.toFixed(2)}" readonly>
                </td>

                <td class="am-td am-td--discType">
                    <select name="items[${idx}][line_discount_type]" class="am-input line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>

                <td class="am-td am-td--discVal">
                    <input type="number" step="0.01" min="0" name="items[${idx}][line_discount_value]"
                           class="am-input am-num line-discount-value" value="" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--taxAmt">
                    <input type="number" name="items[${idx}][tax_amount]" class="am-input am-num am-readonly tax-amount-input" readonly value="0.00">
                </td>

                <td class="am-td am-td--totalTtc">
                    <input type="number" name="items[${idx}][total_price_with_tax]" class="am-input am-num am-readonly total-price-with-tax-input" readonly value="0.00">
                </td>

                <td class="am-td am-td--act">
                    <button type="button" class="am-btn am-btn-danger am-icon" onclick="removeItem(this)" aria-label="{{ __('Supprimer') }}">×</button>
                </td>
            `;

            tbody.append(row);
            recomputeAllTotals();
            closeInventoryModal();
        }

        // pack modal
        function openPackModal() {
            const m = document.getElementById('packModal');
            m.classList.remove('hidden');
            m.setAttribute('aria-hidden','false');
        }
        function closePackModal() {
            const m = document.getElementById('packModal');
            m.classList.add('hidden');
            m.setAttribute('aria-hidden','true');
        }

        function addPackItemFromModal() {
            const sel = document.getElementById('pack_product_id');
            const opt = sel.options[sel.selectedIndex];
            if (!opt.value) return;

            const name = opt.dataset.name || 'Pack';
            const ht   = _num(opt.dataset.price, 0);
            const tax  = _num(opt.dataset.tax, 0);

            const tbody = document.querySelector('#invoice-items-table tbody');
            const idx = itemIndex++;
            const row = document.createElement('tr');

            row.innerHTML = `
                <td class="am-td am-td--type">
                    <span class="am-pill am-pill--alt">Pack</span>
                    <input type="hidden" name="items[${idx}][type]" value="custom">
                </td>

                <td class="am-td am-td--product">
                    <input type="hidden" name="items[${idx}][product_id]" value="">
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="">
                    <input type="text" name="items[${idx}][label]" class="am-input custom-name"
                           value="Pack : ${escapeHtml(name)}" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--desc">
                    <input type="text" name="items[${idx}][description]" class="am-input custom-details"
                           value="" placeholder="{{ __('Détails (optionnel)') }}" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--qty">
                    <input type="number" name="items[${idx}][quantity]" class="am-input am-num quantity-input"
                           value="1" min="0.01" step="0.01" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--unit">
                    <input type="number" name="items[${idx}][unit_price]" class="am-input am-num unit-price-input"
                           value="${ht.toFixed(2)}" step="0.01" min="0" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--tax">
                    <input type="number" name="items[${idx}][tax_rate]" class="am-input am-num tax-rate-input"
                           value="${tax.toFixed(2)}" step="0.01" min="0" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--discType">
                    <select name="items[${idx}][line_discount_type]" class="am-input line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>

                <td class="am-td am-td--discVal">
                    <input type="number" step="0.01" min="0" name="items[${idx}][line_discount_value]"
                           class="am-input am-num line-discount-value" value="" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--taxAmt">
                    <input type="number" name="items[${idx}][tax_amount]" class="am-input am-num am-readonly tax-amount-input" readonly value="0.00">
                </td>

                <td class="am-td am-td--totalTtc">
                    <input type="number" name="items[${idx}][total_price_with_tax]" class="am-input am-num am-readonly total-price-with-tax-input" readonly value="0.00">
                </td>

                <td class="am-td am-td--act">
                    <button type="button" class="am-btn am-btn-danger am-icon" onclick="removeItem(this)" aria-label="{{ __('Supprimer') }}">×</button>
                </td>
            `;

            tbody.append(row);
            recomputeAllTotals();
            closePackModal();
        }

        // ---------- select updates ----------
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

        // ---------- init ----------
        window.onload = () => {
            document.querySelectorAll('.product-select[data-preload="true"]').forEach(sel => updateItem(sel));
            document.querySelectorAll('.inventory-select[data-preload="true"]').forEach(sel => updateInventoryItem(sel));
            recomputeAllTotals();
        };
    </script>



    <style>
        /* Same UI system as create invoice (shared styles, inline) */
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
        .am-err{ margin-top:6px; color:#e3342f; font-size:.875rem; }

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

        .am-th--type, .am-td--type{ width:90px; }
        .am-th--product, .am-td--product{ min-width:280px; }
        .am-th--desc, .am-td--desc{ min-width:260px; }
        .am-th--qty, .am-td--qty{ width:120px; }
        .am-th--unit, .am-td--unit{ width:140px; }
        .am-th--tax, .am-td--tax{ width:110px; }
        .am-th--discType, .am-td--discType{ width:140px; }
        .am-th--discVal, .am-td--discVal{ width:140px; }
        .am-th--taxAmt, .am-td--taxAmt{ width:160px; }
        .am-th--totalTtc, .am-td--totalTtc{ width:150px; }
        .am-th--act, .am-td--act{ width:86px; text-align:center; }

        .am-table .am-input{ border-radius:10px; padding:9px 10px; }
        .am-num{ text-align:right; font-variant-numeric:tabular-nums; min-width:92px; }

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

        .am-discount-row{
            display:flex;
            flex-wrap:wrap;
            gap:12px;
            align-items:flex-end;
            margin-bottom:12px;
        }
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

        .am-totals{
            margin-left:auto;
            max-width:620px;
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:14px;
            padding:12px 14px;
        }
        .am-totals--wide{ max-width:720px; }
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
            .am-actions{ justify-content:stretch; }
            .am-actions .am-btn{ flex:1 1 auto; width:100%; }
            .am-footer{ justify-content:stretch; }
            .am-footer .am-btn{ width:100%; }
            .am-totals{ max-width:100%; }
        }
    </style>
</x-app-layout>

