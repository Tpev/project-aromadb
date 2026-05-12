<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #6B4A3A;">
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
            color: #6B4A3A;
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
            background-color: #6B4A3A;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: #5F7048;
        }

        .btn-secondary {
            background-color: transparent;
            color: #5F7048;
            border: 1px solid #5F7048;
            margin-left: 10px;
        }

        .btn-secondary:hover {
            background-color: #5F7048;
            color: white;
        }
    </style>
</x-app-layout>
