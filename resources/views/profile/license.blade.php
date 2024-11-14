<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
@if(auth()->user()->isTherapist())
    <!-- Section : Licence actuelle -->
    <div class="section">
        <h1 class="details-title">{{ __('Informations de Licence') }}</h1>
        <div class="section-content text-center">
            @if(auth()->user()->license_product && auth()->user()->license_product)
                <p><strong>{{ __('Licence Actuelle :') }}</strong> {{ auth()->user()->license_product }}</p>

            @else
                <p>{{ __('Aucune licence n\'est actuellement attribuée.') }}</p>
			 <a href="{{ route('license-tiers.pricing') }}" class="btn-primary mt-4">{{ __('Débloquer l\'accès illimité') }}</a>
            @endif
        </div>
    </div>
@endif

</div>
    </div>

    <!-- Styles personnalisés -->
    <style>
        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .section {
            margin-bottom: 30px;
        }

        .details-title {
            font-size: 1.75rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        .section-content {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            display: inline-block;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .mt-4 {
            margin-top: 1rem;
        }

        .text-center {
            text-align: center;
        }
    </style>
</x-app-layout>
