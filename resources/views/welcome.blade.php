<x-app-layout>
  {{-- ========================  SEO  ======================== --}}
  @section('title', 'Trouvez un thérapeute près de chez vous | AromaMade')
  @section('meta_description')
    Réservez en ligne avec des thérapeutes certifiés (naturopathie, sophrologie, ostéopathie…). Profils vérifiés, avis, tarifs, prise de RDV simple. Espace Client pour partager vos documents en toute sécurité. Événements & ateliers organisés par nos membres.
  @endsection

  {{-- Head extras: Canonical, Social --}}
  <x-slot name="head">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="index,follow">

    {{-- Social --}}
    <meta property="og:site_name" content="AromaMade">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Trouvez un thérapeute près de chez vous | AromaMade">
    <meta property="og:description" content="Réservez avec des praticiens vérifiés. Avis, tarifs, Espace Client sécurisé.">
    <meta property="og:image" content="{{ asset('images/og-home.webp') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Trouvez un thérapeute près de chez vous | AromaMade">
    <meta name="twitter:description" content="Prise de RDV simple, profils vérifiés, Espace Client sécurisé.">
    <meta name="twitter:image" content="{{ asset('images/og-home.webp') }}">

    <style>
      /* Utility clamps for titles/excerpts */
      .line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
      .line-clamp-3{display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
    </style>

    {{-- JSON-LD: WebSite + SearchAction (therapists) --}}
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "AromaMade",
      "url": "{{ url('/') }}",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "{{ route('therapists.search') }}?specialty={specialty}&location={location}",
        "query-input": [
          "required name=specialty",
          "required name=location"
        ]
      }
    }
    </script>
  </x-slot>

  {{-- ========================  HERO + PRIMARY SEARCH  ======================== --}}
  <section class="relative bg-cover bg-center" style="background-image:url('{{ asset('images/hero-background.webp') }}')">
    <div class="absolute inset-0 bg-black/45"></div>
    <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20 text-center">
      <img src="{{ asset('images/white-logo.png') }}" alt="AromaMade - Plateforme de prise de rendez-vous bien-être"
           class="mx-auto w-[200px] sm:w-[260px] md:w-[320px] mb-5 md:mb-7" loading="lazy">

      <h1 class="text-white text-3xl sm:text-4xl md:text-5xl font-extrabold leading-tight">
        Trouvez votre thérapeute et réservez en quelques clics
      </h1>
      <p class="text-white/90 text-base sm:text-lg md:text-xl mt-3 md:mt-4">
        Naturopathe, sophrologue, ostéopathe… Profils vérifiés, avis et tarifs. <span class="font-semibold">Espace Client</span> pour partager vos documents en toute sécurité.
      </p>

      {{-- Search Card (no "mode", mobile-first) --}}
      <div class="mt-6 md:mt-8 max-w-5xl mx-auto bg-white/95 backdrop-blur rounded-2xl shadow-2xl p-4 sm:p-5">
        <form action="{{ route('therapists.search') }}" method="POST"
              class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
          @csrf
          {{-- Specialty --}}
          <div>
            <label for="specialty" class="block text-xs sm:text-sm font-semibold text-[#647a0b] mb-1">
              Spécialité
            </label>
            <input type="text" id="specialty" name="specialty" list="specialties"
                   class="w-full rounded-xl border-gray-300 focus:ring-[#647a0b] focus:border-[#647a0b] px-4 py-3"
                   placeholder="Ex. naturopathie, sophrologie">
            <datalist id="specialties"></datalist>
          </div>

          {{-- Location --}}
          <div>
            <label for="location" class="block text-xs sm:text-sm font-semibold text-[#647a0b] mb-1">
              Lieu
            </label>
            <input type="text" id="location" name="location" list="regions"
                   class="w-full rounded-xl border-gray-300 focus:ring-[#647a0b] focus:border-[#647a0b] px-4 py-3"
                   placeholder="Ville, code postal ou région">
            <datalist id="regions"></datalist>
          </div>

          {{-- Submit --}}
          <div class="flex items-end">
            <button type="submit"
                    class="w-full inline-flex items-center justify-center bg-[#647a0b] hover:bg-[#576a0a] text-white font-semibold rounded-xl px-6 py-3 shadow-lg transition active:scale-[0.99]">
              Rechercher
            </button>
          </div>
        </form>

        {{-- Quick chips --}}
        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs sm:text-sm">
          <span class="text-gray-600">Populaires :</span>
          @php $popular = ['Naturopathie', 'Sophrologie', 'Ostéopathie', 'Hypnose', 'Massage bien-être']; @endphp
          @foreach($popular as $label)
            <a href="{{ route('therapists.search') }}?specialty={{ urlencode($label) }}"
               class="px-3 py-1 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 transition">{{ $label }}</a>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  {{-- ========================  TRUST STRIP  ======================== --}}
  <section class="bg-[#f8f8f8] py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white rounded-xl p-4 shadow">
        <p class="font-semibold text-[#647a0b]">Praticiens vérifiés</p>
        <p class="text-sm text-gray-600">Diplômes & profils revus par notre équipe.</p>
      </div>
      <div class="bg-white rounded-xl p-4 shadow">
        <p class="font-semibold text-[#647a0b]">Réservation rapide</p>
        <p class="text-sm text-gray-600">Créneaux en cabinet, à domicile ou en visio.</p>
      </div>
      <div class="bg-white rounded-xl p-4 shadow">
        <p class="font-semibold text-[#647a0b]">Données sécurisées</p>
        <p class="text-sm text-gray-600">Confidentialité & chiffrement renforcés.</p>
      </div>
    </div>
  </section>

 {{-- ========================  FEATURED THERAPISTS  ======================== --}}
