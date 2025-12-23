{{-- resources/views/tools/konva/partials/left-sidebar.blade.php --}}
<aside class="space-y-4">

    {{-- CONTENT --}}
    <div class="toolbar-card glass-card">
        <div class="mb-2 flex items-center justify-between gap-2">
            <span class="toolbar-title">Contenu</span>
            <span id="formatBadge"
                  class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                Choisir un format
            </span>
        </div>

        <div class="flex flex-wrap gap-2 mb-2">
            <input id="imageUpload" type="file" accept="image/*" class="hidden">

            <label for="imageUpload" class="pill-btn pill-btn-main cursor-pointer">
                <span class="pill-icon">🖼️</span>
                Importer une image
            </label>

            <button id="btnAddText" type="button" class="pill-btn pill-btn-ghost">
                <span class="pill-icon">✏️</span>
                Ajouter du texte
            </button>
        </div>

{{-- Shapes drawer --}}
<div class="mt-2">
    <button id="btnToggleShapesDrawer" type="button" class="pill-btn pill-btn-ghost w-full justify-between">
        <span class="flex items-center gap-2">
            🔷 Ajouter une forme
        </span>
        <span id="shapesDrawerChevron">▾</span>
    </button>

    <div id="shapesDrawer" class="mt-2 hidden">
        <div class="grid grid-cols-4 gap-2">
            {{-- 20 quick shapes --}}
            <button type="button" class="shape-btn" data-shape="rect" title="Rectangle">▭</button>
            <button type="button" class="shape-btn" data-shape="roundRect" title="Rectangle arrondi">▢</button>
            <button type="button" class="shape-btn" data-shape="circle" title="Cercle">●</button>
            <button type="button" class="shape-btn" data-shape="ellipse" title="Ellipse">⬭</button>

            <button type="button" class="shape-btn" data-shape="triangle" title="Triangle">▲</button>
            <button type="button" class="shape-btn" data-shape="rightTriangle" title="Triangle droit">◢</button>
            <button type="button" class="shape-btn" data-shape="diamond" title="Losange">◆</button>
            <button type="button" class="shape-btn" data-shape="parallelogram" title="Parallélogramme">▱</button>

            <button type="button" class="shape-btn" data-shape="trapezoid" title="Trapèze">⏢</button>
            <button type="button" class="shape-btn" data-shape="pentagon" title="Pentagone">⬟</button>
            <button type="button" class="shape-btn" data-shape="hexagon" title="Hexagone">⬢</button>
            <button type="button" class="shape-btn" data-shape="octagon" title="Octogone">🛑</button>

            <button type="button" class="shape-btn" data-shape="star5" title="Étoile 5">★</button>
            <button type="button" class="shape-btn" data-shape="star6" title="Étoile 6">✶</button>
            <button type="button" class="shape-btn" data-shape="star8" title="Étoile 8">✷</button>
            <button type="button" class="shape-btn" data-shape="burst" title="Burst / Explosion">✹</button>

            <button type="button" class="shape-btn" data-shape="arrowRight" title="Flèche droite">➜</button>
            <button type="button" class="shape-btn" data-shape="arrowLeft" title="Flèche gauche">⬅</button>
            <button type="button" class="shape-btn" data-shape="arrowUp" title="Flèche haut">⬆</button>
            <button type="button" class="shape-btn" data-shape="arrowDown" title="Flèche bas">⬇</button>
        </div>

        <p class="mt-2 text-[11px] text-slate-500">
            Astuce : cliquez une forme pour l’ajouter, puis modifiez couleur/contour à droite.
        </p>
    </div>
