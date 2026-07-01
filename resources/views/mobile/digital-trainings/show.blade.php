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
    $blockLabels = [
        'text' => 'Texte',
        'video_url' => 'Video',
        'audio' => 'Audio',
        'pdf' => 'PDF',
    ];
    $statusLabel = $statusLabels[$training->status] ?? 'Statut';
    $statusClass = $statusClasses[$training->status] ?? $statusClasses['draft'];
    $price = $training->is_free ? 'Gratuit' : $formatMoney($training->price_cents);
    $publicUrl = $training->status === 'published' ? route('digital-trainings.public.show', $training->slug) : null;
@endphp

<x-mobile-layout :title="$training->title">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.digital-trainings.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Formations digitales
            </a>

            <div class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    @if($training->cover_image_path)
                        <img src="{{ asset('storage/' . $training->cover_image_path) }}"
                             alt=""
                             class="h-14 w-14 shrink-0 rounded-lg object-cover">
                    @else
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                            <i class="fas fa-graduation-cap text-sm"></i>
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl font-semibold leading-tight text-gray-900">
                            {{ $training->title }}
                        </h1>
                        <p class="mt-1 line-clamp-3 text-sm leading-snug text-gray-600">
                            {{ $training->description ?: 'Formation sans description' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
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

                <div class="mt-4 flex flex-wrap gap-1.5">
                    <span class="rounded-full border px-2 py-1 text-[11px] font-medium {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                    <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                        {{ $training->access_type === 'public' ? 'Public' : ($training->access_type === 'private' ? 'Prive' : 'Abonnement') }}
                    </span>
                    @if($training->estimated_duration_minutes)
                        <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                            {{ $training->estimated_duration_minutes }} min
                        </span>
                    @endif
                    @if($training->installments_enabled)
                        <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                            Paiement {{ implode('/', $training->allowed_installments ?? []) }}x
                        </span>
                    @endif
                    @foreach(($training->tags ?? []) as $tag)
                        <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                            {{ $tag }}
                        </span>
                    @endforeach
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('mobile.digital-trainings.edit', $training) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-edit mr-1.5 text-[11px]"></i>
                        Modifier
                    </a>
                    <a href="{{ route('digital-trainings.builder', $training) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg bg-[#647a0b] text-xs font-semibold text-white">
                        <i class="fas fa-layer-group mr-1.5 text-[11px]"></i>
                        Contenu
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Actions rapides</h2>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a href="{{ route('digital-trainings.enrollments.index', $training) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-users mr-1.5 text-[11px]"></i>
                        Participants
                    </a>
                    <a href="{{ route('digital-trainings.preview', $training) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-eye mr-1.5 text-[11px]"></i>
                        Previsualiser
                    </a>
                    @if($publicUrl)
                        <button type="button"
                                onclick="copyMobileTrainingLink('{{ $publicUrl }}')"
                                class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                            <i class="fas fa-link mr-1.5 text-[11px]"></i>
                            Copier le lien
                        </button>
                        <a href="{{ $publicUrl }}"
                           class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                            <i class="fas fa-external-link-alt mr-1.5 text-[10px]"></i>
                            Page publique
                        </a>
                    @else
                        <div class="col-span-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs leading-snug text-amber-800">
                            Publiez la formation pour obtenir un lien public partageable.
                        </div>
                    @endif
                </div>
                <div id="mobileTrainingCopyStatus" class="mt-2 hidden rounded-lg bg-[#f5f7eb] px-3 py-2 text-xs font-medium text-[#647a0b]">
                    Lien copie.
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Plan de formation</h2>
                        <p class="mt-1 text-xs text-gray-500">{{ $blocksCount }} contenu{{ $blocksCount > 1 ? 's' : '' }} dans le builder.</p>
                    </div>
                    <a href="{{ route('digital-trainings.builder', $training) }}"
                       class="inline-flex h-9 shrink-0 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-xs font-semibold text-white">
                        Gerer
                    </a>
                </div>

                @if($training->modules->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">
                        Aucun module pour le moment. Ajoutez vos contenus depuis le builder.
                    </p>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach($training->modules as $module)
                            <article class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-sm font-semibold text-gray-900">{{ $module->title }}</h3>
                                        @if($module->description)
                                            <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">{{ $module->description }}</p>
                                        @endif
                                    </div>
                                    <span class="shrink-0 rounded-full border border-[#647a0b]/20 bg-[#647a0b]/10 px-2 py-0.5 text-[10px] font-medium text-[#647a0b]">
                                        {{ $module->blocks->count() }} contenu{{ $module->blocks->count() > 1 ? 's' : '' }}
                                    </span>
                                </div>

                                @if($module->blocks->isNotEmpty())
                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        @foreach($module->blocks->take(4) as $block)
                                            <span class="rounded-full bg-white px-2 py-1 text-[11px] text-gray-600">
                                                {{ $blockLabels[$block->type] ?? ucfirst((string) $block->type) }}
                                            </span>
                                        @endforeach
                                        @if($module->blocks->count() > 4)
                                            <span class="rounded-full bg-white px-2 py-1 text-[11px] text-gray-600">
                                                +{{ $module->blocks->count() - 4 }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Participants recents</h2>

                @if($training->enrollments->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">Aucun participant pour cette formation.</p>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach($training->enrollments as $enrollment)
                            @php
                                $clientName = trim(($enrollment->clientProfile->first_name ?? '') . ' ' . ($enrollment->clientProfile->last_name ?? ''));
                                $participant = $enrollment->participant_name ?: ($clientName ?: 'Participant');
                            @endphp
                            <article class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-gray-900">{{ $participant }}</div>
                                        <div class="mt-1 truncate text-xs text-gray-600">
                                            {{ $enrollment->participant_email ?: 'Email non renseigne' }}
                                        </div>
                                    </div>
                                    <span class="shrink-0 rounded-full border border-[#647a0b]/20 bg-[#647a0b]/10 px-2 py-0.5 text-[10px] font-medium text-[#647a0b]">
                                        {{ (int) $enrollment->progress_percent }}%
                                    </span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <form method="POST"
                  action="{{ route('mobile.digital-trainings.destroy', $training) }}"
                  onsubmit="return confirm('Supprimer cette formation ?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-sm font-semibold text-red-600">
                    <i class="fas fa-trash-alt mr-1.5 text-[11px]"></i>
                    Supprimer la formation
                </button>
            </form>
        </div>
    </div>

    <script>
        async function copyMobileTrainingLink(url) {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(url);
            } else {
                const input = document.createElement('input');
                input.value = url;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                input.remove();
            }

            const status = document.getElementById('mobileTrainingCopyStatus');
            if (status) {
                status.classList.remove('hidden');
                setTimeout(() => status.classList.add('hidden'), 2200);
            }
        }
    </script>
</x-mobile-layout>
