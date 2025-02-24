<x-app-layout>
    <!-- Importing necessary CSS and JS via Vite -->

    @push('styles')
        <!-- AOS Animation Library -->
        <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
        <!-- Font Awesome for icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
        <!-- Custom Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto&display=swap" rel="stylesheet">
    @endpush

    @section('meta_description')
        Découvrez AromaMade PRO, l'application ultime pour simplifier la vie des thérapeutes du bien-être. Gérez vos rendez-vous, vos clients, et développez votre pratique avec des outils puissants et faciles à utiliser.
    @endsection

    <!-- Hero Section with Background Video -->
    <section class="hero relative">
    <div class="hero-bg absolute w-full h-full bg-center bg-cover" style="background-image: url('{{ asset('images/hero.webp') }}');">
        <div class="overlay absolute inset-0 bg-gradient-to-b from-black via-transparent to-black opacity-60"></div>
    </div>
        <div class="container mx-auto text-center relative z-10 py-24">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 text-white animate-fade-in">Transformez votre pratique avec AromaMade PRO</h1>
            <p class="text-xl md:text-2xl mb-8 text-white">L'outil tout-en-un pour les thérapeutes du bien-être</p>
            <a href="{{ route('register-pro') }}" class="btn-primary animate-pulse">Commencer votre essai gratuit</a>
        </div>
        <div class="overlay absolute inset-0 bg-black opacity-50"></div>
    </section>

    <!-- Introduction Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto text-center px-4">
            <h2 class="text-4xl font-bold mb-6 text-primary">Pourquoi choisir AromaMade PRO ?</h2>
            <p class="text-lg text-gray-700 max-w-3xl mx-auto">
                Notre application a été spécialement conçue pour simplifier la vie de tous les thérapeutes du bien-être, quels que soient vos domaines d’expertise. Offrez-vous un outil puissant pour gérer facilement vos rendez-vous, vos clients et bien plus encore.
            </p>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto text-center px-4">
            <h2 class="text-3xl font-bold mb-10 text-primary">Des fonctionnalités qui révolutionnent votre pratique</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- First 6 Feature Cards -->
                <!-- Feature Card 1 -->
                <div class="feature-card group" data-aos="fade-up">
                    <div class="p-6 bg-white rounded-lg shadow-lg transform transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                        <i class="fas fa-calendar-alt text-5xl mb-4 text-primary"></i>
                        <h3 class="text-2xl font-bold mb-2">Prise de rendez-vous</h3>
                        <p class="text-gray-700">Gagnez du temps en créant vos rendez-vous depuis votre espace, ou laissez vos clients réserver directement via votre Portail Pro. Vous restez maître de votre planning tout en offrant une flexibilité maximale à vos patients.</p>
                    </div>
                </div>
                <!-- Feature Card 2 -->
                <div class="feature-card group" data-aos="fade-up" data-aos-delay="100">
                    <div class="p-6 bg-white rounded-lg shadow-lg transform transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                        <i class="fas fa-folder-open text-5xl mb-4 text-secondary"></i>
                        <h3 class="text-2xl font-bold mb-2">Gestion des dossiers clients</h3>
                        <p class="text-gray-700">Centralisez toutes les informations essentielles dans un dossier sécurisé. Plus besoin de jongler entre différents outils, toutes les données de vos patients sont accessibles en un clic.</p>
                    </div>
                </div>
                <!-- Feature Card 3 -->
                <div class="feature-card group" data-aos="fade-up" data-aos-delay="200">
                    <div class="p-6 bg-white rounded-lg shadow-lg transform transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                        <i class="fas fa-globe text-5xl mb-4 text-primary"></i>
                        <h3 class="text-2xl font-bold mb-2">Portail Pro</h3>
                        <p class="text-gray-700">Une véritable vitrine en ligne pour attirer de nouveaux clients ! Partagez facilement votre lien sur vos réseaux sociaux et permettez à vos patients de consulter vos services et de prendre rendez-vous sans effort.</p>
                    </div>
                </div>
                <!-- Feature Card 4 -->
                <div class="feature-card group" data-aos="fade-up" data-aos-delay="300">
                    <div class="p-6 bg-white rounded-lg shadow-lg transform transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                        <i class="fas fa-file-invoice-dollar text-5xl mb-4 text-secondary"></i>
                        <h3 class="text-2xl font-bold mb-2">Facturation simplifiée</h3>
                        <p class="text-gray-700">Automatisez la génération et l’envoi de vos factures, et suivez facilement les paiements. Vous allez adorer ne plus vous soucier de l’administratif !</p>
                    </div>
                </div>
                <!-- Feature Card 5 -->
                <div class="feature-card group" data-aos="fade-up" data-aos-delay="400">
                    <div class="p-6 bg-white rounded-lg shadow-lg transform transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                        <i class="fas fa-question-circle text-5xl mb-4 text-primary"></i>
                        <h3 class="text-2xl font-bold mb-2">Questionnaires</h3>
                        <p class="text-gray-700">Créez des questionnaires personnalisés à envoyer avant ou pendant les séances, pour mieux comprendre vos patients et personnaliser vos soins.</p>
                    </div>
                </div>
                <!-- Feature Card 6 -->
                <div class="feature-card group" data-aos="fade-up" data-aos-delay="500">
                    <div class="p-6 bg-white rounded-lg shadow-lg transform transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                        <i class="fas fa-bullseye text-5xl mb-4 text-secondary"></i>
                        <h3 class="text-2xl font-bold mb-2">Suivi des objectifs thérapeutiques</h3>
                        <p class="text-gray-700">Suivez les progrès de vos patients en définissant des objectifs personnalisés pour chaque séance.</p>
                    </div>
                </div>
            </div>

            <!-- Hidden Additional Features -->
            <div id="additional-features" class="feature-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-8">
                    <!-- Feature Cards 7 to 14 -->
                    <!-- Feature Card 7 -->
                    <div class="feature-card group" data-aos="fade-up" data-aos-delay="600">
                        <div class="p-6 bg-white rounded-lg shadow-lg transform transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                            <i class="fas fa-credit-card text-5xl mb-4 text-primary"></i>
                            <h3 class="text-2xl font-bold mb-2">Options de paiement en ligne</h3>
                            <p class="text-gray-700">Permettez à vos clients de régler leurs séances directement en ligne pour une gestion simplifiée des paiements.</p>
                        </div>
                    </div>
                    <!-- Feature Card 8 -->
                    <div class="feature-card group" data-aos="fade-up" data-aos-delay="700">
                        <div class="p-6 bg-white rounded-lg shadow-lg transform transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                            <i class="fas fa-share-alt text-5xl mb-4 text-secondary"></i>
                            <h3 class="text-2xl font-bold mb-2">Intégration réseaux sociaux</h3>
                            <p class="text-gray-700">Publiez automatiquement vos actualités et événements sur tous vos réseaux sociaux, directement depuis AromaMade.</p>
                        </div>
                    </div>
                    <!-- Feature Card 9 -->
                    <div class="feature-card group" data-aos="fade-up" data-aos-delay="800">
                        <div class="p-6 bg-white rounded-lg shadow-lg transform transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                            <i class="fas fa-calendar-plus text-5xl mb-4 text-primary"></i>
                            <h3 class="text-2xl font-bold mb-2">Création d'événements</h3>
                            <p class="text-gray-700">Organisez des ateliers, séminaires ou événements avec des limites de participants, et proposez la réservation en ligne.</p>
                        </div>
                    </div>
                    <!-- Feature Card 10 -->
                    <div class="feature-card group" data-aos="fade-up" data-aos-delay="900">
                        <div class="p-6 bg-white rounded-lg shadow-lg transform transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                            <i class="fas fa-book text-5xl mb-4 text-secondary"></i>
                            <h3 class="text-2xl font-bold mb-2">Bibliothèque de conseils</h3>
                            <p class="text-gray-700">Créez et envoyez à vos clients des recommandations régulières et personnalisées pour les accompagner dans leur suivi.</p>
                        </div>
                    </div>
                    <!-- Feature Card 11 -->
                    <div class="feature-card group" data-aos="fade-up" data-aos-delay="1000">
                        <div class="p-6 bg-white rounded-lg shadow-lg transform transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                            <i class="fas fa-file-upload text-5xl mb-4 text-primary"></i>
                            <h3 class="text-2xl font-bold mb-2">Gestion et stockage de documents</h3>
                            <p class="text-gray-700">Stockez et gérez facilement tous vos documents professionnels dans un espace sécurisé.</p>
                        </div>
                    </div>
                    <!-- Feature Card 12 -->
                    <div class="feature-card group" data-aos="fade-up" data-aos-delay="1100">
                        <div class="p-6 bg-white rounded-lg shadow-lg transform transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                            <i class="fas fa-star text-5xl mb-4 text-secondary"></i>
                            <h3 class="text-2xl font-bold mb-2">Avis clients</h3>
                            <p class="text-gray-700">Affichez les retours et avis de vos clients directement sur votre profil pour renforcer votre crédibilité et attirer de nouveaux patients.</p>
                        </div>
                    </div>
                    <!-- Feature Card 13 -->
                    <div class="feature-card group" data-aos="fade-up" data-aos-delay="1200">
                        <div class="p-6 bg-white rounded-lg shadow-lg transform transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                            <i class="fas fa-sync-alt text-5xl mb-4 text-primary"></i>
                            <h3 class="text-2xl font-bold mb-2">Synchronisation des calendriers</h3>
                            <p class="text-gray-700">Synchronisez vos rendez-vous avec les calendriers Google, Apple, Microsoft pour une compatibilité totale avec Android, iPhone, etc.</p>
                        </div>
                    </div>
                    <!-- Feature Card 14 -->
                    <div class="feature-card group" data-aos="fade-up" data-aos-delay="1300">
                        <div class="p-6 bg-white rounded-lg shadow-lg transform transition duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                            <i class="fas fa-video text-5xl mb-4 text-secondary"></i>
                            <h3 class="text-2xl font-bold mb-2">Visio-conférence intégrée</h3>
                            <p class="text-gray-700">Offrez des séances à distance grâce à la visio-conférence intégrée directement dans AromaMade, sans besoin d’outils tiers.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call to Action within the section -->
            <div class="mt-12">
                <button id="show-more-features" class="btn-secondary">Voir toutes les fonctionnalités</button>
            </div>
        </div>
    </section>

    <!-- Wave Separator -->
    <div class="wave-container">
        <svg viewBox="0 0 1440 320">
            <path fill="#ffffff" fill-opacity="1" d="M0,224L1440,96L1440,320L0,320Z"></path>
        </svg>
    </div>
    <!-- Statistics Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto text-center px-4">
            <h2 class="text-3xl font-bold mb-8 text-primary">Notre impact en chiffres</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-5xl font-bold text-green-500 counter" data-count="500">1000+</h3>
                    <p class="text-xl mt-2">Utilisateurs satisfaits</p>
                </div>
                <div>
                    <h3 class="text-5xl font-bold text-green-500 counter" data-count="10000">100%</h3>
                    <p class="text-xl mt-2">Rendez-vous gérés</p>
                </div>
                <div>
                    <h3 class="text-5xl font-bold text-green-500 counter" data-count="99">99%</h3>
                    <p class="text-xl mt-2">Taux de satisfaction</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto text-center px-4">
            <h2 class="text-3xl font-bold mb-8 text-primary">Ils nous font confiance</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Testimonial 1 -->
                <div class="testimonial-card" data-aos="fade-up">
                    <p class="text-lg italic">"AromaMade PRO a véritablement simplifié la gestion quotidienne de mon cabinet. Je me sens plus organisée et sereine dans mon travail."</p>
                    <h4 class="mt-4 font-bold">— Camille, Naturopathe</h4>
                </div>
                <!-- Testimonial 2 -->
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                    <p class="text-lg italic">"Depuis que j'utilise AromaMade PRO, j'ai constaté une augmentation notable de ma clientèle. C'est devenu un outil indispensable pour mon activité."</p>
                    <h4 class="mt-4 font-bold">— Pierre, Hypnothérapeute</h4>
                </div>
                <!-- Add more testimonials if needed -->
            </div>
        </div>
    </section>

