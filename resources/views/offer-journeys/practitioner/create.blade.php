<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('offer-journeys.index') }}" class="text-sm font-medium text-[#647a0b] hover:text-[#854f38]">Pages et campagnes</a>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900">Créer une page et son suivi</h1>
            <p class="mt-1 text-sm text-gray-500">Choisissez le résultat attendu. Olithea préparera le parcours adapté.</p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" enctype="multipart/form-data" action="{{ route('offer-journeys.store') }}" class="space-y-5"
                  x-data="{
                      step: {{ $errors->any() ? 4 : 1 }},
                      objective: @js(old('objective', 'appointment')),
                      templateKey: @js(old('template_key', '')),
                      sourceRef: @js(old('source_ref', '')),
                      name: @js(old('name', '')),
                      publicTitle: @js(old('public_title', '')),
                      summary: @js(old('summary', '')),
                      cta: @js(old('cta_label', 'Découvrir l’offre')),
                      objectives: @js($objectives),
                      templates: @js($templates->keyBy('key')),
                      applyTemplate(key) {
                          const template = this.templates[key];
                          if (!template || template.objective !== this.objective) return;
                          this.templateKey = key;
                          this.name = template.name;
                          this.publicTitle = template.name;
                          this.summary = template.summary;
                          this.cta = template.cta;
                      },
                      chooseObjective(value) {
                          if (this.objective !== value) this.templateKey = '';
                          this.objective = value;
                      },
                      canReview() { return this.name.trim() && this.publicTitle.trim() && this.cta.trim(); },
                      actionLabel() {
                          return {appointment:'une prise de rendez-vous',event:'une inscription à un événement',lead_magnet:'l’accès à une ressource',training:'l’accès à une formation',gift_voucher:'l’achat d’un bon cadeau',contact_request:'une demande de contact'}[this.objective] || 'l’action choisie';
                      }
                  }">
                @csrf

                @if($errors->any())
                    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"><p class="font-semibold">Vérifiez les informations indiquées.</p><ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <nav class="grid grid-cols-5 gap-1" aria-label="Progression">
                    @foreach(['Résultat','Offre','Exemple','Préparation','Vérification'] as $number => $label)
                        <button type="button" @click="if (step > {{ $number + 1 }}) step = {{ $number + 1 }}" class="min-w-0 border-t-2 pt-2 text-left" :class="step >= {{ $number + 1 }} ? 'border-[#647a0b] text-[#526509]' : 'border-gray-200 text-gray-400'">
                            <span class="block text-xs font-semibold">{{ $number + 1 }}</span><span class="hidden truncate text-xs sm:block">{{ $label }}</span>
                        </button>
                    @endforeach
                </nav>

                <section x-show="step === 1" x-cloak class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Quel résultat souhaitez-vous obtenir ?</h2>
                    <p class="mt-1 text-sm text-gray-600">Vous pourrez tout modifier avant la publication.</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach($objectives as $key => $objective)
                            <label class="cursor-pointer rounded-lg border p-4 transition" :class="objective === '{{ $key }}' ? 'border-[#647a0b] bg-[#f7f9ec]' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="objective" value="{{ $key }}" :checked="objective === '{{ $key }}'" @change="chooseObjective('{{ $key }}')" class="sr-only">
                                <span class="block font-semibold text-gray-900">{{ $objective['label'] }}</span><span class="mt-1 block text-sm leading-5 text-gray-600">{{ $objective['description'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-5 flex justify-end"><button type="button" @click="step = 2" class="rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white">Continuer</button></div>
                </section>

                <section x-show="step === 2" x-cloak class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Quelle offre souhaitez-vous promouvoir ?</h2>
                    <p class="mt-1 text-sm text-gray-600">Associez un élément déjà géré dans Olithea. Vous pouvez aussi continuer sans association.</p>
                    <label for="source_ref" class="mt-4 block text-sm font-medium text-gray-700">Élément associé</label>
                    <select id="source_ref" name="source_ref" x-model="sourceRef" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">
                        <option value="">Aucun pour le moment</option>
                        <optgroup label="Prestations">@foreach($products as $product)<option value="product:{{ $product->id }}">{{ $product->name }}</option>@endforeach</optgroup>
                        <optgroup label="Événements">@foreach($events as $event)<option value="event:{{ $event->id }}">{{ $event->name }}</option>@endforeach</optgroup>
                        <optgroup label="Formations">@foreach($trainings as $training)<option value="digital_training:{{ $training->id }}">{{ $training->title }}</option>@endforeach</optgroup>
                    </select>
                    <p x-show="objective === 'gift_voucher'" class="mt-3 rounded-md bg-[#f7f9ec] p-3 text-sm text-[#526509]">Olithea utilisera automatiquement votre page de bons cadeaux.</p>
                    <div class="mt-5 flex justify-between"><button type="button" @click="step = 1" class="text-sm font-semibold text-gray-600">Retour</button><button type="button" @click="step = 3" class="rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white">Continuer</button></div>
                </section>

                <section x-show="step === 3" x-cloak class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Choisissez un exemple adapté</h2>
                    <p class="mt-1 text-sm text-gray-600">Les textes sont des points de départ entièrement modifiables.</p>
                    <input type="hidden" name="template_key" x-model="templateKey">
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach($templates as $template)
                            <button type="button" x-show="objective === '{{ $template['objective'] }}'" @click="applyTemplate('{{ $template['key'] }}')" class="min-h-[112px] rounded-md border p-4 text-left transition" :class="templateKey === '{{ $template['key'] }}' ? 'border-[#647a0b] bg-[#f7f9ec]' : 'border-gray-200 hover:border-gray-300'">
                                <span class="block font-semibold text-gray-900">{{ $template['name'] }}</span><span class="mt-1 block text-sm leading-5 text-gray-600">{{ $template['description'] }}</span><span class="mt-2 block text-xs font-semibold text-[#647a0b]" x-text="templateKey === '{{ $template['key'] }}' ? 'Exemple sélectionné' : 'Utiliser cet exemple'"></span>
                            </button>
                        @endforeach
                        <button type="button" @click="templateKey = ''; name = ''; publicTitle = ''; summary = ''" class="min-h-[112px] rounded-md border border-dashed border-gray-300 p-4 text-left"><span class="block font-semibold text-gray-900">Partir d’une page vide</span><span class="mt-1 block text-sm text-gray-600">Rédigez vous-même les premiers contenus.</span></button>
                    </div>
                    <div class="mt-5 flex justify-between"><button type="button" @click="step = 2" class="text-sm font-semibold text-gray-600">Retour</button><button type="button" @click="step = 4" class="rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white">Continuer</button></div>
                </section>

                <section x-show="step === 4" x-cloak class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Olithea prépare votre parcours</h2>
                    <p class="mt-1 text-sm text-gray-600">Vérifiez les textes essentiels. Les pages, le formulaire, la confirmation et le suivi seront générés au prochain écran.</p>
                    <div class="mt-4 grid gap-4">
                        <div><label for="name" class="block text-sm font-medium text-gray-700">Nom interne</label><input id="name" name="name" x-model="name" required maxlength="160" class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div><label for="public_title" class="block text-sm font-medium text-gray-700">Titre affiché aux visiteurs</label><input id="public_title" name="public_title" x-model="publicTitle" required maxlength="180" class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div><label for="summary" class="block text-sm font-medium text-gray-700">Présentation en quelques mots</label><textarea id="summary" name="summary" x-model="summary" rows="4" maxlength="1200" class="mt-1 block w-full rounded-md border-gray-300"></textarea></div>
                        <div><label for="cta_label" class="block text-sm font-medium text-gray-700">Texte du bouton</label><input id="cta_label" name="cta_label" x-model="cta" required maxlength="80" class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <details x-show="objective === 'lead_magnet'" class="rounded-md border border-gray-200 p-4"><summary class="cursor-pointer text-sm font-semibold text-[#647a0b]">Personnaliser davantage la ressource</summary><div class="mt-4 space-y-4"><div><label for="resource_url" class="block text-sm font-medium text-gray-700">Lien HTTPS de la ressource</label><input id="resource_url" type="url" name="resource_url" value="{{ old('resource_url') }}" maxlength="2000" class="mt-1 block w-full rounded-md border-gray-300"></div><div><label for="resource_file" class="block text-sm font-medium text-gray-700">Ou fichier privé, 50 Mo maximum</label><input id="resource_file" type="file" name="resource_file" accept=".pdf,.zip,.mp3,.m4a,.wav,.mp4,.webm" class="mt-1 block w-full rounded-md border border-gray-300 bg-white p-2 text-sm"></div></div></details>
                    </div>
                    <div class="mt-5 flex justify-between"><button type="button" @click="step = 3" class="text-sm font-semibold text-gray-600">Retour</button><button type="button" @click="if (canReview()) step = 5" :disabled="!canReview()" class="rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50">Vérifier le parcours</button></div>
                </section>

                <section x-show="step === 5" x-cloak class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Voici ce qui sera préparé</h2>
                    <div class="mt-4 overflow-hidden rounded-md border border-gray-200">
                        <div class="border-b border-gray-100 p-4"><p class="text-xs font-semibold uppercase text-[#526509]">Page publique</p><p class="mt-1 font-semibold text-gray-900" x-text="publicTitle"></p><p class="mt-1 text-sm text-gray-600" x-text="summary || 'Vous pourrez compléter la présentation dans l’éditeur.'"></p></div>
                        <ol class="divide-y divide-gray-100 text-sm text-gray-700"><li class="p-4"><strong>1. Le visiteur découvre votre page</strong><span class="mt-1 block text-gray-500" x-text="'Le bouton proposera ' + actionLabel() + '.'"></span></li><li class="p-4"><strong>2. Olithea recueille uniquement les informations nécessaires</strong><span class="mt-1 block text-gray-500">Un formulaire est ajouté seulement lorsque l’objectif le nécessite.</span></li><li class="p-4"><strong>3. Une confirmation claire est affichée</strong><span class="mt-1 block text-gray-500">Les emails facultatifs restent soumis au consentement de la personne.</span></li><li class="p-4"><strong>4. Vous suivez les résultats dans Olithea</strong><span class="mt-1 block text-gray-500">Le brouillon ne sera pas publié automatiquement.</span></li></ol>
                    </div>
                    <div class="mt-4 rounded-md border border-[#dfe6c7] bg-[#f7f9ec] p-3 text-sm text-gray-700">Après la création, vous pourrez prévisualiser, tester le formulaire et modifier chaque étape avant de publier.</div>
                    <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between"><button type="button" @click="step = 4" class="text-sm font-semibold text-gray-600">Modifier les informations</button><button class="rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white">Créer le brouillon</button></div>
                </section>
            </form>
        </div>
    </div>
</x-app-layout>