</div>

    </div>

    {{-- LAYOUT / CANVAS --}}
    <div class="toolbar-card glass-card">
        <div class="mb-2 flex items-center justify-between">
            <span class="toolbar-title">Mise en page</span>
            <span class="badge-soft">🧩 Canvas</span>
        </div>

        {{-- Zoom (display only) --}}
        <div class="mb-3">
            <div class="small-label">Zoom</div>
            <div class="range-row">
                <input id="zoomSlider" type="range" min="40" max="140" value="100">
                <div id="zoomValue" class="range-value">100%</div>
            </div>
            <p class="mt-1 text-[11px] text-slate-500">
                Le zoom est un confort d’affichage. L’export reste en pleine résolution.
            </p>
        </div>

        {{-- Background --}}
        <div class="mb-3 border-t border-dashed border-slate-200 pt-3">
            <div class="small-label mb-1">Fond</div>

            <div class="flex items-center gap-2">
                <input id="bgColorPicker" type="color"
                       class="h-8 w-12 rounded-lg border border-slate-200 bg-white"
                       value="#f9fafb">
                <button id="btnResetBg" type="button" class="pill-btn pill-btn-ghost px-3 py-1 text-[11px]">
                    Réinitialiser
                </button>
            </div>

            <div class="mt-2 grid grid-cols-6 gap-1.5">
                <button type="button" class="h-6 rounded-md border border-slate-200 bg-white" data-bg="#ffffff"></button>
                <button type="button" class="h-6 rounded-md border border-slate-200" data-bg="#f9fafb" style="background:#f9fafb"></button>
                <button type="button" class="h-6 rounded-md border border-slate-200" data-bg="#f1f5f9" style="background:#f1f5f9"></button>
                <button type="button" class="h-6 rounded-md border border-slate-200" data-bg="#ecfccb" style="background:#ecfccb"></button>
                <button type="button" class="h-6 rounded-md border border-slate-200" data-bg="#dcfce7" style="background:#dcfce7"></button>
                <button type="button" class="h-6 rounded-md border border-slate-200" data-bg="#fef3c7" style="background:#fef3c7"></button>
            </div>
        </div>

        {{-- Grid + quick actions --}}
        <div class="border-t border-dashed border-slate-200 pt-3">
            <div class="flex items-center justify-between gap-2 mb-2">
                <div class="small-label" style="margin-bottom:0;">Grille</div>
                <label class="flex items-center gap-2 text-[11px] text-slate-600">
                    <input id="toggleGrid" type="checkbox" class="rounded border-slate-300">
                    Afficher
                </label>
            </div>

            <div class="flex flex-wrap gap-2">
                <button id="btnCenterSelection" type="button" class="pill-btn pill-btn-ghost">🎯 Centrer</button>
                <button id="btnDeleteSelection" type="button" class="pill-btn pill-btn-ghost">🗑️ Supprimer</button>
            </div>
        </div>
    </div>

    {{-- EVENT SELECTOR (optional) --}}
    <div class="toolbar-card glass-card">
        <div class="mb-2 flex items-center justify-between">
            <span class="toolbar-title">Événement</span>
            <span class="badge-soft">📅</span>
        </div>

        <select id="eventSelector" class="small-select w-full">
            <option value="">— Aucun —</option>
            @foreach(($events ?? collect()) as $event)
                <option value="{{ $event->id }}">{{ $event->title ?? ('Événement #' . $event->id) }}</option>
            @endforeach
        </select>

        <p class="mt-2 text-[11px] text-slate-500">
            (Optionnel) Tu pourras utiliser l’événement pour pré-remplir des textes dans un template.
        </p>
    </div>

    {{-- TEMPLATES --}}
    <div class="toolbar-card glass-card">
        <div class="mb-2 flex items-center justify-between gap-2">
            <span class="toolbar-title">Templates</span>
            <span class="badge-soft">🎨</span>
        </div>

        <div id="templatesGrid" class="grid grid-cols-2 gap-1.5 text-[11px]">
            @foreach(config('konva.templates', []) as $tpl)
                <button
                    type="button"
                    class="pill-btn pill-btn-ghost w-full justify-center js-template-btn opacity-40 pointer-events-none"
                    data-template="{{ $tpl['id'] }}"
                    data-format="{{ $tpl['format_id'] ?? '' }}"
                    title="{{ $tpl['hint'] ?? '' }}"
                >
                    {{ $tpl['label'] }}
                </button>
            @endforeach
        </div>

        <p class="mt-2 text-[11px] leading-snug text-slate-500">
            Les templates s’activent après sélection du format.
        </p>
    </div>

    {{-- HISTORY --}}
    <div class="toolbar-card glass-card">
        <div class="mb-1 flex items-center justify-between">
            <span class="toolbar-title">Historique</span>
            <button id="btnUndo" type="button" class="pill-btn pill-btn-ghost px-2 py-1 text-[10px]">⤺ Annuler</button>
        </div>
        <p class="text-[11px] leading-snug text-slate-500">
            Espace de test : expérimentez en toute liberté, rien n’est enregistré dans AromaMade.
        </p>
    </div>

</aside>
