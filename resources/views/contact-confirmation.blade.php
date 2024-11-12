{{-- resources/views/contact-confirmation.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Confirmation') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4 text-center">
            <h1 class="details-title">{{ __('Merci de nous avoir contactés !') }}</h1>
            <p>{{ __('Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.') }}</p>
            <a href="{{ route('dashboard-pro') }}" class="btn-primary mt-4">
                <i class="fas fa-home mr-2"></i> {{ __('Retour à l\'accueil') }}
            </a>
        </div>
    </div>

    <!-- Styles personnalisés -->
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
        }

        .btn-primary {
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            background-color: #647a0b;
            color: #ffffff;
            border: none;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .mr-2 {
            margin-right: 8px;
        }
    </style>
</x-app-layout>
