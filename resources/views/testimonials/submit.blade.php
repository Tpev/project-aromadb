{{-- resources/views/testimonials/submit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Soumettre votre Témoignage') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Form Section --}}
            <div class="bg-white shadow-lg rounded-lg p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">{{ __('Votre Témoignage') }}</h1>

                <form action="{{ route('testimonials.submit.post', ['token' => $testimonialRequest->token]) }}" method="POST">
                    @csrf

                    {{-- Testimonial Textarea --}}
                    <div class="mb-6">
                        <label for="testimonial" class="block text-lg font-medium text-gray-700">{{ __('Votre Témoignage') }}</label>
                        <textarea name="testimonial" id="testimonial" rows="6" class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 @error('testimonial') border-red-500 @enderror" placeholder="{{ __('Partagez votre expérience ici...') }}" required>{{ old('testimonial') }}</textarea>
                        @error('testimonial')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <div class="text-center">
                        <button type="submit" class="bg-green-500 text-white px-6 py-3 rounded-lg text-lg font-medium hover:bg-green-600 transition-colors duration-300">
                            {{ __('Soumettre') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    {{-- Styles for Customization --}}
    @push('styles')
        <style>
            /* Custom styles to align with the therapist's show page */

            /* Smooth fade-in animation for the form section */
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

            /* Enhanced textarea focus */
            textarea:focus {
                border-color: #38a169; /* Tailwind's green-500 */
                box-shadow: 0 0 0 3px rgba(56, 161, 105, 0.5);
            }
        </style>
    @endpush

    {{-- Scripts for Animations --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const formSection = document.querySelector('.bg-white');
                if(formSection){
                    formSection.classList.add('fade-in');
                }
            });
        </script>
    @endpush
</x-app-layout>
