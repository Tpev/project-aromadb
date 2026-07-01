@php
    $total = $companies->count();
    $contactsCount = $companies->sum('client_profiles_count');
    $withBillingEmail = $companies->filter(fn ($company) => filled($company->billing_email))->count();
@endphp

<x-mobile-layout title="Entreprises">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-building text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Entreprises</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Clients B2B, contacts rattaches et facturation.
                </p>
            </div>

            <a href="{{ route('mobile.menu') }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-gray-500 shadow-sm"
               aria-label="Retour au menu">
                <i class="fas fa-bars text-xs"></i>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Entreprises</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $total }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Contacts</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $contactsCount }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Email fac.</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $withBillingEmail }}</div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2">
            <a href="{{ route('mobile.corporate-clients.create') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                Ajouter
            </a>
            <a href="{{ route('corporate-clients.index') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                Vue web
            </a>
        </div>

        @if($companies->isNotEmpty())
            <div class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <label class="flex h-10 items-center gap-2 rounded-lg bg-[#f7f8f1] px-3">
                    <i class="fas fa-search text-[11px] text-gray-400"></i>
                    <input type="search"
                           id="mobileCompanySearch"
                           placeholder="Rechercher une entreprise"
                           class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 focus:ring-0"
                           oninput="filterMobileCompanies()">
                </label>
            </div>
        @endif

        @if($companies->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-building text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucune entreprise</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Ajoutez vos clients professionnels pour relier les contacts et factures.
                </p>
            </div>
        @else
            <div id="mobileCompanyList" class="space-y-2">
                @foreach($companies as $company)
                    @php
                        $contactName = trim(($company->main_contact_first_name ?? '') . ' ' . ($company->main_contact_last_name ?? ''));
                        $searchText = trim($company->name . ' ' . ($company->trade_name ?? '') . ' ' . ($company->billing_city ?? '') . ' ' . ($company->billing_email ?? '') . ' ' . $contactName);
                    @endphp

                    <a href="{{ route('mobile.corporate-clients.show', $company) }}"
                       class="block rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99]"
                       data-company="{{ Str::lower($searchText) }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-semibold text-gray-900">
                                    {{ $company->name }}
                                </h2>
                                <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">
                                    {{ $company->trade_name ?: ($company->billing_city ?: 'Coordonnees a completer') }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full border border-[#647a0b]/20 bg-[#647a0b]/10 px-2 py-0.5 text-[10px] font-medium text-[#647a0b]">
                                B2B
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $company->client_profiles_count }} contact{{ $company->client_profiles_count > 1 ? 's' : '' }}
                            </span>
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $company->siret ? 'SIRET ' . $company->siret : 'SIRET manquant' }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2 text-[11px] text-gray-600">
                            <div class="min-w-0 rounded-lg bg-[#f7f8f1] p-2">
                                <div class="font-medium text-gray-500">Facturation</div>
                                <div class="mt-0.5 truncate text-gray-900">
                                    {{ $company->billing_email ?: 'Email manquant' }}
                                </div>
                            </div>
                            <div class="min-w-0 rounded-lg bg-[#f7f8f1] p-2">
                                <div class="font-medium text-gray-500">Contact</div>
                                <div class="mt-0.5 truncate text-gray-900">
                                    {{ $contactName ?: 'Non renseigne' }}
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function filterMobileCompanies() {
            const input = document.getElementById('mobileCompanySearch');
            const filter = input ? input.value.toLowerCase() : '';
            const items = document.querySelectorAll('#mobileCompanyList > a');

            items.forEach((item) => {
                const text = item.getAttribute('data-company') || '';
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</x-mobile-layout>
