{{-- resources/views/digital-trainings/player.blade.php --}}
@php
    $brandGreen = '#647a0b';

    // Optional author (assuming DigitalTraining belongsTo User)
    $author     = optional($training->user);
    $authorName = $author->company_name
        ?? ($author->name ?? ($author->first_name ?? null) . ' ' . ($author->last_name ?? ''))
        ?? 'Votre thérapeute';

    // Precompute safe array for JS
    $jsModules = $modules->map(function ($m) {
        return [
            'id'          => $m->id,
            'title'       => $m->title,
            'description' => $m->description,
            'blocks'      => $m->sorted_blocks->map(function ($b) {
                return [
                    'id'        => $b->id,
                    'type'      => $b->type,
                    'title'     => $b->title,
                    'content'   => $b->content,
                    'file_path' => $b->file_path,
                ];
            })->values()->all(),
        ];
    })->values()->all();
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $training->title }} - {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        :root {
            --brand: {{ $brandGreen }};
        }
        * {
            box-sizing: border-box;
        }
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
            padding: 14px 20px;
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
            padding: 18px;
            max-width: 1200px;
            margin: 0 auto 18px;
            display: grid;
            grid-template-columns: minmax(230px, 280px) minmax(0, 1fr);
            gap: 18px;
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

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 500;
            background: #ecfdf3;
            color: #166534;
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
            padding: 16px;
            min-height: 320px;
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
            }
            .sidebar {
                order: 2;
            }
            .content {
                order: 1;
            }
        }
    </style>
