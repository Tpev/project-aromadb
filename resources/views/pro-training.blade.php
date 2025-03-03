<x-app-layout>
    <!-- Importing CSS/JS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @section('meta_description')
        Découvrez "De la Passion au Succès", la formation complète pour lancer et développer votre activité de thérapeute du bien-être. Maîtrisez le business, le marketing et la gestion pour transformer votre passion en succès.
    @endsection

    <!-- Big Page Title Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto text-center">
            <h1 class="font-bold text-center mb-8 animate-fade-in text-2xl md:text-4xl" style="color: #647a0b;">
                De la Passion au Succès : Guide Complet pour Lancer et Développer son Activité de Thérapeute du Bien-Être
            </h1>
            <img src="{{ asset('images/pro.webp') }}" alt="Formation De la Passion au Succès" class="mx-auto my-6 max-w-md shadow-lg rounded-lg">
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-8 bg-green-100">
        <div class="container mx-auto text-center px-4">
            <h2 class="text-3xl font-bold mb-4 text-primary">Commencez Votre Formation Dès Aujourd'hui</h2>
            <p class="text-lg max-w-3xl mx-auto mb-6 text-gray-700">
                Accédez immédiatement à notre formation pour transformer votre expertise thérapeutique en un véritable succès commercial.
            </p>
            <a href="{{ route('trainings.show-lesson', [1, 1]) }}" class="btn-primary flex items-center justify-center">
                <i class="fas fa-forward mr-2"></i> Commencez la Formation
            </a>
        </div>
    </section>

    <!-- Quick Info Section -->
    <section class="py-8 bg-gray-100">
        <div class="container mx-auto max-w-4xl">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <!-- Expertise -->
                <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition-shadow duration-300 transform hover:-translate-y-2 animate-slide-in">
                    <i class="fas fa-briefcase text-5xl mb-4" style="color: #647a0b;"></i>
                    <h3 class="text-2xl font-bold mb-2" style="color: #647a0b;">Expertise Professionnelle</h3>
                    <p class="text-lg text-gray-700">
                        Destiné aux thérapeutes compétents qui souhaitent aller au-delà de la pratique pour développer leur activité.
                    </p>
                </div>
                <!-- Strategies -->
                <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition-shadow duration-300 transform hover:-translate-y-2 animate-slide-in">
                    <i class="fas fa-chart-line text-5xl mb-4" style="color: #854f38;"></i>
                    <h3 class="text-2xl font-bold mb-2" style="color: #647a0b;">Stratégies Marketing & Business</h3>
                    <p class="text-lg text-gray-700">
                        Découvrez comment attirer et fidéliser vos clients grâce à des techniques digitales et locales éprouvées.
                    </p>
                </div>
                <!-- Management -->
                <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition-shadow duration-300 transform hover:-translate-y-2 animate-slide-in">
                    <i class="fas fa-handshake text-5xl mb-4" style="color: #647a0b;"></i>
                    <h3 class="text-2xl font-bold mb-2" style="color: #647a0b;">Gestion & Réseautage</h3>
                    <p class="text-lg text-gray-700">
                        Apprenez à organiser votre planning et à bâtir un réseau solide pour assurer la croissance de votre pratique.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Introduction Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto">
            <h1 class="text-4xl font-bold text-center mb-6 animate-fade-in" style="color: #647a0b;">
                Transformez votre expertise en succès commercial
            </h1>
            <p class="text-lg text-center max-w-3xl mx-auto mb-8 text-gray-700">
                Vous excellez dans l’art thérapeutique, mais gérer une pratique exige de maîtriser le marketing, la gestion et le réseautage. Notre formation vous guide à travers 8 étapes clés pour réussir.
            </p>
        </div>
    </section>

    <!-- "Pourquoi suivre cette formation ?" Section -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto max-w-3xl text-center">
            <h2 class="text-4xl font-bold mb-8 animate-slide-in" style="color: #647a0b;">Pourquoi suivre cette formation ?</h2>
            <div class="bg-white p-8 rounded-lg shadow-lg">
                <p class="text-lg text-gray-700">
                    Vous êtes déjà un excellent thérapeute, mais transformer votre savoir-faire en une entreprise prospère nécessite bien plus.
                    Cette formation vous guide pour maîtriser les aspects marketing, la gestion opérationnelle et le développement de votre réseau,
                    afin d'assurer la réussite de votre pratique.
                </p>
                <p class="text-lg text-gray-700 mt-4">
                    Découvrez comment adapter votre expertise aux exigences du marché et attirer durablement de nouveaux clients.
                </p>
            </div>
        </div>
    </section>

    <!-- "Ce que vous apprendrez" Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto max-w-3xl text-center">
            <h2 class="text-4xl font-bold mb-8" style="color: #647a0b;">Ce que vous apprendrez</h2>
            <div class="bg-gray-100 p-8 rounded-lg shadow-lg">
                <div class="space-y-6 text-left">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-3xl mr-4" style="color: #647a0b;"></i>
                        <div>
                            <h3 class="text-2xl font-bold" style="color: #647a0b;">INTRODUCTION & OBJECTIFS</h3>
                            <p class="text-lg text-gray-700">
                                Comprenez les bases et posez les fondations pour votre succès professionnel.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-user-edit text-3xl mr-4" style="color: #854f38;"></i>
                        <div>
                            <h3 class="text-2xl font-bold" style="color: #647a0b;">CLARIFIER SON IDENTITÉ & SON POSITIONNEMENT</h3>
                            <p class="text-lg text-gray-700">
                                Définissez une identité forte et positionnez-vous pour vous démarquer dans un marché concurrentiel.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-share-alt text-3xl mr-4" style="color: #647a0b;"></i>
                        <div>
                            <h3 class="text-2xl font-bold" style="color: #647a0b;">CONSTRUIRE ET OPTIMISER SES RÉSEAUX SOCIAUX</h3>
                            <p class="text-lg text-gray-700">
                                Apprenez à utiliser efficacement les réseaux sociaux pour augmenter votre visibilité et attirer des clients.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-tags text-3xl mr-4" style="color: #854f38;"></i>
                        <div>
                            <h3 class="text-2xl font-bold" style="color: #647a0b;">CRÉER UNE OFFRE SPÉCIALE DE LANCEMENT</h3>
                            <p class="text-lg text-gray-700">
                                Concevez une offre irrésistible pour lancer votre activité et capter rapidement l’attention de vos futurs clients.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-network-wired text-3xl mr-4" style="color: #647a0b;"></i>
                        <div>
                            <h3 class="text-2xl font-bold" style="color: #647a0b;">PARTICIPER À DES ÉVÉNEMENTS & CONSTRUIRE SON RÉSEAU LOCAL</h3>
                            <p class="text-lg text-gray-700">
                                Apprenez à créer des partenariats et à développer un réseau solide pour soutenir la croissance de votre pratique.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-calendar-alt text-3xl mr-4" style="color: #854f38;"></i>
                        <div>
                            <h3 class="text-2xl font-bold" style="color: #647a0b;">GÉRER SES PREMIERS RENDEZ-VOUS & L’EXPÉRIENCE CLIENT</h3>
                            <p class="text-lg text-gray-700">
                                Organisez efficacement vos rendez-vous et offrez une expérience client mémorable pour fidéliser votre clientèle.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-comments text-3xl mr-4" style="color: #647a0b;"></i>
                        <div>
                            <h3 class="text-2xl font-bold" style="color: #647a0b;">FIDÉLISER & ENCOURAGER LE BOUCHE-à-OREILLE</h3>
                            <p class="text-lg text-gray-700">
                                Découvrez des stratégies pour transformer vos clients satisfaits en ambassadeurs de votre pratique.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-tachometer-alt text-3xl mr-4" style="color: #854f38;"></i>
                        <div>
                            <h3 class="text-2xl font-bold" style="color: #647a0b;">MESURER & AJUSTER SES ACTIONS</h3>
                            <p class="text-lg text-gray-700">
                                Apprenez à analyser vos performances et à adapter vos stratégies pour assurer une croissance continue.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
       </div>
    </section>

    <!-- Final Call to Action Section -->
    <section class="py-8 bg-green-100 animate-fade-in">
        <div class="container mx-auto text-center">
            <h2 class="text-3xl font-bold mb-4 text-primary">Prêt à transformer votre passion en succès ?</h2>
            <p class="text-lg max-w-3xl mx-auto mb-6 text-gray-700">
                Inscrivez-vous dès maintenant et bénéficiez d’un accompagnement sur-mesure pour lancer et développer votre pratique.
            </p>
            <a href="{{ route('trainings.show-lesson', [1, 1]) }}" class="btn-primary animate-pulse">
                <i class="fas fa-forward mr-2"></i> Commencez la Formation
            </a>
        </div>
    </section>

    <!-- Custom Scripts and Additional Styles -->
    @push('scripts')
        <!-- Optional: Additional JS libraries or custom scripts -->
    @endpush

    <style>
        .text-primary {
            color: #647a0b;
        }
        .btn-primary {
            background: linear-gradient(90deg, #647a0b 0%, #854f38 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.125rem;
            transition: transform 0.3s, box-shadow 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        .btn-primary i {
            transition: margin-right 0.3s;
        }
        .btn-primary:hover i {
            margin-right: 8px;
        }
        .animate-fade-in {
            animation: fadeIn 1.5s ease-in-out;
        }
        .animate-slide-in {
            animation: slideIn 1.5s ease-in-out;
        }
        .animate-pulse {
            animation: pulse 2s infinite;
        }
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        @keyframes slideIn {
            0% { opacity: 0; transform: translateY(50px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</x-app-layout>
