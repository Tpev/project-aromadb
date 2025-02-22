<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer une Prestation') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouvelle Prestation') }}</h1>

            <!-- Updated form with enctype for file uploads -->
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Nom de la Prestation -->
                <div class="details-box">
                    <label class="details-label" for="name">{{ __('Nom de la Prestation') }}</label>
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

                <!-- Prix -->
                <div class="details-box">
                    <label class="details-label" for="price">{{ __('Prix (€)') }}</label>
                    <input type="number" id="price" name="price" class="form-control" value="{{ old('price') }}" step="0.01" min="0" required>
                    @error('price')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>
				<!-- Collect Payment -->
				<div class="details-box">
					<label class="details-label" for="collect_payment">{{ __('Collecter le Paiement durant la prise de rdv sur votre portail') }}</label>
					<!-- Hidden input to ensure a value is always sent -->
					<input type="hidden" name="collect_payment" value="0">
					<!-- Checkbox input -->
					<input type="checkbox" id="collect_payment" name="collect_payment" value="1" {{ old('collect_payment') ? 'checked' : '' }}>
					@error('collect_payment')
						<p class="text-red-500">{{ $message }}</p>
					@enderror
				</div>

                <!-- Taux de Taxe -->
                <div class="details-box">
                    <label class="details-label" for="tax_rate">{{ __('TVA (%)') }}</label>
                    <input type="number" id="tax_rate" name="tax_rate" class="form-control" value="{{ old('tax_rate', 0) }}" step="0.01" min="0" max="100" required>
                    @error('tax_rate')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Durée -->
                <div class="details-box">
                    <label class="details-label" for="duration">{{ __('Durée (en minutes)') }}</label>
                    <input type="number" id="duration" name="duration" class="form-control" value="{{ old('duration') }}" min="1">
                    @error('duration')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mode de prestation -->
                <div class="details-box">
                    <label class="details-label" for="mode">{{ __('Mode de Prestation') }}</label>
                    <select id="mode" name="mode" class="form-control" required>
                        <option value="visio" {{ old('mode') == 'visio' ? 'selected' : '' }}>{{ __('Visio') }}</option>
                        <option value="adomicile" {{ old('mode') == 'adomicile' ? 'selected' : '' }}>{{ __('À domicile') }}</option>
                        <option value="dans_le_cabinet" {{ old('mode') == 'dans_le_cabinet' ? 'selected' : '' }}>{{ __('Dans le cabinet') }}</option>
                    </select>
                    @error('mode')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Can Be Booked Online -->
                <div class="details-box">
                    <label class="details-label" for="can_be_booked_online">{{ __('Peut être réservé en ligne') }}</label>
                    <!-- Hidden input to ensure a value is always sent -->
                    <input type="hidden" name="can_be_booked_online" value="0">
                    <!-- Checkbox input -->
                    <input type="checkbox" id="can_be_booked_online" name="can_be_booked_online" value="1" {{ old('can_be_booked_online') ? 'checked' : '' }}>
                    @error('can_be_booked_online')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Maximum séances par jour -->
                <div class="details-box">
                    <label class="details-label" for="max_per_day">{{ __('Nombre maximum de séances par jour') }}</label>
                    <input type="number" id="max_per_day" name="max_per_day" class="form-control" value="{{ old('max_per_day') }}" min="1">
                    @error('max_per_day')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image Upload -->
                <div class="details-box">
                    <label class="details-label" for="image">{{ __('Image') }}</label>
                    <input type="file" id="image" name="image" class="form-control">
                    @error('image')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Brochure Upload -->
                <div class="details-box">
                    <label class="details-label" for="brochure">{{ __('Brochure (PDF)') }}</label>
                    <input type="file" id="brochure" name="brochure" class="form-control">
                    @error('brochure')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>
				<!-- Display Order -->
				<div class="details-box">
					<label class="details-label" for="display_order">{{ __('Ordre d\'affichage') }}</label>
					<input type="number" id="display_order" name="display_order" class="form-control" value="{{ old('display_order', 0) }}" min="0">
					@error('display_order')
						<p class="text-red-500">{{ $message }}</p>
					@enderror
					<small class="text-gray-500">{{ __('Les prestations seront affichées en ordre croissant basé sur ce nombre.') }}</small>
				</div>

                <button type="submit" class="btn-primary mt-4">{{ __('Créer la Prestation') }}</button>
                <a href="{{ route('products.index') }}" class="btn-secondary mt-4">{{ __('Annuler') }}</a>
            </form>
        </div>
    </div>

    <!-- Styles personnalisés -->
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
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
        }

        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
        }
    </style>
</x-app-layout>
