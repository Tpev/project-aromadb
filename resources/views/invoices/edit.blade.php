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

            <form action="{{ route('invoices.update', $invoice) }}" method="POST">
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
                                    <th>{{ __('Montant Taxe (€)') }}</th>
                                    <th>{{ __('Total TTC (€)') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $i => $item)
                                    <tr>
                                        {{-- hidden type --}}
                                        <input type="hidden" name="items[{{ $i }}][type]" value="{{ $item->type }}">

                                        <td class="align-middle text-center">
                                            {{ ucfirst($item->type) }}
                                        </td>

                                        {{-- product select or inventory --}}
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
                                            @else
                                                <em>{{ __('Personnalisé') }}</em>
                                            @endif
                                        </td>

                                        {{-- common fields --}}
                                        <td>
                                            <input type="text" name="items[{{ $i }}][description]"
                                                   class="form-control description-input"
                                                   value="{{ old("items.$i.description", $item->description) }}">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $i }}][quantity]"
                                                   class="form-control quantity-input"
                                                   min="1" value="{{ old("items.$i.quantity", $item->quantity) }}"
                                                   onchange="updateItem(this)">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $i }}][unit_price]"
                                                   class="form-control unit-price-input"
                                                   step="0.01" min="0"
                                                   value="{{ old("items.$i.unit_price", $item->unit_price) }}"
                                                   onchange="updateItem(this)" data-manual="true">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $i }}][tax_rate]"
                                                   class="form-control tax-rate-input"
                                                   step="0.01" readonly
                                                   value="{{ old("items.$i.tax_rate", $item->tax_rate) }}">
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

                    <div class="mt-3 flex gap-2">
                        <button type="button" class="btn-primary" onclick="addProductItem()">{{ __('Ajouter Produit') }}</button>
                        <button type="button" class="btn-primary" onclick="addInventoryItem()">{{ __('Ajouter Inventaire') }}</button>
                     
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn-primary">{{ __('Mettre à jour la Facture') }}</button>
                    <a href="{{ route('invoices.show', $invoice) }}" class="btn-secondary">{{ __('Annuler') }}</a>
                </div>
            </form>
        </div>
    </div>

    {{-- JavaScript --}}
    <script>
        let itemIndex = {{ $invoice->items->count() }};

        function removeItem(btn) {
            btn.closest('tr').remove();
        }

        function addProductItem() {
            const tbody = document.querySelector('#invoice-items-table tbody');
            const row = document.createElement('tr');
            row.innerHTML = `
                <input type="hidden" name="items[${itemIndex}][type]" value="product">
                <td class="text-center">{{ __('Produit') }}</td>
                <td>
                    <select name="items[${itemIndex}][product_id]" class="form-control product-select" onchange="updateItem(this)">
                        <option value="">{{ __('Sélectionnez un produit') }}</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}"
                                    data-price="{{ $prod->price }}"
                                    data-tax-rate="{{ $prod->tax_rate }}">
                                {{ $prod->name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="items[${itemIndex}][description]" class="form-control description-input"></td>
                <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity-input" value="1" min="1" onchange="updateItem(this)"></td>
                <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price-input" step="0.01" min="0" onchange="updateItem(this)" data-manual="false"></td>
                <td><input type="number" name="items[${itemIndex}][tax_rate]" class="form-control tax-rate-input" readonly></td>
                <td><input type="number" name="items[${itemIndex}][tax_amount]" class="form-control tax-amount-input" readonly></td>
                <td><input type="number" name="items[${itemIndex}][total_price_with_tax]" class="form-control total-price-with-tax-input readonly-field" readonly></td>
                <td><button type="button" class="btn btn-danger" onclick="removeItem(this)">-</button></td>
            `;
            tbody.append(row);
            itemIndex++;
        }

        function addInventoryItem() {
            const tbody = document.querySelector('#invoice-items-table tbody');
            const row = document.createElement('tr');
            row.innerHTML = `
                <input type="hidden" name="items[${itemIndex}][type]" value="inventory">
                <td class="text-center">{{ __('Inventaire') }}</td>
                <td>
                    <select name="items[${itemIndex}][inventory_item_id]" class="form-control inventory-select" onchange="updateInventoryItem(this)">
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
                </td>
                <td><input type="text" name="items[${itemIndex}][description]" class="form-control description-input"></td>
                <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity-input" value="1" min="1" onchange="updateInventoryItem(this)"></td>
                <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price-input" step="0.01" min="0" readonly></td>
                <td><input type="number" name="items[${itemIndex}][tax_rate]" class="form-control tax-rate-input" readonly></td>
                <td><input type="number" name="items[${itemIndex}][tax_amount]" class="form-control tax-amount-input" readonly></td>
                <td><input type="number" name="items[${itemIndex}][total_price_with_tax]" class="form-control total-price-with-tax-input readonly-field" readonly></td>
                <td><button type="button" class="btn btn-danger" onclick="removeItem(this)">-</button></td>
            `;
            tbody.append(row);
            itemIndex++;
        }

        function addCustomItem() {
            const tbody = document.querySelector('#invoice-items-table tbody');
            const row = document.createElement('tr');
            row.innerHTML = `
                <input type="hidden" name="items[${itemIndex}][type]" value="custom">
                <td class="text-center">{{ __('Personnalisé') }}</td>
                <td><input type="text" name="items[${itemIndex}][description]" class="form-control description-input"></td>
                <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity-input" value="1" min="1" onchange="updateItem(this)"></td>
                <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price-input" step="0.01" min="0" onchange="updateItem(this)" data-manual="true"></td>
                <td><input type="number" name="items[${itemIndex}][tax_rate]" class="form-control tax-rate-input" readonly value="0"></td>
                <td><input type="number" name="items[${itemIndex}][tax_amount]" class="form-control tax-amount-input" readonly></td>
                <td><input type="number" name="items[${itemIndex}][total_price_with_tax]" class="form-control total-price-with-tax-input readonly-field" readonly></td>
                <td><button type="button" class="btn btn-danger" onclick="removeItem(this)">-</button></td>
            `;
            tbody.append(row);
            itemIndex++;
        }

        function updateItem(el) {
            const row = el.closest('tr');
            const qty = parseFloat(row.querySelector('.quantity-input').value) || 1;
            const price = parseFloat(row.querySelector('.unit-price-input').value) || 0;
            const tax = parseFloat(row.querySelector('.tax-rate-input').value) || 0;
            const totalHt = qty * price;
            const taxAmt = totalHt * (tax/100);
            row.querySelector('.tax-amount-input').value = taxAmt.toFixed(2);
            row.querySelector('.total-price-with-tax-input').value = (totalHt + taxAmt).toFixed(2);
        }
        function updateInventoryItem(el) {
            const row = el.closest('tr');
            const sel = row.querySelector('.inventory-select');
            const opt = sel.options[sel.selectedIndex];
            const unitType = opt.dataset.unitType;
            const priceTtc = unitType === 'unit'
                ? parseFloat(opt.dataset.priceUnit)
                : parseFloat(opt.dataset.priceMl);
            const tax = parseFloat(opt.dataset.taxRate) || 0;
            const qty = parseFloat(row.querySelector('.quantity-input').value) || 1;
            const priceHt = priceTtc / (1 + tax/100);
            row.querySelector('.unit-price-input').value = priceHt.toFixed(2);
            row.querySelector('.tax-rate-input').value = tax.toFixed(2);
            row.querySelector('.tax-amount-input').value = (priceHt*qty*(tax/100)).toFixed(2);
            row.querySelector('.total-price-with-tax-input').value = (priceTtc*qty).toFixed(2);
        }

        window.onload = () => {
            document.querySelectorAll('.product-select[data-preload="true"]').forEach(updateItem);
            document.querySelectorAll('.inventory-select[data-preload="true"]').forEach(updateInventoryItem);
        };
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

        /* Styles pour la table des articles */
        #invoice-items-table {
            width: 100%;
            margin-bottom: 15px;
            table-layout: auto; /* Better layout */
        }

        #invoice-items-table th, #invoice-items-table td {
            padding: 8px;
            text-align: left;
        }

        #invoice-items-table th {
            background-color: #647a0b;
            color: #fff;
            white-space: nowrap;
        }

        #invoice-items-table td {
            border-bottom: 1px solid #ccc;
        }

        #invoice-items-table td input,
        #invoice-items-table td select {
            width: 100%; /* Ensure full width */
            /* Removed or adjusted max-width to allow expansion */
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

        /* Custom class to grey out readonly fields */
        .readonly-field {
            background-color: #e9ecef; /* Light grey background */
            cursor: not-allowed; /* Indicate non-editable */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .details-title {
                font-size: 1.5rem;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
                text-align: center;
                margin-bottom: 10px;
            }

            #invoice-items-table th, #invoice-items-table td {
                padding: 6px;
            }
        }
    </style>
</x-app-layout>