</head>
<body>
<div class="page-shell">
    {{-- Top header --}}
    <header class="page-header">
        <div class="page-header-left">
            <div class="page-header-title">
                {{ $training->title }}
            </div>
            <div class="page-header-sub">
                Accès direct à votre formation – {{ config('app.name') }}
            </div>
        </div>
        <div class="page-header-right">
            <div style="margin-bottom:4px;">
                @if($enrollment->participant_name)
                    {{ $enrollment->participant_name }}<br>
                @endif
                <span style="color:#6b7280;">{{ $enrollment->participant_email }}</span>
            </div>
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px;">
                <span style="font-size:11px;color:#6b7280;">
                    Progrès : {{ $enrollment->progress_percent }}%
                </span>
                <div class="progress-bar-outer">
                    <div class="progress-bar-inner" id="globalProgressBar"
                         style="width: {{ max(0, min(100, (int)$enrollment->progress_percent)) }}%;"></div>
                </div>
            </div>
        </div>
    </header>

    <main class="page-main">
        {{-- Sidebar: overview + navigation --}}
        <aside class="card sidebar">
            {{-- Training + author --}}
            <div class="sidebar-header">
                <div class="sidebar-cover">
                    @if($training->cover_image_path)
                        <img src="{{ asset('storage/'.$training->cover_image_path) }}" alt="">
                    @else
                        IMG
                    @endif
                </div>
                <div class="sidebar-header-main">
                    <div class="sidebar-title">
                        {{ $training->title }}
                    </div>
                    <div class="sidebar-meta">
                        @if($training->estimated_duration_minutes)
                            Durée estimée :
                            <strong>{{ $training->estimated_duration_minutes }} min</strong>
                        @else
                            Formation digitale
                        @endif
                    </div>

                    <div class="author-box">
                        <div class="author-avatar">
                            {{ mb_strtoupper(mb_substr(trim($authorName), 0, 1)) }}
                        </div>
                        <div class="author-text">
                            <div class="author-name">{{ $authorName }}</div>
                            <div>
                                <span style="opacity:.9;">Créateur de cette formation.</span>
                                @if($author && $author->city)
                                    <span style="opacity:.7;"> Basé à {{ $author->city }}.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tags --}}
            @if(is_array($training->tags) && count($training->tags))
                <div class="tag-list">
                    @foreach($training->tags as $tag)
                        <span class="tag-chip">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif

            {{-- Modules & content --}}
            <div>
                <div class="sidebar-section-title">
                    Contenu de la formation
                </div>

                <div class="module-list">
                    @foreach($modules as $mIndex => $module)
                        <div class="module-item"
                             data-module-index="{{ $mIndex }}"
                             onclick="selectModule({{ $mIndex }})">
                            <div class="module-title-row">
                                <div class="module-title-text">
                                    {{ $module->title ?: 'Module '.($mIndex + 1) }}
                                </div>
                                <div class="module-index-pill">
                                    M{{ $mIndex + 1 }}
                                </div>
                            </div>
                            @if($module->description)
                                <div class="module-desc">
                                    {{ \Illuminate\Support\Str::limit($module->description, 80) }}
                                </div>
                            @endif>

                            @if($module->sorted_blocks->isNotEmpty())
                                <div class="block-list">
                                    @foreach($module->sorted_blocks as $bIndex => $block)
                                        <span class="block-pill"
                                              data-module-index="{{ $mIndex }}"
                                              data-block-index="{{ $bIndex }}"
                                              onclick="event.stopPropagation(); selectBlock({{ $mIndex }}, {{ $bIndex }})">
                                            @if($block->type === 'text')
                                                📝
                                            @elseif($block->type === 'video_url')
                                                🎥
                                            @else
                                                📄
                                            @endif
                                            {{ $block->title ?: 'Contenu '.($bIndex + 1) }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="module-desc" style="font-style:italic;color:#9ca3af;">
                                    Aucun contenu pour l’instant.
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Mark as complete --}}
            <div class="sidebar-footer">
                <form action="{{ route('digital-trainings.access.complete', $enrollment->access_token) }}"
                      method="POST" style="margin-top:10px;">
                    @csrf
                    <button type="submit"
                            class="btn btn-primary"
                            style="width:100%;">
                        ✅ Marquer la formation comme terminée
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main content: block player --}}
        <section class="card content">
            <div class="content-meta-row">
                <div class="content-meta-left">
                    <div class="content-path" id="content-path">
                        {{-- ex: Module 1 · Leçon 2 --}}
                    </div>
                    <div class="content-header-title" id="block-title"></div>
                    <div class="content-subtitle" id="block-subtitle">
                        {{-- small helper text --}}
                    </div>
                </div>
                <div class="nav-side">
                    <span class="badge-pill">
                        <span>👣</span>
                        <span id="stepIndicator">0/0</span>
                    </span>
                </div>
            </div>

            @if(session('success'))
                <div class="alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="content-body">
                <div id="block-content" class="content-body-inner"></div>
            </div>

            <div class="nav-buttons">
                <button class="btn btn-outline" id="btnPrev" onclick="goPrev()">
                    ← Précédent
                </button>
                <button class="btn btn-outline" id="btnNext" onclick="goNext()" style="margin-left:auto;">
                    Suivant →
                </button>
            </div>
        </section>
    </main>
</div>

<script>
    const modules = @json($jsModules);

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
            const escaped = block.content ? block.content.replace(/\n/g, '<br>') : '';
            html = `<div>${escaped}</div>`;
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
                        <a href="${url}" target="_blank" style="color:{{ $brandGreen }};text-decoration:underline;">ouvrir la vidéo dans un nouvel onglet</a>.
                    </div>
                </div>`;
        } else if (block.type === 'pdf') {
            const filePath = block.file_path ? `{{ asset('storage') }}/` + block.file_path : null;
            if (filePath) {
                html = `
                    <div style="display:flex;flex-direction:column;gap:8px;height:100%;">
                        <div style="flex:1;min-height:420px;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
                            <embed src="${filePath}#toolbar=1&navpanes=0" type="application/pdf" style="width:100%;height:100%;">
                        </div>
                        <div style="font-size:12px;color:#6b7280;">
                            Si le document ne s’affiche pas, vous pouvez
                            <a href="${filePath}" target="_blank" style="color:{{ $brandGreen }};text-decoration:underline;">télécharger le PDF</a>.
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
        const flat   = flattenBlocks();
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

    // Init
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
</body>
</html>
