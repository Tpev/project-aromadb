<x-app-layout>

    <!-- Hero Section with Logo -->
    <div class="hero bg-cover bg-center flex items-center justify-center" style="background-image: url('{{ asset('images/hero-background.jpg') }}'); height: 60vh;">
        <div class="container mx-auto text-center">
            <!-- Display Logo -->
            <img src="{{ asset('images/png-01.png') }}" alt="AromaMade Logo" class="mx-auto logo">
        </div>
    </div>

<!-- Features Section (Nos Catégories) -->
<section class="py-8 bg-white">
    <div class="container mx-auto">
        <h2 class="text-3xl font-bold text-center mb-4" style="color: #647a0b;">
            <i class="fas fa-th-large mr-2" style="color: #854f38;"></i>Nos catégories
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Huile Essentielle -->
            <div class="text-center bg-gray-100 p-6 rounded-lg shadow">
                <i class="fas fa-leaf text-6xl mb-4" style="color: #854f38;"></i>
                <h3 class="text-2xl font-bold mb-4" style="color: #647a0b;">Huiles Essentielles</h3>
                <p class="text-lg mb-4">Découvrez des informations fiables sur les huiles essentielles : leurs bienfaits, usages, et précautions, pour un usage éclairé et responsable.</p>
                <a href="{{ route('huilehes.index') }}" class="btn-primary">Découvrir</a>
            </div>

            <!-- Huile Végétale -->
            <div class="text-center bg-gray-100 p-6 rounded-lg shadow">
                <i class="fas fa-seedling text-6xl mb-4" style="color: #854f38;"></i>
                <h3 class="text-2xl font-bold mb-4" style="color: #647a0b;">Huiles Végétales</h3>
                <p class="text-lg mb-4">Découvrez notre collection d'huiles végétales, soigneusement documentée pour offrir des informations fiables sur leurs bienfaits naturels.</p>
                <a href="{{ route('huilehvs.index') }}" class="btn-primary">Explorer</a>
            </div>

            <!-- Tisanes -->
            <div class="text-center bg-gray-100 p-6 rounded-lg shadow">
                <i class="fas fa-mug-hot text-6xl mb-4" style="color: #854f38;"></i>
                <h3 class="text-2xl font-bold mb-4" style="color: #647a0b;">Tisanes</h3>
                <p class="text-lg mb-4">Explorez les tisanes, où chaque infusion est accompagnée d'informations précises et vérifiées. Que ce soit pour la relaxation ou pour leurs vertus spécifiques.</p>
                <a href="{{ route('tisanes.index') }}" class="btn-primary">Voir plus</a>
            </div>

            <!-- Recettes -->
            <div class="text-center bg-gray-100 p-6 rounded-lg shadow">
                <i class="fas fa-book-open text-6xl mb-4" style="color: #854f38;"></i>
                <h3 class="text-2xl font-bold mb-4" style="color: #647a0b;">Recettes</h3>
                <p class="text-lg mb-4">Explorez des recettes naturelles soigneusement élaborées pour soutenir votre bien-être, tout en mettant en avant l’efficacité des huiles essentielles, végétales, et tisanes.</p>
                <a href="{{ route('recettes.index') }}" class="btn-primary">Voir Recettes</a>
            </div>

            <!-- Articles -->
            <div class="text-center bg-gray-100 p-6 rounded-lg shadow">
                <i class="fas fa-newspaper text-6xl mb-4" style="color: #854f38;"></i>
                <h3 class="text-2xl font-bold mb-4" style="color: #647a0b;">Articles</h3>
                <p class="text-lg mb-4">Découvrez des articles hebdomadaires sur l'aromathérapie, le bien-être, et les bonnes pratiques pour rester en bonne santé naturellement.</p>
                <a href="{{ route('blog.index') }}" class="btn-primary">Lire les Articles</a>
            </div>
<!-- Therapists Section -->
<section class="py-12 bg-white">
    <div class="container mx-auto text-center px-4">
        <!-- Icon at the top -->
        <i class="fas fa-user-md text-6xl mb-4" style="color: #854f38;"></i>
        <h2 class="text-2xl font-bold mb-4" style="color: #647a0b;">Espace Thérapeutes</h2>
        <p class="text-lg mb-4 text-gray-700 max-w-3xl mx-auto">
            Vous êtes thérapeute ? Découvrez comment AromaMade PRO peut vous aider à optimiser votre pratique, élargir votre clientèle, et gagner du temps au quotidien grâce à nos outils dédiés.
        </p>

        <div class="mt-8">
            <a href="{{ route('prolanding') }}" class="btn-primary">Découvrir l'Espace Thérapeutes</a>
        </div>
    </div>
</section>


        </div>
    </div>
</section>

    <!-- New Section for Additional Features -->
    <section class="py-8 bg-gray-50">
        <div class="container mx-auto text-center">
            <h2 class="text-3xl font-bold mb-4" style="color: #647a0b;">Créez un compte gratuitement</h2>
            <p class="text-lg max-w-3xl mx-auto mb-6">
                En créant un compte gratuit sur AromaMade, vous accédez à des fonctionnalités exclusives. Sauvegardez vos fiches préférées, que ce soit pour les huiles essentielles, les huiles végétales, les tisanes ou les recettes, et bien plus encore !
            </p>
            <a href="{{ route('register') }}" class="btn-secondary">Créer un compte</a>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="py-8 bg-gray-100">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-center mb-4" style="color: #647a0b;"><i class="fas fa-info-circle mr-2" style="color: #854f38;"></i>À propos de nous</h2>
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
            width: 100%;
            height: auto;
        }

        @media (max-width: 768px) {
            .logo {
                max-width: 500px;
            }
        }

        /* Button Styles */
        .btn-primary {
            background-color: #647a0b;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #576a0a;
        }

        .btn-secondary {
            background-color: #854f38;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-secondary:hover {
            background-color: #723c2f;
        }

        /* Custom Icon Color */
        i {
            color: #854f38;
        }

        /* Title Color */
        h2, h3 {
            color: #647a0b;
        }
    </style>

    <!-- Font Awesome for icons and Flag Icons for flags -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css">

</x-app-layout>
