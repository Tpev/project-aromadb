<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer un devis') }}
        </h2>
    </x-slot>

    <div class="container-fluid mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouveau Devis') }}</h1>

            @if ($errors->any())
                <div class="mb-4 text-red-500">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('invoices.storeQuote') }}" method="POST">
                @csrf

                {{-- Métadonnées du devis --}}
                <div class="input-section">
                    <div class="details-box">
                        <label class="details-label" for="client_profile_id">{{ __('Client / Entreprise') }}</label>
                        <select id="client_profile_id" name="client_profile_id" class="form-control" required>
                            <option value="">{{ __('Sélectionnez un client') }}</option>
                            @foreach($clients as $client)
                                @php
                                    $company = $client->company ?? null;

                                    $billingFirst = $client->first_name_billing ?: $client->first_name;
                                    $billingLast  = $client->last_name_billing  ?: $client->last_name;

                                    if ($company) {
                                        // Ex: ACME SA – Jean Dupont
                                        $label = $company->name . ' – ' . trim($billingFirst.' '.$billingLast);
                                    } else {
                                        // Client normal : Prénom Nom
                                        $label = trim($client->first_name.' '.$client->last_name);
                                    }
                                @endphp
                                <option
                                    value="{{ $client->id }}"
                                    {{ old('client_profile_id') == $client->id ? 'selected' : '' }}
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="details-box">
                        <label class="details-label" for="quote_date">{{ __('Date du Devis') }}</label>
                        <input type="date" id="quote_date" name="quote_date" class="form-control"
                               value="{{ old('quote_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="details-box">
                        <label class="details-label" for="valid_until">{{ __('Valable Jusqu\'au') }}</label>
                        <input type="date" id="valid_until" name="valid_until" class="form-control"
                               value="{{ old('valid_until') }}">
                    </div>
                    <div class="details-box">
                        <label class="details-label" for="notes">{{ __('Notes') }}</label>
                        <textarea id="notes" name="notes" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Tableau des articles --}}
                <div class="details-box">
                    <label class="details-label">{{ __('Articles du devis') }}</label>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="quote-items-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Produit / Article') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Quantité') }}</th>
                                    <th>{{ __('P.U. HT (€)') }}</th>
                                    <th>{{ __('TVA (%)') }}</th>
                                    <th>{{ __('Montant TVA (€)') }}</th>
                                    <th>{{ __('Total TTC (€)') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="flex gap-2 mt-2">
                        <button type="button" class="btn-primary" onclick="addProductItem()">
                            {{ __('Ajouter une prestation') }}
                        </button>
                        <button type="button" class="btn-primary" onclick="openInventoryModal()">
                            {{ __('Ajouter depuis l\'inventaire') }}
                        </button>
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

    {{-- JS commun à invoice/create --}}
    <script>
        let itemIndex = 0;

        function addProductItem() {
            const tbody = document.querySelector('#quote-items-table tbody');
            const idx   = itemIndex++;
            const row   = document.createElement('tr');

            row.innerHTML = `
                <td>Prest.</td>
                <td>
                  <input type="hidden" name="items[${idx}][type]" value="product">
                  <select name="items[${idx}][product_id]" class="form-control product-select" onchange="updateRow(this)">
                      <option value="">{{ __('Sélectionnez') }}</option>
                      @foreach($products as $p)
                          <option value="{{ $p->id }}"
                              data-price="{{ $p->price }}"
                              data-tax="{{ $p->tax_rate }}"
                          >{{ $p->name }}</option>
                      @endforeach
                  </select>
                </td>
                <td><input type="text" name="items[${idx}][description]" class="form-control"></td>
                <td><input type="number" name="items[${idx}][quantity]" class="form-control" value="1" min="1" onchange="updateRow(this)"></td>
                <td><input type="number" name="items[${idx}][unit_price]" class="form-control unit-price" readonly></td>
                <td><input type="number" name="items[${idx}][tax_rate]" class="form-control tax-rate" readonly></td>
                <td><input type="number" name="items[${idx}][tax_amount]" class="form-control tax-amt" readonly></td>
                <td><input type="number" name="items[${idx}][total_price_with_tax]" class="form-control total-ttc" readonly></td>
                <td><button type="button" class="btn btn-danger" onclick="removeItem(this)">×</button></td>
            `;
            tbody.appendChild(row);
        }

        function addInventoryItem() {
            const sel = document.getElementById('inventory_item_id');
            const opt = sel.options[sel.selectedIndex];
            const qty = parseFloat(document.getElementById('inventory_quantity').value) || 1;
            if (!opt.value) return;

            const ttc = opt.dataset.unitType === 'ml'
                ? parseFloat(opt.dataset.ttcPerMl)
                : parseFloat(opt.dataset.ttcUnit);
            const tax = parseFloat(opt.dataset.tax) || 0;
            const ht  = ttc / (1 + tax / 100);

            const tbody = document.querySelector('#quote-items-table tbody');
            const idx   = itemIndex++;
            const row   = document.createElement('tr');

            row.innerHTML = `
                <td>Inv.</td>
                <td>
                  <input type="hidden" name="items[${idx}][type]" value="inventory">
                  <input type="hidden" name="items[${idx}][inventory_item_id]" value="${opt.value}">
                  ${opt.text}
                </td>
                <td><input type="text" name="items[${idx}][description]" class="form-control" value="${opt.dataset.name}" readonly></td>
                <td><input type="number" name="items[${idx}][quantity]" class="form-control" value="${qty}" readonly></td>
                <td><input type="number" name="items[${idx}][unit_price]" class="form-control unit-price" value="${ht.toFixed(2)}" readonly></td>
                <td><input type="number" name="items[${idx}][tax_rate]" class="form-control tax-rate" value="${tax.toFixed(2)}" readonly></td>
                <td><input type="number" name="items[${idx}][tax_amount]" class="form-control tax-amt" value="${(ht*qty*(tax/100)).toFixed(2)}" readonly></td>
                <td><input type="number" name="items[${idx}][total_price_with_tax]" class="form-control total-ttc" value="${(ttc*qty).toFixed(2)}" readonly></td>
                <td><button type="button" class="btn btn-danger" onclick="removeItem(this)">×</button></td>
            `;
            tbody.appendChild(row);
            closeInventoryModal();
        }

        function updateRow(el) {
            const r       = el.closest('tr');
            const productSelect = r.querySelector('.product-select');
            const selected      = productSelect ? productSelect.selectedOptions[0] : null;

            const price   = selected ? parseFloat(selected.dataset.price) || 0 : 0;
            const tax     = selected ? parseFloat(selected.dataset.tax)   || 0 : 0;
            const qty     = parseFloat(r.querySelector('input[name*="[quantity]"]').value) || 1;
            const ht      = price;
            const amt     = ht * qty * (tax / 100);
            const ttc     = ht * qty + amt;

            r.querySelector('.unit-price').value  = ht.toFixed(2);
            r.querySelector('.tax-rate').value    = tax.toFixed(2);
            r.querySelector('.tax-amt').value     = amt.toFixed(2);
            r.querySelector('.total-ttc').value   = ttc.toFixed(2);
        }

        function removeItem(btn) {
            btn.closest('tr').remove();
        }

        function openInventoryModal(){ document.getElementById('inventoryModal').classList.remove('hidden'); }
        function closeInventoryModal(){ document.getElementById('inventoryModal').classList.add('hidden'); }
    </script>

    <style>
        .container-fluid {
            max-width: 1200px;
        }

        .input-section {
            max-width: 600px;
            margin-bottom: 30px;
        }

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

        .details-box {
            margin-bottom: 15px;
        }

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

        .btn-primary:hover {
            background-color: #854f38;
        }

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

        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
        }

        #quote-items-table {
            width: 100%;
            margin-bottom: 15px;
            table-layout: auto;
        }

        #quote-items-table th, #quote-items-table td {
            padding: 8px;
            text-align: left;
        }

        #quote-items-table th {
            background-color: #647a0b;
            color: #fff;
            white-space: nowrap;
        }

        #quote-items-table td {
            border-bottom: 1px solid #ccc;
        }

        #quote-items-table td input,
        #quote-items-table td select {
            width: 100%;
        }

        .btn-danger {
            background-color: #e3342f;
            color: #fff;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-danger:hover {
            background-color: #cc1f1a;
        }

        .readonly-field {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .details-title {
                font-size: 1.5rem;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
                text-align: center;
                margin-bottom: 10px;
            }

            #quote-items-table th, #quote-items-table td {
                padding: 6px;
            }
        }
    </style>
</x-app-layout>
