<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Liste des Prestations') }}
        </h2>
    </x-slot>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <div class="container mt-5">
        <h1 class="page-title">Liste des Prestations</h1>

@php
    $user = auth()->user();
    $canCreateProduct = $user->canUseFeature('products');

    // Determine minimum license family that includes this feature
    $plansConfig = config('license_features.plans', []);
    $familyOrder = ['free', 'starter', 'pro', 'premium']; // ignore trials

    $requiredFamily = null;
    foreach ($familyOrder as $family) {
        if (in_array('products', $plansConfig[$family] ?? [], true)) {
            $requiredFamily = $family;
            break;
        }
    }

    $familyLabels = [
        'free'    => __('Gratuit'),
        'starter' => __('Starter'),
        'pro'     => __('PRO'),
        'premium' => __('Premium'),
    ];

    $requiredLabel = $requiredFamily
        ? ($familyLabels[$requiredFamily] ?? ucfirst($requiredFamily))
        : __('une formule supérieure');
@endphp

<!-- Search Bar and Create Button -->
<div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">

    {{-- Search Bar --}}
    <input 
        type="text" 
        id="search" 
        class="form-control mb-2 mb-md-0" 
        placeholder="Recherche par nom de la prestation..." 
        onkeyup="filterTable()" 
        style="border-color: #854f38; max-width: 300px;"
    >

    {{-- Button Wrapper --}}
    <div style="position: relative; display: inline-flex; margin-top: 6px;">

        @if($canCreateProduct)
            {{-- Normal usable button --}}
            <a href="{{ route('products.create') }}" class="btn-primary">
                <i class="fas fa-plus mr-2"></i> Créer une prestation
            </a>
        @else
            {{-- Greyed-out locked button --}}
            <a href="/license-tiers/pricing"
               class="btn"
               style="
                   background-color: #e5e7eb;
                   border: 1px solid #d1d5db;
                   color: #6b7280;
                   font-weight: 600;
                   padding: 0.5rem 1rem;
                   border-radius: 7px;
                   white-space: nowrap;
               ">
                <i class="fas fa-plus mr-2"></i> Créer une prestation
            </a>

            {{-- Floating pill --}}
            <div style="
                position: absolute;
                top: -10px;
                right: -12px;
                background-color: #fff1d6;
                border: 1px solid rgba(250, 204, 21, 0.4);
                padding: 2px 8px;
                font-size: 9px;
                border-radius: 9999px;
                font-weight: 600;
                color: #854f38;
                display: inline-flex;
                align-items: center;
                gap: 4px;
                box-shadow: 0 1px 2px rgba(0,0,0,0.08);
            ">
                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="currentColor"
                     viewBox="0 0 20 20"
                     style="width: 12px; height: 12px;">
                    <path fill-rule="evenodd"
                        d="M10 2a4 4 0 00-4 4v2H5a2 
                           2 0 00-2 2v6a2 2 0 
                           002 2h10a2 2 0 
                           002-2v-6a2 2 0 
                           00-2-2h-1V6a4 4 
                           0 00-4-4zm0 6a2 2 
                           0 00-2 2v2a2 2 
                           0 104 0v-2a2 2 
                           0 00-2-2z"
                        clip-rule="evenodd" />
                </svg>

                {{ __('À partir de :') }} <strong>{{ $requiredLabel }}</strong>
            </div>
        @endif

    </div>
