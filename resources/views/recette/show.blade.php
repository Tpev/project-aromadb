<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ $recette->NomRecette }}
        </h2>
		@section('title', 'Recette ' . $recette->NomRecette . ' (' .  $recette->TypeApplication  . ')')
    </x-slot>

    <!-- Ensure Font Awesome icons are loaded -->
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    </head>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">Recette {{ $recette->NomRecette }}</h1>

            <!-- Favorites Button -->
            <div class="text-center mb-4">
                @auth
                    <form id="favorite-form" method="POST" action="{{ route('favorites.toggle', ['type' => 'recette', 'id' => $recette->id]) }}">
                        @csrf
                        <button type="submit" class="btn btn-favorite" id="favorite-btn">
                            @if(auth()->user()->favorites->contains('favoritable_id', $recette->id) && auth()->user()->favorites->contains('favoritable_type', 'App\Models\Recette'))
                                <i class="fas fa-heart" style="color: #854f38;"></i> <span>Retirer des Favoris</span>
                            @else
                                <i class="far fa-heart" style="color: #854f38;"></i> <span>Ajouter aux Favoris</span>
                            @endif
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-favorite" id="favorite-btn">
                        <i class="far fa-heart" style="color: #854f38;"></i> <span>Ajouter aux Favoris</span>
                    </a>
                @endauth
            </div>

            <!-- Custom JavaScript for AJAX with Console Logs -->
            @auth
            <script>
            document.getElementById('favorite-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const form = this;
                const formData = new FormData(form);
                const favoriteBtn = document.getElementById('favorite-btn');

                fetch(form.action, {
                    method: form.method,
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.action === 'added') {
                            favoriteBtn.innerHTML = '<i class="fas fa-heart" style="color: #854f38;"></i> <span>Remove from Favorites</span>';
                        } else if (data.action === 'removed') {
                            favoriteBtn.innerHTML = '<i class="far fa-heart" style="color: #854f38;"></i> <span>Add to Favorites</span>';
                        }
                    }
                })
                .catch(error => console.error('Error occurred:', error));
            });
            </script>
            @endauth

            <!-- Recipe General Information -->
            <div class="col-md-6">
                <div class="details-box">
                    <label class="details-label"><i class="fas fa-syringe" style="color: #647a0b;"></i> Type Application</label>
                    <p class="details-value">{{ $recette->TypeApplication }}</p>
                </div>
            </div>

            <!-- Ingredients Section with Accordion for HuileHE, HuileHV, and Tisane -->
            <div class="details-box">
                <label class="details-label"><i class="fas fa-vial" style="color: #647a0b;"></i> Ingredients</label>

                @if(count($parsed_ingredients_he) > 0)
                    <h2 class="section-title">Huile Essentielle (HE)</h2>
                    <ul>
                        @foreach($parsed_ingredients_he as $ingredient)
							<li class="mb-3 d-flex align-items-center">
								<div>
									<p class="mb-0">&bull; {{ $ingredient['quantity'] }} {{ $ingredient['huile']->NomHE ?? 'Unknown' }} (<em>{{ $ingredient['huile']->NomLatin ?? 'Unknown' }}</em>)
			
									<button class="accordion-toggle btn btn-sm btn-theme" onclick="toggleAccordion(this)">En savoir plus</button></p>
								</div>
							</li>


                            <div class="custom-accordion">
								<div class="accordion-content">
									<p><strong><i class="fas fa-globe" style="color: #647a0b;"></i> Provenance:</strong> {{ $ingredient['huile']->Provenance ?? 'Unknown' }}</p>
									<p><strong><i class="fas fa-seedling" style="color: #647a0b;"></i> Organe Producteur:</strong> {{ $ingredient['huile']->OrganeProducteur ?? 'Unknown' }}</p>
									<p><strong><i class="fas fa-vial" style="color: #647a0b;"></i> Substances (Sb):</strong> {{ $ingredient['huile']->Sb ?? 'Unknown' }}</p>
									<p><strong><i class="fas fa-capsules" style="color: #647a0b;"></i> Propriétés:</strong> {{ $ingredient['huile']->Properties ?? 'Unknown' }}</p>
									<p><strong><i class="fas fa-stethoscope" style="color: #647a0b;"></i> Indications:</strong> {{ $ingredient['huile']->Indications ?? 'Unknown' }}</p>
									<p><strong><i class="fas fa-exclamation-circle" style="color: #647a0b;"></i> Contre Indications:</strong> {{ $ingredient['huile']->ContreIndications ?? 'None' }}</p>
									<p><strong><i class="fas fa-info-circle" style="color: #647a0b;"></i> Note:</strong> {{ $ingredient['huile']->Note ?? 'None' }}</p>
									<p><strong><i class="fas fa-align-left" style="color: #647a0b;"></i> Description:</strong> {{ $ingredient['huile']->Description ?? 'None' }}</p>

									<!-- Redirect Button to HuileHE Page -->
									<a href="{{ route('huilehes.show', $ingredient['huile']->slug) }}" class="btn btn-theme mt-3">Voir plus sur {{ $ingredient['huile']->NomHE }}</a>
								</div>

                            </div>
                        @endforeach
                    </ul>
                @endif

                @if(count($parsed_ingredients_hv) > 0)
                    <h2 class="section-title">Huile Végétale (HV)</h2>
                    <ul>
                        @foreach($parsed_ingredients_hv as $ingredient)
							<li class="mb-3 d-flex align-items-center">
								<div>
									<p class="mb-0">&bull; {{ $ingredient['quantity'] }} {{ $ingredient['huile']->NomHV ?? 'Unknown' }} (<em>{{ $ingredient['huile']->NomLatin ?? 'Unknown' }}</em>)
									
									<button class="accordion-toggle btn btn-sm btn-theme" onclick="toggleAccordion(this)">En savoir plus</button></p>
								</div>
							</li>


                            <div class="custom-accordion">
                                <div class="accordion-content">
                                    <p><strong><i class="fas fa-globe" style="color: #647a0b;"></i> Provenance:</strong> {{ $ingredient['huile']->Provenance ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-seedling" style="color: #647a0b;"></i> Organe Producteur:</strong> {{ $ingredient['huile']->OrganeProducteur ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-vial" style="color: #647a0b;"></i> Substances (Sb):</strong> {{ $ingredient['huile']->Sb ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-capsules" style="color: #647a0b;"></i> Propriétés:</strong> {{ $ingredient['huile']->Properties ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-stethoscope" style="color: #647a0b;"></i> Indications:</strong> {{ $ingredient['huile']->Indications ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-exclamation-circle" style="color: #647a0b;"></i> Contre Indications:</strong> {{ $ingredient['huile']->ContreIndications ?? 'None' }}</p>
                                    <p><strong><i class="fas fa-info-circle" style="color: #647a0b;"></i> Note:</strong> {{ $ingredient['huile']->Note ?? 'None' }}</p>
                                    <p><strong><i class="fas fa-align-left" style="color: #647a0b;"></i> Description:</strong> {{ $ingredient['huile']->Description ?? 'None' }}</p>
                                <!-- Redirect Button to HuileHV Page -->
									<a href="{{ route('huilehvs.show', $ingredient['huile']->slug) }}" class="btn btn-theme mt-3">Voir plus sur {{ $ingredient['huile']->NomHV }}</a>
								</div>
                            </div>
                        @endforeach
                    </ul>
                @endif

                @if(count($parsed_ingredients_tisane) > 0)
                    <h2 class="section-title">Tisane</h2>
                    <ul>
                        @foreach($parsed_ingredients_tisane as $ingredient)
							<li class="mb-3 d-flex align-items-center">
								<div>
									<p class="mb-0">&bull; {{ $ingredient['quantity'] }} {{ $ingredient['tisane']->NomTisane ?? 'Unknown' }} (<em>{{ $ingredient['tisane']->NomLatin ?? 'Unknown' }}</em>)
									
									<button class="accordion-toggle btn btn-sm btn-theme" onclick="toggleAccordion(this)">En savoir plus</button></p>
								</div>
							</li>


                            <div class="custom-accordion">
                                <div class="accordion-content">
                                    <p><strong><i class="fas fa-globe" style="color: #647a0b;"></i> Provenance:</strong> {{ $ingredient['tisane']->Provenance ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-seedling" style="color: #647a0b;"></i> Organe Producteur:</strong> {{ $ingredient['tisane']->OrganeProducteur ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-vial" style="color: #647a0b;"></i> Substances (Sb):</strong> {{ $ingredient['tisane']->Sb ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-capsules" style="color: #647a0b;"></i> Propriétés:</strong> {{ $ingredient['tisane']->Properties ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-stethoscope" style="color: #647a0b;"></i> Indications:</strong> {{ $ingredient['tisane']->Indications ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-exclamation-circle" style="color: #647a0b;"></i> Contre Indications:</strong> {{ $ingredient['tisane']->ContreIndications ?? 'None' }}</p>
                                    <p><strong><i class="fas fa-info-circle" style="color: #647a0b;"></i> Note:</strong> {{ $ingredient['tisane']->Note ?? 'None' }}</p>
                                    <p><strong><i class="fas fa-align-left" style="color: #647a0b;"></i> Description:</strong> {{ $ingredient['tisane']->Description ?? 'None' }}</p>
                                  <!-- Redirect Button to Tisane Page -->
									<a href="{{ route('tisane.show', $ingredient['tisane']->slug) }}" class="btn btn-theme mt-3">Voir plus sur {{ $ingredient['tisane']->NomTisane }}</a>
								
								</div>
                            </div>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Contre Indications Summary -->
            @if(count($all_contre_indications) > 0)
                <div class="details-box">
                    <label class="details-label"><i class="fas fa-exclamation-triangle" style="color: #647a0b;"></i> Contre Indications</label>
                    <ul class="details-list">
                        @foreach($all_contre_indications as $contreIndication)
                            <li>{{ $contreIndication }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Explication section after ingredients and contre indications -->
            <div class="details-box">
                <label class="details-label"><i class="fas fa-align-left" style="color: #647a0b;"></i> Explication</label>
                <p class="details-value">{{ $recette->Explication }}</p>
            </div>            
			
			<!-- Explication section after ingredients and contre indications -->
            <div class="details-box">
                <label class="details-label"><i class="fas fa-align-left" style="color: #647a0b;"></i> Note</label>
                <p class="details-value">{{ $recette->note }}</p>
            </div>

            <a href="{{ route('recettes.index') }}" class="btn btn-primary mt-4">&larr; Retour à la liste</a>

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
	
	.ms-auto {
    margin-left: auto; /* Pushes the button to the far right */
}

.btn-theme {
    background-color: #647a0b; /* Green theme color */
    color: #ffffff; /* White text */
    border: 1px solid #647a0b; /* Matching border color */
    padding: 5px 10px; /* Add padding for better appearance */
    border-radius: 5px; /* Slightly rounded corners */
    font-size: 0.875rem; /* Slightly smaller font for the button */
    transition: background-color 0.3s, color 0.3s; /* Smooth transition on hover */
}

.btn-theme:hover {
    background-color: #854f38; /* Brown hover color */
    border-color: #854f38; /* Match the border to hover color */
    color: #ffffff; /* White text on hover */
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
            margin-bottom: 30px;
            text-align: center;
        }

        .details-box {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
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
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 10px;
        }

        ul {
            list-style-type: none;
            padding-left: 0;
        }

        .custom-accordion {
            margin-top: 10px;
        }

        .accordion-toggle {
            cursor: pointer;
        }

        .accordion-content {
            display: none;
            padding: 10px;
            margin-top: 5px;
            background-color: #f7f7f7;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        }

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

    <!-- Custom JavaScript -->
    <script>
        function toggleAccordion(element) {
            const content = element.closest('li').nextElementSibling.querySelector('.accordion-content');
            content.style.display = content.style.display === "block" ? "none" : "block";
            element.classList.toggle("active");
        }
    </script>
</x-app-layout>
