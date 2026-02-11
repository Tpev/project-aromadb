<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Modifier une Disponibilité Ponctuelle') }}
        </h2>
    </x-slot>

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Timepicker CSS -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">

    <!-- Flatpickr CSS -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Modifier la Disponibilité Ponctuelle') }}</h1>

            <p class="mb-4 text-gray-700">
                {{ __("Modifiez la date, les horaires, le lieu et les prestations concernées pour ce créneau ponctuel.") }}
            </p>

            <form method="POST" action="{{ route('special-availabilities.update', $specialAvailability->id) }}">
                @csrf
                @method('PUT')

                <!-- Lieu (facultatif) -->
                <div class="details-box">
                    <label class="details-label" for="practice_location_id">{{ __('Lieu (cabinet)') }}</label>
                    <select id="practice_location_id" name="practice_location_id" class="form-control select2-single">
                        <option value="">{{ __('— Aucun (Visio / Domicile / Générale) —') }}</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}"
                                {{ (string)old('practice_location_id', $specialAvailability->practice_location_id) === (string)$loc->id ? 'selected' : '' }}>
                                {{ $loc->label }}
                                @if($loc->is_primary) — {{ __('Principal') }} @endif
                                @if($loc->city) — {{ $loc->city }} @endif
                            </option>
                        @endforeach
                    </select>

                    <small class="form-text text-muted">
                        {{ __('Laisser vide pour des créneaux non liés à un cabinet (ex : Visio, Domicile).') }}
                    </small>

                    @error('practice_location_id')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date -->
                <div class="details-box">
                    <label class="details-label" for="date">{{ __('Date') }}</label>
                    <input type="text"
                           id="date"
                           name="date"
                           class="form-control"
                           value="{{ old('date', $specialAvailability->date) }}"
                           autocomplete="off"
                           required>
                    @error('date')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Heure de Début -->
                <div class="details-box">
                    <label class="details-label" for="start_time">{{ __('Heure de Début') }}</label>
                    <input type="text"
                           id="start_time"
                           name="start_time"
                           class="form-control timepicker"
                           value="{{ old('start_time', \Carbon\Carbon::createFromFormat('H:i:s', $specialAvailability->start_time)->format('H:i')) }}"
                           required
                           autocomplete="off">
                    @error('start_time')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Heure de Fin -->
                <div class="details-box">
                    <label class="details-label" for="end_time">{{ __('Heure de Fin') }}</label>
                    <input type="text"
                           id="end_time"
                           name="end_time"
                           class="form-control timepicker"
                           value="{{ old('end_time', \Carbon\Carbon::createFromFormat('H:i:s', $specialAvailability->end_time)->format('H:i')) }}"
                           required
                           autocomplete="off">
                    @error('end_time')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Checkbox: s'applique à tous les produits -->
                <div class="details-box">
                    <div class="form-check">
                        <input type="checkbox"
                               name="applies_to_all"
                               id="applies_to_all"
                               class="form-check-input"
                               value="1"
                               {{ old('applies_to_all', $specialAvailability->applies_to_all) ? 'checked' : '' }}>
                        <label class="form-check-label" for="applies_to_all">
                            {{ __('Appliquer ces disponibilités à tous les produits') }}
                        </label>
                    </div>
                    @error('applies_to_all')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sélecteur multi-produits -->
                @php
                    $oldProducts = old('products', $selectedProducts ?? []);
                    if (!is_array($oldProducts)) { $oldProducts = []; }
                @endphp

                <div class="details-box"
                     id="products_select_group"
                     style="{{ old('applies_to_all', $specialAvailability->applies_to_all) ? 'display: none;' : '' }}">
                    <label class="details-label" for="products">{{ __('Sélectionner les Produits') }}</label>

                    <select name="products[]" id="products" class="form-control select2" multiple>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                {{ in_array($product->id, $oldProducts) ? 'selected' : '' }}>
                                {{ $product->name }} - {{ $product->getConsultationModes() }}
                            </option>
                        @endforeach
                    </select>

                    <small class="form-text text-muted">{{ __('Recherchez et sélectionnez un ou plusieurs produits.') }}</small>

                    @error('products')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="d-flex justify-content-center mt-4">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i> {{ __('Mettre à jour') }}
                    </button>

                    <a href="{{ route('special-availabilities.index') }}" class="btn-secondary ml-3">
                        <i class="fas fa-arrow-left mr-2"></i> {{ __('Retour à la Liste') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Timepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>

    <script>
        $(document).ready(function(){
            // Timepicker
            $('.timepicker').timepicker({
                timeFormat: 'HH:mm',
                interval: 15,
                minTime: '00:00',
                maxTime: '23:45',
                dynamic: false,
                dropdown: true,
                scrollbar: true
            });

            // Select2
            $('.select2').select2({
                placeholder: "{{ __('Sélectionner des produits') }}",
                allowClear: true,
                width: '100%',
                dropdownAutoWidth: true,
            });

            $('.select2-single').select2({
                placeholder: "{{ __('— Aucun (Visio / Domicile / Générale) —') }}",
                allowClear: true,
                width: '100%',
                dropdownAutoWidth: true,
            });

            // Toggle produits si "tous"
            $('#applies_to_all').change(function(){
                if(this.checked){
                    $('#products_select_group').hide();
                } else {
                    $('#products_select_group').show();
                }
            });

            // Etat initial
            if($('#applies_to_all').is(':checked')){
                $('#products_select_group').hide();
            } else {
                $('#products_select_group').show();
            }

            // Flatpickr date unique
            flatpickr("#date", {
                mode: "single",
                dateFormat: "Y-m-d",
                locale: "fr"
            });
        });
    </script>

    <style>
        .container { max-width: 800px; }
        .details-container {
            background-color: #f9f9f9; border-radius: 10px; padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); margin: 0 auto;
        }
        .details-title { font-size: 2rem; font-weight: bold; color: #647a0b; margin-bottom: 20px; text-align: center; }
        .details-box { margin-bottom: 20px; text-align: left; }
        .details-label { font-weight: 600; color: #647a0b; display: block; margin-bottom: 5px; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #854f38; border-radius: 5px; box-sizing: border-box; }
        .form-check-input { margin-right: 10px; }
        .btn-primary, .btn-secondary {
            padding: 10px 20px; font-size: 1rem; border-radius: 5px; text-decoration: none;
            display: inline-flex; align-items: center; cursor: pointer; transition: background-color 0.3s; margin: 5px;
        }
        .btn-primary { background-color: #647a0b; color: #ffffff; border: none; }
        .btn-primary:hover { background-color: #854f38; }
        .btn-secondary { background-color: transparent; color: #854f38; border: 1px solid #854f38; }
        .btn-secondary:hover { background-color: #854f38; color: #ffffff; }
        .text-red-500 { color: #e3342f; font-size: 0.875rem; margin-top: 5px; }
        .d-flex { display: flex; align-items: center; }
        .justify-content-center { justify-content: center; }
        .ml-3 { margin-left: 15px; }
        .mr-2 { margin-right: 8px; }
        .form-check { display: flex; align-items: center; }
    </style>
</x-app-layout>
