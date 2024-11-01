{{-- resources/views/public/therapist/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ $therapist->business_name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- Hero Section --}}
            <div class="bg-white shadow-lg rounded-lg p-8 flex flex-col md:flex-row items-center justify-center transform transition duration-500 hover:scale-105 hover:shadow-2xl">
                @if($therapist->profile_picture)
                    <img src="{{ asset('storage/' . $therapist->profile_picture) }}" alt="{{ __('Photo de Profil') }}" class="w-40 h-40 rounded-full object-cover border-4 border-green-500 shadow-md animate-bounce">
                @else
                    <div class="w-40 h-40 rounded-full bg-gray-300 flex items-center justify-center text-white text-3xl">
                        {{ strtoupper(substr($therapist->company_name, 0, 1)) }}
                    </div>
                @endif
                <div class="mt-6 md:mt-0 text-center">
                    <h1 class="text-4xl font-bold text-gray-800">{{ $therapist->company_name }}</h1>
                    <p class="mt-4 text-lg text-gray-600">
                        {{ $therapist->profile_description ?? __('') }}
                    </p>
                </div>
            </div>



            {{-- About Section --}}
            <div class="bg-white shadow rounded-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <h3 class="text-2xl font-semibold text-gray-700">{{ __('À Propos') }}</h3>
                <p class="mt-4 text-gray-600 text-lg">
                    {{ $therapist->about ?? __('Informations à propos non disponibles.') }}
                </p>
            </div>

            {{-- Services Section --}}
            <div class="bg-white shadow rounded-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <h3 class="text-2xl font-semibold text-gray-700">{{ __('Services') }}</h3>
                
                {{-- Decode services JSON string into an array --}}
                @php
                    $services = json_decode($therapist->services, true) ?? [];
                @endphp

                @if(is_array($services) && count($services) > 0)
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($services as $service)
                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                {{ $service }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-gray-600">{{ __('Aucun service spécifié.') }}</p>
                @endif
            </div>

            {{-- Contact Section --}}
            <div class="bg-white shadow rounded-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <h3 class="text-2xl font-semibold text-gray-700">{{ __('Contact') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">

                    @if($therapist->share_address_publicly)
                        <div>
                            <h4 class="text-xl font-semibold text-gray-700">{{ __('Adresse') }}</h4>
                            <p class="text-gray-600">{{ $therapist->company_address ?? __('Adresse non disponible.') }}</p>
                        </div>
                    @endif

                    @if($therapist->share_phone_publicly)
                        <div>
                            <h4 class="text-xl font-semibold text-gray-700">{{ __('Téléphone') }}</h4>
                            <p class="text-gray-600">{{ $therapist->company_phone ?? __('Téléphone non disponible.') }}</p>
                        </div>
                    @endif

                </div>

                @if($therapist->share_email_publicly)
                    <div class="mt-6">
                        <h4 class="text-xl font-semibold text-gray-700">{{ __('Email') }}</h4>
                        <p class="text-gray-600">
                            <a href="mailto:{{ $therapist->company_email }}" class="text-indigo-600 hover:text-indigo-800">
                                {{ $therapist->company_email }}
                            </a>
                        </p>
                    </div>
                @endif
            </div>


		{{-- Prendre Rendez-vous Section --}}
		<div class="bg-white shadow rounded-lg p-6 hover:shadow-xl transition-shadow duration-300 text-center">
			<h3 class="text-2xl font-semibold text-gray-700">{{ __('Prendre Rendez-vous') }}</h3>
			<p class="text-lg text-gray-600 mt-4">
				{{ __('Contactez-moi pour prendre rendez-vous ou réservez directement en ligne.') }}
			</p>
			<div class="mt-6">
				@if($therapist->accept_online_appointments)
					<a href="{{ route('appointments.createPatient', $therapist->id) }}" class="inline-block bg-green-500 text-white text-lg px-6 py-3 rounded-lg hover:bg-green-600 transition-colors duration-300">
						{{ __('Réservez Maintenant') }}
					</a>
				@endif
			</div>
		</div>
{{-- Prestations Section --}}
<div class="bg-white shadow rounded-lg p-6 hover:shadow-xl transition-shadow duration-300">
    <h3 class="text-2xl font-semibold text-gray-700">{{ __('Prestations') }}</h3>
    @if($prestations->count() > 0)
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($prestations as $prestation)
                <div class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    @if($prestation->image)
                        <img src="{{ asset('storage/' . $prestation->image) }}" alt="{{ $prestation->name }}" class="w-full h-48 object-cover">
                    @endif
                    <div class="p-4">
                        <h4 class="text-xl font-semibold text-gray-800">{{ $prestation->name }}</h4>
                        <h5 class="text-l font-semibold text-gray-600"> Durée: {{ $prestation->duration }} min</h5>
                        <!-- Add 'break-words' class to handle long text -->
                        <p class="mt-2 text-gray-600 break-words">{{ $prestation->description }}</p>
                        @if($prestation->brochure)
                            <a href="{{ asset('storage/' . $prestation->brochure) }}" target="_blank" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800">
                                {{ __('Télécharger la brochure') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="mt-4 text-gray-600">{{ __('Aucune prestation disponible pour le moment.') }}</p>
    @endif
</div>



{{-- Témoignages Section --}}
<div class="bg-white shadow rounded-lg p-6 hover:shadow-xl transition-shadow duration-300">
    <h3 class="text-2xl font-semibold text-gray-700">{{ __('Témoignages') }}</h3>

    @if($testimonials->count() > 0)
        <div class="mt-4 space-y-6">
            @foreach($testimonials as $testimonial)
                <div class="p-4 border-l-4 border-green-500 bg-gray-50 rounded-md">
                    <p class="text-gray-700 italic">"{{ $testimonial->testimonial }}"</p>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ $testimonial->clientProfile->first_name }}, {{ $testimonial->created_at->format('d/m/Y') }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $testimonials->links() }}
        </div>
    @else
        <p class="mt-4 text-lg text-gray-600">
            {{ __('Les témoignages de mes clients seront bientôt disponibles ici.') }}
        </p>
    @endif
