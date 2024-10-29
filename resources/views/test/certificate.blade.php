<x-app-layout>
    <!-- Importation des styles et scripts nécessaires via Vite -->
    @push('styles')
        <!-- AOS Animation Library -->
        <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
        <!-- Font Awesome pour les icônes -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
        <!-- Polices personnalisées -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto&display=swap" rel="stylesheet">
    @endpush

    @section('meta_description')
        Affichez votre certificat de complétion après avoir terminé la formation sur AromaMade PRO.
    @endsection

    <!-- Section du Certificat -->
    <section class="py-12 bg-white">
        <div class="container mx-auto text-center px-4">
            <h2 class="text-3xl font-bold mb-8 text-primary">Votre Certificat de Complétion</h2>
            <div class="flex flex-col items-center">
                <img src="{{ asset($imagePath) }}" alt="Certificat Généré" class="max-w-lg mx-auto mb-6 shadow-lg rounded-lg">
                <a href="{{ asset($imagePath) }}" download="Certificat_{{ auth()->user()->name }}.png" class="btn-primary flex items-center">
                    <i class="fas fa-download mr-2"></i>Télécharger le Certificat
                </a>
            </div>
        </div>
    </section>

    <!-- Footer (réutilisation de votre footer existant) -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto text-center px-4">
            <p>&copy; {{ date('Y') }} AromaMade PRO. Tous droits réservés.</p>
            <!-- Icônes Sociales (optionnel) -->
            <div class="social-icons flex justify-center space-x-4 mt-4">
                <a href="#" class="text-gray-400 hover:text-blue-500 transition-colors duration-300">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors duration-300">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-pink-500 transition-colors duration-300">
                    <i class="fab fa-instagram"></i>
                </a>
            </div>
        </div>
    </footer>

    <!-- Scripts personnalisés -->
    @push('scripts')
        <!-- AOS Animation Library -->
        <script src="https://unpkg.com/aos@next/dist/aos.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Initialiser AOS pour les animations
                AOS.init({
                    once: true
                });

                // Fonctionnalité de l'accordéon (si nécessaire sur cette page)
                const accordionItems = document.querySelectorAll('.accordion-item');

                accordionItems.forEach(item => {
                    const header = item.querySelector('.accordion-header');
                    header.addEventListener('click', () => {
                        item.classList.toggle('active');
                    });
                });
            });
        </script>
    @endpush

    <!-- Styles personnalisés supplémentaires -->
    <style>
        /* Styles spécifiques à la page du certificat */

        /* Bouton personnalisé */
        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
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

        /* Icône dans le bouton */
        .btn-primary i {
            transition: margin-right 0.3s;
        }

        .btn-primary:hover i {
            margin-right: 8px;
        }
    </style>
</x-app-layout>
