{{-- resources/views/formation/Utilisateur-Aromatherapie49.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center" style="color: #854f38;">
            {{ __('Formation en Aromathérapie') }}
        </h2>
    </x-slot>

    <!-- Inclure les styles personnalisés -->
    <link rel="stylesheet" href="{{ asset('css/formation.css') }}">
    <!-- Optionnel : Inclure Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <div class="slide-container">
        <!-- Diapositive 49 : Félicitations et Clôture de la Formation -->
        <div class="slide-content" style="text-align: center;">
            <h1 class="slide-title" style="color: #16a34a;">Félicitations !</h1>

            <p style="font-size: 1.5rem; color: #333333; margin-top: 20px;">
                Vous avez complété avec succès la formation dédiée aux utilisateurs d'aromathérapie.
            </p>

            <p style="font-size: 1.2rem; color: #333333; margin-top: 20px;">
                Prenez le temps de revoir et de consolider vos connaissances. L'aromathérapie est un domaine en constante évolution, toujours propice à l'apprentissage et à la découverte.
            </p>

            <p style="font-size: 1.2rem; color: #333333; margin-top: 20px;">
                Explorez notre site pour trouver des recettes personnalisées, des informations approfondies et bien plus encore pour enrichir votre pratique de l'aromathérapie.
            </p>

            <!-- Image de félicitations (optionnel) -->
            <img src="{{ asset('images/congratulation.webp') }}" alt="Félicitations" style="width: 50%; margin-top: 30px; max-width: 300px;">

            <!-- Boutons de navigation -->
            <div class="navigation-buttons" style="justify-content: center; margin-top: 40px;">
                <!-- Bouton Retour à l'accueil -->


                <!-- Bouton Réclamer le certificat -->
                <a href="{{ route('register') }}" class="btn-slide btn-certificate" style="margin-left: 10px;">
                    <i class="fas fa-award" style="margin-right: 8px;"></i> Réclamer mon certificat gratuit
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
