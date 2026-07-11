<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('offer-journeys.index') }}" class="text-sm font-medium text-[#647a0b] hover:text-[#854f38]">Parcours d’offre</a>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900">Créer un parcours</h1>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" enctype="multipart/form-data" action="{{ route('offer-journeys.store') }}" class="space-y-6"
                  x-data="{
                      objective: '{{ old('objective', 'appointment') }}',
                      templateKey: '{{ old('template_key', '') }}',
                      templates: @js($templates->keyBy('key')),
                      applyTemplate(key) {
                          const template = this.templates[key];
                          if (!template || template.objective !== this.objective) return;
                          this.templateKey = key;
                          document.getElementById('name').value = template.name;
                          document.getElementById('public_title').value = template.name;
                          document.getElementById('summary').value = template.summary;
                          document.getElementById('cta_label').value = template.cta;
                      }
                  }">
                @csrf

                @if($errors->any())
                    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <p class="font-semibold">Vérifiez les informations indiquées.</p>
                        <ul class="mt-1 list-disc pl-5">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">1. Quel résultat souhaitez-vous ?</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach($objectives as $key => $objective)
                            <label class="cursor-pointer rounded-lg border p-4 transition"
                                   :class="objective === '{{ $key }}' ? 'border-[#647a0b] bg-[#f7f9ec]' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="objective" value="{{ $key }}" x-model="objective" class="sr-only">
                                <span class="block font-semibold text-gray-900">{{ $objective['label'] }}</span>
                                <span class="mt-1 block text-sm leading-5 text-gray-600">{{ $objective['description'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>

                @if($templates->isNotEmpty())
                    <section class="border-y border-gray-200 bg-white py-5">
                        <div class="px-5">
                            <h2 class="text-lg font-semibold text-gray-900">2. Partez d'un exemple métier</h2>
                            <p class="mt-1 text-sm text-gray-600">Le modèle prépare un brouillon entièrement modifiable. Il ne publie rien automatiquement.</p>
                            <input type="hidden" name="template_key" x-model="templateKey">
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach($templates as $template)
                                    <button type="button" x-show="objective === '{{ $template['objective'] }}'" x-cloak
                                            @click="applyTemplate('{{ $template['key'] }}')"
                                            class="min-h-[112px] border p-4 text-left transition"
                                            :class="templateKey === '{{ $template['key'] }}' ? 'border-[#647a0b] bg-[#f7f9ec]' : 'border-gray-200 hover:border-gray-300'">
                                        <span class="block font-semibold text-gray-900">{{ $template['name'] }}</span>
                                        <span class="mt-1 block text-sm leading-5 text-gray-600">{{ $template['description'] }}</span>
                                        <span class="mt-2 block text-xs font-semibold text-[#647a0b]">Utiliser ce modèle</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif

                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $templates->isNotEmpty() ? '3' : '2' }}. Choisissez ce que vous souhaitez proposer</h2>
                    <p class="mt-1 text-sm text-gray-600">Sélectionnez une prestation, un événement ou une formation déjà créé dans Olithea. Vous continuerez à le gérer depuis son écran habituel.</p>
                    <p x-show="objective === 'gift_voucher'" x-cloak class="mt-2 text-sm text-[#647a0b]">Le parcours ouvrira automatiquement votre page d’achat de bons cadeaux.</p>
                    <label for="source_ref" class="mt-4 block text-sm font-medium text-gray-700">Élément associé</label>
                    <select id="source_ref" name="source_ref" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">
                        <option value="">Aucune pour le moment</option>
                        <optgroup label="Prestations">
                            @foreach($products as $product)<option value="product:{{ $product->id }}">{{ $product->name }}</option>@endforeach
                        </optgroup>
                        <optgroup label="Événements">
                            @foreach($events as $event)<option value="event:{{ $event->id }}">{{ $event->name }}</option>@endforeach
                        </optgroup>
                        <optgroup label="Formations">
                            @foreach($trainings as $training)<option value="digital_training:{{ $training->id }}">{{ $training->title }}</option>@endforeach
                        </optgroup>
                    </select>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $templates->isNotEmpty() ? '4' : '3' }}. Préparez la première page</h2>
                    <div class="mt-4 grid gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nom du parcours, visible uniquement par vous</label>
                            <input id="name" name="name" value="{{ old('name') }}" required maxlength="160" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </div>
                        <div>
                            <label for="public_title" class="block text-sm font-medium text-gray-700">Titre affiché aux visiteurs</label>
                            <input id="public_title" name="public_title" value="{{ old('public_title') }}" required maxlength="180" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </div>
                        <div>
                            <label for="summary" class="block text-sm font-medium text-gray-700">Votre offre en quelques mots</label>
                            <textarea id="summary" name="summary" rows="4" maxlength="1200" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('summary') }}</textarea>
                        </div>
                        <div>
                            <label for="cta_label" class="block text-sm font-medium text-gray-700">Action proposée sur le bouton</label>
                            <input id="cta_label" name="cta_label" value="{{ old('cta_label', 'Découvrir l’offre') }}" required maxlength="80" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </div>
                        <div x-show="objective === 'lead_magnet'" x-cloak>
                            <label for="resource_url" class="block text-sm font-medium text-gray-700">Lien de la ressource</label>
                            <p class="text-xs text-gray-500">Lien HTTPS vers une ressource déjà hébergée, ou chargez un fichier privé ci-dessous.</p>
                            <input id="resource_url" type="url" name="resource_url" value="{{ old('resource_url') }}" maxlength="2000" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </div>
                        <div x-show="objective === 'lead_magnet'" x-cloak>
                            <label for="resource_file" class="block text-sm font-medium text-gray-700">Fichier privé</label>
                            <p class="text-xs text-gray-500">PDF, ZIP, audio ou vidéo. 50 Mo maximum. Le téléchargement utilisera un lien temporaire.</p>
                            <input id="resource_file" type="file" name="resource_file" accept=".pdf,.zip,.mp3,.m4a,.wav,.mp4,.webm" class="mt-1 block w-full rounded-md border border-gray-300 bg-white p-2 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </div>
                    </div>
                </section>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('offer-journeys.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Annuler</a>
                    <button class="inline-flex justify-center rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#526509]">Créer le brouillon</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
