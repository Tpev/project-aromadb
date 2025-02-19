<x-app-layout>
  <!-- Head Slot -->
  <x-slot name="head">
    <meta name="description" content="Trouvez les meilleurs praticiens en médecines douces. Recherchez par spécialité et localisation pour consulter des professionnels certifiés du bien-être.">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-pVgOFDHlfxgzlRfVWYW52IGgh3FQxF71+oR4U77wCQuQ0+NfjVul2Oo+5hC5R9fGhO+I3Ff9Nd36/6V6G4a2ug==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
              @if($therapist->verified ?? false)
                <div class="absolute top-2 right-2">
                  <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                    Vérifié
                  </span>
                </div>
              @endif
            </div>
            <!-- Profile Image (overlapping the banner) -->
            <div class="relative flex justify-center -mt-16">
              <img class="w-32 h-32 rounded-full border-4 border-white object-cover"
                   src="{{ $therapist->profile_picture ? asset('storage/' . $therapist->profile_picture) : 'https://via.placeholder.com/150' }}"
                   alt="{{ $therapist->name }}">
            </div>
            <!-- Card Details -->
            <div class="flex flex-col flex-grow px-4 pt-2 pb-4">
              <div class="text-center">
                <h4 class="text-2xl font-bold text-[#647a0b]">{{ $therapist->name }}</h4>
                @if($therapist->company_name)
                  <p class="text-sm text-[#647a0b]">{{ $therapist->company_name }}</p>
                @endif
                @if($therapist->city_setByAdmin)
                  <p class="text-sm text-[#647a0b]">
                    <i class="fas fa-map-marker-alt"></i> {{ $therapist->city_setByAdmin }}
                  </p>
                @endif
              </div>
              <!-- Specialty Badge (using profile_description as an example) -->
              <div class="mt-2 text-center">
                @if(isset($therapist->profile_description))
                  <span class="inline-block bg-[#647a0b] text-white text-xs px-3 py-1 rounded-full">
                    {{ $therapist->profile_description }}
                  </span>
                @endif
              </div>
              <!-- Short Description -->
              <p class="mt-4 text-sm text-[#647a0b] text-center">
                {!! Str::limit($therapist->about ?? __('Informations à propos non disponibles.'), 100) !!}
              </p>
              <!-- Rating and Call-to-Action -->
              <div class="mt-4 flex flex-col items-center space-y-2">
<div class="flex items-center space-x-1">
  <i class="fas fa-comment-alt text-[#647a0b]"></i>
  <span class="text-sm text-[#647a0b]">
    {{ $therapist->testimonials()->count() }} témoignages
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
