<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Informations de l\'Entreprise') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Mettre à Jour les Informations de l\'Entreprise') }}</h1>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('profile.updateCompanyInfo') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Nom de l'Entreprise -->
                <div class="details-box">
                    <label class="details-label" for="company_name">{{ __('Nom de l\'Entreprise') }}</label>
                    <input type="text" id="company_name" name="company_name" class="form-control" value="{{ old('company_name', auth()->user()->company_name) }}">
                    @error('company_name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Adresse de l'Entreprise -->
                <div class="details-box">
                    <label class="details-label" for="company_address">{{ __('Adresse de l\'Entreprise') }}</label>
                    <textarea id="company_address" name="company_address" class="form-control">{{ old('company_address', auth()->user()->company_address) }}</textarea>
                    @error('company_address')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email de l'Entreprise -->
                <div class="details-box">
                    <label class="details-label" for="company_email">{{ __('Email de l\'Entreprise') }}</label>
                    <input type="email" id="company_email" name="company_email" class="form-control" value="{{ old('company_email', auth()->user()->company_email) }}">
                    @error('company_email')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Téléphone de l'Entreprise -->
                <div class="details-box">
                    <label class="details-label" for="company_phone">{{ __('Téléphone de l\'Entreprise') }}</label>
                    <input type="text" id="company_phone" name="company_phone" class="form-control" value="{{ old('company_phone', auth()->user()->company_phone) }}">
                    @error('company_phone')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mentions Légales -->
                <div class="details-box">
                    <label class="details-label" for="legal_mentions">{{ __('Mentions Légales') }}</label>
                    <textarea id="legal_mentions" name="legal_mentions" class="form-control">{{ old('legal_mentions', auth()->user()->legal_mentions) }}</textarea>
                    @error('legal_mentions')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary mt-4">{{ __('Enregistrer les Modifications') }}</button>
                <a href="{{ route('profile.edit') }}" class="btn-secondary mt-4">{{ __('Annuler') }}</a>
            </form>
        </div>
    </div>

    <!-- Styles personnalisés -->
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
            border: 1px solid #854f38;
            border-radius: 5px;
            font-size: 1rem;
            color: #333;
        }

        .form-control:focus {
            border-color: #647a0b;
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
            cursor: pointer;
            display: inline-block;
            margin-right: 10px;
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
            cursor: pointer;
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

        .mt-4 {
            margin-top: 1rem;
        }
    </style>
</x-app-layout>
