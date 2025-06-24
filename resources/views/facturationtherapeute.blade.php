<x-app-layout>

    @section('title', 'Facturation et devis pour thérapeute : logiciel AromaMade')
    @section('meta_description', 'Découvrez AromaMade, l’application idéale pour créer facilement vos factures et devis thérapeute en ligne. Modèles gratuits, automatisation et paiement sécurisé.')

<section class="hero relative"
         style="background-image: url('{{ asset('images/facturation-therapeute-en-ligne.webp') }}');
                background-size: cover;
                background-position: center center;
                background-repeat: no-repeat;">
    <!-- Balise image cachée pour le SEO -->
    <img src="{{ asset('images/facturation-therapeute-en-ligne.webp') }}"
         alt="Illustration de la facturation en ligne pour thérapeutes avec AromaMade"
         class="hidden">

    <div class="overlay absolute inset-0 bg-black opacity-50"></div>

    <div class="container mx-auto relative z-10 py-24 px-6 lg:px-20 max-w-6xl text-center">
        <h1 class="text-5xl font-bold text-white">Facturation simplifiée pour thérapeutes</h1>
        <p class="text-xl text-white mt-4">Créez facilement vos factures et devis avec AromaMade</p>
        <a href="{{ route('register-pro') }}" class="btn-primary mt-6">Essai gratuit 14 jours</a>
    </div>
</section>





<!-- Section Factures Rapides -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-primary mb-4">Factures thérapeute en quelques clics</h2>

        <p class="text-lg text-gray-700">Avec AromaMade, vous gagnez un temps précieux sur la gestion de votre facturation. Notre outil a été conçu spécifiquement pour répondre aux besoins des thérapeutes : simplicité, conformité légale, rapidité d’exécution et image professionnelle.</p>

        <p class="mt-4 text-lg text-gray-700">Fini les tableurs ou les documents Word à dupliquer et adapter manuellement. Dès la création de votre compte, vous pouvez personnaliser votre logo, vos informations professionnelles et vos mentions obligatoires. AromaMade intègre ces éléments dans chaque facture automatiquement, vous garantissant des documents conformes et cohérents.</p>

        <p class="mt-4 text-lg text-gray-700">La génération de factures devient un jeu d’enfant : sélectionnez votre client, ajoutez la prestation réalisée, appliquez si besoin la TVA ou une exonération selon votre statut (ex : article 293B du CGI), et envoyez en un clic. Le tout dans une interface fluide et intuitive, utilisable sur ordinateur, tablette ou smartphone.</p>

        <p class="mt-4 text-lg text-gray-700">Chaque facture émise est stockée automatiquement dans l’espace de votre client et reste accessible à tout moment. Vous avez également accès à un tableau de bord pour suivre les paiements en temps réel, savoir qui a réglé, relancer si besoin en quelques clics.</p>

        <p class="mt-4 text-lg text-gray-700">Cette fluidité et cette rigueur dans la facturation vous permettent de gagner en sérénité et en crédibilité auprès de vos clients. Vous envoyez des documents clairs, bien présentés, avec un suivi professionnel de A à Z.</p>

        <ul class="mt-6 list-disc ml-5 text-lg text-gray-700">
            <li>Création de facture en moins d'une minute</li>
            <li>Modèles pré-remplis et adaptables</li>
            <li>Numérotation automatique conforme</li>
            <li>TVA gérée selon votre statut</li>
            <li>Logo et mentions personnalisables</li>
            <li>Envoi automatique par email</li>
            <li>Suivi des paiements et relances</li>
            <li>Historique client complet</li>
        </ul>

        <p class="mt-6 text-lg text-gray-700">Notre objectif est simple : vous libérer du temps et vous permettre de vous concentrer sur votre cœur de métier. La facturation ne doit plus être une corvée, mais un réflexe automatisé et sécurisé.</p>

        <a href="https://support.aromamade.com/fr/tutoriel-facturation-aromamade-pro" class="text-primary font-bold mt-6 inline-block">Découvrez comment créer votre facture →</a>
    </div>
</section>


    <!-- Section Devis intégrés -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-primary mb-4">Devis thérapeute automatisés</h2>
            <p class="text-lg text-gray-700">Générez rapidement des devis thérapeute professionnels avec conversion automatique en factures.</p>
            <ul class="mt-4 list-disc ml-5">
                <li>Modèles prêts à l'emploi et adaptables</li>
                <li>Respect des obligations légales</li>
                <li>Conversion devis → facture en 1 clic</li>
            </ul>
            <a href="https://support.aromamade.com/fr/conseils-metiers/devis-therapeute-modele" class="text-primary font-bold mt-4 inline-block">Voir nos modèles de devis →</a>
        </div>
    </section>

    <!-- Paiement sécurisé Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-primary mb-4">Paiement en ligne sécurisé</h2>
            <p class="text-lg text-gray-700">Offrez à vos clients la possibilité de payer directement en ligne de manière sécurisée.</p>
            <ul class="mt-4 list-disc ml-5">
                <li>Paiement sécurisé par carte bancaire</li>
                <li>Notifications en temps réel des paiements</li>
                <li>Moins de relances administratives</li>
            </ul>
        </div>
    </section>
