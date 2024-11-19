{{-- resources/views/public/therapist/show.blade.php --}}
<x-app-layout>
    {{-- En-tête de la page --}}
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ $therapist->business_name }}
        </h2>
    </x-slot>

    {{-- Contenu principal --}}
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Section Hero --}}
            <div class="bg-[#8ea633] shadow-lg rounded-lg p-8 flex flex-col md:flex-row items-center">
                @if($therapist->profile_picture)
                    <img src="{{ asset('storage/' . $therapist->profile_picture) }}" alt="{{ __('Photo de Profil') }}" class="w-40 h-40 rounded-full object-cover border-4 border-white shadow-md">
                @else
                    <div class="w-40 h-40 rounded-full bg-white flex items-center justify-center text-[#8ea633] text-3xl font-bold">
                        {{ strtoupper(substr($therapist->company_name, 0, 1)) }}
                    </div>
                @endif
                <div class="mt-6 md:mt-0 md:ml-8 text-center md:text-left">
                    <h1 class="text-4xl font-bold text-white">{{ $therapist->company_name }}</h1>
                    <p class="mt-4 text-lg text-white">
                        {{ $therapist->profile_description ?? '' }}
                    </p>
                    {{-- Bouton d'appel à l'action --}}
                    @if($therapist->accept_online_appointments)
                        <div class="mt-6">
                            <a href="{{ route('appointments.createPatient', $therapist->id) }}" class="inline-block bg-white text-[#8ea633] font-semibold text-lg px-6 py-3 rounded-full hover:bg-[#e8f0d8] transition-colors duration-300">
                                {{ __('Prendre Rendez-vous') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Section À Propos --}}
            @if($therapist->about)
                <div class="bg-white shadow rounded-lg p-8">
                    <h3 class="text-3xl font-semibold text-[#647a0b] mb-6">{{ __('À Propos') }}</h3>
                    <p class="text-gray-700 text-lg leading-relaxed">
                        {{ $therapist->about }}
                    </p>
                </div>
            @endif

            {{-- Section Services --}}
            @if($therapist->services)
                <div class="bg-[#f0f8e8] shadow rounded-lg p-8">
                    <h3 class="text-3xl font-semibold text-[#647a0b] mb-6">{{ __('Services') }}</h3>
                    @php
                        $services = json_decode($therapist->services, true) ?? [];
                    @endphp
                    <div class="flex flex-wrap gap-4">
                        @foreach($services as $service)
                            <span class="bg-[#647a0b] text-white px-4 py-2 rounded-full text-sm font-medium service-tag">
                                {{ $service }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Section Prestations --}}
            @if($prestations->count() > 0)
                <div class="bg-white shadow rounded-lg p-8">
                    <h3 class="text-3xl font-semibold text-[#854f38] mb-6">{{ __('Prestations') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($prestations as $prestation)
                            @php
                                $truncatedDescription = \Illuminate\Support\Str::limit($prestation->description, 200);
                            @endphp
                            <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300 prestation-item">
                                @if($prestation->image)
                                    <img src="{{ asset('storage/' . $prestation->image) }}" alt="{{ $prestation->name }}" class="w-full h-48 object-cover rounded-t-lg">
                                @endif
                                <div class="p-6">
                                    <h4 class="text-xl font-semibold text-[#647a0b]">{{ $prestation->name }}</h4>
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
                </div>
            @endif

            {{-- Section Événements --}}
            @if($events->count() > 0)
                <div class="bg-[#f0f8e8] shadow rounded-lg p-8">
                    <h3 class="text-3xl font-semibold text-[#854f38] mb-6">{{ __('Événements à Venir') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($events as $event)
                            <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300 event-item">
                                @if($event->image)
                                    <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->name }}" class="w-full h-48 object-cover rounded-t-lg">
                                @endif
                                <div class="p-6">
                                    <h4 class="text-xl font-semibold text-[#854f38]">{{ $event->name }}</h4>
                                    <p class="mt-2 text-gray-600">
                                        <i class="fas fa-calendar-alt mr-1 text-[#854f38]"></i> {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}
                                    </p>
                                    <p class="text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-1 text-[#854f38]"></i> {{ $event->location }}
                                    </p>
                                    @if($event->limited_spot)
                                        <p class="text-gray-600">
                                            <i class="fas fa-users mr-1 text-[#854f38]"></i> {{ __('Places restantes :') }} {{ $event->number_of_spot - $event->reservations->count() }}
                                        </p>
                                    @endif
                                    @if($event->associatedProduct && $event->associatedProduct->price > 0)
                                        <p class="text-gray-600">
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
                                                <a href="{{ route('events.reserve.create', $event->id) }}" class="inline-block bg-[#854f38] text-white text-sm px-6 py-2 rounded-full button">
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
                </div>
            @endif

            {{-- Section Témoignages --}}
            @if($testimonials->count() > 0)
                <div class="bg-white shadow rounded-lg p-8">
                    <h3 class="text-3xl font-semibold text-[#647a0b] mb-6">{{ __('Témoignages') }}</h3>
                    <div class="space-y-8">
                        @foreach($testimonials as $testimonial)
                            <div class="bg-[#f0f8e8] p-6 rounded-lg shadow-sm">
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
                </div>
            @endif

            {{-- Section Contact --}}
            <div class="bg-[#f0f8e8] shadow rounded-lg p-8">
                <h3 class="text-3xl font-semibold text-[#647a0b] mb-6">{{ __('Contact') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                    @if($therapist->share_address_publicly && $therapist->company_address)
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-2xl text-[#854f38] mr-4"></i>
                            <div>
                                <h4 class="text-xl font-semibold text-[#647a0b]">{{ __('Adresse') }}</h4>
                                <p class="text-gray-700 mt-2">{{ $therapist->company_address }}</p>
                            </div>
                        </div>
                    @endif
                    @if($therapist->share_phone_publicly && $therapist->company_phone)
                        <div class="flex items-start">
                            <i class="fas fa-phone-alt text-2xl text-[#854f38] mr-4"></i>
                            <div>
                                <h4 class="text-xl font-semibold text-[#647a0b]">{{ __('Téléphone') }}</h4>
                                <p class="text-gray-700 mt-2">{{ $therapist->company_phone }}</p>
                            </div>
                        </div>
                    @endif
                    @if($therapist->share_email_publicly && $therapist->company_email)
                        <div class="flex items-start">
                            <i class="fas fa-envelope text-2xl text-[#854f38] mr-4"></i>
                            <div>
                                <h4 class="text-xl font-semibold text-[#647a0b]">{{ __('Email') }}</h4>
                                <p class="text-gray-700 mt-2">
                                    <a href="mailto:{{ $therapist->company_email }}" class="underline hover:text-[#854f38]">{{ $therapist->company_email }}</a>
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- Styles personnalisés --}}
    @push('styles')
        <style>
            /* Polices personnalisées */
            body {
                font-family: 'Montserrat', sans-serif;
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

            /* Tags de service */
            .service-tag:hover {
                background-color: #6a3f2c;
            }

            /* Boutons */
            .button {
                transition: background-color 0.3s ease, transform 0.2s ease;
            }
            .button:hover {
                background-color: #6a3f2c;
                transform: translateY(-3px);
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            /* Sections */
            .bg-white, .bg-[#f0f8e8] {
                animation: fadeInUp 0.6s ease-out both;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translate3d(0, 40px, 0);
                }
                to {
                    opacity: 1;
                    transform: translate3d(0, 0, 0);
                }
            }

            /* Prestations et événements */
            .prestation-item, .event-item {
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }
            .prestation-item:hover, .event-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            }

            /* Responsivité */
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

    {{-- Scripts personnalisés --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Animation d'apparition
                const elements = document.querySelectorAll('.bg-white, .bg-[#f0f8e8]');
                elements.forEach((el, index) => {
                    el.style.animationDelay = `${index * 0.2}s`;
                    el.classList.add('fade-in');
                });

                // Fonctionnalité "Voir plus"
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
