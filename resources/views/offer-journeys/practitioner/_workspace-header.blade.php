@php
    $workspaceSection = $workspaceSection ?? 'overview';
    $firstPage = $workspace['first_page'];
    $formPage = $workspace['form_page'] ?: $firstPage;
    $tabs = [
        ['key' => 'overview', 'label' => 'Vue d’ensemble', 'url' => route('offer-journeys.show', $journey)],
        ['key' => 'page', 'label' => 'Page', 'url' => $firstPage ? route('offer-journeys.pages.edit', [$journey, $firstPage]) : route('offer-journeys.show', $journey)],
        ['key' => 'form', 'label' => 'Formulaire', 'url' => $formPage ? route('offer-journeys.pages.edit', [$journey, $formPage]).'?section=form' : route('offer-journeys.show', $journey)],
        ['key' => 'messages', 'label' => 'Messages', 'url' => route('offer-journeys.automation', $journey)],
        ['key' => 'share', 'label' => 'Partage', 'url' => route('offer-journeys.share', $journey)],
        ['key' => 'contacts', 'label' => 'Contacts', 'url' => route('offer-journeys.contacts.index', ['journey_id' => $journey->id])],
        ['key' => 'analytics', 'label' => 'Résultats', 'url' => route('offer-journeys.analytics', $journey)],
    ];
@endphp

<div>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <a href="{{ route('offer-journeys.index') }}" class="text-sm font-semibold text-[#647a0b] hover:text-[#526509]">Pages et campagnes</a>
                <div class="mt-1 flex flex-wrap items-center gap-2">
                    <h1 class="truncate text-2xl font-semibold text-gray-900">{{ $journey->name }}</h1>
                    <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">
                        {{ config('offer_journeys.status_labels.'.$journey->status, $journey->status) }}
                    </span>
                    @if($workspace['public_version_is_live'] && $workspace['draft_has_blockers'])
                        <span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-800">Brouillon à terminer</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500">{{ config('offer_journeys.objective_labels.'.$journey->objective, $journey->objective) }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('offer-journeys.preview', $journey) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Tester</a>
                <a href="{{ route('offer-journeys.edit', $journey) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Paramètres</a>
                @if($workspace['draft_has_blockers'])
                    <a href="{{ $workspace['next_action']['url'] }}" class="inline-flex items-center justify-center rounded-md bg-[#647a0b] px-3 py-2 text-sm font-semibold text-white hover:bg-[#526509]">Terminer le brouillon</a>
                @elseif(!$workspace['public_version_is_live'] || $workspace['has_unpublished_changes'])
                    <form method="POST" action="{{ route('offer-journeys.publish', $journey) }}" onsubmit="return confirm('{{ $journey->status === 'published' ? 'Mettre cette nouvelle version en ligne ? La version actuelle restera disponible dans l’historique.' : 'Mettre cette page en ligne maintenant ?' }}')">@csrf<button class="rounded-md bg-[#647a0b] px-3 py-2 text-sm font-semibold text-white hover:bg-[#526509]">{{ $journey->status === 'published' ? 'Republier les modifications' : 'Publier' }}</button></form>
                @endif
                @if($journey->status === 'published')
                    <form method="POST" action="{{ route('offer-journeys.pause', $journey) }}" onsubmit="return confirm('Mettre cette page en pause ? Elle ne sera plus accessible au public.')">@csrf<button class="rounded-md border border-amber-300 bg-white px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-50">Mettre en pause</button></form>
                @endif
            </div>
        </div>

        <nav class="-mb-5 mt-4 flex gap-1 overflow-x-auto" aria-label="Sections du parcours">
            @foreach($tabs as $tab)
                <a href="{{ $tab['url'] }}"
                   @class([
                       'shrink-0 border-b-2 px-3 py-3 text-sm font-semibold focus:outline-none focus-visible:ring-2 focus-visible:ring-[#647a0b]',
                       'border-[#647a0b] text-[#526509]' => $workspaceSection === $tab['key'],
                       'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-800' => $workspaceSection !== $tab['key'],
                   ])
                   @if($workspaceSection === $tab['key']) aria-current="page" @endif>
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </nav>
</div>