<!-- Pricing Section -->
<section class="py-12 bg-white">
    <div class="container mx-auto text-center px-4">
        <h2 class="text-3xl font-bold mb-8 text-primary">Des tarifs adaptés à vos besoins</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Pricing Plan 1: Référencement gratuit dans l'annuaire -->
            <div class="pricing-card flex flex-col h-full" data-aos="fade-up">
                <h3 class="text-2xl font-bold mb-4">Référencement gratuit</h3>
                <p class="text-4xl font-bold mb-6">0€</p>
                <ul class="text-left mb-6">
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Listing basic de votre profil
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Visibilité auprès de milliers de clients
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Essai de 14 jours de la version Pro
                    </li>
                </ul>
                <p class="text-gray-700 mb-6">
                    Profitez d'un référencement gratuit dans notre annuaire de praticiens pour augmenter votre visibilité et attirer de nouveaux clients. Votre profil sera présenté de manière professionnelle et optimisé pour un meilleur positionnement dans notre moteur de recherche interne.
                </p>
                <div class="mt-auto">
                    <a href="{{ route('register-pro') }}" class="btn-primary">
                        Inscrivez-vous gratuitement
                    </a>
                </div>
            </div>

            <!-- Pricing Plan 2: Starter -->
            <div class="pricing-card flex flex-col h-full" data-aos="fade-up" data-aos-delay="100">
                <h3 class="text-2xl font-bold mb-4">Starter</h3>
                <p class="text-4xl font-bold mb-6">
                    9,90€<span class="text-xl font-medium">/mois</span>
                </p>
                <ul class="text-left mb-6">
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>50 dossiers clients
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Prise de rendez-vous
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Gestion des dossiers clients
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Portail Pro
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Facturation simplifiée
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Questionnaires
                    </li>
                </ul>
                <p class="text-gray-700 mb-6">
                    Le plan Starter est idéal pour les praticiens en début de carrière qui souhaitent disposer d’un ensemble complet de fonctionnalités essentielles. Gérez efficacement vos rendez-vous et dossiers clients tout en bénéficiant d’un portail professionnel pour attirer de nouveaux clients.
                </p>
                <div class="mt-auto">
                    <a href="{{ route('register-pro') }}" class="btn-primary">
                        Choisir le plan
                    </a>
                </div>
            </div>

            <!-- Pricing Plan 3: Pro -->
            <div class="pricing-card flex flex-col h-full" data-aos="fade-up" data-aos-delay="200">
                <h3 class="text-2xl font-bold mb-4">Pro</h3>
                <p class="text-4xl font-bold mb-6">
                    29,90€<span class="text-xl font-medium">/mois</span>
                </p>
                <ul class="text-left mb-6">
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Dossiers clients illimités
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Toutes les fonctionnalités du plan Starter
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Suivi des objectifs thérapeutiques
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Options de paiement en ligne
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Intégration réseaux sociaux
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Création d'événements
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Bibliothèque de conseils
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Gestion et stockage de documents
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Avis clients
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Synchronisation des calendriers
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-green-500 mr-2"></i>Visio-conférence intégrée
                    </li>
                </ul>
                <p class="text-gray-700 mb-6">
                    Le plan Pro est conçu pour les praticiens expérimentés qui recherchent une solution complète et avancée pour optimiser leur pratique. Bénéficiez d'un nombre illimité de dossiers clients et d'outils performants pour gérer votre activité au quotidien.
                </p>
                <div class="mt-auto">
                    <a href="{{ route('register-pro') }}" class="btn-primary">
                        Choisir le plan
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>



    <!-- Wave Separator -->
    <div class="wave-container">
        <svg viewBox="0 0 1440 320">
            <path fill="#f7fafc" fill-opacity="1" d="M0,224L1440,96L1440,320L0,320Z"></path>
        </svg>
    </div>
