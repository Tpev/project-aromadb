<x-app-layout>
    @section('title', 'Questionnaires & formulaires | Pré-séance, suivi, consentements | AromaMade PRO')
    @section('meta_description')
Créez des questionnaires professionnels pour vos clients : anamnèse, bilans pré/post-séance, consentements signés, fichiers joints. Envoi par email, lien sécurisé, réponses stockées dans le dossier client, exports PDF/CSV. Données hébergées en France (HDS), conforme RGPD.
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
        <div class="hero-bg absolute w-full h-full bg-center bg-cover" style="background-image:url('{{ asset('images/questionnaires-hero.webp') }}');">
            <div class="overlay absolute inset-0 bg-gradient-to-b from-black via-transparent to-black opacity-60"></div>
        </div>
        <div class="container mx-auto text-center relative z-10 py-24 px-4">
            <nav class="breadcrumb" aria-label="breadcrumb">
                <a href="{{ url('/') }}">Accueil</a> <span>›</span>
                <a href="{{ url('/fonctionnalites') }}">Fonctionnalités</a> <span>›</span>
                <span class="current">Questionnaires & formulaires</span>
            </nav>
            <h1 class="text-white text-5xl md:text-6xl font-bold mb-6" data-aos="fade-up">
                Questionnaires pro : anamnèse, suivi et consentements
            </h1>
            <p class="text-white text-xl md:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Envoyez un formulaire avant, pendant ou après la séance. Les réponses sont enregistrées dans le dossier client et exploitables en un clic.
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
            <h2 class="section-title text-center" data-aos="fade-up">Des formulaires pensés pour les praticiens</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
                <div class="card" data-aos="fade-up">
                    <i class="fas fa-paperclip card-icon"></i>
                    <h3 class="card-title">Flexible & complet</h3>
                    <p>Questions libres, choix multiples, échelles, cases à cocher, fichiers, signatures — composez le formulaire adapté à votre pratique.</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-link card-icon"></i>
                    <h3 class="card-title">Envoi par email & lien sécurisé</h3>
                    <p>Envoyez le questionnaire par email ou partagez un lien. Remplissage sans compte si vous le souhaitez.</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-folder card-icon"></i>
                    <h3 class="card-title">Dossier client intégré</h3>
                    <p>Chaque réponse est stockée dans le <strong>dossier client</strong>, consultable avant la séance et exportable en PDF/CSV.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURE GRID -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Tout ce qu’il vous faut pour des bilans précis</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-10">
                <div class="feature-tile" data-aos="fade-up">
                    <i class="fas fa-list tile-icon"></i>
                    <h3>Types de champs variés</h3>
                    <p>Texte, long texte, choix unique/multiple, curseur d’échelle, date, numéro, fichier, signature manuscrite.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="50">
                    <i class="fas fa-sitemap tile-icon"></i>
                    <h3>Logique conditionnelle</h3>
                    <p>Affichez des questions selon des réponses précédentes pour garder des formulaires courts et pertinents.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-shield-alt tile-icon"></i>
                    <h3>Consentements signés</h3>
                    <p>Ajoutez des consentements, conditions et cases d’acceptation. Horodatage et archivage dans le dossier.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="150">
                    <i class="fas fa-user-check tile-icon"></i>
                    <h3>Sans compte si besoin</h3>
                    <p>Vos clients peuvent répondre via un <strong>lien privé</strong> sans créer de compte. Token sécurisé et durée de validité.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-envelope-open-text tile-icon"></i>
                    <h3>Envois & rappels email</h3>
                    <p>Envoyez le formulaire par email. Relance automatique si non complété à l’approche du rendez-vous.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="250">
                    <i class="fas fa-stream tile-icon"></i>
                    <h3>Avant / pendant / après séance</h3>
                    <p>Anamnèse préalable, check-in le jour J, bilan de suivi après séance — tout est tracé au bon endroit.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas fa-copy tile-icon"></i>
                    <h3>Modèles réutilisables</h3>
                    <p>Créez vos modèles (ex. bilan stress, habitudes, contre-indications) et réutilisez-les en un clic.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="350">
                    <i class="fas fa-file-export tile-icon"></i>
                    <h3>Exports PDF/CSV</h3>
                    <p>Générez un PDF propre pour le dossier ou exportez en CSV pour vos analyses et archives.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="400">
                    <i class="fas fa-lock tile-icon"></i>
                    <h3>RGPD & HDS</h3>
                    <p>Données hébergées en France sur infra <strong>HDS</strong>, accès restreints, chiffrement en transit et au repos.</p>
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
                        <h3>Créez votre modèle</h3>
                        <p>Assemblez vos questions, ajoutez des consentements et activez la logique conditionnelle si nécessaire.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="100">
                    <span class="bubble">2</span>
                    <div>
                        <h3>Envoyez le lien</h3>
                        <p>Partagez par email ou copiez le lien privé. Vous pouvez autoriser le remplissage sans compte.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="200">
                    <span class="bubble">3</span>
                    <div>
                        <h3>Collectez les réponses</h3>
                        <p>Les réponses arrivent automatiquement dans le dossier client, prêtes à être consultées en séance.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="300">
                    <span class="bubble">4</span>
                    <div>
                        <h3>Exportez & archivez</h3>
                        <p>Générez un PDF propre ou exportez en CSV pour votre suivi et vos obligations de traçabilité.</p>
                    </div>
                </div>
            </div>

            <div class="center mt-12" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">Créer mon premier questionnaire</a>
            </div>
        </div>
    </section>

    <!-- TRUST / SECURITY STRIP -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto px-4 text-center">
            <h2 class="section-title" data-aos="fade-up">Sécurité et conformité</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Hébergement en France, conformité <strong>RGPD</strong>, infrastructure <strong>HDS</strong>. Gestion fine des accès, horodatage des consentements, traçabilité.
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
            <h2 class="section-title text-center" data-aos="fade-up">Pensé pour votre quotidien</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
                <div class="testimonial-card" data-aos="fade-up">
                    <p class="quote">« L’anamnèse avant la première séance m’aide à préparer des séances plus ciblées. Tout est déjà dans le dossier. »</p>
                    <h4 class="author">— Zoé, Aromathérapeute</h4>
                </div>
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                    <p class="quote">« Les consentements signés et les exports PDF me simplifient la vie. C’est carré et professionnel. »</p>
                    <h4 class="author">— Paul, Naturopathe</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Questions fréquentes — Questionnaires</h2>
            <div class="accordion mt-8 max-w-4xl mx-auto">
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Mes clients doivent-ils créer un compte pour répondre ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Non, vous pouvez autoriser la réponse via un lien privé sécurisé, sans création de compte. À vous de choisir.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Puis-je récupérer un PDF du formulaire rempli ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui, chaque réponse peut être exportée en <strong>PDF</strong> et jointe au dossier client. Un export <strong>CSV</strong> est aussi disponible.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Proposez-vous des modèles prêts à l’emploi ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui : anamnèse, bilan bien-être, contre-indications, suivi post-séance… Vous pouvez aussi créer vos propres modèles.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Où sont stockées les données sensibles ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>En France, sur une infrastructure <strong>HDS</strong>, avec chiffrement et contrôles d’accès. Respect du <strong>RGPD</strong>.</p>
                    </div>
                </div>
            </div>

            <div class="center mt-12" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">Créer un questionnaire</a>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 bg-green-100">
        <div class="container mx-auto text-center px-4">
            <h2 class="section-title" data-aos="fade-up">Recueillez les bonnes infos, au bon moment</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Préparez mieux vos séances, structurez votre suivi et gagnez du temps à chaque consultation.
            </p>
            <div class="mt-8" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register-pro') }}" class="btn-primary">Essayer gratuitement</a>
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
