<x-app-layout>
    @section('title', 'Agenda praticien | Prise de rendez-vous en ligne | AromaMade PRO')
    @section('meta_description')
Simplifiez votre agenda de praticien avec AromaMade PRO : gestion intelligente des disponibilités, réservations en ligne 24h/24, rappels automatiques par email et synchronisation avec vos calendriers Google, Apple ou Outlook.
    @endsection

  @push('styles')
    <!-- AOS Animation -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <!-- Icons & Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto&display=swap" rel="stylesheet">
    <!-- Custom Feature CSS -->
    <link rel="stylesheet" href="{{ asset('css/feature-agenda.css') }}">
@endpush

<!-- HERO -->
<section class="hero hero--agenda relative">
    <div class="hero-bg absolute w-full h-full bg-center bg-cover" style="background-image:url('{{ asset('images/agenda-hero.webp') }}');">
        <div class="overlay absolute inset-0 bg-gradient-to-b from-black via-transparent to-black opacity-60"></div>
    </div>
    <div class="container mx-auto text-center relative z-10 py-24 px-4">
        <nav class="breadcrumb" aria-label="breadcrumb">
            <a href="{{ url('/') }}">Accueil</a> <span>›</span>
            <a href="{{ url('/fonctionnalites') }}">Fonctionnalités</a> <span>›</span>
            <span class="current">Agenda & prise de rendez-vous</span>
        </nav>
        <h1 class="text-white text-5xl md:text-6xl font-bold mb-6" data-aos="fade-up">
            Agenda praticien et prise de rendez-vous en ligne : planification simplifiée et automatisée
        </h1>
        <p class="text-white text-xl md:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
            Centralisez vos rendez-vous, laissez vos clients réserver en ligne, recevez des rappels automatiques et gardez une vision claire de votre planning.
        </p>
        <div class="cta-group" data-aos="fade-up" data-aos-delay="200">
            <a href="{{ route('register-pro') }}" class="btn-primary">Essayer gratuitement</a>
            <a href="{{ url('/pro') }}" class="btn-secondary">Découvrir AromaMade PRO</a>
        </div>
    </div>
    <div class="overlay absolute inset-0 bg-black opacity-50"></div>
</section>

