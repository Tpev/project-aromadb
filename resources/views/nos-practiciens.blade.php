<x-app-layout>
  <!-- Head Slot -->
  @section('title', 'Annuaire Thérapeute en Ligne | Trouvez votre praticien de médecine douce')
  @section('meta_description')
Découvrez notre annuaire thérapeute dédié aux médecines douces. Sophrologie, naturopathie, ostéopathie : trouvez le professionnel certifié idéal pour votre bien-être
  @endsection

  <x-slot name="head">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" 
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
          integrity="sha512-pVgOFDHlfxgzlRfVWYW52IGgh3FQxF71+oR4U77wCQuQ0+NfjVul2Oo+5hC5R9fGhO+I3Ff9Nd36/6V6G4a2ug==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer" />
  </x-slot>

  <!-- Header Slot -->
  <x-slot name="header">
    <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
      {{ __('Nos Praticiens') }}
    </h2>
  </x-slot>

  <!-- PAGE CONTENT -->

  <!-- HERO SECTION -->
  <section class="relative bg-gradient-to-r from-green-50 to-white overflow-hidden h-screen" 
           x-data="{ showHero: false }" 
           x-init="setTimeout(() => showHero = true, 200)">
    <div class="absolute inset-0">
      <img src="{{ asset('images/hero-practicien.webp') }}" 
           alt="Hero Practicien" 
           class="w-full h-full object-cover opacity-20">
      <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col items-center justify-center relative z-10">
      <div class="text-center" 
           x-show="showHero" 
           x-transition 
           x-transition.duration.500ms>
        <h1 class="text-4xl sm:text-5xl font-bold mb-4 text-[#647a0b]">
          Trouvez votre praticien en médecine douce
        </h1>
        <p class="max-w-3xl mx-auto text-lg sm:text-xl mb-8 text-[#647a0b]">
          Sophrologie, naturopathie, ostéopathie… Parcourez notre sélection rigoureuse de professionnels du bien-être pour prendre soin de vous en toute sérénité.
        </p>

        <!-- Search Form Container -->
        <div class="flex justify-center mb-8 px-4">
          <div class="w-full max-w-3xl bg-white border border-gray-200 rounded-lg sm:rounded-full p-3 sm:p-6 shadow-xl">
            <form action="{{ route('therapists.search') }}" method="POST" class="flex flex-col sm:flex-row gap-4 w-full">
              @csrf

              <!-- Name (NEW: fuzzy by name & company_name) -->
              <div class="flex-1">
                <label for="name" class="sr-only">Nom</label>
                <input
                  type="text"
                  name="name"
                  id="name"
                  class="w-full rounded-full border-gray-300 shadow-sm focus:ring-[#647a0b] focus:border-[#647a0b] px-4 py-2"
                  placeholder="Nom du praticien (ex. Marie Dupont ou Cabinet Harmonie)"
                  value="{{ old('name', request('name')) }}"
                >
              </div>

              <!-- Specialty Dropdown (with Autocomplete) -->
              <div class="flex-1">
                <label for="specialty" class="sr-only">Spécialité</label>
                <input 
                  type="text" 
                  name="specialty" 
                  id="specialty" 
                  class="w-full rounded-full border-gray-300 shadow-sm focus:ring-[#647a0b] focus:border-[#647a0b] px-4 py-2" 
                  placeholder="Spécialité (ex. Naturopathie, Sophrologie)" 
                  list="specialties"
                  value="{{ old('specialty', request('specialty')) }}"
                >
                <datalist id="specialties">
                  <!-- Populated dynamically by JavaScript -->
                </datalist>
              </div>

              <!-- Location Autocomplete (FIXED closing tag) -->
              <div class="flex-1">
                <label for="location" class="sr-only">Lieu</label>
                <input 
                  type="text"
                  name="location"
                  id="location"
                  class="w-full rounded-full border-gray-300 shadow-sm focus:ring-[#647a0b] focus:border-[#647a0b] px-4 py-2"
                  placeholder="Lieu (ville ou région)"
                  list="regions"
                  value="{{ old('location', request('location')) }}"
                >
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
      </div>
    </div>
  </section>

  <!-- TRUST / REASSURANCE SECTION with FontAwesome icons -->
  <section class="py-12 bg-[#f8f8f8]" 
           x-data="{ showTrust: false }" 
           x-init="setTimeout(() => showTrust = true, 400)">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8" 
           x-show="showTrust" 
           x-transition.duration.700ms 
           x-transition:enter.scale.95 
           x-transition:leave.scale.90>
        <!-- Card #1: Praticiens Vérifiés -->
        <div class="bg-white shadow-lg rounded-xl p-6 flex items-center space-x-4 hover:shadow-2xl transition-shadow duration-300">
          <div class="text-5xl text-[#647a0b]">
            <i class="fas fa-check-circle"></i>
          </div>
          <div>
            <h3 class="text-xl font-semibold mb-2 text-[#647a0b]">
              Praticiens Vérifiés
            </h3>
            <p class="text-[#647a0b]">
              Chaque thérapeute est minutieusement sélectionné et vérifié pour garantir votre sécurité et votre bien-être.
            </p>
          </div>
        </div>
        <!-- Card #2: Disponibilité & Flexibilité -->
        <div class="bg-white shadow-lg rounded-xl p-6 flex items-center space-x-4 hover:shadow-2xl transition-shadow duration-300">
          <div class="text-5xl text-[#647a0b]">
            <i class="fas fa-calendar-alt"></i>
          </div>
          <div>
            <h3 class="text-xl font-semibold mb-2 text-[#647a0b]">
              Disponibilité & Flexibilité
            </h3>
            <p class="text-[#647a0b]">
              Prenez rendez-vous en quelques clics, que ce soit en cabinet, à domicile ou en téléconsultation, selon vos disponibilités.
            </p>
          </div>
        </div>
        <!-- Card #3: Sécurité & Confidentialité -->
        <div class="bg-white shadow-lg rounded-xl p-6 flex items-center space-x-4 hover:shadow-2xl transition-shadow duration-300">
          <div class="text-5xl text-[#647a0b]">
            <i class="fas fa-lock"></i>
          </div>
          <div>
            <h3 class="text-xl font-semibold mb-2 text-[#647a0b]">
              Sécurité & Confidentialité
            </h3>
            <p class="text-[#647a0b]">
              Vos données sont protégées avec le plus grand soin, sans aucune commission sur vos consultations.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ABOUT US SECTION -->
  <section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
      <div class="relative" x-data="{ reveal: false }" x-init="setTimeout(() => reveal = true, 200)">
         <img src="{{ asset('images/verif.webp') }}" 
             alt="Nos praticiens"
             class="w-full rounded-xl shadow-2xl transform transition duration-700"
             :class="{ 'translate-x-0 opacity-100': reveal, 'translate-x-10 opacity-0': !reveal }">
        <div class="absolute bottom-0 right-0 bg-[#854f38] text-white px-4 py-2 rounded-tl-lg shadow-lg">
          100% praticiens
        </div>
      </div>
      <div class="space-y-6">
        <h2 class="text-3xl font-bold text-[#647a0b]">
          Pourquoi choisir nos praticiens ?
        </h2>
        <p class="text-xl text-[#647a0b]">
          Nous mettons en relation des experts en médecines douces pour vous offrir un accompagnement personnalisé et de qualité. Grâce à un processus rigoureux de sélection, nous vous garantissons des professionnels compétents et passionnés par le bien-être.
        </p>
        <p class="text-xl text-[#647a0b]">
          Que vous recherchiez une thérapie manuelle, un accompagnement en sophrologie ou une approche naturopathique, notre réseau est à votre écoute pour trouver la solution qui vous correspond.
        </p>
        <a href="#" class="inline-block mt-4 bg-[#647a0b] text-white px-6 py-3 rounded-full shadow hover:bg-[#8ea633] transition">
          En savoir plus
        </a>
      </div>
    </div>
  </section>

  <!-- ARTICLES / BLOG SECTION -->
  <section class="py-12 bg-[#f8f8f8]" 
           x-data="{ revealBlog: false }" 
           x-init="setTimeout(() => revealBlog = true, 400)">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-[#6a3f2c]">
          Actualités & Conseils Bien-Être
        </h2>
        <a href="{{ route('blog.index') }}" class="text-[#854f38] hover:text-[#6a3f2c] font-semibold flex items-center">
          Voir tous les articles
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 ml-1" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 5l7 7-7 7" />
          </svg>
        </a>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8" x-show="revealBlog" x-transition.duration.700ms>
        @forelse($blogPosts as $post)
          <div class="bg-white shadow-xl rounded-lg overflow-hidden hover:shadow-2xl transition-shadow duration-300">
            <img src="{{ asset('images/' . $post->slug . '.webp') }}" 
                 alt="{{ $post->Title }}" 
                 class="w-full h-48 object-cover">
            <div class="p-6 space-y-3">
              @if($post->Tags)
                <span class="inline-block bg-[#647a0b] text-white text-xs px-3 py-1 rounded-full">
                  {{ $post->Tags }}
                </span>
              @endif
              <h3 class="text-2xl font-semibold text-[#854f38]">
                {{ $post->Title }}
              </h3>
              <p class="text-xl text-[#647a0b]">
                {{ Str::limit(strip_tags($post->Contents), 100) }}
              </p>
              <a href="{{ route('blog.show', $post->slug) }}" 
                 class="text-[#854f38] font-medium hover:underline">
                Lire l’article
              </a>
            </div>
          </div>
        @empty
          <p class="text-center text-xl text-[#647a0b]">Aucun article disponible.</p>
        @endforelse
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS SECTION -->
  <section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-3xl font-bold text-center mb-8 text-[#647a0b]">
        Ils nous font confiance
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Témoignage 1 -->
        <div class="bg-gray-100 p-6 rounded-lg shadow-md">
          <p class="italic text-xl mb-4 text-[#647a0b]">
            "Grâce à cette plateforme, j'ai retrouvé le praticien qui correspondait parfaitement à mes besoins. Un service professionnel et chaleureux !"
          </p>
          <div class="flex items-center">
            <img src="https://randomuser.me/api/portraits/women/44.jpg" 
                 alt="Témoignage" 
                 class="w-12 h-12 rounded-full mr-4">
            <div>
              <p class="font-semibold text-[#647a0b]">Sophie L.</p>
              <p class="text-sm text-[#647a0b]"></p>
            </div>
          </div>
        </div>
        <!-- Témoignage 2 -->
        <div class="bg-gray-100 p-6 rounded-lg shadow-md">
          <p class="italic text-xl mb-4 text-[#647a0b]">
            "Un service impeccable, qui m'a permis de découvrir des praticiens compétents et attentionnés. Je recommande vivement !"
          </p>
          <div class="flex items-center">
            <img src="https://randomuser.me/api/portraits/men/46.jpg" 
                 alt="Témoignage" 
                 class="w-12 h-12 rounded-full mr-4">
            <div>
              <p class="font-semibold text-[#647a0b]">Marc D.</p>
              <p class="text-sm text-[#647a0b]"></p>
            </div>
          </div>
        </div>
        <!-- Témoignage 3 -->
        <div class="bg-gray-100 p-6 rounded-lg shadow-md">
          <p class="italic text-xl mb-4 text-[#647a0b]">
            "La prise de rendez-vous est simple et rapide. J'ai pu consulter un expert en naturopathie qui a transformé ma routine de santé !"
          </p>
          <div class="flex items-center">
            <img src="https://randomuser.me/api/portraits/women/65.jpg" 
                 alt="Témoignage" 
                 class="w-12 h-12 rounded-full mr-4">
            <div>
              <p class="font-semibold text-[#647a0b]">Isabelle R.</p>
              <p class="text-sm text-[#647a0b]"></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- NEWSLETTER SECTION -->
  <section class="py-16 bg-gradient-to-r from-green-100 to-white">
    <div class="max-w-xl mx-auto text-center space-y-6">
      <h2 class="text-3xl font-bold text-[#647a0b]">
        Restez connecté(e)
      </h2>
      <p class="text-xl text-[#647a0b]">
        Abonnez-vous à notre newsletter et recevez nos conseils exclusifs pour améliorer votre bien-être et rester informé(e) de nos actualités.
      </p>
      <form action="#" method="POST" class="flex flex-col sm:flex-row justify-center items-center gap-4 mt-6">
        <input type="email" 
               placeholder="Ex: jane.doe@gmail.com" 
               class="border border-gray-300 rounded-full px-4 py-2 focus:ring-[#647a0b] focus:border-[#647a0b] w-full sm:w-auto" 
               required>
        <button type="submit" class="btn btn-primary">
          S’abonner
        </button>
      </form>
      <p class="text-xs text-[#647a0b]">
        Vous pouvez vous désabonner à tout moment. Vos données sont protégées selon notre politique de confidentialité.
      </p>
    </div>
  </section>

  <!-- FAQ SECTION -->
  <section class="py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-3xl font-bold text-center mb-8 text-[#647a0b]">
        Questions Fréquentes
      </h2>
      <div class="space-y-4">
        <div x-data="{ open: false }" class="border border-gray-200 rounded-lg p-4">
          <button @click="open = !open" class="w-full text-left flex justify-between items-center focus:outline-none">
            <span class="font-semibold text-lg text-[#647a0b]">
              Comment se déroule la vérification des praticiens ?
            </span>
            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" 
                 class="h-6 w-6 text-[#647a0b]" 
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    d="M12 4v16m8-8H4"/>
            </svg>
            <svg x-show="open" xmlns="http://www.w3.org/2000/svg" 
                 class="h-6 w-6 text-[#647a0b]" 
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    d="M20 12H4"/>
            </svg>
          </button>
          <div x-show="open" x-transition class="mt-4 text-[#647a0b]">
            Chaque praticien est soumis à un contrôle strict de ses diplômes et certifications pour assurer un service de qualité.
          </div>
        </div>

        <div x-data="{ open: false }" class="border border-gray-200 rounded-lg p-4">
          <button @click="open = !open" class="w-full text-left flex justify-between items-center focus:outline-none">
            <span class="font-semibold text-lg text-[#647a0b]">
              Puis-je consulter un praticien en téléconsultation ?
            </span>
            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" 
                 class="h-6 w-6 text-[#647a0b]" 
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    d="M12 4v16m8-8H4"/>
            </svg>
            <svg x-show="open" xmlns="http://www.w3.org/2000/svg" 
                 class="h-6 w-6 text-[#647a0b]" 
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    d="M20 12H4"/>
            </svg>
          </button>
          <div x-show="open" x-transition class="mt-4 text-[#647a0b]">
            Oui, notre réseau regroupe des praticiens disponibles pour des consultations en cabinet et en téléconsultation, selon vos préférences.
          </div>
        </div>

        <div x-data="{ open: false }" class="border border-gray-200 rounded-lg p-4">
          <button @click="open = !open" class="w-full text-left flex justify-between items-center focus:outline-none">
            <span class="font-semibold text-lg text-[#647a0b]">
              Comment protéger mes données personnelles ?
            </span>
            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" 
                 class="h-6 w-6 text-[#647a0b]" 
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    d="M12 4v16m8-8H4"/>
            </svg>
            <svg x-show="open" xmlns="http://www.w3.org/2000/svg" 
                 class="h-6 w-6 text-[#647a0b]" 
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    d="M20 12H4"/>
            </svg>
          </button>
          <div x-show="open" x-transition class="mt-4 text-[#647a0b]">
            Nous utilisons des protocoles de sécurité avancés pour protéger vos informations. Vos données ne sont jamais vendues et restent strictement confidentielles.
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER SLOT -->
  <x-slot name="footer">
    @include('layouts.footer')
  </x-slot>

  <!-- Custom Styles & Alpine fallback for hidden -->
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
      [x-cloak] {
        display: none !important;
      }
  </style>

  <!-- Autocomplete Script -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const specialtyInput = document.getElementById('specialty');
      const dataList = document.getElementById('specialties');

      specialtyInput?.addEventListener('input', function () {
        const term = this.value.trim();

        if (term.length > 0) {
          fetch('{{ route('autocomplete.specialties') }}?term=' + encodeURIComponent(term))
            .then(response => response.json())
            .then(data => {
              dataList.innerHTML = '';
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
          dataList.innerHTML = '';
        }
      });
    });

    // ========== REGION AUTOCOMPLETE ==========
    const regionInput = document.getElementById('location');
    const regionsDataList = document.getElementById('regions');

    regionInput?.addEventListener('input', function () {
      const term = this.value.trim();

      if (term.length > 0) {
        fetch('{{ route('autocomplete.regions') }}?term=' + encodeURIComponent(term))
          .then(response => response.json())
          .then(data => {
            regionsDataList.innerHTML = '';
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

</x-app-layout>
