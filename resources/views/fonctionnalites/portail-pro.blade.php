<x-app-layout>
    @section('title', 'Portail Pro | Vitrine en ligne & prise de rendez-vous | AromaMade PRO')
    @section('meta_description')
Créez votre vitrine en ligne : services, tarifs, disponibilités, avis clients et prise de rendez-vous. Lien unique à partager, QR code, SEO local, annuaire des praticiens et événements avec paiement à l’inscription.
    @endsection

    @push('styles')
        <!-- AOS -->
        <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
        <!-- Icons & Fonts -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto&display=swap" rel="stylesheet">
        <!-- Shared feature stylesheet -->
        <link rel="stylesheet" href="{{ asset('css/feature-agenda.css') }}">
    @endpush>

    <!-- HERO -->
    <section class="hero relative">
        <div class="hero-bg absolute w-full h-full bg-center bg-cover" style="background-image:url('{{ asset('images/portailpro-hero.webp') }}');">
            <div class="overlay absolute inset-0 bg-gradient-to-b from-black via-transparent to-black opacity-60"></div>
        </div>
        <div class="container mx-auto text-center relative z-10 py-24 px-4">
            <nav class="breadcrumb" aria-label="breadcrumb">
                <a href="{{ url('/') }}">Accueil</a> <span>›</span>
                <a href="{{ url('/fonctionnalites') }}">Fonctionnalités</a> <span>›</span>
                <span class="current">Portail Pro</span>
            </nav>
            <h1 class="text-white text-5xl md:text-6xl font-bold mb-6" data-aos="fade-up">
                Votre vitrine pro, prête à être partagée
            </h1>
            <p class="text-white text-xl md:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Présentez vos services, affichez vos disponibilités et laissez vos clients réserver en ligne depuis un lien unique et professionnel.
            </p>
            <div class="cta-group" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register-pro') }}" class="btn-primary">Créer mon Portail Pro</a>
                <a href="{{ url('/pro') }}" class="btn-secondary">Découvrir AromaMade PRO</a>
            </div>
        </div>
        <div class="overlay absolute inset-0 bg-black opacity-50"></div>
    </section>

    <!-- 3 BENEFICES -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Plus de visibilité, moins de friction</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
                <div class="card" data-aos="fade-up">
                    <i class="fas fa-bullhorn card-icon"></i>
                    <h3 class="card-title">Vitrine pro</h3>
                    <p>Une page claire avec vos <strong>services, tarifs, bio, photos</strong> et un bouton de réservation bien visible.</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-map-marker-alt card-icon"></i>
                    <h3 class="card-title">Pensé pour le SEO local</h3>
                    <p>Optimisé pour apparaître sur des recherches de type <em>“naturopathe + ville”</em> grâce aux <strong>sections dédiées</strong> et données structurées. Votre page est aussi listée dans notre <strong>annuaire des praticiens</strong>.</p>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-link card-icon"></i>
                    <h3 class="card-title">Partage instantané</h3>
                    <p><strong>Lien unique</strong> pour Instagram, Google Business, WhatsApp. <strong>QR code</strong> pour vos cartes et flyers.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES GRID -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Tout ce qu’il faut pour convertir vos visiteurs</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-10">
                <div class="feature-tile" data-aos="fade-up">
                    <i class="fas fa-list tile-icon"></i>
                    <h3>Services & tarifs</h3>
                    <p>Présentez vos prestations avec durées, tarifs, modes (cabinet, domicile, visio) et description claire.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="50">
                    <i class="fas fa-calendar-check tile-icon"></i>
                    <h3>Prise de RDV en ligne</h3>
                    <p>Bouton “Réserver” connecté à votre agenda. Choix du service, créneau et email de confirmation automatique.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-star tile-icon"></i>
                    <h3>Avis clients</h3>
                    <p>Affichez les <strong>avis de vos clients</strong> sur votre page pour renforcer la confiance et votre présence locale.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="150">
                    <i class="fas fa-user tile-icon"></i>
                    <h3>Bio & spécialités</h3>
                    <p>Expliquez votre approche, vos spécialisations et votre parcours pour inspirer confiance.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-qrcode tile-icon"></i>
                    <h3>QR code</h3>
                    <p>Générez un <strong>QR code</strong> vers <code>aromamade.com/pro/{{ '{slug}' }}</code> pour cartes de visite, affiches, salons.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="250">
                    <i class="fas fa-share-alt tile-icon"></i>
                    <h3>Intégrations sociales</h3>
                    <p>Boutons de partage et <strong>lien bio</strong> pour Instagram, Facebook, WhatsApp, Google Business Profile.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas fa-search-location tile-icon"></i>
                    <h3>Zones desservies</h3>
                    <p>Ajoutez vos villes cibles pour capter plus de recherches locales et préciser vos déplacements à domicile.</p>
                </div>

                <!-- Removed the original "Widget d’intégration" tile as requested -->

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="350">
                    <i class="fas fa-users tile-icon"></i>
                    <h3>Événements & ateliers</h3>
                    <p>Publiez vos <strong>ateliers, formations, cercles</strong> sur votre page. Gérez la <strong>capacité</strong> et les <strong>réservations</strong>.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="400">
                    <i class="fas fa-credit-card tile-icon"></i>
                    <h3>Paiement à la réservation</h3>
                    <p>Demandez un <strong>paiement sécurisé</strong> au moment de la réservation (rendez-vous ou événement).</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="450">
                    <i class="fas fa-address-card tile-icon"></i>
                    <h3>Annuaire des praticiens</h3>
                    <p>Votre Portail Pro apparaît automatiquement dans notre <strong>annuaire</strong> pour booster votre visibilité.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="500">
                    <i class="fas fa-shield-alt tile-icon"></i>
                    <h3>Respect de la vie privée</h3>
                    <p>Hébergement en France (HDS), conformité RGPD, consentements et mentions légales sur votre page publique.</p>
                </div>
            </div>

            <div class="center mt-10" data-aos="fade-up" data-aos-delay="550">
                <a href="{{ url('/tarifs') }}" class="btn-secondary">Voir les tarifs</a>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Mise en place en 4 étapes</h2>
            <div class="steps mt-10">
                <div class="step" data-aos="fade-right">
                    <span class="bubble">1</span>
                    <div>
                        <h3>Complétez votre profil</h3>
                        <p>Bio, photos, coordonnées, modes de consultation et zones desservies.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="100">
                    <span class="bubble">2</span>
                    <div>
                        <h3>Ajoutez services & tarifs</h3>
                        <p>Définissez vos prestations, durées, prix et conditions de réservation.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="200">
                    <span class="bubble">3</span>
                    <div>
                        <h3>Activez le bouton “Réserver”</h3>
                        <p>Connectez votre agenda. Les confirmations et rappels email s’enclenchent automatiquement.</p>
                    </div>
                </div>
                <div class="step" data-aos="fade-right" data-aos-delay="300">
                    <span class="bubble">4</span>
                    <div>
                        <h3>Partagez le lien & le QR code</h3>
                        <p>Ajoutez-le à Instagram, Google Business, WhatsApp, votre signature email et vos supports imprimés.</p>
                    </div>
                </div>
            </div>

            <div class="center mt-12" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">Créer mon Portail Pro</a>
            </div>
        </div>
    </section>

    <!-- STRIP SEO LOCAL -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto px-4 text-center">
            <h2 class="section-title" data-aos="fade-up">Optimisé pour la recherche locale</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Balises méta propres, titres clairs, zones desservies, et données structurées <strong>LocalBusiness</strong> pour mieux ressortir sur Google.
            </p>
            <div class="logo-row mt-8" data-aos="fade-up" data-aos-delay="150">
                <img src="{{ asset('images/seo/google.svg') }}" alt="Google" />
                <img src="{{ asset('images/seo/maps.svg') }}" alt="Google Maps" />
                <img src="{{ asset('images/seo/og.svg') }}" alt="OpenGraph" />
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Une vitrine qui convertit</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
                <div class="testimonial-card" data-aos="fade-up">
                    <p class="quote">« Mon Portail Pro me sert de mini-site. Les clients réservent directement et tout arrive dans mon agenda. »</p>
                    <h4 class="author">— Maëlle, Praticienne bien-être</h4>
                </div>
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                    <p class="quote">« J’ai ajouté le lien à mon Google Business et en bio Instagram. Les demandes de rendez-vous ont augmenté. »</p>
                    <h4 class="author">— Hugo, Naturopathe</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ (inchangé) -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="section-title text-center" data-aos="fade-up">Questions fréquentes — Portail Pro</h2>
            <div class="accordion mt-8 max-w-4xl mx-auto">
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Puis-je personnaliser l’apparence de mon Portail ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Oui : photo de couverture, couleurs d’accent, sections visibles, liens sociaux et ordre des services.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Où trouver mon QR code ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Depuis votre tableau de bord, bouton <strong>“Générer le QR code”</strong> qui pointe vers <code>aromamade.com/pro/{{ '{slug}' }}</code>.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Comment relier mon Portail à Google Business Profile ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Ajoutez le <strong>lien de votre Portail</strong> dans la section “Site web” de votre fiche Google et dans vos posts. Pensez à renseigner vos zones et horaires.</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>Puis-je intégrer un bouton “Réserver” sur mon site actuel ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-content">
                        <p>Nous recommandons de rediriger vers votre Portail Pro pour une expérience optimale et centralisée.</p>
                    </div>
                </div>
            </div>

            <div class="center mt-12" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">Activer mon Portail</a>
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
            <h2 class="section-title" data-aos="fade-up">Soyez trouvable et réservable en quelques minutes</h2>
            <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Partagez un lien pro, ajoutez un QR code à vos supports et facilitez la prise de rendez-vous.
            </p>
            <div class="mt-8" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register-pro') }}" class="btn-primary">Créer mon Portail Pro</a>
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
