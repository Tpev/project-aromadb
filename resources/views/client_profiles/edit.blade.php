<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Modifier le profil du client') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Modifier le Profil Client') }}</h1>

            <form action="{{ route('client_profiles.update', $clientProfile->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- First Name -->
                <div class="details-box">
                    <label class="details-label" for="first_name">{{ __('Prénom') }}</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="{{ old('first_name', $clientProfile->first_name) }}" required>
                    @error('first_name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Last Name -->
                <div class="details-box">
                    <label class="details-label" for="last_name">{{ __('Nom') }}</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="{{ old('last_name', $clientProfile->last_name) }}" required>
                    @error('last_name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="details-box">
                    <label class="details-label" for="email">{{ __('Email') }}</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $clientProfile->email) }}">
                    @error('email')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div class="details-box">
                    <label class="details-label" for="phone">{{ __('Téléphone') }}</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $clientProfile->phone) }}">
                    @error('phone')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Birthdate -->
                <div class="details-box">
                    <label class="details-label" for="birthdate">{{ __('Date de naissance') }}</label>
                    <input type="date" id="birthdate" name="birthdate" class="form-control" value="{{ old('birthdate', $clientProfile->birthdate) }}">
                    @error('birthdate')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="details-box">
                    <label class="details-label" for="address">{{ __('Adresse') }}</label>
                    <input type="text" id="address" name="address" class="form-control" value="{{ old('address', $clientProfile->address) }}">
                    @error('address')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="details-box mt-4 d-flex justify-content-between">
                    <button type="submit" class="btn-primary">{{ __('Mettre à jour le Profil') }}</button>
                    <a href="{{ route('client_profiles.index') }}" class="btn-secondary">{{ __('Retour à la liste') }}</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 15px; /* Ensure some padding on smaller screens */
            text-align: center;
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
            text-align: left; /* Align labels and inputs to the left for better readability */
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
            border: 1px solid #854f38; /* Consistent border color */
            border-radius: 5px;
            box-sizing: border-box; /* Ensure padding doesn't affect width */
        }

        .form-control:focus {
            border-color: #647a0b; /* Highlight border on focus */
            outline: none;
            box-shadow: 0 0 5px rgba(100, 122, 11, 0.5);
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
            transition: background-color 0.3s;
            font-size: 1rem;
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
            transition: background-color 0.3s, color 0.3s;
            font-size: 1rem;
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
        }

        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        /* Responsive Adjustments */
        @media (max-width: 600px) {
            .details-container {
                padding: 20px;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
                margin-bottom: 10px;
            }

            .details-box.mt-4.d-flex.justify-content-between {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</x-app-layout>
