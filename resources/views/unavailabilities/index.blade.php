<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Liste des Indisponibilités') }}
        </h2>
    </x-slot>

    <!-- Lien vers Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <div class="container mt-5">
        <h1 class="page-title">{{ __('Liste des Indisponibilités') }}</h1>

        <!-- Barre de recherche et bouton de création -->
        <div class="mb-4 d-flex justify-content-between">
            <input type="text" id="search" class="form-control" placeholder="{{ __('Recherche par date ou raison...') }}" onkeyup="filterTable()" style="border-color: #854f38; max-width: 300px;">
            <a href="{{ route('unavailabilities.create') }}" class="btn btn-primary" style="white-space: nowrap;">
                <i class="fas fa-plus mr-2"></i> {{ __('Créer une Indisponibilité') }}
            </a>
        </div>

        <!-- Table des indisponibilités -->
        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover" id="unavailabilityTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">{{ __('Date de début') }} <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(1)">{{ __('Heure de début') }} <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(2)">{{ __('Date de fin') }} <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(3)">{{ __('Heure de fin') }} <i class="fas fa-sort"></i></th>
                        <th>{{ __('Raison') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unavailabilities as $unavailability)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($unavailability->start_date)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($unavailability->start_date)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($unavailability->end_date)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($unavailability->end_date)->format('H:i') }}</td>
                            <td>{{ $unavailability->reason ?? __('Aucune raison spécifiée') }}</td>
                            <td class="action-buttons">
                                <form action="{{ route('unavailabilities.destroy', $unavailability->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette indisponibilité ?') }}');">{{ __('Supprimer') }}</button>
                                </form>
                            </td>
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

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: #647a0b;
            margin-bottom: 20px;
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

        .btn-danger {
            background-color: #e3342f;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            transition: background-color 0.3s;
        }

        .btn-danger:hover {
            background-color: #cc1f1a;
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

        .table tbody tr:hover {
            background-color: #f2f2f2;
            cursor: pointer;
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
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

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.5rem;
            }

            #search {
                max-width: 100%;
                margin-bottom: 10px;
            }

            .d-flex {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-primary {
                width: 100%;
                margin-bottom: 10px;
            }

            .table-responsive {
                padding: 10px;
            }
        }
    </style>

    <!-- JavaScript pour le tri et la recherche -->
    <script>
        function filterTable() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('unavailabilityTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let tdStartDate = tr[i].getElementsByTagName('td')[0];
                let tdStartTime = tr[i].getElementsByTagName('td')[1];
                let tdEndDate = tr[i].getElementsByTagName('td')[2];
                let tdEndTime = tr[i].getElementsByTagName('td')[3];
                let tdReason = tr[i].getElementsByTagName('td')[4];

                if (tdStartDate && tdStartTime && tdEndDate && tdEndTime && tdReason) {
                    let txtValueStartDate = tdStartDate.textContent || tdStartDate.innerText;
                    let txtValueStartTime = tdStartTime.textContent || tdStartTime.innerText;
                    let txtValueEndDate = tdEndDate.textContent || tdEndDate.innerText;
                    let txtValueEndTime = tdEndTime.textContent || tdEndTime.innerText;
                    let txtValueReason = tdReason.textContent || tdReason.innerText;

                    if (
                        txtValueStartDate.toLowerCase().indexOf(filter) > -1 ||
                        txtValueStartTime.toLowerCase().indexOf(filter) > -1 ||
                        txtValueEndDate.toLowerCase().indexOf(filter) > -1 ||
                        txtValueEndTime.toLowerCase().indexOf(filter) > -1 ||
                        txtValueReason.toLowerCase().indexOf(filter) > -1
                    ) {
                        tr[i].style.display = '';
                    } else {
                        tr[i].style.display = 'none';
                    }
                }
            }
        }

        function sortTable(n) {
            let table = document.getElementById('unavailabilityTable');
            let rows = table.rows;
            let switching = true;
            let dir = 'asc';
            let switchcount = 0;

            while (switching) {
                switching = false;
                let rowsArray = Array.from(rows).slice(1); // Exclude header
                for (let i = 0; i < rowsArray.length - 1; i++) {
                    let shouldSwitch = false;
                    let x = rowsArray[i].getElementsByTagName('td')[n];
                    let y = rowsArray[i + 1].getElementsByTagName('td')[n];

                    if (dir === 'asc') {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir === 'desc') {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
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
