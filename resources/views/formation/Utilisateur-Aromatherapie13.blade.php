{{-- resources/views/formation/Utilisateur-Aromatherapie13.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
        <!-- Diapositive 13 : Introduction -->
        <div class="slide-content">
            <h1 class="slide-title">Introduction aux huiles essentielles polyvalentes</h1>
            <p>
                Dans cette session, nous allons découvrir 4 huiles essentielles qui sont polyvalentes, faciles à utiliser, et qui offrent des bienfaits pour des situations courantes comme la relaxation, l’énergie, le sommeil, et même le nettoyage. Ces huiles sont idéales pour les personnes qui débutent en aromathérapie et qui souhaitent les intégrer dans leur quotidien.
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
