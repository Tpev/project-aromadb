<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    @endpush

    @section('title', 'Logiciel naturopathe – Agenda, dossiers clients & facturation | AromaMade PRO')
    @section('meta_description')
AromaMade PRO est un logiciel conçu pour les naturopathes indépendants : agenda et réservation en ligne, dossiers clients, questionnaires d’anamnèse, facturation simple et visibilité pour développer votre cabinet de naturopathie.
    @endsection

    <style>
        :root {
            --primary-color: #647a0b;
            --secondary-color: #854f38;
        }

        body { font-family: 'Roboto', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Poppins', sans-serif; }
        .text-primary { color: var(--primary-color); }
        .bg-primary { background-color: var(--primary-color); }
        .text-secondary { color: var(--secondary-color); }
        .bg-secondary { background-color: var(--secondary-color); }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1.075rem;
            font-weight: 800;
            transition: transform 0.3s, box-shadow 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.25);
        }

        .btn-secondary {
            background-color: transparent;
            color: white;
            padding: 12px 24px;
            border: 2px solid rgba(255,255,255,.92);
            border-radius: 8px;
            text-decoration: none;
            font-size: 1.075rem;
            font-weight: 800;
            transition: background-color 0.3s, color 0.3s, transform .3s, box-shadow .3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-secondary:hover {
            background-color: rgba(255,255,255,.95);
            color: #111827;
            transform: scale(1.03);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
        }

        .hero {
            background-size: cover;
            background-position: center;
            position: relative;
            min-height: 80vh;
            display: flex;
            align-items: center;
        }
        .hero .overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.58);
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(8px);
            font-weight: 800;
            letter-spacing: .2px;
        }

        .feature-card {
            transition: box-shadow 0.3s, transform 0.3s;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
        }
        .feature-card:hover {
            box-shadow: 0 14px 26px rgba(0, 0, 0, 0.10);
            transform: translateY(-10px);
        }

        .soft-panel {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
        }

        .pill {
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:8px 12px;
            border-radius:999px;
            background: rgba(100,122,11,.08);
            border: 1px solid rgba(100,122,11,.18);
            color: var(--primary-color);
            font-weight: 800;
            font-size: .95rem;
            line-height: 1;
        }

        .wave-container { position: relative; overflow: hidden; line-height: 0; }
        .wave-container svg { position: relative; display: block; width: calc(100% + 1.3px); height: 100px; }

        .accordion .accordion-item { border-bottom: 1px solid #e2e8f0; padding: 14px 0; }
        .accordion .accordion-header {
            display: flex; justify-content: space-between; align-items: center;
            cursor: pointer; font-size: 1.1rem; font-weight: 800; color: var(--primary-color);
            gap: 16px;
        }
        .accordion .accordion-content { display: none; padding-top: 12px; color: #4a5568; line-height: 1.75; }
        .accordion .accordion-item.active .accordion-content { display: block; }

        .seo-text p { color:#4b5563; line-height: 1.85; font-size: 1.06rem; }
        .seo-text ul { margin-top: 10px; color:#4b5563; line-height: 1.85; font-size: 1.05rem; }
        .seo-text li { margin: 6px 0; }
        .seo-text h3 { margin-top: 18px; }

        @media (max-width: 768px) {
            .hero { min-height: 74vh; }
        }
    </style>

    {{-- HERO --}}
    <section class="hero relative" style="background-image:url('{{ asset('images/features-hero.webp') }}')">
        <div class="overlay"></div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="max-w-4xl">
                <div class="mb-5 text-white opacity-90" data-aos="fade-down">
                    <a href="/" class="text-white hover:underline">Accueil</a>
                    <span class="mx-2">›</span>
                    <span>Métiers</span>
                    <span class="mx-2">›</span>
                    <span class="font-semibold">Naturopathe</span>
                </div>

                <div class="hero-badge text-white mb-6" data-aos="fade-up">
                    <i class="fas fa-leaf"></i>
                    <span>Logiciel naturopathe : agenda, dossiers & facturation</span>
                </div>

                <h1 class="text-white text-4xl md:text-6xl font-bold leading-tight mb-5" data-aos="fade-up">
                    Le logiciel pour naturopathe<br class="hidden md:block">
                    <span style="text-shadow:0 10px 30px rgba(0,0,0,.35);">qui centralise votre cabinet</span>
                </h1>

                <p class="text-white text-lg md:text-xl opacity-90 leading-relaxed mb-8" data-aos="fade-up" data-aos-delay="100">
                    AromaMade PRO vous aide à gérer votre activité de naturopathe de manière simple et professionnelle :
                    <b>agenda</b>, <b>prise de rendez-vous en ligne</b>, <b>dossiers clients</b>, <b>questionnaires d’anamnèse</b> et <b>facturation</b>.
                    Un seul outil, pensé pour votre quotidien.
                </p>

                <div class="flex flex-wrap gap-4" data-aos="fade-up" data-aos-delay="150">
                    <a href="{{ route('register-pro') }}" class="btn-primary">
                        Essai gratuit 14 jours <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="/fonctionnalites" class="btn-secondary">
                        Voir les fonctionnalités <i class="fas fa-th-large"></i>
                    </a>
                </div>

                <div class="mt-7 flex flex-wrap gap-3" data-aos="fade-up" data-aos-delay="250">
                    <span class="pill"><i class="fas fa-check"></i> Sans engagement</span>
                    <span class="pill"><i class="fas fa-check"></i> RDV en ligne inclus</span>
                    <span class="pill"><i class="fas fa-check"></i> Cabinet · Visio · Domicile</span>
                    <span class="pill"><i class="fas fa-check"></i> Devis & factures micro</span>
                </div>


                <p class="text-white opacity-80 mt-6 text-sm" data-aos="fade-up" data-aos-delay="350">
                    Vous êtes naturopathe, praticien bien-être ou en cours d’installation ? AromaMade PRO est fait pour une pratique moderne, simple et structurée.
                </p>
            </div>
        </div>
    </section>

    {{-- Wave --}}
    <div class="wave-container">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"
                  fill="#ffffff"></path>
        </svg>
    </div>

    {{-- SECTION: Pourquoi un logiciel naturopathe --}}
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-5 text-primary" data-aos="fade-up">
                    Pourquoi choisir un logiciel dédié aux naturopathes ?
                </h2>
                <p class="text-lg text-gray-600 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
                    Un agenda seul ne suffit pas quand vous gérez des consultations longues, un suivi personnalisé,
                    des documents, des questionnaires et une facturation régulière. Un <b>logiciel naturopathe</b> doit
                    centraliser le cabinet, réduire les oublis et vous faire gagner du temps.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="feature-card p-7" data-aos="fade-up">
                    <div class="text-3xl text-primary mb-4"><i class="fas fa-layer-group"></i></div>
                    <h3 class="text-xl font-bold mb-2">Tout au même endroit</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Dossier client, anamnèse, notes, documents, factures : fini les infos dispersées.
                    </p>
                </div>

                <div class="feature-card p-7" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-3xl text-primary mb-4"><i class="fas fa-check-circle"></i></div>
                    <h3 class="text-xl font-bold mb-2">Un cadre plus pro</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Une organisation claire améliore l’expérience client et renforce la confiance.
                    </p>
                </div>

                <div class="feature-card p-7" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-3xl text-primary mb-4"><i class="fas fa-magic"></i></div>
                    <h3 class="text-xl font-bold mb-2">Moins de charge mentale</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Rappels automatiques, préparation de séance, documents structurés : vous respirez.
                    </p>
                </div>
            </div>

            <div class="mt-12 text-center" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">
                    Tester AromaMade PRO <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- SECTION: Fonctionnalités clés --}}
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-3xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold mb-5 text-primary" data-aos="fade-up">
                    Les fonctionnalités essentielles d’un logiciel pour naturopathe
                </h2>
                <p class="text-lg text-gray-600 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
                    Tout ce qu’il faut pour gérer votre cabinet au quotidien, sans complexité.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="/fonctionnalites/agenda" class="feature-card p-7 block" data-aos="fade-up">
                    <div class="flex items-start gap-4">
                        <div class="text-3xl text-primary"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Agenda & prise de rendez-vous en ligne</h3>
                            <p class="text-gray-600 leading-relaxed mb-3">
                                Vos clients réservent 24/7. Vous gardez le contrôle sur vos créneaux, durées et types de consultations.
                                Les rappels automatiques réduisent les oublis.
                            </p>
                            <span class="text-primary font-semibold">Découvrir →</span>
                        </div>
                    </div>
                </a>

                <a href="/fonctionnalites/dossiers-clients" class="feature-card p-7 block" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-start gap-4">
                        <div class="text-3xl text-primary"><i class="fas fa-user-friends"></i></div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Dossiers clients & suivi</h3>
                            <p class="text-gray-600 leading-relaxed mb-3">
                                Retrouver l’historique, les notes, les documents et les informations utiles en quelques secondes.
                                Un suivi plus cohérent d’une séance à l’autre.
                            </p>
                            <span class="text-primary font-semibold">Découvrir →</span>
                        </div>
                    </div>
                </a>

                <a href="/fonctionnalites/questionnaires" class="feature-card p-7 block" data-aos="fade-up">
                    <div class="flex items-start gap-4">
                        <div class="text-3xl text-primary"><i class="fas fa-clipboard-list"></i></div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Questionnaires d’anamnèse</h3>
                            <p class="text-gray-600 leading-relaxed mb-3">
                                Envoyez des questionnaires avant la consultation (anamnèse, habitudes, objectif, suivi).
                                Les réponses sont automatiquement attachées au dossier client.
                            </p>
                            <span class="text-primary font-semibold">Découvrir →</span>
                        </div>
                    </div>
                </a>

                <a href="/fonctionnalites/facturation" class="feature-card p-7 block" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-start gap-4">
                        <div class="text-3xl text-primary"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Facturation pour micro-entreprise</h3>
                            <p class="text-gray-600 leading-relaxed mb-3">
                                Créez devis et factures en quelques clics, avec une présentation professionnelle.
                                Parfait si vous voulez arrêter les tableaux Excel.
                            </p>
                            <span class="text-primary font-semibold">Découvrir →</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="mt-12 text-center" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">
                    Démarrer l’essai gratuit <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- SECTION: SEO texte long (vraie valeur + mots-clés) --}}
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="max-w-5xl mx-auto seo-text">
                <div class="text-center mb-10">
                    <h2 class="text-3xl md:text-4xl font-bold text-primary" data-aos="fade-up">
                        AromaMade PRO : un logiciel de naturopathie pensé pour le terrain
                    </h2>
                    <p class="text-lg text-gray-600 leading-relaxed mt-4" data-aos="fade-up" data-aos-delay="100">
                        Que vous exerciez en cabinet, en visio, à domicile, ou en mixte, un bon logiciel doit vous aider à rester
                        organisé sans alourdir vos journées. AromaMade PRO est conçu pour structurer votre pratique et vous faire gagner du temps.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10" data-aos="fade-up">
                    <div class="feature-card p-8">
                        <h3 class="text-2xl font-bold text-primary">Gérer votre cabinet de naturopathie au quotidien</h3>
                        <p class="mt-3">
                            La naturopathie implique souvent un suivi dans la durée : bilans, objectifs, routines, ajustements.
                            AromaMade PRO vous permet de regrouper l’ensemble des informations au même endroit :
                            dossier client, notes, documents, réponses aux questionnaires et historique des rendez-vous.
                        </p>
                        <ul class="mt-4">
                            <li><i class="fas fa-check text-primary mr-2"></i> Retrouver un client en quelques secondes</li>
                            <li><i class="fas fa-check text-primary mr-2"></i> Structurer l’anamnèse et le suivi</li>
                            <li><i class="fas fa-check text-primary mr-2"></i> Garder une trace claire des consultations</li>
                        </ul>
                    </div>

                    <div class="feature-card p-8">
                        <h3 class="text-2xl font-bold text-primary">Réservation en ligne + organisation = plus de sérénité</h3>
                        <p class="mt-3">
                            La prise de rendez-vous en ligne évite les messages à rallonge et les allers-retours.
                            Vous définissez vos disponibilités, vos durées, et vos modalités de consultation
                            (cabinet, visio, domicile). Les rappels automatiques diminuent les oublis.
                        </p>
                        <ul class="mt-4">
                            <li><i class="fas fa-check text-primary mr-2"></i> RDV 24/7, sans friction</li>
                            <li><i class="fas fa-check text-primary mr-2"></i> Rappels automatiques</li>
                            <li><i class="fas fa-check text-primary mr-2"></i> Moins d’absences / no-show</li>
                        </ul>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mt-10" data-aos="fade-up">
                    <div class="feature-card p-8">
                        <h3 class="text-2xl font-bold text-primary">Facturation naturopathe : devis & factures plus simples</h3>
                        <p class="mt-3">
                            Si vous êtes naturopathe en micro-entreprise, vous avez besoin d’une facturation simple,
                            claire, et présentable. AromaMade PRO vous aide à produire des devis et factures rapidement,
                            et à garder un suivi propre de votre activité.
                        </p>
                        <ul class="mt-4">
                            <li><i class="fas fa-check text-primary mr-2"></i> Documents professionnels</li>
                            <li><i class="fas fa-check text-primary mr-2"></i> Suivi plus clair client par client</li>
                            <li><i class="fas fa-check text-primary mr-2"></i> Finis les fichiers dispersés</li>
                        </ul>
                    </div>

                    <div class="feature-card p-8">
                        <h3 class="text-2xl font-bold text-primary">Questionnaires d’anamnèse : mieux préparer vos séances</h3>
                        <p class="mt-3">
                            Les questionnaires d’anamnèse (ou de suivi) permettent de mieux comprendre le contexte de votre client
                            avant la consultation. Vous pouvez envoyer un lien, récupérer les réponses automatiquement
                            et les rattacher au dossier.
                        </p>
                        <ul class="mt-4">
                            <li><i class="fas fa-check text-primary mr-2"></i> Préparation plus efficace</li>
                            <li><i class="fas fa-check text-primary mr-2"></i> Réponses centralisées</li>
                            <li><i class="fas fa-check text-primary mr-2"></i> Suivi avant / après séance</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 text-center" data-aos="fade-up">
                    <p class="text-gray-600 mb-6">
                        Vous cherchez un <b>logiciel de naturopathie</b> pour gagner du temps et professionnaliser votre cabinet ?
                        Testez AromaMade PRO gratuitement.
                    </p>
                    <a href="{{ route('register-pro') }}" class="btn-primary">
                        Créer mon compte <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION: Comment ça marche (SEO + conversion) --}}
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-3xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold mb-5 text-primary" data-aos="fade-up">
                    Comment ça marche ?
                </h2>
                <p class="text-lg text-gray-600 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
                    Une mise en place simple pour que vous puissiez commencer rapidement.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="feature-card p-7" data-aos="fade-up">
                    <div class="text-3xl text-primary mb-4"><i class="fas fa-user-plus"></i></div>
                    <h3 class="text-xl font-bold mb-2">1) Créez votre compte</h3>
                    <p class="text-gray-600 leading-relaxed">
                        En quelques minutes, vous accédez à l’espace PRO et à votre tableau de bord.
                    </p>
                </div>

                <div class="feature-card p-7" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-3xl text-primary mb-4"><i class="fas fa-calendar-check"></i></div>
                    <h3 class="text-xl font-bold mb-2">2) Configurez vos RDV</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Durées, modalités (cabinet/visio/domicile), disponibilités et page de réservation.
                    </p>
                </div>

                <div class="feature-card p-7" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-3xl text-primary mb-4"><i class="fas fa-rocket"></i></div>
                    <h3 class="text-xl font-bold mb-2">3) Gérez tout au même endroit</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Dossiers clients, questionnaires, notes, documents, devis et factures.
                    </p>
                </div>
            </div>

            <div class="mt-12 text-center" data-aos="fade-up">
                <a href="{{ route('register-pro') }}" class="btn-primary">
                    Je démarre maintenant <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-3xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold mb-5 text-primary" data-aos="fade-up">
                    FAQ – Logiciel naturopathe
                </h2>
                <p class="text-lg text-gray-600 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
                    Questions fréquentes avant de choisir un logiciel pour gérer une activité de naturopathie.
                </p>
            </div>

            <div class="accordion max-w-3xl mx-auto mt-10" data-aos="fade-up">
                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Quel logiciel choisir pour une naturopathe en micro-entreprise ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        L’idéal est d’avoir un outil qui centralise agenda, dossiers clients, questionnaires et facturation,
                        sans complexité. AromaMade PRO a été conçu pour un usage quotidien, avec des documents pro et une gestion simple.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Est-ce que AromaMade PRO convient aux consultations en visio ou à domicile ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        Oui. Vous pouvez organiser votre activité selon vos modalités : cabinet, visio, domicile, ou mixte,
                        tout en gardant une gestion centralisée.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>En quoi la prise de rendez-vous en ligne change vraiment la donne ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        Elle réduit les allers-retours par messages, fluidifie l’expérience client et évite les oublis grâce aux rappels automatiques.
                        Vous gagnez du temps et vos clients réservent quand ils veulent.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Peut-on envoyer un questionnaire d’anamnèse avant la consultation ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        Oui. Vous pouvez envoyer un lien de questionnaire avant ou après la séance.
                        Les réponses sont automatiquement rattachées au dossier client.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Y a-t-il un essai gratuit du logiciel naturopathe ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        Oui. Vous pouvez tester AromaMade PRO gratuitement pendant 14 jours, sans engagement.
                    </div>
                </div>
            </div>
        </div>

        {{-- FAQ Schema (SEO) --}}
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "FAQPage",
          "mainEntity": [
            {
              "@type": "Question",
              "name": "Quel logiciel choisir pour une naturopathe en micro-entreprise ?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "L’idéal est un outil qui centralise agenda, dossiers clients, questionnaires et facturation. AromaMade PRO a été conçu pour un usage quotidien simple et professionnel."
              }
            },
            {
              "@type": "Question",
              "name": "Est-ce que AromaMade PRO convient aux consultations en visio ou à domicile ?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Oui. Vous pouvez organiser votre activité en cabinet, en visio, à domicile, ou en mixte tout en gardant une gestion centralisée."
              }
            },
            {
              "@type": "Question",
              "name": "En quoi la prise de rendez-vous en ligne est utile pour une naturopathe ?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Elle réduit les allers-retours, fluidifie l’expérience client et limite les oublis grâce aux rappels automatiques. Vous gagnez du temps et vos clients réservent 24/7."
              }
            },
            {
              "@type": "Question",
              "name": "Peut-on envoyer un questionnaire d’anamnèse avant la consultation ?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Oui. Vous pouvez envoyer un lien de questionnaire avant ou après la séance, et les réponses sont rattachées au dossier client."
              }
            },
            {
              "@type": "Question",
              "name": "Y a-t-il un essai gratuit du logiciel naturopathe ?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Oui, AromaMade PRO propose un essai gratuit de 14 jours, sans engagement."
              }
            }
          ]
        }
        </script>
    </section>

    {{-- CTA FINAL --}}
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto text-center feature-card p-10" data-aos="zoom-in">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-primary">
                    Essayez le logiciel naturopathe AromaMade PRO
                </h2>
                <p class="text-lg text-gray-600 leading-relaxed mb-8">
                    Centralisez votre agenda, votre prise de rendez-vous en ligne, vos dossiers clients,
                    vos questionnaires d’anamnèse et votre facturation. Testez gratuitement pendant 14 jours.
                </p>
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="{{ route('register-pro') }}" class="btn-primary">
                        Créer mon compte <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="/fonctionnalites" class="btn-secondary" style="border-color: var(--primary-color); color: var(--primary-color);">
                        Voir le détail <i class="fas fa-th-large"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.AOS) AOS.init({ duration: 800, once: true });

                const items = document.querySelectorAll('.accordion .accordion-item');
                items.forEach(item => {
                    const header = item.querySelector('.accordion-header');
                    header.addEventListener('click', () => {
                        const isActive = item.classList.contains('active');
                        items.forEach(i => i.classList.remove('active'));
                        if (!isActive) item.classList.add('active');
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
