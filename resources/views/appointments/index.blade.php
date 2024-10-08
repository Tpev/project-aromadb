{{-- resources/views/appointments/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Liste des Rendez-vous') }}
        </h2>
    </x-slot>

  

    <!-- Vos styles personnalisés -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <div class="container mt-5">
        <h1 class="page-title">Liste des Rendez-vous</h1>

        <!-- Barre de recherche et bouton de création -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <input type="text" id="search" class="form-control" placeholder="Recherche par client..." onkeyup="filterTable()" style="border-color: #854f38; max-width: 300px;">

            <!-- Bouton pour créer un nouveau rendez-vous -->
            <a href="{{ route('appointments.create') }}" class="btn-primary" style="white-space: nowrap;">
                <i class="fas fa-plus mr-2"></i> Créer un rendez-vous
            </a>
        </div>

        <!-- Calendrier -->
        <div id="calendar"></div>

        <!-- Table des rendez-vous -->
        <div class="table-responsive mx-auto mt-5">
            <table class="table table-bordered table-hover" id="appointmentTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">Nom du Client <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(1)">Date du Rendez-vous <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(2)">Durée <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(3)">Produit <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(4)">Statut <i class="fas fa-sort"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($appointments as $appointment)
                        <tr class="table-row" data-url="{{ route('appointments.show', $appointment->id) }}">
                            <td>{{ $appointment->clientProfile->first_name }} {{ $appointment->clientProfile->last_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y H:i') }}</td>
                            <td>{{ $appointment->duration }} {{ __('min') }}</td>
                            <td>{{ $appointment->product->name ?? __('Aucun produit') }}</td>
                            <td>{{ ucfirst($appointment->status) }}</td>
                            <td>
                                <!-- Bouton pour générer une facture -->
                                <a href="{{ route('invoices.create', ['client_id' => $appointment->client_profile_id, 'product_id' => $appointment->product_id]) }}" class="btn btn-success">
                                    <i class="fas fa-file-invoice-dollar"></i> Générer une facture
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

 

    <!-- Vos scripts personnalisés -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialisation du calendrier
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: [ FullCalendar.dayGridPlugin, FullCalendar.timeGridPlugin, FullCalendar.interactionPlugin, FullCalendar.listPlugin, FullCalendar.bootstrapPlugin ],
                locale: 'fr',
                initialView: 'dayGridMonth',
                themeSystem: 'bootstrap',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    month:    'Mois',
                    week:     'Semaine',
                    day:      'Jour', 
                },
				firstDay: 1, // **Added this line to start the week on Monday**
                events: @json($events),
                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                        info.jsEvent.preventDefault();
                    }
                }
            });

            calendar.render();

            // Fonction pour gérer la redirection lors du clic sur une ligne de la table
            const rows = document.querySelectorAll('.table-row');
            rows.forEach(row => {
                row.addEventListener('click', function() {
                    const url = this.getAttribute('data-url');
                    window.location.href = url;
                });
            });
        });

        // Fonction de filtrage pour la recherche
        function filterTable() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('appointmentTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td')[0];
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
        }

        // Fonction de tri pour les colonnes
        function sortTable(n) {
            let table = document.getElementById('appointmentTable');
            let rows = table.rows;
            let switching = true;
            let shouldSwitch, i, x, y, asc = true, switchcount = 0;

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

    <!-- Vos styles personnalisés -->
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

        /* Styles pour le calendrier */
        #calendar {
            max-width: 100%;
            margin: 0 auto 50px auto;
        }

        /* Personnalisation des boutons du calendrier */
        .fc .fc-toolbar.fc-header-toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .fc .fc-toolbar .fc-left,
        .fc .fc-toolbar .fc-center,
        .fc .fc-toolbar .fc-right {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .fc .fc-toolbar .fc-button {
            margin: 5px;
            background-color: #647a0b;
            border-color: #647a0b;
        }

        .fc .fc-toolbar .fc-button:hover {
            background-color: #854f38;
            border-color: #854f38;
        }

        .fc .fc-toolbar h2 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #647a0b;
            margin: 0 10px;
        }
    </style>
</x-app-layout>
