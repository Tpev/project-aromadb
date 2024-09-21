<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Liste des Profils Clients') }}
        </h2>
    </x-slot>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <div class="container mt-5">
        <h1 class="page-title">Liste des Profils Clients</h1>

        <!-- Search Bar and Create Button -->
        <div class="mb-4 d-flex justify-content-between">
            <input type="text" id="search" class="form-control" placeholder="Recherche par nom..." onkeyup="filterTable()" style="border-color: #854f38; max-width: 300px;">

            <!-- Create Client Button -->
            <a href="{{ route('client_profiles.create') }}" class="btn-primary" style="white-space: nowrap;">
                <i class="fas fa-plus mr-2"></i> Créer un profil client
            </a>
        </div>

        <!-- Sortable and Filterable Table -->
        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover" id="clientTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">Nom du Client <i class="fas fa-sort-up"></i></th>
                        <th onclick="sortTable(1)">Email <i class="fas fa-sort-up"></i></th>
                        <th onclick="sortTable(2)">Téléphone <i class="fas fa-sort-up"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clientProfiles as $clientProfile)
                        <tr class="table-row" onclick="animateAndRedirect(this, '{{ route('client_profiles.show', $clientProfile->id) }}');">
                            <td>{{ $clientProfile->first_name }} {{ $clientProfile->last_name }}</td>
                            <td>{{ $clientProfile->email }}</td>
                            <td>{{ $clientProfile->phone }}</td>
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

    <!-- JavaScript for sorting, filtering, and redirect -->
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
            let table = document.getElementById('clientTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td')[0];
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
        }

        function sortTable(n) {
            let table = document.getElementById('clientTable');
            let rows = table.rows;
            let switching = true;
            let shouldSwitch, i, x, y, dir, switchcount = 0;
            let asc = true;

            while (switching) {
                switching = false;
                for (i = 1; i < rows.length - 1; i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName('td')[n];
                    y = rows[i + 1].getElementsByTagName('td')[n];
                    if (asc ? (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) : (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())) {
                        shouldSwitch = true;
                        break;
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else if (switchcount === 0 && asc) {
                    asc = false;
                    switching = true;
                }
            }
        }
    </script>
</x-app-layout>
