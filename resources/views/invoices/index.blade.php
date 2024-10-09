<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Liste des Factures') }}
        </h2>
    </x-slot>
    <!-- Lien vers Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <div class="container mt-5">
        <h1 class="page-title">Liste des Factures</h1>

        <!-- Barre de recherche et bouton de création -->
        <div class="mb-4 d-flex justify-content-between">
            <input type="text" id="search" class="form-control" placeholder="Recherche par client ou statut..." onkeyup="filterTable()" style="border-color: #854f38; max-width: 300px;">

            <!-- Bouton pour créer une facture -->
            <a href="{{ route('invoices.create') }}" class="btn-primary" style="white-space: nowrap;">
                <i class="fas fa-plus mr-2"></i> Créer une facture
            </a>
        </div>

        <!-- Table triable et filtrable -->
        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover" id="invoiceTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">Numéro de Facture <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(1)">Client <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(2)">Date de Facture <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(3)">Montant Total TTC <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(4)">Statut <i class="fas fa-sort"></i></th>
                        <!-- New Column for Email Sent Status -->
                        <th onclick="sortTable(5)">Envoyée <i class="fas fa-sort"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                        <tr class="table-row" onclick="animateAndRedirect(this, '{{ route('invoices.show', $invoice->id) }}');">
                            <td>{{ $invoice->id }}</td>
                            <td>{{ $invoice->clientProfile->first_name }} {{ $invoice->clientProfile->last_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</td>
                            <td>{{ number_format($invoice->total_amount_with_tax, 2, ',', ' ') }} €</td>
                            <td>{{ ucfirst($invoice->status) }}</td>
                            <!-- Email Sent Status Cell -->
                            <td>
                                @if(is_null($invoice->sent_at))
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-times-circle"></i> Non Envoyée
                                    </span>
                                @else
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> Envoyée le {{ \Carbon\Carbon::parse($invoice->sent_at)->format('d/m/Y à H:i') }}
                                    </span>
                                @endif
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

        /* Badge Styles */
        .badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: #fff;
        }

        .badge-success {
            background-color: #28a745;
            color: #fff;
        }

        .badge i {
            margin-right: 5px;
        }

        /* Responsive Adjustments */
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

            .justify-content-between {
                justify-content: flex-start;
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

    <!-- JavaScript pour le tri, le filtrage et la redirection -->
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
            let table = document.getElementById('invoiceTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let tdClient = tr[i].getElementsByTagName('td')[1];
                let tdStatus = tr[i].getElementsByTagName('td')[4];
                if (tdClient && tdStatus) {
                    let txtValueClient = tdClient.textContent || tdClient.innerText;
                    let txtValueStatus = tdStatus.textContent || tdStatus.innerText;
                    if (txtValueClient.toLowerCase().indexOf(filter) > -1 || txtValueStatus.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = '';
                    } else {
                        tr[i].style.display = 'none';
                    }
                }
            }
        }

        function sortTable(n) {
            let table = document.getElementById('invoiceTable');
            let rows = table.rows;
            let switching = true;
            let dir = 'asc';
            let switchcount = 0;

            while (switching) {
                switching = false;
                let rowsArray = Array.from(rows).slice(1); // Exclure l'en-tête
                for (let i = 0; i < rowsArray.length - 1; i++) {
                    let shouldSwitch = false;
                    let x = rowsArray[i].getElementsByTagName('td')[n];
                    let y = rowsArray[i + 1].getElementsByTagName('td')[n];

                    let xContent = x.innerHTML.toLowerCase();
                    let yContent = y.innerHTML.toLowerCase();

                    if (n === 0 || n === 3) { // Numéro de facture ou Montant Total
                        xContent = parseFloat(xContent.replace(',', '.').replace(/[^0-9.-]+/g,""));
                        yContent = parseFloat(yContent.replace(',', '.').replace(/[^0-9.-]+/g,""));
                    } else if (n === 2) { // Date de Facture
                        xContent = new Date(xContent.split('/').reverse().join('-'));
                        yContent = new Date(yContent.split('/').reverse().join('-'));
                    } else if (n === 5) { // Envoyée (sent_at)
                        if (xContent.includes('non envoyée')) {
                            xContent = 0;
                        } else {
                            let dateStr = xContent.split('le ')[1];
                            xContent = new Date(dateStr.split(' à ')[0] + 'T' + dateStr.split(' à ')[1]);
                        }

                        if (yContent.includes('non envoyée')) {
                            yContent = 0;
                        } else {
                            let dateStr = yContent.split('le ')[1];
                            yContent = new Date(dateStr.split(' à ')[0] + 'T' + dateStr.split(' à ')[1]);
                        }
                    }

                    if (dir === 'asc') {
                        if (xContent > yContent) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir === 'desc') {
                        if (xContent < yContent) {
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
