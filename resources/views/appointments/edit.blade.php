<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Modifier le rendez-vous') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <h1 class="page-title">Modifier le rendez-vous</h1>

        <form action="{{ route('appointments.update', $appointment->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Client Profile Selection -->
            <div class="mb-4">
                <label for="client_profile_id" class="form-label">Client</label>
                <select name="client_profile_id" id="client_profile_id" class="form-control" required>
                    <option value="">Sélectionner un client</option>
                    @foreach($clientProfiles as $clientProfile)
                        <option value="{{ $clientProfile->id }}" {{ old('client_profile_id', $appointment->client_profile_id) == $clientProfile->id ? 'selected' : '' }}>
                            {{ $clientProfile->first_name }} {{ $clientProfile->last_name }}
                        </option>
                    @endforeach
                </select>
                @error('client_profile_id')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <!-- Appointment Date -->
            <div class="mb-4">
                <label for="appointment_date" class="form-label">Date du rendez-vous</label>
                <input type="datetime-local" name="appointment_date" id="appointment_date" class="form-control" value="{{ old('appointment_date', $appointment->appointment_date) }}" required>
                @error('appointment_date')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="mb-4">
                <label for="status" class="form-label">Statut</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="scheduled" {{ old('status', $appointment->status) == 'scheduled' ? 'selected' : '' }}>Prévu</option>
                    <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>Terminé</option>
                    <option value="canceled" {{ old('status', $appointment->status) == 'canceled' ? 'selected' : '' }}>Annulé</option>
                </select>
                @error('status')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label for="notes" class="form-label">Notes</label>
                <textarea name="notes" id="notes" class="form-control">{{ old('notes', $appointment->notes) }}</textarea>
                @error('notes')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary" style="background-color: #647a0b; border-color: #647a0b;">Mettre à jour le rendez-vous</button>
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

        .text-danger {
            color: #e3342f;
            font-size: 0.875rem;
        }
    </style>
</x-app-layout>
