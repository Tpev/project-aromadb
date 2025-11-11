<x-app-layout>
    @section('title', 'Dossiers clients | Suivi thérapeutique & documents | AromaMade PRO')
    @section('meta_description')
Centralisez l’historique, les notes et objectifs de vos clients. Stockage sécurisé HDS en France, consentements, documents, photos, questionnaires et exports PDF. Le dossier du praticien, clair et complet.
    @endsection

    @push('styles')
        <!-- AOS -->
        <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
        <!-- Icons & Fonts -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto&display=swap" rel="stylesheet">
        <!-- Reuse the same feature stylesheet -->
        <link rel="stylesheet" href="{{ asset('css/feature-agenda.css') }}">
    @endpush

    <!-- HERO -->
    <section class="hero relative">
        <div class="hero-bg absolute w-full h-full bg-center bg-cover" style="background-image:url('{{ asset('images/dossiers-hero.webp') }}');">
            <div class="overlay absolute inset-0 bg-gradient-to-b from-black via-transparent to-black opacity-60"></div>
        </div>
        <div class="container mx-auto text-center relative z-10 py-24 px-4">
            <nav class="breadcrumb" aria-label="breadcrumb">
                <a href="{{ url('/') }}">Accueil</a> <span>›</span>
                <a href="{{ url('/fonctionnalites') }}">Fonctionnalités</a> <span>›</span>
                <span class="current">Dossiers clients</span>
            </nav>
            <h1 class="text-white text-5xl md:text-6xl font-bold mb-6" data-aos="fade-up">
                Dossiers clients clairs, complets et sécurisés
            </h1>
            <p class="text-white text-xl md:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Historique, notes, objectifs, documents et consentements — tout au même endroit pour un suivi professionnel et serein.
            </p>
            <div class="cta-group" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register-pro') }}" class="btn-primary">Commencer l’essai gratuit</a>
                <a href="{{ url('/pro') }}" class="btn-secondary">Découvrir AromaMade PRO</a>
            </div>
        </div>
        <div class="overlay absolute inset-0 bg-black opacity-50"></div>
    </section>

    <!-- 3 BENEFITS -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Le cœur de votre pratique, bien organisé</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
                <div class="card" data-aos="fade-up">
                    <i class="fas fa-folder-open card-icon"></i>
                    <h3 class="card-title">Tout au même endroit</h3>
                    <p>Fiches clients, séances, documents, photos et questionnaires sont centralisés pour un accès immédiat.</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-bullseye card-icon"></i>
                    <h3 class="card-title">Suivi structuré</h3>
                    <p>Notes cliniques, objectifs par client et par séance, alertes et contre-indications pour un suivi précis.</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-shield-alt card-icon"></i>
                    <h3 class="card-title">Sécurité & conformité</h3>
                    <p>Données hébergées en France sur infrastructure <strong>HDS</strong>, conformité <strong>RGPD</strong> incluse.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURE GRID -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Un dossier client pensé pour les praticiens</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-10">
                <div class="feature-tile" data-aos="fade-up">
                    <i class="fas fa-id-card tile-icon"></i>
                    <h3>Fiche client complète</h3>
                    <p>Coordonnées, antécédents, préférences, contre-indications et tags personnalisés pour filtrer facilement.</p>
                </div>
                <div class="feature-tile" data-aos="fade-up" data-aos-delay="50">
                    <i class="fas fa-stream tile-icon"></i>
                    <h3>Historique des séances</h3>
                    <p>Vue chronologique de toutes les séances avec accès direct aux notes, objectifs et documents associés.</p>
                </div>
                <div class="feature-tile" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-notes-medical tile-icon"></i>
                    <h3>Notes structurées</h3>
                    <p>Notes libres ou modèles structurés (ex. SOAP). Modèles enregistrés pour gagner du temps au quotidien.</p>
                </div>
                <div class="feature-tile" data-aos="fade-up" data-aos-delay="150">
                    <i class="fas fa-bullseye tile-icon"></i>
                    <h3>Objectifs & progression</h3>
                    <p>Définissez des objectifs par client, suivez l’évolution séance après séance et consignez les résultats.</p>
                </div>
                <div class="feature-tile" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-file-upload tile-icon"></i>
                    <h3>Documents & photos</h3>
                    <p>Ajoutez des fichiers, photos, comptes-rendus, ordonnances ou bilans et retrouvez-les en un clic.</p>
                </div>
                <div class="feature-tile" data-aos="fade-up" data-aos-delay="250">
                    <i class="fas fa-file-signature tile-icon"></i>
                    <h3>Consentements</h3>
                    <p>Consignez les consentements et formulaires signés, attachés au dossier pour une traçabilité parfaite.</p>
                </div>
                <div class="feature-tile" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas fa-list-alt tile-icon"></i>
                    <h3>Questionnaires & bilans</h3>
                    <p>Envoyez des questionnaires avant ou après la séance. Les réponses sont stockées dans le dossier.</p>
                </div>
                <div class="feature-tile" data-aos="fade-up" data-aos-delay="350">
                    <i class="fas fa-exclamation-triangle tile-icon"></i>
                    <h3>Alertes & précautions</h3>
                    <p>Signalez les allergies, contre-indications et précautions. Alertes visuelles lors de la prise de rendez-vous.</p>
                </div>
                <div class="feature-tile" data-aos="fade-up" data-aos-delay="400">
                    <i class="fas fa-file-export tile-icon"></i>
                    <h3>Exports & partages</h3>
                    <p>Export PDF/CSV des données essentielles et partage sécurisé de documents si besoin.</p>
                </div>
            </div>

            <div class="center mt-10" data-aos="fade-up" data-aos-delay="450">
                <a href="{{ url('/tarifs') }}" class="btn-secondary">Voir les tarifs</a>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Comment ça fonctionne ?</h2>
            <div class="steps mt-10">
                <div class="step" data-aos="fade-right">
                    <span class="bubble">1</span>
                    <div>
                        <h3>Créez la fiche client</h3>
                        <p>Saisissez les informations essentielles, appliquez des tags et renseignez les précautions à connaître.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="100">
                    <span class="bubble">2</span>
                    <div>
                        <h3>Consignez chaque séance</h3>
                        <p>Ajoutez vos notes, objectifs, photos et documents en quelques secondes, depuis le dossier.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="200">
                    <span class="bubble">3</span>
                    <div>
                        <h3>Suivez l’évolution</h3>
                        <p>Visualisez la progression par objectif et préparez la prochaine séance en toute confiance.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="300">
                    <span class="bubble">4</span>
                    <div>
                        <h3>Partagez & exportez</h3>
                        <p>Générez un PDF ou exportez les données utiles lorsque nécessaire, dans le respect du RGPD.</p>
                    </div>
                </div>
            </div>

            <div class="center mt-12" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">Essayer maintenant</a>
            </div>
        </div>
    </section>

    <!-- TRUST / SECURITY STRIP -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto px-4 text-center">
            <h2 class="section-title" data-aos="fade-up">Sécurité & conformité par défaut</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Hébergement en France sur infrastructure <strong>HDS</strong>. Chiffrement en transit et au repos, gestion des accès et traçabilité pour protéger vos données et celles de vos clients.
            </p>
            <div class="logo-row mt-8" data-aos="fade-up" data-aos-delay="150">
                <img src="{{ asset('images/security/france.svg') }}" alt="Hébergement en France" />
                <img src="{{ asset('images/security/hds.svg') }}" alt="HDS" />
                <img src="{{ asset('images/security/rgpd.svg') }}" alt="RGPD" />
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Pensé avec des praticiens</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
                <div class="testimonial-card" data-aos="fade-up">
                    <p class="quote">« Le dossier client d’AromaMade me fait gagner un temps fou. Les notes et objectifs sont bien rangés et je retrouve tout en 2 secondes. »</p>
                    <h4 class="author">— Élodie, Réflexologue</h4>
                </div>
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                    <p class="quote">« J’apprécie surtout la partie documents et consentements. C’est clair, enregistré au bon endroit et je suis serein sur la conformité. »</p>
                    <h4 class="author">— Marc, Naturopathe</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Questions fréquentes</h2>
            <div class="accordion mt-8 max-w-4xl mx-auto">
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Puis-je créer mes propres modèles de notes ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui. Créez et enregistrez vos modèles (ex. SOAP) pour les réutiliser lors des prochaines séances.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Où sont stockés les documents ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Tous les fichiers sont stockés en France sur des serveurs conformes HDS, avec sauvegardes régulières.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Les consentements sont-ils horodatés ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui. Chaque consentement est horodaté et rattaché au dossier du client, avec la version du document signé.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Puis-je exporter un dossier ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Vous pouvez générer un PDF récapitulatif et exporter certaines données en CSV pour archivage.</p>
                    </div>
                </div>
            </div>

            <div class="center mt-12" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">Essayer gratuitement 14 jours</a>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 bg-green-100">
        <div class="container mx-auto text-center px-4">
            <h2 class="section-title" data-aos="fade-up">Le dossier client qui vous fait gagner du temps</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Préparez vos séances en quelques minutes, gardez l’historique clair et offrez un suivi professionnel à vos clients.
            </p>
            <div class="mt-8" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register-pro') }}" class="btn-primary">Commencer mon essai gratuit</a>
            </div>
        </div>
    </section>

    @push('scripts')
        <!-- AOS -->
        <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                AOS.init({ once: true });
                document.querySelectorAll('.accordion-item').forEach(item => {
                    const header = item.querySelector('.accordion-header');
                    header.addEventListener('click', () => item.classList.toggle('open'));
                });
            });
        </script>
    @endpush
</x-app-layout>
