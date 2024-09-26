<x-app-layout>
    <x-slot name="header">
        @section('title', 'Liste des Huiles essentielles par Propriétés')
    </x-slot>

    <!-- Ensure Font Awesome icons are loaded -->
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    </head>

    <div class="container mt-5">
        <h1 class="page-title">Huiles Essentielles par Propriétés</h1>

        <!-- Description Section -->
        <div class="description-box">
            <p class="description-text">
                Découvrez les huiles essentielles classées par leurs propriétés spécifiques. Chaque huile offre une multitude de bienfaits, et nous avons organisé cette section pour vous aider à trouver rapidement celles qui correspondent à vos besoins.
            </p>
        </div>

        <!-- Property-based Display -->
        <div class="mb-4 text-end">
            <input type="text" id="search" class="form-control" placeholder="Recherche par propriété..." onkeyup="filterProperty()" style="border-color: #854f38;">
        </div>

        <div class="table-responsive mx-auto">
            @foreach($groupedByProperty as $property => $huileHEs)
                <div class="property-section mb-5"> <!-- Wrapper for each property -->
               <h2 class="text-2xl font-bold mb-4" style="color: #647a0b; text-align: left;">{{ $property }}</h2>

                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Nom Huiles Essentielles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($huileHEs as $huileHE)
                                <tr class="table-row" onclick="animateAndRedirect(this, '{{ route('huilehes.show', $huileHE->slug) }}');">
                                    <td>
                                        {{ $huileHE->NomHE }} (<em>{{ $huileHE->NomLatin ?? 'Unknown' }}</em>)
                                        @auth
                                            @if(auth()->user()->favorites->contains(fn($fav) => $fav->favoritable_id == $huileHE->id && $fav->favoritable_type == 'App\Models\HuileHE'))
                                                <i class="fas fa-heart ms-2" style="color: #854f38;"></i> <!-- Show only if it's a favorite -->
                                            @endif
                                        @endauth
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> <!-- End of property section -->
            @endforeach
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .description-box {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
            position: relative;
        }

        .description-box:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .container {
            max-width: 1200px;
            text-align: center;
        }

        .table-responsive {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table {
            width: 100%;
            max-width: 1000px;
        }

        .table thead {
            background-color: #647a0b;
            color: #ffffff;
        }

        .table tbody tr:hover {
            background-color: #854f38;
            color: #ffffff;
            transform: scale(1.02);
        }

        .table tbody tr.active {
            transform: scale(1.1);
            transition: transform 0.5s ease;
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

        .btn-favorite {
            background-color: transparent;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .btn-favorite i {
            transition: color 0.3s;
        }

        .btn-favorite:hover i {
            color: #854f38;
        }

        #search {
            width: 100%;
            max-width: 300px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #854f38;
            margin-right: 15px;
        }

        .ms-2 {
            margin-left: 8px;
        }

        /* Add space between property sections */
        .property-section {
            margin-bottom: 30px;
        }
    </style>

    <!-- JavaScript for row click animation and filtering -->
    <script>
        function animateAndRedirect(row, url) {
            row.classList.add('active');
            setTimeout(function() {
                window.location.href = url;
            }, 500);
        }

        function filterProperty() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let tables = document.querySelectorAll('.table tbody');

            tables.forEach(tbody => {
                let rows = tbody.getElementsByTagName('tr');
                let isVisible = false;
                
                for (let i = 0; i < rows.length; i++) {
                    let td = rows[i].getElementsByTagName('td')[0];
                    if (td) {
                        let txtValue = td.textContent || td.innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            rows[i].style.display = '';
                            isVisible = true;
                        } else {
                            rows[i].style.display = 'none';
                        }
                    }
                }
                tbody.closest('table').style.display = isVisible ? '' : 'none';
            });
        }
    </script>
</x-app-layout>