<!-- Security and Trust Section -->
<section class="py-12 bg-white">
    <div class="container mx-auto text-center px-4">
        <h2 class="text-3xl font-bold mb-8 text-primary">Sécurité et Confiance</h2>
        <p class="text-lg text-gray-700 max-w-3xl mx-auto mb-8">
            Chez AromaMade PRO, la sécurité de vos données est notre priorité absolue. Nous sommes fiers d'héberger nos services en France, en conformité avec les normes HDS (Hébergement de Données de Santé), pour vous offrir le plus haut niveau de protection et de confidentialité.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Security Feature 1 -->
            <div class="security-feature" data-aos="fade-up">
                <i class="fas fa-shield-alt text-5xl mb-4 text-primary"></i>
                <h3 class="text-2xl font-bold mb-2">Conformité HDS</h3>
                <p class="text-gray-700">Nos infrastructures respectent les exigences strictes de l'Hébergement de Données de Santé, assurant une protection optimale des informations sensibles de vos patients.</p>
            </div>
            <!-- Security Feature 2 -->
            <div class="security-feature" data-aos="fade-up" data-aos-delay="100">
                <i class="fas fa-lock text-5xl mb-4 text-secondary"></i>
                <h3 class="text-2xl font-bold mb-2">Hébergement en France</h3>
                <p class="text-gray-700">Vos données sont stockées sur des serveurs situés en France, garantissant une conformité totale avec les réglementations locales et européennes.</p>
            </div>
            <!-- Security Feature 3 -->
            <div class="security-feature" data-aos="fade-up" data-aos-delay="200">
                <i class="fas fa-user-shield text-5xl mb-4 text-primary"></i>
                <h3 class="text-2xl font-bold mb-2">Protection des Données</h3>
                <p class="text-gray-700">Nous utilisons des protocoles de sécurité avancés pour assurer la confidentialité et l'intégrité de vos informations professionnelles et celles de vos patients.</p>
            </div>
        </div>
    </div>
