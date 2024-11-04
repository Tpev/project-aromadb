{{-- resources/views/events/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Liste des Événements') }}
        </h2>
    </x-slot>
    
    <!-- Include FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    
    <div class="container mt-5">
        <h1 class="page-title">{{ __('Liste des Événements') }}</h1>
    
        <!-- Search Bar and Create Button -->
        <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap">
            <input type="text" id="search" class="form-control" placeholder="{{ __('Recherche par nom...') }}" onkeyup="filterTable()" style="border-color: #854f38; max-width: 300px; margin-bottom: 10px;">
    
            <!-- Create Event Button -->
            <a href="{{ route('events.create') }}" class="btn-primary" style="white-space: nowrap;">
                <i class="fas fa-plus mr-2"></i> {{ __('Créer un événement') }}
            </a>
        </div>
    
        <!-- Upcoming Events Table -->
        <div class="table-responsive mx-auto">
            <h2 class="table-title">{{ __('Événements à Venir') }}</h2>
            <table class="table table-bordered table-hover" id="upcomingEventTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0, 'upcomingEventTable')">{{ __('Nom de l\'Événement') }} <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(1, 'upcomingEventTable')">{{ __('Date de Début') }} <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(2, 'upcomingEventTable')">{{ __('Lieu') }} <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(3, 'upcomingEventTable')">{{ __('Réservations') }} <i class="fas fa-sort"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($upcomingEvents as $event)
                        <tr class="table-row" onclick="animateAndRedirect(this, '{{ route('events.show', $event->id) }}');">
                            <td>{{ $event->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y H:i') }}</td>
                            <td>{{ $event->location }}</td>
                            <td>
                                @php
                                    $totalReservations = $event->reservations->count();
                                    $availableSpots = $event->limited_spot ? $event->number_of_spot : '∞';
                                @endphp
                                {{ $totalReservations }} / {{ $availableSpots }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Past Events Table -->
        <div class="table-responsive mx-auto mt-5">
            <h2 class="table-title">{{ __('Événements Passés') }}</h2>
            <table class="table table-bordered table-hover" id="pastEventTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0, 'pastEventTable')">{{ __('Nom de l\'Événement') }} <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(1, 'pastEventTable')">{{ __('Date de Début') }} <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(2, 'pastEventTable')">{{ __('Lieu') }} <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(3, 'pastEventTable')">{{ __('Réservations') }} <i class="fas fa-sort"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pastEvents as $event)
                        <tr class="table-row" onclick="animateAndRedirect(this, '{{ route('events.show', $event->id) }}');">
                            <td>{{ $event->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y H:i') }}</td>
                            <td>{{ $event->location }}</td>
                            <td>
                                @php
                                    $totalReservations = $event->reservations->count();
                                    $availableSpots = $event->limited_spot ? $event->number_of_spot : '∞';
                                @endphp
                                {{ $totalReservations }} / {{ $availableSpots }}
                            </td>
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
            padding: 0 15px;
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
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .table {
            width: 100%;
            max-width: 100%;
        }

        .table thead {
            background-color: #647a0b;
            color: #ffffff;
            cursor: pointer;
        }

        .table thead th {
            text-align: center;
            position: relative;
        }

        .table thead th i {
            margin-left: 5px;
            font-size: 0.9rem;
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
            padding: 15px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: #647a0b;
            margin-bottom: 20px;
        }

        .table-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #854f38;
            margin-bottom: 15px;
            text-align: left;
        }

        #search {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #854f38;
            margin-right: 15px;
            max-width: 100%;
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

        .flex-wrap {
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .table-responsive {
                padding: 10px;
            }

            .table thead th, .table tbody td {
                padding: 10px;
                font-size: 0.9rem;
            }

            .btn-primary {
                margin-top: 10px;
                width: 100%;
                text-align: center;
            }

            .mb-4.d-flex {
                flex-direction: column;
                align-items: flex-start;
            }

            #search {
                margin-bottom: 10px;
                width: 100%;
            }

            .table-title {
                text-align: center;
            }
        }
    </style>
    
    <!-- JavaScript for sorting, filtering, and redirect -->
    <script>
        function animateAndRedirect(row, url) {
            row.classList.add('active');
            setTimeout(function() {
                window.location.href = url;
            }, 300);
        }

        function filterTable() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let upcomingTable = document.getElementById('upcomingEventTable');
            let pastTable = document.getElementById('pastEventTable');
            filterTableRows(upcomingTable, filter);
            filterTableRows(pastTable, filter);
        }

        function filterTableRows(table, filter) {
            let tr = table.getElementsByTagName('tr');
            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td')[0];
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
        }

        function sortTable(n, tableId) {
            let table = document.getElementById(tableId);
            let rows = Array.from(table.rows).slice(1);
            let asc = table.getAttribute('data-sort-dir') === 'asc' ? false : true;

            rows.sort(function(a, b) {
                let x = a.getElementsByTagName('td')[n].innerText.toLowerCase();
                let y = b.getElementsByTagName('td')[n].innerText.toLowerCase();

                if (n === 1) {
                    x = new Date(x.split('/').reverse().join('-'));
                    y = new Date(y.split('/').reverse().join('-'));
                }

                if (x < y) return asc ? -1 : 1;
                if (x > y) return asc ? 1 : -1;
                return 0;
            });

            // Append sorted rows to the table body
            for (let i = 0; i < rows.length; i++) {
                table.tBodies[0].appendChild(rows[i]);
            }

            // Toggle sort direction
            table.setAttribute('data-sort-dir', asc ? 'asc' : 'desc');
        }
    </script>
</x-app-layout>
