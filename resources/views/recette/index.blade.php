<x-app-layout>
    <x-slot name="header">
@section('title', 'Liste des Recettes')
    </x-slot>

    <!-- Ensure Font Awesome icons are loaded -->
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    </head>

    <div class="container mt-5">
        <h1 class="page-title">Liste des Recettes</h1>

<!-- Description Section for Recette Page -->
<div class="description-box">
    <p class="description-text">
        Bienvenue dans notre collection de recettes d'aromathérapie. Vous trouverez ici des préparations spécifiques à base d'huiles essentielles, d'huiles végétales, et de tisanes pour diverses applications, telles que cutanées, inhalation, diffusion, et voie orale. Chaque recette est minutieusement élaborée pour répondre à des besoins spécifiques, comme soulager les douleurs musculaires, améliorer la digestion, ou traiter des affections cutanées. Que vous soyez un passionné d'aromathérapie ou un professionnel, cette base de données vous fournira des solutions naturelles et efficaces pour vous accompagner au quotidien. Explorez les recettes, découvrez les bienfaits des ingrédients utilisés, et apprenez à appliquer chaque formule en toute sécurité.
    </p>
</div>
        <!-- Search Bar -->
        <div class="mb-4 text-end">
            <input type="text" id="search" class="form-control" placeholder="Recherche par nom..." onkeyup="filterTable()" style="border-color: #854f38;">
        </div>

        <div class="table-responsive mx-auto">
            <table class="table table-bordered table-hover mx-auto" id="recetteTable">
                <thead>
                    <tr>
                        <th class="text-center">Nom Recette</th>
                        <th class="text-center">Type Application</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recettes as $recette)
                        <tr class="table-row text-center" onclick="window.location='{{ route('recettes.show', $recette->slug) }}';">
                            <td>
                                {{ $recette->NomRecette }}
                                @auth
                                    @if(auth()->user()->favorites->contains(fn($fav) => $fav->favoritable_id == $recette->id && $fav->favoritable_type == 'App\Models\Recette'))
                                        <i class="fas fa-heart ms-2" style="color: #854f38;"></i> <!-- Show heart if favorited -->
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
            background-color: #647a0b; /* Primary color */
            color: #ffffff;
        }

        .table tbody tr {
            transition: background-color 0.3s, color 0.3s; /* Smooth transition */
            cursor: pointer; /* Change cursor to pointer to indicate the row is clickable */
        }

        .table tbody tr:hover {
            background-color: #854f38; /* Hover color */
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
            color: #647a0b; /* Primary color */
            margin-bottom: 20px;
            text-align: center;
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
            color: #854f38; /* Hover color */
        }

        #search {
            width: 100%;
            max-width: 300px; /* Adjust the width of the search bar */
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #854f38;
            margin-right: 15px; /* Add a bit of margin on the right for spacing */
        }

        .text-end {
            padding-right: 15px; /* Ensure there's padding on the right side */
        }

        .ms-2 {
            margin-left: 8px;
        }
		
		
		    .description-box {
        background-color: #f9f9f9;
        border-radius: 10px;
        padding: 20px 30px;
        margin-bottom: 30px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease-in-out;
        position: relative;
        overflow: hidden;
    }

    .description-box:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .description-text {
        font-size: 1.2rem;
        line-height: 1.7;
        color: #333;
        text-align: justify;
    }

    /* Animation */
    .description-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 300%;
        height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.5), transparent);
        transition: all 0.3s ease-in-out;
    }

    .description-box:hover::before {
        left: 100%;
    }
    </style>

    <!-- Search Filter Script -->
    <script>
        function filterTable() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('recetteTable');
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