<!-- 3 BENEFITS -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="section-title text-center" data-aos="fade-up">Un agenda conçu pour les praticiens du bien-être</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
            <div class="card" data-aos="fade-up">
                <i class="fas fa-clock card-icon"></i>
                <h3 class="card-title">Réservations en ligne 24h/24</h3>
                <p>Vos clients peuvent prendre rendez-vous à tout moment via votre <strong>Portail Pro</strong>. Vous gardez le contrôle de vos disponibilités et validez chaque demande en un clic.</p>
            </div>
            <div class="card" data-aos="fade-up" data-aos-delay="100">
                <i class="fas fa-envelope card-icon"></i>
                <h3 class="card-title">Rappels automatiques par email</h3>
                <p>Évitez les oublis grâce à deux rappels automatiques envoyés à vos clients : <strong>un à 24h</strong> et <strong>un à 1h</strong> avant chaque rendez-vous.</p>
            </div>
            <div class="card" data-aos="fade-up" data-aos-delay="200">
                <i class="fas fa-sync card-icon"></i>
                <h3 class="card-title">Synchronisation instantanée</h3>
                <p>Connectez facilement votre agenda <strong>Google, Apple ou Outlook</strong> pour éviter tout chevauchement entre vos rendez-vous personnels et professionnels.</p>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES GRID -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="section-title text-center" data-aos="fade-up">Une organisation fluide et sans stress</h2>

        <div class="max-w-5xl mx-auto mt-8" data-aos="fade-up" data-aos-delay="50">
            <h3 class="text-xl md:text-2xl font-semibold mb-3" style="color:#1f2937;">Sous-fonctionnalités de l’agenda</h3>
            <p class="muted" style="color:#6b7280;line-height:1.7;">
                Cette page centralise la fonctionnalité <strong>Agenda & prise de rendez-vous</strong>. Les sections ci-dessous décrivent les sous-fonctionnalités déjà disponibles,
                afin que Google (et vos futurs guides d’aide) puissent référencer clairement chaque élément : disponibilités, créneaux, modes de séance, événements, rappels et synchronisation.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-10">
            <div class="feature-tile" data-aos="fade-up">
                <i class="fas fa-calendar-check tile-icon"></i>
                <h3>Disponibilités et créneaux personnalisés</h3>
                <p>Définissez la durée de chaque service, insérez des <strong>temps de pause</strong> entre deux rendez-vous et ajustez vos horaires selon vos besoins.</p>
            </div>

            <div class="feature-tile" data-aos="fade-up" data-aos-delay="50">
                <i class="fas fa-map-marker-alt tile-icon"></i>
                <h3>Modes de séance et multi-lieux</h3>
                <p>Gérez vos séances au cabinet, à domicile ou en visio — chaque service peut avoir ses propres modalités et adresses.</p>
            </div>

            <!-- UPDATED: Portail Pro (replaces "Lien de réservation unique") -->
            <div class="feature-tile" data-aos="fade-up" data-aos-delay="100">
                <i class="fas fa-id-badge tile-icon"></i>
                <h3>Portail Pro de réservation en ligne</h3>
                <p>Un <strong>lien unique</strong> vers votre profil AromaMade PRO. Vos clients réservent directement depuis votre Portail Pro — simple et efficace.</p>
            </div>

            <!-- UPDATED: Ateliers & événements (replaces recurrent/shared) -->
            <div class="feature-tile" data-aos="fade-up" data-aos-delay="150">
                <i class="fas fa-users tile-icon"></i>
                <h3>Ateliers, stages et événements</h3>
                <p>Créez des séances spéciales : <strong>ateliers, stages, événements</strong>… avec des dates dédiées et une description claire.</p>
            </div>

            <div class="feature-tile" data-aos="fade-up" data-aos-delay="200">
                <i class="fas fa-envelope-open-text tile-icon"></i>
                <h3>Confirmations et rappels automatiques</h3>
                <p>Vos clients reçoivent une confirmation dès qu’un rendez-vous est validé, ainsi qu’un lien direct pour le modifier ou l’annuler.</p>
            </div>

            <!-- REMOVED: Gestion des annulations -->

            <div class="feature-tile" data-aos="fade-up" data-aos-delay="300">
                <i class="fas fa-calendar-day tile-icon"></i>
                <h3>Horaires types, exceptions et indisponibilités</h3>
                <p>Enregistrez vos semaines types, créez des modèles d’horaires et définissez facilement les jours de fermeture ou de congés.</p>
            </div>

            <div class="feature-tile" data-aos="fade-up" data-aos-delay="350">
                <i class="fas fa-exchange-alt tile-icon"></i>
                <h3>Replanification simplifiée</h3>
                <p>Modifiez un rendez-vous par simple glisser-déposer. Le client reçoit immédiatement la mise à jour par email.</p>
            </div>

            <div class="feature-tile" data-aos="fade-up" data-aos-delay="400">
                <i class="fas fa-shield-alt tile-icon"></i>
                <h3>Protection des données</h3>
                <p>Toutes les données de vos clients sont hébergées en France, sur des serveurs certifiés <strong>HDS</strong> (Hébergement de Données de Santé).</p>
            </div>
        </div>

        <div class="max-w-5xl mx-auto mt-10" data-aos="fade-up" data-aos-delay="430">
            <h3 class="text-xl md:text-2xl font-semibold mb-3" style="color:#1f2937;">Ce que couvre l’agenda au quotidien</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
                <div>
                    <h4 class="font-semibold mb-2" style="color:#1f2937;">Disponibilités et indisponibilités</h4>
                    <p class="muted" style="color:#6b7280;line-height:1.7;">
                        Vous définissez vos plages de travail (horaires types) et vous bloquez vos congés ou fermetures (exceptions).
                        L’agenda reste cohérent même lorsque votre rythme varie (périodes chargées, semaines allégées, déplacements).
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold mb-2" style="color:#1f2937;">Prestations et durées de rendez-vous</h4>
                    <p class="muted" style="color:#6b7280;line-height:1.7;">
                        Chaque service peut avoir une durée dédiée, des pauses entre deux séances et un mode de réalisation (cabinet, domicile, visio).
                        Vous gardez une grille de rendez-vous réaliste, alignée sur votre pratique.
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold mb-2" style="color:#1f2937;">Réservation en ligne via Portail Pro</h4>
                    <p class="muted" style="color:#6b7280;line-height:1.7;">
                        Le Portail Pro sert de point d’entrée unique. Les clients demandent un créneau, et vous validez en un clic (ou laissez en automatique si vous le souhaitez).
                        Résultat : moins d’allers-retours, une prise de rendez-vous claire et traçable.
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold mb-2" style="color:#1f2937;">Ateliers et événements</h4>
                    <p class="muted" style="color:#6b7280;line-height:1.7;">
                        L’agenda ne sert pas uniquement aux consultations. Vous pouvez créer des ateliers, stages ou événements avec des dates dédiées
                        et une description, pour organiser vos formats de groupe de façon structurée.
                    </p>
                </div>
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

        <div class="max-w-5xl mx-auto mt-6" data-aos="fade-up" data-aos-delay="80">
            <h3 class="text-xl md:text-2xl font-semibold mb-3" style="color:#1f2937;">Workflow de prise de rendez-vous pour un cabinet</h3>
            <p class="muted" style="color:#6b7280;line-height:1.7;">
                L’agenda AromaMade PRO suit un flux simple : vous paramétrez vos services et vos horaires, vous activez la réservation en ligne via le Portail Pro,
                puis l’outil gère les confirmations, rappels et la synchronisation avec vos calendriers externes.
                L’objectif est de réduire la coordination manuelle tout en gardant le contrôle (validation manuelle ou automatique).
            </p>
        </div>

        <div class="steps mt-10">
            <div class="step" data-aos="fade-right">
                <span class="bubble">1</span>
                <div>
                    <h3>Définissez vos disponibilités</h3>
                    <p>Créez vos horaires types, précisez vos modes de séance (cabinet, visio, domicile) et vos périodes d’absence.</p>
                </div>
            </div>
            <div class="step" data-aos="fade-right" data-aos-delay="100">
                <span class="bubble">2</span>
                <div>
                    <h3>Activez la réservation en ligne</h3>
                    <p>Partagez le lien de votre <strong>Portail Pro</strong> pour permettre la prise de rendez-vous en toute autonomie.</p>
                </div>
            </div>
            <div class="step" data-aos="fade-right" data-aos-delay="200">
                <span class="bubble">3</span>
                <div>
                    <h3>Recevez des rappels automatiques</h3>
                    <p>Vos clients reçoivent automatiquement un rappel par email <strong>24h avant</strong> puis <strong>1h avant</strong> leur séance.</p>
                </div>
            </div>
            <div class="step" data-aos="fade-right" data-aos-delay="300">
                <span class="bubble">4</span>
                <div>
                    <h3>Synchronisez vos calendriers</h3>
                    <p>Connectez votre agenda Google, Apple ou Outlook pour centraliser toute votre organisation.</p>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto mt-10" data-aos="fade-up" data-aos-delay="120">
            <h3 class="text-xl md:text-2xl font-semibold mb-3" style="color:#1f2937;">Cas d’usage thérapeute</h3>
            <ul style="margin:0;padding-left:18px;color:#6b7280;line-height:1.8;">
                <li>Un nouveau client réserve un créneau via votre Portail Pro : vous validez et la confirmation part automatiquement.</li>
                <li>Vous partez en congés : vous bloquez une période d’indisponibilité, le planning se met à jour sans risque de réservation.</li>
                <li>Vous alternez cabinet, domicile et visio : chaque prestation conserve ses modalités et l’agenda reste lisible.</li>
                <li>Vous lancez un atelier : vous créez un événement dédié pour éviter de mélanger consultations et formats de groupe.</li>
            </ul>
        </div>

        <div class="center mt-12" data-aos="fade-up">
            <a href="{{ route('register-pro') }}" class="btn-primary">Essayer maintenant</a>
        </div>
    </div>
