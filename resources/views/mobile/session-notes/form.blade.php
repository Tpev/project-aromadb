@php
    $fullName = trim(($clientProfile->first_name ?? '') . ' ' . ($clientProfile->last_name ?? '')) ?: 'Client sans nom';
    $backUrl = $sessionNote->exists
        ? route('mobile.session-notes.show', $sessionNote)
        : route('mobile.session-notes.index', $clientProfile);
    $noteValue = old('note', $sessionNote->exists ? trim(strip_tags($sessionNote->note ?? '')) : '');
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4">
            <a href="{{ $backUrl }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Notes de seance
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
            <p class="mt-1 break-words text-sm leading-snug text-gray-600">{{ $fullName }}</p>
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
            @if(! $sessionNote->exists && $templates->isNotEmpty())
                <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-900">Template</h2>
                    <p class="mt-1 text-xs leading-snug text-gray-500">
                        Optionnel. Le contenu sera colle dans la note.
                    </p>

                    <div class="mt-3 grid grid-cols-[1fr_auto] gap-2">
                        <select id="mobileSessionNoteTemplate"
                                class="h-11 min-w-0 rounded-lg border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">
                            <option value="" data-content="">Aucun template</option>
                            @foreach($templates as $template)
                                @php
                                    $templateContent = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($template->content ?? ''))));
                                @endphp
                                <option value="{{ $template->id }}" data-content="{{ $templateContent }}">
                                    {{ $template->title }}
                                </option>
                            @endforeach
                        </select>

                        <button type="button"
                                class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700"
                                onclick="applyMobileSessionNoteTemplate()">
                            Appliquer
                        </button>
                    </div>

                    <input type="hidden"
                           name="session_note_template_id"
                           id="mobileSessionNoteTemplateId"
                           value="{{ old('session_note_template_id') }}">
                </section>
            @endif

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <label class="block">
                    <span class="text-sm font-semibold text-gray-900">Note</span>
                    <textarea name="note"
                              id="mobileSessionNoteTextarea"
                              required
                              rows="12"
                              placeholder="Resume de la seance, observations, prochaines actions..."
                              class="mt-3 w-full rounded-lg border-gray-300 text-base leading-relaxed focus:border-[#647a0b] focus:ring-[#647a0b]">{{ $noteValue }}</textarea>
                </label>

                <p class="mt-2 text-xs leading-snug text-gray-500">
                    Les notes restent liees a ce client et a votre compte praticien.
                </p>
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ $backUrl }}"
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

    <script>
        function applyMobileSessionNoteTemplate() {
            const select = document.getElementById('mobileSessionNoteTemplate');
            const textarea = document.getElementById('mobileSessionNoteTextarea');
            const hidden = document.getElementById('mobileSessionNoteTemplateId');
            if (!select || !textarea || !hidden) return;

            const selected = select.options[select.selectedIndex];
            hidden.value = select.value || '';

            if (!selected || !select.value) return;

            const content = selected.dataset.content || '';
            const current = textarea.value.trim();
            if (current && !window.confirm('Remplacer le contenu actuel par ce template ?')) {
                return;
            }

            textarea.value = content;
            textarea.focus();
        }
    </script>
</x-mobile-layout>
