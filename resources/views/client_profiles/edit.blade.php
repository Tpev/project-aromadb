<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Modifier le profil du client') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <h1 class="page-title">Modifier le profil du client</h1>

        <form action="{{ route('client_profiles.update', $clientProfile->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="first_name" class="form-label">Prénom</label>
                <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name', $clientProfile->first_name) }}" required>
                @error('first_name')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="last_name" class="form-label">Nom</label>
                <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name', $clientProfile->last_name) }}" required>
                @error('last_name')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $clientProfile->email) }}">
                @error('email')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="phone" class="form-label">Téléphone</label>
                <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $clientProfile->phone) }}">
                @error('phone')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="birthdate" class="form-label">Date de naissance</label>
                <input type="date" name="birthdate" id="birthdate" class="form-control" value="{{ old('birthdate', $clientProfile->birthdate) }}">
                @error('birthdate')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="address" class="form-label">Adresse</label>
                <textarea name="address" id="address" class="form-control">{{ old('address', $clientProfile->address) }}</textarea>
                @error('address')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary" style="background-color: #647a0b; border-color: #647a0b;">Mettre à jour le profil</button>
            </div>
        </form>
    </div>

    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-label {
            font-weight: bold;
            color: #333333;
        }

        .form-control {
            border-color: #854f38;
        }

        .btn-primary {
            background-color: #647a0b;
            border-color: #647a0b;
        }

        .btn-primary:hover {
            background-color: #854f38;
            border-color: #854f38;
        }
    </style>
</x-app-layout>
