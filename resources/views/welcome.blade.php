<x-app-layout>

    <!-- Hero Section with Logo and Search Bar -->
    <div class="hero bg-cover bg-center flex flex-col items-center justify-center text-white" style="background-image: url('{{ asset('images/hero-background.webp') }}'); height: 60vh;">
        <div class="container mx-auto text-center">
            <!-- Display Logo -->
            <img src="{{ asset('images/white-logo.png') }}" alt="AromaMade Logo" class="mx-auto logo mb-6">
        </div>

        <!-- Search Bar -->
        <div class="search-container absolute bottom-4 left-1/2 transform -translate-x-1/2 w-full max-w-2xl z-10 px-4">
            <input type="text" id="search-input" class="search-input w-full" placeholder="Rechercher des huiles, tisanes, recettes, articles..." autocomplete="off" aria-label="Recherche">
            <div id="search-results" class="search-results"></div>
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
				<!-- Thérapeutes -->
				<div class="text-center bg-gray-100 p-6 rounded-lg shadow">
					<i class="fas fa-user-md text-6xl mb-4" style="color: #854f38;"></i>
					<h3 class="text-2xl font-bold mb-4" style="color: #647a0b;">Thérapeutes</h3>
					<p class="text-lg mb-4">
						Découvrez des praticiens certifiés en médecines douces, soigneusement sélectionnés pour vous accompagner vers un bien-être optimal.
					</p>
					<a href="{{ route('nos-practiciens') }}" class="btn-primary">Voir les Thérapeutes</a>
				</div>

            </div>
        </div>
    </section>

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
            background-size: cover;
            background-position: center;
            min-height: 60vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            color: white;
        }

        /* Overlay for better text visibility */
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 0;
        }

        /* Ensure content is above the overlay */
        .hero > .container,
        .search-container {
            position: relative;
            z-index: 1;
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
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1rem;
            transition: background-color 0.3s, transform 0.2s;
            display: inline-block;
        }
        .btn-primary:hover {
            background-color: #576a0a;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #854f38;
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1rem;
            transition: background-color 0.3s, transform 0.2s;
            display: inline-block;
        }
        .btn-secondary:hover {
            background-color: #723c2f;
            transform: translateY(-2px);
        }

        /* Custom Icon Color */
        i {
            color: #854f38;
        }

        /* Title Color */
        h2, h3 {
            color: #647a0b;
        }

        /* Search Styles */
        .search-container {
            position: absolute; /* Changed from relative to absolute */
            bottom: 1rem; /* Adjust as needed */
            left: 50%;
            transform: translateX(-50%); /* Center horizontally */
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 0 1rem; /* Add some horizontal padding */
        }

        .search-input {
            width: 100%;
            padding: 15px 50px 15px 20px; /* Adjust padding to accommodate search icon */
            font-size: 1.2rem;
            border: 2px solid #fff;
            border-radius: 50px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
        }

        .search-input::placeholder {
            color: #666;
            font-style: italic;
        }

        .search-input:focus {
            border-color: #854f38;
            box-shadow: 0 0 10px rgba(133, 79, 56, 0.5);
        }

        /* Search Icon inside the input */
        .search-container::after {
            content: '\f002'; /* Font Awesome search icon */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #854f38;
            pointer-events: none;
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: white;
            border: 1px solid #ccc;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            z-index: 10;
            max-height: 400px;
            overflow-y: auto;
            border-radius: 0 0 8px 8px;
        }

        .search-result {
            padding: 12px 20px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }

        .search-result a {
            text-decoration: none;
            color: #333;
            display: block;
        }

        .search-result:hover {
            background-color: #f5f5f5;
        }

        .no-results {
            padding: 12px 20px;
            text-align: center;
            color: #888;
        }

        /* Highlighting matched terms */
        .highlight {
            background-color: yellow;
            color: #000;
            border-radius: 3px;
        }

        /* Grouped Results Styles */
        .search-type-header {
            padding: 10px 20px;
            background-color: #f0f0f0;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            color: #647a0b;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .search-results {
                max-height: 300px;
            }
        }
    </style>

    <!-- JavaScript for Instant Search -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-input');
            const resultsContainer = document.getElementById('search-results');

            // Debounce function to limit the rate of AJAX requests
            function debounce(func, delay) {
                let debounceTimer;
                return function() {
                    const context = this;
                    const args = arguments;
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => func.apply(context, args), delay);
                }
            }

            // Function to handle search
            const handleSearch = debounce(function () {
                const query = this.value.trim();

                if (query.length > 2) { // Minimum 3 characters before triggering search
                    fetch(`/search?query=${encodeURIComponent(query)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`Error: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            resultsContainer.innerHTML = ''; // Clear previous results

                            // Combine all results with type
                            const results = [
                                ...data.huileHEs.map(item => ({...item, type: 'Huile Essentielle'})),
                                ...data.huileHVs.map(item => ({...item, type: 'Huile Végétale'})),
                                ...data.tisanes.map(item => ({...item, type: 'Tisane'})),
                                ...data.recettes.map(item => ({...item, type: 'Recette'})),
                                ...data.articles.map(item => ({...item, type: 'Article'})),
                            ];

                            if (results.length > 0) {
                                // Group results by type
                                const groupedResults = results.reduce((acc, item) => {
                                    acc[item.type] = acc[item.type] || [];
                                    acc[item.type].push(item);
                                    return acc;
                                }, {});

                                for (const [type, items] of Object.entries(groupedResults)) {
                                    // Create a header for each type
                                    const typeHeader = document.createElement('div');
                                    typeHeader.classList.add('search-type-header');
                                    typeHeader.textContent = type;
                                    resultsContainer.appendChild(typeHeader);

                                    items.forEach(result => {
                                        const resultElement = document.createElement('div');
                                        resultElement.classList.add('search-result');

                                        // Highlight matched query
                                        let displayName = (result.NomHE || result.NomHV || result.NomTisane || result.NomRecette || result.Title);
                                        const regex = new RegExp(`(${query})`, 'gi');
                                        displayName = displayName.replace(regex, '<span class="highlight">$1</span>');

                                        // Construct URL based on type
                                        let url = '#';
                                        switch(result.type) {
                                            case 'Huile Essentielle':
                                                url = `/huilehes/${result.slug}`;
                                                break;
                                            case 'Huile Végétale':
                                                url = `/huilehvs/${result.slug}`;
                                                break;
                                            case 'Tisane':
                                                url = `/tisanes/${result.slug}`;
                                                break;
                                            case 'Recette':
                                                url = `/recettes/${result.slug}`;
                                                break;
                                            case 'Article':
                                                url = `/article/${result.slug}`;
                                                break;
                                            default:
                                                url = '/';
                                        }

                                        resultElement.innerHTML = `<a href="${url}">${displayName}</a>`;
                                        resultsContainer.appendChild(resultElement);
                                    });
                                }
                            } else {
                                resultsContainer.innerHTML = '<div class="no-results">Aucun résultat trouvé</div>';
                            }
                        })
                        .catch(error => {
                            console.error('Search Error:', error);
                            resultsContainer.innerHTML = '<div class="no-results">Une erreur est survenue. Veuillez réessayer plus tard.</div>';
                        });
                } else {
                    resultsContainer.innerHTML = ''; // Clear results if query is too short
                }
            }, 300); // 300ms debounce delay

            searchInput.addEventListener('input', handleSearch);

            // Close search results when clicking outside
            document.addEventListener('click', function (e) {
                if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                    resultsContainer.innerHTML = '';
                }
            });
        });
    </script>

    <!-- Font Awesome for icons and Flag Icons for flags -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css">

</x-app-layout>
