<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $tisane->NomTisane }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">Tisane {{ $tisane->NomTisane }}</h1>

            <!-- Favorites Button -->
            <div class="text-center mb-4">
                @auth
                    <form id="favorite-form" method="POST" action="{{ route('favorites.toggle', ['type' => 'tisane', 'id' => $tisane->id]) }}">
                        @csrf
                        <button type="submit" class="btn btn-favorite" id="favorite-btn">
                            @if(auth()->user()->favorites->contains('favoritable_id', $tisane->id) && auth()->user()->favorites->contains('favoritable_type', 'App\Models\Tisane'))
                                <i class="fas fa-heart text-red-500"></i> <span>Retirer des Favoris</span>
                            @else
                                <i class="far fa-heart"></i> <span>Ajouter aux Favoris</span>
                            @endif
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-favorite" id="favorite-btn">
                        <i class="far fa-heart"></i> <span>Ajouter aux Favoris</span>
                    </a>
                @endauth
            </div>

            <!-- Image and Info in Two Columns -->
            <div class="row align-items-center">
                <div class="col-md-6 text-center">
                    <img src="{{ asset('images/default_tisane.webp') }}" alt="{{ $tisane->NomTisane }}" class="img-fluid" style="max-width: 400px; height: auto; margin-bottom: 20px;">
                </div>
                <div class="col-md-6">
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-leaf"></i> Nom Latin</label>
                        <p class="details-value"><em>{{ $tisane->NomLatin }}</em></p>
                    </div>
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-globe"></i> Provenance</label>
                        <p class="details-value">{{ $tisane->Provenance }}</p>
                    </div>
                </div>
            </div>

            <!-- Rest of the Information in Two Column Layout -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-capsules"></i> Propriétés</label>
                        @php
                            $properties = explode(';', $tisane->Properties);
                        @endphp
                        <ul class="details-list">
                            @foreach ($properties as $property)
                                <li>{{ trim($property) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-seedling"></i> Partie Utilisée</label>
                        <p class="details-value">{{ $tisane->OrganeProducteur }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-stethoscope"></i> Indications</label>
                        @php
                            $indications = explode(';', $tisane->Indications);
                        @endphp
                        <ul class="details-list">
                            @foreach ($indications as $indication)
                                <li>{{ trim($indication) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-exclamation-circle"></i> Contre Indications</label>
                        <p class="details-value">{{ $tisane->ContreIndications ?? 'None' }}</p>
                    </div>
                </div>
            </div>

            <div class="details-box mt-4">
                <label class="details-label"><i class="fas fa-align-left"></i> Description</label>
                <p class="details-value">{{ $tisane->Description ?? 'None' }}</p>
            </div>
			
<!-- List of Recettes where this HuileHE is used -->
<div class="details-box mt-4">
    <label class="details-label"><i class="fas fa-book-open"></i> Recettes avec {{ $tisane->NomTisane }}</label>
    @if($tisane->relatedRecettes()->isNotEmpty())
        <ul class="details-list">
            @foreach($tisane->relatedRecettes() as $recette)
                <li class="mb-2">
                    <a href="{{ route('recettes.show', $recette->slug) }}" class="recette-link">
                         {{ $recette->NomRecette }} ({{ $recette->TypeApplication }})
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <p>Aucune recette trouvée utilisant cette tisane.</p>
    @endif
</div>
            <a href="{{ route('tisanes.index') }}" class="btn btn-primary mt-4">Retour à la liste</a>

            <!-- Warning Box -->
            <div class="warning-box mt-5 p-4">
                <p class="warning-text">
                    <strong>Attention :</strong> L'auto-médication avec des produits naturels comporte des risques. L'usage inapproprié peut entraîner des effets secondaires. Les informations sur ce site ne constituent pas des conseils médicaux. Consultez un professionnel de santé avant utilisation.
                </p>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
			.recette-link {
		text-decoration: none;
		color: #16a34a;
		}

		.recette-link:hover {
			text-decoration: underline;
			color: #15803d;
		}
        .btn-favorite {
            background-color: transparent;
            border: none;
            color: #ff5a5f;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .btn-favorite:hover {
            color: #ff0000;
        }

        .container {
            max-width: 1200px;
        }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .details-title {
            font-size: 2rem;
            font-weight: bold;
            color: #333333;
            margin-bottom: 10px;
            text-align: center;
        }

        .details-box {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .details-label {
            font-weight: bold;
            color: #555555;
            margin-bottom: 10px;
            display: block;
            font-size: 1.1rem;
        }

        .details-value {
            color: #333333;
            font-size: 1rem;
            margin: 0;
        }

        .details-list {
            list-style-type: disc;
            margin: 0;
            padding-left: 20px;
        }

        .details-list li {
            margin-bottom: 5px;
            font-size: 1rem;
            color: #333333;
        }

        .flag-icon {
            margin-right: 8px;
        }

        .btn-primary {
            background-color: #16a34a;
            border-color: #16a34a;
        }

        .btn-primary:hover {
            background-color: #15803d;
            border-color: #15803d;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .col-md-6 {
            flex: 0 0 calc(50% - 10px);
        }

        .col-md-6 .details-box {
            min-height: 150px;
        }

        @media (max-width: 768px) {
            .col-md-6 {
                flex: 100%;
            }
        }

        img.img-fluid {
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Warning Box */
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .warning-text {
            color: #856404;
            font-size: 1rem;
            font-weight: 500;
        }
    </style>

    <!-- Font Awesome for icons and Flag Icons for flags -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css">
</x-app-layout>