</div>


        <!-- Responsive Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="productTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">Nom de la Prestation <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(2)">Prix (€) <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(3)">Taux de Taxe (%) <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(4)">Durée (minutes) <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(5)">Réservable en ligne <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(6)">Mode <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(7)">Max séances/jour <i class="fas fa-sort"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr 
                            class="table-row" 
                            onclick="animateAndRedirect(this, '{{ route('products.show', $product->id) }}');"
                        >
                            <td>{{ $product->name }}</td>

                            <td>
                                @php
                                    $totalPrice = $product->price + ($product->price * $product->tax_rate / 100);
                                    // Remove trailing zeros and use space as thousand separator and comma as decimal
                                    $formattedPrice = rtrim(rtrim(number_format($totalPrice, 2, ',', ' '), '0'), ',');
                                @endphp
                                {{ $formattedPrice }}€
                            </td>
                            <td>
                                @php
                                    // Remove trailing zeros for tax rate
                                    $formattedTax = rtrim(rtrim(number_format($product->tax_rate, 2, ',', ' '), '0'), ',');
                                @endphp
                                {{ $formattedTax }}%
                            </td>
                            <td>{{ $product->duration }}</td>
                            <td>{{ $product->can_be_booked_online ? 'Oui' : 'Non' }}</td>
                            <td>
                                @if($product->visio) Visio
                                @elseif($product->adomicile) À domicile
                                @else Dans le cabinet
                                @endif
                            </td>
                            <td>{{ $product->max_per_day }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px; /* Ensure some padding on smaller screens */
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
            display: inline-flex;
            align-items: center;
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
            overflow-x: auto; /* Enable horizontal scrolling */
        }

        .table {
            width: 100%;
            min-width: 800px; /* Ensure table has a minimum width for smaller screens */
            max-width: 100%;
            margin: 0 auto;
            font-size: 0.9rem; /* Slightly smaller font for better fit */
        }

        .table thead {
            background-color: #647a0b;
            color: #ffffff;
            cursor: pointer;
        }

        .table thead th {
            text-align: center;
            padding: 12px;
            font-size: 1rem;
            white-space: nowrap; /* Prevent text wrapping */
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
            padding: 10px;
            white-space: nowrap; /* Prevent text wrapping */
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
            width: 100%;
            max-width: 300px;
        }

        i.fas.fa-sort {
            margin-left: 5px;
            color: #ffffff; /* Improved contrast against header background */
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .table {
                font-size: 0.8rem; /* Further reduce font size on very small screens */
            }

            .table thead th, .table tbody td {
                padding: 8px;
            }

            .btn-primary {
                padding: 8px 16px; /* Reduce button padding on small screens */
            }

            .page-title {
                font-size: 1.5rem; /* Adjust page title size for smaller screens */
            }
        }

        /* Active Row Animation */
        .active {
            animation: highlight 0.5s forwards;
        }

        @keyframes highlight {
            from { background-color: #ffffff; }
            to { background-color: #854f38; color: #ffffff; }
        }
    </style>

    <!-- JavaScript for Sorting, Filtering, and Redirection -->
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

            // Loop through all table rows, excluding the header
            for (let i = 1; i < tr.length; i++) {
                let tdName = tr[i].getElementsByTagName('td')[0];
                if (tdName) {
                    let txtValueName = tdName.textContent || tdName.innerText;
                    if (txtValueName.toLowerCase().includes(filter)) {
                        tr[i].style.display = '';
                    } else {
                        tr[i].style.display = 'none';
                    }
                }
            }
        }

        function sortTable(n) {
            let table = document.getElementById('productTable');
            let switching = true;
            let dir = 'asc';
            let switchcount = 0;

            while (switching) {
                switching = false;
                let rows = table.rows;
                for (let i = 1; i < rows.length - 1; i++) { // Start from 1 to skip header
                    let shouldSwitch = false;
                    let x = rows[i].getElementsByTagName('td')[n];
                    let y = rows[i + 1].getElementsByTagName('td')[n];

                    let xContent = x.innerHTML.toLowerCase();
                    let yContent = y.innerHTML.toLowerCase();

                    // Parse numerical values for specific columns
                    if ([2, 3, 4, 7].includes(n)) { // Columns: Price (€), Tax Rate (%), Durée, Max séances/jour
                        xContent = parseFloat(xContent.replace(',', '.').replace(/[^0-9.-]+/g, ""));
                        yContent = parseFloat(yContent.replace(',', '.').replace(/[^0-9.-]+/g, ""));
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
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
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
