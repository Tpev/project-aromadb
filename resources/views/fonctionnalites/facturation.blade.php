<x-app-layout>
    @section('title', 'Facturation thérapeute | Devis, factures, livre de recettes | AromaMade PRO')
    @section('meta_description')
Générez devis et factures conformes, suivez les paiements, gérez acomptes/avoirs et exportez votre livre de recettes (micro-entreprise) en un clic. Numérotation séquentielle, mentions légales et TVA incluses.
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
        <div class="hero-bg absolute w-full h-full bg-center bg-cover" style="background-image:url('{{ asset('images/facturation-hero.webp') }}');">
            <div class="overlay absolute inset-0 bg-gradient-to-b from-black via-transparent to-black opacity-60"></div>
        </div>
        <div class="container mx-auto text-center relative z-10 py-24 px-4">
            <nav class="breadcrumb" aria-label="breadcrumb">
                <a href="{{ url('/') }}">Accueil</a> <span>›</span>
                <a href="{{ url('/fonctionnalites') }}">Fonctionnalités</a> <span>›</span>
                <span class="current">Facturation</span>
            </nav>
            <h1 class="text-white text-5xl md:text-6xl font-bold mb-6" data-aos="fade-up">
                Devis, factures & livre de recettes — simples et conformes
            </h1>
            <p class="text-white text-xl md:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Créez des documents professionnels en quelques secondes, suivez les paiements et restez en règle avec la réglementation française.
            </p>
            <div class="cta-group" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register-pro') }}" class="btn-primary">Essai gratuit 14 jours</a>
                <a href="{{ url('/pro') }}" class="btn-secondary">Découvrir AromaMade PRO</a>
            </div>
        </div>
        <div class="overlay absolute inset-0 bg-black opacity-50"></div>
    </section>

    <!-- 3 BENEFITS -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">La facturation pensée pour les praticiens</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
                <div class="card" data-aos="fade-up">
                    <i class="fas fa-file-invoice-dollar card-icon"></i>
                    <h3 class="card-title">Professionnel & rapide</h3>
                    <p>Devis, factures et reçus en 2 clics : modèles propres, duplications, envoi par email depuis l’application.</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-shield-alt card-icon"></i>
                    <h3 class="card-title">Conforme en France</h3>
                    <p>Numérotation séquentielle, mentions légales, TVA/dispense, livre de recettes (micro-entreprise) et archivage.</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-credit-card card-icon"></i>
                    <h3 class="card-title">Paiements facilités</h3>
                    <p>Lien de paiement sécurisé pour les factures, acomptes, relances. Suivi des règlements et rapprochement rapide.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURE GRID -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Tout ce qu’il faut pour facturer sans stress</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-10">
                <div class="feature-tile" data-aos="fade-up">
                    <i class="fas fa-file-contract tile-icon"></i>
                    <h3>Devis → Facture</h3>
                    <p>Créez un devis, envoyez-le pour validation, puis transformez-le en facture en 1 clic. Historique clair des versions.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="50">
                    <i class="fas fa-hashtag tile-icon"></i>
                    <h3>Numérotation séquentielle</h3>
                    <p>Numéros uniques et continus par année ou série. Verrouillage des numéros émis et traces de modification.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-stamp tile-icon"></i>
                    <h3>Mentions légales 🇫🇷</h3>
                    <p>Mentions obligatoires (identité, date, désignation, quantités/prix, TVA ou <em>TVA non applicable, art. 293 B CGI</em> si éligible), conditions de règlement.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="150">
                    <i class="fas fa-percentage tile-icon"></i>
                    <h3>TVA, TTC/HT & remises</h3>
                    <p>Lignes HT/TTC, remises, multi-taux si nécessaire. Totaux et soldes calculés automatiquement sur le document PDF.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-hand-holding-usd tile-icon"></i>
                    <h3>Acomptes & avoirs</h3>
                    <p>Demande d’acompte, facture d’acompte, régularisation sur la facture finale. Gestion des avoirs si besoin d’annulation partielle.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="250">
                    <i class="fas fa-link tile-icon"></i>
                    <h3>Paiement en ligne</h3>
                    <p>Ajoutez un lien de paiement sécurisé à vos <strong>factures</strong>. Suivi des règlements et relances automatiques par email.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas fa-book tile-icon"></i>
                    <h3>Livre de recettes</h3>
                    <p>Pour micro-entreprise : enregistrez vos encaissements et exportez votre <strong>livre de recettes</strong> (CSV/PDF) conforme aux exigences usuelles.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="350">
                    <i class="fas fa-envelope-open-text tile-icon"></i>
                    <h3>Envoi & relances email</h3>
                    <p>Envoyez devis/factures depuis AromaMade. Relances en cas d’échéance dépassée, reçus de paiement joints automatiquement.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="400">
                    <i class="fas fa-file-export tile-icon"></i>
                    <h3>Exports & archivage</h3>
                    <p>Exports PDF/CSV par période, téléchargement des pièces, conservation des documents émis pour votre traçabilité.</p>
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
                        <h3>Créez le devis</h3>
                        <p>Sélectionnez le client et les prestations, ajustez prix/TVA/remises, puis envoyez pour validation par email.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="100">
                    <span class="bubble">2</span>
                    <div>
                        <h3>Transformez en facture</h3>
                        <p>Le devis accepté devient une facture numérotée. Ajoutez un acompte si besoin, ou un lien de paiement sécurisé.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="200">
                    <span class="bubble">3</span>
                    <div>
                        <h3>Encaissez & suivez</h3>
                        <p>Enregistrez le règlement (espèces, CB, virement). Le statut se met à jour et un reçu est envoyé au client.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="300">
                    <span class="bubble">4</span>
                    <div>
                        <h3>Exportez vos données</h3>
                        <p>Générez vos PDF, exports CSV et votre livre de recettes (si micro-entreprise) pour votre comptabilité.</p>
                    </div>
                </div>
            </div>

            <div class="center mt-12" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">Essayer maintenant</a>
            </div>
        </div>
    </section>

    <!-- INTEGRATIONS -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto px-4 text-center">
            <h2 class="section-title" data-aos="fade-up">Prêt pour vos outils et règlements</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Lien de paiement sécurisé (Stripe) pour les factures, pièces jointes PDF lisibles par tous, exports CSV pour votre comptable.
            </p>
            <div class="logo-row mt-8" data-aos="fade-up" data-aos-delay="150">
                <img src="{{ asset('images/integrations/stripe.svg') }}" alt="Stripe" />
                <img src="{{ asset('images/integrations/pdf.svg') }}" alt="PDF" />
                <img src="{{ asset('images/integrations/csv.svg') }}" alt="CSV" />
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Des documents nets et une compta plus simple</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
                <div class="testimonial-card" data-aos="fade-up">
                    <p class="quote">« Je crée mes devis et les transforme en facture en un clic. Les relances par email m’ont permis d’être payée plus vite. »</p>
                    <h4 class="author">— Maud, Sophrologue</h4>
                </div>
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                    <p class="quote">« Le livre de recettes exportable m’a sauvé du temps pour ma déclaration. La numérotation est propre et conforme. »</p>
                    <h4 class="author">— Thomas, Naturopathe</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Questions fréquentes — Facturation</h2>
            <div class="accordion mt-8 max-w-4xl mx-auto">
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Vos documents sont-ils conformes en France ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui : numérotation séquentielle, mentions obligatoires (identité, date, désignation, quantités/prix, TVA ou mention de dispense si applicable), conditions de règlement, archivage des pièces.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Proposez-vous un livre de recettes ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui, pour les micro-entrepreneurs : enregistrez vos encaissements et exportez votre <strong>livre de recettes</strong> (CSV/PDF) avec les colonnes usuelles (date, client, montant, mode de règlement, référence).</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Un devis peut-il être payé en ligne ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Non : les liens de paiement sont ajoutés uniquement aux <strong>factures</strong>. Un devis accepté peut être transformé en facture payable.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Puis-je gérer la TVA et les remises ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui : gestion HT/TTC, remises par ligne, multi-taux si nécessaire et mention automatique “TVA non applicable, art. 293 B CGI” si votre statut l’exige.</p>
                    </div>
                </div>
            </div>

            <div class="center mt-12" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">Commencer mon essai gratuit</a>
            </div>
        </div>
    </section>

    <!-- FINAL CTA -->
    <section class="py-16 bg-green-100">
        <div class="container mx-auto text-center px-4">
            <h2 class="section-title" data-aos="fade-up">Des devis et factures propres, en règle et envoyés à temps</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Gagnez en professionnalisme, sécurisez vos encaissements et simplifiez votre comptabilité.
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
