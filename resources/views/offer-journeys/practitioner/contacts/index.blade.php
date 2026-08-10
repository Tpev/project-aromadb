<x-app-layout>
    <x-slot name="header">
        @if($selectedJourney)
            @include('offer-journeys.practitioner._workspace-header', ['journey' => $selectedJourney])
        @else
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><a href="{{ route('offer-journeys.index') }}" class="text-sm font-medium text-[#647a0b] hover:text-[#854f38]">Pages et campagnes</a><h1 class="mt-1 text-2xl font-semibold text-gray-900">Personnes intéressées</h1></div>
            <div class="flex flex-wrap gap-2"><a href="{{ route('offer-journeys.contacts.pipeline') }}" class="rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b] hover:bg-[#f7f9ec]">Suivi des contacts</a><a href="{{ route('offer-journeys.contacts.segments') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Segments</a>@if($contactImportEnabled)<a href="{{ route('offer-journeys.contacts.import.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Importer</a>@endif<a href="{{ route('offer-journeys.contacts.export') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Exporter</a></div>
        </div>
        @endif
    </x-slot>
    <div class="py-6"><div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
        @if($selectedJourney)<div><h2 class="text-xl font-semibold text-gray-900">Contacts du parcours</h2><p class="mt-1 text-sm text-gray-500">Demandes et personnes reliées à « {{ $selectedJourney->name }} ».</p></div>@endif
        <section class="grid grid-cols-2 border border-gray-200 bg-white sm:grid-cols-4" aria-label="Résumé des contacts">
            @foreach([['Résultats affichés', $summary['filtered']], ['Nouveaux', $summary['new']], ['Actions en retard', $summary['due']], ['Convertis', $summary['converted']]] as [$label, $value])
                <div class="border-b border-r border-gray-100 px-4 py-3 sm:border-b-0"><p class="text-xs font-medium text-gray-500">{{ $label }}</p><p class="mt-1 text-xl font-semibold text-gray-900">{{ $value }}</p></div>
            @endforeach
        </section>
        <form method="GET" class="grid gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_170px_170px_170px_auto]">
            @if(request('journey_id'))<input type="hidden" name="journey_id" value="{{ (int) request('journey_id') }}">@endif
            @if(request('pipeline_stage_id'))<input type="hidden" name="pipeline_stage_id" value="{{ (int) request('pipeline_stage_id') }}">@endif
            <label class="sr-only" for="q">Rechercher</label><input id="q" name="q" value="{{ request('q') }}" placeholder="Nom ou adresse email" class="rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
            <label class="sr-only" for="status">Statut</label><select id="status" name="status" class="rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"><option value="">Tous les statuts</option>@foreach(['new'=>'Nouveau','qualifying'=>'À qualifier','contacted'=>'Échange en cours','converted'=>'Converti','not_now'=>'Pas maintenant'] as $key=>$label)<option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>@endforeach</select>
            <label class="sr-only" for="tag_id">Étiquette</label><select id="tag_id" name="tag_id" class="rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"><option value="">Toutes les étiquettes</option>@foreach($tags as $tag)<option value="{{ $tag->id }}" @selected((int)request('tag_id')===$tag->id)>{{ $tag->name }}</option>@endforeach</select>
            <label class="sr-only" for="segment_id">Segment</label><select id="segment_id" name="segment_id" class="rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"><option value="">Tous les segments</option>@foreach($segments as $segment)<option value="{{ $segment->id }}" @selected((int)request('segment_id')===$segment->id)>{{ $segment->name }}</option>@endforeach</select>
            <button class="rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#526509]">Filtrer</button>
        </form>
        @if($commercialToolsEnabled)
            <section class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2"><span class="text-xs font-semibold uppercase text-gray-500">Filtres enregistrés</span>@forelse($savedFilters as $filter)<span class="inline-flex items-center rounded-md border border-gray-200 bg-gray-50"><a href="{{ route('offer-journeys.contacts.index', $filter->filters_json) }}" class="px-2.5 py-1.5 text-xs font-medium text-gray-700">{{ $filter->name }}</a><form method="POST" action="{{ route('offer-journeys.contacts.filters.destroy', $filter) }}">@csrf @method('DELETE')<button class="px-2 py-1.5 text-xs text-red-700" title="Supprimer ce filtre">×</button></form></span>@empty<span class="text-xs text-gray-500">Aucun</span>@endforelse</div>
                <form method="POST" action="{{ route('offer-journeys.contacts.filters.store') }}" class="flex gap-2">@csrf<input name="name" required maxlength="80" placeholder="Nom du filtre" class="min-w-0 rounded-md border-gray-300 text-sm"><input type="hidden" name="filters[q]" value="{{ request('q') }}"><input type="hidden" name="filters[status]" value="{{ request('status') }}"><input type="hidden" name="filters[tag_id]" value="{{ request('tag_id') }}"><input type="hidden" name="filters[journey_id]" value="{{ request('journey_id') }}"><button class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700">Enregistrer la vue</button></form>
            </section>
        @endif
        @if($dueTasks->isNotEmpty())<section class="rounded-lg border border-amber-200 bg-amber-50 p-4"><h2 class="font-semibold text-amber-950">Prochaines actions</h2><div class="mt-3 grid gap-2 md:grid-cols-2">@foreach($dueTasks as $task)<a href="{{ route('offer-journeys.contacts.show', $task->contact) }}" class="rounded-md border border-amber-200 bg-white px-3 py-2 text-sm"><span class="font-medium text-gray-900">{{ $task->title }}</span><span class="mt-1 block text-xs text-gray-500">{{ $task->contact?->display_name }} · {{ $task->due_at->diffForHumans() }}</span></a>@endforeach</div></section>@endif
        @if($commercialToolsEnabled)<form method="POST" action="{{ route('offer-journeys.contacts.bulk') }}" x-data="{ selected: [] }" class="space-y-3">@csrf
            <div class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-3 lg:flex-row lg:items-center"><span class="text-sm font-medium text-gray-700" x-text="selected.length + (selected.length === 1 ? ' sélectionné' : ' sélectionnés')"></span><select name="action" required class="rounded-md border-gray-300 text-sm"><option value="">Action groupée prudente</option><option value="move_stage">Déplacer dans une étape</option><option value="add_tag">Ajouter une étiquette</option><option value="set_status">Changer le statut</option></select><select name="pipeline_stage_id" class="rounded-md border-gray-300 text-sm"><option value="">Étape</option>@foreach($pipelineStages as $stage)<option value="{{ $stage->id }}">{{ $stage->name }}</option>@endforeach</select><select name="tag_id" class="rounded-md border-gray-300 text-sm"><option value="">Étiquette</option>@foreach($tags as $tag)<option value="{{ $tag->id }}">{{ $tag->name }}</option>@endforeach</select><select name="status" class="rounded-md border-gray-300 text-sm"><option value="">Statut</option>@foreach(['new'=>'Nouveau','qualifying'=>'À qualifier','contacted'=>'Échange en cours','converted'=>'Converti','not_now'=>'Pas maintenant'] as $key=>$label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select><input type="hidden" name="confirm_count" :value="selected.length"><button class="rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b]" :disabled="selected.length === 0">Appliquer sans envoyer</button></div>
        @endif
        <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            @if($contacts->isEmpty())
                <div class="px-5 py-10 text-center"><h2 class="font-semibold text-gray-900">Aucun contact pour le moment</h2><p class="mt-2 text-sm text-gray-600">Les personnes qui remplissent un formulaire public apparaîtront ici.</p></div>
            @else
                <div @class([
                    'hidden gap-4 border-b border-gray-200 bg-gray-50 px-4 py-3 text-xs font-semibold text-gray-600 lg:grid',
                    'lg:grid-cols-[auto_minmax(180px,1.4fr)_repeat(4,minmax(120px,1fr))_auto]' => $commercialToolsEnabled,
                    'lg:grid-cols-[minmax(180px,1.4fr)_repeat(4,minmax(120px,1fr))_auto]' => ! $commercialToolsEnabled,
                ])>
                    @if($commercialToolsEnabled)<span aria-hidden="true"></span>@endif
                    <span>Contact</span><span>Étape</span><span>Origine</span><span>Suivi</span><span>Activité</span><span class="sr-only">Ouvrir</span>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach($contacts as $contact)
                        @php
                            $contactJourneys = $contact->entries->pluck('journey.name')->filter()->unique()->values();
                            $primaryJourney = $contactJourneys->first();
                            $otherJourneyCount = max(0, $contactJourneys->count() - 1);
                            $marketingConsent = $contact->consents->first(fn ($consent) => $consent->status === 'granted' && ! $consent->withdrawn_at);
                            $nextTask = $contact->tasks->first();
                        @endphp
                        <li @class([
                            'grid gap-3 px-4 py-4 text-sm lg:gap-4',
                            'grid-cols-[auto_minmax(0,1fr)_auto] lg:grid-cols-[auto_minmax(180px,1.4fr)_repeat(4,minmax(120px,1fr))_auto]' => $commercialToolsEnabled,
                            'grid-cols-[minmax(0,1fr)_auto] lg:grid-cols-[minmax(180px,1.4fr)_repeat(4,minmax(120px,1fr))_auto]' => ! $commercialToolsEnabled,
                        ])>
                            @if($commercialToolsEnabled)
                                <label class="pt-1"><span class="sr-only">Sélectionner {{ $contact->display_name }}</span><input type="checkbox" name="contact_ids[]" value="{{ $contact->id }}" x-model="selected" class="rounded border-gray-300 text-[#647a0b]"></label>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate font-medium text-gray-900">{{ $contact->display_name }}</p>
                                <p class="truncate text-gray-500">{{ $contact->email ?: ($contact->phone ?: 'Coordonnées à compléter') }}</p>
                                @if($contact->email && $contact->phone)<p class="truncate text-xs text-gray-400">{{ $contact->phone }}</p>@endif
                            </div>
                            <a href="{{ route('offer-journeys.contacts.show', $contact) }}" class="font-semibold text-[#647a0b] hover:text-[#854f38] lg:order-last lg:text-right">Ouvrir</a>
                            <div @class(['col-span-2 lg:col-span-1 lg:col-start-auto', 'col-start-2' => $commercialToolsEnabled, 'col-start-1' => ! $commercialToolsEnabled])>
                                <p class="text-xs font-medium text-gray-400 lg:hidden">Étape</p>
                                <p class="text-gray-700">{{ $contact->pipelineStage?->name ?? 'Nouveau contact' }}</p>
                                @if($contact->converted_at)<p class="mt-1 text-xs font-medium text-green-700">Réservé ou acheté</p>@endif
                            </div>
                            <div @class(['col-span-2 min-w-0 lg:col-span-1 lg:col-start-auto', 'col-start-2' => $commercialToolsEnabled, 'col-start-1' => ! $commercialToolsEnabled])>
                                <p class="text-xs font-medium text-gray-400 lg:hidden">Origine de la demande</p>
                                <p class="truncate text-gray-700">{{ $primaryJourney ?: '—' }}</p>
                                @if($otherJourneyCount > 0)<p class="text-xs text-gray-400">+ {{ $otherJourneyCount }} {{ $otherJourneyCount === 1 ? 'autre' : 'autres' }}</p>@endif
                            </div>
                            <div @class(['col-span-2 lg:col-span-1 lg:col-start-auto', 'col-start-2' => $commercialToolsEnabled, 'col-start-1' => ! $commercialToolsEnabled])>
                                <p class="text-xs font-medium text-gray-400 lg:hidden">Suivi</p>
                                <p class="text-xs font-medium {{ $marketingConsent ? 'text-green-700' : 'text-gray-500' }}">{{ $marketingConsent ? 'Suivis acceptés' : 'Pas de suivi marketing' }}</p>
                                @if($nextTask)<p class="mt-1 text-xs text-gray-600">{{ $nextTask->title }}@if($nextTask->due_at) · {{ $nextTask->due_at->diffForHumans() }}@endif</p>@else<p class="mt-1 text-xs text-gray-400">Aucune action planifiée</p>@endif
                            </div>
                            <div @class(['col-span-2 lg:col-span-1 lg:col-start-auto', 'col-start-2' => $commercialToolsEnabled, 'col-start-1' => ! $commercialToolsEnabled])>
                                <p class="text-xs font-medium text-gray-400 lg:hidden">Dernière activité</p>
                                <p class="text-gray-600">{{ $contact->last_activity_at?->diffForHumans() ?? '—' }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
        @if($commercialToolsEnabled)</form>@endif
        {{ $contacts->links() }}
    </div></div>
</x-app-layout>
