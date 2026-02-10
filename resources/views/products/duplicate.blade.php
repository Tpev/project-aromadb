{{-- resources/views/products/duplicate.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Dupliquer la Prestation') }}
        </h2>
    </x-slot>

    <div class="container mt-5" x-data="{ adv:false }">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Dupliquer la Prestation') }}</h1>

            <form action="{{ route('products.storeDuplicate', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                @php
                    $currentMode = old('mode');
                    if ($currentMode === null) {
                        $currentMode = $product->visio ? 'visio'
                            : ($product->adomicile ? 'adomicile'
                                : ($product->en_entreprise ? 'en_entreprise'
                                    : ($product->dans_le_cabinet ? 'dans_le_cabinet' : 'visio')));
                    }
                @endphp

                <!-- Nom de la Prestation -->
                <div class="details-box">
                    <label class="details-label" for="name">{{ __('Nom de la Prestation') }}</label>
                    <input type="text" id="name" name="name" class="form-control"
                           value="{{ old('name', $product->name) }}" required>
                    @error('name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="details-box">
                    <label class="details-label" for="description">{{ __('Description') }}</label>
                    <textarea id="description" name="description" class="form-control" rows="4">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prix -->
                <div class="details-box">
                    <label class="details-label" for="price">{{ __('Prix') }}</label>
                    <input type="number" step="0.01" id="price" name="price" class="form-control"
                           value="{{ old('price', $product->price) }}" required>
                    @error('price')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- TVA -->
                <div class="details-box">
                    <label class="details-label" for="tax_rate">{{ __('TVA (%)') }}</label>
                    <input type="number" step="0.01" id="tax_rate" name="tax_rate" class="form-control"
                           value="{{ old('tax_rate', $product->tax_rate) }}" required>
                    @error('tax_rate')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Durée -->
                <div class="details-box">
                    <label class="details-label" for="duration">{{ __('Durée (minutes)') }}</label>
                    <input type="number" id="duration" name="duration" class="form-control"
                           value="{{ old('duration', $product->duration) }}">
                    @error('duration')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mode -->
                <div class="details-box">
                    <label class="details-label" for="mode">{{ __('Mode') }}</label>
                    <select id="mode" name="mode" class="form-control" required>
                        <option value="visio" {{ $currentMode === 'visio' ? 'selected' : '' }}>{{ __('Visio') }}</option>
                        <option value="adomicile" {{ $currentMode === 'adomicile' ? 'selected' : '' }}>{{ __('À domicile') }}</option>
                        <option value="en_entreprise" {{ $currentMode === 'en_entreprise' ? 'selected' : '' }}>{{ __('En entreprise') }}</option>
                        <option value="dans_le_cabinet" {{ $currentMode === 'dans_le_cabinet' ? 'selected' : '' }}>{{ __('Dans le cabinet') }}</option>
                    </select>
                    @error('mode')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ordre d’affichage -->
                <div class="details-box">
                    <label class="details-label" for="display_order">{{ __('Ordre d’affichage') }}</label>
                    <input type="number" id="display_order" name="display_order" class="form-control"
                           value="{{ old('display_order', $product->display_order) }}">
                    @error('display_order')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                    <small class="text-gray-500">{{ __('Les prestations seront affichées en ordre croissant basé sur ce nombre.') }}</small>
                </div>

                <!-- === Options avancées === -->
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
                                <input type="number" id="max_per_day" name="max_per_day" class="form-control"
                                       value="{{ old('max_per_day', $product->max_per_day) }}">
                                @error('max_per_day')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Réservable en ligne -->
                            <div class="details-box">
                                <label class="details-label" for="can_be_booked_online">{{ __('Réservable en ligne') }}</label>
                                <input type="hidden" name="can_be_booked_online" value="0">
                                <input type="checkbox"
                                       id="can_be_booked_online"
                                       name="can_be_booked_online"
                                       value="1"
                                       {{ old('can_be_booked_online', $product->can_be_booked_online ? 1 : 0) ? 'checked' : '' }}>
                                @error('can_be_booked_online')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Visible sur le Portail -->
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
                                    {{ old('visible_in_portal', $product->visible_in_portal ? 1 : 0) ? 'checked' : '' }}>
                                @error('visible_in_portal')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                                <small class="text-gray-500">
                                    {{ __('Décochez si vous souhaitez cacher cette prestation de votre page publique (mais la garder en interne).') }}
                                </small>
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
                                    {{ old('price_visible_in_portal', $product->price_visible_in_portal ? 1 : 0) ? 'checked' : '' }}>
                                @error('price_visible_in_portal')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                                <small class="text-gray-500">
                                    {{ __('Décochez si vous préférez ne pas afficher le tarif de cette prestation sur votre page publique.') }}
                                </small>
                            </div>

                            <!-- Liens réservation directe -->
                            <div class="details-box">
                                <label class="details-label" for="direct_booking_enabled">{{ __('Liens réservation directe') }}</label>

                                <input type="hidden" name="direct_booking_enabled" value="0">
                                <label style="display:flex;align-items:center;gap:10px;margin:0;">
                                    <input type="checkbox"
                                           id="direct_booking_enabled"
                                           name="direct_booking_enabled"
                                           value="1"
                                           {{ old('direct_booking_enabled', 0) ? 'checked' : '' }}>
                                    <span>{{ __('Créer un lien privé de réservation directe pour la prestation dupliquée') }}</span>
                                </label>

                                <small class="text-gray-500">
                                    {{ __('Si coché, un lien privé (partenaire) sera créé pour la nouvelle prestation dupliquée (et non pour l’originale).') }}
                                </small>
                            </div>

                            <!-- Collect Payment -->
                            <div class="details-box">
                                <label class="details-label" for="collect_payment">{{ __('Collecter le paiement') }}</label>
                                <input type="hidden" name="collect_payment" value="0">
                                <input type="checkbox"
                                       id="collect_payment"
                                       name="collect_payment"
                                       value="1"
                                       {{ old('collect_payment', $product->collect_payment ? 1 : 0) ? 'checked' : '' }}>
                                @error('collect_payment')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Emargement -->
                            <div class="details-box">
                                <label class="details-label" for="requires_emargement">{{ __('Fiche d’émargement requise') }}</label>
                                <input type="hidden" name="requires_emargement" value="0">
                                <input type="checkbox"
                                       id="requires_emargement"
                                       name="requires_emargement"
                                       value="1"
                                       {{ old('requires_emargement', $product->requires_emargement ? 1 : 0) ? 'checked' : '' }}>
                                @error('requires_emargement')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Image Upload -->
                            <div class="details-box">
                                <label class="details-label" for="image">{{ __('Image') }}</label>

                                @if(!empty($product->image))
                                    <div style="margin-bottom:10px;">
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="max-width:200px;border-radius:10px;">
                                    </div>

                                    <div style="margin-bottom:10px;">
                                        <input type="hidden" name="remove_image" value="0">
                                        <label style="display:flex;align-items:center;gap:10px;margin:0;">
                                            <input type="checkbox" name="remove_image" value="1" {{ old('remove_image') ? 'checked' : '' }}>
                                            <span>{{ __('Ne pas dupliquer l’image (dupliquer sans image)') }}</span>
                                        </label>
                                    </div>
                                @endif

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
                                <small class="text-gray-500">{{ __('Si vous n’uploadez rien, la brochure actuelle sera réutilisée (même fichier).') }}</small>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="action-buttons mt-4">
                    <button type="submit" class="btn-primary">{{ __('Dupliquer') }}</button>
                    <a href="{{ route('products.show', $product->id) }}" class="btn-secondary">{{ __('Annuler') }}</a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .container { max-width: 900px; }

        .details-container {
            background: #f9f9f9;
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

        .details-box {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .details-label { font-weight: 700; color: #647a0b; margin-bottom: 6px; display:block; }

        .action-buttons { display:flex; gap:10px; flex-wrap:wrap; justify-content:center; margin-top: 20px; }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary {
            background-color: #ccc;
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .text-red-500 { color: #ef4444; font-size: 0.9rem; }
        .text-gray-500 { color: #6b7280; font-size: 0.85rem; }

        .advanced-wrapper { margin-top: 24px; border-top: 1px dashed #d1d5db; padding-top: 16px; }
        .adv-toggle {
            width: 100%;
            display: flex; align-items: center; justify-content: space-between;
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
