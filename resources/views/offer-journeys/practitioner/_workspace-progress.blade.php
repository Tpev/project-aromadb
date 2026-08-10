<section class="border border-[#dfe6c7] bg-[#f7f9ec]" aria-labelledby="journey-progress-title">
    <div class="flex flex-col gap-4 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase text-[#526509]">{{ $workspace['public_version_is_live'] && $workspace['draft_has_blockers'] ? 'Brouillon à terminer' : 'Prochaine étape' }}</p>
            <h2 id="journey-progress-title" class="mt-1 text-lg font-semibold text-gray-900">{{ $workspace['next_action']['title'] }}</h2>
            <p class="mt-1 max-w-3xl text-sm leading-6 text-gray-600">{{ $workspace['next_action']['body'] }}</p>
        </div>
        <a href="{{ $workspace['next_action']['url'] }}" class="inline-flex shrink-0 items-center justify-center rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#526509]">
            {{ $workspace['next_action']['label'] }}
        </a>
    </div>
    <div class="border-t border-[#dfe6c7] bg-white px-5 py-4">
        <div class="mb-3 flex items-center justify-between gap-4">
            <p class="text-sm font-semibold text-gray-800">Préparation du parcours</p>
            <p class="text-sm font-semibold text-[#526509]">{{ $workspace['progress_percent'] }} %</p>
        </div>
        <div class="h-2 overflow-hidden rounded-full bg-gray-100" aria-hidden="true"><div class="h-full bg-[#647a0b]" style="width: {{ $workspace['progress_percent'] }}%"></div></div>
        @if($workspace['public_version_is_live'] && $workspace['draft_has_blockers'])
            <p class="mb-3 border-l-2 border-[#647a0b] bg-[#f7f9ec] px-3 py-2 text-xs text-gray-700">Votre version publique reste en ligne et inchangée. Les corrections concernent uniquement le prochain brouillon.</p>
        @endif
        <ol class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($workspace['progress'] as $item)
                <li>
                    <a href="{{ $item['url'] }}" @class([
                        'flex min-h-11 items-center gap-2 border-l-2 px-3 py-2 text-xs font-semibold',
                        'border-green-600 bg-green-50 text-green-800' => $item['status'] === 'ready',
                        'border-amber-500 bg-amber-50 text-amber-900' => $item['status'] === 'attention',
                        'border-gray-300 bg-gray-50 text-gray-500' => $item['status'] === 'disabled',
                    ])>
                        <span aria-hidden="true">{{ $item['status'] === 'ready' ? '✓' : ($item['status'] === 'disabled' ? '—' : '!') }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ol>
    </div>
</section>
