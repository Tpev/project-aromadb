{{-- resources/views/public/therapist/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ $therapist->business_name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- Hero Section --}}
            <div class="bg-[#8ea633] shadow-lg rounded-lg p-8 flex flex-col md:flex-row items-center justify-center">
                @if($therapist->profile_picture)
                    <img src="{{ asset('storage/' . $therapist->profile_picture) }}" alt="{{ __('Photo de Profil') }}" class="w-40 h-40 rounded-full object-cover border-4 border-white shadow-md">
                @else
                    <div class="w-40 h-40 rounded-full bg-white flex items-center justify-center text-[#8ea633] text-3xl">
                        {{ strtoupper(substr($therapist->company_name, 0, 1)) }}
                    </div>
                @endif
                <div class="mt-6 md:mt-0 text-center md:text-left md:ml-8">
                    <h1 class="text-4xl font-bold text-white">{{ $therapist->company_name }}</h1>
                    <p class="mt-4 text-lg text-white">
                        {{ $therapist->profile_description ?? '' }}
                    </p>
                </div>
            </div>

            {{-- About Section --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-2xl font-semibold text-[#647a0b]">{{ __('À Propos') }}</h3>
                <p class="mt-4 text-gray-600 text-lg">
                    {{ $therapist->about ?? __('Informations à propos non disponibles.') }}
                </p>
            </div>

            {{-- Services Section --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-2xl font-semibold text-[#647a0b]">{{ __('Services') }}</h3>
                
                {{-- Decode services JSON string into an array --}}
                @php
                    $services = json_decode($therapist->services, true) ?? [];
                @endphp

                @if(is_array($services) && count($services) > 0)
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($services as $service)
                            <span class="bg-[#647a0b] text-white px-3 py-1 rounded-full text-sm font-medium service-tag">
                                {{ $service }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-gray-600">{{ __('Aucun service spécifié.') }}</p>
                @endif
            </div>

            {{-- Contact Section --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-2xl font-semibold text-[#647a0b]">{{ __('Contact') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">

                    @if($therapist->share_address_publicly)
                        <div>
                            <h4 class="text-xl font-semibold text-[#647a0b]"><i class="fas fa-map-marker-alt mr-2 text-[#854f38]"></i>{{ __('Adresse') }}</h4>
                            <p class="text-gray-600">{{ $therapist->company_address ?? __('Adresse non disponible.') }}</p>
                        </div>
                    @endif

                    @if($therapist->share_phone_publicly)
                        <div>
                            <h4 class="text-xl font-semibold text-[#647a0b]"><i class="fas fa-phone mr-2 text-[#854f38]"></i>{{ __('Téléphone') }}</h4>
                            <p class="text-gray-600">{{ $therapist->company_phone ?? __('Téléphone non disponible.') }}</p>
                        </div>
                    @endif

                </div>

                @if($therapist->share_email_publicly)
                    <div class="mt-6">
                        <h4 class="text-xl font-semibold text-[#647a0b]"><i class="fas fa-envelope mr-2 text-[#854f38]"></i>{{ __('Email') }}</h4>
                        <p class="text-gray-600">
                            <a href="mailto:{{ $therapist->company_email }}" class="text-[#854f38] hover:text-[#6a3f2c]">
                                {{ $therapist->company_email }}
                            </a>
                        </p>
                    </div>
                @endif
            </div>

            {{-- Prendre Rendez-vous Section --}}
            <div class="bg-white shadow rounded-lg p-6 text-center">
                <h3 class="text-2xl font-semibold text-[#647a0b]">{{ __('Prendre Rendez-vous') }}</h3>
                <p class="text-lg text-gray-600 mt-4">
                    {{ __('Contactez-moi pour prendre rendez-vous ou réservez directement en ligne.') }}
                </p>
                <div class="mt-6">
                    @if($therapist->accept_online_appointments)
                        <a href="{{ route('appointments.createPatient', $therapist->id) }}" class="inline-block bg-[#854f38] text-white text-lg px-6 py-3 rounded-lg button">
                            {{ __('Réservez Maintenant') }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Prestations Section --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-2xl font-semibold text-[#854f38]">{{ __('Prestations') }}</h3>
                @if($prestations->count() > 0)
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($prestations as $prestation)
                            @php
                                $truncatedDescription = \Illuminate\Support\Str::limit($prestation->description, 200);
                            @endphp
                            <div class="border-2 border-[#8ea633] rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 prestation-item">
                                @if($prestation->image)
                                    <img src="{{ asset('storage/' . $prestation->image) }}" alt="{{ $prestation->name }}" class="w-full h-48 object-cover">
                                @endif
                                <div class="p-4">
                                    <h4 class="text-xl font-semibold text-[#647a0b]">{{ $prestation->name }}</h4>
                                    <h5 class="text-l font-semibold text-gray-600">{{ __('Durée:') }} {{ $prestation->duration }} {{ __('min') }}</h5>

                                    <p class="mt-2 text-gray-600 break-words prestation-description" data-full-text="{{ e($prestation->description) }}" data-truncated-text="{{ e($truncatedDescription) }}">
                                        {!! nl2br(e($truncatedDescription)) !!}
                                        @if(\Illuminate\Support\Str::length($prestation->description) > 200)
                                            <span class="text-[#854f38] cursor-pointer voir-plus">{{ __('Voir plus') }}</span>
                                        @endif
                                    </p>

                                    @if($prestation->brochure)
                                        <a href="{{ asset('storage/' . $prestation->brochure) }}" target="_blank" class="mt-4 inline-block text-[#854f38] hover:text-[#6a3f2c]">
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

            {{-- Événements Section --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-2xl font-semibold text-[#854f38]">{{ __('Événements à Venir') }}</h3>
                @if($events->count() > 0)
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($events as $event)
                            <div class="border-2 border-[#854f38] rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 event-item">
                                @if($event->image)
                                    <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->name }}" class="w-full h-48 object-cover">
                                @endif
                                <div class="p-4">
                                    <h4 class="text-xl font-semibold text-[#854f38]">{{ $event->name }}</h4>
                                    <p class="text-gray-600 mt-2">
                                        <i class="fas fa-calendar-alt mr-1 text-[#854f38]"></i> {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}
                                    </p>
                                    <p class="text-gray-600 mt-1">
                                        <i class="fas fa-map-marker-alt mr-1 text-[#854f38]"></i> {{ $event->location }}
                                    </p>
                                    @if($event->limited_spot)
                                        <p class="text-gray-600 mt-1">
                                            <i class="fas fa-users mr-1 text-[#854f38]"></i> {{ __('Places restantes:') }} {{ $event->number_of_spot - $event->reservations->count() }}
                                        </p>
                                    @endif
                                    
                                    {{-- Display price if associated product exists and price > 0 --}}
                                    @if($event->associatedProduct && $event->associatedProduct->price > 0)
                                        <p class="text-gray-600 mt-1">
                                            <i class="fas fa-tag mr-1 text-[#854f38]"></i> {{ __('Prix:') }} {{ number_format($event->associatedProduct->price_incl_tax, 2, ',', ' ') }} €
                                        </p>
                                    @endif
                                    
                                    <p class="mt-2 text-gray-600 break-words">{{ $event->description }}</p>
                                    @php
                                        $spotsLeft = $event->limited_spot ? $event->number_of_spot - $event->reservations->count() : null;
                                    @endphp
                                    @if($event->booking_required)
                                        <div class="mt-4">
                                            @if(!$event->limited_spot || ($spotsLeft > 0))
                                                <a href="{{ route('events.reserve.create', $event->id) }}" class="inline-block bg-[#854f38] text-white text-sm px-4 py-2 rounded-lg button">
                                                    {{ __('Réserver') }}
                                                </a>
                                            @else
                                                <p class="text-red-500 font-semibold">{{ __('Complet') }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-gray-600">{{ __('Aucun événement à venir pour le moment.') }}</p>
                @endif
            </div>

            {{-- Témoignages Section --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-2xl font-semibold text-[#647a0b]">{{ __('Témoignages') }}</h3>

                @if($testimonials->count() > 0)
                    <div class="mt-4 space-y-6">
                        @foreach($testimonials as $testimonial)
                            <div class="p-4 border-l-4 border-[#8ea633] bg-[#f0f8e8] rounded-md">
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

            /* Service Tags */
            .service-tag {
                transition: transform 0.3s ease, background-color 0.3s ease;
                cursor: pointer;
            }
            .service-tag:hover {
                transform: translateY(-5px);
                background-color: #8ea633; /* Vert Secondaire */
            }

            /* Prestations Styling */
            .prestation-item::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 5px;
                height: 100%;
                background-color: #8ea633; /* Vert Secondaire */
            }

            /* Events Styling */
            .event-item::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 5px;
                height: 100%;
                background-color: #854f38; /* Marron Foncé */
            }
            .event-item h4::after {
                content: '';
                width: 50px;
                height: 3px;
                background-color: #854f38; /* Marron Foncé */
                display: block;
                margin-top: 5px;
                border-radius: 2px;
            }

            /* Button Styling */
            .button {
                transition: background-color 0.3s ease, transform 0.3s ease;
            }
            .button:hover {
                background-color: #6a3f2c; /* Teinte plus foncée au survol */
                transform: translateY(-2px);
            }

            /* Responsive adjustments */
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
                .md\:text-left {
                    text-align: center;
                }
                .md\:ml-8 {
                    margin-left: 0;
                }
            }
        </style>
    @endpush

    {{-- Scripts for Animations and "Voir plus" functionality --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Fade-in Animation
                const sections = document.querySelectorAll('.bg-white');
                sections.forEach((section, index) => {
                    section.style.animationDelay = `${index * 0.2}s`;
                    section.classList.add('fade-in');
                });

                // "Voir plus" functionality for Prestations
                const descriptions = document.querySelectorAll('.prestation-description');

                descriptions.forEach(function(description) {
                    const voirPlus = description.querySelector('.voir-plus');
                    if (voirPlus) {
                        voirPlus.addEventListener('click', function() {
                            const fullText = description.getAttribute('data-full-text');
                            description.innerHTML = fullText.replace(/\n/g, '<br>') + ' <span class="text-[#854f38] cursor-pointer voir-moins">{{ __("Voir moins") }}</span>';
                        });
                    }

                    description.addEventListener('click', function(event) {
                        if (event.target.classList.contains('voir-moins')) {
                            const truncatedText = description.getAttribute('data-truncated-text');
                            let voirPlusText = '';
                            if (description.getAttribute('data-full-text').length > 200) {
                                voirPlusText = ' <span class="text-[#854f38] cursor-pointer voir-plus">{{ __("Voir plus") }}</span>';
                            }
                            description.innerHTML = truncatedText.replace(/\n/g, '<br>') + voirPlusText;
                        }

                        if (event.target.classList.contains('voir-plus')) {
                            const fullText = description.getAttribute('data-full-text');
                            description.innerHTML = fullText.replace(/\n/g, '<br>') + ' <span class="text-[#854f38] cursor-pointer voir-moins">{{ __("Voir moins") }}</span>';
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
