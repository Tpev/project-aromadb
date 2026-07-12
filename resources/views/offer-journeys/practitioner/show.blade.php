<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('offer-journeys.index') }}" class="text-sm font-medium text-[#647a0b] hover:text-[#854f38]">Pages et campagnes</a>
                <div class="mt-1 flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $journey->name }}</h1>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ config('offer_journeys.status_labels.'.$journey->status, $journey->status) }}</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('offer-journeys.preview', $journey) }}" target="_blank" rel="noopener" class="inline-flex rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Prévisualiser</a>
                <a href="{{ route('offer-journeys.analytics', $journey) }}" class="inline-flex rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Résultats</a>
                <a href="{{ route('offer-journeys.automation', $journey) }}" class="inline-flex rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Messages de suivi</a>
                <a href="{{ route('offer-journeys.contacts.index', ['journey_id' => $journey->id]) }}" class="inline-flex rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Contacts</a>
                <a href="{{ route('offer-journeys.share', $journey) }}" class="inline-flex rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b] hover:bg-[#f7f9ec]">Partager</a>
                <a href="{{ route('offer-journeys.edit', $journey) }}" class="inline-flex rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Paramètres</a>
                <form method="POST" action="{{ route('offer-journeys.duplicate', $journey) }}">@csrf<button class="inline-flex rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Dupliquer</button></form>
                @if($journey->status === 'published')
                    <form method="POST" action="{{ route('offer-journeys.pause', $journey) }}">@csrf<button class="rounded-md border border-amber-300 px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-50">Mettre en pause</button></form>
                @else
                    <form method="POST" action="{{ route('offer-journeys.publish', $journey) }}">@csrf<button class="rounded-md bg-[#647a0b] px-3 py-2 text-sm font-semibold text-white hover:bg-[#526509]">Publier</button></form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            @php
                $firstPage = $journey->pages->sortBy('position')->first();
                $hasForm = $journey->pages->contains(fn ($page) => $page->form !== null);
                $destination = match($journey->objective) {
                    'appointment' => 'la réservation d’un rendez-vous',
                    'event' => 'l’inscription à votre événement',
                    'lead_magnet' => 'l’accès à la ressource proposée',
                    'training' => 'l’accès à votre formation',
                    'gift_voucher' => 'l’achat d’un bon cadeau',
                    default => 'la prise de contact avec vous',
                };
            @endphp
            <section class="rounded-lg border border-[#dfe6c7] bg-[#f7f9ec] p-5" aria-labelledby="journey-summary-title">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase text-[#526509]">Ce qui se passera après publication</p>
                        <h2 id="journey-summary-title" class="mt-1 font-semibold text-gray-900">Le visiteur découvrira « {{ data_get($firstPage?->draft_content_json, 'title', $journey->name) }} »</h2>
                        <p class="mt-2 text-sm leading-6 text-gray-700">Il {{ $hasForm ? 'remplira le formulaire prévu, puis ' : '' }}sera guidé vers {{ $destination }}. Une confirmation lui indiquera clairement la suite. Les messages facultatifs ne partiront que si leur consentement le permet.</p>
                    </div>
                    <a href="{{ route('offer-journeys.preview', $journey) }}" target="_blank" rel="noopener" class="inline-flex shrink-0 justify-center rounded-md border border-[#647a0b] bg-white px-3 py-2 text-sm font-semibold text-[#647a0b]">Tester le parcours</a>
                </div>
            </section>

            @if($preflight)
                <section class="border border-gray-200 bg-white" aria-labelledby="preflight-title">
                    <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 id="preflight-title" class="font-semibold text-gray-900">Contrôle avant publication</h2>
                            <p class="mt-1 text-sm text-gray-500">Les erreurs bloquent la publication. Les recommandations restent facultatives.</p>
                        </div>
                        <span class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold {{ $preflight['ready'] ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
                            <span class="h-2 w-2 rounded-full {{ $preflight['ready'] ? 'bg-green-600' : 'bg-red-600' }}"></span>
                            {{ $preflight['ready'] ? 'Prêt à publier' : count($preflight['errors']).' '.(count($preflight['errors']) > 1 ? 'corrections requises' : 'correction requise') }}
                        </span>
                    </div>
                    <div class="grid gap-5 px-5 py-4 lg:grid-cols-2">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">À corriger</h3>
                            <ul class="mt-2 space-y-2 text-sm">
                                @forelse($preflight['errors'] as $message)
                                    <li class="flex gap-2 text-red-800"><span aria-hidden="true">●</span><span>{{ $message }}</span></li>
                                @empty
                                    <li class="text-green-700">Aucune erreur bloquante.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Pour aller plus loin</h3>
                            <ul class="mt-2 space-y-2 text-sm">
                                @forelse($preflight['warnings'] as $message)
                                    <li class="flex gap-2 text-amber-800"><span aria-hidden="true">●</span><span>{{ $message }}</span></li>
                                @empty
                                    <li class="text-gray-500">Aucune recommandation supplémentaire.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </section>
            @endif

            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
                <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                        <div>
                            <h2 class="font-semibold text-gray-900">Étapes du parcours</h2>
                            <p class="mt-1 text-sm text-gray-500">Vos modifications restent privées jusqu’à ce que vous publiiez une nouvelle version.</p>
                        </div>
                    </div>
                    <ol class="divide-y divide-gray-100">
                        @foreach($journey->pages as $page)
                            <li class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#f0f4df] text-sm font-semibold text-[#647a0b]">{{ $loop->iteration }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-gray-900">{{ $page->name }}</p>
                                        <p class="text-sm text-gray-500">{{ config('offer_journeys.page_type_labels.'.$page->type, $page->type) }} · /{{ $page->slug }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @unless($loop->first)<form method="POST" action="{{ route('offer-journeys.pages.move', [$journey, $page]) }}">@csrf<input type="hidden" name="direction" value="up"><button class="rounded-md border border-gray-200 px-2 py-1 text-sm text-gray-600" title="Monter">↑</button></form>@endunless
                                    @unless($loop->last)<form method="POST" action="{{ route('offer-journeys.pages.move', [$journey, $page]) }}">@csrf<input type="hidden" name="direction" value="down"><button class="rounded-md border border-gray-200 px-2 py-1 text-sm text-gray-600" title="Descendre">↓</button></form>@endunless
                                    <a href="{{ route('offer-journeys.pages.edit', [$journey, $page]) }}" class="rounded-md border border-[#647a0b] px-3 py-1.5 text-sm font-semibold text-[#647a0b] hover:bg-[#f7f9ec]">Modifier</a>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    <form method="POST" action="{{ route('offer-journeys.pages.store', $journey) }}" class="grid gap-3 border-t border-gray-200 bg-gray-50 p-4 sm:grid-cols-[1fr_190px_auto]">
                        @csrf
                        <label class="sr-only" for="page-name">Nom de la nouvelle étape</label>
                        <input id="page-name" name="name" required placeholder="Nouvelle étape" class="rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                        <label class="sr-only" for="page-type">Type</label>
                        <select id="page-type" name="type" class="rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                            @foreach(config('offer_journeys.allowed_page_types') as $type)<option value="{{ $type }}">{{ config('offer_journeys.page_type_labels.'.$type, $type) }}</option>@endforeach
                        </select>
                        <button class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-[#647a0b] ring-1 ring-inset ring-[#647a0b] hover:bg-[#f7f9ec]">Ajouter</button>
                    </form>
                </section>

                <aside class="space-y-5">
                    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <h2 class="font-semibold text-gray-900">Mise en ligne</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div><dt class="text-gray-500">Version en ligne</dt><dd class="font-medium text-gray-900">{{ $journey->publishedVersion?->version_number ? 'Version '.$journey->publishedVersion->version_number : 'Aucune' }}</dd></div>
                            <div><dt class="text-gray-500">Dernière publication</dt><dd class="font-medium text-gray-900">{{ $journey->published_at?->format('d/m/Y H:i') ?? 'Non publié' }}</dd></div>
                        </dl>
                        @if($journey->status === 'published' && $journey->user?->slug)
                            <a target="_blank" rel="noopener" href="{{ route('offer-journeys.public.show', ['therapist' => $journey->user, 'journeySlug' => $journey->slug]) }}" class="mt-4 inline-flex text-sm font-semibold text-[#647a0b] hover:text-[#854f38]">Voir la page publique</a>
                        @endif
                    </section>

                    @if($journey->versions->isNotEmpty())
                        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 class="font-semibold text-gray-900">Historique</h2>
                            <div class="mt-3 space-y-3">
                                @foreach($journey->versions as $version)
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <div><p class="font-medium text-gray-900">Version {{ $version->version_number }}</p><p class="text-xs text-gray-500">{{ $version->published_at->format('d/m/Y H:i') }}</p></div>
                                        @if($journey->published_version_id !== $version->id)
                                            <form method="POST" action="{{ route('offer-journeys.versions.restore', [$journey, $version]) }}">@csrf<button class="text-xs font-semibold text-[#647a0b] hover:text-[#854f38]">Restaurer</button></form>
                                        @else
                                            <span class="text-xs font-medium text-green-700">En ligne</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
