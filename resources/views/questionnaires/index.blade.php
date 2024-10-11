<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Liste des Questionnaires') }}
        </h2>
    </x-slot>

    <!-- Lien vers Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <div class="container mt-5">
        <h1 class="page-title">{{ __('Liste des Questionnaires') }}</h1>

        <!-- Barre de recherche et bouton de création -->
        <div class="mb-4 d-flex justify-content-between">
            <input type="text" id="search" class="form-control" placeholder="{{ __('Recherche par titre ou description...') }}" onkeyup="filterTable()" style="border-color: #854f38; max-width: 300px;">
            <a href="{{ route('questionnaires.create') }}" class="btn btn-primary" style="white-space: nowrap;">
                <i class="fas fa-plus mr-2"></i> {{ __('Créer un Questionnaire') }}
            </a>
        </div>

        <!-- Table des questionnaires -->
        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover" id="questionnaireTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">{{ __('Titre') }} <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(1)">{{ __('Description') }} <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(2)">{{ __('Date de création') }} <i class="fas fa-sort"></i></th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($questionnaires as $questionnaire)
                        <tr>
                            <td>{{ $questionnaire->title }}</td>
                            <td>{{ $questionnaire->description ?? __('Aucune description') }}</td>
                            <td>{{ $questionnaire->created_at->format('d/m/Y') }}</td>
                            <td class="action-buttons">
                                <a href="{{ route('questionnaires.show', $questionnaire->id) }}" class="btn btn-info btn-sm">{{ __('Voir') }}</a>
                                <form action="{{ route('questionnaires.destroy', $questionnaire->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer ce questionnaire ?') }}');">{{ __('Supprimer') }}</button>
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

        .btn-info {
            background-color: #17a2b8;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            margin-right: 5px;
            border: none;
            transition: background-color 0.3s;
        }

        .btn-info:hover {
            background-color: #138496;
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

    <!-- JavaScript pour le tri et la redirection -->
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
            let table = document.getElementById('questionnaireTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let tdTitle = tr[i].getElementsByTagName('td')[0];
                let tdDescription = tr[i].getElementsByTagName('td')[1];
                if (tdTitle && tdDescription) {
                    let txtValueTitle = tdTitle.textContent || tdTitle.innerText;
                    let txtValueDescription = tdDescription.textContent || tdDescription.innerText;
                    if (txtValueTitle.toLowerCase().indexOf(filter) > -1 || txtValueDescription.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = '';
                    } else {
                        tr[i].style.display = 'none';
                    }
                }
            }
        }

        function sortTable(n) {
            let table = document.getElementById('questionnaireTable');
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
