@php
    $statusTone = in_array($user->license_status, ['active', 'trialing'], true)
        ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]'
        : 'border-amber-200 bg-amber-50 text-amber-700';
    $featurePercent = $totalFeatureCount > 0
        ? (int) round(($enabledCount / $totalFeatureCount) * 100)
        : 0;
@endphp

<x-mobile-layout title="Abonnement">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-id-card text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Abonnement</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Licence, acces modules et raccourcis de gestion.
                </p>
            </div>

            <a href="{{ route('mobile.menu') }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-gray-500 shadow-sm"
               aria-label="Retour au menu">
                <i class="fas fa-bars text-xs"></i>
            </a>
        </div>

        <section class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-gray-900">{{ $planLabel }}</h2>
                    <p class="mt-1 text-sm leading-snug text-gray-600">
                        {{ $user->license_product ?: 'Aucun produit de licence associe.' }}
                    </p>
                </div>
                <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $statusTone }}">
                    {{ $statusLabel }}
                </span>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-2">
                <div class="rounded-lg bg-[#f7f8f1] p-2">
                    <div class="text-[11px] font-medium text-gray-500">Offre</div>
                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $planLabel }}</div>
                </div>
                <div class="rounded-lg bg-[#f7f8f1] p-2">
                    <div class="text-[11px] font-medium text-gray-500">Modules</div>
                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $enabledCount }}/{{ $totalFeatureCount }}</div>
                </div>
                <div class="rounded-lg bg-[#f7f8f1] p-2">
                    <div class="text-[11px] font-medium text-gray-500">Stripe</div>
                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $user->stripe_customer_id ? 'Client' : 'Non lie' }}</div>
                </div>
            </div>

            <div class="mt-4 h-2 overflow-hidden rounded-full bg-[#eef2df]">
                <div class="h-full rounded-full bg-[#647a0b]" style="width: {{ $featurePercent }}%"></div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2">
                <a href="{{ route('license-tiers.pricing') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-xs font-semibold text-white">
                    Voir les offres
                </a>
                <a href="{{ route('profile.license') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700">
                    Vue web
                </a>
            </div>
        </section>

        <section class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-900">Compte</h2>
            <div class="mt-3 space-y-2">
                <div class="rounded-lg bg-[#f7f8f1] p-3">
                    <div class="text-[11px] font-medium text-gray-500">Email</div>
                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $user->email }}</div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-3">
                        <div class="text-[11px] font-medium text-gray-500">Famille</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ ucfirst($family) }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-3">
                        <div class="text-[11px] font-medium text-gray-500">Client Stripe</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                            {{ $user->stripe_customer_id ? 'Renseigne' : 'Absent' }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-gray-900">Modules inclus</h2>
                <span class="text-xs font-semibold text-[#647a0b]">{{ $enabledCount }} actifs</span>
            </div>

            <div class="mt-3 space-y-2">
                @foreach($features as $feature)
                    <div class="flex items-center justify-between gap-3 rounded-lg bg-[#f7f8f1] px-3 py-2">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-gray-900">{{ $feature['label'] }}</div>
                        </div>
                        <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $feature['enabled'] ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' : 'border-gray-200 bg-white text-gray-500' }}">
                            {{ $feature['enabled'] ? 'Inclus' : 'Upgrade' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-900">Raccourcis</h2>
            <div class="mt-3 grid grid-cols-2 gap-2">
                <a href="{{ route('mobile.profile.index') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700">
                    Profil
                </a>
                <a href="{{ route('therapist.stripe') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700">
                    Stripe ventes
                </a>
            </div>
        </section>
    </div>
</x-mobile-layout>
