<x-app-layout>
    <!-- Importation des styles nécessaires via Vite -->
    @push('styles')
        <!-- AOS Animation Library -->
        <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
        <!-- Font Awesome for icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        <!-- Custom Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto&display=swap" rel="stylesheet">
    @endpush

    @section('meta_description')
        Gérez votre compte Stripe directement depuis AromaMade PRO. Connectez votre compte pour accepter les paiements et accédez à votre tableau de bord Stripe en un clic.
    @endsection

    <!-- Hero Section -->
    <section class="hero relative">
        <div class="hero-bg absolute w-full h-full bg-center bg-cover" style="background-image: url('{{ asset('images/hero.webp') }}');">
            <div class="overlay absolute inset-0 bg-gradient-to-b from-black via-transparent to-black opacity-60"></div>
        </div>
        <div class="container mx-auto text-center relative z-10 py-24">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 text-white animate-fade-in">Gestion de Votre Compte Stripe</h1>
            <p class="text-xl md:text-2xl mb-8 text-white">Configurez et gérez vos paiements facilement</p>
        </div>
        <div class="overlay absolute inset-0 bg-black opacity-50"></div>
    </section>

    <!-- Stripe Management Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="card shadow-sm p-6 bg-gray-50 rounded-lg" data-aos="fade-up">
                <!-- Messages de succès -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @endif

                <!-- Messages d'erreur -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @endif

                <!-- Gestion du Statut de la Connexion Stripe -->
                @if($accountStatus === 'connected')
                    <!-- Compte Stripe Connecté et Complet -->
                    <div class="mb-4">
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong>Succès !</strong> Votre compte Stripe est connecté.
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('stripe.dashboard') }}" class="btn btn-success btn-lg">
                            <i class="bi bi-bar-chart-line me-2"></i> Accéder à votre Tableau de Bord Stripe
                        </a>
                    </div>
                @elseif($accountStatus === 'incomplete')
                    <!-- Compte Stripe Connecté mais Incomplet -->
                    <div class="mb-4">
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Attention !</strong> La configuration de votre compte Stripe est incomplète.
                        </div>
                    </div>
                    <div class="mb-4 text-center">
                        <p>Il semble que vous n'ayez pas terminé la configuration de votre compte Stripe. Vous pouvez reprendre le processus d'onboarding.</p>
                        <a href="{{ route('stripe.refresh') }}" class="btn btn-secondary btn-lg">
                            <i class="bi bi-arrow-clockwise me-2"></i> Reprendre l'Onboarding Stripe
                        </a>
                    </div>
                @else
                    <!-- Compte Stripe Non Connecté -->
                    <div class="mb-4 text-center">
                        <p>Créez votre compte Stripe en seulement 5 minutes pour commencer à accepter les paiements par carte de crédit.</p>
                        <a href="{{ route('stripe.connect') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-arrow-up-right-square me-2"></i> Créer mon compte Stripe
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Autres sections existantes de votre application -->

    <!-- Custom Styles -->
    <style>
        /* Custom Colors */
        :root {
            --primary-color: #647a0b;
            --secondary-color: #854f38;
        }

        body {
            font-family: 'Roboto', sans-serif;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
        }

        .text-primary {
            color: var(--primary-color);
        }

        .bg-primary {
            background-color: var(--primary-color);
        }

        .text-secondary {
            color: var(--secondary-color);
        }

        .bg-secondary {
            background-color: var(--secondary-color);
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.125rem;
            transition: transform 0.3s, box-shadow 0.3s;
            display: inline-block;
        }

        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .btn-success {
            background-color: var(--primary-color);
            color: white;
            padding: 14px 28px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.125rem;
            transition: transform 0.3s, box-shadow 0.3s;
            display: inline-block;
        }

        .btn-success:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--primary-color);
            padding: 12px 24px;
            border: 2px solid var(--primary-color);
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.125rem;
            transition: background-color 0.3s, color 0.3s;
            display: inline-block;
        }

        .btn-secondary:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* Card Styling */
        .card {
            background-color: #f7fafc;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card {
                padding: 16px;
            }
        }

        /* Alert Icon Styling */
        .alert i {
            font-size: 1.5rem;
        }
    </style>

    @push('scripts')
        <!-- AOS Animation Library -->
        <script src="https://unpkg.com/aos@next/dist/aos.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Initialiser AOS pour les animations
                AOS.init({
                    once: true
                });
            });
        </script>
    @endpush
</x-app-layout>
