@php
    $fieldValue = fn (string $field, mixed $default = null) => old($field, $questionnaire->{$field} ?? $default);
    $oldQuestions = old('questions');

    if (is_array($oldQuestions)) {
        $questionRows = collect($oldQuestions)->values()->map(fn ($row) => [
            'id' => $row['id'] ?? null,
            'text' => $row['text'] ?? '',
            'type' => $row['type'] ?? 'text',
            'options' => $row['options'] ?? '',
        ]);
    } elseif($questionnaire->exists) {
        $questionRows = $questionnaire->questions->map(fn ($question) => [
            'id' => $question->id,
            'text' => $question->text,
            'type' => $question->type,
            'options' => $question->options,
        ])->values();
    } else {
        $questionRows = collect([[
            'id' => null,
            'text' => '',
            'type' => 'text',
            'options' => '',
        ]]);
    }
@endphp

<x-mobile-layout :title="$title" :hide-nav="true">
    <form method="POST" action="{{ $action }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4">
            <a href="{{ $questionnaire->exists ? route('mobile.questionnaires.show', $questionnaire) : route('mobile.questionnaires.index') }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Questionnaires
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm leading-snug text-gray-600">
                Composez un modele simple avec questions texte ou choix multiple.
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
                <h2 class="text-sm font-semibold text-gray-900">Informations</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Titre</span>
                        <input type="text"
                               name="title"
                               value="{{ $fieldValue('title') }}"
                               required
                               placeholder="Bilan avant rendez-vous"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Description</span>
                        <textarea name="description"
                                  rows="3"
                                  placeholder="Ce formulaire aide a preparer la seance."
                                  class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ $fieldValue('description') }}</textarea>
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-gray-900">Questions</h2>
                    <button type="button"
                            class="inline-flex h-9 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700"
                            onclick="addMobileQuestion()">
                        Ajouter
                    </button>
                </div>

                <div id="mobileQuestionRows" class="mt-3 space-y-3">
                    @foreach($questionRows as $index => $row)
                        <article class="mobile-question-row rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                            @if(! empty($row['id']))
                                <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $row['id'] }}">
                            @endif

                            <div class="flex items-center justify-between gap-3">
                                <div class="text-xs font-semibold uppercase text-gray-500">
                                    Question
                                </div>
                                <button type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-100 bg-white text-red-500"
                                        aria-label="Supprimer cette question"
                                        onclick="removeMobileQuestion(this)">
                                    <i class="fas fa-times text-[11px]"></i>
                                </button>
                            </div>

                            <label class="mt-3 block">
                                <span class="text-sm font-medium text-gray-700">Texte</span>
                                <input type="text"
                                       name="questions[{{ $index }}][text]"
                                       value="{{ $row['text'] }}"
                                       required
                                       class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            </label>

                            <label class="mt-3 block">
                                <span class="text-sm font-medium text-gray-700">Type</span>
                                <select name="questions[{{ $index }}][type]"
                                        required
                                        data-question-type
                                        onchange="syncMobileQuestionOptions(this)"
                                        class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                                    <option value="text" @selected($row['type'] === 'text')>Texte</option>
                                    <option value="multiple_choice" @selected($row['type'] === 'multiple_choice')>Choix multiple</option>
                                </select>
                            </label>

                            <div class="mobile-question-options {{ $row['type'] === 'multiple_choice' ? '' : 'hidden' }}">
                                <label class="mt-3 block">
                                    <span class="text-sm font-medium text-gray-700">Options</span>
                                    <input type="text"
                                           name="questions[{{ $index }}][options]"
                                           value="{{ $row['options'] }}"
                                           placeholder="Oui, Non, A preciser"
                                           class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                                </label>
                                <p class="mt-1 text-xs leading-snug text-gray-500">
                                    Separez les options par des virgules.
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ $questionnaire->exists ? route('mobile.questionnaires.show', $questionnaire) : route('mobile.questionnaires.index') }}"
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
        let mobileQuestionIndex = {{ $questionRows->count() }};

        function mobileQuestionTemplate(index) {
            return `
                <article class="mobile-question-row rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-xs font-semibold uppercase text-gray-500">
                            Question
                        </div>
                        <button type="button"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-100 bg-white text-red-500"
                                aria-label="Supprimer cette question"
                                onclick="removeMobileQuestion(this)">
                            <i class="fas fa-times text-[11px]"></i>
                        </button>
                    </div>

                    <label class="mt-3 block">
                        <span class="text-sm font-medium text-gray-700">Texte</span>
                        <input type="text"
                               name="questions[${index}][text]"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="mt-3 block">
                        <span class="text-sm font-medium text-gray-700">Type</span>
                        <select name="questions[${index}][type]"
                                required
                                data-question-type
                                onchange="syncMobileQuestionOptions(this)"
                                class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                            <option value="text">Texte</option>
                            <option value="multiple_choice">Choix multiple</option>
                        </select>
                    </label>

                    <div class="mobile-question-options hidden">
                        <label class="mt-3 block">
                            <span class="text-sm font-medium text-gray-700">Options</span>
                            <input type="text"
                                   name="questions[${index}][options]"
                                   placeholder="Oui, Non, A preciser"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                        <p class="mt-1 text-xs leading-snug text-gray-500">
                            Separez les options par des virgules.
                        </p>
                    </div>
                </article>
            `;
        }

        function addMobileQuestion() {
            const container = document.getElementById('mobileQuestionRows');
            if (!container) return;

            container.insertAdjacentHTML('beforeend', mobileQuestionTemplate(mobileQuestionIndex));
            mobileQuestionIndex += 1;
        }

        function removeMobileQuestion(button) {
            const row = button.closest('.mobile-question-row');
            if (row) {
                row.remove();
            }
        }

        function syncMobileQuestionOptions(select) {
            const row = select.closest('.mobile-question-row');
            const options = row ? row.querySelector('.mobile-question-options') : null;
            if (!options) return;

            options.classList.toggle('hidden', select.value !== 'multiple_choice');
        }

        document.querySelectorAll('[data-question-type]').forEach((select) => {
            syncMobileQuestionOptions(select);
        });
    </script>
</x-mobile-layout>
