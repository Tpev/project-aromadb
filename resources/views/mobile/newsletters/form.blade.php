@php
    $fieldValue = fn (string $field, mixed $default = null) => old($field, $newsletter->{$field} ?? $default);
    $mobileValue = fn (string $field, mixed $default = null) => old($field, $mobileFields[$field] ?? $default);
    $selectedAudience = old('audience_id', $newsletter->audience_id);
    $includeDivider = filter_var(old('include_divider', $mobileFields['include_divider'] ?? false), FILTER_VALIDATE_BOOLEAN);
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4">
            <a href="{{ $newsletter->exists ? route('mobile.newsletters.show', $newsletter) : route('mobile.newsletters.index') }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Newsletters
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm leading-snug text-gray-600">
                Version mobile simplifiee pour ecrire vite: message, visuel et bouton d action.
            </p>
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <div class="font-semibold">A corriger</div>
                <ul class="mt-1 list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Parametres</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Titre interne</span>
                        <input type="text"
                               name="title"
                               value="{{ $fieldValue('title') }}"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Objet email</span>
                        <input type="text"
                               name="subject"
                               value="{{ $fieldValue('subject') }}"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Pre-header</span>
                        <input type="text"
                               name="preheader"
                               value="{{ $fieldValue('preheader') }}"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <div class="grid grid-cols-[1fr_76px] gap-3">
                        <label class="block min-w-0">
                            <span class="text-sm font-medium text-gray-700">Expediteur</span>
                            <input type="text"
                                   name="from_name"
                                   value="{{ $fieldValue('from_name', auth()->user()->name) }}"
                                   required
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Fond</span>
                            <input type="color"
                                   name="background_color"
                                   value="{{ $fieldValue('background_color', '#ffffff') }}"
                                   class="mt-1 h-11 w-full rounded-lg border border-gray-300 p-1 focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>

                    <input type="hidden" name="from_email" value="contact@aromamade.com">
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Destinataires</h2>

                <label class="mt-3 block">
                    <span class="text-sm font-medium text-gray-700">Audience</span>
                    <select name="audience_id"
                            class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        <option value="">Tous mes clients avec email</option>
                        @foreach($audiences as $audience)
                            <option value="{{ $audience->id }}" @selected((string) $selectedAudience === (string) $audience->id)>
                                {{ $audience->name }} ({{ $audience->clients_count }})
                            </option>
                        @endforeach
                    </select>
                </label>

                <a href="{{ route('mobile.audiences.index') }}"
                   class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                    Gerer les audiences
                </a>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Message</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Titre visible</span>
                        <input type="text"
                               name="heading"
                               value="{{ $mobileValue('heading') }}"
                               placeholder="Titre de votre newsletter"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Texte</span>
                        <textarea name="body_text"
                                  rows="8"
                                  required
                                  class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]"
                                  placeholder="Bonjour {{ '{' }}{{ ' client.first_name ' }}{{ '}' }},">{{ $mobileValue('body_text') }}</textarea>
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Visuel</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">URL image</span>
                        <input type="url"
                               name="image_url"
                               value="{{ $mobileValue('image_url') }}"
                               inputmode="url"
                               placeholder="https://..."
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Texte alternatif</span>
                        <input type="text"
                               name="image_alt"
                               value="{{ $mobileValue('image_alt') }}"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Bouton</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Texte du bouton</span>
                        <input type="text"
                               name="button_label"
                               value="{{ $mobileValue('button_label') }}"
                               placeholder="En savoir plus"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Lien</span>
                        <input type="url"
                               name="button_url"
                               value="{{ $mobileValue('button_url') }}"
                               inputmode="url"
                               placeholder="https://..."
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="flex items-start justify-between gap-4 rounded-lg bg-[#f7f8f1] px-3 py-3">
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">Separateur avant le bouton</span>
                            <span class="mt-0.5 block text-xs leading-snug text-gray-500">Ajoute une ligne fine entre le texte et l appel a l action.</span>
                        </span>
                        <span>
                            <input type="hidden" name="include_divider" value="0">
                            <input type="checkbox"
                                   name="include_divider"
                                   value="1"
                                   class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ $includeDivider ? 'checked' : '' }}>
                        </span>
                    </label>
                </div>
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ $newsletter->exists ? route('mobile.newsletters.show', $newsletter) : route('mobile.newsletters.index') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-sm font-semibold text-gray-700">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] text-sm font-semibold text-white">
                    {{ $submitLabel }}
                </button>
            </div>
        </div>
    </form>
</x-mobile-layout>
