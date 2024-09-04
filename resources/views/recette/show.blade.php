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
            <h1 class="details-title">Recette {{ $recette->NomRecette }} </h1>

            <!-- Recipe General Information -->
            <div class="row">
                <div class="col-md-6">
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-syringe"></i> Type Application</label>
                        <p class="details-value">{{ $recette->TypeApplication }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-align-left"></i> Explication</label>
                        <p class="details-value">{{ $recette->Explication }}</p>
                    </div>
                </div>
            </div>

            <!-- Ingredients with Accordion -->
            <div class="details-box">
                <label class="details-label"><i class="fas fa-vial"></i> Ingredients</label>
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
                                <button class="accordion-toggle btn btn-sm btn-outline-secondary ms-3" onclick="toggleAccordion(this)">More Details</button>
                            </div>
                        </li>

                        <!-- Custom Accordion for additional details -->
                        <div class="custom-accordion">
                            <div class="accordion-content">
                                <p><strong><i class="fas fa-globe"></i> Provenance:</strong> {{ $ingredient['huileHE']->Provenance ?? 'Unknown' }}</p>
                                <p><strong><i class="fas fa-seedling"></i> Organe Producteur:</strong> {{ $ingredient['huileHE']->OrganeProducteur ?? 'Unknown' }}</p>
                                <p><strong><i class="fas fa-vial"></i> Substances (Sb):</strong> {{ $ingredient['huileHE']->Sb ?? 'Unknown' }}</p>
                                <p><strong><i class="fas fa-capsules"></i> Properties:</strong> {{ $ingredient['huileHE']->Properties ?? 'Unknown' }}</p>
                                <p><strong><i class="fas fa-stethoscope"></i> Indications:</strong> {{ $ingredient['huileHE']->Indications ?? 'Unknown' }}</p>
                                <p><strong><i class="fas fa-exclamation-circle"></i> Contre Indications:</strong> {{ $ingredient['huileHE']->ContreIndications ?? 'None' }}</p>
                                <p><strong><i class="fas fa-info-circle"></i> Note:</strong> {{ $ingredient['huileHE']->Note ?? 'None' }}</p>
                                <p><strong><i class="fas fa-align-left"></i> Description:</strong> {{ $ingredient['huileHE']->Description ?? 'None' }}</p>
                            </div>
                        </div>
                    @endforeach
                </ul>
            </div>

            <!-- Contre Indications Summary -->
            @php
                // Remove duplicate contre indications
                $uniqueContreIndications = array_unique($allContreIndications);
            @endphp

            @if(count($uniqueContreIndications) > 0)
                <div class="details-box">
                    <label class="details-label"><i class="fas fa-exclamation-triangle"></i> Contre Indications</label>
                    <ul class="details-list">
                        @foreach($uniqueContreIndications as $contreIndication)
                            <li>{{ $contreIndication }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <a href="{{ route('recettes.index') }}" class="btn btn-outline-secondary mt-4">&larr; Back to List</a>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
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

        .btn-outline-secondary {
            border-color: #16a34a;
            color: #16a34a;
            transition: background-color 0.3s, color 0.3s;
        }

        .btn-outline-secondary:hover {
            background-color: #16a34a;
            color: #ffffff;
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