</section>

<!-- Social Responsibility Section -->
<section class="py-12 bg-green-50">
    <div class="container mx-auto text-center px-4">
        <h2 class="text-3xl font-bold mb-8 text-primary">Notre Engagement Écologique</h2>
        <p class="text-lg text-gray-700 max-w-3xl mx-auto mb-8">
            Chez AromaMade PRO, nous croyons en la responsabilité sociale et environnementale. Nous nous engageons à réduire notre impact écologique et à promouvoir des pratiques durables dans tout ce que nous faisons.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Ecology Feature 1 -->
            <div class="ecology-feature" data-aos="fade-up">
                <i class="fas fa-leaf text-5xl mb-4 text-primary"></i>
                <h3 class="text-2xl font-bold mb-2">Zéro Déchet de Papier</h3>
                <p class="text-gray-700">Nous adoptons une politique "zéro papier" en numérisant tous nos processus, ce qui réduit considérablement notre consommation de papier et préserve les ressources forestières.</p>
            </div>
			<!-- Ecology Feature 2 -->
			<div class="ecology-feature" data-aos="fade-up" data-aos-delay="100">
				<i class="fas fa-laptop-code text-5xl mb-4 text-secondary"></i>
				<h3 class="text-2xl font-bold mb-2">Empreinte Numérique Réduite</h3>
				<p class="text-gray-700">Nous optimisons nos infrastructures et nos codes pour minimiser la consommation d'énergie et réduire l'impact environnemental de nos services numériques.</p>
			</div>

			<!-- Ecology Feature 3 -->
			<div class="ecology-feature" data-aos="fade-up" data-aos-delay="200">
				<i class="fas fa-paw text-5xl mb-4 text-primary"></i>
				<h3 class="text-2xl font-bold mb-2">Protection de la Faune</h3>
				<p class="text-gray-700">Nous soutenons des initiatives visant à protéger les animaux et leurs habitats, contribuant ainsi à la préservation de la biodiversité.</p>
			</div>

        </div>
    </div>
