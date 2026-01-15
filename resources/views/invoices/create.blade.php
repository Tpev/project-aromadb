<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer une facture') }}
        </h2>
    </x-slot>

    <div class="container-fluid mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouvelle Facture') }}</h1>

            @if ($errors->any())
                <div class="mb-4 text-red-500">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                // Avoid "Undefined variable $selectedClient"
                $selectedClientId = old('client_profile_id', isset($selectedClient) ? $selectedClient->id : null);
            @endphp

            <form id="invoiceForm" action="{{ route('invoices.store') }}" method="POST">
                @csrf

                <!-- Métadonnées de la facture -->
                <div class="input-section">
                    <div class="details-box">
                        <label class="details-label" for="client_profile_id">{{ __('Client') }}</label>
                        <select id="client_profile_id" name="client_profile_id" class="form-control" required>
                            <option value="">{{ __('Sélectionnez un client') }}</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}"
                                        {{ (string)$selectedClientId === (string)$client->id ? 'selected' : '' }}>
                                    {{ $client->first_name }} {{ $client->last_name }}
                                    @if($client->company)
                                        — 👔 {{ $client->company->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="details-box">
                        <label class="details-label" for="invoice_date">{{ __('Date de Facture') }}</label>
                        <input type="date" id="invoice_date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="details-box">
                        <label class="details-label" for="due_date">{{ __('Date d\'échéance') }}</label>
                        <input type="date" id="due_date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                    </div>

                    <div class="details-box">
                        <label class="details-label" for="notes">{{ __('Notes') }}</label>
                        <textarea id="notes" name="notes" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Tableau des articles -->
                <div class="details-box">
                    <label class="details-label">{{ __('Articles de la facture') }}</label>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="invoice-items-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Produit / Article') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Quantité') }}</th>
                                    <th>{{ __('P.U. HT (€)') }}</th>
                                    <th>{{ __('TVA (%)') }}</th>
                                    <th>{{ __('Remise') }}</th>
                                    <th>{{ __('Valeur remise') }}</th>
                                    <th>{{ __('Montant TVA (€)') }}</th>
                                    <th>{{ __('Total TTC (€)') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- on part vide, on ajoute via JS --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="flex gap-2 mt-2 flex-wrap">
                        <button type="button" class="btn-primary" onclick="addProductItem()">
                            {{ __('Ajouter une prestation') }}
                        </button>

                        <button type="button" class="btn-primary" onclick="openInventoryModal()">
                            {{ __('Ajouter depuis l\'inventaire') }}
                        </button>

                        {{-- pack --}}
                        <button type="button" class="btn-primary" onclick="openPackModal()">
                            {{ __('Ajouter un pack') }}
                        </button>

                        {{-- custom --}}
                        <button type="button" class="btn-primary" onclick="addCustomItem()">
                            {{ __('Ajouter une ligne libre') }}
                        </button>
                    </div>
                </div>

                <!-- Remise globale + Totaux -->
                <div class="mt-4 p-4 bg-white rounded-lg border" style="border-color: rgba(100,122,11,0.25);">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="details-label">{{ __('Remise globale') }}</label>
                            <select id="global_discount_type" name="global_discount_type" class="form-control" onchange="recomputeAllTotals()">
                                <option value="">{{ __('Aucune') }}</option>
                                <option value="percent" {{ old('global_discount_type') === 'percent' ? 'selected' : '' }}>%</option>
                                <option value="amount" {{ old('global_discount_type') === 'amount' ? 'selected' : '' }}>€</option>
                            </select>
                        </div>
                        <div>
                            <label class="details-label">{{ __('Valeur') }}</label>
                            <input id="global_discount_value" type="number" step="0.01" min="0" name="global_discount_value" class="form-control"
                                   value="{{ old('global_discount_value') }}" oninput="recomputeAllTotals()">
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
                        <div class="flex justify-between md:col-span-1"><span>{{ __('Total TTC') }}</span><strong><span id="ui_total_ttc">0.00</span> €</strong></div>
                    </div>
                </div>

                <button type="submit" class="btn-primary mt-4">{{ __('Créer la Facture') }}</button>
                <a href="{{ route('invoices.index') }}" class="btn-secondary mt-4">{{ __('Retour à la liste') }}</a>
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
                <input type="number" id="inventory_quantity" class="form-control" min="1" value="1">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" class="btn-secondary" onclick="closeInventoryModal()">
                    {{ __('Annuler') }}
                </button>
                <button type="button" class="btn-primary" onclick="addInventoryItem()">
                    {{ __('Ajouter') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Modal packs --}}
    <div id="packModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-xl shadow-lg">
            <h2 class="text-xl font-semibold mb-4 text-[#647a0b]">
                {{ __('Ajouter un pack') }}
            </h2>

            <div class="mb-4">
                <label class="block font-semibold">{{ __('Pack') }}</label>
                <select id="pack_product_id" class="form-control">
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
                <p class="text-xs text-slate-500 mt-2">
                    {{ __('Le pack sera ajouté comme une ligne personnalisée (facturation).') }}
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" class="btn-secondary" onclick="closePackModal()">
                    {{ __('Annuler') }}
                </button>
                <button type="button" class="btn-primary" onclick="addPackItem()">
                    {{ __('Ajouter') }}
                </button>
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
                else hiddenDescEl.value = details; // fallback
            });
        }

        // ---------- totals ----------
        function recomputeAllTotals() {
            // Make sure custom hidden descriptions are up to date (no impact on totals, but good habit)
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
                <td>
                    <input type="hidden" name="items[${idx}][type]" value="product">
                    Prest.
                </td>
                <td>
                    <select name="items[${idx}][product_id]" class="form-control product-select" onchange="updateProductRow(this)">
                        <option value="">{{ __('Sélectionnez') }}</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}"
                                data-price="{{ $p->price }}"
                                data-tax="{{ $p->tax_rate }}"
                            >{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="">
                </td>
                <td><input type="text" name="items[${idx}][description]" class="form-control" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][quantity]" class="form-control" value="1" min="0.01" step="0.01" oninput="recomputeAllTotals()"></td>
                <td><input type="number" name="items[${idx}][unit_price]" class="form-control unit-price" readonly value="0.00"></td>
                <td><input type="number" name="items[${idx}][tax_rate]" class="form-control tax-rate" readonly value="0.00"></td>

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

                <td><input type="number" class="form-control tax-amt" readonly value="0.00"></td>
                <td><input type="number" class="form-control total-ttc" readonly value="0.00"></td>
                <td><button type="button" class="btn btn-danger" onclick="removeItem(this)">×</button></td>
            `;

            table.appendChild(row);
            recomputeAllTotals();
        }

        function addCustomItem() {
            const table = document.querySelector('#invoice-items-table tbody');
            const idx = itemIndex++;
            const row = document.createElement('tr');

            row.innerHTML = `
                <td>
                    <input type="hidden" name="items[${idx}][type]" value="custom">
                    Libre
                </td>

                <td>
                    <input type="hidden" name="items[${idx}][product_id]" value="">
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="">

                    <input type="text"
                           class="form-control custom-name"
                           placeholder="Ex: Consultation, Atelier, Prestation…"
                           oninput="recomputeAllTotals()">
                </td>

                <td>
                    <input type="text"
                           class="form-control custom-details"
                           placeholder="Détails (optionnel)"
                           oninput="recomputeAllTotals()">

                    {{-- ✅ real field saved in DB --}}
                    <input type="hidden"
                           name="items[${idx}][description]"
                           class="custom-description-hidden"
                           value="">
                </td>

                <td>
                    <input type="number" step="0.01" min="0.01"
                           name="items[${idx}][quantity]" class="form-control"
                           value="1" oninput="recomputeAllTotals()">
                </td>

                <td>
                    <input type="number" step="0.01" min="0"
                           name="items[${idx}][unit_price]" class="form-control unit-price"
                           value="0.00" oninput="recomputeAllTotals()">
                </td>

                <td>
                    <input type="number" step="0.01" min="0"
                           name="items[${idx}][tax_rate]" class="form-control tax-rate"
                           value="0.00" oninput="recomputeAllTotals()">
                </td>

                <td>
                    <select name="items[${idx}][line_discount_type]" class="form-control line-discount-type"
                            onchange="recomputeAllTotals()">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">€</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" min="0"
                           name="items[${idx}][line_discount_value]"
                           class="form-control line-discount-value"
                           value="" oninput="recomputeAllTotals()">
                </td>

                <td><input type="number" class="form-control tax-amt" readonly value="0.00"></td>
                <td><input type="number" class="form-control total-ttc" readonly value="0.00"></td>
                <td><button type="button" class="btn btn-danger" onclick="removeItem(this)">×</button></td>
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
        function openInventoryModal() { document.getElementById('inventoryModal').classList.remove('hidden'); }
        function closeInventoryModal() { document.getElementById('inventoryModal').classList.add('hidden'); }

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
                <td>
                    <input type="hidden" name="items[${idx}][type]" value="inventory">
                    Inv.
                </td>

                <td>
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="${opt.value}">
                    <input type="hidden" name="items[${idx}][product_id]" value="">
                    ${escapeHtml(opt.text)}
                </td>

                <td>
                    <input type="text" name="items[${idx}][description]" class="form-control"
                           value="${escapeHtml(opt.dataset.name || '')}" readonly>
                </td>

                <td><input type="number" name="items[${idx}][quantity]" class="form-control" value="${qty}" readonly></td>
                <td><input type="number" name="items[${idx}][unit_price]" class="form-control unit-price" value="${ht.toFixed(2)}" readonly></td>
                <td><input type="number" name="items[${idx}][tax_rate]" class="form-control tax-rate" value="${tax.toFixed(2)}" readonly></td>

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

                <td><input type="number" class="form-control tax-amt" readonly value="0.00"></td>
                <td><input type="number" class="form-control total-ttc" readonly value="0.00"></td>
                <td><button type="button" class="btn btn-danger" onclick="removeItem(this)">×</button></td>
            `;

            table.appendChild(row);
            recomputeAllTotals();
            closeInventoryModal();
        }

        // ---------- pack modal ----------
        function openPackModal() { document.getElementById('packModal').classList.remove('hidden'); }
        function closePackModal() { document.getElementById('packModal').classList.add('hidden'); }

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
                <td>
                    <input type="hidden" name="items[${idx}][type]" value="custom">
                    Pack
                </td>

                <td>
                    <input type="hidden" name="items[${idx}][product_id]" value="">
                    <input type="hidden" name="items[${idx}][inventory_item_id]" value="">
                    <input type="text" class="form-control custom-name" value="Pack : ${escapeHtml(name)}" oninput="recomputeAllTotals()">
                </td>

                <td>
                    <input type="text" class="form-control custom-details" value="" placeholder="Détails (optionnel)" oninput="recomputeAllTotals()">
                    <input type="hidden" name="items[${idx}][description]" class="custom-description-hidden" value="">
                </td>

                <td><input type="number" step="0.01" min="0.01" name="items[${idx}][quantity]" class="form-control" value="1" oninput="recomputeAllTotals()"></td>
                <td><input type="number" step="0.01" min="0" name="items[${idx}][unit_price]" class="form-control unit-price" value="${ht.toFixed(2)}" oninput="recomputeAllTotals()"></td>
                <td><input type="number" step="0.01" min="0" name="items[${idx}][tax_rate]" class="form-control tax-rate" value="${tax.toFixed(2)}" oninput="recomputeAllTotals()"></td>

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

                <td><input type="number" class="form-control tax-amt" readonly value="0.00"></td>
                <td><input type="number" class="form-control total-ttc" readonly value="0.00"></td>
                <td><button type="button" class="btn btn-danger" onclick="removeItem(this)">×</button></td>
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

        // submit: ensure custom descriptions are synced
        document.addEventListener('DOMContentLoaded', () => {
            recomputeAllTotals();

            const form = document.getElementById('invoiceForm');
            if (form) {
                form.addEventListener('submit', () => {
                    syncCustomDescriptions();
                });
            }
        });
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

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
        }

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

        @media (max-width: 768px) {
            .details-title { font-size: 1.5rem; }
            .btn-primary, .btn-secondary { width: 100%; text-align: center; margin-bottom: 10px; }
            #invoice-items-table th, #invoice-items-table td { padding: 6px; }
        }
    </style>
</x-app-layout>
