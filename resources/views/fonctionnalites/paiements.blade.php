<x-app-layout>
    @section('title', 'Paiements en ligne | Acomptes, soldes, remboursements | AromaMade PRO')
    @section('meta_description')
Encaissez vos séances en ligne de façon sécurisée : lien de paiement sur facture, acomptes, soldes, reçus automatiques, relances email, remboursements partiels. SCA (3D Secure) via Stripe & cadre PSD2. Compatible CB, Apple Pay, Google Pay, SEPA.
    @endsection

    @push('styles')
        <!-- AOS -->
        <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
        <!-- Icons & Fonts -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto&display=swap" rel="stylesheet">
        <!-- Shared feature stylesheet -->
        <link rel="stylesheet" href="{{ asset('css/feature-agenda.css') }}">
    @endpush

    <!-- HERO -->
    <section class="hero relative">
        <div class="hero-bg absolute w-full h-full bg-center bg-cover" style="background-image:url('{{ asset('images/paiements-hero.webp') }}');">
            <div class="overlay absolute inset-0 bg-gradient-to-b from-black via-transparent to-black opacity-60"></div>
        </div>
        <div class="container mx-auto text-center relative z-10 py-24 px-4">
            <nav class="breadcrumb" aria-label="breadcrumb">
                <a href="{{ url('/') }}">Accueil</a> <span>›</span>
                <a href="{{ url('/fonctionnalites') }}">Fonctionnalités</a> <span>›</span>
                <span class="current">Paiements</span>
            </nav>
            <h1 class="text-white text-5xl md:text-6xl font-bold mb-6" data-aos="fade-up">
                Encaissez en ligne, simplement et en toute sécurité
            </h1>
            <p class="text-white text-xl md:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Lien de paiement sur vos factures, acomptes et soldes, reçus automatiques, relances email et rapprochement instantané.
            </p>
            <div class="cta-group" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register-pro') }}" class="btn-primary">Activer les paiements</a>
                <a href="{{ url('/pro') }}" class="btn-secondary">Découvrir AromaMade PRO</a>
            </div>
        </div>
        <div class="overlay absolute inset-0 bg-black opacity-50"></div>
    </section>

    <!-- 3 BENEFITS -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Pensé pour les praticiens, fluide pour les clients</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
                <div class="card" data-aos="fade-up">
                    <i class="fas fa-link card-icon"></i>
                    <h3 class="card-title">Lien de paiement sur facture</h3>
                    <p>Ajoutez un <strong>lien sécurisé</strong> à vos factures. Le client règle en quelques secondes, vous recevez la confirmation et le reçu part automatiquement.</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-university card-icon"></i>
                    <h3 class="card-title">Sécurisé & conforme</h3>
                    <p>Authentification forte (SCA 3D Secure) et cadre <strong>PSD2</strong> assurés via Stripe. CB, Apple Pay, Google Pay, SEPA (selon activation).</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-sync-alt card-icon"></i>
                    <h3 class="card-title">Statuts synchronisés</h3>
                    <p>La facture passe automatiquement en “payée” et le <strong>rapprochement</strong> se fait dans votre tableau de bord.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURE GRID -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Tout ce qu’il faut pour être payé à temps</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-10">
                <div class="feature-tile" data-aos="fade-up">
                    <i class="fas fa-receipt tile-icon"></i>
                    <h3>Factures payables en ligne</h3>
                    <p>Insérez un lien de paiement sécurisé sur chaque <strong>facture</strong>. Les devis ne sont pas payables avant conversion.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="50">
                    <i class="fas fa-hand-holding-usd tile-icon"></i>
                    <h3>Acomptes & soldes</h3>
                    <p>Demandez un <strong>acompte</strong> avant la séance, puis encaissez le solde. Tout est tracé et ventilé sur la facture finale.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-ticket-alt tile-icon"></i>
                    <h3>Paiement à l’inscription (événements)</h3>
                    <p>Pour vos <strong>ateliers/formations</strong> : exigez le paiement au moment de l’inscription et <strong>gérez la capacité</strong> automatiquement.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="150">
                    <i class="fas fa-envelope-open-text tile-icon"></i>
                    <h3>Relances email automatiques</h3>
                    <p>Relances programmées en cas d’échéance dépassée : ton poli, lien direct, reçu envoyé après paiement.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-undo tile-icon"></i>
                    <h3>Remboursements</h3>
                    <p>Remboursements <strong>totaux ou partiels</strong> depuis la facture. Statuts et reçus mis à jour automatiquement.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="250">
                    <i class="fas fa-wallet tile-icon"></i>
                    <h3>Modes de paiement</h3>
                    <p>CB, Apple Pay, Google Pay, SEPA (selon activation Stripe). Le client choisit le mode qu’il préfère.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas fa-book tile-icon"></i>
                    <h3>Livre de recettes (micro)</h3>
                    <p>Chaque encaissement alimente votre <strong>livre de recettes</strong>. Export PDF/CSV pour vos déclarations.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="350">
                    <i class="fas fa-file-export tile-icon"></i>
                    <h3>Exports comptables</h3>
                    <p>Exports PDF/CSV par période, pièces jointes aux factures et historique des règlements pour votre comptable.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="400">
                    <i class="fas fa-plug tile-icon"></i>
                    <h3>Intégration Stripe</h3>
                    <p>Encaissements sécurisés via Stripe. Activation rapide et suivi centralisé dans AromaMade PRO.</p>
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
                        <h3>Activez les paiements</h3>
                        <p>Connectez votre compte Stripe et sélectionnez les moyens de paiement à proposer (CB, wallets, SEPA).</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="100">
                    <span class="bubble">2</span>
                    <div>
                        <h3>Ajoutez le lien sur la facture</h3>
                        <p>Chaque facture peut inclure un <strong>lien sécurisé</strong>. Le client paie en ligne et reçoit son reçu par email.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="200">
                    <span class="bubble">3</span>
                    <div>
                        <h3>Suivez les règlements</h3>
                        <p>Les statuts se mettent à jour automatiquement ; déclenchez des relances si nécessaire.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="300">
                    <span class="bubble">4</span>
                    <div>
                        <h3>Exportez & comptabilisez</h3>
                        <p>Exports PDF/CSV, <strong>livre de recettes</strong> (micro-entreprise) et archivage des justificatifs.</p>
                    </div>
                </div>
            </div>

            <div class="center mt-12" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">Encaisser en ligne</a>
            </div>
        </div>
    </section>

    <!-- INTEGRATIONS / TRUST -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto px-4 text-center">
            <h2 class="section-title" data-aos="fade-up">Moyens de paiement & conformité</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Cartes (Visa, MasterCard), Apple Pay, Google Pay, virement SEPA (selon activation Stripe). SCA (3D Secure) et cadre PSD2 assurés par Stripe.
            </p>
            <div class="logo-row mt-8" data-aos="fade-up" data-aos-delay="150">
                <img src="{{ asset('images/payments/stripe.svg') }}" alt="Stripe" />
                <img src="{{ asset('images/payments/visa.svg') }}" alt="Visa" />
                <img src="{{ asset('images/payments/mastercard.svg') }}" alt="Mastercard" />
                <img src="{{ asset('images/payments/applepay.svg') }}" alt="Apple Pay" />
                <img src="{{ asset('images/payments/googlepay.svg') }}" alt="Google Pay" />
                <img src="{{ asset('images/payments/sepa.svg') }}" alt="SEPA" />
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Des paiements clairs et des reçus automatiques</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
                <div class="testimonial-card" data-aos="fade-up">
                    <p class="quote">« J’envoie mes factures avec un lien de paiement. Les clients règlent en ligne et je reçois le reçu automatiquement. »</p>
                    <h4 class="author">— Anaïs, Praticienne bien-être</h4>
                </div>
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                    <p class="quote">« Acomptes + solde gérés proprement. Les relances email m’ont clairement aidé à réduire les retards de paiement. »</p>
                    <h4 class="author">— David, Naturopathe</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Questions fréquentes — Paiements</h2>
            <div class="accordion mt-8 max-w-4xl mx-auto">
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Puis-je encaisser un acompte avant la séance ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui. Émettez une <strong>facture d’acompte</strong> avec lien de paiement, puis déduisez l’acompte sur la facture finale.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Les devis sont-ils payables en ligne ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Non. Seules les <strong>factures</strong> contiennent un lien de paiement. Convertissez d’abord votre devis en facture.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Quels moyens de paiement sont disponibles ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Cartes bancaires (Visa, MasterCard), Apple Pay, Google Pay, et SEPA selon l’activation de votre compte Stripe.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Puis-je rembourser un client ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui, remboursements <strong>totaux ou partiels</strong> directement depuis la facture ; statuts et reçus sont mis à jour.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Quand arrive l’argent sur mon compte ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Les <strong>virements</strong> vers votre banque sont gérés par Stripe (délais et planning de versement variables). Les paiements <strong>SEPA</strong> ont un délai de confirmation plus long.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Les frais de paiement sont-ils inclus ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Des <strong>frais Stripe</strong> s’appliquent sur chaque transaction. Le détail figure dans votre compte Stripe.</p>
                    </div>
                </div>
            </div>

            <div class="center mt-12" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">Activer les paiements en ligne</a>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 bg-green-100">
        <div class="container mx-auto text-center px-4">
            <h2 class="section-title" data-aos="fade-up">Soyez réglé plus vite, sans complication</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Lien de paiement sur facture, reçus automatiques, relances email et exports comptables : tout est pensé pour gagner du temps.
            </p>
            <div class="mt-8" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register-pro') }}" class="btn-primary">Essayer gratuitement 14 jours</a>
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
