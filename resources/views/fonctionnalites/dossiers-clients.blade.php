<x-app-layout>
    @section('title', 'Dossiers clients | Suivi & documents | AromaMade PRO')
    @section('meta_description')
Centralisez l’historique, les notes de séance et objectifs de vos clients. Consentements signés (SES), documents, photos, questionnaires et exports PDF. Stockage en France chez un hébergeur certifié HDS, conforme RGPD.
    @endsection

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/feature-agenda.css') }}">
    @endpush>

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
                Historique, notes de séance, objectifs, documents et consentements — tout au même endroit pour un suivi professionnel et serein (non médical).
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
                    <p>Notes de séance, objectifs par client et par rendez-vous, alertes et précautions pour un suivi précis.</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-shield-alt card-icon"></i>
                    <h3 class="card-title">Sécurité & conformité</h3>
                    <p>Données hébergées en France chez un prestataire <strong>certifié HDS</strong>, conformité <strong>RGPD</strong> incluse.</p>
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
                    <p>Coordonnées, préférences, informations utiles, précautions et tags personnalisés pour filtrer facilement.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="50">
                    <i class="fas fa-stream tile-icon"></i>
                    <h3>Historique des séances</h3>
                    <p>Vue chronologique de toutes les séances avec accès direct aux notes, objectifs et documents associés.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-notes-medical tile-icon"></i>
                    <h3>Notes de séance structurées</h3>
                    <p>Notes libres ou modèles (ex. SOAP d’accompagnement). Enregistrez vos modèles pour gagner du temps.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="150">
                    <i class="fas fa-bullseye tile-icon"></i>
                    <h3>Objectifs & progression</h3>
                    <p>Définissez des objectifs par client, suivez l’évolution séance après séance et consignez les résultats.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-file-upload tile-icon"></i>
                    <h3>Documents & photos</h3>
                    <p>Ajoutez des fichiers, photos et comptes-rendus. Intégrez, si le client vous les remet, des documents médicaux <em>fournis par le client</em>.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="250">
                    <i class="fas fa-file-signature tile-icon"></i>
                    <h3>Consentements (SES eIDAS)</h3>
                    <p>Faites signer les consentements / bilans avec une signature électronique simple (SES), horodatée et rattachée au dossier.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas fa-list-alt tile-icon"></i>
                    <h3>Questionnaires & bilans</h3>
                    <p>Envoyez des questionnaires avant/après séance. Les réponses sont stockées automatiquement dans le dossier.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="350">
                    <i class="fas fa-exclamation-triangle tile-icon"></i>
                    <h3>Alertes & précautions</h3>
                    <p>Signalez allergies, contre-indications déclarées et précautions. Alertes visuelles lors de la prise de rendez-vous.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="400">
                    <i class="fas fa-share-square tile-icon"></i>
                    <h3>Partage sécurisé</h3>
                    <p>Partage de documents via lien protégé (mot de passe + expiration). Journalisation des accès.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="450">
                    <i class="fas fa-history tile-icon"></i>
                    <h3>Historique & versions</h3>
                    <p>Traçabilité complète : horodatage, auteur, historique non destructif des modifications (audit trail).</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="500">
                    <i class="fas fa-user-check tile-icon"></i>
                    <h3>Attestation de présence</h3>
                    <p>Générez une attestation de présence (PDF) depuis la séance en un clic, avec vos infos de praticien.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="550">
                    <i class="fas fa-file-export tile-icon"></i>
                    <h3>Exports & RGPD</h3>
                    <p>Export PDF/CSV des données utiles, réponse aux demandes d’accès/portabilité et aide au suivi des durées de conservation.</p>
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
                        <h3>Créez la fiche client</h3>
                        <p>Saisissez les informations essentielles, appliquez des tags et renseignez les précautions à connaître.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="100">
                    <span class="bubble">2</span>
                    <div>
                        <h3>Consignez chaque séance</h3>
                        <p>Ajoutez vos notes de séance, objectifs, photos et documents en quelques secondes, depuis le dossier.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="200">
                    <span class="bubble">3</span>
                    <div>
                        <h3>Faites signer si besoin</h3>
                        <p>Envoyez consentements et autres documents à signer (SES). Tout est horodaté et rattaché au dossier.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="300">
                    <span class="bubble">4</span>
                    <div>
                        <h3>Exportez & partagez</h3>
                        <p>Générez un PDF récapitulatif ou partagez un document via lien sécurisé, dans le respect du RGPD.</p>
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
                Hébergement en France chez un prestataire <strong>certifié HDS</strong>. Chiffrement en transit et au repos, contrôle d’accès nominatif et traçabilité des opérations.
            </p>
            <div class="logo-row mt-8" data-aos="fade-up" data-aos-delay="150">
                <img src="{{ asset('images/security/france.svg') }}" alt="Hébergement en France" />
                <img src="{{ asset('images/security/hds.svg') }}" alt="HDS" />
                <img src="{{ asset('images/security/rgpd.svg') }}" alt="RGPD" />
            </div>
        </div>
    </section>

    <!-- DISCLAIMER (legal safety) -->
    <section class="py-8 bg-white">
        <div class="container mx-auto px-4">
            <div class="p-4 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-900 text-sm" data-aos="fade-up">
                <strong>Important :</strong> AromaMade PRO s’adresse à des praticiens du bien-être. La plateforme n’est pas un service de télémédecine ni un Dossier Médical Partagé. 
                Les informations saisies ne constituent ni un diagnostic, ni une prescription médicale et ne remplacent pas l’avis d’un professionnel de santé.
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
                    <p class="quote">« J’apprécie surtout la partie documents, consentements et le partage sécurisé. Je suis serein sur la conformité. »</p>
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
                        <p>Oui. Créez et enregistrez vos modèles (ex. SOAP d’accompagnement) pour les réutiliser lors des prochaines séances.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Les signatures sont-elles valables ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Nous utilisons la <strong>signature électronique simple (SES)</strong> conforme au règlement <strong>eIDAS</strong>, adaptée pour des consentements et documents d’accompagnement.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Où sont stockés les documents ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Tous les fichiers sont stockés en France chez un hébergeur <strong>certifié HDS</strong>, avec sauvegardes régulières et chiffrement.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Puis-je répondre à une demande d’accès RGPD ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui. Vous pouvez exporter le dossier (PDF/CSV) et partager des documents via lien protégé par mot de passe et expiration.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Proposez-vous un consentement “photos avant/après” ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui, c’est un modèle distinct du consentement de séance. Il précise l’usage des images et les droits du client.</p>
                    </div>
                </div>
            </div>

            <div class="center mt-12" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">Essayer gratuitement 14 jours</a>
            </div>
        </div>
    </section>
