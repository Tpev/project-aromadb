<x-app-layout>
    <!-- Importing FontAwesome for icons using Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@section('meta_description')
    Découvrez notre mini-formation gratuite sur l'aromathérapie, conçue pour les utilisateurs non thérapeutes. Apprenez à utiliser les huiles essentielles de manière sûre et efficace en seulement 45 minutes. Flexibilité, quizz interactifs, et certificat numérique offert à la fin du cours !
@endsection

<!-- Big Page Title Section -->
<section class="py-12 bg-white">
    <div class="container mx-auto text-center">
        <!-- Big H1 Title with Custom Large Font Size -->
        <h1 class="font-bold text-center mb-8 animate-fade-in" style="color: #647a0b; font-size: 2rem;">
            Mini-Formation - Introduction à l'aromathérapie pour utilisateurs non thérapeutes
        </h1>
        <!-- Centered and Smaller Image -->
        <img src="{{ asset('images/FormationAromatherapie.webp') }}" alt="AromaMade Logo" class="mx-auto my-6" style="max-width: 300px;">
    </div>
</section>


    <!-- Quick Info Section -->
    <section class="py-8 bg-gray-100">
        <div class="container mx-auto max-w-4xl">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <!-- Entry Level -->
                <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition-shadow duration-300 transform hover:-translate-y-2 animate-slide-in">
                    <i class="fas fa-seedling text-5xl mb-4" style="color: #647a0b;"></i>
                    <h3 class="text-2xl font-bold mb-2" style="color: #647a0b;">Niveau Débutant</h3>
                    <p class="text-lg text-gray-700">Conçu pour les utilisateurs, aucune expérience nécessaire. Découvrez l'aromathérapie facilement.</p>
                </div>
                <!-- Short Duration -->
                <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition-shadow duration-300 transform hover:-translate-y-2 animate-slide-in">
                    <i class="fas fa-clock text-5xl mb-4" style="color: #854f38;"></i>
                    <h3 class="text-2xl font-bold mb-2" style="color: #647a0b;">45 Minutes</h3>
                    <p class="text-lg text-gray-700">Complétez la formation en seulement 45 minutes, idéale pour les utilisateurs ayant un emploi du temps chargé.</p>
                </div>
                <!-- Flexible Sessions -->
                <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition-shadow duration-300 transform hover:-translate-y-2 animate-slide-in">
                    <i class="fas fa-check-circle text-5xl mb-4" style="color: #647a0b;"></i>
                    <h3 class="text-2xl font-bold mb-2" style="color: #647a0b;">Sessions Flexibles</h3>
                    <p class="text-lg text-gray-700">Apprenez en plusieurs sessions de 5 à 10 minutes. Faites des pauses et revenez quand vous le souhaitez.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Introduction Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto">
            <h1 class="text-4xl font-bold text-center mb-6 animate-fade-in" style="color: #647a0b;">Découvrez l’aromathérapie et maîtrisez l’utilisation des huiles essentielles en toute sécurité – Formation Gratuite</h1>
            <p class="text-lg text-center max-w-3xl mx-auto mb-8 text-gray-700">
                Vous souhaitez apprendre à utiliser les huiles essentielles de manière simple, sûre, et efficace dans votre vie quotidienne ? Cette certification gratuite est faite pour vous ! Conçue spécifiquement pour les utilisateurs non professionnels, elle vous apportera une compréhension précieuse et des connaissances pratiques pour intégrer les huiles essentielles à votre routine bien-être.
            </p>
        </div>
    </section>

<!-- Why Take This Certification Section -->
<section class="py-12 bg-gray-100">
    <div class="container mx-auto max-w-3xl text-center">
        <h2 class="text-4xl font-bold mb-8 animate-slide-in text-center" style="color: #647a0b;">Pourquoi suivre cette mini-formation ?</h2>
        <div class="bg-white p-8 rounded-lg shadow-lg">
            <div class="grid grid-cols-1 gap-8 text-left">
                <div class="flex items-start">
                    <i class="fas fa-leaf text-3xl mr-4" style="color: #647a0b;"></i>
                    <p class="text-lg text-gray-700"><strong> Maîtrisez les bases de l’aromathérapie</strong> : Apprenez à utiliser les huiles essentielles pour améliorer votre bien-être physique, émotionnel, et mental.</p>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-shield-alt text-3xl mr-4" style="color: #647a0b;"></i>
                    <p class="text-lg text-gray-700"><strong> Utilisation simple et sécurisée</strong> : Découvrez comment utiliser les huiles en diffusion, inhalation ou application cutanée tout en respectant les consignes de sécurité.</p>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-gift text-3xl mr-4" style="color: #854f38;"></i>
                    <p class="text-lg text-gray-700"><strong> Gratuit et accessible</strong> : Cette certification en ligne est totalement gratuite, accessible à tous et sans aucun prérequis.</p>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-clock text-3xl mr-4" style="color: #854f38;"></i>
                    <p class="text-lg text-gray-700"><strong> Flexible et rapide</strong> : La certification prend environ 45 minutes à compléter, mais vous pouvez avancer à votre rythme.</p>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-tasks text-3xl mr-4" style="color: #647a0b;"></i>
                    <p class="text-lg text-gray-700"><strong> Chapitre par chapitre avec quiz récapitulatifs</strong> : À la fin de chaque section, des quiz vous permettront de valider vos connaissances.</p>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-award text-3xl mr-4" style="color: #647a0b;"></i>
                    <p class="text-lg text-gray-700"><strong> Bonus spécial</strong> : En réussissant le quiz final, vous recevrez un certificat numérique élégant à partager avec vos amis ou sur vos réseaux sociaux !</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- What You Will Learn Section -->
