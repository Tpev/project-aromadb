<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><a href="{{ route('offer-journeys.show', $journey) }}" class="text-sm font-medium text-[#647a0b] hover:text-[#854f38]">{{ $journey->name }}</a><h1 class="mt-1 text-2xl font-semibold text-gray-900">{{ $page->name }}</h1></div>
            <div class="flex items-center gap-2"><a href="{{ route('offer-journeys.preview', $journey) }}" target="_blank" rel="noopener" class="rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b]">Prévisualiser</a><span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">Brouillon · {{ config('offer_journeys.page_type_labels.'.$page->type, $page->type) }}</span></div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if(session('success'))<div class="mb-5 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

            <form method="POST" enctype="multipart/form-data" action="{{ route('offer-journeys.pages.update', [$journey, $page]) }}" class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px]"
                  x-data="{
                      writingReview: null,
                      writingLoading: false,
                      async reviewWriting() {
                          this.writingLoading = true;
                          const response = await fetch('{{ route('offer-journeys.pages.writing-assistant', [$journey, $page]) }}', {
                              method: 'POST',
                              headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                              body: JSON.stringify({title: document.getElementById('title').value, summary: document.getElementById('summary').value, cta_label: document.getElementById('cta_label').value})
                          });
                          this.writingReview = response.ok ? await response.json() : {warnings: ['Le contrôle est temporairement indisponible.'], title_suggestions: []};
                          this.writingLoading = false;
                      }
                  }">
                @csrf @method('PUT')
                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-gray-900">Contenu de la page</h2>
                    <div class="mt-4 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label for="name" class="block text-sm font-medium text-gray-700">Nom interne</label><input id="name" name="name" value="{{ old('name', $page->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"></div>
                            <div><label for="slug" class="block text-sm font-medium text-gray-700">Adresse de l'étape</label><input id="slug" name="slug" value="{{ old('slug', $page->slug) }}" required class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"></div>
                        </div>
                        <div><label for="title" class="block text-sm font-medium text-gray-700">Titre public</label><input id="title" name="title" value="{{ old('title', $content['title'] ?? '') }}" required maxlength="180" class="mt-1 block w-full rounded-md border-gray-300 text-lg font-semibold focus:border-[#647a0b] focus:ring-[#647a0b]"></div>
                        @if(config('offer_journeys.writing_assistant_enabled'))
                            <div class="border-y border-gray-200 bg-[#f7f8f3] px-4 py-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div><h3 class="text-sm font-semibold text-gray-900">Aide à la rédaction</h3><p class="text-xs text-gray-600">Analyse uniquement le brouillon affiché. Aucune modification n'est appliquée sans votre accord.</p></div>
                                    <button type="button" @click="reviewWriting" :disabled="writingLoading" class="border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b] disabled:opacity-50" x-text="writingLoading ? 'Analyse…' : 'Analyser le texte'"></button>
                                </div>
                                <template x-if="writingReview">
                                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                                        <div><p class="text-xs font-semibold uppercase text-gray-500">Titres proposés</p><div class="mt-2 space-y-2"><template x-for="suggestion in writingReview.title_suggestions" :key="suggestion"><button type="button" @click="document.getElementById('title').value = suggestion" class="block w-full border border-gray-200 bg-white px-3 py-2 text-left text-sm hover:border-[#647a0b]" x-text="suggestion"></button></template></div></div>
                                        <div><p class="text-xs font-semibold uppercase text-gray-500">Points à vérifier</p><ul class="mt-2 space-y-2 text-sm text-amber-900"><template x-for="warning in writingReview.warnings" :key="warning"><li class="flex gap-2"><span aria-hidden="true">●</span><span x-text="warning"></span></li></template><li x-show="writingReview.warnings.length === 0" class="text-green-700">Aucun point sensible détecté.</li></ul></div>
                                    </div>
                                </template>
                            </div>
                        @endif
                        <div><label for="summary" class="block text-sm font-medium text-gray-700">Présentation</label><textarea id="summary" name="summary" rows="4" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('summary', $content['summary'] ?? '') }}</textarea></div>
                        <div><label for="audience" class="block text-sm font-medium text-gray-700">À qui s'adresse cette offre ?</label><textarea id="audience" name="audience" rows="3" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('audience', $content['audience'] ?? '') }}</textarea></div>
                        <div><label for="outcomes" class="block text-sm font-medium text-gray-700">Ce que la personne va obtenir</label><p class="text-xs text-gray-500">Une ligne par élément.</p><textarea id="outcomes" name="outcomes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('outcomes', implode("\n", $content['outcomes'] ?? [])) }}</textarea></div>
                        <div><label for="steps" class="block text-sm font-medium text-gray-700">Déroulé</label><p class="text-xs text-gray-500">Une étape par ligne.</p><textarea id="steps" name="steps" rows="4" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('steps', implode("\n", $content['steps'] ?? [])) }}</textarea></div>
                        <div><label for="practical_details" class="block text-sm font-medium text-gray-700">Informations pratiques</label><textarea id="practical_details" name="practical_details" rows="3" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('practical_details', $content['practical_details'] ?? '') }}</textarea></div>
                        <div><label for="resource_url" class="block text-sm font-medium text-gray-700">Lien vers une ressource</label><p class="text-xs text-gray-500">Facultatif, sauf pour une ressource gratuite.</p><input id="resource_url" type="url" name="resource_url" value="{{ old('resource_url', $content['resource_url'] ?? '') }}" maxlength="2000" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"></div>
                        <div><label for="resource_file_upload" class="block text-sm font-medium text-gray-700">Fichier privé</label><input id="resource_file_upload" type="file" name="resource_file_upload" accept=".pdf,.zip,.mp3,.m4a,.wav,.mp4,.webm" class="mt-1 block w-full rounded-md border border-gray-300 bg-white p-2 text-sm">@if($content['resource_file']['original_name'] ?? null)<div class="mt-2 flex items-center justify-between gap-3 text-sm"><span class="truncate text-gray-600">{{ $content['resource_file']['original_name'] }}</span><label class="flex items-center gap-2 text-red-700"><input type="checkbox" name="remove_resource_file" value="1" class="rounded border-gray-300 text-red-600">Retirer du brouillon</label></div>@endif</div>
                        <div><label for="faq" class="block text-sm font-medium text-gray-700">Questions fréquentes</label><p class="text-xs text-gray-500">Une ligne par question, au format: Question | Réponse</p><textarea id="faq" name="faq" rows="5" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('faq', collect($content['faq'] ?? [])->map(fn($item) => ($item['question'] ?? '').' | '.($item['answer'] ?? ''))->implode("\n")) }}</textarea></div>
                        @if(config('offer_journeys.rich_editor_enabled'))
                            @php
                                $savedBlocks = collect($content['blocks'] ?? [])->keyBy('type');
                                $defaultEnabledBlocks = $savedBlocks->isNotEmpty() ? $savedBlocks->keys() : collect(['audience', 'outcomes', 'steps', 'practical', 'faq']);
                                $blockLabels = [
                                    'hero_image' => 'Image principale', 'audience' => 'Public concerné', 'outcomes' => 'Résultats proposés',
                                    'steps' => 'Déroulé', 'gallery' => 'Galerie', 'video' => 'Vidéo', 'testimonials' => 'Témoignages',
                                    'speaker' => 'Intervenant', 'price' => 'Tarif', 'practical' => 'Informations pratiques', 'faq' => 'Questions fréquentes',
                                ];
                                $orderedTypes = $savedBlocks->sortBy('position')->keys()->merge(collect(array_keys($blockLabels))->diff($savedBlocks->keys()))->values();
                            @endphp
                            <section class="border-y border-gray-200 bg-[#f7f8f3] px-4 py-5">
                                <h3 class="font-semibold text-gray-900">Organisation visuelle</h3>
                                <p class="mt-1 text-sm text-gray-600">Activez les sections utiles puis faites-les glisser pour choisir leur ordre.</p>
                                <ol id="offer-block-order" class="mt-4 space-y-2">
                                    @foreach($orderedTypes as $blockType)
                                        <li draggable="true" class="flex min-h-11 cursor-move items-center gap-3 border border-gray-200 bg-white px-3 py-2" data-block-row>
                                            <span class="select-none text-gray-400" aria-hidden="true">↕</span>
                                            <input type="hidden" name="block_order[]" value="{{ $blockType }}">
                                            <label class="flex flex-1 items-center gap-2 text-sm font-medium text-gray-800"><input type="checkbox" name="enabled_blocks[]" value="{{ $blockType }}" @checked(old('enabled_blocks') ? in_array($blockType, old('enabled_blocks', []), true) : $defaultEnabledBlocks->contains($blockType)) class="border-gray-300 text-[#647a0b] focus:ring-[#647a0b]">{{ $blockLabels[$blockType] }}</label>
                                        </li>
                                    @endforeach
                                </ol>

                                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                    <div><label for="hero_image_url" class="block text-sm font-medium text-gray-700">Image principale</label><input id="hero_image_url" type="url" name="hero_image_url" value="{{ old('hero_image_url', data_get($savedBlocks, 'hero_image.data.url')) }}" class="mt-1 block w-full border-gray-300 text-sm" placeholder="https://..."></div>
                                    <div><label for="hero_image_alt" class="block text-sm font-medium text-gray-700">Description de l'image</label><input id="hero_image_alt" name="hero_image_alt" value="{{ old('hero_image_alt', data_get($savedBlocks, 'hero_image.data.alt')) }}" class="mt-1 block w-full border-gray-300 text-sm" placeholder="Ce que montre l'image"></div>
                                    <div class="sm:col-span-2"><label for="gallery_items" class="block text-sm font-medium text-gray-700">Galerie</label><p class="text-xs text-gray-500">Une ligne par image : Adresse HTTPS | Description</p><textarea id="gallery_items" name="gallery_items" rows="3" class="mt-1 block w-full border-gray-300 text-sm">{{ old('gallery_items', collect(data_get($savedBlocks, 'gallery.data.items', []))->map(fn($item) => ($item['url'] ?? '').' | '.($item['alt'] ?? ''))->implode("\n")) }}</textarea></div>
                                    <div class="sm:col-span-2"><label for="video_url" class="block text-sm font-medium text-gray-700">Vidéo YouTube ou Vimeo</label><input id="video_url" type="url" name="video_url" value="{{ old('video_url', data_get($savedBlocks, 'video.data.url')) }}" class="mt-1 block w-full border-gray-300 text-sm" placeholder="https://..."></div>
                                    <div class="sm:col-span-2"><label for="testimonials" class="block text-sm font-medium text-gray-700">Témoignages</label><p class="text-xs text-gray-500">Un par ligne : Prénom ou initiales | Témoignage dont vous avez l'autorisation</p><textarea id="testimonials" name="testimonials" rows="4" class="mt-1 block w-full border-gray-300 text-sm">{{ old('testimonials', collect(data_get($savedBlocks, 'testimonials.data.items', []))->map(fn($item) => ($item['author'] ?? '').' | '.($item['quote'] ?? ''))->implode("\n")) }}</textarea></div>
                                    <div><label for="speaker_name" class="block text-sm font-medium text-gray-700">Nom de l'intervenant</label><input id="speaker_name" name="speaker_name" value="{{ old('speaker_name', data_get($savedBlocks, 'speaker.data.name')) }}" class="mt-1 block w-full border-gray-300 text-sm"></div>
                                    <div><label for="speaker_title" class="block text-sm font-medium text-gray-700">Rôle ou spécialité</label><input id="speaker_title" name="speaker_title" value="{{ old('speaker_title', data_get($savedBlocks, 'speaker.data.title')) }}" class="mt-1 block w-full border-gray-300 text-sm"></div>
                                    <div class="sm:col-span-2"><label for="speaker_bio" class="block text-sm font-medium text-gray-700">Présentation de l'intervenant</label><textarea id="speaker_bio" name="speaker_bio" rows="3" class="mt-1 block w-full border-gray-300 text-sm">{{ old('speaker_bio', data_get($savedBlocks, 'speaker.data.bio')) }}</textarea></div>
                                    <div><label for="speaker_image_url" class="block text-sm font-medium text-gray-700">Photo de l'intervenant</label><input id="speaker_image_url" type="url" name="speaker_image_url" value="{{ old('speaker_image_url', data_get($savedBlocks, 'speaker.data.image_url')) }}" class="mt-1 block w-full border-gray-300 text-sm"></div>
                                    <div><label for="price_label" class="block text-sm font-medium text-gray-700">Tarif affiché</label><input id="price_label" name="price_label" value="{{ old('price_label', data_get($savedBlocks, 'price.data.label')) }}" class="mt-1 block w-full border-gray-300 text-sm" placeholder="Ex. 45 € par personne"></div>
                                </div>
                            </section>
                        @endif
                        <div><label for="cta_label" class="block text-sm font-medium text-gray-700">Bouton principal</label><input id="cta_label" name="cta_label" value="{{ old('cta_label', $content['cta_label'] ?? 'Continuer') }}" required maxlength="80" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"></div>
                    </div>
                </section>

                <aside class="space-y-5">
                    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <h2 class="font-semibold text-gray-900">Référencement</h2>
                        <div class="mt-4 space-y-4">
                            <div><label for="seo_title" class="block text-sm font-medium text-gray-700">Titre SEO</label><input id="seo_title" name="seo_title" value="{{ old('seo_title', $page->seo_title) }}" maxlength="160" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"></div>
                            <div><label for="seo_description" class="block text-sm font-medium text-gray-700">Description SEO</label><textarea id="seo_description" name="seo_description" rows="3" maxlength="300" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('seo_description', $page->seo_description) }}</textarea></div>
                            <label class="flex items-start gap-3"><input type="checkbox" name="is_indexable" value="1" @checked(old('is_indexable', $page->is_indexable)) class="mt-1 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"><span class="text-sm text-gray-700">Autoriser l'indexation de cette page après publication</span></label>
                            @if(config('offer_journeys.rich_editor_enabled'))<div><label for="theme_style" class="block text-sm font-medium text-gray-700">Style de la page</label><select id="theme_style" name="theme_style" class="mt-1 block w-full border-gray-300 text-sm"><option value="olive" @selected(old('theme_style', data_get($page->theme_json, 'style', 'olive'))==='olive')>Olive</option><option value="forest" @selected(old('theme_style', data_get($page->theme_json, 'style'))==='forest')>Forêt</option><option value="clay" @selected(old('theme_style', data_get($page->theme_json, 'style'))==='clay')>Terre cuite</option><option value="neutral" @selected(old('theme_style', data_get($page->theme_json, 'style'))==='neutral')>Neutre</option></select></div>@endif
                        </div>
                    </section>
                    @if($form)
                        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 class="font-semibold text-gray-900">Formulaire</h2>
                            @php
                                $selectedFormFields = $form->fields->pluck('name');
                            @endphp
                            <div class="mt-4 space-y-4">
                                <fieldset><legend class="text-sm font-medium text-gray-700">Informations demandées</legend><div class="mt-2 grid gap-2 sm:grid-cols-2">@foreach(['first_name'=>'Prénom','last_name'=>'Nom','email'=>'Adresse email','phone'=>'Téléphone','contact_preference'=>'Préférence de contact','city'=>'Ville','postal_code'=>'Code postal'] as $fieldName=>$fieldLabel)<label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="form_fields[]" value="{{ $fieldName }}" @checked($fieldName === 'email' || $selectedFormFields->contains($fieldName)) @disabled($fieldName === 'email') class="rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]">{{ $fieldLabel }}</label>@endforeach</div></fieldset>
                                <div><label for="form_submit_label" class="block text-sm font-medium text-gray-700">Bouton du formulaire</label><input id="form_submit_label" name="form_submit_label" value="{{ old('form_submit_label', $form->submit_label) }}" maxlength="80" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
                                <div><label for="form_privacy_text" class="block text-sm font-medium text-gray-700">Information de confidentialité</label><textarea id="form_privacy_text" name="form_privacy_text" rows="3" maxlength="1000" class="mt-1 block w-full rounded-md border-gray-300 text-sm">{{ old('form_privacy_text', $form->privacy_text) }}</textarea></div>
                                <div><label for="marketing_consent_mode" class="block text-sm font-medium text-gray-700">Suivis facultatifs</label><select id="marketing_consent_mode" name="marketing_consent_mode" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="optional" @selected($form->marketing_consent_mode==='optional')>Proposer une case séparée, non cochée</option><option value="disabled" @selected($form->marketing_consent_mode==='disabled')>Ne pas proposer de suivi marketing</option></select></div>
                            </div>
                            @if(config('offer_journeys.custom_forms_enabled'))
                                <div class="mt-5 border-t border-gray-200 pt-5">
                                    <h3 class="text-sm font-semibold text-gray-900">Questions personnalisées</h3>
                                    <p class="mt-1 text-xs text-gray-500">Trois questions maximum. Ne demandez aucune information médicale ou clinique.</p>
                                    <div class="mt-3 space-y-3">
                                        @for($customIndex = 0; $customIndex < 3; $customIndex++)
                                            @php
                                                $customField = $customFields->get($customIndex);
                                                $customType = match($customField?->type) { 'select' => 'single_choice', 'multiselect' => 'multiple_choice', default => 'short_text' };
                                                $customOptions = $customField?->options_json ?? [];
                                            @endphp
                                            <details class="border border-gray-200 bg-gray-50 p-3" @if($customField) open @endif>
                                                <summary class="cursor-pointer text-sm font-semibold text-gray-800">Question {{ $customIndex + 1 }}{{ $customField ? ' · '.$customField->label : '' }}</summary>
                                                <div class="mt-3 space-y-3">
                                                    <div><label class="block text-xs font-medium text-gray-700">Question</label><input name="custom_fields[{{ $customIndex }}][label]" value="{{ old("custom_fields.$customIndex.label", $customField?->label) }}" maxlength="120" class="mt-1 block w-full border-gray-300 text-sm" placeholder="Ex. Quel format préférez-vous ?"></div>
                                                    <div><label class="block text-xs font-medium text-gray-700">Type de réponse</label><select name="custom_fields[{{ $customIndex }}][type]" class="mt-1 block w-full border-gray-300 text-sm"><option value="short_text" @selected(old("custom_fields.$customIndex.type", $customType)==='short_text')>Texte court</option><option value="single_choice" @selected(old("custom_fields.$customIndex.type", $customType)==='single_choice')>Un seul choix</option><option value="multiple_choice" @selected(old("custom_fields.$customIndex.type", $customType)==='multiple_choice')>Plusieurs choix</option></select></div>
                                                    <div><label class="block text-xs font-medium text-gray-700">Choix proposés</label><textarea name="custom_fields[{{ $customIndex }}][options]" rows="3" class="mt-1 block w-full border-gray-300 text-sm" placeholder="Un choix par ligne">{{ old("custom_fields.$customIndex.options", implode("\n", $customOptions['options'] ?? [])) }}</textarea></div>
                                                    <div><label class="block text-xs font-medium text-gray-700">Pourquoi cette information est-elle demandée ?</label><input name="custom_fields[{{ $customIndex }}][purpose]" value="{{ old("custom_fields.$customIndex.purpose", $customField?->purpose) }}" maxlength="255" class="mt-1 block w-full border-gray-300 text-sm" placeholder="Ex. proposer le format adapté"></div>
                                                    <label class="flex items-center gap-2 text-xs text-gray-700"><input type="checkbox" name="custom_fields[{{ $customIndex }}][is_required]" value="1" @checked(old("custom_fields.$customIndex.is_required", $customField?->is_required)) class="border-gray-300 text-[#647a0b]">Réponse obligatoire</label>
                                                    <div class="grid gap-2 sm:grid-cols-2">
                                                        <div><label class="block text-xs font-medium text-gray-700">Afficher seulement si</label><select name="custom_fields[{{ $customIndex }}][condition_field]" class="mt-1 block w-full border-gray-300 text-xs"><option value="">Toujours afficher</option><option value="contact_preference" @selected(old("custom_fields.$customIndex.condition_field", $customOptions['show_if']['field'] ?? '')==='contact_preference')>Préférence de contact</option><option value="city" @selected(old("custom_fields.$customIndex.condition_field", $customOptions['show_if']['field'] ?? '')==='city')>Ville</option></select></div>
                                                        <div><label class="block text-xs font-medium text-gray-700">Valeur attendue</label><input name="custom_fields[{{ $customIndex }}][condition_value]" value="{{ old("custom_fields.$customIndex.condition_value", $customOptions['show_if']['value'] ?? '') }}" maxlength="120" class="mt-1 block w-full border-gray-300 text-xs" placeholder="Ex. phone"></div>
                                                    </div>
                                                </div>
                                            </details>
                                        @endfor
                                    </div>
                                </div>
                            @endif
                        </section>
                    @endif
                    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <h2 class="font-semibold text-gray-900">Étape suivante</h2>
                        @php
                            $transitionAction = $primaryTransition?->to_page_id ? 'next_page' : ($primaryTransition?->external_action ? 'source' : 'none');
                            $transitionCondition = $primaryTransition?->condition_json['type'] ?? 'always';
                        @endphp
                        <div class="mt-4 space-y-4">
                            <div><label for="transition_action" class="block text-sm font-medium text-gray-700">Après le bouton principal</label><select id="transition_action" name="transition_action" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"><option value="none" @selected(old('transition_action', $transitionAction)==='none')>Terminer le parcours</option><option value="next_page" @selected(old('transition_action', $transitionAction)==='next_page')>Ouvrir une autre étape</option><option value="source" @selected(old('transition_action', $transitionAction)==='source')>Ouvrir la réservation ou le paiement lié</option></select></div>
                            <div><label for="transition_page_id" class="block text-sm font-medium text-gray-700">Étape cible</label><select id="transition_page_id" name="transition_page_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"><option value="">Choisir une étape</option>@foreach($journey->pages->where('id', '!=', $page->id) as $targetPage)<option value="{{ $targetPage->id }}" @selected((int) old('transition_page_id', $primaryTransition?->to_page_id)===$targetPage->id)>{{ $targetPage->name }}</option>@endforeach</select></div>
                            <div><label for="transition_condition" class="block text-sm font-medium text-gray-700">Condition</label><select id="transition_condition" name="transition_condition" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"><option value="always" @selected(old('transition_condition', $transitionCondition)==='always')>Dans tous les cas</option><option value="marketing_consent" @selected(old('transition_condition', $transitionCondition)==='marketing_consent')>Si la personne accepte les suivis</option></select></div>
                            <div><label for="fallback_page_id" class="block text-sm font-medium text-gray-700">Sinon, ouvrir</label><select id="fallback_page_id" name="fallback_page_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"><option value="">Aucune étape de secours</option>@foreach($journey->pages->where('id', '!=', $page->id) as $targetPage)<option value="{{ $targetPage->id }}" @selected((int) old('fallback_page_id', $fallbackTransition?->to_page_id)===$targetPage->id)>{{ $targetPage->name }}</option>@endforeach</select></div>
                        </div>
                    </section>
                    <div class="flex flex-col gap-3">
                        <button class="rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#526509]">Enregistrer le brouillon</button>
                        <a href="{{ route('offer-journeys.show', $journey) }}" class="rounded-md border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">Retour au parcours</a>
                    </div>
                </aside>
            </form>
            @if(config('offer_journeys.rich_editor_enabled'))
                <section class="mt-6 border-y border-gray-200 bg-white py-5" aria-labelledby="reusable-title">
                    <div class="px-5">
                        <h2 id="reusable-title" class="font-semibold text-gray-900">Sections réutilisables</h2>
                        <p class="mt-1 text-sm text-gray-600">Enregistrez une section du brouillon, puis réutilisez-la dans une autre page. L'application remplace uniquement une section du même type.</p>
                        <form method="POST" action="{{ route('offer-journeys.pages.reusable-sections.store', [$journey, $page]) }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_220px_auto]">
                            @csrf
                            <input name="name" required maxlength="120" class="border-gray-300 text-sm" placeholder="Nom dans votre bibliothèque">
                            <select name="type" class="border-gray-300 text-sm">@foreach($blockLabels as $type => $label)<option value="{{ $type }}">{{ $label }}</option>@endforeach</select>
                            <button class="bg-[#28331f] px-4 py-2 text-sm font-semibold text-white">Enregistrer</button>
                        </form>
                        @if($reusableSections->isNotEmpty())
                            <div class="mt-4 divide-y divide-gray-100 border border-gray-200">
                                @foreach($reusableSections as $section)
                                    <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div><p class="text-sm font-semibold text-gray-900">{{ $section->name }}</p><p class="text-xs text-gray-500">{{ $blockLabels[$section->type] ?? $section->type }}</p></div>
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('offer-journeys.pages.reusable-sections.apply', [$journey, $page, $section]) }}">@csrf<button class="border border-[#647a0b] px-3 py-2 text-xs font-semibold text-[#647a0b]">Appliquer au brouillon</button></form>
                                            <form method="POST" action="{{ route('offer-journeys.reusable-sections.destroy', $section) }}">@csrf @method('DELETE')<button class="border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-600">Supprimer</button></form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>

                <section class="mt-6" aria-labelledby="preview-title">
                    <div class="flex items-end justify-between gap-3"><div><h2 id="preview-title" class="font-semibold text-gray-900">Aperçu ordinateur et téléphone</h2><p class="mt-1 text-sm text-gray-600">Enregistrez le brouillon avant d'actualiser ces aperçus.</p></div><a href="{{ route('offer-journeys.preview', $journey) }}" target="_blank" rel="noopener" class="text-sm font-semibold text-[#647a0b]">Ouvrir en grand</a></div>
                    <div class="mt-4 grid gap-5 lg:grid-cols-[minmax(0,1fr)_390px]">
                        <div class="overflow-hidden border border-gray-300 bg-white"><div class="border-b border-gray-200 px-3 py-2 text-xs font-semibold text-gray-500">ORDINATEUR</div><iframe src="{{ route('offer-journeys.preview', $journey) }}" title="Aperçu ordinateur" class="h-[620px] w-full"></iframe></div>
                        <div class="mx-auto w-full max-w-[390px] overflow-hidden border border-gray-300 bg-white"><div class="border-b border-gray-200 px-3 py-2 text-xs font-semibold text-gray-500">TÉLÉPHONE</div><iframe src="{{ route('offer-journeys.preview', $journey) }}" title="Aperçu téléphone" class="h-[700px] w-full"></iframe></div>
                    </div>
                </section>
            @endif
        </div>
    </div>
    @if(config('offer_journeys.rich_editor_enabled'))
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const list = document.getElementById('offer-block-order');
                    if (!list) return;
                    let dragged = null;
                    list.querySelectorAll('[data-block-row]').forEach(function (row) {
                        row.addEventListener('dragstart', function () { dragged = row; row.classList.add('opacity-50'); });
                        row.addEventListener('dragend', function () { row.classList.remove('opacity-50'); dragged = null; });
                        row.addEventListener('dragover', function (event) {
                            event.preventDefault();
                            if (!dragged || dragged === row) return;
                            const rect = row.getBoundingClientRect();
                            list.insertBefore(dragged, event.clientY < rect.top + rect.height / 2 ? row : row.nextSibling);
                        });
                    });
                });
            </script>
        @endpush
    @endif
</x-app-layout>