</section>

<!-- INTEGRATIONS -->
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4 text-center">
        <h2 class="section-title" data-aos="fade-up">Compatible avec vos outils préférés</h2>
        <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
            Synchronisation en temps réel avec Google Calendar, Apple iCloud et Outlook pour une gestion sans doublons.
        </p>
        <div class="logo-row mt-8" data-aos="fade-up" data-aos-delay="150">
            <img src="{{ asset('images/integrations/google.svg') }}" alt="Google" />
            <img src="{{ asset('images/integrations/apple.svg') }}" alt="Apple" />
            <img src="{{ asset('images/integrations/outlook.svg') }}" alt="Outlook" />
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="section-title text-center" data-aos="fade-up">Ils ont simplifié leur agenda avec AromaMade</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
            <div class="testimonial-card" data-aos="fade-up">
                <p class="quote">« Depuis que mes clients réservent en ligne, j’ai beaucoup moins d’échanges pour fixer les rendez-vous. Mon planning est toujours clair et à jour. »</p>
                <h4 class="author">— Claire, Sophrologue</h4>
            </div>
            <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                <p class="quote">« L’agenda connecté d’AromaMade est un vrai confort. Les rappels automatiques évitent les oublis et tout reste synchronisé avec mon calendrier Google. »</p>
                <h4 class="author">— Jérôme, Naturopathe</h4>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="section-title text-center" data-aos="fade-up">Questions fréquentes sur l’agenda</h2>
        <div class="accordion mt-8 max-w-4xl mx-auto">
            <div class="accordion-item">
                <button class="accordion-header">
                    <span>Puis-je valider manuellement les réservations ?</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="accordion-content">
                    <p>Oui. Vous pouvez choisir entre validation automatique ou manuelle de chaque rendez-vous depuis votre tableau de bord.</p>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header">
                    <span>Les rappels sont-ils automatiques ?</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="accordion-content">
                    <p>Oui. Vos clients reçoivent un email de rappel <strong>24h</strong> puis <strong>1h avant</strong> leur rendez-vous, sans que vous ayez à intervenir.</p>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header">
                    <span>Puis-je bloquer des créneaux pour mes congés ?</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="accordion-content">
                    <p>Bien sûr. Vous pouvez marquer des périodes d’indisponibilité directement depuis votre agenda.</p>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header">
                    <span>Est-ce compatible avec mon smartphone ?</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="accordion-content">
                    <p>Oui. L’agenda est responsive et fonctionne sur ordinateur, tablette et téléphone.</p>
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

