@php
    $formatMoney = fn (?int $cents) => is_null($cents) ? 'Gratuit' : number_format($cents / 100, 2, ',', ' ') . ' EUR';
    $statusLabels = [
        'draft' => 'Brouillon',
        'published' => 'Publiee',
        'archived' => 'Archivee',
    ];
    $statusClasses = [
        'draft' => 'border-gray-200 bg-gray-50 text-gray-600',
        'published' => 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]',
        'archived' => 'border-amber-200 bg-amber-50 text-amber-700',
    ];
    $total = $trainings->count();
    $published = $trainings->where('status', 'published')->count();
    $enrollments = $trainings->sum('enrollments_count');
@endphp

<x-mobile-layout title="Formations digitales">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-graduation-cap text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Formations digitales</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Programmes, acces clients et ventes numeriques.
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
                <div class="text-[11px] font-medium leading-tight text-gray-500">Formations</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $total }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Publiees</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $published }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Inscrits</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $enrollments }}</div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2">
            <a href="{{ route('mobile.digital-trainings.create') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                Ajouter
            </a>
            <a href="{{ route('digital-trainings.index') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                Vue web
            </a>
        </div>

        @if($trainings->isNotEmpty())
            <div class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <label class="flex h-10 items-center gap-2 rounded-lg bg-[#f7f8f1] px-3">
                    <i class="fas fa-search text-[11px] text-gray-400"></i>
                    <input type="search"
                           id="mobileTrainingSearch"
                           placeholder="Rechercher une formation"
                           class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 focus:ring-0"
                           oninput="filterMobileTrainings()">
                </label>
            </div>
        @endif

        @if($trainings->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-graduation-cap text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucune formation</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Creez un programme digital a partager, vendre ou associer a une prestation.
                </p>
            </div>
        @else
            <div id="mobileTrainingList" class="space-y-2">
                @foreach($trainings as $training)
                    @php
                        $badgeClass = $statusClasses[$training->status] ?? $statusClasses['draft'];
                        $statusLabel = $statusLabels[$training->status] ?? 'Statut';
                        $price = $training->is_free ? 'Gratuit' : $formatMoney($training->price_cents);
                        $tags = collect($training->tags ?? [])->take(3);
                        $searchText = trim($training->title . ' ' . ($training->description ?? '') . ' ' . $statusLabel . ' ' . $price . ' ' . $tags->implode(' '));
                    @endphp

                    <a href="{{ route('mobile.digital-trainings.show', $training) }}"
                       class="block rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99]"
                       data-training="{{ Str::lower($searchText) }}">
                        <div class="flex items-start gap-3">
                            @if($training->cover_image_path)
                                <img src="{{ asset('storage/' . $training->cover_image_path) }}"
                                     alt=""
                                     class="h-12 w-12 shrink-0 rounded-lg object-cover">
                            @else
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#f5f7eb] text-[#647a0b]">
                                    <i class="fas fa-play text-sm"></i>
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <h2 class="min-w-0 truncate text-sm font-semibold text-gray-900">
                                        {{ $training->title }}
                                    </h2>
                                    <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $badgeClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">
                                    {{ $training->description ?: 'Formation sans description' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-3 gap-2">
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Prix</div>
                                <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $price }}</div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Modules</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $training->modules_count }}</div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Inscrits</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $training->enrollments_count }}</div>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $training->access_type === 'public' ? 'Public' : ($training->access_type === 'private' ? 'Prive' : 'Abonnement') }}
                            </span>
                            @if($training->estimated_duration_minutes)
                                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                    {{ $training->estimated_duration_minutes }} min
                                </span>
                            @endif
                            @foreach($tags as $tag)
                                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function filterMobileTrainings() {
            const input = document.getElementById('mobileTrainingSearch');
            const filter = input ? input.value.toLowerCase() : '';
            const items = document.querySelectorAll('#mobileTrainingList > a');

            items.forEach((item) => {
                const text = item.getAttribute('data-training') || '';
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</x-mobile-layout>
