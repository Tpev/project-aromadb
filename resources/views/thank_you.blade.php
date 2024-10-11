<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Merci !') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="thank-you-container text-center">
            <div class="thank-you-message">
                <h1 class="thank-you-title">{{ __('Merci pour votre réponse !') }}</h1>
                <p class="thank-you-subtitle">{{ __('Votre réponse a été soumise avec succès.') }}</p>
            </div>
            <div class="thank-you-icon">
                <i class="fas fa-check-circle" style="font-size: 50px; color: #28a745;"></i>
            </div>

        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .thank-you-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .thank-you-title {
            font-size: 2rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 15px;
        }

        .thank-you-subtitle {
            font-size: 1.2rem;
            color: #333333;
            margin-bottom: 20px;
        }

        .thank-you-icon {
            margin-bottom: 20px;
        }

        .thank-you-actions .btn {
            padding: 10px 20px;
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #647a0b;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            border: 1px solid #854f38;
            margin-left: 10px;
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: white;
        }
    </style>
</x-app-layout>
