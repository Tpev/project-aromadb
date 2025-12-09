{{-- resources/views/digital-trainings/player.blade.php --}}
@php
    $author      = optional($training->user);
    $authorName  = $author->name ?? __('Votre thérapeute');
    $authorEmail = $author->email ?? null;

    // Replace use Str::... with full namespace
    $authorInit  = \Illuminate\Support\Str::upper(
                        \Illuminate\Support\Str::substr($authorName, 0, 1)
                   );

    $participantName  = $enrollment->participant_name ?: $enrollment->participant_email;
    $participantEmail = $enrollment->participant_email;

    $progress = (int) ($enrollment->progress_percent ?? 0);
    if ($enrollment->completed_at ?? false) { $progress = 100; }
    $progress = max(0, min(100, $progress));

    $modulesPayload = $modules->map(function($m) {
        return [
            'id'          => $m->id,
            'title'       => $m->title,
            'description' => $m->description,
            'blocks'      => ($m->sorted_blocks ?? $m->blocks ?? collect())
                                ->map(function($b) {
                                    return [
                                        'id'        => $b->id,
                                        'type'      => $b->type,
                                        'title'     => $b->title,
                                        'content'   => $b->content,
                                        'file_path' => $b->file_path,
                                    ];
                                })
                                ->values()
                                ->all(),
        ];
    })->values()->all();
@endphp