</section>

    <!-- FAQ Section -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8 text-primary">Questions Fréquentes</h2>
            <div class="accordion">
<!-- FAQ Item 4 -->
<div class="accordion-item">
    <div class="accordion-header">
        <h3>Quelles sont les fonctionnalités incluses dans chaque plan ?</h3>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="accordion-content">
        <p>Chaque plan offre un ensemble de fonctionnalités adaptées à vos besoins. Le plan Starter inclut les fonctionnalités de base pour débuter, tandis que le plan Pro offre l'accès à toutes les fonctionnalités avancées de l'application.</p>
    </div>
</div>
<!-- FAQ Item 5 -->
<div class="accordion-item">
    <div class="accordion-header">
        <h3>Comment assurez-vous la sécurité de mes données et celles de mes patients ?</h3>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="accordion-content">
        <p>Nous hébergeons vos données en France, en conformité avec les normes HDS, pour garantir la sécurité et la confidentialité de toutes les informations.</p>
    </div>
</div>
<!-- FAQ Item 6 -->
<div class="accordion-item">
    <div class="accordion-header">
        <h3>Puis-je utiliser AromaMade PRO sur plusieurs appareils ?</h3>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="accordion-content">
        <p>Oui, vous pouvez accéder à votre compte depuis n'importe quel appareil connecté à Internet, que ce soit un ordinateur, une tablette ou un smartphone.</p>
    </div>
