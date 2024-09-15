<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $recette->NomRecette }}
        </h2>
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
                                <i class="fas fa-heart text-red-500"></i> <span>Remove from Favorites</span>
                            @else
                                <i class="far fa-heart"></i> <span>Add to Favorites</span>
                            @endif
                        </button>
                    </form>
                @else
                    <!-- Redirect to login if the user is not authenticated -->
                    <a href="{{ route('login') }}" class="btn btn-favorite" id="favorite-btn">
                        <i class="far fa-heart"></i> <span>Add to Favorites</span>
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

                console.log('Submitting favorite form...');

                fetch(form.action, {
                    method: form.method,
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => {
                    console.log('Response received:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    if (data.success) {
                        if (data.action === 'added') {
                            console.log('Marked as favorite');
                            favoriteBtn.innerHTML = '<i class="fas fa-heart text-red-500"></i> <span>Remove from Favorites</span>';
                        } else if (data.action === 'removed') {
                            console.log('Removed from favorites');
                            favoriteBtn.innerHTML = '<i class="far fa-heart"></i> <span>Add to Favorites</span>';
                        }
                    } else {
                        console.log('Failed to update favorite status');
                    }
                })
                .catch(error => {
                    console.error('Error occurred:', error);
                });
            });
            </script>
            @endauth
                <div class="col-md-6">
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-syringe"></i> Type Application</label>
                        <p class="details-value">{{ $recette->TypeApplication }}</p>
                    </div>
                </div>
            <!-- Ingredients Section with Accordion for HuileHE, HuileHV, and Tisane -->
            <div class="details-box">
                <label class="details-label"><i class="fas fa-vial"></i> Ingredients</label>

                <!-- Display Ingredients for HuileHE only if there are ingredients -->
                @if(count($parsed_ingredients_he) > 0)
                    <h2 class="section-title">Huile Essentielle (HE)</h2>
                    <ul>
                        @foreach($parsed_ingredients_he as $ingredient)
                            <li class="mb-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="mb-0">&bull; {{ $ingredient['quantity'] }} {{ $ingredient['huile']->NomHE ?? 'Unknown' }} (<em>{{ $ingredient['huile']->NomLatin ?? 'Unknown' }}</em>)</p>
                                </div>
                                <div>
                                    <button class="accordion-toggle btn btn-sm btn-outline-secondary ms-3" onclick="toggleAccordion(this)">En savoir plus</button>
                                </div>
                            </li>

                            <div class="custom-accordion">
                                <div class="accordion-content">
                                    <p><strong><i class="fas fa-globe"></i> Provenance:</strong> {{ $ingredient['huile']->Provenance ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-seedling"></i> Organe Producteur:</strong> {{ $ingredient['huile']->OrganeProducteur ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-vial"></i> Substances (Sb):</strong> {{ $ingredient['huile']->Sb ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-capsules"></i> Properties:</strong> {{ $ingredient['huile']->Properties ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-stethoscope"></i> Indications:</strong> {{ $ingredient['huile']->Indications ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-exclamation-circle"></i> Contre Indications:</strong> {{ $ingredient['huile']->ContreIndications ?? 'None' }}</p>
                                    <p><strong><i class="fas fa-info-circle"></i> Note:</strong> {{ $ingredient['huile']->Note ?? 'None' }}</p>
                                    <p><strong><i class="fas fa-align-left"></i> Description:</strong> {{ $ingredient['huile']->Description ?? 'None' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </ul>
                @endif

                <!-- Display Ingredients for HuileHV only if there are ingredients -->
                @if(count($parsed_ingredients_hv) > 0)
                    <h2 class="section-title">Huile Végétale (HV)</h2>
                    <ul>
                        @foreach($parsed_ingredients_hv as $ingredient)
                            <li class="mb-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="mb-0">&bull; {{ $ingredient['quantity'] }} {{ $ingredient['huile']->NomHV ?? 'Unknown' }} (<em>{{ $ingredient['huile']->NomLatin ?? 'Unknown' }}</em>)</p>
                                </div>
                                <div>
                                    <button class="accordion-toggle btn btn-sm btn-outline-secondary ms-3" onclick="toggleAccordion(this)">En savoir plus</button>
                                </div>
                            </li>

                            <div class="custom-accordion">
                                <div class="accordion-content">
                                    <p><strong><i class="fas fa-globe"></i> Provenance:</strong> {{ $ingredient['huile']->Provenance ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-seedling"></i> Organe Producteur:</strong> {{ $ingredient['huile']->OrganeProducteur ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-vial"></i> Substances (Sb):</strong> {{ $ingredient['huile']->Sb ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-capsules"></i> Properties:</strong> {{ $ingredient['huile']->Properties ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-stethoscope"></i> Indications:</strong> {{ $ingredient['huile']->Indications ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-exclamation-circle"></i> Contre Indications:</strong> {{ $ingredient['huile']->ContreIndications ?? 'None' }}</p>
                                    <p><strong><i class="fas fa-info-circle"></i> Note:</strong> {{ $ingredient['huile']->Note ?? 'None' }}</p>
                                    <p><strong><i class="fas fa-align-left"></i> Description:</strong> {{ $ingredient['huile']->Description ?? 'None' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </ul>
                @endif

                <!-- Display Ingredients for Tisane only if there are ingredients -->
                @if(count($parsed_ingredients_tisane) > 0)
                    <h2 class="section-title">Tisane</h2>
                    <ul>
                        @foreach($parsed_ingredients_tisane as $ingredient)
                            <li class="mb-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="mb-0">&bull; {{ $ingredient['quantity'] }} {{ $ingredient['tisane']->NomTisane ?? 'Unknown' }} (<em>{{ $ingredient['tisane']->NomLatin ?? 'Unknown' }}</em>)</p>
                                </div>
                                <div>
                                    <button class="accordion-toggle btn btn-sm btn-outline-secondary ms-3" onclick="toggleAccordion(this)">En savoir plus</button>
                                </div>
                            </li>

                            <div class="custom-accordion">
                                <div class="accordion-content">
                                    <p><strong><i class="fas fa-globe"></i> Provenance:</strong> {{ $ingredient['tisane']->Provenance ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-seedling"></i> Organe Producteur:</strong> {{ $ingredient['tisane']->OrganeProducteur ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-vial"></i> Substances (Sb):</strong> {{ $ingredient['tisane']->Sb ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-capsules"></i> Properties:</strong> {{ $ingredient['tisane']->Properties ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-stethoscope"></i> Indications:</strong> {{ $ingredient['tisane']->Indications ?? 'Unknown' }}</p>
                                    <p><strong><i class="fas fa-exclamation-circle"></i> Contre Indications:</strong> {{ $ingredient['tisane']->ContreIndications ?? 'None' }}</p>
                                    <p><strong><i class="fas fa-info-circle"></i> Note:</strong> {{ $ingredient['tisane']->Note ?? 'None' }}</p>
                                    <p><strong><i class="fas fa-align-left"></i> Description:</strong> {{ $ingredient['tisane']->Description ?? 'None' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Contre Indications Summary -->
            @if(count($all_contre_indications) > 0)
                <div class="details-box">
                    <label class="details-label"><i class="fas fa-exclamation-triangle"></i> Contre Indications</label>
                    <ul class="details-list">
                        @foreach($all_contre_indications as $contreIndication)
                            <li>{{ $contreIndication }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Explication section after ingredients and contre indications -->
            <div class="details-box">
                <label class="details-label"><i class="fas fa-align-left"></i> Explication</label>
                <p class="details-value">{{ $recette->Explication }}</p>
            </div>

            <a href="{{ route('recettes.index') }}" class="btn btn-outline-secondary mt-4">&larr; Back to List</a>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
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
            background-color: #f7f7f7;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .details-title {
            font-size: 2rem;
            font-weight: bold;
            color: #333333;
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
            color: #16a34a;
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

        .d-flex {
            display: flex;
        }

        .align-items-center {
            align-items: center;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .btn-sm {
            font-size: 0.875rem;
            padding: 5px 10px;
        }

        .ms-3 {
            margin-left: 1rem;
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

        .fa-syringe,
        .fa-align-left,
        .fa-vial,
        .fa-exclamation-triangle,
        .fa-globe,
        .fa-seedling,
        .fa-vial,
        .fa-capsules,
        .fa-stethoscope,
        .fa-exclamation-circle,
        .fa-info-circle,
        .fa-align-left {
            margin-right: 10px;
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
