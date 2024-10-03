<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Gestion des Licences des Thérapeutes') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Liste des Thérapeutes et Licences') }}</h1>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Therapist List -->
            <table class="table table-bordered table-hover mt-4">
                <thead>
                    <tr>
                        <th>{{ __('Nom du Thérapeute') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Licence Actuelle') }}</th>
                        <th>{{ __('Expiration de la Licence') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($therapists as $therapist)
                        <tr>
                            <td>{{ $therapist->name }}</td>
                            <td>{{ $therapist->email }}</td>
                            <td>
                                @if($therapist->license && $therapist->license->licenseTier)
                                    {{ $therapist->license->licenseTier->name }}
                                @else
                                    {{ __('Pas de licence attribuée') }}
                                @endif
                            </td>
                            <td>
                                @if($therapist->license && $therapist->license->expiration_date)
                                    {{ \Carbon\Carbon::parse($therapist->license->expiration_date)->format('Y-m-d') }}
                                @else
                                    {{ __('N/A') }}
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.license.assign', $therapist->id) }}" method="POST">
                                    @csrf
                                    <div class="d-flex align-items-center">
                                        <select name="license_tier_name" class="form-control mr-2">
                                            @foreach($availableLicenses as $license)
                                                <option value="{{ $license->name }}" 
                                                    {{ $therapist->license && $therapist->license->licenseTier->name == $license->name ? 'selected' : '' }}>
                                                    {{ $license->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-primary ml-2">{{ __('Attribuer') }}</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Custom Styling -->
    <style>
        .container {
            max-width: 1200px;
        }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .details-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        .table {
            background-color: #fff;
            border-radius: 5px;
            overflow: hidden;
            border-collapse: collapse;
            width: 100%;
        }

        .table th, .table td {
            padding: 15px;
            text-align: left;
        }

        .table th {
            background-color: #647a0b;
            color: #fff;
        }

        .table td {
            background-color: #fff;
        }

        .btn-primary {
            background-color: #647a0b;
            border: none;
            color: #fff;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</x-app-layout>
