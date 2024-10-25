{{-- resources/views/formation/Utilisateur-Aromatherapie14.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
        <!-- Diapositive 14 : Huile essentielle de Lavande -->
        <div class="slide-content">
            <h1 class="slide-title">Lavande : La reine de la détente</h1>
            <p><strong>Nom scientifique :</strong> <em>Lavandula angustifolia</em></p>
            <p><strong>Propriétés principales :</strong> La lavande est célèbre pour ses propriétés calmantes et relaxantes. Elle est souvent utilisée pour réduire le stress, calmer l’anxiété, et favoriser un sommeil réparateur.</p>

            <h2 class="slide-subtitle">Applications courantes :</h2>
            <div class="details-box">
                <ul>
                    <li><strong>Diffusion :</strong> Ajoutez 4 à 5 gouttes de lavande dans un diffuseur pour créer une atmosphère apaisante dans une pièce, notamment avant de se coucher.</li>
                    <li><strong>Bain relaxant :</strong> Mélangez 5 gouttes de lavande dans du sel d’Epsom et ajoutez-les à l’eau de votre bain pour un moment de détente.</li>
                    <li><strong>Application cutanée :</strong> Diluez 2 gouttes de lavande dans une cuillère à café d'huile végétale et appliquez sur les tempes ou la nuque pour apaiser les tensions.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Utilisations rapides :</h2>
            <div class="details-box">
                <ul>
                    <li>Diffusez la lavande dans la chambre 30 minutes avant le coucher pour favoriser le sommeil.</li>
                    <li>Appliquez-la sur les poignets ou le cou pour une relaxation immédiate en cas de stress.</li>
                </ul>
            </div>
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
