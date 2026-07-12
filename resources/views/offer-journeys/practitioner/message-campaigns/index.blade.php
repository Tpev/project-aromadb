<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('offer-journeys.index') }}" class="text-sm font-medium text-[#647a0b]">Pages et campagnes</a>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div><h1 class="mt-1 text-2xl font-semibold text-gray-900">Campagnes email</h1><p class="mt-1 text-sm text-gray-500">Préparez un message, contrôlez les destinataires puis choisissez quand l’envoyer.</p></div>
                @if(config('offer_journeys.email_editor_enabled'))<form method="POST" action="{{ route('offer-journeys.email-editor.start') }}">@csrf<button class="rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#526509]">Créer avec l’éditeur visuel</button></form>@endif
            </div>
        </div>
    </x-slot>

    @php
        $segmentSummaries = $segments->mapWithKeys(fn ($segment) => [$segment->id => $segment->audience_summary])->all();
        $editingJourneyIds = $editCampaign?->journeys->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [];
    @endphp
    <div class="py-6" x-data="{
        audienceType: @js(old('audience_type', $editCampaign?->audience_type ?? 'journeys')),
        segmentId: @js((string) old('segment_id', $editCampaign?->offer_journey_segment_id ?? '')),
        subject: @js(old('subject', $editCampaign?->subject ?? '')),
        body: @js(old('body', $editCampaign?->body ?? '')),
        summaries: @js($segmentSummaries),
        estimate: null,
        estimating: false,
        sample(text) { return (text || '').replaceAll('@{{prenom}}', 'Camille').replaceAll('@{{offre}}', 'votre offre').replaceAll('@{{nom_praticien}}', @js(auth()->user()->company_name ?: auth()->user()->name)).replaceAll('@{{lien_offre}}', 'https://olithea.fr/...') },
        get summary() { return this.estimate || this.summaries[this.segmentId] || null },
        async refreshEstimate() {
            if (!this.$refs.campaignForm) return;
            const data = new FormData(this.$refs.campaignForm);
            data.delete('_method'); data.delete('action'); data.delete('scheduled_at');
            this.estimating = true;
            try {
                const response = await fetch(@js(route('offer-journeys.message-campaigns.estimate')), { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) }, body: data });
                this.estimate = response.ok ? await response.json() : null;
            } finally { this.estimating = false; }
        }
    }" x-init="$nextTick(() => refreshEstimate())">
        <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_430px] lg:px-8">
            <div class="space-y-5">
                @if(session('success'))<div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-gray-900">Aperçu du message</h2>
                    <div class="mt-4 overflow-hidden rounded-md border border-gray-200">
                        <div class="border-b border-gray-200 bg-gray-50 px-4 py-3"><p class="text-xs font-medium text-gray-500">Objet</p><p class="mt-1 text-sm font-semibold text-gray-900" x-text="sample(subject) || 'Votre objet apparaîtra ici'"></p></div>
                        <div class="min-h-44 whitespace-pre-line px-4 py-5 text-sm leading-6 text-gray-700" x-text="sample(body) || 'Rédigez votre message pour le prévisualiser.'"></div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">L’email final comprend aussi l’identité de l’expéditeur et un lien de désinscription.</p>
                </section>

                <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4"><h2 class="font-semibold text-gray-900">Campagnes enregistrées</h2></div>
                    <div class="divide-y divide-gray-100">
                        @forelse($campaigns as $campaign)
                            <article class="px-5 py-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $campaign->name }}</p>
                                        <p class="mt-1 text-sm text-gray-600">{{ $campaign->subject }}</p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $campaign->audience_type === 'segment' ? 'Segment : '.($campaign->segment?->name ?? 'supprimé') : $campaign->journeys->pluck('name')->join(', ') }}
                                            @if($campaign->scheduled_at) · {{ $campaign->scheduled_at->format('d/m/Y H:i') }}@endif
                                        </p>
                                        @if($campaign->status === 'sent')
                                            <p class="mt-2 text-xs text-gray-600">{{ $campaign->sent_count }} envoyé(s) · {{ $campaign->skipped_count }} exclu(s) · {{ $campaign->eligible_count }} éligible(s)</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ data_get($campaign->summary_json, 'no_consent', 0) }} sans consentement · {{ data_get($campaign->summary_json, 'unsubscribed', 0) }} désinscrit(s) · {{ data_get($campaign->summary_json, 'suppressed', 0) }} supprimé(s)</p>
                                            <p class="mt-1 text-xs font-medium {{ ($campaign->failed_deliveries_count + $campaign->bounced_deliveries_count + $campaign->complained_deliveries_count) > 0 ? 'text-amber-800' : 'text-gray-500' }}">{{ $campaign->failed_deliveries_count }} échec(s) · {{ $campaign->bounced_deliveries_count }} rejet(s) · {{ $campaign->complained_deliveries_count }} plainte(s)</p>
                                        @endif
                                    </div>
                                    <span class="w-fit rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ ['draft'=>'Brouillon','scheduled'=>'Programmée','processing'=>'En cours','sent'=>'Envoyée','cancelled'=>'Annulée'][$campaign->status] ?? $campaign->status }}</span>
                                </div>
                                @if(in_array($campaign->status, ['draft', 'scheduled'], true))
                                    <div class="mt-3 flex flex-wrap items-end gap-3 border-t border-gray-100 pt-3">
                                        @if(config('offer_journeys.email_editor_enabled') && $campaign->content_json)
                                            <a href="{{ route('offer-journeys.email-editor.edit', $campaign) }}" class="text-xs font-semibold text-[#526509]">{{ $campaign->status === 'draft' ? 'Ouvrir l’éditeur visuel' : 'Voir l’email programmé' }}</a>
                                        @elseif(config('offer_journeys.email_editor_enabled') && $campaign->status === 'draft')
                                            <form method="POST" action="{{ route('offer-journeys.email-editor.convert', $campaign) }}" onsubmit="return confirm('Conserver le texte actuel et le convertir en blocs modifiables ?')">@csrf<button class="text-xs font-semibold text-[#526509]">Convertir dans l’éditeur visuel</button></form>
                                        @elseif($campaign->status === 'draft')
                                            <a href="{{ route('offer-journeys.message-campaigns.index', ['edit' => $campaign->id]) }}" class="text-xs font-semibold text-gray-700">Modifier le brouillon</a>
                                        @endif
                                        <form method="POST" action="{{ route('offer-journeys.message-campaigns.test', $campaign) }}">@csrf<button class="text-xs font-semibold text-[#647a0b]">Envoyer un test à mon adresse</button></form>
                                        @if($campaign->status === 'draft')
                                            <form method="POST" action="{{ route('offer-journeys.message-campaigns.schedule', $campaign) }}" class="flex items-end gap-2">@csrf<div><label class="block text-xs text-gray-500">Programmer</label><input type="datetime-local" name="scheduled_at" required class="mt-1 rounded-md border-gray-300 py-1.5 text-xs"></div><button class="rounded-md border border-[#647a0b] px-2.5 py-1.5 text-xs font-semibold text-[#647a0b]">Valider</button></form>
                                        @endif
                                        <form method="POST" action="{{ route('offer-journeys.message-campaigns.cancel', $campaign) }}">@csrf<button class="text-xs font-semibold text-red-700">Annuler</button></form>
                                    </div>
                                @endif
                            </article>
                        @empty
                            <div class="px-5 py-10 text-center"><p class="font-medium text-gray-900">Aucune campagne</p><p class="mt-1 text-sm text-gray-500">Préparez un premier brouillon sans rien envoyer.</p></div>
                        @endforelse
                    </div>
                    <div class="border-t border-gray-100 px-5 py-4">{{ $campaigns->links() }}</div>
                </section>
            </div>

            <aside class="order-first lg:order-last">
                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3"><div><h2 class="font-semibold text-gray-900">{{ $editCampaign ? 'Modifier le brouillon' : 'Préparer une campagne' }}</h2><p class="mt-1 text-xs text-gray-500">Aucun message ne part sans action explicite.</p></div>@if($editCampaign)<a href="{{ route('offer-journeys.message-campaigns.index') }}" class="text-xs font-semibold text-gray-600">Fermer</a>@endif</div>
                    <form x-ref="campaignForm" @change.debounce.250ms="refreshEstimate()" method="POST" action="{{ $editCampaign ? route('offer-journeys.message-campaigns.update', $editCampaign) : route('offer-journeys.message-campaigns.store') }}" class="mt-4 space-y-4">
                        @csrf
                        @if($editCampaign) @method('PUT') @endif
                        <div><label class="block text-sm font-medium text-gray-700" for="campaign-name">Nom interne</label><input id="campaign-name" name="name" value="{{ old('name', $editCampaign?->name) }}" required maxlength="120" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700" for="campaign-subject">Objet</label><input id="campaign-subject" name="subject" x-model="subject" required maxlength="180" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700" for="campaign-body">Message</label><textarea id="campaign-body" name="body" x-model="body" rows="7" required maxlength="6000" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></textarea><p class="mt-1 text-xs text-gray-500">Variables : @{{prenom}}, @{{offre}}, @{{nom_praticien}}, @{{lien_offre}}</p></div>

                        <fieldset><legend class="text-sm font-medium text-gray-700">À qui envoyer ?</legend><div class="mt-2 grid grid-cols-2 gap-2"><label class="rounded-md border border-gray-200 p-2 text-sm"><input type="radio" name="audience_type" value="journeys" x-model="audienceType" class="text-[#647a0b]"> Contacts d’une page</label>@if(config('offer_journeys.segment_campaigns_enabled'))<label class="rounded-md border border-gray-200 p-2 text-sm"><input type="radio" name="audience_type" value="segment" x-model="audienceType" class="text-[#647a0b]"> Segment enregistré</label>@endif</div></fieldset>

                        <fieldset x-show="audienceType === 'journeys'" x-cloak><legend class="text-sm font-medium text-gray-700">Pages concernées</legend><div class="mt-2 max-h-40 space-y-2 overflow-y-auto rounded-md border border-gray-200 p-3">@forelse($journeys as $journey)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="journey_ids[]" value="{{ $journey->id }}" :disabled="audienceType !== 'journeys'" @checked(in_array($journey->id, old('journey_ids', $editingJourneyIds))) class="rounded border-gray-300 text-[#647a0b]">{{ $journey->name }}</label>@empty<p class="text-sm text-gray-500">Publiez d’abord une page.</p>@endforelse</div></fieldset>

                        @if(config('offer_journeys.segment_campaigns_enabled'))
                            <div x-show="audienceType === 'segment'" x-cloak class="space-y-3">
                                <div><label for="campaign-segment" class="block text-sm font-medium text-gray-700">Segment</label><select id="campaign-segment" name="segment_id" x-model="segmentId" :disabled="audienceType !== 'segment'" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="">Choisir un segment</option>@foreach($segments as $segment)<option value="{{ $segment->id }}">{{ $segment->name }} · {{ $segment->audience_summary['eligible'] }} {{ $segment->audience_summary['eligible'] > 1 ? 'joignables' : 'joignable' }}</option>@endforeach</select></div>
                                <div><label for="campaign-destination" class="block text-sm font-medium text-gray-700">Page à promouvoir, facultatif</label><select id="campaign-destination" name="journey_ids[]" :disabled="audienceType !== 'segment'" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="">Aucune</option>@foreach($journeys as $journey)<option value="{{ $journey->id }}" @selected(in_array($journey->id, old('journey_ids', $editingJourneyIds)))>{{ $journey->name }}</option>@endforeach</select></div>
                            </div>
                        @endif

                        @unless($editCampaign)<div><label class="block text-sm font-medium text-gray-700" for="campaign-date">Date pour la programmation</label><input id="campaign-date" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>@endunless
                        <div class="rounded-md border border-[#dfe6c7] bg-[#f7f9ec] p-3 text-xs text-gray-700" aria-live="polite">
                            <p x-show="estimating">Vérification des destinataires…</p>
                            <div x-show="!estimating && summary"><p><strong x-text="summary?.matching || 0"></strong> <span x-text="summary?.matching > 1 ? 'personnes correspondent' : 'personne correspond'"></span> à l’audience.</p><p class="mt-1 text-[#526509]"><strong x-text="summary?.eligible || 0"></strong> <span x-text="summary?.eligible > 1 ? 'destinataires peuvent' : 'destinataire peut'"></span> actuellement recevoir ce message.</p><p class="mt-1 text-gray-500"><span x-text="summary?.no_consent || 0"></span> sans consentement · <span x-text="summary?.unsubscribed || 0"></span> désinscrit(s) · <span x-text="summary?.suppressed || 0"></span> supprimé(s), dont <span x-text="summary?.bounce_or_complaint || 0"></span> rejet(s) ou plainte(s) · <span x-text="summary?.frequency_limited || 0"></span> contacté(s) récemment · <span x-text="summary?.invalid_email || 0"></span> adresse(s) invalide(s)</p></div>
                            <p x-show="!estimating && !summary">Choisissez une audience pour obtenir l’estimation.</p>
                        </div>
                        <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900">Les étiquettes servent uniquement à sélectionner une audience. Les contacts sans consentement actif, désinscrits ou supprimés seront toujours exclus.</div>
                        @if($editCampaign)
                            <button class="w-full rounded-md bg-[#647a0b] px-3 py-2 text-sm font-semibold text-white">Enregistrer les modifications</button>
                        @else
                            <div class="grid gap-2 sm:grid-cols-2"><button name="action" value="draft" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700">Enregistrer le brouillon</button><button name="action" value="schedule" @click="if (summary && !confirm('Programmer cette campagne pour environ ' + summary.eligible + ' destinataire(s) actuellement joignable(s) ? Ils seront revérifiés au moment de l’envoi.')) $event.preventDefault()" class="rounded-md bg-[#647a0b] px-3 py-2 text-sm font-semibold text-white">Programmer</button></div>
                            <button name="action" value="send_now" class="w-full text-sm font-semibold text-[#647a0b]" @click="if (!summary || !confirm('Envoyer dès que possible à environ ' + summary.eligible + ' destinataire(s) actuellement joignable(s) ? Ils seront revérifiés avant envoi.')) $event.preventDefault()">Envoyer dès que possible</button>
                        @endif
                    </form>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
