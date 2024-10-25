{{-- resources/views/formation/Utilisateur-Aromatherapie12.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
        <!-- Diapositive 12 : Titre du Module -->
        <div class="slide-content">
            <h1 class="slide-title">Huiles essentielles de base pour usage quotidien</h1>
            <p><strong>Objectif :</strong> Connaître 4 huiles essentielles polyvalentes pour les besoins courants.</p>
            <p><strong>Durée :</strong> 10 minutes</p>
        </div>

        <!-- Boutons de navigation -->
        <div class="navigation-buttons">
            <!-- Bouton Précédent -->
            @if($numero > 1)
                <a href="{{ route('formation.show', ['numero' => $numero - 1]) }}" class="btn-slide">Précédent</a>
            @else
                <span class="btn-slide btn-disabled">Précédent</span>
            @endif

            <!-- Lien vers la diapositive suivante -->
            @if($numero < $totalSlides)
                <a href="{{ route('formation.show', ['numero' => $numero + 1]) }}" class="btn-slide">Suivant</a>
            @else
                <span class="btn-slide btn-disabled">Suivant</span>
            @endif
        </div>
    </div>
</x-app-layout>
