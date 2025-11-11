<nav class="app-tabbar fixed inset-x-0 bottom-0 z-50 hidden bg-white/95 border-t border-gray-200 backdrop-blur
            safe-bottom">
  <ul class="flex justify-between items-stretch text-sm">
    <li><a href="{{ route('dashboard') }}" class="tab-item {{ request()->routeIs('dashboard')?'active':'' }}">🏠<span>Accueil</span></a></li>
    <li><a href="{{ route('client_profiles.index') }}" class="tab-item {{ request()->is('clients*')?'active':'' }}">👤<span>Clients</span></a></li>
    <li><a href="{{ route('appointments.index') }}" class="tab-item {{ request()->is('rendez-vous*')?'active':'' }}">🗓️<span>Agenda</span></a></li>
    <li><a href="{{ route('invoices.index') }}" class="tab-item {{ request()->is('factures*')?'active':'' }}">🧾<span>Factures</span></a></li>
    
  </ul>
</nav>