<x-guest-layout>
    <style>
        :root {
            --brand: #647a0b;
        }
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: radial-gradient(circle at top left, #e4f0d4 0, #f3f4f6 45%, #e5e7eb 100%);
            color: #0f172a;
        }

        .page-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e5e7eb;
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-header-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .page-header-title {
            font-size: 16px;
            font-weight: 650;
            color: var(--brand);
        }
        .page-header-sub {
            font-size: 11px;
            color: #6b7280;
        }
        .page-header-right {
            text-align: right;
            font-size: 11px;
            color: #4b5563;
        }

        .page-main {
            flex: 1;
            width: 100%;
            padding: 24px 32px 32px;
            display: grid;
            grid-template-columns: minmax(260px, 320px) minmax(0, 1fr); /* ~1/4 – 3/4 */
            gap: 24px;
            align-items: flex-start;
        }

        .card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        }

        .sidebar {
            padding: 16px 14px 14px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .content {
            padding: 18px 18px 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .badge-pill {
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 10px;
            background: #eff6ff;
            color: #1d4ed8;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .progress-bar-outer {
            position: relative;
            width: 180px;
            height: 6px;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
        }
        .progress-bar-inner {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            width: 0;
            background: linear-gradient(90deg, var(--brand), #a3e635);
            transition: width 0.35s ease;
        }

        .sidebar-header {
            display: flex;
            gap: 10px;
        }
        .sidebar-cover {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            overflow: hidden;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #9ca3af;
        }
        .sidebar-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .sidebar-header-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .sidebar-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }
        .sidebar-meta {
            font-size: 11px;
            color: #6b7280;
        }

        .author-box {
            margin-top: 4px;
            padding: 8px 9px;
            border-radius: 12px;
            border: 1px dashed #e5e7eb;
            background: #f9fafb;
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }
        .author-avatar {
            width: 26px;
            height: 26px;
            border-radius: 999px;
            background: #e4f0d4;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: var(--brand);
        }
        .author-text {
            font-size: 11px;
            color: #4b5563;
        }
        .author-name {
            font-weight: 600;
            font-size: 11px;
            color: #111827;
        }

        .tag-list {
            margin-top: 4px;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        .tag-chip {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            background: #f3f4f6;
            font-size: 10px;
            color: #4b5563;
        }

        .sidebar-section-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
            margin-top: 4px;
        }

        .module-list {
            margin-top: 6px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .module-item {
            border-radius: 12px;
            padding: 8px 9px;
            cursor: pointer;
            border: 1px solid transparent;
            font-size: 13px;
        }
        .module-item:hover {
            background: #f9fafb;
        }
        .module-item.active {
            border-color: var(--brand);
            background: #f7fbe8;
            box-shadow: 0 0 0 1px rgba(100, 122, 11, 0.06);
        }
        .module-title-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 6px;
        }
        .module-title-text {
            font-size: 12px;
            font-weight: 600;
            color: #111827;
        }
        .module-index-pill {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 999px;
            background: #e5e7eb;
            color: #374151;
        }
        .module-desc {
            margin-top: 2px;
            font-size: 11px;
            color: #6b7280;
        }
        .block-list {
            margin-top: 5px;
        }
        .block-pill {
            font-size: 11px;
            padding: 3px 6px;
            border-radius: 999px;
            margin-right: 4px;
            margin-bottom: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f3f4f6;
            color: #4b5563;
            border: 1px solid transparent;
            cursor: pointer;
        }
        .block-pill.active {
            background: var(--brand);
            color: #ffffff;
            border-color: var(--brand);
        }

        .sidebar-footer {
            margin-top: auto;
        }

        .btn {
            border-radius: 999px;
            border: 1px solid #d1d5db;
            padding: 7px 14px;
            font-size: 13px;
            cursor: pointer;
            background: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-primary {
            background: var(--brand);
            color: white;
            border-color: var(--brand);
        }
        .btn-primary:hover {
            background: #506108;
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: default;
        }
        .btn-outline {
            background: transparent;
        }

        .content-meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .content-meta-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .content-path {
            font-size: 11px;
            color: #9ca3af;
        }
        .content-header-title {
            font-size: 18px;
            font-weight: 630;
            color: #111827;
        }
        .content-subtitle {
            font-size: 13px;
            color: #6b7280;
        }

        .content-body {
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            padding: 20px;
            min-height: 60vh; /* larger working area */
        }
        .content-body-inner {
            font-size: 14px;
            line-height: 1.6;
            color: #111827;
        }

        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            gap: 10px;
        }
        .nav-side {
            font-size: 11px;
            color: #6b7280;
        }

        .alert-success {
            border-radius: 10px;
            border: 1px solid #bbf7d0;
            background: #ecfdf3;
            color: #166534;
            font-size: 12px;
            padding: 8px 10px;
            margin-top: 2px;
        }

        @media (max-width: 900px) {
            .page-main {
                grid-template-columns: minmax(0, 1fr);
                padding: 16px;
            }
            .sidebar {
                order: 2;
            }
            .content {
                order: 1;
            }
        }
    </style>

    <div class="page-shell">
        <header class="page-header">
            <div class="page-header-left">
                <div class="page-header-title">
                    {{ $training->title }}
                </div>
                <div class="page-header-sub">
                    {{ __('Accès direct à votre formation – AromaMade') }}
                </div>
            </div>
            <div class="page-header-right">
                <div style="margin-bottom:4px;">
                    {{ $participantName }}<br>
                    <span style="color:#6b7280;">{{ $participantEmail }}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px;">
                    <span style="font-size:11px;color:#6b7280;">
                        {{ __('Progrès : :p%', ['p' => $progress]) }}
                    </span>
                    <div class="progress-bar-outer">
                        <div class="progress-bar-inner"
                             id="globalProgressBar"
                             style="width: {{ $progress }}%;"></div>
                    </div>
                </div>
            </div>
        </header>

        <main class="page-main">
            {{-- SIDEBAR --}}
            <aside class="card sidebar">
                <div class="sidebar-header">
                    <div class="sidebar-cover">
                        @if($training->cover_image_path)
                            <img src="{{ asset('storage/'.$training->cover_image_path) }}" alt="">
                        @else
                            <span>IMG</span>
                        @endif
                    </div>
                    <div class="sidebar-header-main">
                        <div class="sidebar-title">
                            {{ $training->title }}
                        </div>
                        <div class="sidebar-meta">
                            @if($training->estimated_duration_minutes)
                                {{ __('Durée estimée :') }}
                                <strong>{{ $training->estimated_duration_minutes }} min</strong>
                            @endif
                        </div>

                        <div class="author-box">
                            <div class="author-avatar">
                                {{ $authorInit }}
                            </div>
                            <div class="author-text">
                                <div class="author-name">{{ $authorName }}</div>
                                <div>
                                    <span style="opacity:.9;">
                                        {{ __('Créateur de cette formation.') }}
                                    </span>
                                    @if($authorEmail)
                                        <br>
                                        <span style="font-size:10px;color:#9ca3af;">
                                            {{ $authorEmail }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($training->tags)
                    <div class="tag-list">
                        @foreach($training->tags as $tag)
                            <span class="tag-chip">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                <div>
                    <div class="sidebar-section-title">
                        {{ __('Contenu de la formation') }}
                    </div>

                    <div class="module-list">
                        @foreach($modules as $index => $module)
                            <div class="module-item"
                                 data-module-index="{{ $index }}"
                                 onclick="selectModule({{ $index }})">
                                <div class="module-title-row">
                                    <div class="module-title-text">
                                        {{ $module->title ?? __('Module :num', ['num' => $index + 1]) }}
                                    </div>
                                    <div class="module-index-pill">
                                        {{ 'M'.($index + 1) }}
                                    </div>
                                </div>
                                @if($module->description)
                                    <div class="module-desc">
                                        {{ $module->description }}
                                    </div>
                                @endif

                                @php
                                    $blocks = $module->sorted_blocks ?? $module->blocks ?? collect();
                                @endphp
                                @if($blocks->count())
                                    <div class="block-list">
                                        @foreach($blocks as $bIndex => $block)
                                            @php
                                                $icon = '📝';
                                                if ($block->type === 'pdf') {
                                                    $icon = '📄';
                                                } elseif ($block->type === 'video_url') {
                                                    $icon = '🎬';
                                                }
                                            @endphp
                                            <span class="block-pill"
                                                  data-module-index="{{ $index }}"
                                                  data-block-index="{{ $bIndex }}"
                                                  onclick="event.stopPropagation(); selectBlock({{ $index }}, {{ $bIndex }})">
                                                <span>{{ $icon }}</span>
                                                <span>{{ $block->title ?: __('Contenu :num', ['num' => $bIndex + 1]) }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="sidebar-footer">
                    <form action="{{ route('digital-trainings.access.complete', $enrollment->access_token) }}"
                          method="POST" style="margin-top:10px;">
                        @csrf
                        <button type="submit"
                                class="btn btn-primary"
                                style="width:100%;">
                            ✅ {{ __('Marquer la formation comme terminée') }}
                        </button>
                    </form>
                </div>
            </aside>

            {{-- MAIN CONTENT --}}
            <section class="card content">
                <div class="content-meta-row">
                    <div class="content-meta-left">
                        <div class="content-path" id="content-path"></div>
                        <div class="content-header-title" id="block-title"></div>
                        <div class="content-subtitle" id="block-subtitle"></div>
                    </div>
                    <div class="nav-side">
                        <span class="badge-pill">
                            <span>👣</span>
                            <span id="stepIndicator">0/0</span>
                        </span>
                    </div>
                </div>

                <div class="content-body">
                    <div id="block-content" class="content-body-inner"></div>
                </div>

                <div class="nav-buttons">
                    <button class="btn btn-outline" id="btnPrev" onclick="goPrev()">
                        ← {{ __('Précédent') }}
                    </button>
                    <button class="btn btn-outline" id="btnNext" onclick="goNext()" style="margin-left:auto;">
                        {{ __('Suivant') }} →
                    </button>
                </div>
            </section>
        </main>
    </div>

    <script>
        const modules = @json($modulesPayload);

        let currentModuleIndex = 0;
        let currentBlockIndex  = 0;

        function selectModule(mIndex) {
            currentModuleIndex = mIndex;
            currentBlockIndex  = 0;
            updateActiveUI();
            renderCurrentBlock();
        }

        function selectBlock(mIndex, bIndex) {
            currentModuleIndex = mIndex;
            currentBlockIndex  = bIndex;
            updateActiveUI();
            renderCurrentBlock();
        }

        function flattenBlocks() {
            const flat = [];
            modules.forEach((m, mi) => {
                (m.blocks || []).forEach((b, bi) => {
                    flat.push({ moduleIndex: mi, blockIndex: bi });
                });
            });
            return flat;
        }

        function findCurrentFlatIndex(flat) {
            for (let i = 0; i < flat.length; i++) {
                if (flat[i].moduleIndex === currentModuleIndex && flat[i].blockIndex === currentBlockIndex) {
                    return i;
                }
            }
            return null;
        }

        function goPrev() {
            const flat = flattenBlocks();
            const currentFlatIndex = findCurrentFlatIndex(flat);
            if (currentFlatIndex === null || currentFlatIndex <= 0) return;
            const prev = flat[currentFlatIndex - 1];
            currentModuleIndex = prev.moduleIndex;
            currentBlockIndex  = prev.blockIndex;
            updateActiveUI();
            renderCurrentBlock();
        }

        function goNext() {
            const flat = flattenBlocks();
            const currentFlatIndex = findCurrentFlatIndex(flat);
            if (currentFlatIndex === null || currentFlatIndex >= flat.length - 1) return;
            const next = flat[currentFlatIndex + 1];
            currentModuleIndex = next.moduleIndex;
            currentBlockIndex  = next.blockIndex;
            updateActiveUI();
            renderCurrentBlock();
        }

        function renderCurrentBlock() {
            const metaTitle   = document.getElementById('block-title');
            const metaSub     = document.getElementById('block-subtitle');
            const metaPath    = document.getElementById('content-path');
            const contentWrap = document.getElementById('block-content');
            const stepInd     = document.getElementById('stepIndicator');

            const module = modules[currentModuleIndex];
            const flat   = flattenBlocks();

            if (!module || !module.blocks || !module.blocks.length) {
                metaTitle.textContent = 'Aucun contenu';
                metaSub.textContent   = '';
                metaPath.textContent  = '';
                stepInd.textContent   = '0/0';
                contentWrap.innerHTML = '<p style="font-size:13px;color:#6b7280;">Ce module ne contient aucun contenu pour le moment.</p>';
                updateNavButtons();
                return;
            }

            const block = module.blocks[currentBlockIndex];

            const moduleLabel = module.title ? module.title : ('Module ' + (currentModuleIndex + 1));
            const blockLabel  = block.title ? block.title : ('Contenu ' + (currentBlockIndex + 1));

            metaTitle.textContent = blockLabel;
            metaSub.textContent   = moduleLabel;

            const currentFlatIndex = findCurrentFlatIndex(flat);
            const totalSteps       = flat.length || 0;
            const currentStep      = currentFlatIndex !== null ? currentFlatIndex + 1 : 1;

            metaPath.textContent = 'Module ' + (currentModuleIndex + 1) + ' · Leçon ' + (currentBlockIndex + 1);
            stepInd.textContent  = currentStep + '/' + totalSteps;

            let html = '';

            if (block.type === 'text') {
                const content = block.content || '';
                html = `<div>${content}</div>`;
            } else if (block.type === 'video_url') {
                const url = block.content || '';
                html = `
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <div style="position:relative;width:100%;padding-bottom:56.25%;border-radius:12px;overflow:hidden;background:#0f172a;">
                            <iframe src="${url}" frameborder="0" allowfullscreen
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;"></iframe>
                        </div>
                        <div style="font-size:12px;color:#6b7280;">
                            Si la vidéo ne s’affiche pas correctement, vous pouvez
                            <a href="${url}" target="_blank" style="color:#647a0b;text-decoration:underline;">ouvrir la vidéo dans un nouvel onglet</a>.
                        </div>
                    </div>`;
            } else if (block.type === 'pdf') {
                const filePath = block.file_path ? `{{ rtrim(config('app.url'), '/') }}/storage/` + block.file_path : null;
                if (filePath) {
                    html = `
                        <div style="display:flex;flex-direction:column;gap:8px;height:100%;">
                            <div style="flex:1;min-height:420px;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
                                <embed src="${filePath}#toolbar=1&navpanes=0" type="application/pdf" style="width:100%;height:100%;">
                            </div>
                            <div style="font-size:12px;color:#6b7280;">
                                Si le document ne s’affiche pas, vous pouvez
                                <a href="${filePath}" target="_blank" style="color:#647a0b;text-decoration:underline;">télécharger le PDF</a>.
                            </div>
                        </div>`;
                } else {
                    html = `<p style="font-size:13px;color:#6b7280;">Ce document PDF n’est pas disponible.</p>`;
                }
            } else {
                html = `<p style="font-size:13px;color:#6b7280;">Type de contenu non reconnu.</p>`;
            }

            contentWrap.innerHTML = html;
            updateNavButtons();
        }

        function updateActiveUI() {
            document.querySelectorAll('.module-item').forEach((el, idx) => {
                el.classList.toggle('active', idx === currentModuleIndex);
            });
            document.querySelectorAll('.block-pill').forEach((el) => {
                const mi = parseInt(el.getAttribute('data-module-index'), 10);
                const bi = parseInt(el.getAttribute('data-block-index'), 10);
                const active = (mi === currentModuleIndex && bi === currentBlockIndex);
                el.classList.toggle('active', active);
            });
        }

        function updateNavButtons() {
            const flat    = flattenBlocks();
            const prevBtn = document.getElementById('btnPrev');
            const nextBtn = document.getElementById('btnNext');

            const idx = findCurrentFlatIndex(flat);
            if (idx === null || !flat.length) {
                prevBtn.disabled = true;
                nextBtn.disabled = true;
                return;
            }

            prevBtn.disabled = (idx === 0);
            nextBtn.disabled = (idx === flat.length - 1);
        }

        (function init() {
            let found = false;
            modules.forEach((m, mi) => {
                if (!found && m.blocks && m.blocks.length) {
                    currentModuleIndex = mi;
                    currentBlockIndex  = 0;
                    found = true;
                }
            });
            if (!found) {
                currentModuleIndex = 0;
                currentBlockIndex  = 0;
            }
            updateActiveUI();
            renderCurrentBlock();
        })();
    </script>
</x-guest-layout>
