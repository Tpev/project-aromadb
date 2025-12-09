{{-- resources/views/digital-trainings/player.blade.php --}}
@php
    $brandGreen = '#647a0b';

    // Precompute a clean PHP array for JS to avoid nested closures inside @json
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
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background-color: #f3f4f6;
            color: #0f172a;
        }
        .page-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .page-header {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-header-title {
            font-size: 15px;
            font-weight: 600;
            color: {{ $brandGreen }};
        }
        .page-main {
            flex: 1;
            display: flex;
            padding: 16px;
            gap: 16px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }
        .sidebar {
            width: 280px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .content {
            flex: 1;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 500;
            background: #ecfdf3;
            color: #166534;
        }
        .module-item {
            border-radius: 10px;
            padding: 8px 10px;
            cursor: pointer;
            border: 1px solid transparent;
            font-size: 13px;
        }
        .module-item:hover {
            background: #f9fafb;
        }
        .module-item.active {
            border-color: {{ $brandGreen }};
            background: #f7fbe8;
        }
        .block-list {
            margin-top: 6px;
            padding-left: 8px;
        }
        .block-pill {
            font-size: 11px;
            padding: 3px 7px;
            border-radius: 999px;
            margin-right: 4px;
            margin-bottom: 4px;
            display: inline-flex;
            background: #f3f4f6;
        }
        .block-pill.active {
            background: {{ $brandGreen }};
            color: white;
        }
        .content-header-title {
            font-size: 18px;
            font-weight: 600;
        }
        .content-subtitle {
            font-size: 13px;
            color: #6b7280;
        }
        .content-body {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            padding: 14px;
            min-height: 300px;
        }
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            gap: 10px;
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
            gap: 6px;
        }
        .btn-primary {
            background: {{ $brandGreen }};
            color: white;
            border-color: {{ $brandGreen }};
        }
        .btn-primary:hover {
            background: #506108;
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: default;
        }
        .tag-chip {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            background: #f3f4f6;
            font-size: 10px;
            color: #4b5563;
            margin-right: 4px;
            margin-bottom: 4px;
        }
        .alert-success {
            border-radius: 10px;
            border: 1px solid #bbf7d0;
            background: #ecfdf3;
            color: #166534;
            font-size: 12px;
            padding: 8px 10px;
            margin: 10px 0;
        }

        @media (max-width: 900px) {
            .page-main {
                flex-direction: column;
                padding: 10px;
            }
            .sidebar {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="page-shell">
    <header class="page-header">
        <div>
            <div class="page-header-title">
                {{ $training->title }}
            </div>
            <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">
                {{ config('app.name') }} – Accès à votre formation
            </div>
        </div>
        <div style="font-size: 11px; text-align: right; color: #4b5563;">
            @if($enrollment->participant_name)
                {{ $enrollment->participant_name }}<br>
            @endif
            {{ $enrollment->participant_email }}
        </div>
    </header>

    <main class="page-main">
        {{-- Sidebar: training info and modules --}}
        <aside class="card sidebar">
            <div style="display:flex; gap:10px;">
                @if($training->cover_image_path)
                    <img src="{{ asset('storage/'.$training->cover_image_path) }}"
                         alt=""
                         style="width:64px;height:64px;border-radius:12px;object-fit:cover;">
                @else
                    <div style="width:64px;height:64px;border-radius:12px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:11px;color:#9ca3af;">
                        IMG
                    </div>
                @endif
                <div>
                    <div style="font-size:13px;font-weight:600;color:#111827;">
                        {{ $training->title }}
                    </div>
                    @if($training->estimated_duration_minutes)
                        <div style="font-size:11px;color:#6b7280;margin-top:2px;">
                            Durée estimée : <strong>{{ $training->estimated_duration_minutes }} min</strong>
                        </div>
                    @endif
                    <div style="margin-top:6px;">
                        <span class="badge">
                            @if($enrollment->completed_at)
                                ✅ Terminé
                            @else
                                {{ $enrollment->progress_percent }}% complété
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            @if(is_array($training->tags) && count($training->tags))
                <div style="margin-top:8px;">
                    @foreach($training->tags as $tag)
                        <span class="tag-chip">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif

            <div style="margin-top:12px;">
                <div style="font-size:12px;font-weight:600;margin-bottom:6px;">
                    Contenu de la formation
                </div>

                @foreach($modules as $mIndex => $module)
                    <div class="module-item"
                         data-module-index="{{ $mIndex }}"
                         onclick="selectModule({{ $mIndex }})">
                        <div style="font-size:12px;font-weight:600;color:#111827;">
                            {{ $module->title ?: 'Module '.($mIndex+1) }}
                        </div>
                        @if($module->description)
                            <div style="font-size:11px;color:#6b7280;margin-top:2px;">
                                {{ \Illuminate\Support\Str::limit($module->description, 80) }}
                            </div>
                        @endif

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
                                        {{ $block->title ?: 'Contenu '.($bIndex+1) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div style="font-size:11px;color:#9ca3af;margin-top:4px;">
                                Aucun contenu pour l’instant.
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <form action="{{ route('digital-trainings.access.complete', $enrollment->access_token) }}"
                  method="POST"
                  style="margin-top:auto;margin-top:16px;">
                @csrf
                <button type="submit"
                        class="btn btn-primary"
                        style="width:100%;justify-content:center;">
                    ✅ Marquer la formation comme terminée
                </button>
            </form>
        </aside>

        {{-- Main content: block player --}}
        <section class="card content">
            @if(session('success'))
                <div class="alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div style="font-size:11px;color:#6b7280;">
                <span>Vous suivez cette formation en accès direct via un lien personnel.</span>
            </div>

            <div id="current-meta">
                <div class="content-header-title" id="block-title"></div>
                <div class="content-subtitle" id="block-subtitle"></div>
            </div>

            <div class="content-body" id="block-content"></div>

            <div class="nav-buttons">
                <button class="btn" id="btnPrev" onclick="goPrev()">
                    ← Précédent
                </button>
                <button class="btn" id="btnNext" onclick="goNext()" style="margin-left:auto;">
                    Suivant →
                </button>
            </div>
        </section>
    </main>
</div>

<script>
    // Precomputed PHP array -> JSON, no nested closures
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
        const contentWrap = document.getElementById('block-content');

        const module = modules[currentModuleIndex];
        if (!module || !module.blocks || !module.blocks.length) {
            metaTitle.textContent = 'Aucun contenu';
            metaSub.textContent   = '';
            contentWrap.innerHTML = '<p style="font-size:13px;color:#6b7280;">Ce module ne contient aucun contenu pour le moment.</p>';
            updateNavButtons();
            return;
        }

        const block = module.blocks[currentBlockIndex];

        const moduleLabel = module.title ? module.title : ('Module ' + (currentModuleIndex + 1));
        const blockLabel  = block.title ? block.title : ('Contenu ' + (currentBlockIndex + 1));

        metaTitle.textContent = blockLabel;
        metaSub.textContent   = moduleLabel;

        let html = '';

        if (block.type === 'text') {
            const escaped = block.content ? block.content.replace(/\n/g, '<br>') : '';
            html = `<div style="font-size:14px;line-height:1.6;color:#111827;">${escaped}</div>`;
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
                        <div style="flex:1;min-height:400px;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
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
        const flat = flattenBlocks();
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
