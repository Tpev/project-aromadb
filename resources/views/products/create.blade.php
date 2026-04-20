<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer une Prestation') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouvelle Prestation') }}</h1>

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
                        {{ old('price_visible_in_portal', 1) ? 'checked' : '' }}>
                    @error('price_visible_in_portal')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                    <small class="text-gray-500">
                        {{ __('Décochez si vous préférez ne pas afficher le tarif de cette prestation sur votre page publique.') }}
                    </small>
                </div>

                <!-- Collect Payment -->
                <div class="details-box">
                    <label class="details-label" for="collect_payment">{{ __('Collecter le Paiement durant la prise de RDV sur votre portail') }}</label>
                    <input type="hidden" name="collect_payment" value="0">
                    <input type="checkbox" id="collect_payment" name="collect_payment" value="1" {{ old('collect_payment') ? 'checked' : '' }}>
                    @error('collect_payment')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- TVA -->
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
                        <option value="en_entreprise" {{ old('mode') == 'en_entreprise' ? 'selected' : '' }}>{{ __('En entreprise') }}</option>
                        <option value="dans_le_cabinet" {{ old('mode') == 'dans_le_cabinet' ? 'selected' : '' }}>{{ __('Dans le cabinet') }}</option>
                    </select>

                    @error('mode')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
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
                        {{ old('visible_in_portal', 1) ? 'checked' : '' }}>
                    @error('visible_in_portal')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                    <small class="text-gray-500">
                        {{ __('Si coché, cette prestation apparaît sur votre portail (page publique et prise de rendez-vous).') }}
                    </small>
                </div>

                <!-- Peut être réservé en ligne -->
                <div class="details-box">
                    <label class="details-label" for="can_be_booked_online">{{ __('Peut être réservé en ligne') }}</label>
                    <input type="hidden" name="can_be_booked_online" value="0">
                    <input type="checkbox" id="can_be_booked_online" name="can_be_booked_online" value="1" {{ old('can_be_booked_online') ? 'checked' : '' }}>
                    @error('can_be_booked_online')
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

                <!-- Ordre d’affichage -->
                <div class="details-box">
                    <label class="details-label" for="display_order">{{ __('Ordre d\'affichage') }}</label>
                    <input type="number" id="display_order" name="display_order" class="form-control" value="{{ old('display_order', 0) }}" min="0">
                    @error('display_order')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                    <small class="text-gray-500">{{ __('Les prestations seront affichées en ordre croissant basé sur ce nombre.') }}</small>
                </div>

                <!-- === Options avancées === -->
                <div class="extra-settings-wrapper">
                    <h3 class="extra-settings-title">Options avancées</h3>
                    <div class="extra-settings-box">
                        <!-- Nombre maximum de séances par jour -->
                        <div class="details-box">
                            <label class="details-label" for="max_per_day">{{ __('Nombre maximum de séances par jour') }}</label>
                            <input type="number" id="max_per_day" name="max_per_day" class="form-control" value="{{ old('max_per_day') }}" min="1">
                            @error('max_per_day') <p class="text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Fiche d’émargement requise -->
                        <div class="details-box">
                            <label class="details-label" for="requires_emargement">{{ __('Fiche d’émargement requise') }}</label>
                            <input type="hidden" name="requires_emargement" value="0">
                            <input type="checkbox" id="requires_emargement" name="requires_emargement" value="1" {{ old('requires_emargement') ? 'checked' : '' }}>
                            @error('requires_emargement') <p class="text-red-500">{{ $message }}</p> @enderror
                            <small class="text-gray-500">
                                {{ __('Si coché, chaque rendez-vous créé avec cette prestation nécessitera un envoi de feuille d’émargement à signer.') }}
                            </small>
                        </div>

                        <!-- Liens réservation directe -->
                        @php
                            $canConfigureQuestionnaires = auth()->user()?->canUseFeature('questionnaires');
                            $hasQuestionnaires = ($questionnaires ?? collect())->isNotEmpty();
                        @endphp

                        <div class="details-box">
                            <label class="details-label" for="booking_questionnaire_enabled">{{ __('Questionnaire automatique') }}</label>

                            <input type="hidden" name="booking_questionnaire_enabled" value="0">
                            <label style="display:flex;align-items:center;gap:10px;margin:0 0 10px 0;">
                                <input type="checkbox"
                                       id="booking_questionnaire_enabled"
                                       name="booking_questionnaire_enabled"
                                       value="1"
                                       {{ old('booking_questionnaire_enabled') ? 'checked' : '' }}
                                       {{ $canConfigureQuestionnaires ? '' : 'disabled' }}>
                                <span>{{ __('Envoyer automatiquement un questionnaire après la réservation') }}</span>
                            </label>
                            @error('booking_questionnaire_enabled') <p class="text-red-500">{{ $message }}</p> @enderror

                            <label class="details-label" for="booking_questionnaire_id">{{ __('Questionnaire à envoyer') }}</label>
                            <select id="booking_questionnaire_id"
                                    name="booking_questionnaire_id"
                                    class="form-control"
                                    {{ ($canConfigureQuestionnaires && $hasQuestionnaires) ? '' : 'disabled' }}>
                                <option value="">{{ __('Sélectionner un questionnaire') }}</option>
                                @foreach(($questionnaires ?? collect()) as $questionnaire)
                                    <option value="{{ $questionnaire->id }}" {{ (string) old('booking_questionnaire_id') === (string) $questionnaire->id ? 'selected' : '' }}>
                                        {{ $questionnaire->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('booking_questionnaire_id') <p class="text-red-500">{{ $message }}</p> @enderror

                            <label class="details-label" for="booking_questionnaire_frequency" style="margin-top:10px;">{{ __('Quand l’envoyer') }}</label>
                            <select id="booking_questionnaire_frequency"
                                    name="booking_questionnaire_frequency"
                                    class="form-control"
                                    {{ ($canConfigureQuestionnaires && $hasQuestionnaires) ? '' : 'disabled' }}>
                                <option value="first_time_only" {{ old('booking_questionnaire_frequency', 'first_time_only') === 'first_time_only' ? 'selected' : '' }}>
                                    {{ __('Uniquement la première réservation de cette prestation') }}
                                </option>
                                <option value="every_booking" {{ old('booking_questionnaire_frequency') === 'every_booking' ? 'selected' : '' }}>
                                    {{ __('À chaque réservation de cette prestation') }}
                                </option>
                            </select>
                            @error('booking_questionnaire_frequency') <p class="text-red-500">{{ $message }}</p> @enderror

                            @if(!$canConfigureQuestionnaires)
                                <small class="text-gray-500">
                                    {{ __('L’automatisation des questionnaires est disponible avec une formule incluant la fonctionnalité questionnaires.') }}
                                </small>
                            @elseif(!$hasQuestionnaires)
                                <small class="text-gray-500">
                                    {{ __('Créez d’abord un questionnaire pour pouvoir l’envoyer automatiquement après réservation.') }}
                                </small>
                            @else
                                <small class="text-gray-500">
                                    {{ __('Le questionnaire sera envoyé par email au client dès que la réservation sera confirmée.') }}
                                </small>
                            @endif
                        </div>
                        <div class="details-box">
                            <label class="details-label" for="direct_booking_enabled">{{ __('Liens réservation directe') }}</label>

                            <input type="hidden" name="direct_booking_enabled" value="0">
                            <label style="display:flex;align-items:center;gap:10px;margin:0;">
                                <input type="checkbox"
                                       id="direct_booking_enabled"
                                       name="direct_booking_enabled"
                                       value="1"
                                       {{ old('direct_booking_enabled') ? 'checked' : '' }}>
                                <span>{{ __('Activer un lien privé de réservation directe pour cette prestation') }}</span>
                            </label>

                            <small class="text-gray-500">
                                {{ __('Si coché, un lien privé sera généré après la création. Il permettra de réserver uniquement cette prestation.') }}
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <button type="submit" class="btn-primary mt-4">{{ __('Créer la Prestation') }}</button>
                <a href="{{ route('products.index') }}" class="btn-secondary mt-4">{{ __('Annuler') }}</a>
            </form>
        </div>
    </div>

    <!-- Styles -->
    <style>
        .container { max-width: 800px; }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin: 0 auto;
        }

        .details-title {
            font-size: 2rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        .details-box { margin-bottom: 15px; }

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

        .btn-primary:hover { background-color: #854f38; }

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

        .text-red-500 { color: #e3342f; font-size: 0.875rem; }
        .text-gray-500 { color: #6b7280; font-size: 0.85rem; }

        /* Extra settings section */
        .extra-settings-wrapper { margin-top: 24px; border-top: 1px dashed #d1d5db; padding-top: 16px; }
        .extra-settings-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 12px;
        }

        .extra-settings-box {
            background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px;
            padding: 16px; margin-top: 12px;
        }
    </style>
</x-app-layout>
