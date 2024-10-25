{{-- resources/views/formation/Utilisateur-Aromatherapie6.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Styles personnalisés -->
    <style>
        .slide-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
        }

        .slide-title {
            font-size: 2.5rem;
            color: #854f38;
            margin-bottom: 20px;
            text-align: center;
        }

        .slide-subtitle {
            font-size: 1.8rem;
            color: #16a34a;
            margin-top: 30px;
            margin-bottom: 15px;
        }

        .slide-content p {
            font-size: 1.1rem;
            line-height: 1.8;
            text-align: justify;
            margin-bottom: 15px;
            color: #333333;
        }

        .slide-content ul {
            list-style-type: disc;
            margin-left: 20px;
            color: #333333;
        }

        .details-box {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .details-box strong {
            color: #854f38;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .btn-slide {
            background-color: #647a0b;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .btn-slide:hover {
            background-color: #854f38;
        }

        .btn-disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            color: #666666;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .slide-title {
                font-size: 2rem;
            }

            .slide-subtitle {
                font-size: 1.5rem;
            }
        }
    </style>

    <div class="slide-container">
        <div class="slide-content">
            <!-- Diapositive 6 : Méthodes d'application des huiles essentielles -->
            <h1 class="slide-title">Méthodes d'application des huiles essentielles</h1>

            <h2 class="slide-subtitle">Méthodes d’utilisation courantes des huiles essentielles</h2>

            <div class="details-box">
                <h3 style="color: #16a34a;">Diffusion :</h3>
                <p>
                    L’utilisation d’un diffuseur permet de répandre les huiles essentielles dans l’air ambiant pour purifier, calmer ou revitaliser. La diffusion est idéale pour les espaces de relaxation comme les chambres à coucher ou les salons.
                </p>
                <p><em>Exemple :</em> Diffusez 4 à 5 gouttes de lavande dans votre chambre 30 minutes avant de dormir pour favoriser un sommeil apaisant.</p>
            </div>

            <div class="details-box">
                <h3 style="color: #16a34a;">Inhalation directe :</h3>
                <p>
                    Vous pouvez inhaler les huiles directement à partir d’un mouchoir ou les ajouter à un bol d’eau chaude pour dégager les voies respiratoires ou soulager les maux de tête.
                </p>
                <p><em>Exemple :</em> Inhaler 1 goutte de menthe poivrée sur un mouchoir pour soulager un mal de tête.</p>
            </div>

            <div class="details-box">
                <h3 style="color: #16a34a;">Application cutanée (toujours diluée) :</h3>
                <p>
                    Appliquez des huiles diluées dans une huile de support (comme l’huile de coco ou d’amande douce) pour un massage localisé ou pour des soins de la peau.
                </p>
                <p><em>Exemple :</em> Pour soulager des douleurs musculaires, diluez quelques gouttes d’eucalyptus dans une huile végétale et massez la zone affectée.</p>
            </div>

            <div class="details-box" style="background-color: #fff5f5; border-left: 5px solid #e3342f;">
                <h3 style="color: #e3342f;">Attention !</h3>
                <ul>
                    <li>
                        Ne jamais appliquer une huile essentielle pure sur la peau sans dilution, car cela peut causer des irritations ou des brûlures. Toujours diluer dans une huile de support.
                    </li>
                    <li>
                        Ne pas ingérer les huiles essentielles sans supervision d'un professionnel qualifié.
                    </li>
                </ul>
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
                @if(view()->exists('formation.Utilisateur-Aromatherapie' . ($numero + 1)))
                    <a href="{{ route('formation.show', ['numero' => $numero + 1]) }}" class="btn-slide">Suivant</a>
                @else
                    <span class="btn-slide btn-disabled">Suivant</span>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