@if(isset($featuredTherapists) && $featuredTherapists->count())
  <section class="py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl md:text-3xl font-bold text-[#647a0b]">Praticiens à la une</h2>
        <a href="{{ route('nos-practiciens') }}" class="text-[#854f38] hover:underline font-semibold">Voir tout</a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($featuredTherapists as $therapist)
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
                @php
                  // services can be JSON or array; normalize to array
                  $servicesRaw = $therapist->services ?? [];
                  $services = is_array($servicesRaw) ? $servicesRaw : (json_decode($servicesRaw, true) ?: []);
                @endphp
                @if(!empty($services))
                  @foreach($services as $service)
                    <span class="inline-block bg-[#647a0b] text-white text-xs px-3 py-1 rounded-full m-1">
                      {{ $service }}
                    </span>
                  @endforeach
                @endif
              </div>

              @php
                $aboutPlain = strip_tags($therapist->about ?? 'Informations à propos non disponibles.');
              @endphp

              <p class="mt-4 text-sm text-[#647a0b] text-center">
                {{ Str::limit($aboutPlain, 100) }}
              </p>

              <!-- Rating / Testimonials and CTA -->
              <div class="mt-4 flex flex-col items-center space-y-2">
                <div class="flex items-center space-x-2 text-[#647a0b] text-sm">
                  <i class="fas fa-comment-alt"></i>
                  <span>{{ $therapist->testimonials()->count() }} témoignage(s)</span>

                  @if(!is_null($therapist->average_rating))
                    <span class="inline-flex items-center gap-1">
                      • <strong>{{ number_format($therapist->average_rating, 1) }}</strong>★
                    </span>
                  @endif
                </div>

                <a href="{{ route('therapist.show', $therapist->slug) }}" class="btn btn-primary text-xs w-full md:w-auto">
                  Voir profil
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endif


  {{-- ========================  HOW IT WORKS + ESPACE CLIENT  ======================== --}}
  <section class="bg-[#f9fafb] py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-2xl md:text-3xl font-bold text-center text-[#647a0b]">Comment ça marche ?</h2>
      <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl shadow p-6">
          <h3 class="font-semibold text-lg text-[#647a0b]">1. Recherchez</h3>
          <p class="text-gray-600">Saisissez une spécialité et un lieu pour afficher les praticiens disponibles.</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-6">
          <h3 class="font-semibold text-lg text-[#647a0b]">2. Comparez</h3>
          <p class="text-gray-600">Lisez les profils, avis et tarifs pour choisir le bon accompagnement.</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-6">
          <h3 class="font-semibold text-lg text-[#647a0b]">3. Réservez</h3>
          <p class="text-gray-600">
            Confirmez votre créneau. Votre <strong>Espace Client</strong> est créé pour partager documents, formulaires et notes en toute confidentialité.
          </p>
        </div>
      </div>
    </div>
  </section>

  {{-- ========================  EVENTS / WORKSHOPS (Members organized)  ======================== --}}
  <section class="py-12 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h2 class="text-2xl md:text-3xl font-bold text-[#854f38]">
          Événements & Ateliers des membres
        </h2>
        <p class="text-gray-600 max-w-2xl">
          Découvrez les prochains <strong>événements, ateliers et stages</strong> organisés par nos praticiens membres : conférences, initiations, découvertes de pratiques…
        </p>
      </div>

      @if(isset($events) && $events->count())
        <div class="mt-6 flex overflow-x-auto space-x-4 pb-2 -mx-1 px-1">
          @foreach($events as $event)
            <div class="flex-shrink-0 w-72 sm:w-80 bg-white rounded-2xl border border-gray-100 shadow hover:shadow-lg transition overflow-hidden">
              @if($event->image)
                <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->name }}" class="w-full h-40 object-cover" loading="lazy">
              @endif
              <div class="p-5 flex flex-col h-[260px]">
                <h3 class="text-lg font-semibold text-[#854f38] line-clamp-2">{{ $event->name }}</h3>
                <p class="mt-2 text-gray-600 text-sm">
                  {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y \à H:i') }}
                </p>
                <p class="text-gray-600 text-sm">{{ $event->location }}</p>

                @if($event->user)
                  <p class="text-gray-600 text-sm">
                    Organisé par
                    <a href="{{ route('therapist.show', $event->user->slug) }}" class="text-[#647a0b] underline">
                      {{ $event->user->name }}
                    </a>
                  </p>
                @endif

                @php
                  $spotsLeft = $event->limited_spot ? $event->number_of_spot - $event->reservations->count() : null;
                @endphp
                @if($event->limited_spot)
                  <p class="text-gray-600 text-sm">
                    Places restantes : {{ max($spotsLeft,0) }}
                  </p>
                @endif
                @if($event->associatedProduct && $event->associatedProduct->price > 0)
                  <p class="text-gray-600 text-sm">
                    Prix : {{ number_format($event->associatedProduct->price_incl_tax, 2, ',', ' ') }} €
                  </p>
                @endif

                <div x-data="{ open:false }" class="mt-2 text-sm text-gray-700">
                  <p x-show="!open">{{ Str::limit(strip_tags($event->description), 80) }}</p>
                  <p x-show="open">{{ strip_tags($event->description) }}</p>
                  @if(strlen(strip_tags($event->description)) > 80)
                    <button @click="open = !open" class="text-[#854f38] underline mt-1">
                      <span x-show="!open">Lire plus</span><span x-show="open">Réduire</span>
                    </button>
                  @endif
                </div>

                <div class="mt-auto pt-3">
                  @if($event->booking_required)
                    @if(!$event->limited_spot || ($spotsLeft > 0))
                      <a href="{{ route('events.reserve.create', $event->id) }}"
                         class="inline-block bg-[#854f38] hover:bg-[#6a3f2c] text-white text-sm px-5 py-2 rounded-full transition">
                        S’inscrire
                      </a>
                    @else
                      <span class="text-red-500 font-semibold text-sm">Complet</span>
                    @endif
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <p class="mt-4 text-gray-600">Aucun événement à venir pour le moment.</p>
      @endif
    </div>
  </section>

  {{-- ========================  CONTENT HUB (BLOG)  ======================== --}}
  @if(isset($blogPosts))
    <section class="py-12 bg-[#f9fafb]">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl md:text-3xl font-bold text-[#6a3f2c]">Conseils & Articles</h2>
          <a href="{{ route('blog.index') }}" class="text-[#854f38] hover:underline font-semibold">Tous les articles</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          @forelse($blogPosts as $post)
            <a href="{{ route('blog.show', $post->slug) }}"
               class="bg-white rounded-2xl border border-gray-100 shadow hover:shadow-lg transition overflow-hidden">
              <img src="{{ asset('images/'.$post->slug.'.webp') }}" alt="{{ $post->Title }}" class="w-full h-44 object-cover" loading="lazy">
              <div class="p-5">
                @if($post->Tags)
                  <span class="inline-block bg-[#647a0b] text-white text-xs px-3 py-1 rounded-full">{{ $post->Tags }}</span>
                @endif
                <h3 class="mt-2 text-lg sm:text-xl font-semibold text-[#854f38] line-clamp-2">{{ $post->Title }}</h3>
                <p class="mt-1 text-gray-600 line-clamp-3">{{ Str::limit(strip_tags($post->Contents), 110) }}</p>
                <span class="mt-3 inline-flex items-center text-[#647a0b] font-semibold">
                  Lire l’article →
                </span>
              </div>
            </a>
          @empty
            <p class="text-gray-600">Aucun article disponible.</p>
          @endforelse
        </div>
      </div>
    </section>
  @endif

  {{-- ========================  PRO CTA (Therapists only)  ======================== --}}
  <section class="bg-gradient-to-r from-[#854f38] to-[#6a3f2c] py-12 text-white text-center">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-2xl md:text-3xl font-bold">Vous êtes thérapeute ? Rejoignez AromaMade PRO</h2>
      <p class="mt-3 text-white/90">
        Agenda en ligne, téléconsultation, dossiers clients, facturation, questionnaires, rappels — <strong>sans commission</strong>.
      </p>
      <a href="{{ route('prolanding') }}"
         class="inline-block mt-6 bg-white text-[#6a3f2c] font-semibold px-6 py-3 rounded-full shadow hover:shadow-lg transition">
        Découvrir l’Espace PRO
      </a>
    </div>
  </section>

  {{-- ========================  SEO / ABOUT + FAQ  ======================== --}}
  <section class="py-12 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
      <div>
        <h2 class="text-2xl md:text-3xl font-bold text-[#647a0b]">AromaMade, votre guide bien-être</h2>
        <p class="mt-3 text-gray-700">
          Notre mission : rendre l’accès aux médecines douces simple, fiable et transparent. Nous centralisons un
          annuaire de praticiens vérifiés et des contenus pédagogiques (huiles essentielles, végétales, tisanes,
          recettes, articles) pour vous aider à faire des choix éclairés.
        </p>
        <p class="mt-3 text-gray-700">
          Réservez votre rendez-vous en toute sérénité, comparez les profils, découvrez les avis, et utilisez votre
          <strong>Espace Client</strong> pour partager vos informations avec votre thérapeute en toute confidentialité.
        </p>
        <a href="{{ route('welcome') }}" class="mt-4 inline-flex items-center text-[#854f38] font-semibold hover:underline">
          En savoir plus →
        </a>
      </div>

      <div class="bg-[#f9fafb] rounded-2xl shadow p-6">
        <h3 class="text-xl font-semibold text-[#647a0b]">Questions fréquentes</h3>
        <div class="mt-4 divide-y">
          <details class="py-3">
            <summary class="cursor-pointer font-medium text-[#647a0b]">
              Comment vérifiez-vous les praticiens ?
            </summary>
            <p class="mt-2 text-gray-600">Contrôle manuel des diplômes/certifications et revues régulières des profils.</p>
          </details>
          <details class="py-3">
            <summary class="cursor-pointer font-medium text-[#647a0b]">
              L’Espace Client est-il inclus ?
            </summary>
            <p class="mt-2 text-gray-600">Oui, il est créé automatiquement après votre première réservation.</p>
          </details>
          <details class="py-3">
            <summary class="cursor-pointer font-medium text-[#647a0b]">
              Mes données sont-elles protégées ?
            </summary>
            <p class="mt-2 text-gray-600">Oui. Nous appliquons des mesures strictes de sécurité et ne revendons jamais vos informations.</p>
          </details>
        </div>

        {{-- FAQ JSON-LD --}}
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "FAQPage",
          "mainEntity": [{
            "@type": "Question",
            "name": "Comment vérifiez-vous les praticiens ?",
            "acceptedAnswer": {"@type":"Answer","text":"Contrôle manuel des diplômes et certifications, avec revues régulières des profils."}
          },{
            "@type": "Question",
            "name": "L’Espace Client est-il inclus ?",
            "acceptedAnswer": {"@type":"Answer","text":"Oui. Il est créé automatiquement après votre première réservation pour partager documents et formulaires."}
          },{
            "@type": "Question",
            "name": "Mes données sont-elles protégées ?",
            "acceptedAnswer": {"@type":"Answer","text":"Oui, nous appliquons des mesures strictes de sécurité et ne revendons jamais vos informations."}
          }]
        }
        </script>
      </div>
    </div>
  </section>

  {{-- ========================  FOOTER SLOT  ======================== --}}
  <x-slot name="footer">
    @include('layouts.footer')
  </x-slot>

  {{-- ========================  AUTOCOMPLETE ONLY (no global search)  ======================== --}}
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Specialties autocomplete
      const specialtyInput = document.getElementById('specialty');
      const specialtiesList = document.getElementById('specialties');
      specialtyInput?.addEventListener('input', function () {
        const term = this.value.trim();
        if (!term) { specialtiesList.innerHTML = ''; return; }
        fetch('{{ route('autocomplete.specialties') }}?term=' + encodeURIComponent(term))
          .then(r => r.json())
          .then(data => {
            specialtiesList.innerHTML = '';
            data.forEach(item => {
              const opt = document.createElement('option');
              opt.value = item;
              specialtiesList.appendChild(opt);
            });
          }).catch(()=>{});
      });

      // Regions autocomplete
      const regionInput = document.getElementById('location');
      const regionsList = document.getElementById('regions');
      regionInput?.addEventListener('input', function () {
        const term = this.value.trim();
        if (!term) { regionsList.innerHTML = ''; return; }
        fetch('{{ route('autocomplete.regions') }}?term=' + encodeURIComponent(term))
          .then(r => r.json())
          .then(data => {
            regionsList.innerHTML = '';
            data.forEach(item => {
              const opt = document.createElement('option');
              opt.value = item;
              regionsList.appendChild(opt);
            });
          }).catch(()=>{});
      });
    });
  </script>
</x-app-layout>
