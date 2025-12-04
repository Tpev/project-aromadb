{{-- resources/views/appointments/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Liste des Rendez-vous') }}
        </h2>
    </x-slot>

    <!-- Vos styles personnalisés / FontAwesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <div class="container mt-5">
        <h1 class="page-title">Liste des Rendez-vous</h1>

        <!-- Barre de recherche et bouton de création -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <input type="text"
                   id="search"
                   class="form-control"
                   placeholder="Recherche par client..."
                   onkeyup="filterTable()"
                   style="border-color: #854f38; max-width: 300px;">

            <!-- Bouton pour créer un nouveau rendez-vous -->
            <a href="{{ route('appointments.create') }}" class="btn-primary" style="white-space: nowrap;">
                <i class="fas fa-plus mr-2"></i> Créer un rendez-vous
            </a>
        </div>

        <!-- Calendrier -->
        <div id="calendar"></div>

        {{-- ============================
             Rendez-vous à venir
        ============================= --}}
        <div class="table-responsive mx-auto mt-5">
            <h2 class="mb-3 font-semibold text-lg" style="color:#647a0b;">
                Rendez-vous à venir
            </h2>

            @if($rendezVousAVenir->isNotEmpty())
                <table class="table table-bordered table-hover appointment-table"
                       id="upcomingTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable('upcomingTable', 0)">
                                Nom du Client <i class="fas fa-sort"></i>
                            </th>
                            <th onclick="sortTable('upcomingTable', 1)">
                                Date du Rendez-vous <i class="fas fa-sort"></i>
                            </th>
                            <th onclick="sortTable('upcomingTable', 2)">
                                Durée <i class="fas fa-sort"></i>
                            </th>
                            <th onclick="sortTable('upcomingTable', 3)">
                                Produit <i class="fas fa-sort"></i>
                            </th>
                            <th onclick="sortTable('upcomingTable', 4)">
                                Statut <i class="fas fa-sort"></i>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rendezVousAVenir as $appointment)
                            <tr class="table-row"
                                data-url="{{ route('appointments.show', $appointment->id) }}">
                                <td>
                                    @if($appointment->external)
                                        {{ $appointment->notes ?: 'Occupé' }}
                                    @else
                                        {{ optional($appointment->clientProfile)->first_name }}
                                        {{ optional($appointment->clientProfile)->last_name }}
                                    @endif
                                </td>
                                <td>
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d/m/Y') }}
                                    <br>
                                    <i class="fas fa-clock"></i>
                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('H:i') }}
                                </td>
                                <td>{{ $appointment->duration }} {{ __('min') }}</td>
                                <td>{{ $appointment->product->name ?? '—' }}</td>
                                <td id="status-{{ $appointment->id }}">
                                    {{ ucfirst($appointment->status) }}
                                </td>

                                {{-- Actions (masquées pour les imports Google/external) --}}
                                <td>
                                    @unless($appointment->external)
                                        <a href="{{ route('invoices.create', [
                                                 'client_id'  => $appointment->client_profile_id,
                                                 'product_id' => $appointment->product_id]) }}"
                                           class="btn-invoice">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                            Générer une facture
                                        </a>

                                        @if ($appointment->status !== 'Complété')
                                            <form action="{{ route('appointments.completeindex', $appointment->id) }}"
                                                  method="POST"
                                                  style="display:inline-block;">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit"
                                                        class="btn-complete"
                                                        onclick="return confirm('Marquer ce rendez-vous comme complété ?')">
                                                    <i class="fas fa-check-circle"></i>
                                                    Marquer comme Complété
                                                </button>
                                            </form>
                                        @endif
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="mt-3 text-sm text-gray-500">
                    Aucun rendez-vous à venir pour le moment.
                </p>
            @endif
        </div>

        {{-- ============================
             Rendez-vous passés
        ============================= --}}
        <div class="table-responsive mx-auto mt-5">
            <h2 class="mb-3 font-semibold text-lg" style="color:#854f38;">
                Rendez-vous passés
            </h2>

            @if($rendezVousPasses->isNotEmpty())
                <table class="table table-bordered table-hover appointment-table"
                       id="pastTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable('pastTable', 0)">
                                Nom du Client <i class="fas fa-sort"></i>
                            </th>
                            <th onclick="sortTable('pastTable', 1)">
                                Date du Rendez-vous <i class="fas fa-sort"></i>
                            </th>
                            <th onclick="sortTable('pastTable', 2)">
                                Durée <i class="fas fa-sort"></i>
                            </th>
                            <th onclick="sortTable('pastTable', 3)">
                                Produit <i class="fas fa-sort"></i>
                            </th>
                            <th onclick="sortTable('pastTable', 4)">
                                Statut <i class="fas fa-sort"></i>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rendezVousPasses as $appointment)
                            <tr class="table-row"
                                data-url="{{ route('appointments.show', $appointment->id) }}">
                                <td>
                                    @if($appointment->external)
                                        {{ $appointment->notes ?: 'Occupé' }}
                                    @else
                                        {{ optional($appointment->clientProfile)->first_name }}
                                        {{ optional($appointment->clientProfile)->last_name }}
                                    @endif
                                </td>
                                <td>
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d/m/Y') }}
                                    <br>
                                    <i class="fas fa-clock"></i>
                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('H:i') }}
                                </td>
                                <td>{{ $appointment->duration }} {{ __('min') }}</td>
                                <td>{{ $appointment->product->name ?? '—' }}</td>
                                <td id="status-{{ $appointment->id }}">
                                    {{ ucfirst($appointment->status) }}
                                </td>

                                {{-- Actions (masquées pour les imports Google/external) --}}
                                <td>
                                    @unless($appointment->external)
                                        <a href="{{ route('invoices.create', [
                                                 'client_id'  => $appointment->client_profile_id,
                                                 'product_id' => $appointment->product_id]) }}"
                                           class="btn-invoice">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                            Générer une facture
                                        </a>

                                        @if ($appointment->status !== 'Complété')
                                            <form action="{{ route('appointments.completeindex', $appointment->id) }}"
                                                  method="POST"
                                                  style="display:inline-block;">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit"
                                                        class="btn-complete"
                                                        onclick="return confirm('Marquer ce rendez-vous comme complété ?')">
                                                    <i class="fas fa-check-circle"></i>
                                                    Marquer comme Complété
                                                </button>
                                            </form>
                                        @endif
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="mt-3 text-sm text-gray-500">
                    Aucun rendez-vous passé enregistré.
                </p>
            @endif
        </div>
    </div>

    <!-- Meta CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts personnalisés -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialisation du calendrier
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: [
                    FullCalendar.dayGridPlugin,
                    FullCalendar.timeGridPlugin,
                    FullCalendar.interactionPlugin,
                    FullCalendar.listPlugin,
                    FullCalendar.bootstrapPlugin
                ],
                locale: 'fr',
                initialView: 'dayGridMonth',
                themeSystem: 'bootstrap',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    month: 'Mois',
                    week:  'Semaine',
                    day:   'Jour',
                },
                firstDay: 1, // Commencer la semaine le lundi
                events: @json($events),
                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                        info.jsEvent.preventDefault();
                    }
                }
            });

            calendar.render();

            // Gestion du clic sur les lignes (naviguer vers la page du rendez-vous)
            const rows = document.querySelectorAll('.table-row');
            rows.forEach(row => {
                row.addEventListener('click', function(e) {
                    // Empêcher la redirection si le clic est sur un bouton ou un lien
                    if (e.target.closest('button') || e.target.closest('a')) {
                        return;
                    }
                    const url = this.getAttribute('data-url');
                    if (url) {
                        window.location.href = url;
                    }
                });
            });
        });

        // Fonction de filtrage pour la recherche (appliquée aux deux tableaux)
        function filterTable() {
            let input  = document.getElementById('search');
            let filter = input.value.toLowerCase();

            const rows = document.querySelectorAll('.appointment-table tbody tr');

            rows.forEach(row => {
                let firstCell = row.querySelector('td');
                if (firstCell) {
                    let txtValue = firstCell.textContent || firstCell.innerText;
                    row.style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
                }
            });
        }

        // Fonction de tri pour les colonnes (par table)
        function sortTable(tableId, n) {
            let table = document.getElementById(tableId);
            if (!table) return;

            let rows = table.rows;
            let switching = true;
            let asc = true;
            let switchcount = 0;

            while (switching) {
                switching = false;
                for (let i = 1; i < rows.length - 1; i++) {
                    let shouldSwitch = false;
                    let x = rows[i].getElementsByTagName('td')[n];
                    let y = rows[i + 1].getElementsByTagName('td')[n];

                    if (!x || !y) continue;

                    let xContent = x.innerText.toLowerCase();
                    let yContent = y.innerText.toLowerCase();

                    if (asc ? (xContent > yContent) : (xContent < yContent)) {
                        shouldSwitch = true;
                        rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                        switching = true;
                        switchcount++;
                        break;
                    }
                }
                if (!switching && switchcount === 0 && asc) {
                    asc = false;
                    switching = true;
                }
            }
        }
    </script>

    <!-- Styles personnalisés -->
    <style>
        /* Icônes date & heure */
        td i.fas.fa-calendar-alt,
        td i.fas.fa-clock {
            margin-right: 5px;
            color: #647a0b;
        }

        [data-toggle="tooltip"] {
            cursor: pointer;
            text-decoration: underline dotted;
        }

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

        .table th,
        .table td {
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

        /* Bouton "Générer une facture" */
        .btn-invoice {
            background-color: #647a0b;
            border-color: #28a745;
            color: #ffffff;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.2s;
            display: inline-flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .btn-invoice i {
            margin-right: 5px;
        }

        .btn-invoice:hover {
            background-color: #647a0b;
            transform: translateY(-2px);
        }

        /* Bouton "Marquer comme complété" */
        .btn-complete {
            background-color: #647a0b;
            border-color: #007bff;
            color: #ffffff;
            padding: 8px 12px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            display: inline-flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .btn-complete i {
            margin-right: 5px;
        }

        .btn-complete:hover {
            background-color: #647a0b;
            transform: translateY(-2px);
        }

        .btn-complete-form {
            display: inline-block;
            margin-left: 5px;
        }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            border: 1px solid #854f38;
        }
    </style>
</x-app-layout>
