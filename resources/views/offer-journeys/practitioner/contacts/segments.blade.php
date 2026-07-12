<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('offer-journeys.contacts.index') }}" class="text-sm font-medium text-[#647a0b] hover:text-[#854f38]">Personnes intéressées</a>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900">Segments et étiquettes</h1>
            <p class="mt-1 text-sm text-gray-500">Regroupez les personnes selon leur activité et vos repères internes.</p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_420px] lg:px-8">
            <div class="space-y-5">
                @if(session('success'))<div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

                <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h2 class="font-semibold text-gray-900">Segments enregistrés</h2>
                        <p class="mt-1 text-sm text-gray-500">Le contenu est recalculé automatiquement. Ajouter une étiquette ne donne jamais un consentement marketing.</p>
                    </div>
                    @forelse($segments as $segment)
                        <article class="border-b border-gray-100 px-5 py-4 last:border-0">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <a href="{{ route('offer-journeys.contacts.index', ['segment_id' => $segment->id]) }}" class="font-medium text-gray-900 hover:text-[#647a0b]">{{ $segment->name }}</a>
                                    <p class="mt-1 text-sm text-gray-500">{{ $segment->contacts_count }} {{ $segment->contacts_count > 1 ? 'personnes' : 'personne' }} · {{ $segment->match_type === 'any' ? 'au moins une règle' : 'toutes les règles' }}</p>
                                    @if($segment->description)<p class="mt-1 text-sm text-gray-600">{{ $segment->description }}</p>@endif
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach($segment->rules as $rule)
                                            @php
                                                $value = $rule->value_json['value'] ?? null;
                                                $label = match($rule->field) {
                                                    'tag' => ($rule->operator === 'missing' ? 'Sans ' : 'Avec ').($tags->firstWhere('id', (int) $value)?->name ?? 'étiquette supprimée'),
                                                    'journey' => 'Parcours : '.($journeys->firstWhere('id', (int) $value)?->name ?? 'supprimé'),
                                                    'inactive_days' => 'Sans activité depuis '.$value.' jours',
                                                    'marketing_consent' => 'Consentement marketing actif',
                                                    default => 'Statut : '.$value,
                                                };
                                            @endphp
                                            <span class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600">{{ $label }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('offer-journeys.contacts.segments.destroy', $segment) }}" onsubmit="return confirm('Supprimer ce segment ? Les contacts et étiquettes ne seront pas supprimés.')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-red-700">Supprimer</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="px-5 py-10 text-center"><p class="font-medium text-gray-900">Aucun segment</p><p class="mt-1 text-sm text-gray-500">Créez votre premier groupe de personnes à suivre.</p></div>
                    @endforelse
                </section>
            </div>

            <aside class="space-y-5">
                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm" x-data="{ rules: [{ field: 'tag', operator: 'has' }] }">
                    <h2 class="font-semibold text-gray-900">Créer un segment</h2>
                    <p class="mt-1 text-xs text-gray-500">Combinez jusqu’à 10 règles. Les étiquettes des contacts et des fiches clients liées sont prises en compte.</p>
                    <form method="POST" action="{{ route('offer-journeys.contacts.segments.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div><label for="segment-name" class="block text-sm font-medium text-gray-700">Nom</label><input id="segment-name" name="name" required maxlength="120" value="{{ old('name') }}" placeholder="Ex. Intéressés par les ateliers" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                        <div><label for="segment-description" class="block text-sm font-medium text-gray-700">Description facultative</label><textarea id="segment-description" name="description" rows="2" maxlength="500" class="mt-1 block w-full rounded-md border-gray-300 text-sm">{{ old('description') }}</textarea></div>
                        <fieldset><legend class="text-sm font-medium text-gray-700">Correspondance</legend><div class="mt-2 grid grid-cols-2 gap-2"><label class="rounded-md border border-gray-200 p-2 text-sm"><input type="radio" name="match_type" value="all" checked class="text-[#647a0b]"> Toutes les règles</label><label class="rounded-md border border-gray-200 p-2 text-sm"><input type="radio" name="match_type" value="any" class="text-[#647a0b]"> Au moins une</label></div></fieldset>

                        <div class="space-y-3">
                            <template x-for="(rule, index) in rules" :key="index">
                                <div class="rounded-md border border-gray-200 bg-gray-50 p-3">
                                    <div class="flex items-center justify-between"><p class="text-xs font-semibold uppercase text-gray-500" x-text="'Règle ' + (index + 1)"></p><button type="button" x-show="rules.length > 1" @click="rules.splice(index, 1)" class="text-xs font-semibold text-red-700">Retirer</button></div>
                                    <select x-model="rule.field" :name="`rules[${index}][field]`" class="mt-2 block w-full rounded-md border-gray-300 text-sm">
                                        <option value="tag">Étiquette</option><option value="status">Statut du contact</option><option value="journey">Parcours d’origine</option><option value="inactive_days">Sans activité depuis</option><option value="marketing_consent">Consentement marketing</option>
                                    </select>
                                    <select x-show="rule.field === 'tag'" :disabled="rule.field !== 'tag'" x-model="rule.operator" :name="`rules[${index}][operator]`" class="mt-2 block w-full rounded-md border-gray-300 text-sm"><option value="has">Possède l’étiquette</option><option value="missing">Ne possède pas l’étiquette</option></select>
                                    <select x-show="rule.field === 'tag'" :disabled="rule.field !== 'tag'" :name="`rules[${index}][value]`" class="mt-2 block w-full rounded-md border-gray-300 text-sm">@foreach($tags as $tag)<option value="{{ $tag->id }}">{{ $tag->name }}</option>@endforeach</select>
                                    <select x-show="rule.field === 'status'" :disabled="rule.field !== 'status'" :name="`rules[${index}][value]`" class="mt-2 block w-full rounded-md border-gray-300 text-sm"><option value="new">Nouveau</option><option value="qualifying">À qualifier</option><option value="contacted">Échange en cours</option><option value="converted">Converti</option><option value="not_now">Pas maintenant</option></select>
                                    <select x-show="rule.field === 'journey'" :disabled="rule.field !== 'journey'" :name="`rules[${index}][value]`" class="mt-2 block w-full rounded-md border-gray-300 text-sm">@foreach($journeys as $journey)<option value="{{ $journey->id }}">{{ $journey->name }}</option>@endforeach</select>
                                    <input x-show="rule.field === 'inactive_days'" :disabled="rule.field !== 'inactive_days'" type="number" min="1" max="3650" value="30" :name="`rules[${index}][value]`" class="mt-2 block w-full rounded-md border-gray-300 text-sm">
                                    <input x-show="rule.field === 'marketing_consent'" :disabled="rule.field !== 'marketing_consent'" type="hidden" value="1" :name="`rules[${index}][value]`">
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="rules.push({ field: 'tag', operator: 'has' })" class="text-sm font-semibold text-[#647a0b]">+ Ajouter une règle</button>
                        <button class="w-full rounded-md bg-[#647a0b] px-3 py-2 text-sm font-semibold text-white hover:bg-[#526509]">Créer le segment</button>
                    </form>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-gray-900">Nouvelle étiquette</h2>
                    <p class="mt-1 text-xs text-gray-500">Utilisez un repère non sensible, par exemple « atelier juillet » ou « bon cadeau ».</p>
                    <form method="POST" action="{{ route('offer-journeys.contacts.tags.store') }}" class="mt-4 flex gap-2">@csrf<input name="name" required maxlength="80" placeholder="Nom de l’étiquette" class="min-w-0 flex-1 rounded-md border-gray-300 text-sm"><button class="rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b]">Ajouter</button></form>
                    <div class="mt-4 flex flex-wrap gap-2">@foreach($tags as $tag)<span class="rounded-full bg-[#f0f4df] px-2.5 py-1 text-xs font-medium text-[#526509]">{{ $tag->name }}</span>@endforeach</div>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
