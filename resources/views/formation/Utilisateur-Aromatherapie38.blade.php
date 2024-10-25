{{-- resources/views/formation/Utilisateur-Aromatherapie38.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
        <!-- Diapositive 38 : Titre -->
        <div class="slide-content">
            <h1 class="slide-title">Application pratique : créer un mélange maison</h1>
            <p><strong>Objectif :</strong> Appliquer les connaissances pour préparer un simple mélange maison.</p>
            <p><strong>Durée :</strong> 15 minutes</p>
        </div>

        <!-- Boutons de navigation -->
        <div class="navigation-buttons">
            <!-- Bouton Précédent -->
            <a href="{{ route('formation.show', ['numero' => $numero - 1]) }}" class="btn-slide">Précédent</a>

            <!-- Lien vers la diapositive suivante -->
            @if($numero < $totalSlides)
                <a href="{{ route('formation.show', ['numero' => $numero + 1]) }}" class="btn-slide">Suivant</a>
            @else
                <span class="btn-slide btn-disabled">Suivant</span>
            @endif
        </div>
    </div>
</x-app-layout>
