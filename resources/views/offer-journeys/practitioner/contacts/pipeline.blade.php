<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('offer-journeys.contacts.index') }}" class="text-sm font-medium text-[#647a0b] hover:text-[#854f38]">Personnes intéressées</a>
                <h1 class="mt-1 text-2xl font-semibold text-gray-900">Suivi des contacts</h1>
                <p class="mt-1 text-sm text-gray-500">Suivez les prochaines actions sans modifier vos dossiers clients.</p>
            </div>
            <a href="{{ route('offer-journeys.contacts.index') }}" class="inline-flex w-fit rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Vue liste</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('success') }}</div>
            @endif
            @if($errors->any())<div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

            @if($commercialToolsEnabled)
                <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"><div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"><div><h2 class="font-semibold text-gray-900">Objectif de {{ now()->translatedFormat('F Y') }}</h2><p class="mt-1 text-sm text-gray-500">Fixez un repère global ou propre à un parcours. Les conversions réelles restent la source de vérité.</p><div class="mt-2 flex flex-wrap gap-2">@forelse($goals as $goal)<span class="rounded-full bg-[#f0f4df] px-2.5 py-1 text-xs font-medium text-[#526509]">{{ $goal->offer_journey_id ? ($journeys->firstWhere('id', $goal->offer_journey_id)?->name ?? 'Parcours') : 'Tous les parcours' }}: {{ $goal->target_count }}</span>@empty<span class="text-xs text-gray-500">Aucun objectif défini.</span>@endforelse</div></div><form method="POST" action="{{ route('offer-journeys.contacts.goals.store') }}" class="grid gap-2 sm:grid-cols-[180px_120px_auto]">@csrf<input type="hidden" name="period" value="{{ $period }}"><select name="journey_id" class="rounded-md border-gray-300 text-sm"><option value="">Tous les parcours</option>@foreach($journeys as $journey)<option value="{{ $journey->id }}">{{ $journey->name }}</option>@endforeach</select><input type="number" name="target_count" min="1" max="100000" required placeholder="Objectif" class="rounded-md border-gray-300 text-sm"><button class="rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b]">Enregistrer</button></form></div></section>
            @endif

            <div class="grid min-w-0 gap-4 pb-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($stages as $stage)
                    <section class="pipeline-stage min-w-0 rounded-lg border border-gray-200 bg-gray-50" data-stage-id="{{ $stage->id }}">
                        <header class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
                            <h2 class="font-semibold text-gray-900">{{ $stage->name }}</h2>
                            <span data-stage-count class="rounded-full bg-white px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-200">{{ $stage->contacts_count }}</span>
                        </header>
                        <div class="min-h-28 space-y-3 p-3">
                            @forelse($stage->contacts as $contact)
                                <article class="pipeline-card rounded-md border border-gray-200 bg-white p-3 shadow-sm" draggable="{{ $commercialToolsEnabled ? 'true' : 'false' }}" data-move-url="{{ route('offer-journeys.contacts.pipeline.move', $contact) }}">
                                    <a href="{{ route('offer-journeys.contacts.show', $contact) }}" class="block font-medium text-gray-900 hover:text-[#647a0b]">{{ $contact->display_name }}</a>
                                    <p class="mt-1 truncate text-sm text-gray-500">{{ $contact->email ?: $contact->phone }}</p>
                                    <p class="mt-2 text-xs text-gray-400">{{ $contact->last_activity_at?->diffForHumans() ?? 'Aucune activité' }}</p>
                                    <form method="POST" action="{{ route('offer-journeys.contacts.pipeline.move', $contact) }}" class="mt-3 flex gap-2">
                                        @csrf
                                        @method('PUT')
                                        <label for="stage-{{ $contact->id }}" class="sr-only">Déplacer {{ $contact->display_name }}</label>
                                        <select id="stage-{{ $contact->id }}" name="pipeline_stage_id" class="min-w-0 flex-1 rounded-md border-gray-300 py-1.5 text-xs focus:border-[#647a0b] focus:ring-[#647a0b]">
                                            @foreach($stages as $targetStage)
                                                <option value="{{ $targetStage->id }}" @selected($targetStage->id === $stage->id)>{{ $targetStage->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="rounded-md border border-[#647a0b] px-2 py-1.5 text-xs font-semibold text-[#647a0b] hover:bg-[#f7f9ec]">Déplacer</button>
                                    </form>
                                    @if($commercialToolsEnabled)<details class="mt-2"><summary class="cursor-pointer text-xs font-medium text-gray-500">Motif ou date de reprise</summary><form method="POST" action="{{ route('offer-journeys.contacts.pipeline.move', $contact) }}" class="mt-2 space-y-2">@csrf @method('PUT')<select name="pipeline_stage_id" class="block w-full rounded-md border-gray-300 text-xs">@foreach($stages as $targetStage)<option value="{{ $targetStage->id }}" @selected($targetStage->id === $stage->id)>{{ $targetStage->name }}</option>@endforeach</select><input name="reason" value="{{ $contact->pipeline_outcome_reason }}" maxlength="255" placeholder="Motif du report ou de la perte" class="block w-full rounded-md border-gray-300 text-xs"><input type="date" name="next_action_at" value="{{ $contact->next_action_at?->format('Y-m-d') }}" class="block w-full rounded-md border-gray-300 text-xs"><button class="text-xs font-semibold text-[#647a0b]">Enregistrer le suivi</button></form></details>@endif
                                </article>
                            @empty
                                <p class="px-2 py-6 text-center text-sm text-gray-500">Aucun contact</p>
                            @endforelse
                        </div>
                        @if($stage->contacts_count > $stage->contacts->count())
                            <footer class="border-t border-gray-200 px-4 py-3 text-xs text-gray-500">{{ $stage->contacts->count() }} affichés sur {{ $stage->contacts_count }} · <a href="{{ route('offer-journeys.contacts.index', ['pipeline_stage_id' => $stage->id]) }}" class="font-semibold text-[#647a0b]">Voir la liste complète</a></footer>
                        @endif
                    </section>
                @endforeach
            </div>
        </div>
    </div>
    @if($commercialToolsEnabled)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                let dragged = null;
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                document.querySelectorAll('.pipeline-card').forEach((card) => card.addEventListener('dragstart', () => dragged = card));
                document.querySelectorAll('.pipeline-stage').forEach((stage) => {
                    stage.addEventListener('dragover', (event) => event.preventDefault());
                    stage.addEventListener('drop', async (event) => {
                        event.preventDefault();
                        if (!dragged) return;
                        const response = await fetch(dragged.dataset.moveUrl, {method: 'PUT', headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token}, body: JSON.stringify({pipeline_stage_id: stage.dataset.stageId})});
                        const result = await response.json();
                        if (response.ok) {
                            const sourceStage = dragged.closest('.pipeline-stage');
                            if (sourceStage === stage) return;
                            stage.querySelector('.min-h-28').appendChild(dragged);
                            dragged.querySelector('select[name="pipeline_stage_id"]')?.setAttribute('value', stage.dataset.stageId);
                            dragged.querySelectorAll('select[name="pipeline_stage_id"]').forEach((select) => select.value = stage.dataset.stageId);
                            const sourceCount = sourceStage?.querySelector('[data-stage-count]');
                            const targetCount = stage.querySelector('[data-stage-count]');
                            if (sourceCount) sourceCount.textContent = Math.max(0, Number(sourceCount.textContent) - 1);
                            if (targetCount) targetCount.textContent = Number(targetCount.textContent) + 1;
                        }
                        else window.alert(result.message || 'Ce déplacement nécessite des informations complémentaires.');
                    });
                });
            });
        </script>
    @endif
</x-app-layout>
