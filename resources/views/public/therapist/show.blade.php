{{-- resources/views/public/therapist/show.blade.php --}}
<x-app-layout>
    {{-- En-tête de la page --}}
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-[#647a0b] leading-tight">
            {{ $therapist->business_name }}
        </h2>
    </x-slot>

    {{-- Contenu principal --}}
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-12">
            
            {{-- Section Hero --}}
            <div class="bg-[#8ea633] shadow-lg rounded-lg p-8 flex flex-col md:flex-row items-center">
                @if($therapist->profile_picture)
                    <img src="{{ asset('storage/' . $therapist->profile_picture) }}" alt="{{ __('Photo de Profil') }}" class="w-48 h-48 rounded-full object-cover border-4 border-white shadow-md">
                @else
                    <div class="w-48 h-48 rounded-full bg-white flex items-center justify-center text-[#8ea633] text-4xl font-bold">
                        {{ strtoupper(substr($therapist->company_name, 0, 1)) }}
                    </div>
                @endif
                <div class="mt-6 md:mt-0 md:ml-8 text-center md:text-left w-full">
                    <h1 class="text-3xl md:text-5xl font-bold text-white break-words whitespace-normal px-4">
                        {{ $therapist->company_name }}
                    </h1>
                    <p class="mt-4 text-xl text-white leading-relaxed">
                        {{ $therapist->profile_description ?? '' }}
                    </p>
                    {{-- Bouton d'appel à l'action --}}
                    @if($therapist->accept_online_appointments)
                        <div class="mt-6">
                            <a href="{{ route('appointments.createPatient', $therapist->id) }}" class="inline-block bg-white text-[#8ea633] font-semibold text-lg px-8 py-3 rounded-full hover:bg-[#e8f0d8] transition-colors duration-300">
                                {{ __('Prendre Rendez-vous') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Section À Propos --}}
            <div class="bg-[#f9fafb] shadow rounded-lg p-8">
                <h3 class="text-3xl font-semibold text-[#647a0b] flex items-center">
                    <i class="fas fa-info-circle text-[#854f38] mr-3"></i> {{ __('À Propos') }}
                </h3>
                <p class="mt-6 text-gray-700 text-lg leading-relaxed">
                    {{ $therapist->about ?? __('Informations à propos non disponibles.') }}
                </p>
            </div>

            {{-- Section Services --}}
            <div class="bg-white shadow rounded-lg p-8">
                <h3 class="text-3xl font-semibold text-[#647a0b] flex items-center">
                    <i class="fas fa-concierge-bell text-[#854f38] mr-3"></i> {{ __('Services') }}
                </h3>
                
                {{-- Decode services JSON string into an array --}}
                @php
                    $services = json_decode($therapist->services, true) ?? [];
                @endphp

                @if(is_array($services) && count($services) > 0)
                    <div class="mt-6 flex flex-wrap gap-3">
                        @foreach($services as $service)
                            <span class="bg-[#647a0b] text-white px-4 py-2 rounded-full text-sm font-medium service-tag">
                                {{ $service }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="mt-6 text-gray-600">{{ __('Aucun service spécifié.') }}</p>
                @endif
            </div>

            {{-- Section Contact --}}
            <div class="bg-[#f9fafb] shadow rounded-lg p-8">
                <h3 class="text-3xl font-semibold text-[#647a0b] flex items-center">
                    <i class="fas fa-address-book text-[#854f38] mr-3"></i> {{ __('Contact') }}
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mt-6">

                    @if($therapist->share_address_publicly)
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-2xl text-[#854f38] mr-4 mt-1"></i>
                            <div>
                                <h4 class="text-xl font-semibold text-[#647a0b]">{{ __('Adresse') }}</h4>
                                <p class="text-gray-700 mt-2">{{ $therapist->company_address ?? __('Adresse non disponible.') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($therapist->share_phone_publicly)
                        <div class="flex items-start">
                            <i class="fas fa-phone-alt text-2xl text-[#854f38] mr-4 mt-1"></i>
                            <div>
                                <h4 class="text-xl font-semibold text-[#647a0b]">{{ __('Téléphone') }}</h4>
                                <p class="text-gray-700 mt-2">{{ $therapist->company_phone ?? __('Téléphone non disponible.') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($therapist->share_email_publicly)
                        <div class="flex items-start">
                            <i class="fas fa-envelope text-2xl text-[#854f38] mr-4 mt-1"></i>
                            <div>
                                <h4 class="text-xl font-semibold text-[#647a0b]">{{ __('Email') }}</h4>
                                <p class="text-gray-700 mt-2">
                                    <a href="mailto:{{ $therapist->company_email }}" class="text-[#854f38] hover:text-[#6a3f2c]">
                                        {{ $therapist->company_email }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Section Prestations --}}
            <div class="bg-white shadow rounded-lg p-8">
                <h3 class="text-3xl font-semibold text-[#854f38] flex items-center">
                    <i class="fas fa-spa text-[#854f38] mr-3"></i> {{ __('Prestations') }}
                </h3>
                @if($prestations->count() > 0)
                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($prestations as $prestation)
                            @php
                                $truncatedDescription = \Illuminate\Support\Str::limit($prestation->description, 200);
                            @endphp
                            <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 prestation-item bg-[#f9fafb]">
                                @if($prestation->image)
                                    <img src="{{ asset('storage/' . $prestation->image) }}" alt="{{ $prestation->name }}" class="w-full h-48 object-cover">
                                @endif
                                <div class="p-6">
                                    <h4 class="text-2xl font-semibold text-[#647a0b]">{{ $prestation->name }}</h4>
                                    <p class="mt-2 text-gray-600">{{ __('Durée :') }} {{ $prestation->duration }} {{ __('min') }}</p>

                                    <p class="mt-4 text-gray-700 prestation-description" data-full-text="{{ e($prestation->description) }}" data-truncated-text="{{ e($truncatedDescription) }}">
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
                    <p class="mt-6 text-gray-600">{{ __('Aucune prestation disponible pour le moment.') }}</p>
                @endif
            </div>

            {{-- Section Événements --}}
            <div class="bg-[#f9fafb] shadow rounded-lg p-8">
                <h3 class="text-3xl font-semibold text-[#854f38] flex items-center">
                    <i class="fas fa-calendar-alt text-[#854f38] mr-3"></i> {{ __('Événements à Venir') }}
                </h3>
                @if($events->count() > 0)
                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($events as $event)
                            <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 event-item bg-white">
                                @if($event->image)
                                    <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->name }}" class="w-full h-48 object-cover">
                                @endif
                                <div class="p-6">
                                    <h4 class="text-2xl font-semibold text-[#854f38]">{{ $event->name }}</h4>
                                    <p class="mt-2 text-gray-600">
                                        <i class="fas fa-calendar-alt mr-1 text-[#854f38]"></i> {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}
                                    </p>
                                    <p class="text-gray-600 mt-1">
                                        <i class="fas fa-map-marker-alt mr-1 text-[#854f38]"></i> {{ $event->location }}
                                    </p>
                                    @if($event->limited_spot)
                                        <p class="text-gray-600 mt-1">
                                            <i class="fas fa-users mr-1 text-[#854f38]"></i> {{ __('Places restantes :') }} {{ $event->number_of_spot - $event->reservations->count() }}
                                        </p>
                                    @endif
                                    
                                    @if($event->associatedProduct && $event->associatedProduct->price > 0)
                                        <p class="text-gray-600 mt-1">
                                            <i class="fas fa-tag mr-1 text-[#854f38]"></i> {{ __('Prix :') }} {{ number_format($event->associatedProduct->price_incl_tax, 2, ',', ' ') }} €
                                        </p>
                                    @endif
                                    
                                    <p class="mt-4 text-gray-700">{{ $event->description }}</p>
                                    @php
                                        $spotsLeft = $event->limited_spot ? $event->number_of_spot - $event->reservations->count() : null;
                                    @endphp
                                    @if($event->booking_required)
                                        <div class="mt-6">
                                            @if(!$event->limited_spot || ($spotsLeft > 0))
                                                <a href="{{ route('events.reserve.create', $event->id) }}" class="inline-block bg-[#854f38] text-white text-sm px-6 py-2 rounded-full hover:bg-[#6a3f2c] transition-colors duration-300">
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
                    <p class="mt-6 text-gray-600">{{ __('Aucun événement à venir pour le moment.') }}</p>
                @endif
            </div>

            {{-- Section Témoignages --}}
            <div class="bg-white shadow rounded-lg p-8">
                <h3 class="text-3xl font-semibold text-[#647a0b] flex items-center">
                    <i class="fas fa-comments text-[#854f38] mr-3"></i> {{ __('Témoignages') }}
                </h3>

                @if($testimonials->count() > 0)
                    <div class="mt-8 space-y-6">
                        @foreach($testimonials as $testimonial)
                            <div class="p-6 border-l-4 border-[#8ea633] bg-[#f9fafb] rounded-md">
                                <p class="text-gray-700 italic text-lg">"{{ $testimonial->testimonial }}"</p>
                                <p class="mt-4 text-sm text-gray-600">
                                    — {{ $testimonial->clientProfile->first_name }}, {{ $testimonial->created_at->format('d/m/Y') }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8">
                        {{ $testimonials->links() }}
                    </div>
                @else
                    <p class="mt-6 text-lg text-gray-600">
                        {{ __('Les témoignages de mes clients seront bientôt disponibles ici.') }}
                    </p>
                @endif
            </div>

        </div>
    </div>

    {{-- Styles personnalisés --}}
    @push('styles')
        <style>
            /* Styles personnalisés pour le profil du thérapeute */

            /* Service Tags */
            .service-tag {
                transition: transform 0.3s ease, background-color 0.3s ease;
                cursor: pointer;
            }
            .service-tag:hover {
                transform: translateY(-3px);
                background-color: #8ea633; /* Vert Secondaire */
            }

            /* Prestations et Événements */
            .prestation-item, .event-item {
                position: relative;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .prestation-item:hover, .event-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            }

            /* Boutons */
            .button {
                transition: background-color 0.3s ease, transform 0.3s ease;
            }
            .button:hover {
                background-color: #6a3f2c; /* Teinte plus foncée au survol */
                transform: translateY(-2px);
            }

            /* Animation d'apparition */
            .fade-in {
                opacity: 0;
                transform: translateY(20px);
                animation: fadeIn 0.8s forwards;
            }

            @keyframes fadeIn {
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Appliquer l'animation aux sections */
            .bg-white, .bg-[#f9fafb] {
                animation: fadeIn 0.8s forwards;
            }

            /* Ajustements responsives */
            @media (max-width: 768px) {
                .md\:flex-row {
                    flex-direction: column;
                }
                .md\:mt-0 {
                    margin-top: 1.5rem;
                }
                .md\:ml-8 {
                    margin-left: 0;
                }
                .md\:text-left {
                    text-align: center;
                }
            }
        </style>
    @endpush

    {{-- Scripts pour les animations et la fonctionnalité "Voir plus" --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Définir les textes "Voir plus" et "Voir moins"
                const voirPlusText = '{{ __("Voir plus") }}';
                const voirMoinsText = '{{ __("Voir moins") }}';

                // Animation d'apparition
                const sections = document.querySelectorAll('.bg-white, .bg-\\[\\#f9fafb\\]');
                sections.forEach((section, index) => {
                    section.style.animationDelay = `${index * 0.2}s`;
                    section.classList.add('fade-in');
                });

                // Fonctionnalité "Voir plus" pour les Prestations
                const descriptions = document.querySelectorAll('.prestation-description');

                descriptions.forEach(function(description) {
                    description.addEventListener('click', function(event) {
                        if (event.target.classList.contains('voir-plus')) {
                            const fullText = description.getAttribute('data-full-text');
                            description.innerHTML = fullText.replace(/\n/g, '<br>') + ` <span class="text-[#854f38] cursor-pointer voir-moins">${voirMoinsText}</span>`;
                        } else if (event.target.classList.contains('voir-moins')) {
                            const truncatedText = description.getAttribute('data-truncated-text');
                            let voirPlusSpan = '';
                            if (description.getAttribute('data-full-text').length > 200) {
                                voirPlusSpan = ` <span class="text-[#854f38] cursor-pointer voir-plus">${voirPlusText}</span>`;
                            }
                            description.innerHTML = truncatedText.replace(/\n/g, '<br>') + voirPlusSpan;
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
