<x-app-layout>
    <x-slot name="header">
        @section('title', 'Liste des Huiles Essentielles')
    </x-slot>

    <!-- Ensure Font Awesome and Bootstrap are loaded -->
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <!-- Bootstrap CSS (if not already included) -->
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    </head>

    <div class="container mt-5">
        <h1 class="page-title">Liste des Huiles Essentielles</h1>

        <!-- Description Section -->
        <div class="description-box">
            <p class="description-text">
                Bienvenue dans notre bibliothèque d'huiles essentielles, un guide complet dédié aux propriétés et usages des huiles essentielles. Explorez des fiches détaillées sur chaque huile essentielle, avec des informations précises sur leurs bienfaits, leurs indications thérapeutiques, ainsi que des conseils d’utilisation sécurisée. Utilisez les filtres pour parcourir les huiles en fonction de leurs propriétés spécifiques, ou recherchez directement par nom. Cette base de données est conçue pour vous offrir une expertise approfondie sur l'aromathérapie, idéale pour les thérapeutes, passionnés et praticiens.
            </p>
        </div>

        <!-- Filter and Search Form -->
        <form method="GET" action="{{ route('huilehes.index') }}" class="mb-4">
            <div class="row justify-content-end align-items-center">
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <label for="indicationFilter" class="sr-only">Filtrer par Indication</label>
                    <select name="indication" id="indicationFilter" class="form-control">
                        <option value="">Filtrer par Indication</option>
                        @foreach($indications as $indication)
                            <option value="{{ trim($indication) }}" {{ request('indication') == trim($indication) ? 'selected' : '' }}>
                                {{ trim($indication) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <label for="search" class="sr-only">Recherche par Nom</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Recherche par nom..." value="{{ request('search') }}">
                </div>

                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-filter btn-block">Filtrer</button>
                </div>
            </div>
        </form>

        <!-- Grid View -->
        <div class="row" id="huileGrid">
            @forelse($huileHEs as $huileHE)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4 huile-card" data-indications="{{ strtolower(str_replace(';', ' ', $huileHE->Indications)) }}">
                    <a href="{{ route('huilehes.show', $huileHE->slug) }}" class="card-link">
                        <div class="card h-100 shadow-sm">
                            @php
                                $imagePath = 'images/' . $huileHE->slug . '.webp';
                                $defaultImage = 'images/default.webp';
                                $finalImage = file_exists(public_path($imagePath)) ? $imagePath : $defaultImage;
                            @endphp
                            <img src="{{ asset($finalImage) }}" class="card-img-top" alt="{{ $huileHE->NomHE }}" loading="lazy">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    {{ $huileHE->NomHE }} 
                                    <small class="text-muted"><em>{{ $huileHE->NomLatin ?? 'Inconnu' }}</em></small>
                                    @auth
                                        @if(auth()->user()->favorites->contains(fn($fav) => $fav->favoritable_id == $huileHE->id && $fav->favoritable_type == 'App\Models\HuileHE'))
                                            <i class="fas fa-heart ms-2 favorite-icon"></i>
                                        @endif
                                    @endauth
                                </h5>
                                <p class="card-text mt-auto">{{ Str::limit($huileHE->description, 100) }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-center">Aucune huile essentielle trouvée.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination Links -->
        <div class="d-flex justify-content-center">
            {{ $huileHEs->links() }}
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        /* Theme Colors - Update these variables to match your site's theme */
        :root {
            --primary-color: #647a0b; /* Example: Dark Green */
            --secondary-color: #854f38; /* Example: Brownish */
            --card-hover-shadow: rgba(133, 79, 56, 0.2); /* Using secondary color with transparency */
            --description-bg: #f9f9f9;
            --description-hover-bg: #ffffff;
            --text-color: #333;
            --title-color: var(--secondary-color); /* Updated to use secondary color */
            --favorite-color: var(--secondary-color);
            --card-text-color: var(--secondary-color); /* New variable for card text */
            --input-border-color: var(--secondary-color);
            --input-focus-shadow: rgba(133, 79, 56, 0.5);
        }

        body {
            color: var(--text-color);
        }

        .description-box {
            background-color: var(--description-bg);
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            position: relative;
            overflow: hidden;
            text-align: justify;
        }

        .description-box:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 20px var(--card-hover-shadow);
        }

        .description-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 300%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            transition: left 0.3s ease-in-out;
        }

        .description-box:hover::before {
            left: 100%;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 20px;
            text-align: center;
        }

        .card-link {
            text-decoration: none;
            color: inherit;
        }

        .card {
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            border: none;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 20px var(--card-hover-shadow);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: calc(0.25rem - 1px);
            border-top-right-radius: calc(0.25rem - 1px);
        }

        .card-title {
            color: var(--title-color); /* Use secondary color for title */
        }

        .card-text {
            color: var(--card-text-color); /* Use secondary color for card text */
        }

        .favorite-icon {
            font-size: 1.2rem;
            color: var(--favorite-color);
        }

        /* Filter and Search Styles */
        select[name="indication"], input[name="search"] {
            border: 1px solid var(--input-border-color);
            padding: 8px;
            border-radius: 5px;
            background-color: #fff;
            color: var(--text-color);
        }

        select[name="indication"]:focus, input[name="search"]:focus {
            outline: none;
            box-shadow: 0 0 5px var(--input-focus-shadow);
        }

        /* Custom Filter Button */
        .btn-filter {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .btn-filter:hover {
            background-color: #546a08; /* Couleur plus foncée de var(--primary-color) */
            border-color: #546a08; /* Couleur plus foncée de var(--primary-color) */
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            padding: 1rem 0;
        }

        .pagination li a, .pagination li span {
            color: var(--secondary-color);
            border: 1px solid var(--input-border-color);
            padding: 0.5rem 0.75rem;
            margin: 0 0.25rem;
            border-radius: 0.25rem;
            text-decoration: none;
        }

        .pagination li.active span {
            background-color: var(--secondary-color);
            color: #fff;
            border-color: var(--secondary-color);
        }

        .pagination li.disabled span {
            color: #6c757d;
            cursor: not-allowed;
        }

        /* Responsive Adjustments */
        @media (max-width: 576px) {
            .card-img-top {
                height: 150px;
            }
        }

        @media (min-width: 1200px) {
            .container {
                max-width: 1400px;
            }
        }
    </style>
</x-app-layout>
