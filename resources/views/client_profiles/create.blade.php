<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer un profil client') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouveau Profil Client') }}</h1>

            <form action="{{ route('client_profiles.store') }}" method="POST">
                @csrf

                <!-- First Name -->
                <div class="details-box">
                    <label class="details-label" for="first_name">{{ __('Prénom') }}</label>
                    <input type="text" id="first_name" name="first_name" class="form-control"
                           value="{{ old('first_name') }}" required>
                    @error('first_name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Last Name -->
                <div class="details-box">
                    <label class="details-label" for="last_name">{{ __('Nom') }}</label>
                    <input type="text" id="last_name" name="last_name" class="form-control"
                           value="{{ old('last_name') }}" required>
                    @error('last_name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="details-box">
                    <label class="details-label" for="email">{{ __('Email') }}</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="{{ old('email') }}">
                    @error('email')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div class="details-box">
                    <label class="details-label" for="phone">{{ __('Téléphone') }}</label>
                    <input type="text" id="phone" name="phone" class="form-control"
                           value="{{ old('phone') }}">
                    @error('phone')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Birthdate -->
                <div class="details-box">
                    <label class="details-label" for="birthdate">{{ __('Date de naissance') }}</label>
                    <input type="date" id="birthdate" name="birthdate" class="form-control"
                           value="{{ old('birthdate') }}">
                    @error('birthdate')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="details-box">
                    <label class="details-label" for="address">{{ __('Adresse') }}</label>
                    <input type="text" id="address" name="address" class="form-control"
                           value="{{ old('address') }}">
                    @error('address')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Check to copy name fields to billing -->
                <div class="details-box">
                    <input type="checkbox" id="use_same_names" name="use_same_names" class="form-checkbox h-5 w-5">
                    <label for="use_same_names" class="ml-2 details-label">
                        {{ __('Utiliser les mêmes noms pour la facturation ?') }}
                    </label>
                    <small class="text-gray-500 block">
                        {{ __('Cochez cette case pour copier automatiquement le prénom et le nom ci-dessus dans les champs de facturation.') }}
                    </small>
                </div>

                <!-- Billing First Name -->
                <div class="details-box">
                    <label class="details-label" for="first_name_billing">{{ __('Prénom (Facturation)') }}</label>
                    <input type="text" id="first_name_billing" name="first_name_billing" class="form-control"
                           value="{{ old('first_name_billing') }}">
                    @error('first_name_billing')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Billing Last Name -->
                <div class="details-box">
                    <label class="details-label" for="last_name_billing">{{ __('Nom (Facturation)') }}</label>
                    <input type="text" id="last_name_billing" name="last_name_billing" class="form-control"
                           value="{{ old('last_name_billing') }}">
                    @error('last_name_billing')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Entreprise cliente (optionnel) --}}
                @if(isset($companies) && $companies->isNotEmpty())
                    <div class="details-box">
                        <label class="details-label" for="company_id">
                            {{ __('Entreprise cliente (optionnel)') }}
                        </label>
                        <select id="company_id" name="company_id" class="form-control">
                            <option value="">{{ __('— Aucune —') }}</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}"
                                    @if(old('company_id', $selectedCompanyId ?? null) == $company->id) selected @endif>
                                    {{ $company->name }}
                                    @if($company->billing_city)
                                        – {{ $company->billing_city }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-gray-500 block mt-1">
                            {{ __('Permet de rattacher ce client à une entreprise pour la facturation B2B.') }}
                        </small>
                        @error('company_id')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <button type="submit" class="btn-primary mt-4">{{ __('Créer le Profil') }}</button>
                <a href="{{ route('client_profiles.index') }}" class="btn-secondary mt-4">
                    {{ __('Retour à la liste') }}
                </a>
            </form>
        </div>
    </div>

    <!-- Optional JavaScript to auto-copy names to billing fields -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const useSameNames = document.getElementById('use_same_names');
            const firstNameInput = document.getElementById('first_name');
            const lastNameInput = document.getElementById('last_name');
            const firstNameBilling = document.getElementById('first_name_billing');
            const lastNameBilling = document.getElementById('last_name_billing');

            useSameNames.addEventListener('change', function() {
                if (this.checked) {
                    // Copy current values
                    firstNameBilling.value = firstNameInput.value;
                    lastNameBilling.value = lastNameInput.value;

                    // Make them read-only so user can’t modify
                    firstNameBilling.setAttribute('readonly', true);
                    lastNameBilling.setAttribute('readonly', true);
                } else {
                    // Remove read-only restriction
                    firstNameBilling.removeAttribute('readonly');
                    lastNameBilling.removeAttribute('readonly');
                    // Clear them if you prefer, or leave as-is so the user can tweak
                    firstNameBilling.value = '';
                    lastNameBilling.value = '';
                }
            });
        });
    </script>

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

        .form-checkbox {
            border-radius: 3px;
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
