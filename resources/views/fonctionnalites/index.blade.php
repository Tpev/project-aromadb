<x-app-layout>
    @section('title', 'Fonctionnalités | Agenda, Dossiers, Facturation, Questionnaires | AromaMade PRO')
    @section('meta_description')
Découvrez toutes les fonctionnalités d’AromaMade PRO : agenda en ligne, dossiers clients, facturation (devis, factures, livre de recettes), questionnaires, Portail Pro et paiements sécurisés. Données hébergées en France (HDS), conforme RGPD.
    @endsection

    @push('styles')
        <!-- AOS -->
        <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
        <!-- Icons & Fonts -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto&display=swap" rel="stylesheet">
        <!-- Reuse same shared feature stylesheet as other pages -->
        <link rel="stylesheet" href="{{ asset('css/feature-agenda.css') }}">
    @endpush

    <!-- HERO (same structure/design as other feature pages) -->
    <section class="hero relative">
        <div class="hero-bg absolute w-full h-full bg-center bg-cover" style="background-image:url('{{ asset('images/features-hero.webp') }}');">
            <div class="overlay absolute inset-0 bg-gradient-to-b from-black via-transparent to-black opacity-60"></div>
        </div>
        <div class="container mx-auto text-center relative z-10 py-24 px-4">
            <nav class="breadcrumb" aria-label="breadcrumb">
                <a href="{{ url('/') }}">Accueil</a> <span>›</span>
                <span class="current">Fonctionnalités</span>
            </nav>
            <h1 class="text-white text-5xl md:text-6xl font-bold mb-6" data-aos="fade-up">
                Toutes les fonctionnalités AromaMade PRO
            </h1>
            <p class="text-white text-xl md:text-2xl mb-8 max-w-4xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Agenda & réservation, dossiers clients, facturation, questionnaires, Portail Pro et paiements tout ce qu’il faut pour gérer et développer votre activité.
            </p>
            <div class="cta-group" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register-pro') }}" class="btn-primary">Essayer gratuitement 14 jours</a>
                <a href="{{ url('/pro') }}" class="btn-secondary">Découvrir AromaMade PRO</a>
            </div>
        </div>
        <div class="overlay absolute inset-0 bg-black opacity-50"></div>
    </section>

    <!-- FEATURE GRID (uses same .feature-tile style as other pages) -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Ce que vous pouvez faire avec AromaMade PRO</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-10">

                <div class="feature-tile" data-aos="fade-up">
                    <i class="fas fa-calendar-check tile-icon"></i>
                    <h3>Agenda & réservation</h3>
                    <p>Réservation en ligne via votre <strong>Portail Pro</strong>, confirmations et rappels email automatiques, synchronisation Google / Apple / Outlook.</p>
                    <div class="mt-3">
                        <a href="{{ url('/fonctionnalites/agenda') }}" class="text-link">En savoir plus →</a>
                    </div>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="50">
                    <i class="fas fa-folder-open tile-icon"></i>
                    <h3>Dossiers clients</h3>
                    <p>Historique, notes, objectifs, consentements, documents & photos. Exports PDF/CSV. Données hébergées en France (<strong>HDS</strong>), <strong>RGPD</strong>.</p>
                    <div class="mt-3">
                        <a href="{{ url('/fonctionnalites/dossiers-clients') }}" class="text-link">En savoir plus →</a>
                    </div>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-file-invoice-dollar tile-icon"></i>
                    <h3>Facturation</h3>
                    <p>Devis → facture en un clic, numérotation séquentielle, mentions légales, TVA/dispense, <strong>livre de recettes</strong> (micro), exports PDF/CSV.</p>
                    <div class="mt-3">
                        <a href="{{ url('/fonctionnalites/facturation') }}" class="text-link">En savoir plus →</a>
                    </div>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="150">
                    <i class="fas fa-list-alt tile-icon"></i>
                    <h3>Questionnaires & formulaires</h3>
                    <p>Anamnèse, bilans pré/post-séance, consentements signés. Envoi par email/lien privé, ou remplissage <strong>en direct</strong> pendant la séance.</p>
                    <div class="mt-3">
                        <a href="{{ url('/fonctionnalites/questionnaires') }}" class="text-link">En savoir plus →</a>
                    </div>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-id-card tile-icon"></i>
                    <h3>Portail Pro</h3>
                    <p>Vitrine en ligne : services, tarifs, avis, événements/inscriptions. Lien unique + QR code. <strong>Présent dans notre annuaire</strong> de praticiens.</p>
                    <div class="mt-3">
                        <a href="{{ url('/fonctionnalites/portail-pro') }}" class="text-link">En savoir plus →</a>
                    </div>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="250">
                    <i class="fas fa-credit-card tile-icon"></i>
                    <h3>Paiements en ligne</h3>
                    <p>Lien de paiement sur facture, acomptes/solde, reçus automatiques, remboursements. SCA (3D Secure) via Stripe, cadre PSD2.</p>
                    <div class="mt-3">
                        <a href="{{ url('/fonctionnalites/paiements') }}" class="text-link">En savoir plus →</a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- CONNECTED FLOW (reuse “steps” visual language) -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Un écosystème simple qui travaille pour vous</h2>

            <div class="steps mt-10">
                <div class="step" data-aos="fade-right">
                    <span class="bubble">1</span>
                    <div>
                        <h3>Réservation</h3>
                        <p>Le client réserve depuis votre Portail Pro. Confirmation et rappels sont envoyés automatiquement par email.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="100">
                    <span class="bubble">2</span>
                    <div>
                        <h3>Suivi & documents</h3>
                        <p>Dossier client centralisé : notes, objectifs, consentements, questionnaires et documents horodatés.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="200">
                    <span class="bubble">3</span>
                    <div>
                        <h3>Facturation & paiements</h3>
                        <p>Devis → facture, lien de paiement sécurisé, reçus automatiques. <strong>Livre de recettes</strong> pour micro-entreprise.</p>
                    </div>
                </div>
            </div>

            <div class="center mt-12" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">Créer mon compte</a>
                <a href="{{ url('/tarifs') }}" class="btn-secondary">Voir les tarifs</a>
            </div>
        </div>
    </section>

    <!-- TRUST / SECURITY STRIP (same logos / layout) -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="section-title" data-aos="fade-up">Sécurité & conformité par défaut</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Données hébergées en France sur infrastructure <strong>HDS</strong>. Chiffrement en transit et au repos, gestion des accès, conformité <strong>RGPD</strong>.
            </p>
            <div class="logo-row mt-8" data-aos="fade-up" data-aos-delay="150">
                <img src="{{ asset('images/security/france.svg') }}" alt="Hébergement en France" />
                <img src="{{ asset('images/security/hds.svg') }}" alt="HDS" />
                <img src="{{ asset('images/security/rgpd.svg') }}" alt="RGPD" />
            </div>
        </div>
    </section>

    <!-- FAQ (same accordion pattern + tiny JS toggle) -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Questions fréquentes</h2>

            <div class="accordion mt-8 max-w-4xl mx-auto">

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>À qui s’adresse AromaMade PRO ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>AromaMade PRO s’adresse aux praticiens du bien-être (naturopathes, sophrologues, réflexologues, etc.). L’outil n’est pas destiné à la pratique médicale.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Où sont hébergées les données ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>En France, sur une infrastructure <strong>HDS</strong>, avec chiffrement et sauvegardes. Respect du <strong>RGPD</strong>.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Puis-je tester gratuitement ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui : <strong>essai gratuit 14 jours</strong>, sans carte bancaire pour démarrer.</p>
                    </div>
                </div>

            </div>

            <div class="center mt-12" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">Commencer mon essai gratuit</a>
            </div>
        </div>
    </section>

    <!-- FINAL CTA (same CTA style) -->
    <section class="py-16 bg-green-100">
        <div class="container mx-auto text-center px-4">
            <h2 class="section-title" data-aos="fade-up">Tout votre cabinet, bien organisé</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Gagnez du temps, offrez une meilleure expérience et gardez votre activité sous contrôle — du premier contact au paiement.
            </p>
            <div class="mt-8" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register-pro') }}" class="btn-primary">Créer mon compte</a>
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