<!-- H2 (NEW): Obligations légales pour les thérapeutes -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-primary mb-4">Obligations légales pour les thérapeutes</h2>
        <p class="text-lg text-gray-700">En tant que thérapeute, certaines mentions légales doivent impérativement figurer sur vos factures :</p>
        <ul class="mt-4 list-disc ml-5">
            <li>Numéro SIRET et numéro d'identification professionnel</li>
            <li>Numérotation chronologique continue</li>
            <li>Date de la prestation et description claire du service fourni</li>
            <li>Statut de TVA : exonération éventuelle selon l'article 293B du CGI</li>
            <li>Coordonnées complètes du praticien et du client</li>
        </ul>
        <p class="mt-4 text-gray-700">Une fois votre compte correctement configuré, AromaMade ajoutera automatiquement toutes les mentions légales nécessaires à vos documents.</p>
    </div>
</section>

<!-- H2 (NEW): Pourquoi choisir AromaMade plutôt que facture à la main sur Word ? -->
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-primary mb-4">Pourquoi choisir AromaMade plutôt que facture à la main sur Word ?</h2>
        <p class="text-lg text-gray-700">Créer ses factures manuellement avec Word peut sembler simple, mais génère des risques et des pertes de temps importants :</p>
        <ul class="mt-4 list-disc ml-5">
            <li><strong>Risques d’erreurs légales</strong> : mentions obligatoires oubliées, erreurs de numérotation.</li>
            <li><strong>Temps perdu à relancer les paiements</strong> : aucun suivi automatisé des règlements.</li>
            <li><strong>Archivage complexe</strong> : gestion manuelle des documents administratifs.</li>
            <li><strong>Image professionnelle réduite</strong> : manque de cohérence et d’esthétique.</li>
        </ul>
        <p class="mt-4 text-gray-700">AromaMade simplifie l’ensemble du processus : création, suivi automatisé des paiements, archivage sécurisé et facilite le respect des obligations légales.</p>
    </div>
</section>
<section class="py-12 bg-green-50">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-primary mb-4">Bien plus qu’un simple outil de facturation</h2>
        <p class="text-lg text-gray-700 max-w-3xl mx-auto">
            La facturation, c’est seulement une petite partie de ce que propose AromaMade. Notre application est conçue pour accompagner les thérapeutes dans la gestion complète de leur activité : prise de rendez-vous, dossiers patients, questionnaires, visio, paiements, inventaire, portail pro, synchronisation calendrier et plus encore.
        </p>
        <p class="mt-6 text-lg text-gray-700">Découvrez toutes les fonctionnalités qui révolutionnent votre quotidien de praticien.</p>
        <a href="/pro" class="btn-secondary mt-6 inline-block">Voir toutes les fonctionnalités →</a>
    </div>
</section>

<!-- H2 (NEW): Témoignages utilisateurs sur la facturation AromaMade -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-primary mb-4">Témoignages utilisateurs sur la facturation AromaMade</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Témoignage 1 -->
            <div class="testimonial-card p-6 bg-gray-50 rounded shadow">
                <p class="italic text-gray-700">« AromaMade m'a fait gagner plusieurs heures chaque semaine grâce à son système automatisé. Fini les erreurs ou oublis sur mes factures, tout est parfait dès la première fois ! »</p>
                <h4 class="mt-4 font-bold text-primary">— Claire, Sophrologue</h4>
            </div>
            <!-- Témoignage 2 -->
            <div class="testimonial-card p-6 bg-gray-50 rounded shadow">
                <p class="italic text-gray-700">« La fonctionnalité d’envoi automatique et le suivi simplifié des paiements me permettent enfin de me concentrer à 100% sur mes séances avec mes patients. »</p>
                <h4 class="mt-4 font-bold text-primary">— Julien, Réflexologue</h4>
            </div>
        </div>
    </div>
</section>

    <!-- Exemple facture PDF Section -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-primary mb-4">Exemple gratuit de facture thérapeute</h2>
            <p class="text-lg text-gray-700">Téléchargez gratuitement un exemple de facture thérapeute en format PDF, respectant les obligations légales actuelles.</p>
            <a href="https://support.aromamade.com/fr/conseils-metiers/exemple-facture-therapeute" class="text-primary font-bold mt-4 inline-block">Télécharger l'exemple PDF →</a>
        </div>
    </section>

    <!-- Avantages Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-primary mb-4">Pourquoi choisir AromaMade ?</h2>
            <ul class="mt-4 list-disc ml-5 text-lg text-gray-700">
                <li>Facile à utiliser, pensé spécifiquement pour les thérapeutes</li>
                <li>Automatisation de vos tâches administratives</li>
                <li>Respect strict des mentions légales obligatoires</li>
                <li>Service client réactif et disponible</li>
            </ul>
        </div>
    </section>

    <!-- FAQ Schema Section -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto px-4" itemscope itemtype="https://schema.org/FAQPage">
            <h2 class="text-3xl font-bold text-primary mb-6">Questions fréquentes sur la facturation thérapeute</h2>

            <div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question" class="mb-4">
                <h3 itemprop="name" class="font-bold">Comment créer une facture rapidement avec AromaMade ?</h3>
                <div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                    <p itemprop="text">Créez une facture thérapeute en quelques clics grâce à nos modèles pré-remplis et facilement adaptables directement depuis votre interface.</p>
                </div>
            </div>

            <div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question" class="mb-4">
                <h3 itemprop="name" class="font-bold">Est-ce que AromaMade gère aussi les devis thérapeute ?</h3>
                <div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                    <p itemprop="text">Oui, vous pouvez créer facilement des devis thérapeute et les convertir automatiquement en factures en quelques secondes.</p>
                </div>
            </div>

        </div>
    </section>

    <!-- Final Call to Action -->
    <section class="py-12 bg-primary text-white text-center">
        <a href="{{ route('register-pro') }}" class="btn-primary">Commencer votre essai gratuit dès maintenant !</a>
    </section>
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
</x-app-layout>
