{{-- resources/views/testimonials/thankyou.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Merci pour votre Témoignage') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Thank You Section --}}
            <div class="bg-white shadow-lg rounded-lg p-8 text-center">
                <h1 class="text-3xl font-bold text-green-500 mb-6">
                    🎉 {{ __('Merci !') }} 🎉
                </h1>

                <p class="text-lg text-gray-700">
                    {{ __('Votre témoignage a bien été soumis. Nous apprécions vraiment votre retour et nous vous remercions pour votre confiance.') }}
                </p>

                <div class="mt-8">
                    <a href="{{ route('therapist.show', ['slug' => $therapist->slug]) }}" class="inline-block bg-green-500 text-white px-6 py-3 rounded-lg text-lg font-medium hover:bg-green-600 transition-colors duration-300">
                        {{ __('Retour au Profil') }}
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- Styles for Customization --}}
    @push('styles')
        <style>
            /* Custom styles to align with the therapist's show page */

            /* Smooth fade-in animation for the thank you section */
            .bg-white {
                opacity: 0;
                animation: fadeInAnimation ease 1s forwards;
            }

            @keyframes fadeInAnimation {
                0% {
                    opacity: 0;
                    transform: translateY(20px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Enhanced hover effects for the return button */
            .bg-green-500:hover {
                background-color: #2f855a; /* Tailwind's green-600 */
            }
        </style>
    @endpush

    {{-- Scripts for Animations --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const thankYouSection = document.querySelector('.bg-white');
                if(thankYouSection){
                    thankYouSection.classList.add('fade-in');
                }
            });
        </script>
    @endpush
</x-app-layout>
