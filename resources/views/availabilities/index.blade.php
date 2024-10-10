<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Vos Disponibilités') }}
        </h2>
    </x-slot>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Optional: Include Select2 CSS for enhanced search (if needed) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <div class="container mt-5">
        <h1 class="page-title">{{ __('Liste des Disponibilités') }}</h1>

        <!-- Display Success Messages -->
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Search Bar and Create Button -->
        <div class="mb-4 d-flex justify-content-between">
            <!-- Search Bar on the Left -->
            <div class="search-bar mb-2">
                <input type="text" id="search" class="form-control" placeholder="{{ __('Recherche par jour ou préstation...') }}" onkeyup="filterTable()" style="border-color: #854f38; max-width: 300px;">
				
			</div>

            <!-- Create Availability Button on the Right -->
            <a href="{{ route('availabilities.create') }}" class="btn-primary text-white">
                <i class="fas fa-plus mr-2"></i> {{ __('Ajouter une Disponibilité') }}
            </a>
        </div>

        <!-- Sortable and Filterable Table -->
        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover" id="availabilityTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)" style="cursor: pointer;">
                            {{ __('Jour de la Semaine') }}
                            <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(1)" style="cursor: pointer;">
                            {{ __('Heure de Début') }}
                            <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(2)" style="cursor: pointer;">
                            {{ __('Heure de Fin') }}
                            <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(3)" style="cursor: pointer;">
                            {{ __('Applique à Toutes les Préstations') }}
                            <i class="fas fa-sort"></i>
                        </th>
                        <th>{{ __('Préstations Associées') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($availabilities as $availability)
                        <tr class="table-row">
                            <!-- Correct day mapping (Monday as day 0) -->
                            <td>
                                @php
                                    $daysOfWeek = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                                    echo $daysOfWeek[$availability->day_of_week];
                                @endphp
                            </td>
                            
                            <!-- Fixed time formatting -->
                            <td>{{ \Carbon\Carbon::createFromFormat('H:i:s', $availability->start_time)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::createFromFormat('H:i:s', $availability->end_time)->format('H:i') }}</td>
                            
                            <!-- Applique à Toutes les Préstations -->
                            <td>
                                @if($availability->applies_to_all)
                                    <span class="badge badge-primary">{{ __('Oui') }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ __('Non') }}</span>
                                @endif
                            </td>
                            
                            <!-- Préstations Associées -->
                            <td>
                                @if($availability->applies_to_all)
                                    <span class="badge badge-primary">{{ __('Toutes les Préstations') }}</span>
                                @elseif($availability->products->isEmpty())
                                    <span class="badge badge-secondary">{{ __('Aucune Préstation associée') }}</span>
                                @else
                                    @foreach($availability->products as $product)
                                        <span class="badge badge-primary">{{ $product->name }}</span>
                                    @endforeach
                                @endif
                            </td>
                            
                            <!-- Actions -->
                            <td>
                                <a href="{{ route('availabilities.edit', $availability->id) }}" class="btn-secondary text-white" title="{{ __('Éditer') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('availabilities.destroy', $availability->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette disponibilité ?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger text-white" title="{{ __('Supprimer') }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                    @if($availabilities->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center">{{ __('Aucune disponibilité trouvée.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        /* Container */
        .container {
            max-width: 1200px;
            text-align: center;
            padding: 20px;
            margin: 0 auto;
        }

        /* Page Title */
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #647a0b; /* Primary Theme Color */
            margin-bottom: 30px;
        }

        /* Buttons */
        .btn-primary, .btn-secondary, .btn-danger {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 2px;
            font-size: 0.9rem;
            transition: background-color 0.3s, transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background-color: #647a0b; /* Primary Theme Color */
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #854f38; /* Secondary Theme Color on Hover */
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #6c757d; /* Default Secondary Color */
            color: #ffffff;
        }

        .btn-secondary:hover {
            background-color: #5a6268; /* Darker Secondary Color on Hover */
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: #e3342f; /* Default Danger Color */
            color: #ffffff;
        }

        .btn-danger:hover {
            background-color: #cc1f1a; /* Darker Danger Color on Hover */
            transform: translateY(-2px);
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            margin: 2px;
        }

        /* Custom Badge Colors Aligned with Theme */
        .badge-primary {
            background-color: #647a0b; /* Primary Theme Color */
            color: #ffffff;
        }

        .badge-secondary {
            background-color: #854f38; /* Secondary Theme Color */
            color: #ffffff;
        }

        /* Optional: If you want to retain some distinction for info badges */
        .badge-info {
            background-color: #647a0b; /* Using primary color for consistency */
            color: #ffffff;
        }

        /* Table Styles */
        .table-responsive {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #647a0b; /* Primary Theme Color */
            color: #ffffff;
        }

        thead th {
            padding: 12px;
            position: relative;
            font-size: 1rem;
            cursor: pointer;
            user-select: none;
        }

        thead th i.fas.fa-sort {
            margin-left: 5px;
            color: #ffffff;
            font-size: 0.8rem;
        }

        tbody tr {
            transition: background-color 0.3s, color 0.3s, transform 0.2s;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
            transform: scale(1.01);
        }

        tbody td {
            padding: 12px;
            vertical-align: middle;
            font-size: 0.95rem;
            color: #333333;
        }

        /* Search Bar */
        .search-bar input {
            padding: 8px 12px;
            border: 1px solid #854f38; /* Secondary Theme Border Color */
            border-radius: 4px;
            width: 100%;
            max-width: 300px;
        }

        /* Alerts */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 0.95rem;
        }

        .alert-success {
            background-color: #e6ffed; /* Light Green Background */
            color: #38a169; /* Green Text */
            border: 1px solid #38a169;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }

            thead th, tbody td {
                padding: 10px;
                font-size: 0.85rem;
            }

            .btn-primary, .btn-secondary, .btn-danger {
                padding: 6px 10px;
                font-size: 0.8rem;
            }

            .badge {
                font-size: 0.7rem;
                padding: 0.2em 0.5em;
            }

            .search-bar input {
                max-width: 100%;
            }

            .d-flex {
                flex-direction: column;
                align-items: stretch;
            }

            .search-bar, .btn-primary {
                width: 100%;
                margin-bottom: 10px;
            }
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
                let tdDay = tr[i].getElementsByTagName('td')[0];
                let tdProducts = tr[i].getElementsByTagName('td')[4];
                if (tdDay && tdProducts) {
                    let txtValueDay = tdDay.textContent || tdDay.innerText;
                    let txtValueProducts = tdProducts.textContent || tdProducts.innerText;
                    if (txtValueDay.toLowerCase().indexOf(filter) > -1 || txtValueProducts.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function sortTable(n) {
            let table = document.getElementById('availabilityTable');
            let rows = table.rows;
            let switching = true;
            let shouldSwitch;
            let i, x, y;
            let dir = "asc";
            let switchcount = 0;

            while (switching) {
                switching = false;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName('td')[n];
                    y = rows[i + 1].getElementsByTagName('td')[n];
                    let xContent = x.textContent || x.innerText;
                    let yContent = y.textContent || y.innerText;

                    // Determine if column is numeric (like time) or not
                    if (n === 1 || n === 2) { // For 'Heure de Début' and 'Heure de Fin'
                        xContent = xContent.replace(':', '');
                        yContent = yContent.replace(':', '');
                        xContent = parseInt(xContent);
                        yContent = parseInt(yContent);
                    } else { // For 'Jour de la Semaine' and 'Applique à Toutes les Préstations'
                        xContent = xContent.toLowerCase();
                        yContent = yContent.toLowerCase();
                    }

                    if (dir === "asc") {
                        if (xContent > yContent) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir === "desc") {
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
                    if (switchcount === 0 && dir === "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }

            // Update sort icons
            let ths = table.getElementsByTagName('th');
            for (let i = 0; i < ths.length; i++) {
                let icon = ths[i].getElementsByTagName('i')[0];
                if (i === n) {
                    icon.className = dir === "asc" ? "fas fa-sort-up" : "fas fa-sort-down";
                } else {
                    icon.className = "fas fa-sort";
                }
            }
        }
    </script>

    <!-- Optional: Include Select2 JS if enhancing search (not required here) -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
</x-app-layout>
