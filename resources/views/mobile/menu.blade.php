@php
    $mobileReady = [
        ['label' => 'Tableau de bord', 'icon' => 'fa-home', 'route' => 'mobile.dashboard', 'badge' => 'Mobile'],
        ['label' => 'Rendez-vous', 'icon' => 'fa-calendar-alt', 'route' => 'mobile.appointments.index', 'badge' => 'Mobile'],
        ['label' => 'Clients', 'icon' => 'fa-user-friends', 'route' => 'mobile.clients.index', 'badge' => 'Mobile'],
        ['label' => 'Factures & devis', 'icon' => 'fa-file-invoice', 'route' => 'mobile.invoices.index', 'badge' => 'Mobile'],
        ['label' => 'Recherche praticien', 'icon' => 'fa-search-location', 'route' => 'mobile.search.index', 'badge' => 'Mobile'],
    ];

    $workspace = [
        ['label' => 'Prestations', 'icon' => 'fa-spa', 'route' => 'mobile.products.index', 'badge' => 'Mobile'],
        ['label' => 'Disponibilites', 'icon' => 'fa-clock', 'route' => 'mobile.availabilities.index', 'badge' => 'Mobile'],
        ['label' => 'Lieux de pratique', 'icon' => 'fa-map-marker-alt', 'route' => 'mobile.practice-locations.index', 'badge' => 'Mobile'],
        ['label' => 'Questionnaires', 'icon' => 'fa-clipboard-list', 'route' => 'mobile.questionnaires.index', 'badge' => 'Mobile'],
        ['label' => 'Evenements', 'icon' => 'fa-calendar-plus', 'route' => 'mobile.events.index', 'badge' => 'Mobile'],
        ['label' => 'Suivi des mesures', 'icon' => 'fa-chart-line', 'route' => 'mobile.clients.index', 'badge' => 'Client'],
        ['label' => 'Notes de seance', 'icon' => 'fa-notes-medical', 'route' => 'mobile.clients.index', 'badge' => 'Client'],
        ['label' => 'Documents clients', 'icon' => 'fa-folder-open', 'route' => 'mobile.documents.index', 'badge' => 'Mobile'],
        ['label' => 'Emargements', 'icon' => 'fa-signature', 'route' => 'mobile.emargements.index', 'badge' => 'Mobile'],
    ];

    $business = [
        ['label' => 'Recettes', 'icon' => 'fa-receipt', 'route' => 'mobile.receipts.index', 'badge' => 'Mobile'],
        ['label' => 'Stock', 'icon' => 'fa-boxes', 'route' => 'mobile.inventory.index', 'badge' => 'Mobile'],
        ['label' => 'Entreprises', 'icon' => 'fa-building', 'route' => 'mobile.corporate-clients.index', 'badge' => 'Mobile'],
        ['label' => 'Packs', 'icon' => 'fa-layer-group', 'route' => 'mobile.packs.index', 'badge' => 'Mobile'],
        ['label' => 'Bons cadeaux', 'icon' => 'fa-gift', 'route' => 'mobile.gift-vouchers.index', 'badge' => 'Mobile'],
        ['label' => 'Factures recues', 'icon' => 'fa-file-import', 'route' => 'mobile.received-invoices.index', 'badge' => 'Mobile'],
    ];

    $growth = [
        ['label' => 'Formations digitales', 'icon' => 'fa-graduation-cap', 'route' => 'mobile.digital-trainings.index', 'badge' => 'Mobile'],
        ['label' => 'Communautes', 'icon' => 'fa-comments', 'route' => 'mobile.communities.index', 'badge' => 'Mobile'],
        ['label' => 'Audiences', 'icon' => 'fa-users', 'route' => 'mobile.audiences.index', 'badge' => 'Mobile'],
        ['label' => 'Newsletters', 'icon' => 'fa-envelope-open-text', 'route' => 'mobile.newsletters.index', 'badge' => 'Mobile'],
        ['label' => 'Avis Google', 'icon' => 'fa-star', 'route' => 'mobile.google-reviews.index', 'badge' => 'Mobile'],
        ['label' => 'Parrainage', 'icon' => 'fa-handshake', 'route' => 'mobile.referrals.index', 'badge' => 'Mobile'],
        ['label' => 'Profil', 'icon' => 'fa-user-cog', 'route' => 'mobile.profile.index', 'badge' => 'Mobile'],
        ['label' => 'Abonnement', 'icon' => 'fa-id-card', 'route' => 'mobile.subscription.index', 'badge' => 'Mobile'],
    ];

    $sections = [
        'Mobile' => $mobileReady,
        'Espace pro' => $workspace,
        'Gestion' => $business,
        'Croissance' => $growth,
    ];
@endphp

<x-mobile-layout :title="__('Menu')">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-end justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Menu</h1>
                <p class="text-xs text-gray-500">Tous les modules AromaMade PRO</p>
            </div>

            <a href="{{ route('mobile.dashboard') }}"
               class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-[#647a0b] shadow-sm"
               aria-label="{{ __('Retour au tableau de bord') }}">
                <i class="fas fa-home text-xs"></i>
            </a>
        </div>

        <div class="space-y-5">
            @foreach($sections as $sectionTitle => $items)
                <section class="space-y-2">
                    <h2 class="px-1 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                        {{ $sectionTitle }}
                    </h2>

                    <div class="grid grid-cols-2 gap-2">
                        @foreach($items as $item)
                            @php
                                $isMobile = str_starts_with($item['route'], 'mobile.');
                                $href = \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#';
                            @endphp

                            <a href="{{ $href }}"
                               class="min-h-[92px] rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99]">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                                        <i class="fas {{ $item['icon'] }} text-xs"></i>
                                    </span>

                                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $isMobile ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' : 'border-gray-200 bg-gray-50 text-gray-500' }}">
                                        {{ $item['badge'] ?? ($isMobile ? 'Mobile' : 'Web') }}
                                    </span>
                                </div>

                                <div class="mt-3 text-sm font-semibold leading-snug text-gray-900">
                                    {{ $item['label'] }}
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-mobile-layout>
