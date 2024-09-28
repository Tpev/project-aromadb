<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Vos Disponibilités') }}
        </h2>
    </x-slot>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <div class="container mt-5">
        <h1 class="page-title">{{ __('Liste des Disponibilités') }}</h1>

        <!-- Search Bar and Create Button -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <input type="text" id="search" class="form-control" placeholder="{{ __('Recherche par jour...') }}" onkeyup="filterTable()" style="border-color: #854f38; max-width: 300px;">

            <!-- Create Availability Button -->
            <a href="{{ route('availabilities.create') }}" class="btn-primary" style="white-space: nowrap;">
                <i class="fas fa-plus mr-2"></i> {{ __('Ajouter une Disponibilité') }}
            </a>
        </div>

        <!-- Sortable and Filterable Table -->
        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover" id="availabilityTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)" style="cursor: pointer;">{{ __('Jour de la Semaine') }} <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(1)" style="cursor: pointer;">{{ __('Heure de Début') }} <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(2)" style="cursor: pointer;">{{ __('Heure de Fin') }} <i class="fas fa-sort"></i></th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($availabilities as $availability)
                        <tr class="table-row">
                            <!-- Correct day mapping (shifting day_of_week to match Monday as day 0) -->
                            <td>
                                @php
                                    $daysOfWeek = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                                    echo $daysOfWeek[$availability->day_of_week];
                                @endphp
                            </td>
                            
                            <!-- Fixed time formatting -->
                            <td>{{ \Carbon\Carbon::createFromFormat('H:i:s', $availability->start_time)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::createFromFormat('H:i:s', $availability->end_time)->format('H:i') }}</td>
                            <td>
                                <a href="{{ route('availabilities.edit', $availability->id) }}" class="btn-secondary" title="{{ __('Éditer') }}">
                                    <i class="fas fa-edit"></i> {{ __('Éditer') }}
                                </a>
                                <form action="{{ route('availabilities.destroy', $availability->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette disponibilité ?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary" title="{{ __('Supprimer') }}">
                                        <i class="fas fa-trash-alt"></i> {{ __('Supprimer') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                    @if($availabilities->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center">{{ __('Aucune disponibilité trouvée.') }}</td>
                        </tr>
                    @endif
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

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: #647a0b;
            margin-bottom: 20px;
        }

        .btn-primary, .btn-secondary {
            background-color: #647a0b;
            color: #ffffff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            margin: 5px;
            font-size: 0.9rem;
        }

        .btn-primary:hover, .btn-secondary:hover {
            background-color: #854f38;
            color: #ffffff;
        }

        .btn-secondary {
            background-color: #647a0b;
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
            margin: 0 auto;
        }

        .table thead {
            background-color: #647a0b;
            color: #ffffff;
            cursor: pointer;
        }

        .table thead th {
            text-align: center;
            padding: 10px;
            position: relative;
        }

        .table tbody tr {
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
            padding: 12px;
        }

        #search {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #854f38;
            margin-right: 15px;
        }

        i.fas.fa-sort {
            margin-left: 5px;
            color: #ffffff;
            font-size: 0.8rem;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        .text-center {
            text-align: center;
        }

        .text-green-500 {
            color: #38a169;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
        }

        .table-row {
            cursor: pointer;
        }
    </style>

    <!-- JavaScript for sorting and filtering -->
    <script>
        function filterTable() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('availabilityTable');
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
            let table = document.getElementById('availabilityTable');
            let rows = table.rows;
            let switching = true;
            let shouldSwitch;
            let i, x, y, dir, switchcount = 0;
            let asc = true;

            while (switching) {
                switching = false;
                for (i = 1; i < (rows.length - 1); i++) {
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
                } else {
                    if (switchcount === 0 && asc) {
                        asc = false;
                        switching = true;
                    }
                }
            }
        }
    </script>
</x-app-layout>