<section class="py-12 bg-white">
    <div class="container mx-auto max-w-3xl text-center">
        <h2 class="text-4xl font-bold mb-8 text-center" style="color: #647a0b;"> Ce que vous apprendrez</h2>
        <div class="bg-gray-100 p-8 rounded-lg shadow-lg">
            <div class="grid grid-cols-1 gap-8 text-left">
                <div class="flex items-start">
                    <i class="fas fa-vial text-3xl mr-4" style="color: #647a0b;"></i>
                    <p class="text-lg text-gray-700"><strong> Les huiles essentielles de base</strong> : Lavande, menthe poivrée, eucalyptus et citron – des huiles faciles à utiliser et indispensables dans votre maison.</p>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-spa text-3xl mr-4" style="color: #854f38;"></i>
                    <p class="text-lg text-gray-700"><strong> Méthodes d’application</strong> : Diffusion, inhalation, application cutanée – apprenez à choisir la méthode la plus adaptée à chaque situation.</p>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-shield-alt text-3xl mr-4" style="color: #647a0b;"></i>
                    <p class="text-lg text-gray-700"><strong> Sécurité avant tout</strong> : Utilisez les huiles essentielles de manière sécurisée, en respectant les consignes pour les enfants, femmes enceintes et animaux domestiques.</p>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-flask text-3xl mr-4" style="color: #854f38;"></i>
                    <p class="text-lg text-gray-700"><strong> Créer vos propres mélanges</strong> : Réalisez des mélanges simples pour répondre à vos besoins personnels : relaxation, concentration, respiration, etc.</p>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Call to Action Section -->
    <section class="py-8 bg-green-100 animate-fade-in">
        <div class="container mx-auto text-center">
            <h2 class="text-3xl font-bold mb-4" style="color: #647a0b;">Lancez-vous dès aujourd'hui – C’est 100% gratuit !</h2>
            <p class="text-lg max-w-3xl mx-auto mb-6 text-gray-700">
                En moins d’une heure, vous gagnerez en confiance et en autonomie pour utiliser les huiles essentielles de manière efficace et sécurisée. Prenez votre temps, progressez à votre rythme, et profitez de cette flexibilité pour adapter l’apprentissage à votre emploi du temps.
            </p>
            <a href="{{ route('register') }}" class="btn-primary animate-pulse">Commencer à apprendre</a>
        </div>
    </section>

    <!-- Custom Styles -->
    <style>
        /* General styling using Vite */
        @import 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css';

        .hero {
            background: linear-gradient(to right, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5))}}');
            background-size: cover;
            background-position: center;
            height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Quick Info Section Styles */
        .quick-info {
            background-color: #f7fafc;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Responsive Logo */
        .logo {
            max-width: 800px;
            width: 100%;
            height: auto;
        }

        @media (max-width: 768px) {
            .logo {
                max-width: 500px;
            }
        }

        /* Button Styles */
        .btn-primary {
            background-color: #647a0b;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.3s;
        }
        .btn-primary:hover {
            background-color: #576a0a;
            transform: scale(1.05);
        }

        /* Animations */
        .animate-fade-in {
            animation: fadeIn 1.5s ease-in-out;
        }

        .animate-slide-in {
            animation: slideIn 1.5s ease-in-out;
        }

        .animate-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes slideIn {
            0% { opacity: 0; transform: translateY(50px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
		
		
		<!-- Custom Styling for Sections -->

    .bg-gray-100 {
        background-color: #f7fafc;
    }

    .bg-white {
        background-color: #ffffff;
    }

    .shadow-lg {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .rounded-lg {
        border-radius: 0.5rem;
    }

    /* Icons */
    i {
        color: #647a0b;
    }

    /* List styling */
    .list-inside {
        padding-left: 0;
    }

    </style>
</x-app-layout>