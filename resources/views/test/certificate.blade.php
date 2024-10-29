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
                <img src="{{ asset($imagePath) }}" alt="Certificat Généré" class="max-w-md mx-auto mb-6 shadow-lg rounded-lg">
                <a href="{{ asset($imagePath) }}" download="Certificat_{{ auth()->user()->name }}.png" class="btn-primary flex items-center mt-4">
                    <i class="fas fa-download mr-2"></i>Télécharger le Certificat
                </a>
            </div>
        </div>
    </section>

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

        /* Définir les couleurs directement */
        .text-primary {
            color: #647a0b; /* Couleur primaire */
        }

        .text-secondary {
            color: #854f38; /* Couleur secondaire */
        }

        /* Bouton personnalisé */
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

        /* Icône dans le bouton */
        .btn-primary i {
            transition: margin-right 0.3s;
        }

        .btn-primary:hover i {
            margin-right: 8px;
        }

        /* Centrer verticalement le contenu de la section */
        .flex-col.items-center {
            justify-content: center;
            min-height: 50vh; /* Ajustez selon vos besoins */
        }
    </style>
</x-app-layout>
