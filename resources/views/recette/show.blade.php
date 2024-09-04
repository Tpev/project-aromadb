<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-green-600 leading-tight">
            {{ $recette->NomRecette }} Details
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto">
            <h1 class="details-title">{{ $recette->NomRecette }} Details</h1>

            <div class="details-item">
                <label>Type Application:</label>
                <p>{{ $recette->TypeApplication }}</p>
            </div>

            <div class="details-item">
                <label>Explication:</label>
                <p>{{ $recette->Explication }}</p>
            </div>

            <div class="details-item">
                <label>Ingredients:</label>
                <ul>
                    @php
                        $allContreIndications = [];
                    @endphp
                    @foreach($recette->parsed_ingredients as $index => $ingredient)
                        @php
                            // Add contre indications to the array if they are not null or 'None'
                            if (!empty($ingredient['huileHE']->ContreIndications) && $ingredient['huileHE']->ContreIndications !== 'None') {
                                $allContreIndications = array_merge($allContreIndications, explode(';', $ingredient['huileHE']->ContreIndications));
                            }
                        @endphp
                        <li class="mb-3 d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-0">&bull; {{ $ingredient['quantity'] }} {{ $ingredient['huileHE']->NomHE ?? 'Unknown' }} (<em>{{ $ingredient['huileHE']->NomLatin ?? 'Unknown' }}</em>)</p>
                            </div>
                            <div>
                                <!-- Small button for more details -->
                                <button class="accordion-toggle btn btn-sm btn-secondary ms-3" onclick="toggleAccordion(this)">More Details</button>
                            </div>
                        </li>

                        <!-- Custom Accordion for additional details -->
                        <div class="custom-accordion">
                            <div class="accordion-content">
                                <p><strong>Provenance:</strong> {{ $ingredient['huileHE']->Provenance ?? 'Unknown' }}</p>
                                <p><strong>Organe Producteur:</strong> {{ $ingredient['huileHE']->OrganeProducteur ?? 'Unknown' }}</p>
                                <p><strong>Substances (Sb):</strong> {{ $ingredient['huileHE']->Sb ?? 'Unknown' }}</p>
                                <p><strong>Properties:</strong> {{ $ingredient['huileHE']->Properties ?? 'Unknown' }}</p>
                                <p><strong>Indications:</strong> {{ $ingredient['huileHE']->Indications ?? 'Unknown' }}</p>
                                <p><strong>Contre Indications:</strong> {{ $ingredient['huileHE']->ContreIndications ?? 'None' }}</p>
                                <p><strong>Note:</strong> {{ $ingredient['huileHE']->Note ?? 'None' }}</p>
                                <p><strong>Description:</strong> {{ $ingredient['huileHE']->Description ?? 'None' }}</p>
                            </div>
                        </div>
                    @endforeach
                </ul>
            </div>

            @php
                // Remove duplicate contre indications
                $uniqueContreIndications = array_unique($allContreIndications);
            @endphp

            @if(count($uniqueContreIndications) > 0)
                <div class="details-item">
                    <label>Contre Indications:</label>
                    <ul>
                        @foreach($uniqueContreIndications as $contreIndication)
                            <li>{{ $contreIndication }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <a href="{{ route('recettes.index') }}" class="btn btn-outline-secondary mt-4">
    &larr; Back to List
</a>

        </div>
    </div>

    <!-- Custom Styles -->
    <style>
		.btn-outline-secondary {
			border-color: #16a34a;
			color: #16a34a;
			transition: background-color 0.3s, color 0.3s;
		}
		.btn-outline-secondary:hover {
			background-color: #16a34a;
			color: #ffffff;
		}
        .container {
            max-width: 1200px; /* Ensure consistency with other pages */
        }
        .details-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto; /* Center the details container */
        }
        .details-title {
            font-size: 2rem;
            font-weight: 600;
            color: #333333;
            margin-bottom: 20px;
            text-align: center;
        }
        .details-item {
            margin-bottom: 10px;
        }
        .details-item label {
            font-weight: bold;
            color: #555555;
        }
        .btn-primary {
            background-color: #16a34a;
            border-color: #16a34a;
        }
        .btn-primary:hover {
            background-color: #15803d;
            border-color: #15803d;
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
            background-color: #f9f9f9;
            border-radius: 5px;
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
