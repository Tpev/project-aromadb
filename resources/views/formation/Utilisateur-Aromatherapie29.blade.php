{{-- resources/views/formation/Utilisateur-Aromatherapie29.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
        <!-- Diapositive 29 : L'application cutanée des huiles essentielles -->
        <div class="slide-content">
            <h1 class="slide-title">Application cutanée : Méthode efficace mais nécessite une dilution correcte</h1>
            <h2 class="slide-subtitle">Qu’est-ce que l’application cutanée ?</h2>
            <p>
                L'application cutanée consiste à appliquer les huiles essentielles sur la peau après les avoir diluées dans une huile végétale. Cette méthode est idéale pour les douleurs musculaires, les soins de la peau, ou les massages relaxants.
            </p>

            <h2 class="slide-subtitle">Comment l’utiliser ?</h2>
            <div class="details-box">
                <ul>
                    <li>Diluez 1 à 2 gouttes d'huile essentielle dans une cuillère à café d’huile végétale (comme l’huile d’amande douce ou de jojoba).</li>
                    <li>Appliquez le mélange sur la zone ciblée (tempes, poitrine, nuque, muscles) et massez doucement.</li>
                    <li>Utilisez cette méthode pour un soulagement localisé ou pour un massage relaxant.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Exemples d’huiles à appliquer :</h2>
            <div class="details-box">
                <ul>
                    <li><strong>Lavande :</strong> Sur les tempes pour soulager le stress ou favoriser le sommeil.</li>
                    <li><strong>Menthe poivrée :</strong> Sur les tempes pour soulager les maux de tête, sur les muscles fatigués.</li>
                    <li><strong>Eucalyptus :</strong> Sur la poitrine pour dégager les voies respiratoires.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Précautions spécifiques pour l’application cutanée :</h2>
            <div class="details-box" style="background-color: #ffe4e1; border: 1px solid #e3342f;">
                <ul>
                    <li>Toujours faire un test cutané : Avant d’appliquer sur une large zone, testez le mélange sur l’intérieur du poignet pour vérifier l’absence de réaction allergique.</li>
                    <li>Enfants : Diluez les huiles essentielles à une concentration beaucoup plus faible (0,5% à 1% dans une huile végétale). Évitez les huiles comme la menthe poivrée, le romarin et l’eucalyptus pour les jeunes enfants.</li>
                    <li>Femmes enceintes : Diluez davantage (0,5% à 1%) et évitez certaines huiles comme le romarin, la sauge sclarée, et le basilic.</li>
                    <li>Interactions avec les médicaments : Certaines huiles appliquées sur la peau peuvent interférer avec des traitements médicaux. Consultez un professionnel avant application.</li>
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
