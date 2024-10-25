{{-- resources/views/formation/Utilisateur-Aromatherapie16.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
        <!-- Diapositive 16 : Huile essentielle d'Eucalyptus -->
        <div class="slide-content">
            <h1 class="slide-title">Eucalyptus : Votre allié pour une respiration libre</h1>
            <p><strong>Nom scientifique :</strong> <em>Eucalyptus globulus</em></p>
            <p><strong>Propriétés principales :</strong> L’eucalyptus est largement utilisé pour ses propriétés décongestionnantes et son effet rafraîchissant sur les voies respiratoires. Il est idéal pour les rhumes, la toux et les allergies saisonnières.</p>

            <h2 class="slide-subtitle">Applications courantes :</h2>
            <div class="details-box">
                <ul>
                    <li><strong>Inhalation à la vapeur :</strong> Ajoutez 3 gouttes d’eucalyptus dans un bol d’eau chaude, couvrez votre tête avec une serviette, et inhalez la vapeur pour dégager les voies respiratoires.</li>
                    <li><strong>Application cutanée :</strong> Diluez 3 gouttes d’eucalyptus dans une huile végétale et appliquez sur la poitrine pour faciliter la respiration pendant un rhume.</li>
                    <li><strong>Diffusion :</strong> Diffusez de l’eucalyptus dans la maison pour purifier l’air et aider à dégager les voies nasales.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Utilisations rapides :</h2>
            <div class="details-box">
                <ul>
                    <li><strong>En cas de nez bouché :</strong> Inhalez l’eucalyptus pour une respiration plus facile.</li>
                    <li><strong>Purification de l'air :</strong> Diffusez-le dans les pièces de vie pour désinfecter l'air pendant la saison des rhumes et grippes.</li>
                </ul>
            </div>
        </div>

        <!-- Boutons de navigation -->
        <div class="navigation-buttons">
            <a href="{{ route('formation.show', ['numero' => $numero - 1]) }}" class="btn-slide">Précédent</a>

            <!-- Lien vers la diapositive suivante (désactivé si c'est la dernière) -->
            @if($numero < $totalSlides)
                <a href="{{ route('formation.show', ['numero' => $numero + 1]) }}" class="btn-slide">Suivant</a>
            @else
                <span class="btn-slide btn-disabled">Suivant</span>
            @endif
        </div>
    </div>
</x-app-layout>
