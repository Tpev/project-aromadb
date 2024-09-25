<x-app-layout>
    <!-- Meta Description -->
@section('meta_description')
    {{ $huileHE->MetaDesc ?? 'Default meta description for this Huile Végétale.' }}
@endsection
@section('title', 'Huile Végétale ' . $huileHV->NomHV . ' (' . $huileHV->NomLatin . ')')
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ $huileHV->NomHV }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">Huile végétale {{ $huileHV->NomHV }}</h1>

            <!-- Favorites Button -->
            <div class="text-center mb-4">
                @auth
                    <form id="favorite-form" method="POST" action="{{ route('favorites.toggle', ['type' => 'huilehv', 'id' => $huileHV->id]) }}">
                        @csrf
                        <button type="submit" class="btn btn-favorite" id="favorite-btn">
                            @if(auth()->user()->favorites->contains('favoritable_id', $huileHV->id) && auth()->user()->favorites->contains('favoritable_type', 'App\Models\HuileHV'))
                                <i class="fas fa-heart" style="color: #647a0b;"></i> <span>Retirer des Favoris</span>
                            @else
                               <i class="far fa-heart" style="color: #647a0b;"></i> <span>Ajouter aux Favoris</span>
                            @endif
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-favorite" id="favorite-btn">
                        <i class="far fa-heart" style="color: #854f38;"></i> <span>Ajouter aux Favoris</span>
                    </a>
                @endauth
            </div>

            <!-- Image and Info in Two Columns -->
            <div class="row align-items-center">
                <div class="col-md-6 text-center">
                    <img src="{{ asset('images/default.webp') }}" alt="{{ $huileHV->NomHV }}" class="img-fluid" style="max-width: 400px; height: auto; margin-bottom: 20px;">
                </div>
                <div class="col-md-6">
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-leaf" style="color: #647a0b;"></i> Latin Name</label>
                        <p class="details-value"><em>{{ $huileHV->NomLatin }}</em></p>
                    </div>
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-globe" style="color: #647a0b;"></i> Provenance</label>
                        <p class="details-value">
                            @php
                                $provenances = explode(';', $huileHV->Provenance);
                                $countryMap = [
                                    'France' => 'fr',
                                    'Corse' => 'fr',
                                    'Italy' => 'it',
                                    'Espagne' => 'es',
                                    'Madagascar' => 'mg',
                                    'Vietnam' => 'vn',
                                    'Brazil' => 'br',
                                    'Portugal' => 'pt',
                                    'Spain' => 'es',
                                    'Maroc' => 'ma',
                                    'Australie' => 'au',
                                ];
                            @endphp
                            @foreach($provenances as $provenance)
                                @php
                                    $provenance = trim($provenance);
                                    $countryCode = $countryMap[$provenance] ?? null;
                                @endphp
                                @if ($countryCode)
                                    <span class="flag-icon flag-icon-{{ $countryCode }}"></span>
                                @endif
                                {{ $provenance }}<br>
                            @endforeach
                        </p>
                    </div>
                </div>
            </div>

            <!-- Rest of the Information in Two Column Layout -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-capsules" style="color: #647a0b;"></i> Propriétés</label>
                        @php
                            $properties = explode(';', $huileHV->Properties);
                        @endphp
                        <ul class="details-list">
                            @foreach ($properties as $property)
                                <li>{{ trim($property) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-seedling" style="color: #647a0b;"></i> Organe Producteur</label>
                        <p class="details-value">{{ $huileHV->OrganeProducteur }}</p>
                    </div>
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-vial" style="color: #647a0b;"></i> Sb (Substances)</label>
                        @php
                            $substances = explode(';', $huileHV->Sb);
                        @endphp
                        <ul class="details-list">
                            @foreach ($substances as $substance)
                                <li>{{ trim($substance) }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-stethoscope" style="color: #647a0b;"></i> Indications</label>
                        @php
                            $indications = explode(';', $huileHV->Indications);
                        @endphp
                        <ul class="details-list">
                            @foreach ($indications as $indication)
                                <li>{{ trim($indication) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-exclamation-circle" style="color: #647a0b;"></i> Contre Indications</label>
                        <p class="details-value">{{ $huileHV->ContreIndications ?? 'None' }}</p>
                    </div>
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-info-circle" style="color: #647a0b;"></i> Note</label>
                        <p class="details-value">{{ $huileHV->Note ?? 'None' }}</p>
                    </div>
                </div>
            </div>

            <div class="details-box mt-4">
                <label class="details-label"><i class="fas fa-align-left" style="color: #647a0b;"></i> Description</label>
                <p class="details-value">{{ $huileHV->Description ?? 'None' }}</p>
            </div>

			<!-- List of Recettes where this HuileHV is used -->
			<div class="details-box mt-4">
				<label class="details-label"><i class="fas fa-book-open" style="color: #647a0b;"></i> Recettes avec {{ $huileHV->NomHV }}</label>
				@if($huileHV->relatedRecettes()->isNotEmpty())
					<ul class="details-list">
						@foreach($huileHV->relatedRecettes() as $recette)
							<li class="mb-2">
								<a href="{{ route('recettes.show', $recette->slug) }}" class="recette-link">
									 {{ $recette->NomRecette }} ({{ $recette->TypeApplication }})
								</a>
							</li>
						@endforeach
					</ul>
				@else
					<p>Aucune recette trouvée utilisant cette huile végétale.</p>
				@endif
			</div>

            <a href="{{ route('huilehvs.index') }}" class="btn btn-primary mt-4">Retour à la liste</a>

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
            color: #647a0b;
		}

		.recette-link:hover {
			text-decoration: underline;
			color: #854f38;
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
            color: #647a0b;
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
            background-color: #647a0b;
            border-color: #647a0b;
        }

        .btn-primary:hover {
            background-color: #854f38;
            border-color: #854f38;
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
