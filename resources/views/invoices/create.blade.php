<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Créer une facture') }}
        </h2>
    </x-slot>

    {{-- Select2 (searchable dropdown) --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <div class="am-wrap">
        <div class="am-card">
            <div class="am-head">
                <h1 class="am-title">{{ __('Nouvelle Facture') }}</h1>
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

            @php
                // Avoid "Undefined variable $selectedClient"
                $selectedClientId = old('client_profile_id', isset($selectedClient) ? $selectedClient->id : null);
                $selectedCorporateId = old('corporate_client_id', isset($selectedCorporateClient) ? $selectedCorporateClient->id : null);
                $billingTarget = old('billing_target', $selectedCorporateId ? 'corporate' : 'client');

                $gType = old('global_discount_type');
                $gVal  = old('global_discount_value');

                // ✅ Preselect prestation from appointment (when coming from "Facturer" button)
                $preselectedProductId = old('preselected_product_id', isset($selectedProduct) ? $selectedProduct->id : null);
            @endphp

            <form id="invoiceForm" action="{{ route('invoices.store') }}" method="POST" class="am-form">
                @csrf

                {{-- Metadata --}}
                <div class="am-grid">
                    <div class="am-field am-col-6">
                        <label class="am-label">{{ __('Facturer à') }}</label>

                        <div class="am-inline-radio" style="display:flex; gap:14px; align-items:center; flex-wrap:wrap; margin-bottom:10px;">
                            <label style="display:flex; gap:8px; align-items:center;">
                                <input type="radio" name="billing_target" value="client" {{ $billingTarget === 'client' ? 'checked' : '' }}>
                                <span>{{ __('Particulier') }}</span>
                            </label>
                            <label style="display:flex; gap:8px; align-items:center;">
                                <input type="radio" name="billing_target" value="corporate" {{ $billingTarget === 'corporate' ? 'checked' : '' }}>
                                <span>{{ __('Entreprise') }}</span>
                            </label>
                        </div>

                        <div id="billToClientWrap">
                            <label class="am-label" for="client_profile_id">{{ __('Client') }}</label>
                            <select id="client_profile_id" name="client_profile_id" class="am-input js-client-select" data-placeholder="Rechercher un client…">
                                <option value="">{{ __('Sélectionnez un client') }}</option>
                                @foreach(($clients ?? collect())->sortBy(fn($c) => mb_strtolower(trim(($c->last_name ?? '').' '.($c->first_name ?? '')))) as $client)
                                    <option value="{{ $client->id }}"
                                            {{ (string)$selectedClientId === (string)$client->id ? 'selected' : '' }}>
                                        {{ $client->last_name }} {{ $client->first_name }}
                                        @if($client->company)
                                            — 👔 {{ $client->company->name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="billToCorporateWrap" style="display:none; margin-top:12px;">
                            <label class="am-label" for="corporate_client_id">{{ __('Entreprise') }}</label>
                            <select id="corporate_client_id" name="corporate_client_id" class="am-input js-corporate-select" data-placeholder="Rechercher une entreprise…">
                                <option value="">{{ __('Sélectionnez une entreprise') }}</option>
                                @foreach(($corporateClients ?? collect())->sortBy(fn($c) => mb_strtolower(trim(($c->name ?? '')))) as $corp)
                                    <option value="{{ $corp->id }}"
                                            {{ (string)$selectedCorporateId === (string)$corp->id ? 'selected' : '' }}>
                                        {{ $corp->name }}
                                        @if(!empty($corp->trade_name) && $corp->trade_name !== $corp->name)
                                            — {{ $corp->trade_name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="am-field am-col-3">
                        <label class="am-label" for="invoice_date">{{ __('Date de facture') }}</label>
                        <input type="date" id="invoice_date" name="invoice_date" class="am-input"
                               value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="am-field am-col-3">
                        <label class="am-label" for="due_date">{{ __('Date d’échéance') }}</label>
                        <input type="date" id="due_date" name="due_date" class="am-input" value="{{ old('due_date') }}">
                    </div>

                    <div class="am-field am-col-12">
                        <label class="am-label" for="notes">{{ __('Notes') }}</label>
                        <textarea id="notes" name="notes" class="am-input am-textarea" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Items --}}
                <div class="am-section">
                    <div class="am-section-head am-section-head--row">
                        <div>
                            <div class="am-section-title">{{ __('Articles de la facture') }}</div>
                            <div class="am-muted">{{ __('Ajoutez des prestations, inventaire, packs, ou une ligne libre.') }}</div>
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
                                {{-- on part vide, on ajoute via JS --}}
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
                            <select id="global_discount_type" name="global_discount_type" class="am-input" onchange="recomputeAllTotals()">
                                <option value="">{{ __('Aucune') }}</option>
                                <option value="percent" {{ old('global_discount_type') === 'percent' ? 'selected' : '' }}>%</option>
                                <option value="amount" {{ old('global_discount_type') === 'amount' ? 'selected' : '' }}>€</option>
                            </select>
                        </div>

                        <div class="am-field" style="min-width:220px;">
                            <label class="am-label">{{ __('Valeur') }}</label>
                            <input id="global_discount_value" type="number" step="0.01" min="0"
                                   name="global_discount_value" class="am-input"
                                   value="{{ old('global_discount_value') }}" inputmode="decimal"
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
                    <button type="submit" class="am-btn am-btn-primary am-btn-lg">{{ __('Créer la Facture') }}</button>
                    <a href="{{ route('invoices.index') }}" class="am-btn am-btn-secondary am-btn-lg">{{ __('Retour à la liste') }}</a>
                </div>
            </form>
            <script>
                (function () {
                    function syncBillingTarget() {
                        const target = (document.querySelector('input[name="billing_target"]:checked') || {}).value || 'client';

                        const clientWrap = document.getElementById('billToClientWrap');
                        const corpWrap   = document.getElementById('billToCorporateWrap');

                        const clientSel = document.getElementById('client_profile_id');
                        const corpSel   = document.getElementById('corporate_client_id');

                        if (!clientWrap || !corpWrap || !clientSel || !corpSel) return;

                        if (target === 'corporate') {
                            clientWrap.style.display = 'none';
                            corpWrap.style.display   = 'block';

                            clientSel.removeAttribute('required');
                            corpSel.setAttribute('required', 'required');

                            // Avoid submitting both
                            clientSel.value = '';
                        } else {
                            clientWrap.style.display = 'block';
                            corpWrap.style.display   = 'none';

                            corpSel.removeAttribute('required');
                            clientSel.setAttribute('required', 'required');

                            // Avoid submitting both
                            corpSel.value = '';
                        }
                    }

                    document.querySelectorAll('input[name="billing_target"]').forEach(r => {
                        r.addEventListener('change', syncBillingTarget);
                    });

                    // Initial state
                    syncBillingTarget();
                })();
            </script>

        </div>
    </div>

    {{-- Modal inventaire --}}
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

                <div class="am-field">
                    <label class="am-label">{{ __('Quantité à facturer') }}</label>
                    <input type="number" id="inventory_quantity" class="am-input" min="1" value="1" inputmode="decimal">
                </div>
            </div>

            <div class="am-modal-foot">
                <button type="button" class="am-btn am-btn-secondary" onclick="closeInventoryModal()">{{ __('Annuler') }}</button>
                <button type="button" class="am-btn am-btn-primary" onclick="addInventoryItem()">{{ __('Ajouter') }}</button>
            </div>
        </div>
    </div>

    {{-- Modal packs --}}
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
                <button type="button" class="am-btn am-btn-primary" onclick="addPackItem()">{{ __('Ajouter') }}</button>
            </div>
        </div>
    </div>

    <script>
        let itemIndex = 0;

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

        // ✅ Custom lines: store nice "Article name + details" into the real items[][description]
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

        // ---------- totals ----------
        function recomputeAllTotals() {
            syncCustomDescriptions();

            const rows = Array.from(document.querySelectorAll('#invoice-items-table tbody tr'));
            const lines = [];

            let subtotalHt = 0;
            let lineDiscountsHt = 0;

            for (const row of rows) {
                const qtyEl   = row.querySelector('input[name*="[quantity]"]');
                const priceEl = row.querySelector('input[name*="[unit_price]"]');
                const taxEl   = row.querySelector('input[name*="[tax_rate]"]');

                if (!qtyEl || !priceEl || !taxEl) continue;

                const qty     = _num(qtyEl.value, 1);
                const unitHt  = _num(priceEl.value, 0);
                const taxRate = _num(taxEl.value, 0);

                const baseHt = unitHt * qty;

                const discTypeEl = row.querySelector('.line-discount-type');
                const discValEl  = row.querySelector('.line-discount-value');

                const discType = discTypeEl ? discTypeEl.value : '';
                const discVal  = discValEl ? discValEl.value : '';

                const lineDiscHt = computeLineDiscountHt(baseHt, discType, discVal);
                const netHtAfterLine = _money(baseHt - lineDiscHt);

                // subtotalHt here = AFTER line discounts (so global discount applies on correct base)
                subtotalHt += netHtAfterLine;
                lineDiscountsHt += lineDiscHt;

                lines.push({
                    row,
                    taxRate,
                    netHtAfterLine,
                    globalAllocHt: 0,
                });
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

                const taxAmtEl = l.row.querySelector('.tax-amt');
                const ttcEl    = l.row.querySelector('.total-ttc');
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

        // ---------- row builders ----------
        function addProductItem() {
            const table = document.querySelector('#invoice-items-table tbody');
            const idx = itemIndex++;
            const row = document.createElement('tr');

            row.innerHTML = `
                <td class="am-td am-td--type">
                    <span class="am-pill">Prest.</span>
                    <input type="hidden" name="items[${idx}][type]" value="product">
                </td>

                <td class="am-td am-td--product">
                    <select name="items[${idx}][product_id]" class="am-input product-select" onchange="updateProductRow(this)">
                        <option value="">{{ __('Sélectionnez') }}</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->price }}" data-tax="{{ $p->tax_rate }}">
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="">
                </td>

                <td class="am-td am-td--desc">
                    <input type="text" name="items[${idx}][description]" class="am-input" placeholder="{{ __('Détails (optionnel)') }}" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--qty">
                    <input type="number" name="items[${idx}][quantity]" class="am-input am-num"
                           value="1" min="0.01" step="0.01" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--unit">
                    <input type="number" name="items[${idx}][unit_price]" class="am-input unit-price am-num am-readonly" readonly value="0.00">
                </td>

                <td class="am-td am-td--tax">
                    <input type="number" name="items[${idx}][tax_rate]" class="am-input tax-rate am-num am-readonly" readonly value="0.00">
                </td>

                <td class="am-td am-td--discType">
                    <select name="items[${idx}][line_discount_type]" class="am-input line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>

                <td class="am-td am-td--discVal">
                    <input type="number" step="0.01" min="0" name="items[${idx}][line_discount_value]" class="am-input line-discount-value am-num"
                           value="" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--taxAmt">
                    <input type="number" class="am-input tax-amt am-num am-readonly" readonly value="0.00">
                </td>

                <td class="am-td am-td--totalTtc">
                    <input type="number" class="am-input total-ttc am-num am-readonly" readonly value="0.00">
                </td>

                <td class="am-td am-td--act">
                    <button type="button" class="am-btn am-btn-danger am-icon" onclick="removeItem(this)" aria-label="{{ __('Supprimer') }}">×</button>
                </td>
            `;

            table.appendChild(row);
            recomputeAllTotals();
        }

        function addCustomItem() {
            const table = document.querySelector('#invoice-items-table tbody');
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
        <input type="text"
               name="items[${idx}][label]"
               class="am-input custom-name"
               placeholder="Ex: Consultation, Atelier, Prestation…"
               oninput="recomputeAllTotals()">
    </td>

    <td class="am-td am-td--desc">
        <input type="text"
               name="items[${idx}][description]"
               class="am-input custom-details"
               placeholder="{{ __('Détails (optionnel)') }}"
               oninput="recomputeAllTotals()">
    </td>

    <td class="am-td am-td--qty">
        <input type="number" step="0.01" min="0.01" name="items[${idx}][quantity]" class="am-input am-num"
               value="1" inputmode="decimal" oninput="recomputeAllTotals()">
    </td>

    <td class="am-td am-td--unit">
        <input type="number" step="0.01" min="0" name="items[${idx}][unit_price]" class="am-input unit-price am-num"
               value="0.00" inputmode="decimal" oninput="recomputeAllTotals()">
    </td>

    <td class="am-td am-td--tax">
        <input type="number" step="0.01" min="0" name="items[${idx}][tax_rate]" class="am-input tax-rate am-num"
               value="0.00" inputmode="decimal" oninput="recomputeAllTotals()">
    </td>

    <td class="am-td am-td--discType">
        <select name="items[${idx}][line_discount_type]" class="am-input line-discount-type" onchange="recomputeAllTotals()">
            <option value="">—</option>
            <option value="percent">%</option>
            <option value="amount">€</option>
        </select>
    </td>

    <td class="am-td am-td--discVal">
        <input type="number" step="0.01" min="0" name="items[${idx}][line_discount_value]" class="am-input line-discount-value am-num"
               value="" inputmode="decimal" oninput="recomputeAllTotals()">
    </td>

    <td class="am-td am-td--taxAmt">
        <input type="number" class="am-input tax-amt am-num am-readonly" readonly value="0.00">
    </td>

    <td class="am-td am-td--totalTtc">
        <input type="number" class="am-input total-ttc am-num am-readonly" readonly value="0.00">
    </td>

    <td class="am-td am-td--act">
        <button type="button" class="am-btn am-btn-danger am-icon" onclick="removeItem(this)" aria-label="{{ __('Supprimer') }}">×</button>
    </td>
`;


            table.appendChild(row);
            recomputeAllTotals();
        }

        // ---------- product row update ----------
        function updateProductRow(el) {
            const row = el.closest('tr');
            if (!row) return;

            const opt = el.selectedOptions[0];
            const price = opt ? _num(opt.dataset.price, 0) : 0;
            const tax   = opt ? _num(opt.dataset.tax, 0) : 0;

            const unitEl = row.querySelector('.unit-price');
            const taxEl  = row.querySelector('.tax-rate');

            if (unitEl) unitEl.value = price.toFixed(2);
            if (taxEl)  taxEl.value  = tax.toFixed(2);

            recomputeAllTotals();
        }

        // ---------- inventory modal ----------
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

        function addInventoryItem() {
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

            const table = document.querySelector('#invoice-items-table tbody');
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
                    <div class="am-input am-readonly">${escapeHtml(opt.text)}</div>
                </td>

                <td class="am-td am-td--desc">
                    <input type="text" name="items[${idx}][description]" class="am-input am-readonly"
                           value="${escapeHtml(opt.dataset.name || '')}" readonly>
                </td>

                <td class="am-td am-td--qty">
                    <input type="number" name="items[${idx}][quantity]" class="am-input am-num am-readonly" value="${qty}" readonly>
                </td>

                <td class="am-td am-td--unit">
                    <input type="number" name="items[${idx}][unit_price]" class="am-input unit-price am-num am-readonly" value="${ht.toFixed(2)}" readonly>
                </td>

                <td class="am-td am-td--tax">
                    <input type="number" name="items[${idx}][tax_rate]" class="am-input tax-rate am-num am-readonly" value="${tax.toFixed(2)}" readonly>
                </td>

                <td class="am-td am-td--discType">
                    <select name="items[${idx}][line_discount_type]" class="am-input line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>

                <td class="am-td am-td--discVal">
                    <input type="number" step="0.01" min="0" name="items[${idx}][line_discount_value]" class="am-input line-discount-value am-num"
                           value="" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--taxAmt">
                    <input type="number" class="am-input tax-amt am-num am-readonly" readonly value="0.00">
                </td>

                <td class="am-td am-td--totalTtc">
                    <input type="number" class="am-input total-ttc am-num am-readonly" readonly value="0.00">
                </td>

                <td class="am-td am-td--act">
                    <button type="button" class="am-btn am-btn-danger am-icon" onclick="removeItem(this)" aria-label="{{ __('Supprimer') }}">×</button>
                </td>
            `;

            table.appendChild(row);
            recomputeAllTotals();
            closeInventoryModal();
        }

        // ---------- pack modal ----------
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

        function addPackItem() {
            const sel = document.getElementById('pack_product_id');
            const opt = sel.options[sel.selectedIndex];
            if (!opt.value) return;

            const name = opt.dataset.name || 'Pack';
            const ht   = _num(opt.dataset.price, 0);
            const tax  = _num(opt.dataset.tax, 0);

            const table = document.querySelector('#invoice-items-table tbody');
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
                    <input type="text" class="am-input custom-name" value="Pack : ${escapeHtml(name)}" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--desc">
                    <input type="text" class="am-input custom-details" value="" placeholder="{{ __('Détails (optionnel)') }}" oninput="recomputeAllTotals()">
                    <input type="hidden" name="items[${idx}][description]" class="custom-description-hidden" value="">
                </td>

                <td class="am-td am-td--qty">
                    <input type="number" step="0.01" min="0.01" name="items[${idx}][quantity]" class="am-input am-num"
                           value="1" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--unit">
                    <input type="number" step="0.01" min="0" name="items[${idx}][unit_price]" class="am-input unit-price am-num"
                           value="${ht.toFixed(2)}" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--tax">
                    <input type="number" step="0.01" min="0" name="items[${idx}][tax_rate]" class="am-input tax-rate am-num"
                           value="${tax.toFixed(2)}" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--discType">
                    <select name="items[${idx}][line_discount_type]" class="am-input line-discount-type" onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>

                <td class="am-td am-td--discVal">
                    <input type="number" step="0.01" min="0" name="items[${idx}][line_discount_value]" class="am-input line-discount-value am-num"
                           value="" inputmode="decimal" oninput="recomputeAllTotals()">
                </td>

                <td class="am-td am-td--taxAmt">
                    <input type="number" class="am-input tax-amt am-num am-readonly" readonly value="0.00">
                </td>

                <td class="am-td am-td--totalTtc">
                    <input type="number" class="am-input total-ttc am-num am-readonly" readonly value="0.00">
                </td>

                <td class="am-td am-td--act">
                    <button type="button" class="am-btn am-btn-danger am-icon" onclick="removeItem(this)" aria-label="{{ __('Supprimer') }}">×</button>
                </td>
            `;

            table.appendChild(row);
            recomputeAllTotals();
            closePackModal();
        }

        // ---------- remove ----------
        function removeItem(btn) {
            const tr = btn?.closest?.('tr');
            if (tr) tr.remove();
            recomputeAllTotals();
        }

        document.addEventListener('DOMContentLoaded', () => {
            recomputeAllTotals();

            // ✅ If coming from appointment "Facturer", auto-add a prestation line and preselect it
            const preselectedProductId = @json($preselectedProductId);
            const tbody = document.querySelector('#invoice-items-table tbody');
            const hasRows = tbody && tbody.querySelectorAll('tr').length > 0;

            if (preselectedProductId && !hasRows) {
                addProductItem();

                const lastRowSelect = tbody?.querySelector('tr:last-child select.product-select');
                if (lastRowSelect) {
                    lastRowSelect.value = String(preselectedProductId);
                    updateProductRow(lastRowSelect);
                }
            }

            const form = document.getElementById('invoiceForm');
            if (form) form.addEventListener('submit', () => syncCustomDescriptions());
        });
    </script>


    {{-- Select2 scripts --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        (function () {
            function initSelect2() {
                if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2) return;

                const $client = jQuery('.js-client-select');
                const $corp   = jQuery('.js-corporate-select');

                if ($client.length) {
                    $client.select2({
                        width: '100%',
                        placeholder: $client.data('placeholder') || 'Rechercher un client…'
                    });
                }
                if ($corp.length) {
                    $corp.select2({
                        width: '100%',
                        placeholder: $corp.data('placeholder') || 'Rechercher une entreprise…'
                    });
                }

                // keep Select2 in sync when billing_target toggles
                const applyDisabled = () => {
                    const target = (document.querySelector('input[name="billing_target"]:checked') || {}).value || 'client';
                    const clientDisabled = target !== 'client';
                    const corpDisabled   = target !== 'corporate';

                    $client.prop('disabled', clientDisabled).trigger('change.select2');
                    $corp.prop('disabled', corpDisabled).trigger('change.select2');
                };

                document.querySelectorAll('input[name="billing_target"]').forEach(r => {
                    r.addEventListener('change', applyDisabled);
                });
                applyDisabled();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSelect2);
            } else {
                initSelect2();
            }
        })();
    </script>

<style>
        /* Same UI system as quotes (full width, responsive, clean) */
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
        /* Select2 (am-input look) */
        .select2-container { width: 100% !important; }
        .select2-container .select2-selection--single{
            height: 44px;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            background: #fff;
            box-shadow: 0 8px 26px rgba(15, 23, 42, .05);
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered{
            line-height: 26px;
            color:#0f172a;
            padding-left: 0;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow{
            height: 42px;
            right: 10px;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder{
            color: rgba(15, 23, 42, .5);
        }
        .select2-dropdown{
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }
        .select2-search--dropdown .select2-search__field{
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            outline: none;
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
    </style>
</x-app-layout>