<section style="padding:64px 0;background:#ffffff;">
    <div style="max-width:1100px;margin:0 auto;padding:0 24px;">
        <div style="
            background:#ffffff;
            border:1px solid #e5e7eb;
            border-radius:16px;
            padding:32px;
        ">
            <div style="
                display:flex;
                justify-content:space-between;
                align-items:flex-start;
                flex-wrap:wrap;
                gap:24px;
            ">
                <div style="max-width:620px;">
                    <h3 style="
                        font-size:1.5rem;
                        font-weight:700;
                        color:#647a0b;
                        margin-bottom:8px;
                    ">
                        Vous êtes praticien ? Découvrez la page dédiée à votre métier
                    </h3>

                    <p style="
                        color:#4b5563;
                        line-height:1.7;
                        font-size:1.05rem;
                    ">
                        Ces pages expliquent comment AromaMade PRO s’adapte à votre pratique :
                        organisation du cabinet, suivi client, prise de rendez-vous en ligne et facturation.
                    </p>
                </div>

                <div style="
                    display:flex;
                    flex-wrap:wrap;
                    gap:12px;
                ">
                    <a href="{{ url('/metiers/naturopathe') }}"
                       title="Logiciel pour naturopathe"
                       style="
                           display:inline-flex;
                           align-items:center;
                           gap:8px;
                           padding:10px 16px;
                           border-radius:999px;
                           background:rgba(100,122,11,.08);
                           border:1px solid rgba(100,122,11,.2);
                           color:#647a0b;
                           font-weight:700;
                           font-size:.95rem;
                           text-decoration:none;
                           transition:all .25s ease;
                       "
                       onmouseover="this.style.background='rgba(100,122,11,.15)'"
                       onmouseout="this.style.background='rgba(100,122,11,.08)'"
                    >
                        🌿 Logiciel naturopathe
                    </a>

                    <a href="{{ url('/metiers/sophrologue') }}"
                       title="Logiciel pour sophrologue"
                       style="
                           display:inline-flex;
                           align-items:center;
                           gap:8px;
                           padding:10px 16px;
                           border-radius:999px;
                           background:rgba(100,122,11,.08);
                           border:1px solid rgba(100,122,11,.2);
                           color:#647a0b;
                           font-weight:700;
                           font-size:.95rem;
                           text-decoration:none;
                           transition:all .25s ease;
                       "
                       onmouseover="this.style.background='rgba(100,122,11,.15)'"
                       onmouseout="this.style.background='rgba(100,122,11,.08)'"
                    >
                        🧘 Logiciel sophrologue
                    </a>
                </div>
            </div>

            <p style="
                margin-top:20px;
                font-size:.85rem;
                color:#6b7280;
            ">
               
            </p>
        </div>
    </div>
</section>
    <!-- CTA -->
    <section class="py-16 bg-green-100">
        <div class="container mx-auto text-center px-4">
            <h2 class="section-title" data-aos="fade-up">Le dossier client qui vous fait gagner du temps</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Préparez vos séances en quelques minutes, gardez l’historique clair et offrez un suivi professionnel — sans complexité.
            </p>
            <div class="mt-8" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register-pro') }}" class="btn-primary">Commencer mon essai gratuit</a>
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