</div>
<!-- FAQ Item 7 -->
<div class="accordion-item">
    <div class="accordion-header">
        <h3>Proposez-vous une assistance en cas de besoin ?</h3>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="accordion-content">
        <p>Oui, notre équipe de support est disponible pour vous aider par email ou via le chat en direct pendant les heures ouvrables.</p>
    </div>
</div>
<!-- FAQ Item 8 -->
<div class="accordion-item">
    <div class="accordion-header">
        <h3>Puis-je personnaliser mon Portail Pro ?</h3>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="accordion-content">
        <p>Absolument, vous pouvez personnaliser votre Portail Pro avec vos informations, vos services, et vos disponibilités pour offrir la meilleure expérience à vos clients.</p>
    </div>
</div>
<!-- FAQ Item 9 -->
<div class="accordion-item">
    <div class="accordion-header">
        <h3>Comment fonctionne la visio-conférence intégrée ?</h3>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="accordion-content">
        <p>La visio-conférence intégrée vous permet de réaliser des séances à distance directement depuis l'application, sans avoir besoin d'installer de logiciels supplémentaires.</p>
    </div>
</div>
<!-- FAQ Item 10 -->
<div class="accordion-item">
    <div class="accordion-header">
        <h3>Puis-je exporter mes données si je décide de quitter la plateforme ?</h3>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="accordion-content">
        <p>Oui, vous pouvez exporter vos données à tout moment pour les conserver ou les utiliser avec d'autres outils.</p>
    </div>
</div>
<!-- FAQ Item 11 -->
<div class="accordion-item">
    <div class="accordion-header">
        <h3>Offrez-vous des remises pour les abonnements annuels ?</h3>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="accordion-content">
        <p>Oui, nous proposons des tarifs préférentiels pour les abonnements annuels, vous permettant d'économiser sur le long terme.</p>
    </div>
</div>
<!-- FAQ Item 12 -->
<div class="accordion-item">
    <div class="accordion-header">
        <h3>Comment puis-je passer du plan Starter au plan Pro ?</h3>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="accordion-content">
        <p>Vous pouvez mettre à niveau votre abonnement à tout moment depuis votre espace client en sélectionnant le plan Pro.</p>
    </div>
</div>
<!-- FAQ Item 13 -->
<div class="accordion-item">
    <div class="accordion-header">
        <h3>Y a-t-il une formation disponible pour apprendre à utiliser AromaMade PRO ?</h3>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="accordion-content">
        <p>Oui, nous proposons des tutoriels et un support dédié pour vous aider à maîtriser toutes les fonctionnalités de l'application.</p>
    </div>
</div>
<!-- FAQ Item 14 -->
<div class="accordion-item">
    <div class="accordion-header">
        <h3>Comment mes clients peuvent-ils prendre rendez-vous en ligne ?</h3>
        <i class="fas fa-chevron-down"></i>
    </div>
    <div class="accordion-content">
        <p>Vos clients peuvent réserver des séances directement via votre Portail Pro, où ils pourront choisir le service et l'horaire qui leur conviennent.</p>
    </div>
