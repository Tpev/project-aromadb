@php
    $questions = $questionnaire->questions;
    $multipleChoiceCount = $questions->where('type', 'multiple_choice')->count();
@endphp

<x-mobile-layout :title="$questionnaire->title">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.questionnaires.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Questionnaires
            </a>

            <div class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                        <i class="fas fa-clipboard-list text-sm"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl font-semibold leading-tight text-gray-900">
                            {{ $questionnaire->title }}
                        </h1>
                        <p class="mt-1 line-clamp-3 text-sm leading-snug text-gray-600">
                            {{ $questionnaire->description ?: 'Questionnaire sans description' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Questions</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $questionnaire->questions_count }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Choix</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $multipleChoiceCount }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Statut</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                            {{ $questionnaire->questions_count > 0 ? 'Pret' : 'Vide' }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('mobile.questionnaires.edit', $questionnaire) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-edit mr-1.5 text-[11px]"></i>
                        Modifier
                    </a>
                    <a href="{{ route('questionnaires.show', $questionnaire) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-external-link-alt mr-1.5 text-[10px]"></i>
                        Vue web
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-gray-900">Questions</h2>
                <a href="{{ route('mobile.questionnaires.edit', $questionnaire) }}"
                   class="text-xs font-semibold text-[#647a0b]">
                    Gerer
                </a>
            </div>

            @if($questions->isEmpty())
                <div class="mt-3 rounded-lg border border-dashed border-[#d7ddc6] bg-[#fbfcf7] p-4 text-center">
                    <h3 class="text-sm font-semibold text-gray-900">Aucune question</h3>
                    <p class="mt-1 text-sm leading-snug text-gray-600">
                        Ajoutez au moins une question pour utiliser ce modele.
                    </p>
                </div>
            @else
                <div class="mt-3 space-y-2">
                    @foreach($questions as $question)
                        <article class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-semibold leading-snug text-gray-900">
                                        {{ $question->text }}
                                    </div>
                                    @if($question->options)
                                        <div class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">
                                            {{ $question->options }}
                                        </div>
                                    @endif
                                </div>
                                <span class="shrink-0 rounded-full border border-[#e4e8d5] bg-white px-2 py-0.5 text-[10px] font-medium text-gray-600">
                                    {{ $question->type === 'multiple_choice' ? 'Choix' : 'Texte' }}
                                </span>
                            </div>

                            <form method="POST"
                                  action="{{ route('mobile.questionnaires.questions.destroy', [$questionnaire, $question]) }}"
                                  class="mt-3"
                                  onsubmit="return confirm('Supprimer cette question ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex h-9 w-full items-center justify-center rounded-lg border border-red-100 bg-white text-xs font-semibold text-red-600">
                                    Supprimer la question
                                </button>
                            </form>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <form method="POST"
              action="{{ route('mobile.questionnaires.destroy', $questionnaire) }}"
              class="mt-4"
              onsubmit="return confirm('Supprimer ce questionnaire ?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-sm font-semibold text-red-600">
                <i class="fas fa-trash-alt mr-1.5 text-[11px]"></i>
                Supprimer le questionnaire
            </button>
        </form>
    </div>
</x-mobile-layout>
