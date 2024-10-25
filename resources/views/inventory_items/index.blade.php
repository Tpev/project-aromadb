{{-- resources/views/inventory_items/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Gestoin de l\'inventaire') }}
        </h2>
    </x-slot>

    <!-- Assurez-vous que les icônes Font Awesome sont chargées -->
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    </head>

    <div class="container-fluid mt-5">
        <h1 class="page-title">Gestion de l'Inventaire</h1>

        <!-- Section de Description -->
        <div class="description-box">
            <p class="description-text">
                Bienvenue sur votre tableau de bord de gestion d'inventaire. Ici, vous pouvez visualiser tous vos articles d'inventaire, gérer les niveaux de stock et suivre les détails des produits. Utilisez les filtres pour trier les articles par marque ou recherchez par nom ou référence pour trouver rapidement des produits spécifiques. Cet outil vous aide à gérer efficacement votre stock et à vous assurer d'avoir toujours les bons produits disponibles pour vos clients.
            </p>
        </div>

        <!-- Bouton pour ajouter un nouvel article -->
        <div class="mb-4 text-end">
            <a href="{{ route('inventory_items.create') }}" class="btn-add">
                <i class="fas fa-plus mr-2"></i> Ajouter un Article
            </a>
        </div>

        <!-- Barre de Filtre et de Recherche -->
        <div class="mb-4 text-end">
            <select id="brandFilter" class="form-control mb-2" onchange="filterByBrand()" style="border-color: #647a0b; width: 200px; display: inline-block;">
                <option value="">Filtrer par Marque</option>
                @php
                    // Récupérer toutes les marques uniques, supprimer les doublons
                    $brands = $inventoryItems->pluck('brand')->unique()->filter()->sort();
                @endphp
                @foreach($brands as $brand)
                    <option value="{{ trim($brand) }}">{{ trim($brand) }}</option>
                @endforeach
            </select>

            <input type="text" id="search" class="form-control" placeholder="Rechercher par nom ou référence..." onkeyup="filterTable()" style="border-color: #854f38; width: 300px; display: inline-block;">
        </div>

        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover" id="inventoryTable">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Référence</th>
                        <th>Description</th>
                        <th>Prix d'Achat</th>
                        <th>Prix de Vente</th>
                        <th>Quantité en Stock</th>
                        <th>Marque</th>
                        <th>Actions</th>
                        <!-- Colonne Marque Cachée pour le Filtrage -->
                        <th class="d-none">Marque</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventoryItems as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->reference }}</td>
                            <td>{{ Str::limit($item->description, 50) }}</td>
                            <td>{{ number_format($item->price, 2) }}€</td>
                            <td>{{ number_format($item->selling_price, 2) }}€</td>
                            <td>
                                {{ $item->quantity_in_stock }}
                                @if($item->quantity_in_stock <= 5)
                                    <i class="fas fa-exclamation-triangle ms-2" style="color: #e3342f;" title="Stock faible"></i>
                                @endif
                            </td>
                            <td>{{ $item->brand }}</td>
                            <td>
                                <a href="{{ route('inventory_items.show', $item->id) }}" class="btn btn-info btn-sm" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('inventory_items.edit', $item->id) }}" class="btn btn-warning btn-sm" title="Éditer">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('inventory_items.destroy', $item->id) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Supprimer"
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                            <!-- Colonne Marque Cachée pour le Filtrage -->
                            <td class="d-none">{{ $item->brand }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Styles Personnalisés -->
    <style>
        .container-fluid {
            max-width: 100%;
            text-align: center;
        }

        .table-responsive {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .table {
            width: 100%;
        }

        .table thead {
            background-color: #647a0b;
            color: #ffffff;
        }

        .table tbody tr {
            transition: background-color 0.3s, color 0.3s;
        }

        .table tbody tr:hover {
            background-color: #854f38;
            color: #ffffff;
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
            word-wrap: break-word;
            white-space: normal;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        #search, #brandFilter {
            padding: 8px;
            border-radius: 5px;
            margin-right: 15px;
        }

        #search {
            border: 1px solid #854f38;
        }

        #brandFilter {
            border: 1px solid #647a0b;
        }

        .text-end {
            padding-right: 15px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .text-end > * {
            margin-left: 10px;
        }

        .ms-2 {
            margin-left: 8px;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.875rem;
        }

        .btn-add {
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

        .btn-add:hover {
            background-color: #854f38;
        }

        .btn-add i {
            margin-right: 5px;
        }
    </style>

    <!-- JavaScript pour le filtrage -->
    <script>
        function filterTable() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('inventoryTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let tdName = tr[i].getElementsByTagName('td')[0];
                let tdReference = tr[i].getElementsByTagName('td')[1];
                if (tdName || tdReference) {
                    let txtValueName = tdName.textContent || tdName.innerText;
                    let txtValueReference = tdReference.textContent || tdReference.innerText;
                    tr[i].style.display = (txtValueName.toLowerCase().indexOf(filter) > -1 || txtValueReference.toLowerCase().indexOf(filter) > -1) ? '' : 'none';
                }
            }
        }

        function filterByBrand() {
            let select = document.getElementById('brandFilter');
            let filter = select.value.toLowerCase();
            let table = document.getElementById('inventoryTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td')[8]; // Index de la colonne Marque cachée
                if (td) {
                    let brand = td.textContent.toLowerCase().trim();
                    tr[i].style.display = brand === filter || filter === '' ? '' : 'none';
                }
            }
        }
    </script>
</x-app-layout>
