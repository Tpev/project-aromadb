<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $huileHE->NomHE }} Details
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">Huile essentielle {{ $huileHE->NomHE }}</h1>

            <!-- Image and Info in Two Columns -->
            <div class="row align-items-center">
                <div class="col-md-6 text-center">
                    <img src="{{ asset('images/Basilictropical.webp') }}" alt="{{ $huileHE->NomHE }}" class="img-fluid" style="max-width: 400px; height: auto; margin-bottom: 20px;">
                </div>
                <div class="col-md-6">
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-leaf"></i> Latin Name</label>
                        <p class="details-value"><em>{{ $huileHE->NomLatin }}</em></p>
                    </div>
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-globe"></i> Provenance</label>
                        <p class="details-value">
                            @php
                                // Split the Provenance by semicolon and map each to a country code
                                $provenances = explode(';', $huileHE->Provenance);
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
                        <label class="details-label"><i class="fas fa-capsules"></i> Properties</label>
                        @php
                            $properties = explode(';', $huileHE->Properties);
                        @endphp
                        <ul class="details-list">
                            @foreach ($properties as $property)
                                <li>{{ trim($property) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-seedling"></i> Organe Producteur</label>
                        <p class="details-value">{{ $huileHE->OrganeProducteur }}</p>
                    </div>
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-vial"></i> Sb (Substances)</label>
                        @php
                            $substances = explode(';', $huileHE->Sb);
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
                        <label class="details-label"><i class="fas fa-stethoscope"></i> Indications</label>
                        @php
                            $indications = explode(';', $huileHE->Indications);
                        @endphp
                        <ul class="details-list">
                            @foreach ($indications as $indication)
                                <li>{{ trim($indication) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-exclamation-circle"></i> Contre Indications</label>
                        <p class="details-value">{{ $huileHE->ContreIndications ?? 'None' }}</p>
                    </div>
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-info-circle"></i> Note</label>
                        <p class="details-value">{{ $huileHE->Note ?? 'None' }}</p>
                    </div>
                </div>
            </div>

            <div class="details-box mt-4">
                <label class="details-label"><i class="fas fa-align-left"></i> Description</label>
                <p class="details-value">{{ $huileHE->Description ?? 'None' }}</p>
            </div>

            <a href="{{ route('huilehes.index') }}" class="btn btn-primary mt-4">Back to List</a>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
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
            margin-bottom: 30px;
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
    </style>

    <!-- Font Awesome for icons and Flag Icons for flags -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css">
</x-app-layout>
