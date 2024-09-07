<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-green-600 leading-tight">
            {{ __('Aroma Made DB') }}
        </h2>
    </x-slot>
   <!-- Ensure Font Awesome icons are loaded -->
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    </head>
    <div class="container mt-5">
        <h1 class="page-title">Liste des Recettes</h1>

        <!-- Search Bar -->
        <div class="mb-4 text-end">
            <input type="text" id="search" class="form-control" placeholder="Recherche par nom..." onkeyup="filterTable()">
        </div>

        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover mx-auto" id="huileTable">
                <thead>
                    <tr>
                        <th class="text-center">Nom Recette</th>
                        <th class="text-center">Type Application</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recettes as $recette)
                        <tr class="table-row text-center" onclick="window.location='{{ route('recettes.show', $recette->id) }}';">
                            <td>
                                {{ $recette->NomRecette }}
                                @auth
                                    @if(auth()->user()->favorites->contains(fn($fav) => $fav->favoritable_id == $recette->id && $fav->favoritable_type == 'App\Models\Recette'))
                                        <i class="fas fa-heart text-red-500 ms-2"></i> <!-- Show heart if favorited -->
                                    @endif
                                @endauth
                            </td>
                            <td>{{ $recette->TypeApplication }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 1200px; /* Make the container wider */
            text-align: center; /* Center content within the container */
        }
        .table-responsive {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto; /* Center the table container */
            display: flex;
            justify-content: center; /* Ensure the table is centered */
        }
        .table {
            width: 100%; /* Ensure the table takes up the full width of the container */
            max-width: 1000px; /* Control the maximum width of the table */
        }
        .table thead {
            background-color: #16a34a; /* Tailwind CSS green-600 color */
            color: #ffffff;
        }
        .table tbody tr {
            transition: background-color 0.3s, color 0.3s; /* Smooth transition */
            cursor: pointer; /* Change cursor to pointer to indicate the row is clickable */
        }
        .table tbody tr:hover {
            background-color: #16a34a; /* Match the hover color of the button */
            color: #ffffff; /* Change text color to white on hover */
        }
        .table tbody tr:hover a {
            color: #ffffff; /* Ensure links also turn white on hover */
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center; /* Center content within table cells */
        }
        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: #333333;
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-add {
            background-color: #007bff;
            color: #ffffff;
            margin-bottom: 20px;
        }
        .btn-add:hover {
            background-color: #0056b3;
        }
        #search {
            width: 100%;
            max-width: 300px; /* Adjust the width of the search bar */
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-right: 15px; /* Add a bit of margin on the right for spacing */
        }
        .text-end {
            padding-right: 15px; /* Ensure there's padding on the right side */
        }
        .ms-2 {
            margin-left: 8px;
        }
    </style>

    <!-- Search Filter Script -->
    <script>
        function filterTable() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('huileTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td')[0];
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
        }
    </script>
</x-app-layout>
