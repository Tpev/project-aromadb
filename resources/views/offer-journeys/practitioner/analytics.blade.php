<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('offer-journeys.show', $journey) }}" class="text-sm font-medium text-[#647a0b] hover:text-[#854f38]">{{ $journey->name }}</a>
                <h1 class="mt-1 text-2xl font-semibold text-gray-900">Résultats</h1>
            </div>
            <form method="GET">
                <label for="days" class="sr-only">Période</label>
                <select id="days" name="days" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                    @foreach([7 => '7 derniers jours', 30 => '30 derniers jours', 90 => '90 derniers jours', 365 => '12 derniers mois'] as $value => $label)
                        <option value="{{ $value }}" @selected($days === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @unless(config('offer_journeys.tracking_enabled'))
                <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">La collecte de statistiques est actuellement désactivée. Les actions principales du parcours continuent de fonctionner.</div>
            @endunless

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm" aria-labelledby="conversion-path-title">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div><h2 id="conversion-path-title" class="font-semibold text-gray-900">Du visiteur au résultat obtenu</h2><p class="mt-1 text-sm text-gray-500">Chaque chiffre correspond uniquement à la période choisie.</p></div>
                    @foreach($recommendations as $recommendation)<a href="{{ $recommendation['route'] }}" class="text-sm font-semibold text-[#647a0b]">{{ $recommendation['label'] }}</a>@endforeach
                </div>
                <div class="mt-5 grid gap-2 sm:grid-cols-4">
                    @foreach([['Visiteurs',$metrics['visitors']],['Personnes intéressées',$metrics['leads']],['Rendez-vous ou achats',$metrics['conversions']],['Revenu attribué',number_format($metrics['revenue_cents']/100,2,',',' ').' €']] as [$label,$value])
                        <div class="relative rounded-md border border-gray-200 p-4"><p class="text-xs font-medium text-gray-500">{{ $label }}</p><p class="mt-1 text-xl font-semibold text-gray-900">{{ $value }}</p>@unless($loop->last)<span class="absolute -right-2 top-1/2 z-10 hidden h-4 w-4 -translate-y-1/2 items-center justify-center rounded-full bg-[#647a0b] text-[10px] text-white sm:flex">›</span>@endunless</div>
                    @endforeach
                </div>
                @foreach($recommendations as $recommendation)<div class="mt-4 rounded-md border border-[#dfe6c7] bg-[#f7f9ec] p-3"><p class="text-sm font-semibold text-gray-900">{{ $recommendation['title'] }}</p><p class="mt-1 text-sm text-gray-600">{{ $recommendation['body'] }}</p></div>@endforeach
            </section>

            <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6" aria-label="Indicateurs principaux">
                @foreach([
                    ['Visiteurs', $metrics['visitors'], 'Personnes uniques estimées'],
                    ['Vues', $metrics['views'], 'Pages consultées'],
                    ['Actions', $metrics['cta_clicks'], 'Clics sur l’action principale'],
                    ['Contacts', $metrics['leads'], 'Formulaires validés'],
                    ['Conversions', $metrics['conversions'], 'Actions confirmées'],
                    ['Taux', number_format($metrics['conversion_rate'], 1, ',', ' ').' %', 'Conversions par visiteur'],
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
                            <div class="grid grid-cols-[minmax(0,1fr)_60px_70px] gap-3 py-3 text-sm"><span class="truncate text-gray-700">{{ $campaignMetric->campaignLink?->name ?? 'Lien supprimé' }}</span><span class="text-right text-gray-500">{{ $campaignMetric->views }} vues</span><span class="text-right font-semibold text-gray-900">{{ $campaignMetric->leads }} contacts</span></div>
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
