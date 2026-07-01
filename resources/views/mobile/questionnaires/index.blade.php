@php
    $total = $questionnaires->count();
    $questionCount = $questionnaires->sum('questions_count');
    $readyCount = $questionnaires->filter(fn ($questionnaire) => $questionnaire->questions_count > 0)->count();
@endphp

<x-mobile-layout title="Questionnaires">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-clipboard-list text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Questionnaires</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Modeles a envoyer aux clients avant ou apres rendez-vous.
                </p>
            </div>

            <a href="{{ route('mobile.menu') }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-gray-500 shadow-sm"
               aria-label="Retour au menu">
                <i class="fas fa-bars text-xs"></i>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Modeles</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $total }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Questions</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $questionCount }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Prets</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $readyCount }}</div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2">
            <a href="{{ route('mobile.questionnaires.create') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                Ajouter
            </a>
            <a href="{{ route('questionnaires.index') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                Vue web
            </a>
        </div>

        @if($questionnaires->isNotEmpty())
            <div class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <label class="flex h-10 items-center gap-2 rounded-lg bg-[#f7f8f1] px-3">
                    <i class="fas fa-search text-[11px] text-gray-400"></i>
                    <input type="search"
                           id="mobileQuestionnaireSearch"
                           placeholder="Rechercher un modele"
                           class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 focus:ring-0"
                           oninput="filterMobileQuestionnaires()">
                </label>
            </div>
        @endif

        @if($questionnaires->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-clipboard-list text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucun questionnaire</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Creez un modele avec quelques questions pour gagner du temps.
                </p>
            </div>
        @else
            <div id="mobileQuestionnaireList" class="space-y-2">
                @foreach($questionnaires as $questionnaire)
                    @php
                        $searchText = strtolower(trim($questionnaire->title . ' ' . ($questionnaire->description ?? '')));
                    @endphp

                    <a href="{{ route('mobile.questionnaires.show', $questionnaire) }}"
                       class="block rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99]"
                       data-questionnaire="{{ $searchText }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-semibold text-gray-900">
                                    {{ $questionnaire->title }}
                                </h2>
                                <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">
                                    {{ $questionnaire->description ?: 'Questionnaire sans description' }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $questionnaire->questions_count > 0 ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                                {{ $questionnaire->questions_count > 0 ? 'Pret' : 'Vide' }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Questions</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $questionnaire->questions_count }}</div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Mis a jour</div>
                                <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                    {{ $questionnaire->updated_at ? $questionnaire->updated_at->format('d/m') : '-' }}
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function filterMobileQuestionnaires() {
            const input = document.getElementById('mobileQuestionnaireSearch');
            const filter = input ? input.value.toLowerCase() : '';
            const items = document.querySelectorAll('#mobileQuestionnaireList > a');

            items.forEach((item) => {
                const text = item.getAttribute('data-questionnaire') || '';
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</x-mobile-layout>
