<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            <i class="fas fa-check-circle mr-2"></i>{{ __('Réservation Confirmée') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="success-message mx-auto p-4 text-center">
            <h1 class="text-3xl font-bold text-green-600 mb-4">
                <i class="fas fa-thumbs-up mr-2"></i>{{ __('Merci pour votre réservation !') }}
            </h1>
            <p class="text-lg text-gray-700 mb-6">
                {{ __('Votre réservation pour l\'événement :') }} <strong>{{ $event->name }}</strong> {{ __('a été enregistrée avec succès.') }}
            </p>
            <a href="{{ route('therapist.show', $event->user->slug) }}" class="btn-primary">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('Retour au profil du thérapeute') }}
            </a>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 800px;
            animation: fadeIn 1s ease-in;
        }

        .success-message {
            background-color: #f0fff4;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            animation: slideInUp 0.5s ease-in-out;
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s, color 0.3s;
            margin-top: 20px;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</x-app-layout>
