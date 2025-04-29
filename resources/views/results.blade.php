@php
    if(isset($specialty) && isset($region)) {
        $pageTitle = "Les meilleurs " . ucfirst(str_replace('-', ' ', $specialty)) . " dans la région " . ucfirst(str_replace('-', ' ', $region)) . " | AromaMade Pro";
        $pageDescription = "Trouvez un(e) " . ucfirst(str_replace('-', ' ', $specialty)) . " dans la région " . ucfirst(str_replace('-', ' ', $region)) . " sur AromaMade Pro. Des praticiens certifiés pour votre bien-être.";
        $canonicalUrl = url("/practicien-{$specialty}-region-{$region}");
    } elseif(isset($specialty)) {
        $pageTitle = "Les meilleurs " . ucfirst(str_replace('-', ' ', $specialty)) . " | AromaMade Pro";
        $pageDescription = "Découvrez les praticiens spécialisés en " . ucfirst(str_replace('-', ' ', $specialty)) . " sur AromaMade Pro. Consultez leurs profils et avis pour choisir le meilleur.";
        $canonicalUrl = url("/practicien-{$specialty}");
    } elseif(isset($region)) {
        $pageTitle = "Les praticiens en région " . ucfirst(str_replace('-', ' ', $region)) . " | AromaMade Pro";
        $pageDescription = "Recherchez des praticiens en région " . ucfirst(str_replace('-', ' ', $region)) . " sur AromaMade Pro. Des professionnels certifiés pour vous accompagner dans votre bien-être.";
        $canonicalUrl = url("/region-{$region}");
    } else {
        $pageTitle = "Résultats de recherche de praticiens | AromaMade Pro";
        $pageDescription = "Trouvez les meilleurs praticiens en médecines douces. Recherchez par spécialité et localisation pour consulter des professionnels certifiés du bien-être.";
        $canonicalUrl = url("/therapeutes");
    }
@endphp

{{-- Set the meta description and page title for the base layout --}}
@section('title', $pageTitle)
@section('meta_description', $pageDescription)

