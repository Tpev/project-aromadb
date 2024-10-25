{{-- resources/views/formation/Utilisateur-Aromatherapie37.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
        <!-- Diapositive 37 : Félicitations -->
        <div class="slide-content" style="text-align: center;">
            <h1 class="slide-title">Félicitations !</h1>

            <p style="font-size: 1.5rem; color: #16a34a; margin-top: 30px;">
                Vous avez terminé le module sur les méthodes d'application simples et sûres des huiles essentielles.
            </p>

            <p style="font-size: 1.2rem; color: #333333; margin-top: 20px;">
                Nous espérons que cette formation vous a apporté les connaissances nécessaires pour utiliser les huiles essentielles en toute sécurité et efficacement dans votre quotidien.
            </p>

            <!-- Image de félicitations (optionnel) -->
            <img src="{{ asset('images/congratulations.png') }}" alt="Félicitations" style="width: 50%; margin-top: 30px;">

            <!-- Boutons de navigation -->
        <!-- Boutons de navigation -->
        <div class="navigation-buttons">
            <a href="{{ route('formation.show', ['numero' => $numero - 1]) }}" class="btn-slide">Précédent</a>

            @if($numero < $totalSlides)
                <a href="{{ route('formation.show', ['numero' => $numero + 1]) }}" class="btn-slide">Suivant</a>
            @else
                <span class="btn-slide btn-disabled">Suivant</span>
            @endif
        </div>
            </div>
        </div>
    </div>
</x-app-layout>
