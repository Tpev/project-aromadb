{{-- resources/views/formation/Utilisateur-Aromatherapie26.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
        <!-- Diapositive 26 : Introduction -->
        <div class="slide-content">
            <h1 class="slide-title">Introduction aux méthodes d'application des huiles essentielles</h1>
            <p>
                Les huiles essentielles sont des substances naturelles très concentrées qui peuvent offrir de nombreux bienfaits. Cependant, il est essentiel de savoir les utiliser correctement et en toute sécurité, surtout si vous prenez des médicaments ou suivez un traitement. Cette présentation vous guidera à travers trois méthodes d'application courantes : diffusion, inhalation, et application cutanée, tout en couvrant les précautions spécifiques pour certains groupes à risque, comme les enfants, les femmes enceintes, les animaux domestiques, et les personnes sous traitement médical.
            </p>
        </div>

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
</x-app-layout>
