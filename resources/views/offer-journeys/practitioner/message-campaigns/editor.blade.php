<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('offer-journeys.message-campaigns.index') }}" class="text-sm font-medium text-[#647a0b] hover:text-[#526509]">Campagnes email</a>
                <h1 class="mt-1 text-2xl font-semibold text-gray-900">Éditeur d’email</h1>
                <p class="mt-1 text-sm text-gray-500">Composez votre message par blocs, puis vérifiez exactement ce qui sera envoyé.</p>
            </div>
            <span class="w-fit rounded-full px-2.5 py-1 text-xs font-semibold {{ $locked ? 'bg-amber-100 text-amber-900' : 'bg-[#f0f4df] text-[#526509]' }}">{{ $locked ? 'Campagne programmée' : 'Brouillon' }}</span>
        </div>
    </x-slot>

    @php
        $assetPayload = $campaign->emailAssets->map(fn ($asset) => [
            'id' => $asset->id,
            'url' => Storage::disk('public')->url($asset->path),
            'name' => $asset->original_name,
            'width' => $asset->width,
            'height' => $asset->height,
        ])->values();
        $segmentSummaries = $segments->mapWithKeys(fn ($segment) => [$segment->id => $segment->audience_summary])->all();
    @endphp

    <div class="py-5" x-data="offerJourneyEmailEditor({
        locked: @js($locked),
        name: @js($campaign->name),
        subject: @js($campaign->subject),
        preheader: @js($campaign->preheader ?? ''),
        blocks: @js(data_get($campaign->content_json, 'blocks', [])),
        style: @js($campaign->style_json ?? app(\App\Domain\OfferJourneys\Services\OfferJourneyEmailContent::class)->defaultStyle()),
        assets: @js($assetPayload),
        templates: @js($templates),
        audienceType: @js($campaign->audience_type ?? 'journeys'),
        segmentId: @js((string) ($campaign->offer_journey_segment_id ?? '')),
        journeyIds: @js($campaign->journeys->pluck('id')->map(fn ($id) => (int) $id)->values()),
        segmentSummaries: @js($segmentSummaries),
        urls: {
            save: @js(route('offer-journeys.email-editor.autosave', $campaign)),
            preview: @js(route('offer-journeys.email-editor.preview', $campaign)),
            upload: @js(route('offer-journeys.email-editor.assets.store', $campaign)),
            estimate: @js(route('offer-journeys.message-campaigns.estimate')),
            assetBase: @js(url('/dashboard-pro/parcours-offres/editeur-email/'.$campaign->id.'/images'))
        },
        csrf: @js(csrf_token())
    })" x-init="init()">
        <div class="mx-auto max-w-[1500px] px-4 sm:px-6 lg:px-8">
            @if(session('success'))<div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

            @if($locked)
                <div class="mb-4 flex flex-col gap-3 rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between">
                    <p>Cette campagne est programmée et verrouillée. Repassez-la explicitement en brouillon avant toute modification.</p>
                    <form method="POST" action="{{ route('offer-journeys.message-campaigns.return-to-draft', $campaign) }}">@csrf<button class="font-semibold underline">Repasser en brouillon</button></form>
                </div>
            @endif

            <div class="mb-4 grid grid-cols-2 gap-2 lg:hidden" role="tablist">
                <button type="button" @click="mobileTab='content'" :class="mobileTab==='content' ? 'bg-[#647a0b] text-white' : 'border border-gray-300 bg-white text-gray-700'" class="rounded-md px-3 py-2 text-sm font-semibold">Contenu</button>
                <button type="button" @click="mobileTab='preview'" :class="mobileTab==='preview' ? 'bg-[#647a0b] text-white' : 'border border-gray-300 bg-white text-gray-700'" class="rounded-md px-3 py-2 text-sm font-semibold">Aperçu</button>
            </div>

            <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,720px)_minmax(420px,1fr)]">
                <div :class="mobileTab==='content' ? 'block' : 'hidden'" class="space-y-4 lg:block">
                    <fieldset :disabled="locked" class="space-y-4 disabled:opacity-70">
                        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3"><div><h2 class="font-semibold text-gray-900">Informations de la campagne</h2><p class="mt-1 text-xs text-gray-500">Le nom reste interne. L’objet et le texte d’aperçu sont visibles dans la boîte de réception.</p></div><span class="text-xs text-gray-500" aria-live="polite" x-text="saveStatus"></span></div>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2"><label for="editor-name" class="block text-sm font-medium text-gray-700">Nom interne</label><input id="editor-name" x-model="name" @input.debounce.600ms="queueSave()" maxlength="120" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                                <div><label for="editor-subject" class="block text-sm font-medium text-gray-700">Objet</label><input id="editor-subject" x-model="subject" @focus="focusTarget={kind:'subject'}" @input.debounce.600ms="queueSave()" maxlength="180" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><p class="mt-1 text-xs text-gray-500"><span x-text="subject.length"></span>/180</p></div>
                                <div><label for="editor-preheader" class="block text-sm font-medium text-gray-700">Texte d’aperçu</label><input id="editor-preheader" x-model="preheader" @focus="focusTarget={kind:'preheader'}" @input.debounce.600ms="queueSave()" maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><p class="mt-1 text-xs text-gray-500">Complète l’objet sans le répéter.</p></div>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2"><template x-for="variable in variables" :key="variable.key"><button type="button" @click="insertVariable(variable.key)" class="rounded-full border border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-700 hover:border-[#647a0b]" x-text="variable.label"></button></template></div>
                        </section>

                        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
                                <div><label for="email-template" class="block text-sm font-medium text-gray-700">Partir d’un modèle</label><select id="email-template" x-model="templateKey" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="">Choisir un modèle</option><template x-for="template in templates" :key="template.key"><option :value="template.key" x-text="template.name + ' · ' + template.description"></option></template></select></div>
                                <div class="flex gap-2"><button type="button" @click="applyTemplate()" :disabled="!templateKey" class="rounded-md bg-[#647a0b] px-3 py-2 text-sm font-semibold text-white disabled:opacity-40">Utiliser</button><button type="button" @click="clearEmail()" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700">Email vide</button></div>
                            </div>
                        </section>

                        <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-200 p-5">
                                <div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="font-semibold text-gray-900">Contenu</h2><p class="mt-1 text-xs text-gray-500">Déplacez les blocs ou utilisez les flèches. Aucun code HTML n’est nécessaire.</p></div><div class="flex gap-1"><button type="button" @click="undo()" :disabled="history.length===0" title="Annuler" class="h-9 w-9 rounded-md border border-gray-300 text-lg disabled:opacity-30">↶</button><button type="button" @click="redo()" :disabled="future.length===0" title="Rétablir" class="h-9 w-9 rounded-md border border-gray-300 text-lg disabled:opacity-30">↷</button></div></div>
                                <div class="mt-4 grid grid-cols-3 gap-2 sm:grid-cols-5">
                                    <template x-for="item in palette" :key="item.type"><button type="button" @click="addBlock(item.type)" class="min-h-12 rounded-md border border-gray-200 px-2 py-2 text-xs font-semibold text-gray-700 hover:border-[#647a0b] hover:text-[#526509]" x-text="item.label"></button></template>
                                    <label class="flex min-h-12 cursor-pointer items-center justify-center rounded-md border border-dashed border-[#647a0b] px-2 py-2 text-center text-xs font-semibold text-[#526509]"><input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="uploadImage($event)"><span x-text="uploading ? 'Envoi…' : 'Ajouter une image'"></span></label>
                                </div>
                                <div x-show="assets.length" class="mt-4 border-t border-gray-100 pt-3"><p class="text-xs font-semibold uppercase text-gray-500">Images de cette campagne</p><div class="mt-2 flex gap-2 overflow-x-auto pb-1"><template x-for="item in assets" :key="item.id"><div class="w-24 shrink-0"><img :src="item.url" :alt="item.name" class="h-14 w-24 rounded border border-gray-200 object-cover"><button type="button" @click="deleteAsset(item)" :disabled="assetUsed(item.id)" :title="assetUsed(item.id) ? 'Retirez d’abord cette image du message' : 'Supprimer cette image'" class="mt-1 w-full truncate text-xs font-medium text-red-700 disabled:text-gray-400" x-text="assetUsed(item.id) ? 'Utilisée' : 'Supprimer'"></button></div></template></div></div>
                            </div>

                            <div class="space-y-3 p-4" @dragover.prevent>
                                <template x-for="(block,index) in blocks" :key="block.id">
                                    <article draggable="true" @dragstart="dragIndex=index" @drop.prevent="dropAt(index)" class="rounded-md border border-gray-200 bg-gray-50 p-3">
                                        <div class="flex items-center justify-between gap-2 border-b border-gray-200 pb-2"><button type="button" title="Déplacer le bloc" class="cursor-grab text-gray-400">⋮⋮</button><strong class="min-w-0 flex-1 truncate text-xs uppercase text-gray-600" x-text="blockLabel(block.type)"></strong><div class="flex gap-1"><button type="button" @click="move(index,-1)" :disabled="index===0" title="Monter" class="h-7 w-7 rounded border border-gray-300 text-sm disabled:opacity-30">↑</button><button type="button" @click="move(index,1)" :disabled="index===blocks.length-1" title="Descendre" class="h-7 w-7 rounded border border-gray-300 text-sm disabled:opacity-30">↓</button><button type="button" @click="duplicate(index)" title="Dupliquer" class="h-7 w-7 rounded border border-gray-300 text-xs">⧉</button><button type="button" @click="remove(index)" title="Supprimer" class="h-7 w-7 rounded border border-red-200 text-xs text-red-700">×</button></div></div>
                                        <div class="mt-3 grid gap-3">
                                            <template x-if="block.type==='heading'"><div class="grid gap-2 sm:grid-cols-[1fr_100px_110px]"><input x-model="block.data.text" @focus="focusBlock(index,'text')" @input.debounce.500ms="queueSave()" maxlength="180" class="rounded-md border-gray-300 text-sm"><select x-model="block.data.level" @change="queueSave()" class="rounded-md border-gray-300 text-sm"><option value="h1">Grand</option><option value="h2">Moyen</option></select><select x-model="block.data.align" @change="queueSave()" class="rounded-md border-gray-300 text-sm"><option value="left">Gauche</option><option value="center">Centre</option><option value="right">Droite</option></select></div></template>
                                            <template x-if="block.type==='paragraph'"><div><textarea x-model="block.data.text" @focus="focusBlock(index,'text')" @input.debounce.500ms="queueSave()" rows="5" maxlength="3000" class="block w-full rounded-md border-gray-300 text-sm"></textarea><select x-model="block.data.align" @change="queueSave()" class="mt-2 rounded-md border-gray-300 text-sm"><option value="left">Aligné à gauche</option><option value="center">Centré</option><option value="right">Aligné à droite</option></select></div></template>
                                            <template x-if="block.type==='image'"><div class="grid gap-3 sm:grid-cols-[140px_1fr]"><img :src="asset(block.data.asset_id)?.url" :alt="block.data.alt" class="h-28 w-full object-cover"><div class="space-y-2"><input x-model="block.data.alt" @input.debounce.500ms="queueSave()" maxlength="180" placeholder="Description obligatoire de l’image" class="block w-full rounded-md border-gray-300 text-sm"><div class="grid grid-cols-2 gap-2"><select x-model="block.data.width" @change="queueSave()" class="rounded-md border-gray-300 text-sm"><option value="full">Pleine largeur</option><option value="large">Large</option><option value="medium">Moyenne</option></select><select x-model="block.data.align" @change="queueSave()" class="rounded-md border-gray-300 text-sm"><option value="left">Gauche</option><option value="center">Centre</option><option value="right">Droite</option></select></div></div></div></template>
                                            <template x-if="block.type==='button'"><div class="grid gap-2 sm:grid-cols-2"><input x-model="block.data.label" @focus="focusBlock(index,'label')" @input.debounce.500ms="queueSave()" maxlength="80" placeholder="Texte du bouton" class="rounded-md border-gray-300 text-sm"><input x-model="block.data.url" @focus="focusBlock(index,'url')" @input.debounce.500ms="queueSave()" maxlength="2000" placeholder="https://… ou @{{lien_offre}}" class="rounded-md border-gray-300 text-sm"><select x-model="block.data.variant" @change="queueSave()" class="rounded-md border-gray-300 text-sm"><option value="filled">Plein</option><option value="outline">Contour</option></select><select x-model="block.data.align" @change="queueSave()" class="rounded-md border-gray-300 text-sm"><option value="left">Gauche</option><option value="center">Centre</option><option value="right">Droite</option></select></div></template>
                                            <template x-if="block.type==='callout'"><div class="space-y-2"><input x-model="block.data.title" @focus="focusBlock(index,'title')" @input.debounce.500ms="queueSave()" maxlength="120" placeholder="Titre facultatif" class="block w-full rounded-md border-gray-300 text-sm"><textarea x-model="block.data.text" @focus="focusBlock(index,'text')" @input.debounce.500ms="queueSave()" rows="3" maxlength="1200" class="block w-full rounded-md border-gray-300 text-sm"></textarea><select x-model="block.data.tone" @change="queueSave()" class="rounded-md border-gray-300 text-sm"><option value="olive">Vert doux</option><option value="neutral">Neutre</option></select></div></template>
                                            <template x-if="block.type==='divider'"><p class="text-xs text-gray-500">Une ligne discrète séparera les sections.</p></template>
                                            <template x-if="block.type==='spacer'"><select x-model="block.data.size" @change="queueSave()" class="w-full rounded-md border-gray-300 text-sm"><option value="small">Petit espace</option><option value="medium">Espace moyen</option><option value="large">Grand espace</option></select></template>
                                            <template x-if="block.type==='details'"><div class="space-y-2"><input x-model="block.data.title" @focus="focusBlock(index,'title')" @input.debounce.500ms="queueSave()" maxlength="120" class="block w-full rounded-md border-gray-300 text-sm"><textarea x-model="block.data.text" @focus="focusBlock(index,'text')" @input.debounce.500ms="queueSave()" rows="4" maxlength="1500" placeholder="Date, lieu, tarif…" class="block w-full rounded-md border-gray-300 text-sm"></textarea></div></template>
                                            <template x-if="block.type==='signature'"><div><textarea x-model="block.data.text" @focus="focusBlock(index,'text')" @input.debounce.500ms="queueSave()" rows="2" maxlength="500" class="block w-full rounded-md border-gray-300 text-sm"></textarea><label class="mt-2 flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" x-model="block.data.show_contact" @change="queueSave()" class="rounded border-gray-300 text-[#647a0b]"> Afficher mes coordonnées professionnelles</label></div></template>
                                        </div>
                                    </article>
                                </template>
                                <div x-show="blocks.length===0" class="py-10 text-center text-sm text-gray-500">Ajoutez un premier bloc ou choisissez un modèle.</div>
                            </div>
                        </section>

                        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <details><summary class="cursor-pointer font-semibold text-gray-900">Style de l’email</summary><div class="mt-4 grid gap-4 sm:grid-cols-3"><label class="text-sm text-gray-700">Couleur principale<input type="color" x-model="style.primary_color" @change="queueSave()" class="mt-1 h-10 w-full rounded border border-gray-300"></label><label class="text-sm text-gray-700">Fond général<input type="color" x-model="style.background_color" @change="queueSave()" class="mt-1 h-10 w-full rounded border border-gray-300"></label><label class="text-sm text-gray-700">Fond du message<input type="color" x-model="style.content_background" @change="queueSave()" class="mt-1 h-10 w-full rounded border border-gray-300"></label><label class="text-sm text-gray-700">Boutons<select x-model="style.button_style" @change="queueSave()" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="square">Discrets</option><option value="rounded">Arrondis</option><option value="pill">Très arrondis</option></select></label><label class="text-sm text-gray-700">Taille du texte<select x-model="style.text_size" @change="queueSave()" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="compact">Compacte</option><option value="normal">Normale</option><option value="large">Grande</option></select></label></div></details>
                        </section>

                        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 class="font-semibold text-gray-900">Destinataires</h2><p class="mt-1 text-xs text-gray-500">L’audience sera recalculée juste avant l’envoi.</p>
                            <div class="mt-4 grid grid-cols-2 gap-2"><label class="rounded-md border p-3 text-sm"><input type="radio" value="journeys" x-model="audienceType" @change="queueSave(); refreshEstimate()" class="text-[#647a0b]"> Contacts d’une page</label>@if(config('offer_journeys.segment_campaigns_enabled'))<label class="rounded-md border p-3 text-sm"><input type="radio" value="segment" x-model="audienceType" @change="queueSave(); refreshEstimate()" class="text-[#647a0b]"> Segment enregistré</label>@endif</div>
                            <div x-show="audienceType==='journeys'" class="mt-3 max-h-44 space-y-2 overflow-y-auto rounded-md border p-3">@forelse($journeys as $journey)<label class="flex items-center gap-2 text-sm"><input type="checkbox" value="{{ $journey->id }}" x-model.number="journeyIds" @change="queueSave(); refreshEstimate()" class="rounded border-gray-300 text-[#647a0b]">{{ $journey->name }}</label>@empty<p class="text-sm text-gray-500">Publiez d’abord une page.</p>@endforelse</div>
                            @if(config('offer_journeys.segment_campaigns_enabled'))<div x-show="audienceType==='segment'" class="mt-3 grid gap-3 sm:grid-cols-2"><select x-model="segmentId" @change="queueSave(); refreshEstimate()" class="rounded-md border-gray-300 text-sm"><option value="">Choisir un segment</option>@foreach($segments as $segment)<option value="{{ $segment->id }}">{{ $segment->name }}</option>@endforeach</select><select x-model.number="journeyIds[0]" @change="journeyIds=journeyIds[0] ? [Number(journeyIds[0])] : []; queueSave()" class="rounded-md border-gray-300 text-sm"><option value="">Page à promouvoir, facultatif</option>@foreach($journeys as $journey)<option value="{{ $journey->id }}">{{ $journey->name }}</option>@endforeach</select></div>@endif
                            <div class="mt-3 rounded-md bg-[#f7f9ec] p-3 text-sm text-gray-700" aria-live="polite"><span x-show="estimating">Calcul en cours…</span><template x-if="!estimating && estimate"><p><strong x-text="estimate.eligible"></strong> destinataire(s) actuellement joignable(s) sur <span x-text="estimate.matching"></span> personne(s) correspondante(s).</p></template><span x-show="!estimating && !estimate">Sélectionnez une audience pour obtenir une estimation.</span></div>
                        </section>
                    </fieldset>

                    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="font-semibold text-gray-900">Vérification</h2><p class="mt-1 text-xs text-gray-500">Les erreurs bloquent l’envoi. Les recommandations restent non bloquantes.</p></div>@unless($locked)<button type="button" @click="save(true)" class="rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#526509]">Enregistrer maintenant</button>@endunless</div>
                        <div class="mt-3 space-y-2"><template x-for="error in quality.errors" :key="error"><p class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-800" x-text="error"></p></template><template x-for="warning in quality.warnings" :key="warning"><p class="rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-900" x-text="warning"></p></template><p x-show="quality.errors.length===0 && quality.warnings.length===0" class="rounded-md bg-green-50 px-3 py-2 text-sm text-green-800">Le contenu est prêt à être testé.</p></div>
                    </section>

                    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <h2 class="font-semibold text-gray-900">Tester puis envoyer</h2><p class="mt-1 text-xs text-gray-500">Le test part uniquement à votre adresse. Les destinataires finaux sont revérifiés au moment de l’envoi.</p>
                        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                            <button type="button" @click="submitAfterSave('testForm')" class="rounded-md border border-[#647a0b] px-4 py-2 text-sm font-semibold text-[#526509]">Envoyer un test</button>
                            @unless($locked)<div class="flex flex-1 items-end gap-2"><div class="flex-1"><label for="schedule-date" class="block text-xs font-medium text-gray-600">Date et heure</label><input id="schedule-date" x-model="scheduledAt" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div><button type="button" @click="submitAfterSave('scheduleForm')" class="rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white">Programmer</button></div><button type="button" @click="confirm('Envoyer cette campagne dès que possible ? Les destinataires seront revérifiés.') && submitAfterSave('sendNowForm')" class="text-sm font-semibold text-[#526509]">Envoyer dès que possible</button>@endunless
                        </div>
                    </section>
                </div>

                <aside :class="mobileTab==='preview' ? 'block' : 'hidden'" class="lg:sticky lg:top-4 lg:block">
                    <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="font-semibold text-gray-900">Aperçu réel</h2><p class="mt-1 text-xs text-gray-500">Rendu par le même moteur que l’email envoyé.</p></div><div class="inline-flex rounded-md border border-gray-300 p-0.5"><button type="button" @click="previewMode='desktop'" :class="previewMode==='desktop' ? 'bg-gray-900 text-white' : 'text-gray-600'" class="rounded px-2.5 py-1 text-xs font-semibold">Ordinateur</button><button type="button" @click="previewMode='mobile'" :class="previewMode==='mobile' ? 'bg-gray-900 text-white' : 'text-gray-600'" class="rounded px-2.5 py-1 text-xs font-semibold">Téléphone</button></div></div>
                        <div class="mt-4 flex min-h-[680px] justify-center overflow-auto rounded-md bg-gray-100 p-2"><iframe title="Aperçu de l’email" sandbox="allow-popups allow-same-origin" :srcdoc="previewHtml" :style="previewMode==='mobile' ? 'width:390px' : 'width:100%'" class="min-h-[660px] max-w-full border-0 bg-white transition-all"></iframe></div>
                    </section>
                </aside>
            </div>
        </div>

        <form x-ref="testForm" method="POST" action="{{ route('offer-journeys.message-campaigns.test', $campaign) }}" class="hidden">@csrf</form>
        <form x-ref="scheduleForm" method="POST" action="{{ route('offer-journeys.message-campaigns.schedule', $campaign) }}" class="hidden">@csrf<input type="hidden" name="scheduled_at" :value="scheduledAt"></form>
        <form x-ref="sendNowForm" method="POST" action="{{ route('offer-journeys.message-campaigns.send-now', $campaign) }}" class="hidden">@csrf</form>
    </div>

    <script>
        function offerJourneyEmailEditor(config) {
            return {
                ...config, mobileTab: 'content', previewMode: 'desktop', previewHtml: '', saveStatus: config.locked ? 'Verrouillé' : 'Non enregistré', saving: false, uploading: false, estimate: null, estimating: false, quality: {errors: [], warnings: []}, history: [], future: [], lastSnapshot: JSON.stringify(config.blocks), dragIndex: null, templateKey: '', scheduledAt: '', focusTarget: {kind: 'subject'}, saveTimer: null,
                variables: [{key:'prenom',label:'Prénom'},{key:'offre',label:'Offre'},{key:'nom_praticien',label:'Nom du praticien'},{key:'lien_offre',label:'Lien de la page'}],
                palette: [{type:'heading',label:'Titre'},{type:'paragraph',label:'Texte'},{type:'button',label:'Bouton'},{type:'callout',label:'Encadré'},{type:'divider',label:'Séparateur'},{type:'spacer',label:'Espacement'},{type:'details',label:'Infos pratiques'},{type:'signature',label:'Signature'}],
                init() { this.refreshPreview(); this.refreshEstimate(); if (!this.locked) this.queueSave(); },
                uid() { return crypto.randomUUID ? crypto.randomUUID() : Date.now()+'-'+Math.random().toString(16).slice(2); },
                clone(value) { return JSON.parse(JSON.stringify(value)); },
                remember() {},
                defaultData(type) { return {heading:{text:'Votre titre',level:'h2',align:'left'},paragraph:{text:'Votre texte',align:'left'},button:{label:'Découvrir',url:'@{{lien_offre}}',variant:'filled',align:'left'},callout:{title:'À retenir',text:'Votre information importante',tone:'olive'},divider:{},spacer:{size:'medium'},details:{title:'Informations pratiques',text:'Date :\nLieu :'},signature:{text:'@{{nom_praticien}}',show_contact:true}}[type] || {}; },
                blockLabel(type) { return {heading:'Titre',paragraph:'Paragraphe',image:'Image',button:'Bouton',callout:'Encadré',divider:'Séparateur',spacer:'Espacement',details:'Informations pratiques',signature:'Signature'}[type] || type; },
                addBlock(type) { this.remember(); this.blocks.push({id:this.uid(),type,data:this.defaultData(type)}); this.queueSave(); },
                remove(index) { this.remember(); this.blocks.splice(index,1); this.queueSave(); },
                duplicate(index) { this.remember(); const block=this.clone(this.blocks[index]); block.id=this.uid(); this.blocks.splice(index+1,0,block); this.queueSave(); },
                move(index,delta) { const target=index+delta; if(target<0||target>=this.blocks.length)return; this.remember(); const [block]=this.blocks.splice(index,1); this.blocks.splice(target,0,block); this.queueSave(); },
                dropAt(index) { if(this.dragIndex===null||this.dragIndex===index)return; this.remember(); const [block]=this.blocks.splice(this.dragIndex,1); this.blocks.splice(index,0,block); this.dragIndex=null; this.queueSave(); },
                undo() { if(!this.history.length)return; this.future.push(this.clone(this.blocks)); this.blocks=this.history.pop(); this.lastSnapshot=JSON.stringify(this.blocks); this.queueSave(); },
                redo() { if(!this.future.length)return; this.history.push(this.clone(this.blocks)); this.blocks=this.future.pop(); this.lastSnapshot=JSON.stringify(this.blocks); this.queueSave(); },
                focusBlock(index,key) { this.focusTarget={kind:'block',index,key}; },
                insertVariable(key) { const token='@{{'+key+'}}'; if(this.focusTarget.kind==='block' && this.blocks[this.focusTarget.index]) { const data=this.blocks[this.focusTarget.index].data; data[this.focusTarget.key]=(data[this.focusTarget.key]||'')+(data[this.focusTarget.key]?' ':'')+token; } else { this[this.focusTarget.kind]=(this[this.focusTarget.kind]||'')+(this[this.focusTarget.kind]?' ':'')+token; } this.queueSave(); },
                applyTemplate() { const template=this.templates.find(item=>item.key===this.templateKey); if(!template||!confirm('Remplacer le contenu actuel par ce modèle ?'))return; this.remember(); this.subject=template.subject; this.preheader=template.preheader; this.blocks=this.clone(template.content.blocks).map(block=>({...block,id:this.uid()})); this.queueSave(); },
                clearEmail() { if(!confirm('Repartir d’un email vide ?'))return; this.remember(); this.blocks=[]; this.queueSave(); },
                asset(id) { return this.assets.find(item=>Number(item.id)===Number(id)); },
                assetUsed(id) { return this.blocks.some(block=>Number(block.data?.asset_id)===Number(id)); },
                async deleteAsset(item) { if(this.assetUsed(item.id)||!confirm('Supprimer définitivement cette image ?'))return; if(!(await this.save(true)))return; const response=await fetch(this.urls.assetBase+'/'+item.id,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':this.csrf}}); if(response.ok)this.assets=this.assets.filter(asset=>asset.id!==item.id); else alert('Cette image ne peut pas être supprimée pour le moment.'); },
                async uploadImage(event) { const file=event.target.files[0]; event.target.value=''; if(!file)return; this.uploading=true; const data=new FormData(); data.append('image',file); try { const response=await fetch(this.urls.upload,{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':this.csrf},body:data}); const payload=await response.json(); if(!response.ok)throw new Error(Object.values(payload.errors||{}).flat().join(' ')||'Image refusée.'); this.assets.push(payload); this.remember(); this.blocks.push({id:this.uid(),type:'image',data:{asset_id:payload.id,alt:payload.name.replace(/\.[^.]+$/,''),width:'full',align:'center'}}); this.queueSave(); } catch(error) { alert(error.message); } finally { this.uploading=false; } },
                payload() { return {name:this.name,subject:this.subject,preheader:this.preheader,content:{blocks:this.blocks},style:this.style,audience_type:this.audienceType,segment_id:this.segmentId||null,journey_ids:this.journeyIds.filter(Boolean).map(Number)}; },
                queueSave() { if(this.locked)return; const current=JSON.stringify(this.blocks); if(current!==this.lastSnapshot){this.history.push(JSON.parse(this.lastSnapshot));if(this.history.length>30)this.history.shift();this.future=[];this.lastSnapshot=current;} this.saveStatus='Modifications en attente'; clearTimeout(this.saveTimer); this.saveTimer=setTimeout(()=>this.save(),700); },
                async save(force=false) { if(this.locked)return true; clearTimeout(this.saveTimer); if(this.saving&&!force)return false; this.saving=true; this.saveStatus='Enregistrement…'; try { const response=await fetch(this.urls.save,{method:'PUT',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':this.csrf},body:JSON.stringify(this.payload())}); const data=await response.json(); if(!response.ok)throw new Error(Object.values(data.errors||{}).flat().join(' ')||'Enregistrement impossible.'); this.quality=data.quality; this.saveStatus='Enregistré à '+data.saved_at; await this.refreshPreview(); return true; } catch(error) { this.saveStatus='À corriger'; this.quality={errors:[error.message],warnings:[]}; return false; } finally { this.saving=false; } },
                async refreshPreview() { try { const response=await fetch(this.urls.preview,{method:'POST',headers:{'Accept':'text/html','Content-Type':'application/json','X-CSRF-TOKEN':this.csrf},body:JSON.stringify({subject:this.subject,preheader:this.preheader,content:{blocks:this.blocks},style:this.style})}); if(response.ok)this.previewHtml=await response.text(); } catch(error) {} },
                async refreshEstimate() { const hasAudience=this.audienceType==='segment'?this.segmentId:this.journeyIds.length; if(!hasAudience){this.estimate=null;return;} this.estimating=true; const data=new FormData(); data.append('audience_type',this.audienceType); if(this.segmentId)data.append('segment_id',this.segmentId); this.journeyIds.forEach(id=>data.append('journey_ids[]',id)); try { const response=await fetch(this.urls.estimate,{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':this.csrf},body:data}); this.estimate=response.ok?await response.json():null; } finally { this.estimating=false; } },
                async submitAfterSave(ref) { if(!this.locked && !(await this.save(true)))return; if(ref==='scheduleForm'&&!this.scheduledAt){alert('Choisissez une date et une heure.');return;} this.$refs[ref].submit(); }
            };
        }
    </script>
</x-app-layout>
