<nav x-data="{ open: false }" class="bg-white shadow relative z-40" aria-label="Main navigation">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16 items-center">

      <!-- Left Section: Logo and Primary Menu -->
      <div class="flex">
        <!-- Logo -->
        <div class="flex-shrink-0 flex items-center">
          <a href="{{ url('/') }}">
            <x-application-logo />
          </a>
        </div>

        <!-- Desktop Menu -->
        <div class="hidden sm:ml-6 sm:flex sm:space-x-8 items-center">

          <!-- Ressources Dropdown -->
          <div x-data="{ dropdown: false }"
               class="relative"
               x-cloak
               @keydown.escape.window="dropdown=false">

            <button
              @click="dropdown = !dropdown"
              type="button"
              class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-[#647a0b] focus:outline-none"
              aria-haspopup="true"
              :aria-expanded="dropdown.toString()"
              aria-controls="resources-menu">
              Ressources
              <i class="fas fa-chevron-down ml-1 align-middle"></i>
            </button>

            <!-- Dropdown Panel -->
            <div
              x-show="dropdown"
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 translate-y-1"
              x-transition:enter-end="opacity-100 translate-y-0"
              x-transition:leave="transition ease-in duration-150"
              x-transition:leave-start="opacity-100 translate-y-0"
              x-transition:leave-end="opacity-0 translate-y-1"
              @click.outside="dropdown=false"
              @click.stop
              id="resources-menu"
              class="absolute left-0 mt-2 w-64 bg-white shadow-lg rounded-md py-2 z-50 pointer-events-auto">

              <a href="{{ route('formation3') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                Développer votre activité de praticien
              </a>
              <a href="{{ route('guides.sales-funnel-practitioner') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                Comprendre le tunnel de vente
              </a>
              <a href="{{ route('guides.lead-magnet-practitioner') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                Idées de ressources gratuites
              </a>
            </div>
          </div>

          <!-- Olithea (Fonctionnalités) Dropdown -->
          <div x-data="{ proOpen:false }"
               class="relative"
               x-cloak
               @keydown.escape.window="proOpen=false">
            <button
              @click="proOpen = !proOpen"
              type="button"
              class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-[#647a0b] focus:outline-none"
              aria-haspopup="true"
              :aria-expanded="proOpen.toString()"
              aria-controls="pro-menu">
              Olithea
              <i class="fas fa-chevron-down ml-1 align-middle"></i>
            </button>

            <div
              x-show="proOpen"
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 translate-y-1"
              x-transition:enter-end="opacity-100 translate-y-0"
              x-transition:leave="transition ease-in duration-150"
              x-transition:leave-start="opacity-100 translate-y-0"
              x-transition:leave-end="opacity-0 translate-y-1"
              @click.outside="proOpen=false"
              @click.stop
              id="pro-menu"
              class="absolute left-0 mt-2 w-72 bg-white shadow-lg rounded-md py-2 z-50 pointer-events-auto">

              <a href="{{ url('/fonctionnalites') }}" class="block px-4 py-2 text-gray-800 font-semibold hover:bg-gray-100">
                Fonctionnalités
              </a>

              <div class="border-t border-gray-200 my-2"></div>

              <a href="{{ url('/fonctionnalites/agenda') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Agenda & réservation</a>
              <a href="{{ url('/fonctionnalites/dossiers-clients') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Dossiers clients</a>
              <a href="{{ url('/fonctionnalites/facturation') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Facturation</a>
              <a href="{{ route('features.e-invoicing') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Facturation électronique</a>
              <a href="{{ url('/fonctionnalites/questionnaires') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Questionnaires</a>
              <a href="{{ url('/fonctionnalites/portail-pro') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Portail Pro</a>
              <a href="{{ url('/fonctionnalites/paiements') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Paiements</a>
              <a href="{{ route('features.offer-journeys') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Parcours d’offre</a>
            </div>
          </div>

          <!-- Trouver un thérapeute -->
          <a href="{{ route('nos-practiciens') }}"
             class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-[#647a0b] focus:outline-none">
            Trouver un praticien
          </a>
        </div>
      </div>

      <!-- Right Section: Auth & CTA -->
      <div class="flex items-center">
        <a href="{{ route('prolanding') }}"
           class="hidden sm:inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full text-white bg-[#647a0b] hover:bg-[#8ea633] focus:outline-none">
          Vous êtes praticien ?
        </a>

        @guest
          <a href="{{ route('login') }}" class="ml-4 text-sm text-gray-500 hover:text-[#647a0b]">Se Connecter</a>
          <a href="{{ route('register-pro') }}" class="ml-4 text-sm text-gray-500 hover:text-[#647a0b]">S'inscrire</a>
        @else
          <a href="{{ route('logout') }}"
             onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
             class="ml-4 text-sm text-gray-500 hover:text-[#647a0b]">Se déconnecter</a>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        @endguest

        <!-- Mobile Menu Button -->
        <div class="-mr-2 flex sm:hidden">
          <button @click="open = !open"
                  type="button"
                  class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-[#647a0b] hover:bg-gray-100 focus:outline-none"
                  aria-label="Toggle navigation"
                  :aria-expanded="open.toString()">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
              <path :class="{'hidden': open, 'inline-flex': !open}"
                    class="inline-flex"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
              <path :class="{'hidden': !open, 'inline-flex': open}"
                    class="hidden"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

    </div>
  </div>

  <!-- Mobile Menu -->
  <div x-show="open"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 -translate-y-1"
       x-transition:enter-end="opacity-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 translate-y-0"
       x-transition:leave-end="opacity-0 -translate-y-1"
       class="sm:hidden relative z-30 bg-white">
    <div class="pt-2 pb-3 space-y-1">

      <a href="{{ route('formation3') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">Développer votre activité de praticien</a>
      <a href="{{ route('guides.sales-funnel-practitioner') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">Comprendre le tunnel de vente</a>
      <a href="{{ route('guides.lead-magnet-practitioner') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">Idées de ressources gratuites</a>

      <!-- Olithea (mobile collapsible) -->
      <div x-data="{ proMobileOpen:false }" class="border-t border-gray-200 pt-2">
        <button
          @click="proMobileOpen = !proMobileOpen"
          class="w-full text-left pl-3 pr-4 py-2 text-base font-medium text-gray-700 hover:text-[#647a0b] hover:bg-gray-50 flex items-center justify-between">
          Olithea
          <i class="fas fa-chevron-down ml-2" :class="{'rotate-180': proMobileOpen}"></i>
        </button>
        <div x-show="proMobileOpen" x-collapse>
          <a href="{{ url('/fonctionnalites') }}" class="block pl-6 pr-4 py-2 text-base font-medium text-gray-700 hover:text-[#647a0b] hover:bg-gray-50">Fonctionnalités</a>
          <a href="{{ url('/fonctionnalites/agenda') }}" class="block pl-6 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">Agenda & réservation</a>
          <a href="{{ url('/fonctionnalites/dossiers-clients') }}" class="block pl-6 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">Dossiers clients</a>
          <a href="{{ url('/fonctionnalites/facturation') }}" class="block pl-6 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">Facturation</a>
          <a href="{{ route('features.e-invoicing') }}" class="block pl-6 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">Facturation électronique</a>
          <a href="{{ url('/fonctionnalites/questionnaires') }}" class="block pl-6 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">Questionnaires</a>
          <a href="{{ url('/fonctionnalites/portail-pro') }}" class="block pl-6 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">Portail Pro</a>
          <a href="{{ url('/fonctionnalites/paiements') }}" class="block pl-6 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">Paiements</a>
          <a href="{{ route('features.offer-journeys') }}" class="block pl-6 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">Parcours d’offre</a>
        </div>
      </div>

      <a href="{{ route('nos-practiciens') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">Trouver un praticien</a>

      <a href="{{ route('prolanding') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-white bg-[#647a0b] hover:bg-[#8ea633]">
        Vous êtes un praticien ?
      </a>

      @guest
        <a href="{{ route('login') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">Se Connecter</a>
        <a href="{{ route('register-pro') }}" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">S'inscrire</a>
      @else
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:text-[#647a0b] hover:bg-gray-50">Se déconnecter</a>
        <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
      @endguest
    </div>
  </div>
</nav>
