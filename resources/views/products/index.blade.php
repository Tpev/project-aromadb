<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Liste des Produits') }}
        </h2>
    </x-slot>
    <!-- Lien vers Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <div class="container mt-5">
        <h1 class="page-title">Liste des Produits</h1>

        <!-- Barre de recherche et bouton de création -->
        <div class="mb-4 d-flex justify-content-between">
            <input type="text" id="search" class="form-control" placeholder="Recherche par nom du produit..." onkeyup="filterTable()" style="border-color: #854f38; max-width: 300px;">

            <!-- Bouton pour créer un produit -->
            <a href="{{ route('products.create') }}" class="btn-primary" style="white-space: nowrap;">
                <i class="fas fa-plus mr-2"></i> Créer un produit
            </a>
        </div>

        <!-- Table triable et filtrable -->
        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover" id="productTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">Nom du Produit <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(1)">Description <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(2)">Prix (€) <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(3)">Taux de Taxe (%) <i class="fas fa-sort"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr class="table-row" onclick="animateAndRedirect(this, '{{ route('products.show', $product->id) }}');">
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->description }}</td>
                            <td>{{ number_format($product->price, 2, ',', ' ') }}</td>
                            <td>{{ number_format($product->tax_rate, 2, ',', ' ') }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Styles personnalisés -->
    <style>
        .container {
            max-width: 1200px;
            text-align: center;
        }

        .btn-primary {
            background-color: #647a0b;
            border-color: #647a0b;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
            display: inline-block;
        }

        .btn-primary:hover {
            background-color: #854f38;
            border-color: #854f38;
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
            max-width: 1000px;
        }

        .table thead {
            background-color: #647a0b;
            color: #ffffff;
            cursor: pointer;
        }

        .table thead th {
            text-align: center;
        }

        .table tbody tr {
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s, transform 0.3s;
        }

        .table tbody tr:hover {
            background-color: #854f38;
            color: #ffffff;
            transform: scale(1.02);
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: #647a0b;
            margin-bottom: 20px;
        }

        #search {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #854f38;
            margin-right: 15px;
        }

        i.fas.fa-sort {
            margin-left: 5px;
            color: #647a0b;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }
    </style>

    <!-- JavaScript pour le tri, le filtrage et la redirection -->
    <script>
        function animateAndRedirect(row, url) {
            row.classList.add('active');
            setTimeout(function() {
                window.location.href = url;
            }, 500);
        }

        function filterTable() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('productTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let tdName = tr[i].getElementsByTagName('td')[0];
                if (tdName) {
                    let txtValueName = tdName.textContent || tdName.innerText;
                    if (txtValueName.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = '';
                    } else {
                        tr[i].style.display = 'none';
                    }
                }
            }
        }

        function sortTable(n) {
            let table = document.getElementById('productTable');
            let rows = table.rows;
            let switching = true;
            let dir = 'asc';
            let switchcount = 0;

            while (switching) {
                switching = false;
                let rowsArray = Array.from(rows).slice(1); // Exclure l'en-tête
                for (let i = 0; i < rowsArray.length - 1; i++) {
                    let shouldSwitch = false;
                    let x = rowsArray[i].getElementsByTagName('td')[n];
                    let y = rowsArray[i + 1].getElementsByTagName('td')[n];

                    let xContent = x.innerHTML.toLowerCase();
                    let yContent = y.innerHTML.toLowerCase();

                    if (n === 2 || n === 3) { // Colonnes Prix (€) et Taux de Taxe (%)
                        xContent = parseFloat(xContent.replace(',', '.').replace(/[^0-9.-]+/g,""));
                        yContent = parseFloat(yContent.replace(',', '.').replace(/[^0-9.-]+/g,""));
                    }

                    if (dir === 'asc') {
                        if (xContent > yContent) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir === 'desc') {
                        if (xContent < yContent) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rowsArray[i].parentNode.insertBefore(rowsArray[i + 1], rowsArray[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount === 0 && dir === 'asc') {
                        dir = 'desc';
                        switching = true;
                    }
                }
            }
        }
    </script>
</x-app-layout>
