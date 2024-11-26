<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer une facture') }}
        </h2>
    </x-slot>

    <div class="container-fluid mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouvelle Facture') }}</h1>

            <form action="{{ route('invoices.store') }}" method="POST">
                @csrf

                <!-- Wrap inputs in a fixed-width container -->
                <div class="input-section">

                    <!-- Sélection du Client -->
                    <div class="details-box">
                        <label class="details-label" for="client_profile_id">{{ __('Client') }}</label>
                        <select id="client_profile_id" name="client_profile_id" class="form-control" required>
                            <option value="">{{ __('Sélectionnez un client') }}</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_profile_id', $selectedClient->id ?? '') == $client->id ? 'selected' : '' }}>
                                    {{ $client->first_name }} {{ $client->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_profile_id')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date de Facture -->
                    <div class="details-box">
                        <label class="details-label" for="invoice_date">{{ __('Date de Facture') }}</label>
                        <input type="date" id="invoice_date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                        @error('invoice_date')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date d'échéance -->
                    <div class="details-box">
                        <label class="details-label" for="due_date">{{ __('Date d\'échéance') }}</label>
                        <input type="date" id="due_date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                        @error('due_date')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="details-box">
                        <label class="details-label" for="notes">{{ __('Notes') }}</label>
                        <textarea id="notes" name="notes" class="form-control">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Articles de la Facture -->
                <div class="details-box">
                    <label class="details-label">{{ __('Articles de la facture') }}</label>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="invoice-items-table">
                            <thead>
                                <tr>
                                    <th style="width: 35%;">{{ __('Produit') }}</th>
                                    <th style="width: 40%;">{{ __('Description') }}</th>
                                    <th style="width: 5%;">{{ __('Quantité') }}</th>
                                    <th style="width: 10%;">{{ __('Prix Unitaire') }}</th>
                                    <th style="width: 5%;">{{ __('Taxe (%)') }}</th>
                                    <th style="width: 10%;">{{ __('Montant Taxe') }}</th>
                                    <th style="width: 10%;">{{ __('Prix Total TTC') }}</th>
                                    <th style="width: 5%;">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Ligne d'article initiale -->
                                <tr>
                                    <td>
                                        <select name="items[0][product_id]" class="form-control product-select" onchange="updateItem(this)" data-preload="true">
                                            <option value="">{{ __('Sélectionnez un produit') }}</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-tax-rate="{{ $product->tax_rate }}"
                                                    {{ old('items.0.product_id', $selectedProduct->id ?? '') == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][description]" class="form-control description-input" value="{{ old('items.0.description') }}" placeholder="{{ __('Entrez la description') }}">
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control quantity-input" min="1" value="{{ old('items.0.quantity', 1) }}" onchange="updateItem(this)">
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][unit_price]" class="form-control unit-price-input" step="0.01" min="0" value="{{ old('items.0.unit_price') }}" onchange="updateItem(this)" data-manual="{{ old('items.0.unit_price') ? 'true' : 'false' }}">
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][tax_rate]" class="form-control tax-rate-input" step="0.01" readonly>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][tax_amount]" class="form-control tax-amount-input" readonly>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][total_price_with_tax]" class="form-control total-price-with-tax-input readonly-field" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger" onclick="removeItem(this)">-</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn-primary mt-2" onclick="addItem()">{{ __('Ajouter un article') }}</button>
                </div>

                <button type="submit" class="btn-primary mt-4">{{ __('Créer la Facture') }}</button>
                <a href="{{ route('invoices.index') }}" class="btn-secondary mt-4">{{ __('Retour à la liste') }}</a>
            </form>
        </div>
    </div>

    <!-- Scripts JavaScript pour gérer les articles -->
    <script>
        let itemIndex = 1; // Index pour les nouveaux articles

        function addItem() {
            const tableBody = document.querySelector('#invoice-items-table tbody');
            const newRow = document.createElement('tr');

            newRow.innerHTML = `
                <td>
                    <select name="items[\${itemIndex}][product_id]" class="form-control product-select" onchange="updateItem(this)">
                        <option value="">{{ __('Sélectionnez un produit') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-tax-rate="{{ $product->tax_rate }}">
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" name="items[\${itemIndex}][description]" class="form-control description-input" placeholder="{{ __('Entrez la description') }}">
                </td>
                <td>
                    <input type="number" name="items[\${itemIndex}][quantity]" class="form-control quantity-input" min="1" value="1" onchange="updateItem(this)">
                </td>
                <td>
                    <input type="number" name="items[\${itemIndex}][unit_price]" class="form-control unit-price-input" step="0.01" min="0" value="" onchange="updateItem(this)" data-manual="false">
                </td>
                <td>
                    <input type="number" name="items[\${itemIndex}][tax_rate]" class="form-control tax-rate-input" step="0.01" readonly>
                </td>
                <td>
                    <input type="number" name="items[\${itemIndex}][tax_amount]" class="form-control tax-amount-input" readonly>
                </td>
                <td>
                    <input type="number" name="items[\${itemIndex}][total_price_with_tax]" class="form-control total-price-with-tax-input readonly-field" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-danger" onclick="removeItem(this)">-</button>
                </td>
            `;

            tableBody.appendChild(newRow);
            itemIndex++;
        }

        function removeItem(button) {
            const row = button.closest('tr');
            row.remove();
            recalculateTotals(); // Optional: Recalculate invoice totals if you have a summary
        }

        function updateItem(element) {
            const row = element.closest('tr');
            const productSelect = row.querySelector('.product-select');
            const descriptionInput = row.querySelector('.description-input');
            const quantityInput = row.querySelector('.quantity-input');
            const unitPriceInput = row.querySelector('.unit-price-input');
            const taxRateInput = row.querySelector('.tax-rate-input');
            const taxAmountInput = row.querySelector('.tax-amount-input');
            const totalPriceWithTaxInput = row.querySelector('.total-price-with-tax-input');

            // Update unit price, description, and tax rate when a product is selected
            if (element.classList.contains('product-select')) {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                const taxRate = parseFloat(selectedOption.getAttribute('data-tax-rate')) || 0;
                descriptionInput.value = selectedOption.text;

                // Only set unit price if it's not manually changed
                if (unitPriceInput.dataset.manual !== 'true') {
                    unitPriceInput.value = price.toFixed(2);
                }

                taxRateInput.value = taxRate.toFixed(2);
            }

            // Mark unit price as manually edited if user changes it
            unitPriceInput.addEventListener('input', function() {
                this.dataset.manual = 'true';
            }, { once: true });

            // Calculate tax amount and total price with tax
            const quantity = parseFloat(quantityInput.value) || 1;
            const unitPrice = parseFloat(unitPriceInput.value) || 0;
            const taxRate = parseFloat(taxRateInput.value) || 0;

            const totalPrice = quantity * unitPrice;
            const taxAmount = (totalPrice * taxRate) / 100;
            const totalPriceWithTax = totalPrice + taxAmount;

            taxAmountInput.value = taxAmount.toFixed(2);
            totalPriceWithTaxInput.value = totalPriceWithTax.toFixed(2);

            recalculateTotals(); // Optional: Recalculate invoice totals if you have a summary
        }

        function recalculateTotals() {
            // Implement this function if you have a summary section to display total invoice amounts
            // Example:
            /*
            let totalAmount = 0;
            let totalTaxAmount = 0;
            let totalWithTax = 0;

            document.querySelectorAll('#invoice-items-table tbody tr').forEach(function(row) {
                const taxAmount = parseFloat(row.querySelector('.tax-amount-input').value) || 0;
                const totalPriceWithTax = parseFloat(row.querySelector('.total-price-with-tax-input').value) || 0;

                totalTaxAmount += taxAmount;
                totalWithTax += totalPriceWithTax;
            });

            // Update summary fields
            document.getElementById('total_tax_amount').innerText = totalTaxAmount.toFixed(2);
            document.getElementById('total_with_tax').innerText = totalWithTax.toFixed(2);
            */
        }

        // Trigger product selection to preload data
        window.onload = function() {
            const preloadedProducts = document.querySelectorAll('[data-preload="true"]');
            preloadedProducts.forEach(function(productSelect) {
                updateItem(productSelect);
            });
        };
    </script>

    <!-- Styles Personnalisés -->
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
            /* Removed or adjusted max-width */
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
