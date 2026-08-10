<x-app-layout>
    <x-slot name="header">
        @include('offer-journeys.practitioner._workspace-header')
    </x-slot>

    <div class="py-6"><div class="mx-auto max-w-4xl space-y-5 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h2 class="text-xl font-semibold text-gray-900">Messages</h2><p class="mt-1 text-sm text-gray-500">Préparez la confirmation et les suivis envoyés après une demande.</p></div>
            <div class="flex flex-wrap items-center gap-2">@if(config('offer_journeys.campaigns_enabled'))<a href="{{ route('offer-journeys.message-campaigns.index', ['journey_id' => $journey->id]) }}" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Envoyer un email à un groupe</a>@endif<span class="w-fit rounded-full px-2 py-1 text-xs font-medium {{ $automation->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">{{ $automation->status === 'active' ? 'Actif' : ($automation->status === 'paused' ? 'En pause' : 'Brouillon') }}</span></div>
        </div>
        @if(session('success'))<div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        @unless(config('offer_journeys.automation_enabled'))<div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Vous pouvez préparer la séquence, mais son activation reste coupée par le pilote global.</div>@endunless
        @unless(config('offer_journeys.email_enabled'))<div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">L'envoi d'emails est coupé par le pilote global. Vous pouvez tout préparer, mais aucun message ne partira.</div>@endunless
        @if(config('offer_journeys.pause_all_marketing_emails'))<div class="rounded-md border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700">Les relances facultatives sont suspendues pour le moment. Seuls les messages explicitement demandés par la personne peuvent être envoyés.</div>@endif

        <section class="rounded-lg border border-[#dfe6c7] bg-[#f7f9ec] p-5" aria-labelledby="message-status-title">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase text-[#526509]">État des messages</p>
                    <h2 id="message-status-title" class="mt-1 font-semibold text-gray-900">
                        @if($automation->status === 'active' && $version->status === 'draft')
                            Une version est active, ce brouillon attend votre validation
                        @elseif($automation->status === 'active')
                            Les messages sont actifs
                        @elseif($automation->status === 'paused')
                            Les messages sont en pause
                        @else
                            Les messages sont encore en brouillon
                        @endif
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        @if($version->status === 'draft')
                            Relisez les messages inclus ci-dessous, puis activez ce brouillon. Il ne sera envoyé qu’aux prochains contacts concernés.
                        @elseif($automation->status === 'active')
                            Les nouveaux contacts suivront cette version selon leur consentement et les délais indiqués.
                        @else
                            Aucun nouveau message automatique ne partira tant que la séquence restera en pause.
                        @endif
                    </p>
                </div>
                @if($version->status === 'draft')
                    <form method="POST" action="{{ route('offer-journeys.automation.activate', [$journey, $automation, $version]) }}" onsubmit="return confirm('Activer ces messages pour les prochains contacts concernés ?')">@csrf<button class="w-full rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#526509] sm:w-auto">Activer ce brouillon</button></form>
                @endif
            </div>
        </section>

        <details class="rounded-lg border border-gray-200 bg-white shadow-sm" @if(session('simulation')) open @endif>
            <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-gray-800">Tester un scénario sans envoyer d’email</summary>
            <div class="border-t border-gray-100 p-5">
                <p class="text-sm text-gray-500">Vérifiez les étapes qui seraient exécutées, sans créer de contact, de tâche ni envoyer de message.</p>
                <form method="POST" action="{{ route('offer-journeys.automation.simulate', [$journey, $automation, $version]) }}" class="mt-4 grid gap-3 sm:grid-cols-3">@csrf
                    <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="marketing_consent" value="1" class="rounded border-gray-300 text-[#647a0b]">Consentement marketing</label>
                    <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="converted" value="1" class="rounded border-gray-300 text-[#647a0b]">A déjà réservé ou acheté</label>
                    <label class="text-sm text-gray-700">Jours sans interaction<input type="number" name="inactive_days" value="0" min="0" max="3650" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></label>
                    <button class="sm:col-span-3 w-fit rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b] hover:bg-[#f7f9ec]">Voir le scénario</button>
                </form>
                @if(session('simulation'))<ol class="mt-4 divide-y divide-gray-100 rounded-md border border-gray-200">@foreach(session('simulation') as $step)<li class="px-4 py-3"><p class="text-sm font-medium text-gray-900">{{ $loop->iteration }}. {{ $step['name'] }}</p><p class="mt-1 text-xs text-gray-500">{{ $step['detail'] }}</p></li>@endforeach</ol>@endif
            </div>
        </details>

        <details class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-gray-800">Voir l’utilisation mensuelle des emails</summary>
            <section class="grid gap-3 border-t border-gray-100 p-5 sm:grid-cols-3" aria-label="Utilisation des messages"><div class="border-l-2 border-[#647a0b] px-3"><p class="text-xs font-medium text-gray-500">Envoyés ce mois</p><p class="mt-1 text-xl font-semibold text-gray-900">{{ $messageUsage['sent'] }}</p></div><div class="border-l-2 border-gray-200 px-3"><p class="text-xs font-medium text-gray-500">Restants</p><p class="mt-1 text-xl font-semibold text-gray-900">{{ $messageUsage['remaining'] }}</p></div><div class="border-l-2 border-gray-200 px-3"><p class="text-xs font-medium text-gray-500">Limite mensuelle</p><p class="mt-1 text-xl font-semibold text-gray-900">{{ $messageUsage['limit'] }}</p></div></section>
        </details>

        <section>
            <h2 class="font-semibold text-gray-900">Messages envoyés après une demande</h2>
            <p class="mt-1 text-sm text-gray-500">Vous pouvez préparer jusqu’à trois messages. Les conseils et relances ne sont envoyés qu’aux personnes qui les ont acceptés.</p>
            @if($messageToolsEnabled)
                <div class="mt-4 border-l-2 border-[#647a0b] bg-[#f7f9ec] px-4 py-3 text-sm text-gray-700">
                    <strong class="font-semibold text-gray-900">{{ $recipientEstimate }} {{ $recipientEstimate === 1 ? 'destinataire potentiellement concerné' : 'destinataires potentiellement concernés' }}</strong>
                    <p class="mt-1 text-xs text-gray-600">Estimation avant l’envoi: les personnes désinscrites, supprimées ou contactées pendant les {{ config('offer_journeys.contact_frequency_hours', 72) }} dernières heures sont exclues.</p>
                </div>
            @endif
            <form method="POST" action="{{ route('offer-journeys.automation.update', [$journey, $automation]) }}" class="mt-5 space-y-4">
                @csrf @method('PUT')
                @foreach($version->nodes->where('type', 'email')->take(3) as $node)
                    @php
                        $config = $node->config_json ?? [];
                        $emailBlocks = collect(data_get($config, 'email_content.blocks', []))->keyBy('type');
                        $emailHeading = data_get($emailBlocks->get('heading'), 'data.text', '');
                        $emailBody = data_get($emailBlocks->get('paragraph'), 'data.text', $config['body'] ?? '');
                        $emailImage = data_get($emailBlocks->get('image'), 'data', []);
                        $emailButton = data_get($emailBlocks->get('button'), 'data', []);
                        $emailDetails = data_get($emailBlocks->get('details'), 'data', []);
                        $emailSignature = data_get($emailBlocks->get('signature'), 'data.text', '{{nom_praticien}}');
                        $delayMinutes = (int) ($config['delay_minutes'] ?? 0);
                        $delayLabel = match (true) {
                            $delayMinutes === 0 => 'immédiatement',
                            $delayMinutes % 1440 === 0 => 'après '.intdiv($delayMinutes, 1440).' '.(intdiv($delayMinutes, 1440) === 1 ? 'jour' : 'jours'),
                            $delayMinutes % 60 === 0 => 'après '.intdiv($delayMinutes, 60).' '.(intdiv($delayMinutes, 60) === 1 ? 'heure' : 'heures'),
                            default => 'après '.$delayMinutes.' '.($delayMinutes === 1 ? 'minute' : 'minutes'),
                        };
                    @endphp
                    <fieldset id="message-{{ $node->id }}" class="message-tool rounded-lg border border-gray-200" x-data="{ open: window.location.hash === '#message-{{ $node->id }}' || @js($errors->any() && $loop->first) }" @if($messageToolsEnabled) data-preview-url="{{ route('offer-journeys.automation.messages.preview', [$journey, $automation, $node]) }}" data-test-url="{{ route('offer-journeys.automation.messages.test', [$journey, $automation, $node]) }}" @endif>
                        <legend class="sr-only">{{ $node->name }}</legend>
                        <input type="hidden" name="messages[{{ $node->node_key }}][editor_version]" value="blocks-v1">
                        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900">{{ $node->name }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ ($config['category'] ?? 'marketing') === 'transactional' ? 'Message demandé par la personne' : 'Relance facultative avec consentement' }} · {{ $delayLabel }}</p>
                                <p class="mt-1 truncate text-sm text-gray-700">{{ $config['subject'] ?? 'Objet à compléter' }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="messages[{{ $node->node_key }}][is_enabled]" value="1" @checked($config['is_enabled'] ?? false) class="rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]">Inclure dans le suivi</label>
                                <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="message-editor-{{ $node->id }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50" x-text="open ? 'Réduire' : 'Modifier'"></button>
                            </div>
                        </div>
                        <div id="message-editor-{{ $node->id }}" x-show="open" x-cloak class="border-t border-gray-200 p-4">
                        <div class="grid gap-4 sm:grid-cols-[140px_minmax(0,1fr)]">
                            <div><label class="block text-sm font-medium text-gray-700" for="delay-{{ $node->node_key }}">Délai en jours</label><input id="delay-{{ $node->node_key }}" type="number" min="0" max="60" name="messages[{{ $node->node_key }}][delay_days]" value="{{ old('messages.'.$node->node_key.'.delay_days', intdiv((int) ($config['delay_minutes'] ?? 0), 1440)) }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"></div>
                            <div><label class="block text-sm font-medium text-gray-700" for="subject-{{ $node->node_key }}">Objet</label><input id="subject-{{ $node->node_key }}" name="messages[{{ $node->node_key }}][subject]" value="{{ old('messages.'.$node->node_key.'.subject', $config['subject'] ?? '') }}" required maxlength="180" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"></div>
                            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700" for="preheader-{{ $node->node_key }}">Texte d’aperçu dans la boîte de réception</label><input id="preheader-{{ $node->node_key }}" name="messages[{{ $node->node_key }}][preheader]" value="{{ old('messages.'.$node->node_key.'.preheader', $config['preheader'] ?? '') }}" maxlength="180" placeholder="Une phrase courte qui complète l’objet" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"></div>
                            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700" for="heading-{{ $node->node_key }}">Titre dans l’email</label><input id="heading-{{ $node->node_key }}" name="messages[{{ $node->node_key }}][heading]" value="{{ old('messages.'.$node->node_key.'.heading', $emailHeading) }}" maxlength="180" placeholder="Ex. Votre guide est prêt" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"></div>
                            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700" for="body-{{ $node->node_key }}">Message</label><textarea id="body-{{ $node->node_key }}" name="messages[{{ $node->node_key }}][body]" rows="7" required maxlength="6000" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('messages.'.$node->node_key.'.body', $emailBody) }}</textarea><p class="mt-1 text-xs text-gray-500">Variables disponibles: @{{prenom}}, @{{offre}}, @{{nom_praticien}}, @{{lien_offre}}, @{{lien_ressource}}</p></div>
                        </div>
                        <details class="mt-4 border-t border-gray-200 pt-4" @if($emailBlocks->isNotEmpty()) open @endif>
                            <summary class="cursor-pointer text-sm font-semibold text-[#526509]">Mise en page et bouton</summary>
                            <p class="mt-1 text-xs text-gray-500">Ajoutez seulement les éléments utiles. L’email reste lisible sur téléphone et dispose automatiquement d’une version texte.</p>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div><label class="block text-sm font-medium text-gray-700">Image (adresse HTTPS)</label><input type="url" name="messages[{{ $node->node_key }}][image_url]" value="{{ old('messages.'.$node->node_key.'.image_url', $emailImage['url'] ?? '') }}" maxlength="2000" placeholder="https://..." class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                                <div><label class="block text-sm font-medium text-gray-700">Description de l’image</label><input name="messages[{{ $node->node_key }}][image_alt]" value="{{ old('messages.'.$node->node_key.'.image_alt', $emailImage['alt'] ?? '') }}" maxlength="180" placeholder="Ce que montre l’image" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                                <div><label class="block text-sm font-medium text-gray-700">Texte du bouton</label><input name="messages[{{ $node->node_key }}][button_label]" value="{{ old('messages.'.$node->node_key.'.button_label', $emailButton['label'] ?? '') }}" maxlength="80" placeholder="Ex. Réserver mon créneau" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                                <div><label class="block text-sm font-medium text-gray-700">Lien du bouton</label><input name="messages[{{ $node->node_key }}][button_url]" value="{{ old('messages.'.$node->node_key.'.button_url', $emailButton['url'] ?? '') }}" maxlength="2000" placeholder="@{{lien_offre}}" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                                <div><label class="block text-sm font-medium text-gray-700">Titre de l’encadré pratique</label><input name="messages[{{ $node->node_key }}][details_title]" value="{{ old('messages.'.$node->node_key.'.details_title', $emailDetails['title'] ?? '') }}" maxlength="120" placeholder="Informations pratiques" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                                <div><label class="block text-sm font-medium text-gray-700">Couleur principale</label><div class="mt-1 flex gap-2"><input type="color" name="messages[{{ $node->node_key }}][primary_color]" value="{{ old('messages.'.$node->node_key.'.primary_color', data_get($config, 'email_style.primary_color', '#647a0b')) }}" class="h-10 w-12 rounded border border-gray-300 bg-white p-1"><span class="self-center text-xs text-gray-500">Bouton et repères visuels</span></div></div>
                                <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Contenu de l’encadré pratique</label><textarea name="messages[{{ $node->node_key }}][details_text]" rows="3" maxlength="1500" class="mt-1 block w-full rounded-md border-gray-300 text-sm">{{ old('messages.'.$node->node_key.'.details_text', $emailDetails['text'] ?? '') }}</textarea></div>
                                <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Signature</label><textarea name="messages[{{ $node->node_key }}][signature]" rows="2" maxlength="500" class="mt-1 block w-full rounded-md border-gray-300 text-sm">{{ old('messages.'.$node->node_key.'.signature', $emailSignature) }}</textarea><p class="mt-1 text-xs text-gray-500">Les coordonnées professionnelles du praticien sont ajoutées automatiquement.</p></div>
                            </div>
                        </details>
                        @if($messageToolsEnabled)
                            <div class="mt-4 border-t border-gray-100 pt-4">
                                <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-end">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600" for="template-{{ $node->node_key }}">Partir d’un modèle</label>
                                        <select id="template-{{ $node->node_key }}" class="message-template mt-1 block w-full rounded-md border-gray-300 text-sm">
                                            <option value="">Choisir un modèle sobre</option>
                                            @foreach($messageTemplates as $template)
                                                <option value="{{ $template['key'] }}" data-subject="{{ $template['subject'] }}" data-body="{{ $template['body'] }}">{{ $template['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" class="message-preview rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b] hover:bg-[#f7f9ec]">Actualiser l’aperçu</button>
                                    <button type="button" class="message-test rounded-md bg-[#26351f] px-3 py-2 text-sm font-semibold text-white hover:bg-[#34472a]">M’envoyer un test</button>
                                </div>
                                <div class="message-feedback mt-3 hidden rounded-md px-3 py-2 text-sm" role="status"></div>
                                <div class="message-preview-panel mt-3 rounded-md border border-gray-200 bg-gray-50 p-4">
                                    <p class="text-xs font-semibold uppercase text-gray-500">Aperçu avec des données d’exemple</p>
                                    <p class="message-preview-subject mt-2 font-semibold text-gray-900">{{ data_get($messagePreviews, $node->id.'.subject') }}</p>
                                    <div class="message-preview-body mt-3 whitespace-pre-line text-sm leading-6 text-gray-700">{{ data_get($messagePreviews, $node->id.'.body') }}</div>
                                    <iframe title="Aperçu de l’email" class="message-preview-frame mt-3 hidden h-[520px] w-full border border-gray-200 bg-white"></iframe>
                                    <ul class="message-preview-warnings mt-3 space-y-1 text-xs text-amber-800">
                                        @foreach(data_get($messagePreviews, $node->id.'.warnings', []) as $warning)<li>{{ $warning }}</li>@endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                        </div>
                    </fieldset>
                @endforeach
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end"><button class="rounded-md border border-[#647a0b] px-4 py-2 text-sm font-semibold text-[#647a0b] hover:bg-[#f7f9ec]">Enregistrer le brouillon</button></div>
            </form>
        </section>

        @if($recentDeliveries->isNotEmpty())<section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm"><h2 class="font-semibold text-gray-900">Derniers messages</h2><div class="mt-4 divide-y divide-gray-100">@foreach($recentDeliveries as $delivery)<div class="flex items-start justify-between gap-4 py-3 text-sm"><div class="min-w-0"><p class="truncate font-medium text-gray-900">{{ $delivery->subject }}</p><p class="mt-1 text-xs text-gray-500">{{ $delivery->recipient_email }} · {{ $delivery->created_at->format('d/m/Y H:i') }}</p>@if($delivery->failure_reason)<p class="mt-1 text-xs text-red-700">{{ $delivery->failure_reason }}</p>@endif</div><span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ ['queued'=>'En attente','sending'=>'En cours','sent'=>'Envoyé','failed'=>'Échec','skipped'=>'Non envoyé'][$delivery->status] ?? $delivery->status }}</span></div>@endforeach</div></section>@endif

        <details class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <summary class="cursor-pointer px-5 py-4 font-semibold text-gray-900">Réglages avancés du suivi</summary>
            <div class="space-y-5 border-t border-gray-100 p-5">
        <section class="border border-gray-200 bg-white p-5">
            <h2 class="font-semibold text-gray-900">Règles d'exécution</h2>
            <form method="POST" action="{{ route('offer-journeys.automation.settings', [$journey, $automation]) }}" class="mt-4 grid gap-4 sm:grid-cols-2" x-data="{ reentry: @js($automation->reentry_mode) }">@csrf @method('PUT')
                <div><label for="reentry_mode" class="block text-sm font-medium text-gray-700">Si la même personne remplit à nouveau le formulaire</label><select id="reentry_mode" name="reentry_mode" x-model="reentry" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="once">Ne pas recommencer les messages</option><option value="after_delay">Recommencer après un délai</option></select></div>
                <div x-show="reentry === 'after_delay'"><label for="reentry_delay_days" class="block text-sm font-medium text-gray-700">Délai en jours</label><input id="reentry_delay_days" type="number" min="1" max="365" name="reentry_delay_days" value="{{ $automation->reentry_delay_days ?? 30 }}" :required="reentry === 'after_delay'" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                <div><label for="quiet_hours_start" class="block text-sm font-medium text-gray-700">Ne pas envoyer après</label><input id="quiet_hours_start" type="time" name="quiet_hours_start" required value="{{ $automation->quiet_hours_start ?: '20:00' }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                <div><label for="quiet_hours_end" class="block text-sm font-medium text-gray-700">Reprendre à partir de</label><input id="quiet_hours_end" type="time" name="quiet_hours_end" required value="{{ $automation->quiet_hours_end ?: '08:00' }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                <div class="sm:col-span-2"><button class="rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b]">Enregistrer les règles</button></div>
            </form>
        </section>

        <section class="border border-gray-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"><div><h2 class="font-semibold text-gray-900">Déroulement du suivi</h2><p class="mt-1 text-sm text-gray-500">Organisez les délais, vérifications et actions dans leur ordre d’exécution. Une étape ne peut pas revenir en arrière.</p></div>@if($version->status !== 'draft')<form method="POST" action="{{ route('offer-journeys.automation.draft', [$journey, $automation]) }}">@csrf<button class="rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b]">Créer une version brouillon</button></form>@endif</div>
            <ol class="mt-5 space-y-3">
                @foreach($version->nodes as $node)
                    @php($config = $node->config_json ?? [])
                    <li class="rounded-md border border-gray-200 p-4">
                        <div class="flex items-start gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#f0f4df] text-xs font-semibold text-[#526509]">{{ $loop->iteration }}</span><div class="min-w-0 flex-1"><p class="font-medium text-gray-900">{{ $node->name }}</p><p class="mt-1 text-xs uppercase text-gray-500">{{ ['email'=>'Message','wait'=>'Délai','condition'=>'Condition','action'=>'Action','end'=>'Fin'][$node->type] ?? $node->type }}</p></div></div>
                        @if($version->status === 'draft' && $node->type !== 'email')
                            <form method="POST" action="{{ route('offer-journeys.automation.nodes.update', [$journey, $automation, $version, $node]) }}" class="mt-4 grid gap-3 sm:grid-cols-2">@csrf @method('PUT')
                                <div class="sm:col-span-2"><label class="block text-xs font-medium text-gray-600">Nom de l'étape</label><input name="name" value="{{ $node->name }}" required maxlength="120" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                                @if($node->type === 'wait')<div><label class="block text-xs font-medium text-gray-600">Attendre (jours)</label><input type="number" min="0" max="365" name="delay_days" value="{{ intdiv((int)($config['delay_minutes'] ?? 1440), 1440) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>@endif
                                @if($node->type === 'condition')
                                    <div x-data="{ conditionType: @js($config['condition_type'] ?? 'marketing_consent') }" class="contents">
                                        <div><label class="block text-xs font-medium text-gray-600">Vérifier</label><select name="condition_type" x-model="conditionType" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="marketing_consent">Consentement marketing</option><option value="converted">Conversion confirmée</option><option value="has_tag">Étiquette présente</option><option value="inactive_days">Inactif depuis</option></select></div>
                                        <div x-show="conditionType === 'has_tag'"><label class="block text-xs font-medium text-gray-600">Étiquette</label><select name="condition_value" :disabled="conditionType !== 'has_tag'" class="mt-1 block w-full rounded-md border-gray-300 text-sm">@foreach($tags as $tag)<option value="{{ $tag->id }}" @selected((int)($config['value'] ?? 0)===$tag->id)>{{ $tag->name }}</option>@endforeach</select></div>
                                        <div x-show="conditionType === 'inactive_days'"><label class="block text-xs font-medium text-gray-600">Nombre de jours</label><input type="number" min="1" max="3650" name="condition_value" :disabled="conditionType !== 'inactive_days'" value="{{ $config['value'] ?? 30 }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                                    </div>
                                @endif
                                @if($node->type === 'action')
                                    <div x-data="{ actionType: @js($config['action_type'] ?? 'create_task') }" class="contents">
                                        <div><label class="block text-xs font-medium text-gray-600">Action</label><select name="action_type" x-model="actionType" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="create_task">Créer une tâche</option><option value="add_tag">Ajouter une étiquette</option><option value="set_status">Changer le statut</option></select></div>
                                        <div x-show="actionType === 'create_task'"><label class="block text-xs font-medium text-gray-600">Tâche à créer</label><input name="action_value" :disabled="actionType !== 'create_task'" value="{{ ($config['action_type'] ?? '') === 'create_task' ? ($config['value'] ?? '') : '' }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm" placeholder="Ex. proposer un créneau"></div>
                                        <div x-show="actionType === 'add_tag'"><label class="block text-xs font-medium text-gray-600">Étiquette</label><select name="action_value" :disabled="actionType !== 'add_tag'" class="mt-1 block w-full rounded-md border-gray-300 text-sm">@foreach($tags as $tag)<option value="{{ $tag->id }}" @selected(($config['action_type'] ?? '')==='add_tag' && (int)($config['value'] ?? 0)===$tag->id)>{{ $tag->name }}</option>@endforeach</select></div>
                                        <div x-show="actionType === 'set_status'"><label class="block text-xs font-medium text-gray-600">Nouveau statut</label><select name="action_value" :disabled="actionType !== 'set_status'" class="mt-1 block w-full rounded-md border-gray-300 text-sm">@foreach(['new'=>'Nouveau','qualifying'=>'À qualifier','contacted'=>'Échange en cours','converted'=>'Converti','not_now'=>'Pas maintenant'] as $key=>$label)<option value="{{ $key }}" @selected(($config['action_type'] ?? '')==='set_status' && ($config['value'] ?? '')===$key)>{{ $label }}</option>@endforeach</select></div>
                                    </div>
                                @endif
                                @if($node->type === 'condition')
                                    <div><label class="block text-xs font-medium text-gray-600">Si oui</label><select name="yes_node_key" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="">Terminer</option>@foreach($version->nodes->where('position_y','>',$node->position_y) as $target)<option value="{{ $target->node_key }}" @selected($node->yes_node_key===$target->node_key)>{{ $target->name }}</option>@endforeach</select></div>
                                    <div><label class="block text-xs font-medium text-gray-600">Si non</label><select name="no_node_key" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="">Terminer</option>@foreach($version->nodes->where('position_y','>',$node->position_y) as $target)<option value="{{ $target->node_key }}" @selected($node->no_node_key===$target->node_key)>{{ $target->name }}</option>@endforeach</select></div>
                                @elseif($node->type !== 'end')
                                    <div class="sm:col-span-2"><label class="block text-xs font-medium text-gray-600">Étape suivante</label><select name="next_node_key" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="">Terminer</option>@foreach($version->nodes->where('position_y','>',$node->position_y) as $target)<option value="{{ $target->node_key }}" @selected($node->next_node_key===$target->node_key)>{{ $target->name }}</option>@endforeach</select></div>
                                @endif
                                <div class="sm:col-span-2"><button class="rounded-md border border-[#647a0b] px-3 py-1.5 text-xs font-semibold text-[#647a0b]">Enregistrer l'étape</button></div>
                            </form>
                            <form method="POST" action="{{ route('offer-journeys.automation.nodes.destroy', [$journey, $automation, $version, $node]) }}" class="mt-2">@csrf @method('DELETE')<button class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700">Supprimer</button></form>
                        @endif
                    </li>
                @endforeach
            </ol>
            @if($version->status === 'draft')<form method="POST" action="{{ route('offer-journeys.automation.nodes.store', [$journey, $automation, $version]) }}" class="mt-4 flex flex-col gap-2 sm:flex-row">@csrf<label for="new-node-type" class="sr-only">Type d'étape</label><select id="new-node-type" name="type" class="rounded-md border-gray-300 text-sm"><option value="wait">Attendre</option><option value="condition">Vérifier une condition</option><option value="action">Effectuer une action</option><option value="end">Fin de la séquence</option></select><button class="rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b]">Ajouter une étape</button></form>@endif
        </section>
            </div>
        </details>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
            @if($automation->status === 'active')<form method="POST" action="{{ route('offer-journeys.automation.pause', [$journey, $automation]) }}">@csrf<button class="w-full rounded-md border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-50">Mettre en pause</button></form>@endif
        </div>
    </div></div>

    @if($messageToolsEnabled)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const token = document.querySelector('meta[name="csrf-token"]')?.content;

                document.querySelectorAll('.message-tool').forEach((tool) => {
                    const subject = tool.querySelector('input[id^="subject-"]');
                    const body = tool.querySelector('textarea[id^="body-"]');
                    const feedback = tool.querySelector('.message-feedback');

                    const showFeedback = (message, error = false) => {
                        feedback.textContent = message;
                        feedback.className = `message-feedback mt-3 rounded-md px-3 py-2 text-sm ${error ? 'border border-red-200 bg-red-50 text-red-800' : 'border border-green-200 bg-green-50 text-green-800'}`;
                    };

                    const field = (name) => tool.querySelector(`[name$="[${name}]"]`)?.value || '';
                    const post = async (url) => {
                        const payload = {};
                        ['subject','body','editor_version','preheader','heading','image_url','image_alt','button_label','button_url','details_title','details_text','signature','primary_color'].forEach((name) => payload[name] = field(name));
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token},
                            body: JSON.stringify(payload),
                        });
                        const result = await response.json();
                        if (!response.ok) throw result;

                        return result;
                    };

                    tool.querySelector('.message-template')?.addEventListener('change', (event) => {
                        const option = event.target.selectedOptions[0];
                        if (!option?.value) return;
                        subject.value = option.dataset.subject || '';
                        body.value = option.dataset.body || '';
                        showFeedback('Modèle appliqué au brouillon. Relisez-le avant de l’enregistrer.');
                    });

                    tool.querySelector('.message-preview')?.addEventListener('click', async () => {
                        try {
                            const result = await post(tool.dataset.previewUrl);
                            tool.querySelector('.message-preview-subject').textContent = result.subject;
                            tool.querySelector('.message-preview-body').textContent = result.body;
                            const frame = tool.querySelector('.message-preview-frame');
                            if (result.email_html) {
                                frame.srcdoc = result.email_html;
                                frame.classList.remove('hidden');
                                tool.querySelector('.message-preview-body').classList.add('hidden');
                            } else {
                                frame.classList.add('hidden');
                                tool.querySelector('.message-preview-body').classList.remove('hidden');
                            }
                            const warnings = tool.querySelector('.message-preview-warnings');
                            warnings.replaceChildren(...(result.warnings || []).map((warning) => {
                                const item = document.createElement('li');
                                item.textContent = warning;
                                return item;
                            }));
                            showFeedback(result.warnings?.length ? 'Aperçu actualisé. Des points sont à corriger avant l’envoi.' : 'Aperçu actualisé.');
                        } catch (error) {
                            showFeedback(error.message || 'Impossible de produire l’aperçu.', true);
                        }
                    });

                    tool.querySelector('.message-test')?.addEventListener('click', async () => {
                        try {
                            const result = await post(tool.dataset.testUrl);
                            showFeedback(result.message);
                        } catch (error) {
                            showFeedback(error.warnings?.join(' ') || error.message || 'Impossible d’envoyer le message test.', true);
                        }
                    });
                });
            });
        </script>
    @endif
</x-app-layout>
