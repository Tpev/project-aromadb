<x-app-layout>
    <x-slot name="header">
        @include('offer-journeys.practitioner._workspace-header')
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><h2 class="text-xl font-semibold text-gray-900">Résultats</h2><p class="mt-1 text-sm text-gray-500">Comprenez les visites, demandes et actions obtenues.</p></div>
                <form method="GET"><label for="days" class="sr-only">Période</label><select id="days" name="days" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">@foreach([7 => '7 derniers jours', 30 => '30 derniers jours', 90 => '90 derniers jours', 365 => '12 derniers mois'] as $value => $label)<option value="{{ $value }}" @selected($days === $value)>{{ $label }}</option>@endforeach</select></form>
            </div>
            @unless(config('offer_journeys.tracking_enabled'))
                <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">La collecte de statistiques est actuellement désactivée. Les actions principales du parcours continuent de fonctionner.</div>
            @endunless

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm" aria-labelledby="conversion-path-title">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div><h2 id="conversion-path-title" class="font-semibold text-gray-900">Du visiteur au résultat obtenu</h2><p class="mt-1 text-sm text-gray-500">Chaque chiffre correspond uniquement à la période choisie.</p></div>
                    @foreach($recommendations as $recommendation)<a href="{{ $recommendation['route'] }}" class="text-sm font-semibold text-[#647a0b]">{{ $recommendation['label'] }}</a>@endforeach
                </div>
                <div class="mt-5 grid gap-2 sm:grid-cols-4">
                    @foreach([['Visiteurs uniques',$metrics['visitors']],['Formulaires reçus',$metrics['form_submissions']],['Contacts uniques',$metrics['unique_contacts']],['Actions confirmées',$metrics['conversions']]] as [$label,$value])
                        <div class="relative rounded-md border border-gray-200 p-4"><p class="text-xs font-medium text-gray-500">{{ $label }}</p><p class="mt-1 text-xl font-semibold text-gray-900">{{ $value }}</p>@unless($loop->last)<span class="absolute -right-2 top-1/2 z-10 hidden h-4 w-4 -translate-y-1/2 items-center justify-center rounded-full bg-[#647a0b] text-[10px] text-white sm:flex">›</span>@endunless</div>
                    @endforeach
                </div>
                <dl class="mt-4 grid divide-y divide-gray-100 border-y border-gray-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                    @foreach([
                        ['Visite vers formulaire', $metrics['submission_rate']],
                        ['Formulaire vers contact unique', $metrics['form_to_contact_rate']],
                        ['Contact vers action confirmée', $metrics['contact_to_conversion_rate']],
                    ] as [$label, $rate])
                        <div class="flex items-center justify-between gap-3 py-3 sm:block sm:px-4 sm:first:pl-0 sm:last:pr-0">
                            <dt class="text-xs font-medium text-gray-500">{{ $label }}</dt>
                            <dd class="text-sm font-semibold text-gray-900 sm:mt-1">{{ number_format($rate, 1, ',', ' ') }} %</dd>
                        </div>
                    @endforeach
                </dl>
                @foreach($recommendations as $recommendation)<div class="mt-4 rounded-md border border-[#dfe6c7] bg-[#f7f9ec] p-3"><p class="text-sm font-semibold text-gray-900">{{ $recommendation['title'] }}</p><p class="mt-1 text-sm text-gray-600">{{ $recommendation['body'] }}</p></div>@endforeach
            </section>

            <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6" aria-label="Indicateurs principaux">
                @foreach([
                    ['Visiteurs', $metrics['visitors'], 'Personnes uniques estimées'],
                    ['Vues', $metrics['views'], 'Pages consultées'],
                    ['Clics', $metrics['cta_clicks'], 'Clics sur l’action principale'],
                    ['Nouveaux contacts', $metrics['new_contacts'], 'Créés pour la première fois'],
                    ['Taux de demande', number_format($metrics['submission_rate'], 1, ',', ' ').' %', 'Formulaires par visiteur'],
                    ['Taux de résultat', number_format($metrics['conversion_rate'], 1, ',', ' ').' %', 'Actions confirmées par visiteur'],
                ] as [$label, $value, $help])
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $value }}</p>
                        <p class="mt-1 text-xs leading-5 text-gray-500">{{ $help }}</p>
                    </div>
                @endforeach
            </section>

            @if($metrics['revenue_cents'] > 0)
                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Revenu attribué</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($metrics['revenue_cents'] / 100, 2, ',', ' ') }} €</p>
                    <p class="mt-1 text-sm text-gray-500">Uniquement les ventes confirmées reliées à ce parcours.</p>
                </section>
            @endif

            @if($conversionBreakdown->isNotEmpty())
                <section class="border border-gray-200 bg-white p-5">
                    <h2 class="font-semibold text-gray-900">Détail des actions confirmées</h2>
                    <div class="mt-4 divide-y divide-gray-100">
                        @foreach($conversionBreakdown as $item)
                            @php($conversionLabel = ['appointment' => 'Rendez-vous', 'event_registration' => 'Inscriptions à un événement', 'training_enrollment' => 'Inscriptions à une formation', 'gift_voucher_purchase' => 'Achats de bons cadeaux'][$item->conversion_type] ?? 'Autres actions')
                            <div class="grid grid-cols-[minmax(0,1fr)_70px_110px] gap-3 py-3 text-sm"><span class="text-gray-700">{{ $conversionLabel }}</span><span class="text-right font-semibold text-gray-900">{{ $item->total }}</span><span class="text-right text-gray-500">{{ number_format($item->revenue_cents / 100, 2, ',', ' ') }} €</span></div>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="grid gap-5 lg:grid-cols-2">
                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-gray-900">Origine des visites</h2>
                    <div class="mt-4 divide-y divide-gray-100">
                        @forelse($bySource as $source)
                            <div class="flex items-center justify-between gap-4 py-3 text-sm"><span class="truncate text-gray-700">{{ $source->source === 'direct' ? 'Accès direct' : $source->source }}</span><span class="font-semibold text-gray-900">{{ $source->total }}</span></div>
                        @empty
                            <p class="py-6 text-sm text-gray-500">Aucune visite mesurée sur cette période.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-gray-900">Consultations par étape</h2>
                    <div class="mt-4 divide-y divide-gray-100">
                        @forelse($byPage as $pageMetric)
                            <div class="flex items-center justify-between gap-4 py-3 text-sm"><span class="truncate text-gray-700">{{ $pageMetric->page?->name ?? 'Étape supprimée' }}</span><span class="font-semibold text-gray-900">{{ $pageMetric->total }}</span></div>
                        @empty
                            <p class="py-6 text-sm text-gray-500">Aucune étape consultée sur cette période.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-gray-900">Progression par étape</h2>
                    <p class="mt-1 text-sm text-gray-500">Le taux de sortie compare les consultations aux actions mesurées sur chaque étape.</p>
                    <div class="mt-4 divide-y divide-gray-100">
                        @forelse($stepPerformance as $step)
                            <div class="grid grid-cols-[minmax(0,1fr)_70px_70px] gap-3 py-3 text-sm"><span class="truncate text-gray-700">{{ $step['page']->name }}</span><span class="text-right text-gray-500">{{ $step['views'] }} vues</span><span class="text-right font-semibold text-gray-900">{{ number_format($step['drop_off_rate'], 1, ',', ' ') }} %</span></div>
                        @empty<p class="py-6 text-sm text-gray-500">Aucune étape à analyser.</p>@endforelse
                    </div>
                </section>
                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-gray-900">Liens de campagne</h2>
                    <div class="mt-4 divide-y divide-gray-100">
                        @forelse($byCampaign as $campaignMetric)
                            <div class="grid grid-cols-[minmax(0,1fr)_60px_80px] gap-3 py-3 text-sm"><span class="truncate text-gray-700">{{ $campaignMetric->campaignLink?->name ?? 'Lien supprimé' }}</span><span class="text-right text-gray-500">{{ $campaignMetric->views }} vues</span><span class="text-right font-semibold text-gray-900">{{ $campaignMetric->leads }} envois</span></div>
                        @empty<p class="py-6 text-sm text-gray-500">Aucun lien de campagne mesuré.</p>@endforelse
                    </div>
                </section>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('offer-journeys.share', $journey) }}" class="rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#526509]">Créer un lien de campagne</a>
            </div>
        </div>
    </div>
</x-app-layout>
