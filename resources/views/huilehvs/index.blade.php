<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-green-600 leading-tight">
            {{ __('Aroma Made DB') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <h1 class="page-title">Listes des Huiles végétales</h1>

        <!-- Filter and Search Bar -->
        <div class="mb-4 text-end">
            <select id="indicationFilter" class="form-control mb-2" onchange="filterByIndication()">
                <option value="">Filtre par Indication</option>
                @php
                    // Gather all unique indications, split by semicolon, and remove duplicates
                    $indications = collect($huileHVs)->pluck('Indications')
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
            <table class="table table-bordered table-hover" id="huileTable">
                <thead>
                    <tr>
                        <th>Nom HV</th>
                        <!-- Hidden Indications Column -->
                        <th class="d-none">Indications</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($huileHVs as $huileHV)
                        <tr class="table-row" onclick="animateAndRedirect(this, '{{ route('huilehvs.show', $huileHV->id) }}');">
                            <td>{{ $huileHV->NomHV }} (<em>{{ $huileHV->NomLatin ?? 'Unknown' }}</em>)</td>
                            <!-- Hidden Indications Column -->
                            <td class="d-none">{{ $huileHV->Indications }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 1200px; /* Make the container wider */
            text-align: center; /* Center content within the container */
        }
        .table-responsive {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto; /* Center the table container */
            display: flex;
            justify-content: center; /* Ensure the table is centered */
        }
        .table {
            width: 100%; /* Ensure the table takes up the full width of the container */
            max-width: 1000px; /* Control the maximum width of the table */
        }
        .table thead {
            background-color: #16a34a; /* Tailwind CSS green-600 color */
            color: #ffffff;
        }
        .table tbody tr {
            cursor: pointer; /* Change cursor to pointer to indicate the row is clickable */
            transition: background-color 0.3s, color 0.3s, transform 0.3s; /* Smooth transition including transform */
        }
        .table tbody tr:hover {
            background-color: #16a34a; /* Match the hover color */
            color: #ffffff; /* Change text color to white on hover */
            transform: scale(1.02); /* Slightly zoom in on hover */
        }
        .table tbody tr.active {
            transform: scale(1.1); /* Apply larger zoom on click */
            transition: transform 0.5s ease; /* Smooth zoom-out transition */
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center; /* Center content within table cells */
        }
        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: #333333;
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-add {
            background-color: #007bff;
            color: #ffffff;
            margin-bottom: 20px;
        }
        .btn-add:hover {
            background-color: #0056b3;
        }
        #search {
            width: 100%;
            max-width: 300px; /* Adjust the width of the search bar */
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-right: 15px; /* Add a bit of margin on the right for spacing */
        }
        .text-end {
            padding-right: 15px; /* Ensure there's padding on the right side */
        }

        /* Hide Indications Column */
        .d-none {
            display: none !important;
        }
    </style>

    <!-- JavaScript for row click animation and filtering -->
    <script>
        function animateAndRedirect(row, url) {
            row.classList.add('active');
            setTimeout(function() {
                window.location.href = url;
            }, 500); // Duration of the zoom-out animation
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
                    let indications = td.textContent.toLowerCase().split(';').map(s => s.trim()); // Split and trim each indication
                    tr[i].style.display = indications.includes(filter) || filter === '' ? '' : 'none';
                }
            }
        }
    </script>
</x-app-layout>
