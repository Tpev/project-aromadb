{{-- resources/views/formation/Utilisateur-Aromatherapie30.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
        <!-- Diapositive 30 : Sécurité générale pour l’utilisation des huiles essentielles -->
        <div class="slide-content">
            <h1 class="slide-title">Sécurité des huiles essentielles pour tous</h1>
            <h2 class="slide-subtitle">Enfants :</h2>
            <div class="details-box">
                <ul>
                    <li>Utilisez des huiles douces (camomille, lavande) et en faible concentration.</li>
                    <li>Évitez la menthe poivrée, l'eucalyptus ou le romarin chez les enfants de moins de 6 ans.</li>
                    <li>Diffusion modérée et jamais dans la même pièce que l’enfant pendant longtemps.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Femmes enceintes :</h2>
            <div class="details-box">
                <ul>
                    <li>Certaines huiles essentielles peuvent induire des contractions ou perturber les hormones. Évitez les huiles comme la sauge sclarée, la menthe poivrée, et le romarin.</li>
                    <li>Utilisez des huiles sûres comme la lavande, la camomille, et l'encens avec modération.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Animaux domestiques :</h2>
            <div class="details-box">
                <ul>
                    <li>Les huiles essentielles peuvent être toxiques pour les animaux, surtout les chats et les chiens. Évitez les huiles comme l'arbre à thé, l'eucalyptus, et les agrumes en diffusion ou en application cutanée.</li>
                    <li>Diffusez dans une pièce bien ventilée et assurez-vous que l'animal peut quitter la pièce si nécessaire.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Interactions avec les médicaments :</h2>
            <div class="details-box" style="background-color: #ffe4e1; border: 1px solid #e3342f;">
                <ul>
                    <li>Si vous prenez des antidépresseurs, des médicaments pour le cœur ou des anticoagulants, certaines huiles essentielles peuvent interagir avec ces médicaments.</li>
                    <li>Toujours consulter un professionnel de la santé avant utilisation si vous êtes sous traitement.</li>
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
