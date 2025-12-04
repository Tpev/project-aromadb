{{-- resources/views/appointments/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-center">
            <h2 class="font-semibold text-xl" style="color: #647a0b;">
                {{ __('Liste des Rendez-vous') }}
            </h2>
        </div>
    </x-slot>

    {{-- Font Awesome --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <div class="am-page">
        {{-- En-tête + recherche + bouton --}}
        <div class="am-card mb-4">
            <div class="am-card-body">
                <h1 class="page-title mb-2 text-center">Liste des rendez-vous</h1>
                <p class="text-muted small text-center mb-4">
                    Visualisez vos rendez-vous dans le calendrier et retrouvez-les en dessous,
                    séparés en rendez-vous à venir et passés.
                </p>

                <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-3">
                    {{-- Recherche client --}}
                    <input type="text"
                           id="search"
                           class="form-control am-search-input"
                           placeholder="Recherche par client..."
                           onkeyup="filterTable()">

                    {{-- Bouton pour créer un nouveau rendez-vous --}}
                    <a href="{{ route('appointments.create') }}"
                       class="btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Créer un rendez-vous
                    </a>
                </div>
            </div>
        </div>

        {{-- Calendrier --}}
        <div class="am-card mb-4">
            <div class="am-card-header text-center">
                <h2 class="h5 mb-1" style="color:#647a0b;">
                    Calendrier des rendez-vous
                </h2>
                <p class="text-muted small mb-0">
                    Cliquez sur un événement dans le calendrier pour ouvrir le détail du rendez-vous.
                </p>
            </div>
            <div class="am-card-body">
                <div id="calendar" class="am-calendar-wrapper"></div>
            </div>
        </div>

        {{-- ============================
             Rendez-vous à venir
        ============================= --}}
        <div class="am-card mb-4">
            <div class="am-card-header text-center">
                <h2 class="h5 mb-1" style="color:#647a0b;">
                    Rendez-vous à venir
                </h2>
                <p class="text-muted small mb-0">
                    Tri possible sur chaque colonne. Cliquez sur une ligne pour ouvrir le rendez-vous.
                </p>
            </div>

            <div class="am-card-body">
                @if($rendezVousAVenir->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table appointment-table"
                               id="upcomingTable">
                            <thead>
                                <tr>
                                    <th onclick="sortTable('upcomingTable', 0)">
                                        Nom du client
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th onclick="sortTable('upcomingTable', 1)">
                                        Date du rendez-vous
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th onclick="sortTable('upcomingTable', 2)">
                                        Durée
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th onclick="sortTable('upcomingTable', 3)">
                                        Produit
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th onclick="sortTable('upcomingTable', 4)">
                                        Statut
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th>
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rendezVousAVenir as $appointment)
                                    <tr class="table-row am-row-clickable"
                                        data-url="{{ route('appointments.show', $appointment->id) }}">
                                        {{-- Nom du client / externe --}}
                                        <td>
                                            @if($appointment->external)
                                                <span class="d-inline-flex align-items-center justify-content-center gap-1 text-secondary">
                                                    <i class="fas fa-link small text-muted"></i>
                                                    {{ $appointment->notes ?: 'Occupé' }}
                                                </span>
                                            @else
                                                <span class="d-inline-flex align-items-center justify-content-center gap-1">
                                                    <i class="fas fa-user small" style="color:#647a0b;"></i>
                                                    {{ optional($appointment->clientProfile)->first_name }}
                                                    {{ optional($appointment->clientProfile)->last_name }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Date + heure --}}
                                        <td>
                                            <div class="d-flex flex-column align-items-center">
                                                <span>
                                                    <i class="fas fa-calendar-alt me-1 small" style="color:#647a0b;"></i>
                                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d/m/Y') }}
                                                </span>
                                                <span class="small text-muted mt-1">
                                                    <i class="fas fa-clock me-1 small" style="color:#647a0b;"></i>
                                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('H:i') }}
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Durée --}}
                                        <td>
                                            {{ $appointment->duration }} min
                                        </td>

                                        {{-- Produit --}}
                                        <td>
                                            {{ $appointment->product->name ?? '—' }}
                                        </td>

                                        {{-- Statut --}}
                                        <td>
                                            <span id="status-{{ $appointment->id }}"
                                                  class="badge rounded-pill px-3 py-2
                                                  @if($appointment->status === 'Complété')
                                                      bg-success-subtle text-success
                                                  @else
                                                      bg-warning-subtle text-warning
                                                  @endif">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </td>

                                        {{-- Actions (masquées pour external) --}}
                                        <td>
                                            @unless($appointment->external)
                                                <div class="d-flex justify-content-center flex-wrap gap-2">
                                                    {{-- Générer une facture --}}
                                                    <a href="{{ route('invoices.create', [
                                                             'client_id'  => $appointment->client_profile_id,
                                                             'product_id' => $appointment->product_id]) }}"
                                                       class="btn-invoice">
                                                        <i class="fas fa-file-invoice-dollar"></i>
                                                        Facturer
                                                    </a>

                                                    {{-- Marquer comme complété --}}
                                                    @if ($appointment->status !== 'Complété')
                                                        <form action="{{ route('appointments.completeindex', $appointment->id) }}"
                                                              method="POST"
                                                              class="btn-complete-form">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit"
                                                                    class="btn-complete"
                                                                    onclick="return confirm('Marquer ce rendez-vous comme complété ?')">
                                                                <i class="fas fa-check-circle"></i>
                                                                Compléter
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @endunless
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted small text-center mb-0">
                        Aucun rendez-vous à venir pour le moment.
                    </p>
                @endif
            </div>
        </div>

        {{-- ============================
             Rendez-vous passés
        ============================= --}}
        <div class="am-card mb-5">
            <div class="am-card-header text-center">
                <h2 class="h5 mb-1" style="color:#854f38;">
                    Rendez-vous passés
                </h2>
                <p class="text-muted small mb-0">
                    Historique de vos rendez-vous déjà effectués.
                </p>
            </div>

            <div class="am-card-body">
                @if($rendezVousPasses->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table appointment-table"
                               id="pastTable">
                            <thead>
                                <tr>
                                    <th onclick="sortTable('pastTable', 0)">
                                        Nom du client
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th onclick="sortTable('pastTable', 1)">
                                        Date du rendez-vous
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th onclick="sortTable('pastTable', 2)">
                                        Durée
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th onclick="sortTable('pastTable', 3)">
                                        Produit
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th onclick="sortTable('pastTable', 4)">
                                        Statut
                                        <i class="fas fa-sort ms-1"></i>
                                    </th>
                                    <th>
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rendezVousPasses as $appointment)
                                    <tr class="table-row am-row-clickable"
                                        data-url="{{ route('appointments.show', $appointment->id) }}">
                                        {{-- Nom du client / externe --}}
                                        <td>
                                            @if($appointment->external)
                                                <span class="d-inline-flex align-items-center justify-content-center gap-1 text-secondary">
                                                    <i class="fas fa-link small text-muted"></i>
                                                    {{ $appointment->notes ?: 'Occupé' }}
                                                </span>
                                            @else
                                                <span class="d-inline-flex align-items-center justify-content-center gap-1">
                                                    <i class="fas fa-user small" style="color:#854f38;"></i>
                                                    {{ optional($appointment->clientProfile)->first_name }}
                                                    {{ optional($appointment->clientProfile)->last_name }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Date + heure --}}
                                        <td>
                                            <div class="d-flex flex-column align-items-center">
                                                <span>
                                                    <i class="fas fa-calendar-alt me-1 small" style="color:#854f38;"></i>
                                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d/m/Y') }}
                                                </span>
                                                <span class="small text-muted mt-1">
                                                    <i class="fas fa-clock me-1 small" style="color:#854f38;"></i>
                                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('H:i') }}
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Durée --}}
                                        <td>
                                            {{ $appointment->duration }} min
                                        </td>

                                        {{-- Produit --}}
                                        <td>
                                            {{ $appointment->product->name ?? '—' }}
                                        </td>

                                        {{-- Statut --}}
                                        <td>
                                            <span id="status-{{ $appointment->id }}"
                                                  class="badge rounded-pill px-3 py-2
                                                  @if($appointment->status === 'Complété')
                                                      bg-success-subtle text-success
                                                  @else
                                                      bg-secondary-subtle text-secondary
                                                  @endif">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </td>

                                        {{-- Actions (masquées pour external) --}}
                                        <td>
                                            @unless($appointment->external)
                                                <div class="d-flex justify-content-center flex-wrap gap-2">
                                                    {{-- Générer une facture --}}
                                                    <a href="{{ route('invoices.create', [
                                                             'client_id'  => $appointment->client_profile_id,
                                                             'product_id' => $appointment->product_id]) }}"
                                                       class="btn-invoice">
                                                        <i class="fas fa-file-invoice-dollar"></i>
                                                        Facturer
                                                    </a>

                                                    {{-- Marquer comme complété (au cas où oublié) --}}
                                                    @if ($appointment->status !== 'Complété')
                                                        <form action="{{ route('appointments.completeindex', $appointment->id) }}"
                                                              method="POST"
                                                              class="btn-complete-form">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit"
                                                                    class="btn-complete"
                                                                    onclick="return confirm('Marquer ce rendez-vous comme complété ?')">
                                                                <i class="fas fa-check-circle"></i>
                                                                Compléter
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @endunless
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted small text-center mb-0">
                        Aucun rendez-vous passé enregistré.
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Meta CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Scripts personnalisés --}}
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
                firstDay: 1, // Lundi
                events: @json($events),
                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                        info.jsEvent.preventDefault();
                    }
                }
            });

            calendar.render();

            // Clic sur la ligne -> show rendez-vous
            const rows = document.querySelectorAll('.table-row');
            rows.forEach(row => {
                row.addEventListener('click', function(e) {
                    // Pas de redirection si clic sur bouton ou lien
                    if (e.target.closest('button') || e.target.closest('a')) return;
                    const url = this.getAttribute('data-url');
                    if (url) window.location.href = url;
                });
            });
        });

        // Filtrage global (deux tableaux)
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

        // Tri par table + colonne
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

    {{-- Styles personnalisés --}}
    <style>
        .am-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem 3rem 1rem;
            text-align: center;
        }

        .page-title {
            font-size: 1.9rem;
            font-weight: 600;
            color: #647a0b;
        }

        .am-card {
            background-color: #ffffff;
            border-radius: 18px;
            border: 1px solid #e7ebd8;
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.04);
        }

        .am-card-header {
            padding: 16px 22px 10px 22px;
            border-bottom: 1px solid #edf0dd;
            background: linear-gradient(180deg, #fafcf3, #ffffff);
            border-radius: 18px 18px 0 0;
        }

        .am-card-body {
            padding: 20px 22px 22px 22px;
        }

        .am-calendar-wrapper {
            width: 100%;
        }

        .am-search-input {
            border-radius: 9999px;
            border-color: #854f38;
            padding-inline: 16px;
            max-width: 280px;
        }

        .btn-primary {
            background-color: #647a0b;
            border-color: #647a0b;
            color: #ffffff;
            padding: 9px 22px;
            border-radius: 9999px;
            text-decoration: none;
            transition: background-color 0.2s ease, transform 0.1s ease;
            display: inline-flex;
            align-items: center;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .btn-primary:hover {
            background-color: #854f38;
            border-color: #854f38;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .appointment-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .appointment-table thead {
            background-color: #f5f6ea;
        }

        .appointment-table thead th {
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #4b5563;
            border-bottom: none;
            cursor: pointer;
            white-space: nowrap;
            text-align: center;
            padding: 0.75rem 1.25rem;
        }

        .appointment-table tbody tr {
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            border-radius: 14px;
            transition: background-color 0.2s ease, transform 0.1s ease, box-shadow 0.2s ease;
        }

        .appointment-table tbody tr:hover {
            background-color: #f5f7eb;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        }

        .appointment-table tbody td {
            text-align: center;
            padding: 0.85rem 1.4rem;
            border-top: none !important;
        }

        .am-row-clickable {
            cursor: pointer;
        }

        /* Bouton "Générer une facture" */
        .btn-invoice {
            background-color: #647a0b;
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 9999px;
            text-decoration: none;
            transition: background-color 0.2s ease, transform 0.1s ease;
            display: inline-flex;
            align-items: center;
            font-size: 0.8rem;
            border: none;
            white-space: nowrap;
        }

        .btn-invoice i {
            margin-right: 4px;
        }

        .btn-invoice:hover {
            background-color: #854f38;
            transform: translateY(-1px);
            color: #ffffff;
        }

        /* Bouton "Marquer comme complété" */
        .btn-complete {
            background-color: #ffffff;
            color: #647a0b;
            padding: 6px 12px;
            border-radius: 9999px;
            border: 1px solid #647a0b;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.1s ease;
            display: inline-flex;
            align-items: center;
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .btn-complete i {
            margin-right: 4px;
        }

        .btn-complete:hover {
            background-color: #647a0b;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-complete-form {
            display: inline-block;
        }

        /* Soft badges */
        .bg-success-subtle {
            background-color: #e6f7ec !important;
        }
        .bg-warning-subtle {
            background-color: #fff7e6 !important;
        }
        .bg-secondary-subtle {
            background-color: #f3f4f6 !important;
        }

        /* Gaps helpers (au cas où) */
        .gap-2 {
            gap: .5rem !important;
        }
        .gap-3 {
            gap: 1rem !important;
        }
    </style>
</x-app-layout>
