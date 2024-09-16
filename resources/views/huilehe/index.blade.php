<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Aroma Made DB') }}
        </h2>
    </x-slot>

    <!-- Ensure Font Awesome icons are loaded -->
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    </head>

    <div class="container mt-5">
        <h1 class="page-title">Liste des Huiles essentielles</h1>

        <!-- Filter and Search Bar -->
        <div class="mb-4 text-end">
            <select id="indicationFilter" class="form-control mb-2" onchange="filterByIndication()" style="border-color: #647a0b;">
                <option value="">Filtre par Indication</option>
                @php
                    // Gather all unique indications, split by semicolon, and remove duplicates
                    $indications = collect($huileHEs)->pluck('Indications')
                        ->map(function($item) {
                            return explode(';', $item); // Split by semicolon
                        })->flatten()->unique()->filter()->sort();
                @endphp
                @foreach($indications as $indication)
                    <option value="{{ trim($indication) }}">{{ trim($indication) }}</option>
                @endforeach
            </select>

            <input type="text" id="search" class="form-control" placeholder="Recherche par nom..." onkeyup="filterTable()" style="border-color: #854f38;">
        </div>

        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover" id="huileTable">
                <thead>
                    <tr>
                        <th>Nom HE</th>
                        <!-- Hidden Indications Column -->
                        <th class="d-none">Indications</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($huileHEs as $huileHE)
                        <tr class="table-row" onclick="animateAndRedirect(this, '{{ route('huilehes.show', $huileHE->slug) }}');">
                            <td>
                                {{ $huileHE->NomHE }} (<em>{{ $huileHE->NomLatin ?? 'Unknown' }}</em>)
                                @auth
                                    @if(auth()->user()->favorites->contains(fn($fav) => $fav->favoritable_id == $huileHE->id && $fav->favoritable_type == 'App\Models\HuileHE'))
                                        <i class="fas fa-heart ms-2" style="color: #854f38;"></i> <!-- Show only when it's a favorite -->
                                    @endif
                                @endauth
                            </td>
                            <!-- Hidden Indications Column -->
                            <td class="d-none">{{ $huileHE->Indications }}</td>
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
            background-color: #647a0b;
            color: #ffffff;
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
            color: #647a0b;
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
            color: #854f38;
        }

        #search {
            width: 100%;
            max-width: 300px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #854f38;
            margin-right: 15px;
        }

        select#indicationFilter {
            border: 1px solid #647a0b;
            padding: 8px;
            border-radius: 5px;
        }

        .text-end {
            padding-right: 15px;
        }

        .d-none {
            display: none !important;
        }

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
            let table = document.getElementById('huileTable');
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
            let table = document.getElementById('huileTable');
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
