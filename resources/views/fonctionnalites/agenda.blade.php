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
                L’agenda du praticien, simplifié et automatisé
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-10">
                <div class="feature-tile" data-aos="fade-up">
                    <i class="fas fa-calendar-check tile-icon"></i>
                    <h3>Créneaux personnalisés</h3>
                    <p>Définissez la durée de chaque service, insérez des <strong>temps de pause</strong> entre deux rendez-vous et ajustez vos horaires selon vos besoins.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="50">
                    <i class="fas fa-map-marker-alt tile-icon"></i>
                    <h3>Multi-lieux et modes de séance</h3>
                    <p>Gérez vos séances au cabinet, à domicile ou en visio — chaque service peut avoir ses propres modalités et adresses.</p>
                </div>

                <!-- UPDATED: Portail Pro (replaces "Lien de réservation unique") -->
                <div class="feature-tile" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-id-badge tile-icon"></i>
                    <h3>Portail Pro de réservation</h3>
                    <p>Un <strong>lien unique</strong> vers votre profil AromaMade PRO. Vos clients réservent directement depuis votre Portail Pro — simple et efficace.</p>
                </div>

                <!-- UPDATED: Ateliers & événements (replaces recurrent/shared) -->
                <div class="feature-tile" data-aos="fade-up" data-aos-delay="150">
                    <i class="fas fa-users tile-icon"></i>
                    <h3>Ateliers & événements</h3>
                    <p>Créez des séances spéciales : <strong>ateliers, stages, événements</strong>… avec des dates dédiées et une description claire.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-envelope-open-text tile-icon"></i>
                    <h3>Confirmations automatiques</h3>
                    <p>Vos clients reçoivent une confirmation dès qu’un rendez-vous est validé, ainsi qu’un lien direct pour le modifier ou l’annuler.</p>
                </div>

                <!-- REMOVED: Gestion des annulations -->

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas fa-calendar-day tile-icon"></i>
                    <h3>Horaires types et exceptions</h3>
                    <p>Enregistrez vos semaines types, créez des modèles d’horaires et définissez facilement les jours de fermeture ou de congés.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="350">
                    <i class="fas fa-exchange-alt tile-icon"></i>
                    <h3>Replanification simplifiée</h3>
                    <p>Modifiez un rendez-vous par simple glisser-déposer. Le client reçoit immédiatement la mise à jour par email.</p>
                </div>

                <div class="feature-tile" data-aos="fade-up" data-aos-delay="400">
                    <i class="fas fa-shield-alt tile-icon"></i>
                    <h3>Conforme RGPD et HDS</h3>
                    <p>Toutes les données de vos clients sont hébergées en France, sur des serveurs certifiés <strong>HDS</strong> (Hébergement de Données de Santé).</p>
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
