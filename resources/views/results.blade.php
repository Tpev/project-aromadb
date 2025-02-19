<x-app-layout>
  <!-- Head Slot -->
  <x-slot name="head">
    <meta name="description" content="Résultats de recherche pour les praticiens en médecines douces">
    <style>
      /* Custom button styles */
      .btn {
        font-weight: 600;
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 9999px;
        transition: background-color 0.3s ease;
      }
      .btn-primary {
        background-color: #647a0b;
        color: #fff;
      }
      .btn-primary:hover {
        background-color: #8ea633;
      }
      [x-cloak] { display: none !important; }
    </style>
  </x-slot>

  <!-- Header Slot -->
  <x-slot name="header">
    <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
      {{ __('Résultats de recherche') }}
    </h2>
  </x-slot>

  <!-- Page Content -->
  <div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Results Count -->
      <div class="mb-8">
        <h3 class="text-xl text-[#647a0b]">
          {{ $therapists->count() }} {{ __('praticien(s) trouvé(s)') }}
        </h3>
      </div>

      <!-- Therapist Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($therapists as $therapist)
          <!-- Vertical Therapist Card -->
          <div class="bg-white shadow-xl rounded-xl overflow-hidden transform hover:-translate-y-1 transition-all duration-300 flex flex-col">
            <!-- Header Banner -->
            <div class="relative h-40 bg-[#647a0b]">
              @if($therapist->verified ?? true)
                <div class="absolute top-2 right-2">
                  <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                    Vérifié
                  </span>
                </div>
              @endif
            </div>
            <!-- Profile Image (overlapping the banner) -->
            <div class="relative flex justify-center -mt-10">
              <img class="w-20 h-20 rounded-full border-4 border-white object-cover"
                   src="{{ $therapist->profile_picture ? asset('storage/' . $therapist->profile_picture) : 'https://via.placeholder.com/150' }}"
                   alt="{{ $therapist->name }}">
            </div>
            <!-- Card Details -->
            <div class="flex flex-col flex-grow px-4 pt-2 pb-4">
              <div class="text-center">
                <h4 class="text-xl font-bold text-[#647a0b]">{{ $therapist->name }}</h4>
                @if($therapist->company_name)
                  <p class="text-sm text-[#647a0b]">{{ $therapist->company_name }}</p>
                @endif
              </div>
              <!-- Specialty Badge -->
              <div class="mt-2 text-center">
                @if(isset($therapist->profile_description))
                  <span class="inline-block bg-[#647a0b] text-white text-xs px-3 py-1 rounded-full">
                    {{ $therapist->profile_description }}
                  </span>
                @endif
              </div>
			<p class="mt-4 text-sm text-[#647a0b] text-center">
			   {!! Str::limit($therapist->about ?? __('Informations à propos non disponibles.'), 100) !!}
			</p>

              <!-- Rating and Call-to-Action -->
              <div class="mt-4 flex flex-col items-center space-y-2">
                <div class="flex items-center space-x-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.24 3.807a1 1 0 00.95.69h4.012c.969 0 1.371 1.24.588 1.81l-3.244 2.353a1 1 0 00-.364 1.118l1.24 3.807c.3.921-.755 1.688-1.54 1.118l-3.244-2.353a1 1 0 00-1.175 0l-3.244 2.353c-.785.57-1.84-.197-1.54-1.118l1.24-3.807a1 1 0 00-.364-1.118L2.343 9.234c-.783-.57-.38-1.81.588-1.81h4.012a1 1 0 00.95-.69l1.24-3.807z"/>
                  </svg>
                  <span class="text-sm text-[#647a0b]">
                    {{ number_format($therapist->rating ?? 0, 1) }}
                  </span>
                </div>
                <a href="{{ route('therapist.show', $therapist->slug) }}" class="btn btn-primary text-xs w-full">
                  Voir profil
                </a>
              </div>
            </div>
          </div>
        @empty
          <div class="col-span-full text-center">
            <p class="text-xl text-[#647a0b]">{{ __('Aucun praticien trouvé.') }}</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>

  <!-- Footer Slot -->
  <x-slot name="footer">
    @include('layouts.footer')
  </x-slot>
        <style>
      /* Custom button styles */
      .btn {
        font-weight: 600;
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 9999px;
        transition: background-color 0.3s ease;
      }
      .btn-primary {
        background-color: #647a0b;
        color: #fff;
      }
      .btn-primary:hover {
        background-color: #8ea633;
      }
      .btn-secondary {
        background-color: #a96b56;
        color: #fff;
      }
      .btn-secondary:hover {
        background-color: #854f38;
      }
      [x-cloak] { display: none !important; }
    </style>
</x-app-layout>
