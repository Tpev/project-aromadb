<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Modifier la Prestation') }}
        </h2>
    </x-slot>

    <div class="container mt-5" x-data="{ adv:false }">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Modifier la Prestation') }}</h1>

            <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Nom de la Prestation -->
                <div class="details-box">
                    <label class="details-label" for="name">{{ __('Nom de la Prestation') }}</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    @error('name') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Description -->
                <div class="details-box">
                    <label class="details-label" for="description">{{ __('Description') }}</label>
                    <textarea id="description" name="description" class="form-control">{{ old('description', $product->description) }}</textarea>
                    @error('description') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Prix -->
                <div class="details-box">
                    <label class="details-label" for="price">{{ __('Prix (€)') }}</label>
                    <input type="number" id="price" name="price" class="form-control" value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
                    @error('price') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>
				<!-- Afficher le prix sur le portail -->
				<div class="details-box">
					<label class="details-label" for="price_visible_in_portal">
						{{ __('Afficher le prix sur votre portail') }}
					</label>
					<input type="hidden" name="price_visible_in_portal" value="0">
					<input
						type="checkbox"
						id="price_visible_in_portal"
						name="price_visible_in_portal"
						value="1"
						{{ old('price_visible_in_portal', $product->price_visible_in_portal ?? true) ? 'checked' : '' }}>
					@error('price_visible_in_portal')
						<p class="text-red-500">{{ $message }}</p>
					@enderror
					<small class="text-gray-500">
						{{ __('Décochez si vous préférez ne pas afficher le tarif de cette prestation sur votre page publique.') }}
					</small>
				</div>
                <!-- Collecter le paiement -->
                <div class="details-box">
                    <label class="details-label" for="collect_payment">{{ __('Collecter le Paiement durant la prise de RDV sur votre portail') }}</label>
                    <input type="hidden" name="collect_payment" value="0">
                    <input type="checkbox" id="collect_payment" name="collect_payment" value="1" {{ old('collect_payment', $product->collect_payment) ? 'checked' : '' }}>
                    @error('collect_payment') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- TVA -->
                <div class="details-box">
                    <label class="details-label" for="tax_rate">{{ __('TVA (%)') }}</label>
                    <input type="number" id="tax_rate" name="tax_rate" class="form-control" value="{{ old('tax_rate', $product->tax_rate) }}" step="0.01" min="0" max="100" required>
                    @error('tax_rate') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Durée -->
                <div class="details-box">
                    <label class="details-label" for="duration">{{ __('Durée (en minutes)') }}</label>
                    <input type="number" id="duration" name="duration" class="form-control" value="{{ old('duration', $product->duration) }}" min="1">
                    @error('duration') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

				@php
					$currentMode = old(
						'mode',
						$product->visio ? 'visio'
							: ($product->adomicile ? 'adomicile'
								: (($product->en_entreprise ?? false) ? 'en_entreprise'
									: 'dans_le_cabinet'))
					);
				@endphp

				<div class="details-box">
					<label class="details-label" for="mode">{{ __('Mode de Prestation') }}</label>
					<select id="mode" name="mode" class="form-control" required>
						<option value="visio" {{ $currentMode === 'visio' ? 'selected' : '' }}>{{ __('Visio') }}</option>
						<option value="adomicile" {{ $currentMode === 'adomicile' ? 'selected' : '' }}>{{ __('À domicile') }}</option>
						<option value="en_entreprise" {{ $currentMode === 'en_entreprise' ? 'selected' : '' }}>{{ __('En entreprise') }}</option>
						<option value="dans_le_cabinet" {{ $currentMode === 'dans_le_cabinet' ? 'selected' : '' }}>{{ __('Dans le cabinet') }}</option>
					</select>
					@error('mode') <p class="text-red-500">{{ $message }}</p> @enderror
				</div>

                <!-- Peut être réservé en ligne -->
                <div class="details-box">
                    <label class="details-label" for="can_be_booked_online">{{ __('Peut être réservé en ligne') }}</label>
                    <input type="hidden" name="can_be_booked_online" value="0">
                    <input type="checkbox" id="can_be_booked_online" name="can_be_booked_online" value="1" {{ old('can_be_booked_online', $product->can_be_booked_online) ? 'checked' : '' }}>
                    @error('can_be_booked_online') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Visible sur le Portail Pro/Public -->
                <div class="details-box">
                    <label class="details-label" for="visible_in_portal">
                        {{ __('Visible sur votre portail') }}
                    </label>
                    <input type="hidden" name="visible_in_portal" value="0">
                    <input
                        type="checkbox"
                        id="visible_in_portal"
                        name="visible_in_portal"
                        value="1"
                        {{ old('visible_in_portal', (int)($product->visible_in_portal ?? 1)) ? 'checked' : '' }}>
                    @error('visible_in_portal')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                    <small class="text-gray-500">
                        {{ __('Si coché, cette prestation apparaît sur votre portail (page publique).') }}
                    </small>
                </div>

                <!-- Image -->
                <div class="details-box">
                    <label class="details-label" for="image">{{ __('Image') }}</label>
                    @if($product->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-32 h-32 object-cover">
                        </div>
                    @endif
                    <input type="file" id="image" name="image" class="form-control">
                    @error('image') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Brochure -->
                <div class="details-box">
                    <label class="details-label" for="brochure">{{ __('Brochure (PDF)') }}</label>
                    @if($product->brochure)
                        <div class="mb-2">
                            <a href="{{ asset('storage/' . $product->brochure) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800">{{ __('Voir la brochure existante') }}</a>
                        </div>
                    @endif
                    <input type="file" id="brochure" name="brochure" class="form-control">
                    @error('brochure') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Ordre d’affichage -->
                <div class="details-box">
                    <label class="details-label" for="display_order">{{ __('Ordre d\'affichage') }}</label>
                    <input type="number" id="display_order" name="display_order" class="form-control" value="{{ old('display_order', $product->display_order) }}" min="0">
                    @error('display_order') <p class="text-red-500">{{ $message }}</p> @enderror
                    <small class="text-gray-500">{{ __('Les prestations seront affichées en ordre croissant basé sur ce nombre.') }}</small>
                </div>

                <!-- === Options avancées (seulement 2 champs) === -->
                <div class="advanced-wrapper">
                    <button type="button"
                            class="adv-toggle"
                            @click="adv = !adv"
                            :aria-expanded="adv ? 'true' : 'false'">
                        <span>Options avancées</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="chev" :class="{ 'rotate-180': adv }" viewBox="0 0 20 20" fill="currentColor" width="18" height="18" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="adv" x-transition.origin.top.left style="display:none">
                        <div class="adv-box">
                            <!-- Nombre maximum de séances par jour -->
                            <div class="details-box">
                                <label class="details-label" for="max_per_day">{{ __('Nombre maximum de séances par jour') }}</label>
                                <input type="number" id="max_per_day" name="max_per_day" class="form-control" value="{{ old('max_per_day', $product->max_per_day) }}" min="1">
                                @error('max_per_day') <p class="text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Fiche d’émargement requise -->
                            <div class="details-box">
                                <label class="details-label" for="requires_emargement">{{ __('Fiche d’émargement requise') }}</label>
                                <input type="hidden" name="requires_emargement" value="0">
                                <input type="checkbox" id="requires_emargement" name="requires_emargement" value="1" {{ old('requires_emargement', $product->requires_emargement) ? 'checked' : '' }}>
                                @error('requires_emargement') <p class="text-red-500">{{ $message }}</p> @enderror
                                <small class="text-gray-500">
                                    {{ __('Si coché, chaque rendez-vous créé avec cette prestation nécessitera un envoi de feuille d’émargement à signer.') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <button type="submit" class="btn-primary mt-4">{{ __('Mettre à Jour la Prestation') }}</button>
                <a href="{{ route('products.show', $product->id) }}" class="btn-secondary mt-4">{{ __('Annuler') }}</a>
            </form>
        </div>
    </div>

    <!-- Styles personnalisés -->
    <style>
        .container { max-width: 800px; }

        .details-container {
            background-color: #f9f9f9; border-radius: 10px; padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin: 0 auto;
        }

        .details-title {
            font-size: 2rem; font-weight: bold; color: #647a0b;
            margin-bottom: 20px; text-align: center;
        }

        .details-box { margin-bottom: 15px; }
        .details-label { font-weight: bold; color: #647a0b; display: block; margin-bottom: 5px; }

        .form-control {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;
        }

        .btn-primary {
            background-color: #647a0b; color: #fff; padding: 10px 20px; border: none; border-radius: 5px;
            text-decoration: none; display: inline-block; cursor: pointer;
        }
        .btn-primary:hover { background-color: #854f38; }

        .btn-secondary {
            background-color: transparent; color: #854f38; padding: 10px 20px; border: 1px solid #854f38;
            border-radius: 5px; text-decoration: none; display: inline-block;
        }
        .btn-secondary:hover { background-color: #854f38; color: #fff; }

        .text-red-500 { color: #e3342f; font-size: 0.875rem; }
        .text-gray-500 { color: #6b7280; font-size: 0.85rem; }

        .advanced-wrapper { margin-top: 24px; border-top: 1px dashed #d1d5db; padding-top: 16px; }
        .adv-toggle {
            width: 100%; display: flex; align-items: center; justify-content: space-between;
            padding: 12px 14px; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
            color: #374151; font-weight: 600; cursor: pointer;
        }
        .adv-toggle:hover { background: #f8fafc; }
        .chev { transition: transform .2s ease; }
        .rotate-180 { transform: rotate(180deg); }

        .adv-box {
            background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px;
            padding: 16px; margin-top: 12px;
        }
    </style>
</x-app-layout>