<!-- POUR QUELS MÉTIERS -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="section-title text-center" data-aos="fade-up">Pour quels métiers ?</h2>
        <p class="muted max-w-3xl mx-auto text-center" data-aos="fade-up" data-aos-delay="100">
            Cet agenda s’adresse aux praticiens qui gèrent des consultations, des suivis et parfois des ateliers. Les liens ci-dessous sont prêts à être connectés à des pages métiers.
        </p>

        <div class="max-w-4xl mx-auto mt-10" data-aos="fade-up" data-aos-delay="150">
            <ul style="
                list-style:none;
                padding:0;
                margin:0;
                display:flex;
                flex-wrap:wrap;
                gap:12px;
                justify-content:center;
            ">
                <li>
                    <a href="#" style="
                        display:inline-flex;
                        align-items:center;
                        gap:8px;
                        padding:10px 16px;
                        border-radius:999px;
                        background:#f6f8f5;
                        border:1px solid #e5e7eb;
                        color:#1f2937;
                        font-weight:700;
                        font-size:.95rem;
                        text-decoration:none;
                    ">
                        Naturopathe
                    </a>
                </li>
                <li>
                    <a href="#" style="
                        display:inline-flex;
                        align-items:center;
                        gap:8px;
                        padding:10px 16px;
                        border-radius:999px;
                        background:#f6f8f5;
                        border:1px solid #e5e7eb;
                        color:#1f2937;
                        font-weight:700;
                        font-size:.95rem;
                        text-decoration:none;
                    ">
                        Sophrologue
                    </a>
                </li>
                <li>
                    <a href="#" style="
                        display:inline-flex;
                        align-items:center;
                        gap:8px;
                        padding:10px 16px;
                        border-radius:999px;
                        background:#f6f8f5;
                        border:1px solid #e5e7eb;
                        color:#1f2937;
                        font-weight:700;
                        font-size:.95rem;
                        text-decoration:none;
                    ">
                        Réflexologue
                    </a>
                </li>
                <li>
                    <a href="#" style="
                        display:inline-flex;
                        align-items:center;
                        gap:8px;
                        padding:10px 16px;
                        border-radius:999px;
                        background:#f6f8f5;
                        border:1px solid #e5e7eb;
                        color:#1f2937;
                        font-weight:700;
                        font-size:.95rem;
                        text-decoration:none;
                    ">
                        Ostéopathe
                    </a>
                </li>
                <li>
                    <a href="#" style="
                        display:inline-flex;
                        align-items:center;
                        gap:8px;
                        padding:10px 16px;
                        border-radius:999px;
                        background:#f6f8f5;
                        border:1px solid #e5e7eb;
                        color:#1f2937;
                        font-weight:700;
                        font-size:.95rem;
                        text-decoration:none;
                    ">
                        Hypnothérapeute
                    </a>
                </li>
                <li>
                    <a href="#" style="
                        display:inline-flex;
                        align-items:center;
                        gap:8px;
                        padding:10px 16px;
                        border-radius:999px;
                        background:#f6f8f5;
                        border:1px solid #e5e7eb;
                        color:#1f2937;
                        font-weight:700;
                        font-size:.95rem;
                        text-decoration:none;
                    ">
                        Coach bien-être
                    </a>
                </li>
            </ul>
        </div>
    </div>
