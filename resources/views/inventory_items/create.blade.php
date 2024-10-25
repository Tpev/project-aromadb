{{-- resources/views/inventory_items/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Ajouter un Article d\'Inventaire') }}
        </h2>
    </x-slot>

    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Bootstrap CSS pour un meilleur style (optionnel mais recommandé) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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
            margin-bottom: 20px;
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
            border: 1px solid #854f38;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .btn-primary {
            background-color: #647a0b;
            border: none;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .btn-secondary {
            background-color: #647a0b;
            border: none;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 1rem;
        }

        .btn-secondary:hover {
            background-color: #854f38;
        }

        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .form-section {
            text-align: left;
        }

        /* Ajustements pour les petits écrans */
        @media (max-width: 600px) {
            .details-container {
                padding: 20px;
            }

            .details-title {
                font-size: 1.5rem;
            }
        }
    </style>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Ajouter un Article d\'Inventaire') }}</h1>

            <!-- Message de succès -->
            @if(session('success'))
                <div class="alert alert-success text-center">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Message d'erreur -->
            @if ($errors->any())
                <div class="alert alert-danger text-center">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Formulaire d'ajout d'article -->
            <form action="{{ route('inventory_items.store') }}" method="POST">
                @csrf

                <!-- Nom de l'article -->
                <div class="details-box form-section">
                    <label class="details-label" for="name">{{ __('Nom de l\'Article') }}</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Référence -->
                <div class="details-box form-section">
                    <label class="details-label" for="reference">{{ __('Référence') }}</label>
                    <input type="text" id="reference" name="reference" class="form-control" value="{{ old('reference') }}" required>
                    @error('reference')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="details-box form-section">
                    <label class="details-label" for="description">{{ __('Description') }}</label>
                    <textarea id="description" name="description" class="form-control">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prix d'Achat -->
                <div class="details-box form-section">
                    <label class="details-label" for="price">{{ __('Prix d\'Achat (€)') }}</label>
                    <input type="number" id="price" name="price" class="form-control" step="0.01" value="{{ old('price') }}" required>
                    @error('price')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prix de Vente -->
                <div class="details-box form-section">
                    <label class="details-label" for="selling_price">{{ __('Prix de Vente (€)') }}</label>
                    <input type="number" id="selling_price" name="selling_price" class="form-control" step="0.01" value="{{ old('selling_price') }}" required>
                    @error('selling_price')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Quantité en Stock -->
                <div class="details-box form-section">
                    <label class="details-label" for="quantity_in_stock">{{ __('Quantité en Stock') }}</label>
                    <input type="number" id="quantity_in_stock" name="quantity_in_stock" class="form-control" value="{{ old('quantity_in_stock') }}" required>
                    @error('quantity_in_stock')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Marque -->
                <div class="details-box form-section">
                    <label class="details-label" for="brand">{{ __('Marque') }}</label>
                    <input type="text" id="brand" name="brand" class="form-control" value="{{ old('brand') }}">
                    @error('brand')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Boutons de soumission et d'annulation -->
                <div class="d-flex justify-content-center mt-4">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i> {{ __('Ajouter l\'Article') }}
                    </button>
                    <a href="{{ route('inventory_items.index') }}" class="btn-secondary ml-3">
                        <i class="fas fa-arrow-left mr-2"></i> {{ __('Retour à la liste') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS (optionnel mais recommandé) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</x-app-layout>