<x-app-layout>
    <!-- Head Slot: Additional meta tags that complement the base layout -->
    <x-slot name="head">
        <link rel="canonical" href="{{ $canonicalUrl }}" />
        <meta name="theme-color" content="#5ad4db"/>
        <link rel="manifest" href="/manifest.json"/>
        <link rel="apple-touch-icon" href="{{ asset('images/app/icon-apple.png') }}">
        <meta name="apple-mobile-web-app-title" content="AromaMade Pro">
        <meta name="apple-mobile-web-app-capable" content="yes"/>
        <meta property="og:locale" content="fr_FR" />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="{{ $pageTitle }}" />
        <meta property="og:description" content="{{ $pageDescription }}" />
        <meta property="og:url" content="{{ $canonicalUrl }}" />
        <meta property="og:site_name" content="AromaMade Pro" />
        <meta property="og:image" content="{{ asset('images/og-image.jpg') }}" />
        <meta property="og:image:secure_url" content="{{ asset('images/og-image.jpg') }}" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:description" content="{{ $pageDescription }}" />
        <meta name="twitter:title" content="{{ $pageTitle }}" />
        <meta name="twitter:site" content="@AromaMade Pro" />
        <meta name="twitter:image" content="{{ asset('images/og-image.jpg') }}" />
        <meta name="twitter:creator" content="@AromaMade Pro" />

        <!-- Font Awesome CDN -->
        <link 
            rel="stylesheet" 
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
            integrity="sha512-pVgOFDHlfxgzlRfVWYW52IGgh3FQxF71+oR4U77wCQuQ0+NfjVul2Oo+5hC5R9fGhO+I3Ff9Nd36/6V6G4a2ug==" 
            crossorigin="anonymous" 
            referrerpolicy="no-referrer"
        />

        <!-- Custom Styles (keeping your color scheme) -->
        <style>
            /* Buttons */
            .btn {
                font-weight: 600;
                border: none;
                padding: 0.5rem 1.5rem;
                border-radius: 9999px;
                transition: background-color 0.3s ease;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.4rem;
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
            [x-cloak] { 
                display: none !important; 
            }
        </style>
    </x-slot>

    <!-- Header Slot -->
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight text-center md:text-left">
            Résultats de recherche
        </h2>
    </x-slot>

<!-- Dynamic H1 Section -->
@php
    if (isset($specialty) && isset($region)) {
        $h1Text = "Trouvez les meilleurs " . ucfirst(str_replace('-', ' ', $specialty)) . " dans la région " . ucfirst(str_replace('-', ' ', $region));
    } elseif (isset($specialty)) {
        $h1Text = "Trouvez les meilleurs " . ucfirst(str_replace('-', ' ', $specialty));
    } elseif (isset($region)) {
        $h1Text = "Trouvez les praticiens dans la région " . ucfirst(str_replace('-', ' ', $region));
    } else {
        // Fallback when no criteria are provided
        $h1Text = "Trouvez les meilleurs praticiens en médecines douces";
    }
@endphp

<div class="py-4 px-4 sm:px-6 lg:px-8">
    <h1 class="font-bold text-center mb-4 text-3xl md:text-5xl" style="color: #647a0b;">
        <i class="fas fa-trophy mr-2"></i> {{ $h1Text }}
    </h1>
</div>


<!-- Dynamic H2 Section -->
@php
    if (isset($specialty) && isset($region)) {
        $spec = ucfirst(str_replace('-', ' ', $specialty));
        $reg  = ucfirst(str_replace('-', ' ', $region));
        $h2Text = "Annuaire de {$spec} dans la {$reg} : consultez les prix et avis, posez vos questions et prenez rendez-vous. Recherchez un bon {$spec} sérieux à proximité autour de moi.";
    } elseif (isset($specialty)) {
        $spec = ucfirst(str_replace('-', ' ', $specialty));
        $h2Text = "Annuaire de {$spec} : consultez les prix et avis, posez vos questions et prenez rendez-vous. Recherchez un bon {$spec} sérieux à proximité autour de moi.";
    } elseif (isset($region)) {
        $reg = ucfirst(str_replace('-', ' ', $region));
        $h2Text = "Annuaire des praticiens dans la {$reg} : consultez les prix et avis, posez vos questions et prenez rendez-vous.";
    } else {
        $h2Text = "Annuaire des praticiens en médecines douces : consultez les prix et avis, posez vos questions et prenez rendez-vous.";
    }
@endphp

<div class="py-2 px-4 sm:px-6 lg:px-8">
    <h2 class="font-semibold text-center mb-8 text-xl md:text-2xl" style="color: #854f38;">
        <i class="fas fa-address-book mr-2"></i> {{ $h2Text }}
    </h2>
</div>


        <!-- Search Form Container -->
        <div class="flex justify-center mb-8 px-4">
          <div class="w-full max-w-3xl bg-white border border-gray-200 rounded-lg sm:rounded-full p-3 sm:p-6 shadow-xl">
            <form action="{{ route('therapists.search') }}" method="POST" class="flex flex-col sm:flex-row gap-4 w-full">
              @csrf
              <!-- Specialty Dropdown (with Autocomplete) -->
              <div class="flex-1">
                <label for="specialty" class="sr-only">Spécialité</label>
                <input 
                  type="text" 
                  name="specialty" 
                  id="specialty" 
                  class="w-full rounded-full border-gray-300 shadow-sm focus:ring-[#647a0b] focus:border-[#647a0b] px-4 py-2" 
                  placeholder="Spécialité" 
                  list="specialties"
                >
                <datalist id="specialties">
                  <!-- Populated dynamically by JavaScript -->
                </datalist>
              </div>

   <!-- Location Autocomplete -->
              <div class="flex-1">
                <label for="location" class="sr-only">Lieu</label>
                <input 
                  type="text"
                  name="location"
                  id="location"
                  class="w-full rounded-full border-gray-300 shadow-sm focus:ring-[#647a0b] focus:border-[#647a0b] px-4 py-2"
                  placeholder="Lieu (ville ou région)"
                  list="regions" <!-- new datalist for regions -->
                
                <datalist id="regions">
                  <!-- Populated by JS for regions -->
                </datalist>
              </div>


              <!-- Search Button with Icon -->
              <div class="flex-shrink-0 flex items-center">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">
                  <i class="fas fa-search"></i>
                  <span>Rechercher</span>
                </button>
              </div>
            </form>
          </div>
        </div>

    <!-- Page Content -->
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Results Count -->
            <div class="mb-8">
                <h3 class="text-xl text-[#647a0b]">
                    {{ $therapists->count() }} praticien(s) trouvé(s)
                </h3>
            </div>

            <!-- Therapist Cards Grid -->
				<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
					@forelse($therapists as $therapist)
						<div class="flex flex-col bg-white shadow-xl rounded-xl overflow-hidden transform hover:-translate-y-1 transition-all duration-300">


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
                            <img 
                                class="w-32 h-32 rounded-full border-4 border-white object-cover"
                                src="{{ $therapist->profile_picture ? asset('storage/' . $therapist->profile_picture) : 'https://via.placeholder.com/150' }}"
                                alt="{{ $therapist->name }}"
                            >
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

                            <!-- Specialty Badges -->
                            <div class="mt-2 text-center">
                                @if(isset($therapist->services))
                                    @php
                                        $services = is_array($therapist->services) ? $therapist->services : json_decode($therapist->services, true);
                                    @endphp
                                    @if($services)
                                        @foreach($services as $service)
                                            <span class="inline-block bg-[#647a0b] text-white text-xs px-3 py-1 rounded-full m-1">
                                                {{ $service }}
                                            </span>
                                        @endforeach
                                    @endif
                                @endif
                            </div>

                            <!-- Short Description -->
                            <p class="mt-4 text-sm text-[#647a0b] text-center">
                                {!! Str::limit($therapist->about ?? 'Informations à propos non disponibles.', 100) !!}
                            </p>

                            <!-- Rating and Call-to-Action -->
                            <div class="mt-4 flex flex-col items-center space-y-2">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-comment-alt text-[#647a0b]"></i>
                                    <span class="text-sm text-[#647a0b]">
                                        {{ $therapist->testimonials()->count() }} témoignage(s)
                                    </span>
                                </div>
                                <a href="{{ route('therapist.show', $therapist->slug) }}" class="btn btn-primary text-xs w-full md:w-auto">
                                    Voir profil
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center">
                        <p class="text-xl text-[#647a0b]">Aucun praticien trouvé.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
  <style>
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
    <!-- Autocomplete Script -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const specialtyInput = document.getElementById('specialty');
      const dataList = document.getElementById('specialties');

      specialtyInput.addEventListener('input', function () {
        const term = this.value.trim();

        if (term.length > 0) {
          fetch('{{ route('autocomplete.specialties') }}?term=' + encodeURIComponent(term))
            .then(response => response.json())
            .then(data => {
              // Clear previous options
              dataList.innerHTML = '';

              // Populate the datalist with new options
              data.forEach(item => {
                const option = document.createElement('option');
                option.value = item;
                dataList.appendChild(option);
              });
            })
            .catch(error => {
              console.error('Error fetching specialties:', error);
            });
        } else {
          // Clear if input is empty
          dataList.innerHTML = '';
        }
      });
    });
	
	      // ========== REGION AUTOCOMPLETE ==========
      const regionInput = document.getElementById('location');
      const regionsDataList = document.getElementById('regions');

      regionInput.addEventListener('input', function () {
        const term = this.value.trim();

        if (term.length > 0) {
          fetch('{{ route('autocomplete.regions') }}?term=' + encodeURIComponent(term))
            .then(response => response.json())
            .then(data => {
              // Clear existing options
              regionsDataList.innerHTML = '';

              // Populate new options
              data.forEach(item => {
                const option = document.createElement('option');
                option.value = item;
                regionsDataList.appendChild(option);
              });
            })
            .catch(error => {
              console.error('Error fetching regions:', error);
            });
        } else {
          regionsDataList.innerHTML = '';
        }
      });

  </script>
    <!-- Footer Slot -->
    <x-slot name="footer">
        @include('layouts.footer')
    </x-slot>
</x-app-layout>