</section>

<!-- GUIDES PRATIQUES -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="section-title text-center" data-aos="fade-up">Guides pratiques</h2>
        <p class="muted max-w-3xl mx-auto text-center" data-aos="fade-up" data-aos-delay="100">
            Ces ressources expliquent pas à pas l’utilisation de l’agenda AromaMade PRO, afin de vous aider
            à organiser vos rendez-vous et permettre à Google de relier clairement l’aide à la fonctionnalité.
        </p>

        <div class="max-w-4xl mx-auto mt-10" data-aos="fade-up" data-aos-delay="150">
            <ul style="
                margin:0;
                padding-left:18px;
                color:#6b7280;
                line-height:1.9;
            ">
                <li>
                    <a href="{{ url('/aide/agenda/creer-un-rendez-vous-en-ligne') }}"
                       style="color:#647a0b;font-weight:700;text-decoration:none;">
                        Créer un rendez-vous en ligne depuis le Portail Pro
                    </a>
                </li>

                <li>
                    <a href="{{ url('/aide/agenda/configurer-disponibilites') }}"
                       style="color:#647a0b;font-weight:700;text-decoration:none;">
                        Configurer ses disponibilités et horaires types
                    </a>
                </li>

                <li>
                    <a href="{{ url('/aide/agenda/gerer-indisponibilites') }}"
                       style="color:#647a0b;font-weight:700;text-decoration:none;">
                        Gérer ses indisponibilités (congés, absences, fermetures)
                    </a>
                </li>

                <li>
                    <a href="{{ url('/aide/agenda/duree-prestation-temps-de-pause') }}"
                       style="color:#647a0b;font-weight:700;text-decoration:none;">
                        Définir la durée d’une prestation et ajouter un temps de pause
                    </a>
                </li>

                <li>
                    <a href="{{ url('/aide/agenda/creer-un-atelier-ou-evenement') }}"
                       style="color:#647a0b;font-weight:700;text-decoration:none;">
                        Créer un atelier ou un événement dans l’agenda
                    </a>
                </li>

                <li>
                    <a href="{{ url('/aide/agenda/synchroniser-calendrier') }}"
                       style="color:#647a0b;font-weight:700;text-decoration:none;">
                        Synchroniser Google Calendar, Apple iCloud ou Outlook
                    </a>
                </li>
            </ul>
        </div>
    </div>
</section>


<!-- CTA -->
<section class="py-16 bg-green-100">
    <div class="container mx-auto text-center px-4">
        <h2 class="section-title" data-aos="fade-up">Gérez votre agenda en toute sérénité</h2>
        <p class="muted max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
            Simplifiez votre organisation, gagnez du temps et offrez à vos clients une expérience fluide et professionnelle.
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