</div>


        </div>
    </div>

    {{-- Styles for Customization --}}
    @push('styles')
        <style>
            /* Custom styles for the therapist's profile */
/* Prestations Styling */
.prestations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
}

.prestations-item {
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    overflow: hidden;
    transition: box-shadow 0.3s ease;
}

.prestations-item:hover {
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}

.prestations-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.prestations-item h4 {
    font-size: 1.25rem;
    margin-top: 0.5rem;
}

.prestations-item p {
    margin-top: 0.5rem;
    color: #4a5568;
}

.prestations-item a {
    margin-top: 1rem;
    display: inline-block;
    color: #5a67d8;
    font-weight: 500;
}

            /* Smooth fade-in animation for sections */
            .fade-in {
                opacity: 0;
                animation: fadeInAnimation ease 2s forwards;
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

            /* Ensure the CTA button uses the correct brand green */
            .bg-green-500 {
                background-color: #38a169; /* Tailwind's green-500 */
            }

            .bg-green-600 {
                background-color: #2f855a; /* Tailwind's green-600 */
            }

            /* Enhanced hover effects for sections */
            .hover\:shadow-2xl:hover {
                box-shadow: 0 25px 50px -12px rgba(31, 41, 55, 0.25);
            }

            /* Optional: Customize the testimonial placeholder if needed */
            .testimonial-placeholder {
                background-color: #f3f4f6;
                padding: 20px;
                border-radius: 8px;
            }

            /* Responsive adjustments for Hero Section */
            @media (max-width: 768px) {
                .flex-col.md\:flex-row {
                    flex-direction: column;
                }
                .mt-6.md\:mt-0 {
                    margin-top: 1.5rem;
                }
                .text-center {
                    text-align: center;
                }
            }

            /* Add subtle animations to service tags */
            .service-tag {
                transition: transform 0.3s ease, background-color 0.3s ease;
            }

            .service-tag:hover {
                transform: translateY(-5px);
                background-color: #2f855a; /* Tailwind's green-600 */
            }

            /* Animation classes for sections */
            .bg-white {
                animation: fadeInAnimation ease 1s forwards;
            }

        </style>
    @endpush

    {{-- Scripts for Animations --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sections = document.querySelectorAll('.bg-white');
                sections.forEach(section => {
                    section.classList.add('fade-in');
                });
            });
        </script>
    @endpush
</x-app-layout>
