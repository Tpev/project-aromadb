<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-green-600 leading-tight">
            {{ __('Aroma Made DB - Tisanes') }}
        </h2>
    </x-slot>

    <!-- Ensure Font Awesome icons are loaded -->
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    </head>

    <div class="container mt-5">
        <h1 class="page-title">Liste des Tisanes</h1>

        <!-- Filter and Search Bar -->
        <div class="mb-4 text-end">
            <select id="indicationFilter" class="form-control mb-2" onchange="filterByIndication()">
                <option value="">Filtrer par Indication</option>
                @php
                    // Gather all unique indications, split by semicolon, and remove duplicates
                    $indications = collect($tisanes)->pluck('Indications')
                        ->map(function($item) {
                            return explode(';', $item); // Split by semicolon
                        })->flatten()->unique()->filter()->sort();
                @endphp
                @foreach($indications as $indication)
                    <option value="{{ trim($indication) }}">{{ trim($indication) }}</option>
                @endforeach
            </select>

            <input type="text" id="search" class="form-control" placeholder="Recherche par nom..." onkeyup="filterTable()">
        </div>

        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover" id="tisaneTable">
                <thead>
                    <tr>
                        <th>Nom Tisane</th>
                        <!-- Hidden Indications Column -->
                        <th class="d-none">Indications</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tisanes as $tisane)
                        <tr class="table-row">
                            <td onclick="animateAndRedirect(this, '{{ route('tisanes.show', $tisane->id) }}');">
                                {{ $tisane->NomTisane }} (<em>{{ $tisane->NomLatin ?? 'Unknown' }}</em>)
                                @auth
                                    @if(auth()->user()->favorites->contains(fn($fav) => $fav->favoritable_id == $tisane->id && $fav->favoritable_type == 'App\Models\Tisane'))
                                        <i class="fas fa-heart text-red-500 ms-2"></i> <!-- Show only when it's a favorite -->
                                    @endif
                                @endauth
                            </td>
                            <!-- Hidden Indications Column -->
                            <td class="d-none">{{ $tisane->Indications }}</td>
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
            text-align: center;
        }
        .table-responsive {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            display: flex;
            justify-content: center;
        }
        .table {
            width: 100%;
            max-width: 1000px;
        }
        .table thead {
            background-color: #16a34a;
            color: #ffffff;
        }
        .table tbody tr {
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s, transform 0.3s;
        }
        .table tbody tr:hover {
            background-color: #16a34a;
            color: #ffffff;
            transform: scale(1.02);
        }
        .table tbody tr.active {
            transform: scale(1.1);
            transition: transform 0.5s ease;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: #333333;
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-favorite {
            background-color: transparent;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }
        .btn-favorite i {
            transition: color 0.3s;
        }
        .btn-favorite:hover i {
            color: #ff0000;
        }
        #search {
            width: 100%;
            max-width: 300px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-right: 15px;
        }
        .text-end {
            padding-right: 15px;
        }
        .d-none {
            display: none !important;
        }
        /* Adjust spacing between the heart icon and text */
        .ms-2 {
            margin-left: 8px;
        }
    </style>

    <!-- JavaScript for row click animation and filtering -->
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
            let table = document.getElementById('tisaneTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td')[0];
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
        }

        function filterByIndication() {
            let select = document.getElementById('indicationFilter');
            let filter = select.value.toLowerCase();
            let table = document.getElementById('tisaneTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td')[1]; // Indications column
                if (td) {
                    let indications = td.textContent.toLowerCase().split(';').map(s => s.trim());
                    tr[i].style.display = indications.includes(filter) || filter === '' ? '' : 'none';
                }
            }
        }
    </script>
</x-app-layout>