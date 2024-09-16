<x-app-layout>
    <!-- Hero Section with Logo -->
    <div class="hero bg-cover bg-center flex items-center justify-center" style="background-image: url('{{ asset('images/hero-background.jpg') }}'); height: 70vh;">
        <div class="container mx-auto text-center">
            <!-- Display Logo -->
            <img src="{{ asset('images/png-01.png') }}" alt="AromaMade Logo" class="mx-auto logo">
        </div>
    </div>

    <!-- Features Section (Nos Catégories) -->
    <section class="py-8 bg-white">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-center mb-4"><i class="fas fa-th-large mr-2"></i>Nos catégories</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Huile Essentielle -->
                <div class="text-center bg-gray-100 p-6 rounded-lg shadow">
                    <i class="fas fa-leaf text-6xl text-green-500 mb-4"></i>
                    <h3 class="text-2xl font-bold mb-4">Huiles Essentielles</h3>
                    <p class="text-lg mb-4">Découvrez des informations fiables sur les huiles essentielles : leurs bienfaits, usages, et précautions, pour un usage éclairé et responsable.</p>
                    <a href="{{ route('huilehes.index') }}" class="btn-primary">Découvrir</a>
                </div>

                <!-- Huile Végétale -->
                <div class="text-center bg-gray-100 p-6 rounded-lg shadow">
                    <i class="fas fa-seedling text-6xl text-green-500 mb-4"></i>
                    <h3 class="text-2xl font-bold mb-4">Huiles Végétales</h3>
                    <p class="text-lg mb-4">Découvrez notre collection d'huiles végétales, soigneusement documentée pour offrir des informations fiables sur leurs bienfaits naturels.</p>
                    <a href="{{ route('huilehvs.index') }}" class="btn-primary">Explorer</a>
                </div>

                <!-- Tisanes -->
                <div class="text-center bg-gray-100 p-6 rounded-lg shadow">
                    <i class="fas fa-mug-hot text-6xl text-green-500 mb-4"></i>
                    <h3 class="text-2xl font-bold mb-4">Tisanes</h3>
                    <p class="text-lg mb-4">Explorez les tisanes, où chaque infusion est accompagnée d'informations précises et vérifiées. Que ce soit pour la relaxation ou pour leurs vertus spécifiques.</p>
                    <a href="{{ route('tisanes.index') }}" class="btn-primary">Voir plus</a>
                </div>

                <!-- Recettes -->
                <div class="text-center bg-gray-100 p-6 rounded-lg shadow">
                    <i class="fas fa-book-open text-6xl text-green-500 mb-4"></i>
                    <h3 class="text-2xl font-bold mb-4">Recettes</h3>
                    <p class="text-lg mb-4">Explorez des recettes naturelles soigneusement élaborées pour soutenir votre bien-être, tout en mettant en avant l’efficacité des huiles essentielles, végétales, et tisanes.</p>
                    <a href="{{ route('recettes.index') }}" class="btn-primary">Voir Recettes</a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="py-8 bg-gray-100">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-center mb-4"><i class="fas fa-info-circle mr-2"></i>À propos de nous</h2>
            <p class="text-lg text-center max-w-4xl mx-auto mb-6">
                AromaMade est une plateforme créée pour offrir à tous un accès facile et gratuit à des informations fiables et vérifiées sur les huiles essentielles, les huiles végétales, les tisanes, et bien plus encore. 
                Nous croyons que la connaissance de la nature et de ses bienfaits doit être partagée de manière transparente, sans parti pris, afin que chacun puisse prendre des décisions éclairées pour son bien-être.
            </p>
            <p class="text-lg text-center max-w-4xl mx-auto mb-6">
                Notre mission est de bâtir une base de données riche et qualitative, où chaque donnée est soigneusement sélectionnée et validée, pour offrir une ressource accessible à tous, des amateurs de bien-être naturel aux praticiens expérimentés. 
                Nous voulons que ce savoir, autrefois dispersé ou difficile à trouver, soit centralisé et présenté de manière claire, précise, et bienveillante.
            </p>
            <div class="text-center">
                <a href="{{ route('welcome') }}" class="btn-secondary">En savoir plus</a>
            </div>
        </div>
    </section>

    <!-- Custom Styles -->
    <style>
        .hero {
            background: linear-gradient(to right, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/path/to/your/image.jpg');
            background-size: cover;
            background-position: center;
            height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Responsive Logo */
        .logo {
            max-width: 800px;
            width: 100%; /* Ensure it scales down */
            height: auto;
        }

        @media (max-width: 768px) {
            .logo {
                max-width: 300px; /* Smaller size for mobile */
            }
        }

        .btn-primary {
            background-color: #16a34a;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #15803d;
        }

        .btn-secondary {
            background-color: #15803d;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-secondary:hover {
            background-color: #14532d;
        }
    </style>
</x-app-layout>
