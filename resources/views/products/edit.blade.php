<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Modifier le Produit') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Modifier le Produit') }}</h1>

            <form action="{{ route('products.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Nom du Produit -->
                <div class="details-box">
                    <label class="details-label" for="name">{{ __('Nom du Produit') }}</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    @error('name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="details-box">
                    <label class="details-label" for="description">{{ __('Description') }}</label>
                    <textarea id="description" name="description" class="form-control">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prix -->
                <div class="details-box">
                    <label class="details-label" for="price">{{ __('Prix (€)') }}</label>
                    <input type="number" id="price" name="price" class="form-control" value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
                    @error('price')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Taux de Taxe -->
                <div class="details-box">
                    <label class="details-label" for="tax_rate">{{ __('Taux de Taxe (%)') }}</label>
                    <input type="number" id="tax_rate" name="tax_rate" class="form-control" value="{{ old('tax_rate', $product->tax_rate) }}" step="0.01" min="0" max="100" required>
                    @error('tax_rate')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary mt-4">{{ __('Mettre à Jour le Produit') }}</button>
                <a href="{{ route('products.show', $product->id) }}" class="btn-secondary mt-4">{{ __('Annuler') }}</a>
            </form>
        </div>
    </div>

    <!-- Styles personnalisés -->
    <style>
        .container {
            max-width: 800px;
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
    </style>
</x-app-layout>
