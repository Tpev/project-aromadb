<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer un Événement') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouvel Événement') }}</h1>

            <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Name -->
                <div class="details-box">
                    <label class="details-label" for="name">{{ __('Nom de l\'Événement') }}</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="details-box">
                    <label class="details-label" for="description">{{ __('Description') }}</label>
                    <textarea id="description" name="description" class="form-control">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Start Date and Time -->
                <div class="details-box">
                    <label class="details-label" for="start_date_time">{{ __('Date et Heure de Début') }}</label>
                    <input type="datetime-local" id="start_date_time" name="start_date_time" class="form-control" value="{{ old('start_date_time') }}" required>
                    @error('start_date_time')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Duration -->
                <div class="details-box">
                    <label class="details-label" for="duration">{{ __('Durée (minutes)') }}</label>
                    <input type="number" id="duration" name="duration" class="form-control" value="{{ old('duration') }}" required>
                    @error('duration')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Booking Required -->
                <div class="details-box">
                    <label class="details-label">{{ __('Réservation Requise') }}</label>
                    <div class="form-check">
                        <input type="radio" id="booking_required_yes" name="booking_required" value="1" {{ old('booking_required') == '1' ? 'checked' : '' }} required>
                        <label for="booking_required_yes">{{ __('Oui') }}</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" id="booking_required_no" name="booking_required" value="0" {{ old('booking_required') == '0' ? 'checked' : '' }}>
                        <label for="booking_required_no">{{ __('Non') }}</label>
                    </div>
                    @error('booking_required')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Limited Spots -->
                <div class="details-box">
                    <label class="details-label">{{ __('Places Limitées') }}</label>
                    <div class="form-check">
                        <input type="radio" id="limited_spot_yes" name="limited_spot" value="1" {{ old('limited_spot') == '1' ? 'checked' : '' }} required>
                        <label for="limited_spot_yes">{{ __('Oui') }}</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" id="limited_spot_no" name="limited_spot" value="0" {{ old('limited_spot', '0') == '0' ? 'checked' : '' }}>
                        <label for="limited_spot_no">{{ __('Non') }}</label>
                    </div>
                    @error('limited_spot')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Number of Spots -->
                <div class="details-box" id="number_of_spot_container">
                    <label class="details-label" for="number_of_spot">{{ __('Nombre de Places') }}</label>
                    <input type="number" id="number_of_spot" name="number_of_spot" class="form-control" value="{{ old('number_of_spot') }}">
                    @error('number_of_spot')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Associated Product -->
                <div class="details-box">
                    <label class="details-label" for="associated_product">{{ __('Produit Associé') }}</label>
                    <select id="associated_product" name="associated_product" class="form-control">
                        <option value="">{{ __('Aucun') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('associated_product') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('associated_product')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image -->
                <div class="details-box">
                    <label class="details-label" for="image">{{ __('Image') }}</label>
                    <input type="file" id="image" name="image" class="form-control">
                    @error('image')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Show on Portal -->
                <div class="details-box">
                    <label class="details-label">{{ __('Afficher sur le Portail') }}</label>
                    <div class="form-check">
                        <input type="radio" id="showOnPortail_yes" name="showOnPortail" value="1" {{ old('showOnPortail') == '1' ? 'checked' : '' }} required>
                        <label for="showOnPortail_yes">{{ __('Oui') }}</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" id="showOnPortail_no" name="showOnPortail" value="0" {{ old('showOnPortail') == '0' ? 'checked' : '' }}>
                        <label for="showOnPortail_no">{{ __('Non') }}</label>
                    </div>
                    @error('showOnPortail')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location -->
                <div class="details-box">
                    <label class="details-label" for="location">{{ __('Lieu') }}</label>
                    <input type="text" id="location" name="location" class="form-control" value="{{ old('location') }}" required>
                    @error('location')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary mt-4">{{ __('Créer l\'Événement') }}</button>
                <a href="{{ route('events.index') }}" class="btn-secondary mt-4">{{ __('Retour à la liste') }}</a>
            </form>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 800px;
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
            margin-bottom: 20px;
            text-align: center;
        }

        .details-box {
            margin-bottom: 15px;
        }

        .details-label {
            font-weight: bold;
            color: #647a0b;
            display: block;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .form-check input {
            margin-right: 10px;
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            padding: 10px 20px;
            border: 1px solid #854f38;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
        }

        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
        }

        /* Hide the number_of_spot_container by default */
        #number_of_spot_container {
            display: none;
        }
    </style>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function toggleNumberOfSpots() {
                var limitedSpot = document.querySelector('input[name="limited_spot"]:checked').value;
                var numberOfSpotContainer = document.getElementById('number_of_spot_container');
                if (limitedSpot == '1') {
                    numberOfSpotContainer.style.display = 'block';
                } else {
                    numberOfSpotContainer.style.display = 'none';
                }
            }

            // Initial call to set the visibility based on the current selection
            toggleNumberOfSpots();

            // Add event listeners to the radio buttons
            var limitedSpotRadios = document.querySelectorAll('input[name="limited_spot"]');
            limitedSpotRadios.forEach(function(radio) {
                radio.addEventListener('change', toggleNumberOfSpots);
            });
        });
    </script>
</x-app-layout>
