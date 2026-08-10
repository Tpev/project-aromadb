<x-app-layout>
    <x-slot name="header">
        @include('offer-journeys.practitioner._workspace-header')
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <p class="font-semibold">Une correction est nécessaire.</p>
                    @foreach($errors->all() as $error)<p class="mt-1">{{ $error }}</p>@endforeach
                </div>
            @endif

            @include('offer-journeys.practitioner._workspace-progress')

            <section class="border border-gray-200 bg-white" aria-labelledby="journey-results-title">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h2 id="journey-results-title" class="font-semibold text-gray-900">Les 30 derniers jours</h2>
                    <p class="mt-1 text-sm text-gray-500">Les tests et visites automatiques sont exclus.</p>
                </div>
                <dl class="grid grid-cols-2 divide-x divide-y divide-gray-100 sm:grid-cols-3 lg:grid-cols-6 lg:divide-y-0">
                    @foreach([
                        ['Visiteurs', $workspace['metrics']['visitors']],
                        ['Vues', $workspace['metrics']['views']],
                        ['Formulaires reçus', $workspace['metrics']['submissions']],
                        ['Contacts uniques', $workspace['metrics']['unique_contacts']],
                        ['Actions confirmées', $workspace['metrics']['conversions']],
                        ['Revenu attribué', number_format($workspace['metrics']['revenue_cents'] / 100, 2, ',', ' ').' €'],
                    ] as [$label, $value])
                        <div class="min-w-0 px-4 py-4">
                            <dt class="text-xs font-medium text-gray-500">{{ $label }}</dt>
                            <dd class="mt-1 truncate text-xl font-semibold text-gray-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
                <section class="border border-gray-200 bg-white" aria-labelledby="journey-map-title">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h2 id="journey-map-title" class="font-semibold text-gray-900">Ce que vivra votre visiteur</h2>
                        <p class="mt-1 text-sm text-gray-500">Cette vue est construite à partir de votre page, de votre formulaire et de vos messages actuels.</p>
                    </div>
                    <ol class="divide-y divide-gray-100">
                        @foreach($workspace['journey_map'] as $step)
                            @php
                                $statusLabel = match($step['status']) {
                                    'ready' => 'Prêt',
                                    'disabled' => 'Désactivé',
                                    'error' => 'Erreur',
                                    default => 'À compléter',
                                };
                                $statusSymbol = match($step['status']) {
                                    'ready' => '✓',
                                    'disabled' => '–',
                                    'error' => '×',
                                    default => '!',
                                };
                            @endphp
                            <li>
                                <a href="{{ $step['url'] }}" class="group flex items-center gap-4 px-5 py-4 hover:bg-gray-50">
                                    <span @class([
                                        'flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold',
                                        'bg-green-100 text-green-800' => $step['status'] === 'ready',
                                        'bg-amber-100 text-amber-900' => $step['status'] === 'attention',
                                        'bg-gray-100 text-gray-500' => $step['status'] === 'disabled',
                                        'bg-red-100 text-red-800' => $step['status'] === 'error',
                                    ]) aria-hidden="true">{{ $statusSymbol }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block font-semibold text-gray-900">{{ $step['label'] }}</span>
                                        <span class="mt-0.5 block text-sm text-gray-500">{{ $step['detail'] }}</span>
                                    </span>
                                    <span class="flex shrink-0 flex-col items-end gap-1">
                                        <span @class([
                                            'rounded-full px-2 py-0.5 text-xs font-semibold',
                                            'bg-green-50 text-green-800' => $step['status'] === 'ready',
                                            'bg-amber-50 text-amber-900' => $step['status'] === 'attention',
                                            'bg-gray-100 text-gray-600' => $step['status'] === 'disabled',
                                            'bg-red-50 text-red-800' => $step['status'] === 'error',
                                        ])>{{ $statusLabel }}</span>
                                        <span class="text-xs font-semibold text-[#647a0b] group-hover:text-[#526509]">Modifier</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </section>

                <aside class="space-y-5">
                    <section class="border border-gray-200 bg-white p-5" aria-labelledby="publication-title">
                        <h2 id="publication-title" class="font-semibold text-gray-900">Mise en ligne</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div><dt class="text-gray-500">Version publique</dt><dd class="font-semibold text-gray-900">{{ $journey->publishedVersion?->version_number ? 'Version '.$journey->publishedVersion->version_number : 'Aucune' }}</dd></div>
                            <div><dt class="text-gray-500">Dernière publication</dt><dd class="font-semibold text-gray-900">{{ $journey->published_at?->format('d/m/Y à H:i') ?? 'Non publié' }}</dd></div>
                        </dl>
                        <div class="mt-4 space-y-2">
                            <a href="{{ route('offer-journeys.preview', $journey) }}" target="_blank" rel="noopener" class="flex w-full items-center justify-center rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b] hover:bg-[#f7f9ec]">Tester comme un visiteur</a>
                            @if($journey->status === 'published' && $journey->user?->slug)
                                <a target="_blank" rel="noopener" href="{{ route('offer-journeys.public.show', ['therapist' => $journey->user, 'journeySlug' => $journey->slug]) }}" class="flex w-full items-center justify-center rounded-md bg-[#647a0b] px-3 py-2 text-sm font-semibold text-white hover:bg-[#526509]">Voir la page publique</a>
                            @endif
                        </div>
                    </section>

                    <section class="border border-gray-200 bg-white p-5" aria-labelledby="follow-up-title">
                        <h2 id="follow-up-title" class="font-semibold text-gray-900">Suivi commercial</h2>
                        <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                            <div><dt class="text-gray-500">À répondre</dt><dd class="mt-1 text-xl font-semibold text-gray-900">{{ $workspace['pending_contacts'] }}</dd></div>
                            <div><dt class="text-gray-500">Actions dues</dt><dd class="mt-1 text-xl font-semibold text-gray-900">{{ $workspace['due_tasks'] }}</dd></div>
                        </dl>
                        <a href="{{ route('offer-journeys.contacts.index', ['journey_id' => $journey->id]) }}" class="mt-4 inline-flex text-sm font-semibold text-[#647a0b] hover:text-[#526509]">Ouvrir les contacts</a>
                    </section>
                </aside>
            </div>

            @if($preflight)
                <section class="border border-gray-200 bg-white" aria-labelledby="preflight-title">
                    <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div><h2 id="preflight-title" class="font-semibold text-gray-900">Contrôle avant publication</h2><p class="mt-1 text-sm text-gray-500">{{ $workspace['public_version_is_live'] ? 'Ce contrôle concerne votre prochain brouillon. La version en ligne reste inchangée jusqu’à sa republication.' : 'Les erreurs bloquent la publication. Les recommandations restent facultatives.' }}</p></div>
                        <span @class(['inline-flex w-fit rounded-full px-3 py-1 text-sm font-semibold', 'bg-green-50 text-green-800' => $preflight['ready'], 'bg-red-50 text-red-800' => ! $preflight['ready']])>{{ $preflight['ready'] ? 'Prêt à publier' : count($preflight['errors']).' '.(count($preflight['errors']) === 1 ? 'correction requise' : 'corrections requises') }}</span>
                    </div>
                    <div class="grid gap-5 px-5 py-4 lg:grid-cols-2">
                        <div><h3 class="text-sm font-semibold text-gray-900">À corriger</h3><ul class="mt-2 space-y-2 text-sm">@forelse($preflight['errors'] as $message)<li class="text-red-800">{{ $message }}</li>@empty<li class="text-green-700">Aucune erreur bloquante.</li>@endforelse</ul></div>
                        <div><h3 class="text-sm font-semibold text-gray-900">Recommandations</h3><ul class="mt-2 space-y-2 text-sm">@forelse($preflight['warnings'] as $message)<li class="text-amber-800">{{ $message }}</li>@empty<li class="text-gray-500">Aucune recommandation supplémentaire.</li>@endforelse</ul></div>
                    </div>
                </section>
            @endif

            @if($journey->versions->isNotEmpty())
                <details class="border border-gray-200 bg-white">
                    <summary class="cursor-pointer px-5 py-4 font-semibold text-gray-900">Historique des publications</summary>
                    <div class="divide-y divide-gray-100 border-t border-gray-200">
                        @foreach($journey->versions as $version)
                            <div class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                                <div><p class="font-medium text-gray-900">Version {{ $version->version_number }}</p><p class="text-xs text-gray-500">{{ $version->published_at->format('d/m/Y à H:i') }}</p></div>
                                @if($journey->published_version_id !== $version->id)<form method="POST" action="{{ route('offer-journeys.versions.restore', [$journey, $version]) }}">@csrf<button class="font-semibold text-[#647a0b] hover:text-[#526509]">Restaurer</button></form>@else<span class="font-medium text-green-700">En ligne</span>@endif
                            </div>
                        @endforeach
                    </div>
                </details>
            @endif
        </div>
    </div>
</x-app-layout>
