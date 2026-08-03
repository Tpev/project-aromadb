<x-app-layout>
  @section('title', 'Trouvez un praticien près de chez vous | Olithea')
  @section('meta_description')
    Réservez en ligne avec des praticiens vérifiés : naturopathie, sophrologie, ostéopathie, hypnose, massage bien-être et accompagnements. Profils, avis, tarifs, agenda en ligne et espace client sécurisé.
  @endsection

  @section('meta_og')
    <meta property="og:site_name" content="Olithea">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Trouvez un praticien près de chez vous | Olithea">
    <meta property="og:description" content="Comparez les profils, consultez les avis et réservez un rendez-vous avec un praticien vérifié.">
    <meta property="og:image" content="{{ asset('images/og-home.webp') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Trouvez un praticien près de chez vous | Olithea">
    <meta name="twitter:description" content="Prise de rendez-vous simple, profils vérifiés et espace client sécurisé.">
    <meta name="twitter:image" content="{{ asset('images/og-home.webp') }}">
  @endsection

  @push('styles')
    <style>
      .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }

      .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }

      .ol-hero {
        background-image:
          linear-gradient(90deg, rgba(24, 30, 20, .82), rgba(24, 30, 20, .54), rgba(24, 30, 20, .25)),
          url('{{ asset('images/hero-background.webp') }}');
      }

      @media (max-width: 767px) {
        .ol-hero {
          background-image:
            linear-gradient(180deg, rgba(24, 30, 20, .82), rgba(24, 30, 20, .58)),
            url('{{ asset('images/hero-background.webp') }}');
        }
      }
    </style>
  @endpush

  @section('structured_data')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "Olithea",
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
  @endsection

  <section class="ol-hero bg-cover bg-center">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">
      <div class="max-w-3xl text-white">
        <p class="inline-flex rounded-full border border-white/25 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-normal text-white/90">
          Annuaire et agenda des praticiens
        </p>

        <h1 class="mt-5 text-4xl font-bold leading-tight sm:text-5xl lg:text-6xl">
          Trouvez le praticien qui vous correspond.
        </h1>

        <p class="mt-5 max-w-2xl text-base leading-7 text-white/88 sm:text-lg">
          Naturopathie, sophrologie, ostéopathie, hypnose, massage bien-être ou accompagnement : comparez les profils, choisissez un créneau et réservez en quelques clics.
        </p>
      </div>

      <div class="mt-8 max-w-5xl rounded-lg bg-white p-4 shadow-2xl shadow-black/20 sm:p-5">
        <form action="{{ route('therapists.search') }}" method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_1fr_1fr_auto]">
          <div>
            <label for="name" class="mb-1 block text-xs font-semibold text-[#4f6208]">Nom ou cabinet</label>
            <input
              type="text"
              id="name"
              name="name"
              class="h-12 w-full rounded-lg border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"
              placeholder="Ex. Cabinet Harmonie"
              value="{{ old('name', request('name')) }}">
          </div>

          <div>
            <label for="specialty" class="mb-1 block text-xs font-semibold text-[#4f6208]">Spécialité</label>
            <input
              type="text"
              id="specialty"
              name="specialty"
              list="specialties"
              class="h-12 w-full rounded-lg border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"
              placeholder="Ex. sophrologie"
              value="{{ old('specialty', request('specialty')) }}">
            <datalist id="specialties"></datalist>
          </div>

          <div>
            <label for="location" class="mb-1 block text-xs font-semibold text-[#4f6208]">Lieu</label>
            <input
              type="text"
              id="location"
              name="location"
              list="regions"
              class="h-12 w-full rounded-lg border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"
              placeholder="Ville, code postal ou région"
              value="{{ old('location', request('location')) }}">
            <datalist id="regions"></datalist>
          </div>

          <div class="flex items-end">
            <button type="submit" class="inline-flex h-12 w-full items-center justify-center rounded-lg bg-[#647a0b] px-6 text-sm font-bold text-white shadow-sm transition hover:bg-[#536708] md:w-auto">
              Rechercher
            </button>
          </div>
        </form>

        <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
          <span class="font-semibold text-gray-500">Recherches fréquentes</span>
          @foreach(['Naturopathie', 'Sophrologie', 'Ostéopathie', 'Hypnose', 'Massage bien-être'] as $label)
            <a href="{{ route('therapists.search', ['specialty' => $label]) }}" class="rounded-full border border-[#dfe6c8] px-3 py-1 font-semibold text-[#647a0b] transition hover:border-[#647a0b] hover:bg-[#f4f7ea]">
              {{ $label }}
            </a>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  <section class="border-b border-[#e6e8dd] bg-[#f7f8f1]">
    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-px px-4 py-6 sm:grid-cols-3 sm:px-6 lg:px-8">
      <div class="px-0 py-3 sm:px-5">
        <p class="text-sm font-bold text-[#647a0b]">Profils vérifiés</p>
        <p class="mt-1 text-sm text-gray-600">Diplômes, spécialités et informations clés sont relus par notre équipe.</p>
      </div>
      <div class="border-t border-[#e1e5d6] px-0 py-3 sm:border-l sm:border-t-0 sm:px-5">
        <p class="text-sm font-bold text-[#647a0b]">Réservation claire</p>
        <p class="mt-1 text-sm text-gray-600">Créneaux en cabinet, à domicile ou en visio, avec confirmation par email.</p>
      </div>
      <div class="border-t border-[#e1e5d6] px-0 py-3 sm:border-l sm:border-t-0 sm:px-5">
        <p class="text-sm font-bold text-[#647a0b]">Espace client sécurisé</p>
        <p class="mt-1 text-sm text-gray-600">Documents, questionnaires et rendez-vous réunis dans un espace privé.</p>
      </div>
    </div>
  </section>

  @if(isset($featuredTherapists) && $featuredTherapists->count())
    <section class="bg-white py-14">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="text-xs font-bold uppercase tracking-normal text-[#8b5a42]">Sélection Olithea</p>
            <h2 class="mt-2 text-3xl font-bold text-[#2f3825]">Praticiens à la une</h2>
          </div>
          <a href="{{ route('nos-practiciens') }}" class="text-sm font-bold text-[#647a0b] hover:underline">Voir tous les praticiens</a>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
          @foreach($featuredTherapists as $therapist)
            @php
              $servicesRaw = $therapist->services ?? [];
              $services = is_array($servicesRaw) ? $servicesRaw : (json_decode($servicesRaw, true) ?: []);
              $aboutPlain = trim(strip_tags($therapist->about ?? ''));
              $testimonialCount = $therapist->testimonials()->count();
            @endphp

            <article class="group flex min-h-full flex-col overflow-hidden rounded-lg border border-[#e2e7d4] bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-[#cdd8ad] hover:shadow-xl">
              <div class="h-2 bg-[#647a0b]"></div>

              <div class="flex flex-1 flex-col p-5">
                <div class="flex items-start gap-4">
                  <img
                    class="h-20 w-20 flex-none rounded-full border-4 border-[#f4f7ea] object-cover shadow-sm"
                    src="{{ $therapist->profile_picture ? asset('storage/' . $therapist->profile_picture) : 'https://via.placeholder.com/160' }}"
                    alt="{{ $therapist->name }}"
                    loading="lazy">

                  <div class="min-w-0 flex-1">
                    <h3 class="text-xl font-bold leading-tight text-[#2f3825] line-clamp-2">{{ $therapist->name }}</h3>

                    @if($therapist->company_name)
                      <p class="mt-1 text-sm font-semibold text-[#647a0b] line-clamp-1">{{ $therapist->company_name }}</p>
                    @endif

                    @if($therapist->city_setByAdmin)
                      <p class="mt-2 inline-flex items-center rounded-full bg-[#f7f8f1] px-2.5 py-1 text-xs font-semibold text-gray-600">
                        {{ $therapist->city_setByAdmin }}
                      </p>
                    @endif
                  </div>
                </div>

                @if(!empty($services))
                  <div class="mt-5 flex flex-wrap gap-2">
                    @foreach(array_slice($services, 0, 3) as $service)
                      <span class="rounded-full bg-[#eef3de] px-3 py-1.5 text-xs font-semibold leading-snug text-[#566a09]">
                        {{ $service }}
                      </span>
                    @endforeach

                    @if(count($services) > 3)
                      <span class="rounded-full border border-[#dfe6c8] px-3 py-1.5 text-xs font-semibold text-[#647a0b]">
                        +{{ count($services) - 3 }}
                      </span>
                    @endif
                  </div>
                @endif

                <div class="mt-5 min-h-[5rem] rounded-lg bg-[#fbfcf7] p-4">
                  @if($aboutPlain !== '')
                    <p class="text-sm leading-6 text-gray-700 line-clamp-3">{{ $aboutPlain }}</p>
                  @else
                    <p class="text-sm leading-6 text-gray-500">Découvrez les disponibilités, les prestations et les informations du praticien sur son profil.</p>
                  @endif
                </div>

                <div class="mt-auto flex items-center justify-between gap-3 border-t border-[#edf0e4] pt-4">
                  <div class="text-xs font-semibold text-gray-500">
                    <span class="block text-[#2f3825]">{{ $testimonialCount }}</span>
                    <span>{{ Str::plural('témoignage', $testimonialCount) }}</span>
                  </div>

                  <a href="{{ route('therapist.show', $therapist->slug) }}" class="inline-flex items-center justify-center rounded-lg bg-[#2f3825] px-4 py-2.5 text-sm font-bold text-white transition group-hover:bg-[#647a0b]">
                    Voir le profil
                  </a>
                </div>
              </div>
            </article>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  <section class="bg-[#f7f8f1] py-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-2xl text-center">
        <p class="text-xs font-bold uppercase tracking-normal text-[#8b5a42]">Simple et direct</p>
        <h2 class="mt-2 text-3xl font-bold text-[#2f3825]">Comment ça marche ?</h2>
      </div>

      <div class="mt-8 grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="rounded-lg border border-[#e1e5d6] bg-white p-6">
          <span class="text-sm font-bold text-[#8b5a42]">01</span>
          <h3 class="mt-3 text-lg font-bold text-[#2f3825]">Recherchez</h3>
          <p class="mt-2 text-sm leading-6 text-gray-600">Filtrez par nom, spécialité ou lieu pour trouver un praticien disponible.</p>
        </div>
        <div class="rounded-lg border border-[#e1e5d6] bg-white p-6">
          <span class="text-sm font-bold text-[#8b5a42]">02</span>
          <h3 class="mt-3 text-lg font-bold text-[#2f3825]">Comparez</h3>
          <p class="mt-2 text-sm leading-6 text-gray-600">Consultez les profils, les avis, les pratiques proposées et les modalités de rendez-vous.</p>
        </div>
        <div class="rounded-lg border border-[#e1e5d6] bg-white p-6">
          <span class="text-sm font-bold text-[#8b5a42]">03</span>
          <h3 class="mt-3 text-lg font-bold text-[#2f3825]">Réservez</h3>
          <p class="mt-2 text-sm leading-6 text-gray-600">Choisissez un créneau et recevez toutes les informations utiles dans votre espace client.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="bg-white py-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="text-xs font-bold uppercase tracking-normal text-[#8b5a42]">À découvrir</p>
          <h2 class="mt-2 text-3xl font-bold text-[#2f3825]">Événements et ateliers</h2>
        </div>
        <p class="max-w-2xl text-sm leading-6 text-gray-600">
          Conférences, initiations, stages ou ateliers en ligne : découvrez les rendez-vous proposés par les praticiens membres.
        </p>
      </div>

      @if(isset($events) && $events->count())
        <div class="mt-8 grid grid-cols-1 gap-5 md:grid-cols-3">
          @foreach($events as $event)
            @php
              $spotsLeft = $event->limited_spot
                ? max($event->number_of_spot - $event->reservations->count(), 0)
                : null;
              $descPlain = trim(strip_tags((string) ($event->description ?? '')));
            @endphp

            <article class="flex min-h-full flex-col overflow-hidden rounded-lg border border-[#e5e8d8] bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
              <div class="h-44 bg-[#eef3de]">
                @if($event->image)
                  <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->name }}" class="h-full w-full object-cover" loading="lazy">
                @else
                  <div class="h-full bg-[#eef3de]"></div>
                @endif
              </div>

              <div class="flex flex-1 flex-col p-5">
                <h3 class="text-lg font-bold leading-snug text-[#2f3825] line-clamp-2">{{ $event->name }}</h3>

                <div class="mt-3 space-y-1 text-sm text-gray-600">
                  <p>{{ $event->formatted_period }}</p>
                  <p>{{ $event->formatted_duration }}</p>
                  <p>{{ $event->location }}</p>

                  @if($event->user)
                    <p>
                      Par
                      <a href="{{ route('therapist.show', $event->user->slug) }}" class="font-semibold text-[#647a0b] hover:underline">
                        {{ $event->user->name }}
                      </a>
                    </p>
                  @endif

                  @if($event->limited_spot)
                    <p>Places restantes : <strong>{{ $spotsLeft }}</strong></p>
                  @endif
                </div>

                @if($descPlain !== '')
                  <p class="mt-4 text-sm leading-6 text-gray-600 line-clamp-3">{{ $descPlain }}</p>
                @endif

                <div class="mt-auto pt-5">
                  @if($event->booking_required)
                    @if(!$event->limited_spot || ($spotsLeft > 0))
                      <a href="{{ route('events.reserve.create', $event->id) }}" class="inline-flex w-full items-center justify-center rounded-lg bg-[#8b5a42] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#734832]">
                        S'inscrire
                      </a>
                    @else
                      <p class="rounded-lg bg-red-50 px-5 py-3 text-center text-sm font-bold text-red-600">Complet</p>
                    @endif
                  @else
                    <p class="rounded-lg bg-gray-50 px-5 py-3 text-center text-sm font-semibold text-gray-500">Inscription non requise</p>
                  @endif
                </div>
              </div>
            </article>
          @endforeach
        </div>
      @else
        <p class="mt-6 rounded-lg border border-[#e5e8d8] bg-[#f7f8f1] p-5 text-sm text-gray-600">Aucun événement à venir pour le moment.</p>
      @endif
    </div>
  </section>

  <section class="bg-[#2f3825] py-14 text-white">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
      <div>
        <p class="text-sm font-semibold text-[#d8dfbd]">Pour les praticiens, coachs et accompagnants</p>
        <h2 class="mt-2 text-3xl font-bold">Développez votre activité avec Olithea.</h2>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/78">
          Agenda en ligne, dossiers clients, questionnaires, visio, facturation et visibilité en ligne réunis dans un seul espace.
        </p>
      </div>
      <a href="{{ route('prolanding') }}" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-white px-6 py-3 text-sm font-bold text-[#2f3825] transition hover:bg-[#f0f3e5]">
        Découvrir l'espace pro
      </a>
    </div>
  </section>

  <section class="bg-white py-14">
    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-8 px-4 sm:px-6 md:grid-cols-2 lg:px-8">
      <div>
        <p class="text-xs font-bold uppercase tracking-normal text-[#8b5a42]">Notre intention</p>
        <h2 class="mt-2 text-3xl font-bold text-[#2f3825]">Une recherche plus lisible, un parcours plus serein.</h2>
        <p class="mt-4 text-sm leading-7 text-gray-700">
          Olithea rassemble des praticiens aux approches complémentaires pour rendre la prise de rendez-vous plus simple, plus claire et plus fiable. Chaque profil vous aide à comprendre la pratique, les modalités et les informations utiles avant de réserver.
        </p>
        <p class="mt-3 text-sm leading-7 text-gray-700">
          Après la réservation, l'espace client permet de retrouver les détails du rendez-vous, les documents partagés et les échanges importants avec le praticien.
        </p>
      </div>

      <div class="rounded-lg border border-[#e5e8d8] bg-[#f7f8f1] p-6">
        <h3 class="text-xl font-bold text-[#2f3825]">Questions fréquentes</h3>
        <div class="mt-4 divide-y divide-[#dfe4d2]">
          <details class="py-3">
            <summary class="cursor-pointer text-sm font-bold text-[#647a0b]">Comment les praticiens sont-ils vérifiés ?</summary>
            <p class="mt-2 text-sm leading-6 text-gray-600">Notre équipe relit les informations clés du profil et les justificatifs transmis afin de garder un annuaire lisible et fiable.</p>
          </details>
          <details class="py-3">
            <summary class="cursor-pointer text-sm font-bold text-[#647a0b]">L'espace client est-il inclus ?</summary>
            <p class="mt-2 text-sm leading-6 text-gray-600">Oui. Il est créé automatiquement après une réservation afin de centraliser rendez-vous, documents et questionnaires.</p>
          </details>
          <details class="py-3">
            <summary class="cursor-pointer text-sm font-bold text-[#647a0b]">Puis-je réserver une séance en visio ?</summary>
            <p class="mt-2 text-sm leading-6 text-gray-600">Oui, lorsque le praticien propose ce mode de consultation. L'information apparaît directement sur son profil et dans le parcours de réservation.</p>
          </details>
        </div>

        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "FAQPage",
          "mainEntity": [{
            "@type": "Question",
            "name": "Comment les praticiens sont-ils vérifiés ?",
            "acceptedAnswer": {"@type":"Answer","text":"Notre équipe relit les informations clés du profil et les justificatifs transmis afin de garder un annuaire lisible et fiable."}
          },{
            "@type": "Question",
            "name": "L'espace client est-il inclus ?",
            "acceptedAnswer": {"@type":"Answer","text":"Oui. Il est créé automatiquement après une réservation afin de centraliser rendez-vous, documents et questionnaires."}
          },{
            "@type": "Question",
            "name": "Puis-je réserver une séance en visio ?",
            "acceptedAnswer": {"@type":"Answer","text":"Oui, lorsque le praticien propose ce mode de consultation. L'information apparaît directement sur son profil et dans le parcours de réservation."}
          }]
        }
        </script>
      </div>
    </div>
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const specialtyInput = document.getElementById('specialty');
      const specialtiesList = document.getElementById('specialties');

      specialtyInput?.addEventListener('input', function () {
        const term = this.value.trim();
        if (!term) {
          specialtiesList.innerHTML = '';
          return;
        }

        fetch('{{ route('autocomplete.specialties') }}?term=' + encodeURIComponent(term))
          .then(response => response.json())
          .then(data => {
            specialtiesList.innerHTML = '';
            data.forEach(item => {
              const option = document.createElement('option');
              option.value = item;
              specialtiesList.appendChild(option);
            });
          })
          .catch(() => {});
      });

      const regionInput = document.getElementById('location');
      const regionsList = document.getElementById('regions');

      regionInput?.addEventListener('input', function () {
        const term = this.value.trim();
        if (!term) {
          regionsList.innerHTML = '';
          return;
        }

        fetch('{{ route('autocomplete.regions') }}?term=' + encodeURIComponent(term))
          .then(response => response.json())
          .then(data => {
            regionsList.innerHTML = '';
            data.forEach(item => {
              const option = document.createElement('option');
              option.value = item;
              regionsList.appendChild(option);
            });
          })
          .catch(() => {});
      });
    });
  </script>
</x-app-layout>
