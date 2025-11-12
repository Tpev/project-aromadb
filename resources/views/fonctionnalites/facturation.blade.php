<x-app-layout>
    @section('title', 'Facturation thérapeute | Devis, factures, livre de recettes | AromaMade PRO')
    @section('meta_description')
Créez devis et factures propres, suivez les paiements (espèces, virement, CB), gérez acomptes/avoirs et exportez votre livre de recettes (micro-entreprise). Numérotation séquentielle, mentions légales 🇫🇷 et tableau de chiffre d’affaires mensuel.
    @endsection

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto&display=swap" rel="stylesheet">
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
                Devis, factures & livre de recettes simples et conformes
            </h1>
            <p class="text-white text-xl md:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Émettez vos documents en quelques secondes, enregistrez les paiements et suivez votre chiffre d’affaires mois par mois.
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
                    <p>Devis, factures et reçus en 2 clics : modèles propres, duplication, envoi par email depuis l’application.</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-shield-alt card-icon"></i>
                    <h3 class="card-title">Conforme en France</h3>
                    <p>Numérotation séquentielle, mentions obligatoires, TVA/dispense, <strong>livre de recettes</strong> (micro-entreprise) et archivage horodaté.</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-chart-line card-icon"></i>
                    <h3 class="card-title">Suivi du CA mensuel</h3>
                    <p>Tableau de bord par mois (HT/TTC, modes de règlement). Exports CSV/XLSX pour votre déclaration ou votre comptable.</p>
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
                    <p>Créez un devis, envoyez-le pour validation, puis transformez-le en facture en 1 clic. Traçabilité des versions conservée.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="50">
                    <i class="fas fa-hashtag tile-icon"></i>
                    <h3>Numérotation séquentielle</h3>
                    <p>Numéros uniques et continus par année/série. Verrouillage après émission, et journal d’audit non destructif.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-stamp tile-icon"></i>
                    <h3>Mentions légales 🇫🇷</h3>
                    <p>Identité, date, désignation, quantités/prix, TVA ou <em>TVA non applicable, art. 293 B CGI</em> selon votre statut, conditions de règlement.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="150">
                    <i class="fas fa-percentage tile-icon"></i>
                    <h3>TVA, TTC/HT & remises</h3>
                    <p>Lignes HT/TTC, remises, multi-taux si nécessaire. Totaux, soldes et mentions calculés automatiquement sur vos PDF.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-hand-holding-usd tile-icon"></i>
                    <h3>Acomptes & avoirs</h3>
                    <p>Facture d’acompte, régularisation sur la facture finale. <strong>Avoir</strong> pour correction : contre-passation propre et traçable.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="250">
                    <i class="fas fa-credit-card tile-icon"></i>
                    <h3>Enregistrement des paiements</h3>
                    <p>Renseignez le règlement depuis la facture : <strong>espèces</strong>, <strong>virement</strong> ou <strong>CB</strong>. Reçu PDF généré et joint à l’email.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas fa-link tile-icon"></i>
                    <h3>Paiement en ligne (factures)</h3>
                    <p>Ajoutez un lien de paiement sécurisé à vos <strong>factures</strong> (Stripe). <em>Les devis ne comportent pas de lien de paiement.</em></p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="350">
                    <i class="fas fa-book tile-icon"></i>
                    <h3>Livre de recettes automatique</h3>
                    <p>Micro-entreprise : chaque encaissement alimente un registre <strong>numéroté et horodaté</strong>, non modifiable. Export <strong>CSV/XLSX/PDF</strong>.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="400">
                    <i class="fas fa-envelope-open-text tile-icon"></i>
                    <h3>Envoi & relances email</h3>
                    <p>Envoyez devis/factures depuis AromaMade, relancez automatiquement en cas d’échéance dépassée. Pièces jointes propres.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="450">
                    <i class="fas fa-chart-bar tile-icon"></i>
                    <h3>Tableau CA mensuel</h3>
                    <p>Vue mensuelle du CA encaissé, filtres par mode de paiement, produit/prestation, client. Export période en 1 clic.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="500">
                    <i class="fas fa-file-export tile-icon"></i>
                    <h3>Exports & archivage</h3>
                    <p>Exports PDF/CSV par période, téléchargement des pièces. Conservation et traçabilité pour votre comptabilité.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="550">
                    <i class="fas fa-info-circle tile-icon"></i>
                    <h3>Devis ≠ facture</h3>
                    <p>Les <strong>devis</strong> n’impactent pas la numérotation des <strong>factures</strong> et n’ouvrent pas de paiement en ligne. Conversion en un clic.</p>
                </div>
            </div>

            <div class="center mt-10" data-aos="fade-up" data-aos-delay="600">
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
                        <p>Sélectionnez le client et les prestations, ajustez prix/TVA/remises, puis envoyez le PDF par email.</p>
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
                        <h3>Encaissez & enregistrez</h3>
                        <p>Enregistrez le règlement (espèces / virement / CB). Le statut se met à jour et un reçu est envoyé au client.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="300">
                    <span class="bubble">4</span>
                    <div>
                        <h3>Suivez & exportez</h3>
                        <p>Consultez votre CA mensuel et exportez votre livre de recettes (si micro-entreprise), vos PDF et vos CSV.</p>
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
                Paiement en ligne (Stripe) sur facture, PDF lisibles par tous, exports CSV/XLSX pour votre comptable, et reçus envoyés automatiquement.
            </p>
            <div class="logo-row mt-8" data-aos="fade-up" data-aos-delay="150">
                <img src="{{ asset('images/integrations/stripe.svg') }}" alt="Stripe" />
                <img src="{{ asset('images/integrations/pdf.svg') }}" alt="PDF" />
                <img src="{{ asset('images/integrations/csv.svg') }}" alt="CSV/XLSX" />
            </div>
        </div>
    </section>

    <!-- DISCLAIMER (cash register / scope) -->
    <section class="py-8 bg-white">
        <div class="container mx-auto px-4">
            <div class="p-4 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-900 text-sm" data-aos="fade-up">
                <strong>À savoir :</strong> AromaMade PRO facilite l’édition de devis/factures et le suivi des encaissements. 
                La solution n’est pas un <em>logiciel de caisse certifié (NF525)</em>. 
                Si votre activité est soumise à l’usage d’un logiciel de caisse certifié, utilisez un système conforme en complément.
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Des documents nets et une compta plus simple</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
                <div class="testimonial-card" data-aos="fade-up">
                    <p class="quote">« Je transforme mes devis en factures en un clic, et l’export du livre de recettes m’aide pour la déclaration. »</p>
                    <h4 class="author">— Maud, Sophrologue</h4>
                </div>
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                    <p class="quote">« Les relances automatiques et les liens de paiement m’ont permis d’être réglé plus vite, sans relancer à la main. »</p>
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
                        <p>Oui : numérotation séquentielle, mentions obligatoires (identité, date, désignation, quantités/prix, TVA ou mention de dispense si applicable), conditions de règlement, archivage et traçabilité.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Proposez-vous un livre de recettes ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui, pour micro-entrepreneurs : chaque encaissement crée une ligne <strong>numérotée, datée et horodatée</strong>, non modifiable. Export <strong>CSV/XLSX/PDF</strong> par période.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Un devis peut-il être payé en ligne ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Non : les liens de paiement sont ajoutés uniquement aux <strong>factures</strong>. Un devis accepté se convertit en facture payable.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Puis-je gérer TVA et remises ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui : HT/TTC, remises par ligne, multi-taux si nécessaire et mention automatique “TVA non applicable, art. 293 B CGI” selon votre statut.</p>
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
