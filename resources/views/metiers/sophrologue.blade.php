<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    @endpush

    @section('title', 'Logiciel sophrologue – Agenda, dossiers clients & facturation | AromaMade PRO')
    @section('meta_description')
AromaMade PRO est un logiciel conçu pour les sophrologues : agenda et réservation en ligne, dossiers clients, questionnaires de suivi, facturation simple et visibilité pour développer votre activité de sophrologie.
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
                    <span class="font-semibold">Sophrologue</span>
                </div>

                <div class="hero-badge text-white mb-6" data-aos="fade-up">
                    <i class="fas fa-spa"></i>
                    <span>Logiciel sophrologue : RDV, suivi & facturation</span>
                </div>

                <h1 class="text-white text-4xl md:text-6xl font-bold leading-tight mb-5" data-aos="fade-up">
                    Le logiciel pour sophrologue<br class="hidden md:block">
                    <span style="text-shadow:0 10px 30px rgba(0,0,0,.35);">qui structure votre pratique</span>
                </h1>

                <p class="text-white text-lg md:text-xl opacity-90 leading-relaxed mb-8" data-aos="fade-up" data-aos-delay="100">
                    AromaMade PRO vous aide à organiser votre activité de sophrologue de façon fluide et professionnelle :
                    <b>agenda</b>, <b>prise de rendez-vous en ligne</b>, <b>dossiers clients</b>, <b>questionnaires de suivi</b> et <b>facturation</b>.
                    Un seul outil pour gagner en clarté, en régularité et en sérénité.
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
                    Vous êtes sophrologue (séances individuelles, ateliers, accompagnement stress/émotions) ? AromaMade PRO vous aide à structurer votre quotidien et à gagner du temps.
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

    {{-- SECTION: Pourquoi un logiciel sophrologue --}}
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-5 text-primary" data-aos="fade-up">
                    Pourquoi choisir un logiciel dédié aux sophrologues ?
                </h2>
                <p class="text-lg text-gray-600 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
                    Entre la gestion des rendez-vous, les comptes-rendus de séance, le suivi d’objectifs et la facturation,
                    une organisation “au feeling” finit par coûter du temps et de l’énergie. Un <b>logiciel sophrologue</b>
                    vous aide à garder une méthode simple, à fluidifier la relation client et à sécuriser votre quotidien.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="feature-card p-7" data-aos="fade-up">
                    <div class="text-3xl text-primary mb-4"><i class="fas fa-stream"></i></div>
                    <h3 class="text-xl font-bold mb-2">Un suivi plus clair</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Objectifs, notes, documents et historique : vous retrouvez tout sans chercher.
                    </p>
                </div>

                <div class="feature-card p-7" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-3xl text-primary mb-4"><i class="fas fa-handshake"></i></div>
                    <h3 class="text-xl font-bold mb-2">Une expérience plus fluide</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Réservation en ligne, rappels et préparation : vos clients sont mieux accompagnés.
                    </p>
                </div>

                <div class="feature-card p-7" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-3xl text-primary mb-4"><i class="fas fa-feather-alt"></i></div>
                    <h3 class="text-xl font-bold mb-2">Moins de charge mentale</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Une structure simple pour vos séances et vos tâches admin : vous respirez.
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
                    Les fonctionnalités essentielles d’un logiciel pour sophrologue
                </h2>
                <p class="text-lg text-gray-600 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
                    Tout ce qu’il faut pour gérer votre activité au quotidien, sans vous compliquer la vie.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="/fonctionnalites/agenda" class="feature-card p-7 block" data-aos="fade-up">
                    <div class="flex items-start gap-4">
                        <div class="text-3xl text-primary"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Agenda & prise de rendez-vous en ligne</h3>
                            <p class="text-gray-600 leading-relaxed mb-3">
                                Vos clients réservent à tout moment. Vous définissez vos durées (séance, suivi, atelier),
                                vos modalités (cabinet, visio, domicile) et vos règles. Les rappels automatiques limitent les oublis.
                            </p>
                            <span class="text-primary font-semibold">Découvrir →</span>
                        </div>
                    </div>
                </a>

                <a href="/fonctionnalites/dossiers-clients" class="feature-card p-7 block" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-start gap-4">
                        <div class="text-3xl text-primary"><i class="fas fa-user-friends"></i></div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Dossiers clients & notes de séance</h3>
                            <p class="text-gray-600 leading-relaxed mb-3">
                                Centralisez les informations utiles (objectifs, historique, notes) pour retrouver rapidement
                                le fil d’un accompagnement et garder de la cohérence séance après séance.
                            </p>
                            <span class="text-primary font-semibold">Découvrir →</span>
                        </div>
                    </div>
                </a>

                <a href="/fonctionnalites/questionnaires" class="feature-card p-7 block" data-aos="fade-up">
                    <div class="flex items-start gap-4">
                        <div class="text-3xl text-primary"><i class="fas fa-clipboard-list"></i></div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Questionnaires de suivi</h3>
                            <p class="text-gray-600 leading-relaxed mb-3">
                                Avant ou après une séance, envoyez un questionnaire (état du moment, besoins, objectifs, progression).
                                Les réponses sont automatiquement rattachées au dossier.
                            </p>
                            <span class="text-primary font-semibold">Découvrir →</span>
                        </div>
                    </div>
                </a>

                <a href="/fonctionnalites/facturation" class="feature-card p-7 block" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-start gap-4">
                        <div class="text-3xl text-primary"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Facturation simple (micro-entreprise)</h3>
                            <p class="text-gray-600 leading-relaxed mb-3">
                                Devis et factures en quelques clics, avec une présentation professionnelle.
                                Idéal pour sortir d’Excel et garder un suivi clair, client par client.
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

    {{-- SECTION: SEO texte long --}}
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="max-w-5xl mx-auto seo-text">
                <div class="text-center mb-10">
                    <h2 class="text-3xl md:text-4xl font-bold text-primary" data-aos="fade-up">
                        AromaMade PRO : un logiciel de sophrologie pensé pour le quotidien
                    </h2>
                    <p class="text-lg text-gray-600 leading-relaxed mt-4" data-aos="fade-up" data-aos-delay="100">
                        La sophrologie implique souvent un accompagnement progressif : objectifs, exercices, retours d’expérience,
                        séances de suivi. Un bon logiciel doit vous aider à garder le fil, à structurer la relation client et à
                        simplifier la gestion de votre activité.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10" data-aos="fade-up">
                    <div class="feature-card p-8">
                        <h3 class="text-2xl font-bold text-primary">Structurer votre activité de sophrologue</h3>
                        <p class="mt-3">
                            Entre les séances individuelles, les suivis et parfois les ateliers, il est facile de perdre du temps
                            à rechercher des notes ou à reconstituer l’historique d’un client. AromaMade PRO rassemble vos informations
                            (dossiers, notes, documents, réponses aux questionnaires) pour rester cohérent d’une séance à l’autre.
                        </p>
                        <ul class="mt-4">
                            <li><i class="fas fa-check text-primary mr-2"></i> Retrouver un dossier en quelques secondes</li>
                            <li><i class="fas fa-check text-primary mr-2"></i> Garder une continuité de suivi</li>
                            <li><i class="fas fa-check text-primary mr-2"></i> Centraliser documents et informations utiles</li>
                        </ul>
                    </div>

                    <div class="feature-card p-8">
                        <h3 class="text-2xl font-bold text-primary">RDV en ligne : moins d’allers-retours, plus de régularité</h3>
                        <p class="mt-3">
                            La réservation en ligne réduit les messages et fluidifie l’accès à vos séances.
                            Vous configurez vos créneaux, vos durées et vos modalités (cabinet, visio, domicile).
                            Les rappels automatiques aident à maintenir un rythme et limitent les oublis.
                        </p>
                        <ul class="mt-4">
                            <li><i class="fas fa-check text-primary mr-2"></i> Réservation 24/7</li>
                            <li><i class="fas fa-check text-primary mr-2"></i> Rappels automatiques</li>
                            <li><i class="fas fa-check text-primary mr-2"></i> Moins de no-show</li>
                        </ul>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mt-10" data-aos="fade-up">
                    <div class="feature-card p-8">
                        <h3 class="text-2xl font-bold text-primary">Facturation sophrologue : simple et présentable</h3>
                        <p class="mt-3">
                            Si vous exercez en micro-entreprise, vous avez besoin de documents propres, rapides à produire,
                            et faciles à retrouver. AromaMade PRO vous aide à générer devis et factures en quelques clics et à
                            garder une vision claire de votre activité.
                        </p>
                        <ul class="mt-4">
                            <li><i class="fas fa-check text-primary mr-2"></i> Devis & factures professionnels</li>
                            <li><i class="fas fa-check text-primary mr-2"></i> Suivi client par client</li>
                            <li><i class="fas fa-check text-primary mr-2"></i> Fin des fichiers dispersés</li>
                        </ul>
                    </div>

                    <div class="feature-card p-8">
                        <h3 class="text-2xl font-bold text-primary">Questionnaires & suivi : mieux préparer l’accompagnement</h3>
                        <p class="mt-3">
                            Avant une séance, un court questionnaire peut clarifier le besoin du moment.
                            Après, il peut aider à suivre la progression (ressenti, pratique, difficultés).
                            Vous envoyez un lien, les réponses reviennent automatiquement au bon endroit.
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
                        Vous cherchez un <b>logiciel sophrologue</b> pour gagner du temps et professionnaliser votre pratique ?
                        Testez AromaMade PRO gratuitement.
                    </p>
                    <a href="{{ route('register-pro') }}" class="btn-primary">
                        Créer mon compte <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION: Comment ça marche --}}
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-3xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold mb-5 text-primary" data-aos="fade-up">
                    Comment ça marche ?
                </h2>
                <p class="text-lg text-gray-600 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
                    Une mise en place simple pour commencer rapidement, même si vous débutez.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="feature-card p-7" data-aos="fade-up">
                    <div class="text-3xl text-primary mb-4"><i class="fas fa-user-plus"></i></div>
                    <h3 class="text-xl font-bold mb-2">1) Créez votre compte</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Accédez à votre espace PRO et à votre tableau de bord en quelques minutes.
                    </p>
                </div>

                <div class="feature-card p-7" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-3xl text-primary mb-4"><i class="fas fa-calendar-check"></i></div>
                    <h3 class="text-xl font-bold mb-2">2) Paramétrez vos séances</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Durées, modalités (cabinet/visio/domicile), disponibilités, page de réservation.
                    </p>
                </div>

                <div class="feature-card p-7" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-3xl text-primary mb-4"><i class="fas fa-rocket"></i></div>
                    <h3 class="text-xl font-bold mb-2">3) Centralisez votre suivi</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Dossiers, questionnaires, notes, documents, devis et factures : tout au même endroit.
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
                    FAQ – Logiciel sophrologue
                </h2>
                <p class="text-lg text-gray-600 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
                    Réponses aux questions fréquentes avant de choisir un logiciel pour gérer une activité de sophrologie.
                </p>
            </div>

            <div class="accordion max-w-3xl mx-auto mt-10" data-aos="fade-up">
                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Quel logiciel choisir pour un sophrologue en micro-entreprise ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        Choisissez un outil simple qui centralise agenda, dossiers, suivi et facturation. AromaMade PRO est pensé pour un usage quotidien, rapide à prendre en main.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Peut-on proposer des séances en visio ou à domicile ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        Oui. Vous pouvez organiser vos modalités (cabinet, visio, domicile) et garder une gestion unique de votre planning et de vos dossiers.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>À quoi sert la prise de rendez-vous en ligne pour un sophrologue ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        Elle facilite la réservation, réduit les messages, et limite les oublis via les rappels automatiques. Vos clients prennent rendez-vous quand ils en ont besoin.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Peut-on envoyer un questionnaire avant une première séance ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        Oui. Vous pouvez envoyer un questionnaire (besoin, objectifs, contexte) et récupérer les réponses automatiquement dans le dossier client.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Y a-t-il un essai gratuit du logiciel sophrologue ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        Oui, AromaMade PRO propose un essai gratuit de 14 jours, sans engagement.
                    </div>
                </div>
            </div>
        </div>

        {{-- FAQ Schema --}}
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "FAQPage",
          "mainEntity": [
            {
              "@type": "Question",
              "name": "Quel logiciel choisir pour un sophrologue en micro-entreprise ?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Choisissez un outil simple qui centralise agenda, dossiers, suivi et facturation. AromaMade PRO est pensé pour un usage quotidien, rapide à prendre en main."
              }
            },
            {
              "@type": "Question",
              "name": "Peut-on proposer des séances en visio ou à domicile ?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Oui. Vous pouvez organiser vos modalités (cabinet, visio, domicile) et garder une gestion unique de votre planning et de vos dossiers."
              }
            },
            {
              "@type": "Question",
              "name": "À quoi sert la prise de rendez-vous en ligne pour un sophrologue ?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Elle facilite la réservation, réduit les messages, et limite les oublis via les rappels automatiques. Vos clients prennent rendez-vous quand ils en ont besoin."
              }
            },
            {
              "@type": "Question",
              "name": "Peut-on envoyer un questionnaire avant une première séance ?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Oui. Vous pouvez envoyer un questionnaire (besoin, objectifs, contexte) et récupérer les réponses automatiquement dans le dossier client."
              }
            },
            {
              "@type": "Question",
              "name": "Y a-t-il un essai gratuit du logiciel sophrologue ?",
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
                    Essayez le logiciel sophrologue AromaMade PRO
                </h2>
                <p class="text-lg text-gray-600 leading-relaxed mb-8">
                    Centralisez votre agenda, vos rendez-vous en ligne, vos dossiers clients, vos questionnaires de suivi
                    et votre facturation. Testez gratuitement pendant 14 jours.
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
