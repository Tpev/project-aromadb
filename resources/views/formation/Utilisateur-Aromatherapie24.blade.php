{{-- resources/views/formation/Utilisateur-Aromatherapie24.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
        <!-- Diapositive 24 : Félicitations -->
        <div class="slide-content" style="text-align: center;">
            <h1 class="slide-title">Félicitations !</h1>

            <p style="font-size: 1.5rem; color: #16a34a; margin-top: 30px;">
                Vous avez terminé le module sur les huiles essentielles de base pour usage quotidien.
            </p>

            <p style="font-size: 1.2rem; color: #333333; margin-top: 20px;">
                Nous espérons que vous avez apprécié ce module et que vous vous sentez prêt à intégrer ces huiles essentielles dans votre vie quotidienne.
            </p>

            <p style="font-size: 1.2rem; color: #333333; margin-top: 20px;">
                N'hésitez pas à continuer avec les modules suivants pour approfondir vos connaissances en aromathérapie.
            </p>

            <!-- Image de félicitations (optionnel) -->
            <img src="/images/congratulations.png" alt="Félicitations" style="width: 50%; margin-top: 30px;">

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
</x-app-layout>
