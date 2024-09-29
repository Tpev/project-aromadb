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
            <div class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow duration-300 text-center">
                <h1 class="text-3xl font-bold text-gray-800">{{ $therapist->company_name }}</h1>
                <p class="text-lg text-gray-600 mt-2">{{ $therapist->description ?? __('Description non disponible.') }}</p>
            </div>

            {{-- About Section --}}
            <div class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow duration-300">
                <h3 class="text-2xl font-semibold text-gray-700">{{ __('À Propos') }}</h3>
                <p class="mt-4 text-gray-600 text-lg">
                    {{ $therapist->about ?? __('Informations à propos non disponibles.') }}
                </p>
            </div>

            {{-- Services Section --}}
            <div class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow duration-300">
                <h3 class="text-2xl font-semibold text-gray-700">{{ __('Services') }}</h3>
                @if($therapist->services && count($therapist->services) > 0)
                    <ul class="mt-4 list-disc list-inside text-gray-600 text-lg">
                        @foreach($therapist->services as $service)
                            <li>{{ $service }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-600">{{ __('Aucun service spécifié.') }}</p>
                @endif
            </div>

			{{-- Contact Section --}}
			<div class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow duration-300">
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
            <div class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow duration-300 text-center">
                <h3 class="text-2xl font-semibold text-gray-700">{{ __('Prendre Rendez-vous') }}</h3>
                <p class="text-lg text-gray-600 mt-4">
                    {{ __('Contactez-moi pour prendre rendez-vous ou réservez directement en ligne.') }}
                </p>
                <div class="mt-6">
                    <a href="{{ route('appointments.create', $therapist->id) }}" class="inline-block bg-brand-green text-white text-lg px-6 py-3 rounded-lg hover:bg-opacity-90 transition-all">
                        {{ __('Réservez Maintenant') }}
                    </a>
                </div>
            </div>

            {{-- Témoignages Section --}}
            <div class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow duration-300">
                <h3 class="text-2xl font-semibold text-gray-700">{{ __('Témoignages') }}</h3>
                <p class="mt-4 text-lg text-gray-600">
                    {{ __('Les témoignages de mes clients seront bientôt disponibles ici.') }}
                </p>
            </div>

        </div>
    </div>

    {{-- Styles for Customization --}}
    @push('styles')
        <style>
            /* Custom styles for the therapist's profile */
            .hover\:text-brand-green:hover {
                color: #647a0b;
            }

            /* Shadow and hover improvements */
            .hover\:shadow-lg:hover {
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }

            /* Call to action button styles */
            .bg-brand-green {
                background-color: #647a0b;
            }

            /* Testimonial placeholder */
            .testimonial-placeholder {
                background-color: #f3f4f6;
                padding: 20px;
                border-radius: 8px;
            }
        </style>
    @endpush
</x-app-layout>
