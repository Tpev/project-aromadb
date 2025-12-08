{{-- resources/views/digital-trainings/preview.blade.php --}}
@php
    // Flatten blocks for JS + global index mapping
    $flatBlocks = [];
    $globalIndex = 0;

    foreach ($training->modules as $module) {
        foreach ($module->blocks as $block) {
            $flatBlocks[] = [
                'index'        => $globalIndex,
                'id'           => $block->id,
                'module_id'    => $module->id,
                'module_title' => $module->title,
                'type'         => $block->type,
                'title'        => $block->title,
                'content'      => $block->content,
                'file_path'    => $block->file_path,
            ];
            $globalIndex++;
        }
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl" style="color:#647a0b;">
                    {{ __('Prévisualisation de la formation') }}
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    {{ $training->title }}
                </p>
            </div>
            <a href="{{ route('digital-trainings.index') }}"
               class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50">
                ← {{ __('Retour à vos formations') }}
            </a>
        </div>
    </x-slot>

    <div class="container mt-6">
        <div class="mx-auto max-w-6xl space-y-4">

            {{-- Top card: title + meta + progress --}}
            <div class="rounded-2xl bg-gradient-to-r from-[#647a0b]/10 via-white to-[#647a0b]/5 border border-slate-100 p-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        @if($training->cover_image_path)
                            <img src="{{ asset('storage/'.$training->cover_image_path) }}"
                                 alt=""
                                 class="h-16 w-16 rounded-xl object-cover shadow-sm">
                        @else
                            <div class="h-16 w-16 rounded-xl bg-[#647a0b]/10 flex items-center justify-center text-xs font-semibold text-[#647a0b]">
                                {{ __('AM') }}
                            </div>
                        @endif
                        <div>
                            <div class="text-sm font-semibold text-slate-900">
                                {{ $training->title }}
                            </div>
                            @if($training->estimated_duration_minutes)
                                <div class="mt-1 text-xs text-slate-600">
                                    {{ __('Durée estimée :') }} {{ $training->estimated_duration_minutes }} min
                                </div>
                            @endif
                            @if($training->tags)
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach($training->tags as $tag)
                                        <span class="inline-flex items-center rounded-full bg-white/70 px-2 py-0.5 text-[11px] text-slate-700 border border-slate-100">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col items-start md:items-end gap-2 text-xs">
                        <div class="flex flex-wrap gap-2">
                            {{-- Access type --}}
                            @if($training->access_type === 'public')
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700 border border-emerald-100">
                                    {{ __('Accès public') }}
                                </span>
                            @elseif($training->access_type === 'private')
                                <span class="inline-flex items-center rounded-full bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-700 border border-slate-200">
                                    {{ __('Accès privé') }}
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-700 border border-amber-100">
                                    {{ __('Accès par abonnement') }}
                                </span>
                            @endif

                            {{-- Status --}}
                            @if($training->status === 'draft')
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-700 border border-slate-200">
                                    {{ __('Brouillon') }}
                                </span>
                            @elseif($training->status === 'published')
                                <span class="inline-flex items-center rounded-full bg-[#647a0b] px-3 py-1 text-[11px] font-semibold text-white">
                                    {{ __('Publié') }}
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-[11px] font-semibold text-rose-700 border border-rose-100">
                                    {{ __('Archivé') }}
                                </span>
                            @endif
                        </div>

                        <div class="w-48">
                            <div class="flex justify-between text-[11px] text-slate-500 mb-1">
                                <span>{{ __('Progression (aperçu)') }}</span>
                                <span id="preview-progress-label">0%</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-200 overflow-hidden">
                                <div id="preview-progress-bar"
                                     class="h-2 rounded-full"
                                     style="background: linear-gradient(90deg, #647a0b, #8da72c); width:0%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(empty($flatBlocks))
                <div class="mx-auto max-w-2xl rounded-2xl bg-white shadow-sm border border-dashed border-slate-200 p-6 text-center">
                    <p class="text-sm text-slate-600">
                        {{ __('Aucun contenu n’est encore présent dans cette formation.') }}
                    </p>
                    <p class="mt-2 text-xs text-slate-500">
                        {{ __('Ajoutez des modules et des blocs depuis l’éditeur de contenu pour voir ici un aperçu côté client.') }}
                    </p>
                    <a href="{{ route('digital-trainings.builder', $training) }}"
                       class="mt-4 inline-flex items-center rounded-full bg-[#647a0b] px-4 py-2 text-xs font-semibold text-white hover:bg-[#506108]">
                        {{ __('Ouvrir l’éditeur de contenu') }}
                    </a>
                </div>
            @else
                {{-- Main learning player --}}
                <div class="flex flex-col lg:flex-row gap-4">
                    {{-- MAIN CONTENT --}}
                    <div class="flex-1 rounded-2xl bg-white shadow-sm border border-slate-100 p-5 flex flex-col">
                        <div id="preview-module-label" class="text-xs font-semibold uppercase tracking-wide text-[#647a0b] mb-1">
                            {{-- Filled by JS --}}
                        </div>
                        <h1 id="preview-title" class="text-xl font-semibold text-slate-900 mb-3">
                            {{-- Filled by JS --}}
                        </h1>

                        <div id="preview-content" class="flex-1 text-sm text-slate-700 leading-relaxed">
                            {{-- Filled by JS --}}
                        </div>

                        {{-- NAVIGATION FOOTER --}}
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-4">
                            <div class="text-[11px] text-slate-500">
                                <span id="preview-position-label">1 / {{ count($flatBlocks) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        id="preview-prev-btn"
                                        onclick="goToPreviousBlock()"
                                        class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
                                    ← {{ __('Précédent') }}
                                </button>
                                <button type="button"
                                        id="preview-next-btn"
                                        onclick="goToNextBlock()"
                                        class="inline-flex items-center rounded-full bg-[#647a0b] px-4 py-1.5 text-xs font-semibold text-white hover:bg-[#506108] disabled:bg-slate-300 disabled:cursor-not-allowed">
                                    {{ __('Suivant') }} →
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- SIDEBAR: TABLE OF CONTENTS --}}
                    <aside class="w-full lg:w-72 rounded-2xl bg-white shadow-sm border border-slate-100 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-slate-900">
                                {{ __('Plan de la formation') }}
                            </h3>
                            <span class="text-[11px] text-slate-500">
                                {{ count($flatBlocks) }} {{ __('contenus') }}
                            </span>
                        </div>

                        <div class="space-y-4 max-h-[480px] overflow-y-auto pr-1">
                            @php $globalIndex = 0; @endphp
                            @foreach($training->modules as $module)
                                @if($module->blocks->isNotEmpty())
                                    <div class="rounded-xl bg-slate-50/80 border border-slate-100 px-3 py-2">
                                        <div class="text-[12px] font-semibold text-slate-800 mb-1">
                                            {{ $module->title }}
                                        </div>
                                        <ul class="space-y-1">
                                            @foreach($module->blocks as $block)
                                                <li>
                                                    <button
                                                        type="button"
                                                        class="toc-item w-full text-left text-[12px] px-2 py-1 rounded-lg text-slate-600 hover:bg-white hover:text-[#647a0b]"
                                                        data-index="{{ $globalIndex }}"
                                                        onclick="goToIndex({{ $globalIndex }})">
                                                        <span class="mr-1 text-[10px] text-slate-400">
                                                            {{ $globalIndex+1 }}.
                                                        </span>
                                                        <span>
                                                            {{ $block->title ?: __('Contenu').' '.($globalIndex+1) }}
                                                        </span>
                                                    </button>
                                                </li>
                                                @php $globalIndex++; @endphp
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </div>

    @if(!empty($flatBlocks))
        <script>
            const blocks = @json($flatBlocks);
            let currentIndex = 0;

            function renderBlock(index) {
                if (!blocks.length) return;
                if (index < 0 || index >= blocks.length) return;

                currentIndex = index;
                const block = blocks[currentIndex];

                // DOM elements
                const moduleLabel   = document.getElementById('preview-module-label');
                const titleEl       = document.getElementById('preview-title');
                const contentEl     = document.getElementById('preview-content');
                const posLabel      = document.getElementById('preview-position-label');
                const progressBar   = document.getElementById('preview-progress-bar');
                const progressLabel = document.getElementById('preview-progress-label');
                const prevBtn       = document.getElementById('preview-prev-btn');
                const nextBtn       = document.getElementById('preview-next-btn');

                const total    = blocks.length;
                const position = currentIndex + 1;
                const percent  = Math.round((position / total) * 100);

                // Labels & progress
                if (moduleLabel) {
                    moduleLabel.textContent = block.module_title ? block.module_title.toUpperCase() : '';
                }
                if (titleEl) {
                    titleEl.textContent = block.title || '{{ __('Contenu') }} ' + position;
                }
                if (posLabel) {
                    posLabel.textContent = position + ' / ' + total;
                }
                if (progressBar) {
                    progressBar.style.width = percent + '%';
                }
                if (progressLabel) {
                    progressLabel.textContent = percent + '%';
                }

                // Main content
                let html = '';

                if (block.type === 'text') {
                    const content = (block.content || '').replace(/\n/g, '<br>');
                    html = `
                        <div class="prose max-w-none text-sm text-slate-700">
                            ${content || '<span class="text-slate-400 text-xs">{{ __('Aucun texte renseigné pour ce contenu.') }}</span>'}
                        </div>
                    `;
                } else if (block.type === 'video_url') {
                    const url = block.content || '';
                    html = `
                        <div class="space-y-4">
                            <div class="aspect-video w-full rounded-xl overflow-hidden border border-slate-200 bg-black/80">
                                <iframe src="${url}"
                                        class="w-full h-full"
                                        allowfullscreen
                                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                            <p class="text-[11px] text-slate-500">
                                ${url}
                            </p>
                        </div>
                    `;
                } else if (block.type === 'pdf') {
                    if (block.file_path) {
                        const src = `/storage/${block.file_path}#toolbar=1&navpanes=1&scrollbar=1`;
                        html = `
                            <div class="space-y-4">
                                <p class="text-sm text-slate-700">
                                    {{ __('Aperçu intégré du document PDF tel que le verrait votre client.') }}
                                </p>
                                <div class="w-full rounded-xl overflow-hidden border border-slate-200 bg-slate-100" style="min-height: 480px;">
                                    <iframe src="${src}"
                                            class="w-full h-full"
                                            style="min-height:480px;"
                                            title="PDF preview">
                                    </iframe>
                                </div>
                                <p class="text-[11px] text-slate-500">
                                    {{ __('Le client pourra également l’ouvrir en plein écran ou le télécharger depuis son lecteur PDF.') }}
                                </p>
                            </div>
                        `;
                    } else {
                        html = `
                            <p class="text-sm text-slate-500">
                                {{ __('Aucun fichier PDF associé à ce contenu pour le moment.') }}
                            </p>
                        `;
                    }
                } else {
                    html = `
                        <p class="text-sm text-slate-500">
                            {{ __('Type de contenu non pris en charge dans cette prévisualisation.') }}
                        </p>
                    `;
                }

                if (contentEl) {
                    contentEl.innerHTML = html;
                }

                // Buttons state
                if (prevBtn) {
                    prevBtn.disabled = currentIndex === 0;
                }
                if (nextBtn) {
                    nextBtn.disabled = currentIndex === total - 1;
                }

                // Highlight active TOC item
                const tocItems = document.querySelectorAll('.toc-item');
                tocItems.forEach((el) => {
                    const idx = parseInt(el.getAttribute('data-index'), 10);
                    if (idx === currentIndex) {
                        el.classList.add('bg-white', 'border', 'border-[#647a0b]/40', 'text-[#647a0b]');
                    } else {
                        el.classList.remove('bg-white', 'border', 'border-[#647a0b]/40', 'text-[#647a0b]');
                    }
                });
            }

            function goToIndex(index) {
                renderBlock(index);
            }

            function goToPreviousBlock() {
                if (currentIndex > 0) {
                    renderBlock(currentIndex - 1);
                }
            }

            function goToNextBlock() {
                if (currentIndex < blocks.length - 1) {
                    renderBlock(currentIndex + 1);
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                if (blocks.length) {
                    renderBlock(0);
                }
            });
        </script>
    @endif
</x-app-layout>
