<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Modifier un Article d\'Inventaire') }}
        </h2>
    </x-slot>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .container { max-width: 800px; }
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
        .details-box { margin-bottom: 20px; }
        .details-label {
            font-weight: bold;
            color: #647a0b;
            display: block;
            margin-bottom: 5px;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #854f38;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn-primary, .btn-secondary {
            background-color: #647a0b;
            border: none;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-primary:hover, .btn-secondary:hover {
            background-color: #854f38;
        }
        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
            margin-top: 5px;
        }
    </style>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">Modifier un Article d'Inventaire</h1>

            @if(session('success'))
                <div class="alert alert-success text-center">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger text-center">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('inventory_items.update', $inventoryItem->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="details-box">
                    <label class="details-label" for="name">Nom de l'Article</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $inventoryItem->name) }}" required>
                </div>

                <div class="details-box">
                    <label class="details-label" for="reference">Référence</label>
                    <input type="text" id="reference" name="reference" class="form-control" value="{{ old('reference', $inventoryItem->reference) }}" required>
                </div>

                <div class="details-box">
                    <label class="details-label" for="description">Description</label>
                    <textarea id="description" name="description" class="form-control">{{ old('description', $inventoryItem->description) }}</textarea>
                </div>

                <div class="details-box">
                    <label class="details-label" for="price">Prix d'Achat (€)</label>
                    <input type="number" step="0.01" id="price" name="price" class="form-control" value="{{ old('price', $inventoryItem->price) }}">
                </div>

                <div class="details-box">
                    <label class="details-label" for="selling_price">Prix de Vente (€)</label>
                    <input type="number" step="0.01" id="selling_price" name="selling_price" class="form-control" value="{{ old('selling_price', $inventoryItem->selling_price) }}">
                </div>
<!-- TVA à l'achat -->
<div class="mb-4">
    <label for="vat_rate_purchase" class="block text-sm font-medium text-gray-700">TVA à l'achat (%)</label>
    <input
        type="number"
        name="vat_rate_purchase"
        id="vat_rate_purchase"
        step="0.01"
        min="0"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#647a0b] focus:border-[#647a0b] sm:text-sm"
        value="{{ old('vat_rate_purchase', $inventoryItem->vat_rate_purchase ?? '') }}"
    >
</div>

<!-- TVA à la vente -->
<div class="mb-4">
    <label for="vat_rate_sale" class="block text-sm font-medium text-gray-700">TVA à la vente (%)</label>
    <input
        type="number"
        name="vat_rate_sale"
        id="vat_rate_sale"
        step="0.01"
        min="0"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#647a0b] focus:border-[#647a0b] sm:text-sm"
        value="{{ old('vat_rate_sale', $inventoryItem->vat_rate_sale ?? '') }}"
    >
</div>




                <div class="details-box">
                    <label class="details-label" for="unit_type">Type d’Unité</label>
                    <select name="unit_type" id="unit_type" class="form-control" required>
                        <option value="unit" {{ old('unit_type', $inventoryItem->unit_type) == 'unit' ? 'selected' : '' }}>Unité</option>
                        <option value="ml" {{ old('unit_type', $inventoryItem->unit_type) == 'ml' ? 'selected' : '' }}>Millilitre (ml)</option>
                        <option value="gramme" {{ old('unit_type', $inventoryItem->unit_type) == 'gramme' ? 'selected' : '' }}>Gramme (g)</option>
                    </select>
                </div>

                <div class="details-box" id="stock_quantity_box">
                    <label class="details-label" for="quantity_in_stock">Quantité en Stock</label>
                    <input type="number" id="quantity_in_stock" name="quantity_in_stock" class="form-control" value="{{ old('quantity_in_stock', $inventoryItem->quantity_in_stock) }}">
                </div>

                <div class="details-box" id="ml_fields_box">
                    <label class="details-label" for="quantity_per_unit">Contenu Total (ml ou g)</label>
                    <input type="number" step="0.01" id="quantity_per_unit" name="quantity_per_unit" class="form-control" value="{{ old('quantity_per_unit', $inventoryItem->quantity_per_unit) }}">
                </div>

                <div class="details-box" id="ml_fields_remaining">
                    <label class="details-label" for="quantity_remaining">Quantité Restante</label>
                    <input type="number" step="0.01" id="quantity_remaining" name="quantity_remaining" class="form-control" value="{{ old('quantity_remaining', $inventoryItem->quantity_remaining) }}">
                </div>

                <div class="details-box" id="price_per_ml_box">
                    <label class="details-label" for="price_per_ml">Prix d'Achat par ml (€)</label>
                    <input type="number" step="0.01" id="price_per_ml" name="price_per_ml" class="form-control" value="{{ old('price_per_ml', $inventoryItem->price_per_ml) }}">
                </div>

                <div class="details-box" id="selling_price_per_ml_box">
                    <label class="details-label" for="selling_price_per_ml">Prix de Vente par ml (€)</label>
                    <input type="number" step="0.01" id="selling_price_per_ml" name="selling_price_per_ml" class="form-control" value="{{ old('selling_price_per_ml', $inventoryItem->selling_price_per_ml) }}">
                </div>

                <div class="details-box">
                    <label class="details-label" for="brand">Marque</label>
                    <input type="text" id="brand" name="brand" class="form-control" value="{{ old('brand', $inventoryItem->brand) }}">
                </div>

                <div class="d-flex justify-content-center mt-4">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i> Enregistrer les Modifications
                    </button>
                    <a href="{{ route('inventory_items.index') }}" class="btn-secondary ml-3">
                        <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
                    </a>
                </div>
            </form>
        </div>
    </div>

<script>
    let userEditedPricePerMl = false;
    let userEditedSellingPricePerMl = false;

    function updateVisibility() {
        const unitType = document.getElementById('unit_type').value;
        document.getElementById('stock_quantity_box').style.display = unitType === 'unit' ? 'block' : 'none';
        const showFields = (unitType === 'ml' || unitType === 'gramme');
        document.getElementById('ml_fields_box').style.display = showFields ? 'block' : 'none';
        document.getElementById('ml_fields_remaining').style.display = showFields ? 'block' : 'none';
        document.getElementById('price_per_ml_box').style.display = unitType === 'ml' ? 'block' : 'none';
        document.getElementById('selling_price_per_ml_box').style.display = unitType === 'ml' ? 'block' : 'none';
    }

    function autoFillPricePerMl() {
        const price = parseFloat(document.getElementById('price').value) || 0;
        const sellingPrice = parseFloat(document.getElementById('selling_price').value) || 0;
        const qty = parseFloat(document.getElementById('quantity_per_unit').value) || 0;

        if (qty > 0) {
            if (!userEditedPricePerMl) {
                document.getElementById('price_per_ml').value = (price / qty).toFixed(2);
            }
            if (!userEditedSellingPricePerMl) {
                document.getElementById('selling_price_per_ml').value = (sellingPrice / qty).toFixed(2);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateVisibility();
        autoFillPricePerMl();

        document.getElementById('unit_type').addEventListener('change', updateVisibility);
        document.getElementById('price').addEventListener('input', autoFillPricePerMl);
        document.getElementById('selling_price').addEventListener('input', autoFillPricePerMl);
        document.getElementById('quantity_per_unit').addEventListener('input', autoFillPricePerMl);

        document.getElementById('price_per_ml').addEventListener('input', () => {
            userEditedPricePerMl = true;
        });

        document.getElementById('selling_price_per_ml').addEventListener('input', () => {
            userEditedSellingPricePerMl = true;
        });
    });
</script>

</x-app-layout>
