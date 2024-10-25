{{-- resources/views/formation/Utilisateur-Aromatherapie27.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">

    <div class="slide-container">
        <!-- Diapositive 27 : La diffusion des huiles essentielles -->
        <div class="slide-content">
            <h1 class="slide-title">Diffusion : Purifier l'air et se relaxer, mais avec précautions</h1>
            <h2 class="slide-subtitle">Qu’est-ce que la diffusion ?</h2>
            <p>
                La diffusion consiste à disperser les molécules d'huiles essentielles dans l’air à l’aide d’un diffuseur. Elle est idéale pour améliorer l’atmosphère d’une pièce, favoriser la relaxation, ou purifier l’air ambiant.
            </p>

            <h2 class="slide-subtitle">Comment l’utiliser ?</h2>
            <div class="details-box">
                <ul>
                    <li>Ajoutez 4 à 8 gouttes d'huile essentielle dans un diffuseur rempli d'eau.</li>
                    <li>Placez le diffuseur dans une pièce ventilée, loin des courants d’air directs.</li>
                    <li>Limitez les sessions de diffusion à 30 minutes, en particulier dans les espaces clos.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Exemples d’huiles à diffuser :</h2>
            <div class="details-box">
                <ul>
                    <li><strong>Lavande :</strong> Pour calmer l'esprit et préparer le sommeil.</li>
                    <li><strong>Citron :</strong> Pour purifier l'air et élever l'humeur.</li>
                    <li><strong>Eucalyptus :</strong> Pour aider à dégager les voies respiratoires en période de rhume.</li>
                </ul>
            </div>

            <h2 class="slide-subtitle">Précautions spécifiques pour la diffusion :</h2>
            <div class="details-box" style="background-color: #ffe4e1; border: 1px solid #e3342f;">
                <ul>
                    <li>Pas de diffusion en présence de bébés ou d’enfants en bas âge.</li>
                    <li>Pas de diffusion avec des animaux dans la pièce, surtout les chats et les chiens, qui peuvent être sensibles à certaines huiles comme l’eucalyptus ou le citron.</li>
                    <li>Femmes enceintes : Certaines huiles peuvent stimuler des contractions, comme la sauge sclarée ou le romarin.</li>
                    <li>Interactions avec les médicaments : Si vous prenez des médicaments, consultez un professionnel de la santé avant de diffuser des huiles.</li>
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