</div>

                <!-- Add more FAQ items if needed -->
            </div>
        </div>
    </section>

    <!-- Final Call to Action Section -->
    <section class="py-12 bg-green-100">
        <div class="container mx-auto text-center px-4">
            <h2 class="text-3xl font-bold mb-6 text-primary">Rejoignez la communauté des thérapeutes innovants</h2>
            <p class="text-lg max-w-3xl mx-auto mb-8 text-gray-700">
                Essayez AromaMade PRO gratuitement pendant 14 jours et découvrez comment notre application peut transformer votre pratique.
            </p>
            <a href="{{ route('register-pro') }}" class="btn-primary animate-pulse">Commencer votre essai gratuit</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto text-center px-4">
            <p>&copy; {{ date('Y') }} AromaMade PRO. Tous droits réservés.</p>
            <!-- Social Icons (optional) -->
            <div class="social-icons flex justify-center space-x-4 mt-4">
                <a href="#" class="text-gray-400 hover:text-blue-500 transition-colors duration-300">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors duration-300">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-pink-500 transition-colors duration-300">
                    <i class="fab fa-instagram"></i>
                </a>
            </div>
        </div>
    </footer>

    <!-- Custom Styles -->
    <style>
        /* Custom Colors */
        :root {
            --primary-color: #647a0b;
            --secondary-color: #854f38;
        }

        body {
            font-family: 'Roboto', sans-serif;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
        }

        .text-primary {
            color: var(--primary-color);
        }

        .bg-primary {
            background-color: var(--primary-color);
        }

        .text-secondary {
            color: var(--secondary-color);
        }

        .bg-secondary {
            background-color: var(--secondary-color);
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.125rem;
            transition: transform 0.3s, box-shadow 0.3s;
            display: inline-block;
        }

        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--primary-color);
            padding: 12px 24px;
            border: 2px solid var(--primary-color);
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.125rem;
            transition: background-color 0.3s, color 0.3s;
        }

        .btn-secondary:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* Hero Section */
        .hero {
            background-size: cover;
            background-position: center;
            position: relative;
            height: 80vh;
        }

        .hero .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        /* Feature Cards */
        .feature-card {
            transition: box-shadow 0.3s, transform 0.3s;
        }

        .feature-card:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-10px);
        }

        /* Testimonial Cards */
        .testimonial-card {
            padding: 20px;
            border-radius: 8px;
            background-color: #f7fafc;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Pricing Cards */
        .pricing-card {
            background-color: #ffffff;
            padding: 24px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: transform 0.3s;
        }

        .pricing-card:hover {
            transform: scale(1.05);
        }

        /* FAQ Accordion */
        .accordion .accordion-item {
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 0;
        }

        .accordion .accordion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-size: 1.25rem;
            color: var(--primary-color);
        }

        .accordion .accordion-content {
            display: none;
            padding-top: 12px;
            color: #4a5568;
        }

        .accordion .accordion-item.active .accordion-content {
            display: block;
        }

        /* Wave Separator */
        .wave-container {
            position: relative;
            overflow: hidden;
            line-height: 0;
        }

        .wave-container svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 100px;
        }

        /* Animations */
        .animate-fade-in {
            animation: fadeIn 1.5s ease-in-out;
        }

        .animate-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero .container {
                padding-top: 6rem;
                padding-bottom: 6rem;
            }
        }
   /* Define the .feature-hidden class */
        .feature-hidden {
            display: none;
        }
    </style>

     @push('scripts')
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>

    <!-- Show More Features Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize AOS for animations
            AOS.init({
                once: true
            });

            // FAQ Accordion functionality
            const accordionItems = document.querySelectorAll('.accordion-item');

            accordionItems.forEach(item => {
                const header = item.querySelector('.accordion-header');
                header.addEventListener('click', () => {
                    item.classList.toggle('active');
                });
            });

            // Show More Features functionality
            const showMoreButton = document.getElementById('show-more-features');
            const additionalFeatures = document.getElementById('additional-features');

            showMoreButton.addEventListener('click', () => {
                additionalFeatures.classList.toggle('feature-hidden');
                if (additionalFeatures.classList.contains('feature-hidden')) {
                    showMoreButton.textContent = 'Voir toutes les fonctionnalités';
                } else {
                    showMoreButton.textContent = 'Voir moins de fonctionnalités';
                    // Re-initialize AOS animations for newly displayed content
                    AOS.refresh();
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
