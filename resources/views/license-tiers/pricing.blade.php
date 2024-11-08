{{-- resources/views/license-tiers/pricing.blade.php --}}

<x-app-layout>
    <!-- Importation des styles nécessaires -->
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
        Découvrez nos nouvelles offres de licences via Stripe. Choisissez l'offre qui vous convient et commencez dès aujourd'hui à bénéficier de nos services professionnels.
    @endsection

    <!-- Hero Section -->
    <section class="hero relative">
        <div class="hero-bg absolute w-full h-full bg-center bg-cover" style="background-image: url('{{ asset('images/hero.webp') }}');">
            <div class="overlay absolute inset-0 bg-gradient-to-b from-black via-transparent to-black opacity-60"></div>
        </div>
        <div class="container mx-auto text-center relative z-10 py-24">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 text-white animate-fade-in">Nos Offres de Licences</h1>
            <p class="text-xl md:text-2xl mb-8 text-white">Choisissez l'offre qui correspond le mieux à vos besoins</p>
        </div>
        <div class="overlay absolute inset-0 bg-black opacity-50"></div>
    </section>

    <!-- Stripe Pricing Table Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="card shadow-sm p-6 bg-gray-50 rounded-lg" data-aos="fade-up">
                <!-- Stripe Pricing Table -->
				<script async src="https://js.stripe.com/v3/pricing-table.js"></script>
				<stripe-pricing-table pricing-table-id="prctbl_1QIpr7E7cOnJl2vMyaJgW3Va"
				publishable-key="pk_live_51Q9V2qE7cOnJl2vMLc5W3wsvncTnesV0vzuk3q1q84WeAQXdaByNKOSfGUSOHrH0XPLaGDn8iENfk4akaW6FFRkg00Tct8pxG8"
				customer-email="{{ $customer_email }}"
				client-reference-id="{{$customer_stripe}}"
				>
				</stripe-pricing-table>
				
            </div>
        </div>
    </section>

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
