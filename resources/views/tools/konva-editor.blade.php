{{-- resources/views/tools/konva-editor.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl" style="color:#647a0b;">
                    Studio Social Media (beta)
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Créez un visuel carré pour vos réseaux sociaux – test Konva.js (rien n’est enregistré).
                </p>
            </div>
        </div>
    </x-slot>

    <style>
        :root {
            --brand: #647a0b;
        }

        .editor-shell {
            min-height: calc(100vh - 4.5rem);
            background: #f3f4f6;
            background-image:
                radial-gradient(circle at 0 0, rgba(100, 122, 11, 0.08), transparent 55%),
                radial-gradient(circle at 100% 100%, rgba(15, 23, 42, 0.14), transparent 50%);
        }

        .glass-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.96);
        }

        .pill-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.35rem 0.8rem;
            transition: all 0.15s ease;
            border: none;
            cursor: pointer;
        }

        .pill-btn-main {
            background: #647a0b;
            color: #f9fafb;
            box-shadow: 0 10px 25px rgba(100, 122, 11, 0.35);
        }

        .pill-btn-main:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
        }

        .pill-btn-ghost {
            background: rgba(148, 163, 184, 0.1);
            color: #0f172a;
        }

        .pill-btn-ghost:hover {
            background: rgba(148, 163, 184, 0.25);
        }

        .pill-icon {
            font-size: 13px;
        }

        .toolbar-title {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
        }

        .toolbar-card {
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            padding: 0.75rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        #konva-container {
            cursor: default;
        }

        .editor-main-grid {
            display: grid;
            grid-template-columns: minmax(0, 260px) minmax(0, 1.1fr) minmax(0, 260px);
            gap: 1.5rem;
            align-items: flex-start;
        }

        @media (max-width: 1024px) {
            .editor-main-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        .layer-list {
            max-height: 260px;
            overflow-y: auto;
            padding-right: 0.3rem;
        }

        .layer-item-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            border-radius: 0.75rem;
            padding: 0.4rem 0.55rem;
            font-size: 0.75rem;
            border: none;
            background: transparent;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .layer-item-btn:hover {
            background: rgba(226, 232, 240, 0.75);
        }

        .layer-item-btn.is-active {
            background: rgba(100, 122, 11, 0.12);
            box-shadow: 0 0 0 1px rgba(100, 122, 11, 0.4);
        }

        .layer-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            padding: 0.15rem 0.5rem;
            font-size: 0.65rem;
            font-weight: 500;
            background: #f3f4f6;
            color: #4b5563;
        }

        .layer-chip span {
            font-size: 0.75rem;
        }

        .small-label {
            font-size: 0.7rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0.1rem;
        }

        .small-input,
        .small-select {
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            padding: 0.2rem 0.45rem;
            font-size: 0.75rem;
            color: #111827;
            background: white;
        }

        .small-input:focus,
        .small-select:focus {
            outline: none;
            border-color: #647a0b;
            box-shadow: 0 0 0 1px rgba(100, 122, 11, 0.25);
        }

        .range-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .range-row input[type="range"] {
            flex: 1;
        }

        .range-value {
            font-size: 0.7rem;
            color: #4b5563;
            min-width: 2.2rem;
            text-align: right;
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            border-radius: 999px;
            padding: 0.15rem 0.45rem;
            font-size: 0.65rem;
            font-weight: 500;
            background: #ecfdf3;
            color: #166534;
        }

        .z-order-btn {
            padding: 0.2rem 0.4rem;
            border-radius: 999px;
            border: none;
            font-size: 0.75rem;
            background: rgba(148,163,184,0.15);
            cursor: pointer;
            transition: all 0.15s;
        }

        .z-order-btn:hover {
            background: rgba(148,163,184,0.4);
        }
    </style>

    <div class="editor-shell px-4 py-6 md:px-8">
        <div class="mx-auto max-w-6xl">
            {{-- Top CTA / status --}}
            <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">
                        AromaMade • Outil créatif
                    </p>
                    <h1 class="text-xl font-semibold text-slate-900 md:text-2xl">
                        Créez un post carré pour Instagram, Facebook & co.
                    </h1>
                    <p class="mt-1 text-xs text-slate-500">
                        Importez une image, ajoutez du texte, gérez vos calques et exportez en un clic.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button id="btnExport"
                            type="button"
                            class="pill-btn pill-btn-main">
                        <span class="pill-icon">⬇️</span>
                        Exporter en PNG
                    </button>
                    <button id="btnClearCanvas"
                            type="button"
                            class="pill-btn pill-btn-ghost">
                        <span class="pill-icon">🧹</span>
                        Réinitialiser
                    </button>
                </div>
            </div>

            <div class="editor-main-grid">
                {{-- LEFT SIDEBAR – Tools --}}
                <aside class="space-y-4">

                    {{-- Section: Templates --}}
                    <div class="toolbar-card glass-card">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <span class="toolbar-title">Templates rapides</span>
                            <span class="badge-soft">Beta</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mb-2">
                            Charge un canevas pré-structuré pour aller plus vite.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button id="btnTemplateQuote" type="button" class="pill-btn pill-btn-ghost">
                                💬 Citation
                            </button>
                            <button id="btnTemplatePromo" type="button" class="pill-btn pill-btn-ghost">
                                ⭐ Promo
                            </button>
                            <button id="btnTemplateEvent" type="button" class="pill-btn pill-btn-ghost">
                                📅 Atelier
                            </button>
                        </div>
                    </div>

                    {{-- Section: Contenu --}}
                    <div class="toolbar-card glass-card">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <span class="toolbar-title">Contenu</span>
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                1080 × 1080
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-2 mb-2">
                            {{-- Hidden file input --}}
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

                        <div class="flex flex-wrap gap-2">
                            <button id="btnAddRect" type="button" class="pill-btn pill-btn-ghost">
                                ◼️ Forme
                            </button>
                            <button id="btnAddCircle" type="button" class="pill-btn pill-btn-ghost">
                                ⚪ Cercle
                            </button>
                        </div>
                    </div>

                    {{-- Section: Mise en forme globale --}}
                    <div class="toolbar-card glass-card space-y-3">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="toolbar-title">Mise en page</span>
                            <button id="btnCenterSelection" type="button"
                                    class="pill-btn pill-btn-ghost px-2 py-1 text-[11px]">
                                Aligner au centre
                            </button>
                        </div>

                        <div class="space-y-2 text-[11px] text-slate-600">
                            <div class="flex items-center justify-between gap-2">
                                <span>Zoom</span>
                                <span id="zoomValue" class="font-medium text-slate-800">100%</span>
                            </div>
                            <input id="zoomSlider"
                                   type="range"
                                   min="50"
                                   max="200"
                                   value="100"
                                   class="w-full accent-[#647a0b]">
                        </div>

                        <div class="flex flex-col gap-2 text-[11px] text-slate-600">
                            <div class="flex items-center justify-between gap-2">
                                <span>Couleur de fond</span>
                                <div class="flex items-center gap-2">
                                    <input id="bgColorPicker"
                                           type="color"
                                           value="#f9fafb"
                                           class="h-7 w-12 cursor-pointer rounded-md border border-slate-200 bg-white p-0">
                                    <button id="btnResetBg" type="button"
                                            class="pill-btn-ghost pill-btn px-2 py-1 text-[10px]">
                                        Réinitialiser
                                    </button>
                                </div>
                            </div>

                            {{-- Presets --}}
                            <div class="flex items-center gap-2">
                                <span class="small-label" style="margin-bottom:0;">Préréglages</span>
                                <button type="button"
                                        class="h-4 w-4 rounded-full border border-slate-200"
                                        style="background:#647a0b"
                                        data-bg="#647a0b"></button>
                                <button type="button"
                                        class="h-4 w-4 rounded-full border border-slate-200"
                                        style="background:#e5f0c8"
                                        data-bg="#e5f0c8"></button>
                                <button type="button"
                                        class="h-4 w-4 rounded-full border border-slate-200"
                                        style="background:#fef3c7"
                                        data-bg="#fef3c7"></button>
                                <button type="button"
                                        class="h-4 w-4 rounded-full border border-slate-200"
                                        style="background:#f3f4f6"
                                        data-bg="#f3f4f6"></button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 text-[11px] text-slate-600">
                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                <input id="toggleGrid" type="checkbox"
                                       class="h-3.5 w-3.5 rounded border-slate-300 text-[#647a0b] focus:ring-[#647a0b]">
                                <span>Afficher la grille</span>
                            </label>

                            <button id="btnDeleteSelection" type="button"
                                    class="pill-btn-ghost pill-btn px-2 py-1 text-[10px]">
                                Supprimer l’élément
                            </button>
                        </div>
                    </div>

                    {{-- Section: Historique / Infos --}}
                    <div class="toolbar-card glass-card">
                        <div class="mb-1 flex items-center justify-between">
                            <span class="toolbar-title">Historique</span>
                            <button id="btnUndo"
                                    type="button"
                                    class="pill-btn pill-btn-ghost px-2 py-1 text-[10px]">
                                ⤺ Annuler
                            </button>
                        </div>
                        <p class="text-[11px] leading-snug text-slate-500">
                            Espace de test : expérimentez en toute liberté,
                            rien n’est enregistré dans AromaMade.
                        </p>
                    </div>
                </aside>

                {{-- CENTER – Canvas --}}
                <section
                    class="glass-card relative flex items-center justify-center rounded-3xl border border-slate-100 p-3 shadow-lg">
                    <div class="absolute left-3 top-3 flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-[10px] font-medium text-slate-500 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Mode maquette – non sauvegardé
                    </div>

                    <div
                        class="relative aspect-square w-full max-w-[720px] overflow-hidden rounded-3xl border border-dashed border-slate-200 bg-slate-50"
                        id="konva-wrapper">
                        <div id="konva-container" class="h-full w-full"></div>
                    </div>
                </section>

                {{-- RIGHT SIDEBAR – Layers & Inspector --}}
                <aside class="space-y-4">
                    {{-- Layers list --}}
                    <div class="toolbar-card glass-card">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="toolbar-title">Calques</span>
                            <span class="badge-soft">
                                <span>☰</span> Z-order
                            </span>
                        </div>

                        <div id="layersList" class="layer-list space-y-1 text-[12px] text-slate-700">
                            {{-- Filled by JS --}}
                        </div>
                    </div>

                    {{-- Selection inspector --}}
                    <div class="toolbar-card glass-card">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="toolbar-title">Propriétés</span>
                            <span id="selectionTypeBadge"
                                  class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                Aucun élément
                            </span>
                        </div>

                        <p id="noSelectionHint" class="text-[11px] text-slate-500">
                            Sélectionnez un élément sur le canvas ou dans la liste des calques
                            pour afficher ses propriétés.
                        </p>

                        <div id="selectionPanel" class="space-y-3 hidden">
                            {{-- Name --}}
                            <div>
                                <div class="small-label">Nom du calque</div>
                                <input id="inputLayerName" type="text"
                                       class="small-input"
                                       placeholder="Titre principal, Image 1, etc.">
                            </div>

                            {{-- Common opacity --}}
                            <div>
                                <div class="small-label">Opacité</div>
                                <div class="range-row">
                                    <input id="inputOpacity" type="range" min="20" max="100" value="100">
                                    <div id="opacityValue" class="range-value">100%</div>
                                </div>
                            </div>

                            {{-- Shape controls --}}
                            <div id="shapeControls" class="space-y-2 border-t border-dashed border-slate-200 pt-2 hidden">
                                <div class="small-label">Couleurs de la forme</div>
                                <div class="flex items-center gap-2">
                                    <div class="flex flex-col flex-1 gap-1">
                                        <span class="small-label">Fond</span>
                                        <input id="shapeFill" type="color"
                                               class="h-7 w-full rounded-md border border-slate-200 bg-white">
                                    </div>
                                    <div class="flex flex-col flex-1 gap-1">
                                        <span class="small-label">Contour</span>
                                        <input id="shapeStroke" type="color"
                                               class="h-7 w-full rounded-md border border-slate-200 bg-white">
                                    </div>
                                </div>

                                <div>
                                    <div class="small-label">Épaisseur contour</div>
                                    <div class="range-row">
                                        <input id="shapeStrokeWidth" type="range" min="0" max="10" value="1">
                                        <div id="strokeWidthValue" class="range-value">1 px</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Text controls --}}
                            <div id="textControls" class="space-y-2 border-t border-dashed border-slate-200 pt-2 hidden">
                                <div class="small-label">Police & style</div>
                                <div class="flex items-center gap-2">
                                    <select id="textFontFamily" class="small-select">
                                        <option value="system">Système</option>
                                        <option value="serif">Playfair (titre)</option>
                                        <option value="sans">Raleway (léger)</option>
                                    </select>
                                    <div class="flex items-center gap-1">
                                        <span class="small-label" style="margin-bottom:0;">Taille</span>
                                        <input id="textFontSize" type="number" min="10" max="80" value="26"
                                               class="small-input" style="width:3.5rem;">
                                    </div>
                                    <button id="btnToggleBold" type="button"
                                            class="z-order-btn"
                                            title="Gras / Normal">
                                        B
                                    </button>
                                </div>

                                <div class="flex items-center gap-2 mt-1">
                                    <span class="small-label" style="margin-bottom:0;">Alignement</span>
                                    <button type="button" class="z-order-btn text-[10px]" id="btnAlignLeft">⯇</button>
                                    <button type="button" class="z-order-btn text-[10px]" id="btnAlignCenter">⬌</button>
                                    <button type="button" class="z-order-btn text-[10px]" id="btnAlignRight">⯈</button>
                                </div>

                                <div class="flex items-center gap-2 mt-1">
                                    <div class="flex flex-col flex-1 gap-1">
                                        <span class="small-label">Couleur texte</span>
                                        <input id="textColor" type="color"
                                               class="h-7 w-full rounded-md border border-slate-200 bg-white">
                                    </div>
                                </div>
                            </div>

                            {{-- Image controls --}}
                            <div id="imageControls" class="space-y-2 border-t border-dashed border-slate-200 pt-2 hidden">
                                <div class="small-label">Image</div>
                                <div class="range-row">
                                    <span class="small-label" style="margin-bottom:0;">Lumière</span>
                                    <input id="imgBrightness" type="range" min="-0.5" max="0.5" step="0.05" value="0">
                                </div>
                                <div class="range-row">
                                    <span class="small-label" style="margin-bottom:0;">Contraste</span>
                                    <input id="imgContrast" type="range" min="-0.5" max="0.5" step="0.05" value="0">
                                </div>
                            </div>

                            {{-- Z-order & duplicate controls --}}
                            <div class="border-t border-dashed border-slate-200 pt-2">
                                <div class="small-label mb-1">Ordre des calques & actions</div>
                                <div class="flex flex-wrap gap-1.5 mb-1.5">
                                    <button id="btnZTop" type="button" class="z-order-btn" title="Amener tout devant">
                                        ⬆⬆ Avant-plan
                                    </button>
                                    <button id="btnZUp" type="button" class="z-order-btn" title="Monter d’un niveau">
                                        ⬆ Monter
                                    </button>
                                    <button id="btnZDown" type="button" class="z-order-btn" title="Descendre d’un niveau">
                                        ⬇ Descendre
                                    </button>
                                    <button id="btnZBottom" type="button" class="z-order-btn" title="Mettre tout derrière">
                                        ⬇⬇ Arrière-plan
                                    </button>
                                </div>
                                <button id="btnDuplicate" type="button" class="z-order-btn" title="Dupliquer l’élément">
                                    📑 Dupliquer
                                </button>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    {{-- Konva + logic --}}
    <script src="https://unpkg.com/konva@9/konva.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('konva-container');
            const wrapper   = document.getElementById('konva-wrapper');

            const imageUpload         = document.getElementById('imageUpload');
            const btnAddText          = document.getElementById('btnAddText');
            const btnAddRect          = document.getElementById('btnAddRect');
            const btnAddCircle        = document.getElementById('btnAddCircle');
            const btnExport           = document.getElementById('btnExport');
            const btnClearCanvas      = document.getElementById('btnClearCanvas');
            const btnCenterSelection  = document.getElementById('btnCenterSelection');
            const btnDeleteSelection  = document.getElementById('btnDeleteSelection');
            const zoomSlider          = document.getElementById('zoomSlider');
            const zoomValue           = document.getElementById('zoomValue');
            const bgColorPicker       = document.getElementById('bgColorPicker');
            const btnResetBg          = document.getElementById('btnResetBg');
            const toggleGrid          = document.getElementById('toggleGrid');
            const btnUndo             = document.getElementById('btnUndo');

            const btnTemplateQuote    = document.getElementById('btnTemplateQuote');
            const btnTemplatePromo    = document.getElementById('btnTemplatePromo');
            const btnTemplateEvent    = document.getElementById('btnTemplateEvent');

            const layersList          = document.getElementById('layersList');
            const selectionPanel      = document.getElementById('selectionPanel');
            const noSelectionHint     = document.getElementById('noSelectionHint');
            const selectionTypeBadge  = document.getElementById('selectionTypeBadge');

            const inputLayerName      = document.getElementById('inputLayerName');
            const inputOpacity        = document.getElementById('inputOpacity');
            const opacityValue        = document.getElementById('opacityValue');

            const shapeControls       = document.getElementById('shapeControls');
            const shapeFillInput      = document.getElementById('shapeFill');
            const shapeStrokeInput    = document.getElementById('shapeStroke');
            const shapeStrokeWidth    = document.getElementById('shapeStrokeWidth');
            const strokeWidthValue    = document.getElementById('strokeWidthValue');

            const textControls        = document.getElementById('textControls');
            const textFontFamily      = document.getElementById('textFontFamily');
            const textFontSize        = document.getElementById('textFontSize');
            const btnToggleBold       = document.getElementById('btnToggleBold');
            const textColor           = document.getElementById('textColor');
            const btnAlignLeft        = document.getElementById('btnAlignLeft');
            const btnAlignCenter      = document.getElementById('btnAlignCenter');
            const btnAlignRight       = document.getElementById('btnAlignRight');

            const imageControls       = document.getElementById('imageControls');
            const imgBrightness       = document.getElementById('imgBrightness');
            const imgContrast         = document.getElementById('imgContrast');

            const btnZTop             = document.getElementById('btnZTop');
            const btnZUp              = document.getElementById('btnZUp');
            const btnZDown            = document.getElementById('btnZDown');
            const btnZBottom          = document.getElementById('btnZBottom');
            const btnDuplicate        = document.getElementById('btnDuplicate');

            // === Konva setup ===
            const size = wrapper.clientWidth; // square
            const stage = new Konva.Stage({
                container: container,
                width: size,
                height: size
            });

            const backgroundLayer = new Konva.Layer({ name: 'backgroundLayer' });
            const gridLayer       = new Konva.Layer({ name: 'gridLayer' });
            const mainLayer       = new Konva.Layer({ name: 'mainLayer' });

            stage.add(backgroundLayer);
            stage.add(gridLayer);
            stage.add(mainLayer);

            // Background rect
            const bgRect = new Konva.Rect({
                x: 0,
                y: 0,
                width: stage.width(),
                height: stage.height(),
                fill: '#f9fafb',
                name: 'bgRect'
            });
            backgroundLayer.add(bgRect);
            backgroundLayer.draw();

            // Transformer for selection
            const transformer = new Konva.Transformer({
                name: 'mainTransformer',
                rotateEnabled: true,
                enabledAnchors: [
                    'top-left', 'top-right',
                    'bottom-left', 'bottom-right'
                ],
                boundBoxFunc: function (oldBox, newBox) {
                    if (newBox.width < 30 || newBox.height < 30) {
                        return oldBox;
                    }
                    return newBox;
                }
            });
            mainLayer.add(transformer);

            let selectedNode = null;
            let history = [];

            // meta storage: id -> { id, type, name }
            let elementCounter = 0;
            const elementMeta = {};

            function newElementId() {
                elementCounter++;
                return 'elem-' + elementCounter;
            }

            function saveHistory() {
                const json = stage.toJSON();
                history.push(json);
                if (history.length > 20) {
                    history.shift();
                }
            }

            function clearSelection() {
                selectedNode = null;
                transformer.nodes([]);
                mainLayer.draw();
                updateInspector();
                refreshLayersList();
            }

            function setSelection(node) {
                selectedNode = node;
                transformer.nodes([node]);
                transformer.moveToTop();
                mainLayer.draw();
                updateInspector();
                refreshLayersList();
            }

            function registerElement(node, type, name) {
                const id = newElementId();
                const finalName = name || type;

                node.setAttr('amId', id);
                node.setAttr('amType', type);
                node.setAttr('amName', finalName);

                elementMeta[id] = {
                    id: id,
                    type: type,
                    name: finalName
                };
                refreshLayersList();
                saveHistory();
            }

            function removeElementMetaForNode(node) {
                const id = node.getAttr('amId');
                if (id && elementMeta[id]) {
                    delete elementMeta[id];
                }
            }

            // === Helpers ===
            function clearCanvasKeepBg() {
                mainLayer.destroyChildren();
                mainLayer.add(transformer);
                selectedNode = null;
                Object.keys(elementMeta).forEach(k => delete elementMeta[k]);
                updateInspector();
                refreshLayersList();
                mainLayer.draw();
            }

            function withTextNode(callback) {
                if (!selectedNode) return;
                const textNode = selectedNode.findOne('Text') || selectedNode;
                callback(textNode);
                const rect = selectedNode.findOne('Rect');
                if (rect && textNode) {
                    rect.width(textNode.width() + 32);
                    rect.height(textNode.height() + 16);
                }
                mainLayer.batchDraw();
                saveHistory();
            }

            // === Grid ===
            function drawGrid() {
                gridLayer.destroyChildren();
                const spacing = stage.width() / 10; // 10x10 grid
                const color = 'rgba(148, 163, 184, 0.4)';

                for (let i = 1; i < 10; i++) {
                    // vertical
                    gridLayer.add(new Konva.Line({
                        points: [i * spacing, 0, i * spacing, stage.height()],
                        stroke: color,
                        strokeWidth: 0.5
                    }));
                    // horizontal
                    gridLayer.add(new Konva.Line({
                        points: [0, i * spacing, stage.width(), i * spacing],
                        stroke: color,
                        strokeWidth: 0.5
                    }));
                }

                gridLayer.draw();
            }

            function toggleGridVisibility() {
                if (toggleGrid.checked) {
                    drawGrid();
                    gridLayer.visible(true);
                } else {
                    gridLayer.visible(false);
                }
                gridLayer.draw();
            }

            toggleGrid.addEventListener('change', () => {
                toggleGridVisibility();
                saveHistory();
            });

            // === Background color + presets ===
            bgColorPicker.addEventListener('input', (e) => {
                bgRect.fill(e.target.value);
                backgroundLayer.draw();
            });

            bgColorPicker.addEventListener('change', () => {
                saveHistory();
            });

            btnResetBg.addEventListener('click', () => {
                bgRect.fill('#f9fafb');
                backgroundLayer.draw();
                bgColorPicker.value = '#f9fafb';
                saveHistory();
            });

            document.querySelectorAll('[data-bg]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const color = btn.getAttribute('data-bg');
                    bgRect.fill(color);
                    backgroundLayer.draw();
                    bgColorPicker.value = color;
                    saveHistory();
                });
            });

            // === Zoom ===
            function applyZoom(value) {
                const scale = value / 100;
                zoomValue.textContent = value + '%';
                stage.scale({ x: scale, y: scale });

                // Keep center in view
                stage.position({
                    x: (stage.width() - stage.width() * scale) / 2,
                    y: (stage.height() - stage.height() * scale) / 2
                });

                stage.batchDraw();
            }

            zoomSlider.addEventListener('input', (e) => {
                applyZoom(parseInt(e.target.value, 10));
            });

            zoomSlider.addEventListener('change', () => {
                saveHistory();
            });

            // === Resize on window resize ===
            window.addEventListener('resize', () => {
                const newSize = wrapper.clientWidth;
                const scale = newSize / stage.width();

                stage.width(newSize);
                stage.height(newSize);

                bgRect.width(newSize);
                bgRect.height(newSize);
                backgroundLayer.draw();

                if (toggleGrid.checked) {
                    drawGrid();
                }

                stage.scale({ x: stage.scaleX() * scale, y: stage.scaleY() * scale });
                stage.draw();
            });

            // === Add image ===
            imageUpload.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (evt) {
                    const img = new window.Image();
                    img.onload = function () {
                        const maxWidth = stage.width() * 0.7;
                        const maxHeight = stage.height() * 0.7;
                        let width = img.width;
                        let height = img.height;

                        const scale = Math.min(maxWidth / width, maxHeight / height, 1);
                        width *= scale;
                        height *= scale;

                        const konvaImage = new Konva.Image({
                            image: img,
                            x: (stage.width() - width) / 2,
                            y: (stage.height() - height) / 2,
                            width: width,
                            height: height,
                            draggable: true,
                            shadowColor: 'rgba(15,23,42,0.45)',
                            shadowBlur: 20,
                            shadowOffset: { x: 0, y: 10 },
                            shadowOpacity: 0.35
                        });

                        mainLayer.add(konvaImage);
                        registerElement(konvaImage, 'image', 'Image');
                        setSelection(konvaImage);
                        mainLayer.draw();
                    };
                    img.src = evt.target.result;
                };
                reader.readAsDataURL(file);
                // reset input
                e.target.value = '';
            });

            // === Add text ===
            btnAddText.addEventListener('click', () => {
                const text = new Konva.Text({
                    text: 'Double-cliquez pour éditer',
                    x: stage.width() / 2 - 150,
                    y: stage.height() / 2 - 20,
                    fontFamily: 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
                    fontSize: 26,
                    fill: '#111827',
                    draggable: false,
                    padding: 8,
                    align: 'left'
                });

                const rectBehind = new Konva.Rect({
                    x: text.x() - 16,
                    y: text.y() - 8,
                    width: text.width() + 32,
                    height: text.height() + 16,
                    cornerRadius: 18,
                    fill: 'rgba(255,255,255,0.9)',
                    stroke: 'rgba(148,163,184,0.7)',
                    strokeWidth: 1
                });

                const group = new Konva.Group({
                    x: stage.width() / 2 - text.width() / 2,
                    y: stage.height() / 2 - text.height() / 2,
                    draggable: true
                });

                group.add(rectBehind);
                group.add(text);
                mainLayer.add(group);
                mainLayer.draw();

                registerElement(group, 'text', 'Texte');
                setSelection(group);
            });

            // Text editing via double-click
            stage.on('dblclick', function (e) {
                const clicked = e.target;
                if (!clicked) return;

                let textNode = null;

                if (clicked.getClassName() === 'Text') {
                    textNode = clicked;
                } else if (clicked.getClassName() === 'Group') {
                    textNode = clicked.findOne('Text');
                }

                if (!textNode) return;

                const textPosition = textNode.absolutePosition();
                const stageBox = stage.container().getBoundingClientRect();
                const areaPosition = {
                    x: stageBox.left + textPosition.x * stage.scaleX(),
                    y: stageBox.top + textPosition.y * stage.scaleY()
                };

                const textarea = document.createElement('textarea');
                document.body.appendChild(textarea);

                textarea.value = textNode.text();
                textarea.style.position = 'absolute';
                textarea.style.top = areaPosition.y + 'px';
                textarea.style.left = areaPosition.x + 'px';
                textarea.style.width = textNode.width() * stage.scaleX() + 'px';
                textarea.style.height = textNode.height() * stage.scaleY() + 'px';
                textarea.style.fontSize = (textNode.fontSize() * stage.scaleX()) + 'px';
                textarea.style.borderRadius = '12px';
                textarea.style.padding = '6px 10px';
                textarea.style.border = '1px solid #cbd5f5';
                textarea.style.outline = 'none';
                textarea.style.resize = 'both';
                textarea.style.background = '#ffffff';
                textarea.style.color = '#111827';
                textarea.style.fontFamily = textNode.fontFamily();
                textarea.style.lineHeight = '1.4';
                textarea.style.boxShadow = '0 10px 30px rgba(15,23,42,0.18)';
                textarea.style.zIndex = 9999;

                textarea.focus();

                function removeTextarea(save = true) {
                    if (save) {
                        textNode.text(textarea.value);
                        mainLayer.draw();
                        saveHistory();
                    }
                    if (textarea.parentNode) {
                        textarea.parentNode.removeChild(textarea);
                    }
                    window.removeEventListener('click', handleOutsideClick);
                }

                function handleOutsideClick(ev) {
                    if (ev.target !== textarea) {
                        removeTextarea(true);
                    }
                }

                textarea.addEventListener('keydown', function (ev) {
                    if (ev.key === 'Enter' && (ev.metaKey || ev.ctrlKey)) {
                        removeTextarea(true);
                    } else if (ev.key === 'Escape') {
                        removeTextarea(false);
                    }
                });

                setTimeout(() => {
                    window.addEventListener('click', handleOutsideClick);
                });
            });

            // === Shapes ===
            btnAddRect.addEventListener('click', () => {
                const rect = new Konva.Rect({
                    x: stage.width() / 2 - 80,
                    y: stage.height() / 2 - 50,
                    width: 160,
                    height: 100,
                    fill: '#e5f0c8',
                    stroke: '#647a0b',
                    strokeWidth: 1.5,
                    cornerRadius: 22,
                    draggable: true
                });

                mainLayer.add(rect);
                registerElement(rect, 'shape-rect', 'Rectangle');
                setSelection(rect);
                mainLayer.draw();
            });

            btnAddCircle.addEventListener('click', () => {
                const circle = new Konva.Circle({
                    x: stage.width() / 2,
                    y: stage.height() / 2,
                    radius: 70,
                    fill: '#fef3c7',
                    stroke: '#f59e0b',
                    strokeWidth: 1.5,
                    draggable: true
                });

                mainLayer.add(circle);
                registerElement(circle, 'shape-circle', 'Cercle');
                setSelection(circle);
                mainLayer.draw();
            });

            // === Stage click: select / deselect ===
            stage.on('click', function (e) {
                if (e.target === stage || e.target === bgRect) {
                    clearSelection();
                    return;
                }

                let node = e.target;
                while (node.getParent() && node.getParent() !== mainLayer) {
                    node = node.getParent();
                }
                if (node && node !== mainLayer) {
                    setSelection(node);
                } else {
                    clearSelection();
                }
            });

            // === Center selection ===
            btnCenterSelection.addEventListener('click', () => {
                if (!selectedNode) return;

                const box = selectedNode.getClientRect();
                const dx = stage.width() / 2 - (box.x + box.width / 2);
                const dy = stage.height() / 2 - (box.y + box.height / 2);

                selectedNode.x(selectedNode.x() + dx);
                selectedNode.y(selectedNode.y() + dy);
                mainLayer.draw();
                saveHistory();
            });

            // === Delete selection ===
            btnDeleteSelection.addEventListener('click', () => {
                if (!selectedNode) return;
                removeElementMetaForNode(selectedNode);
                selectedNode.destroy();
                clearSelection();
                mainLayer.draw();
                saveHistory();
            });

            // === Clear canvas ===
            btnClearCanvas.addEventListener('click', () => {
                mainLayer.destroyChildren();
                mainLayer.add(transformer);
                Object.keys(elementMeta).forEach(k => delete elementMeta[k]);
                clearSelection();

                bgRect.fill('#f9fafb');
                backgroundLayer.draw();
                bgColorPicker.value = '#f9fafb';

                gridLayer.destroyChildren();
                toggleGrid.checked = false;
                gridLayer.visible(false);

                zoomSlider.value = 100;
                applyZoom(100);

                saveHistory();
            });

            // === Undo ===
            btnUndo.addEventListener('click', () => {
                if (history.length < 2) return;
                history.pop();
                const previous = history[history.length - 1];
                // simple approach: reload page (history kept only in this session)
                window.location.reload();
            });

            // === Layers list & inspector ===
            function refreshLayersList() {
                layersList.innerHTML = '';

                const children = mainLayer.getChildren((n) => n !== transformer);

                const ordered = children.map(node => {
                    const id = node.getAttr('amId');
                    const meta = elementMeta[id] || null;
                    return { node, id, meta };
                }).filter(item => item.id);

                if (ordered.length === 0) {
                    const empty = document.createElement('p');
                    empty.className = 'text-[11px] text-slate-400';
                    empty.textContent = 'Aucun calque pour le moment. Ajoutez du texte, une forme ou une image.';
                    layersList.appendChild(empty);
                    return;
                }

                ordered.forEach((item, idx) => {
                    const node = item.node;

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'layer-item-btn';
                    btn.dataset.id = item.id;

                    if (selectedNode && selectedNode.getAttr('amId') === item.id) {
                        btn.classList.add('is-active');
                    }

                    const left = document.createElement('div');
                    left.style.display = 'flex';
                    left.style.flexDirection = 'column';
                    left.style.alignItems = 'flex-start';
                    left.style.gap = '2px';

                    const titleRow = document.createElement('div');
                    titleRow.style.display = 'flex';
                    titleRow.style.alignItems = 'center';
                    titleRow.style.gap = '0.4rem';

                    const iconSpan = document.createElement('span');
                    iconSpan.style.fontSize = '0.9rem';

                    const meta = item.meta || {};
                    const typeAttr = meta.type || node.getAttr('amType') || node.getClassName();
                    let icon = '⬛';
                    let labelType = 'Autre';
                    if (typeAttr === 'image') {
                        icon = '🖼️';
                        labelType = 'Image';
                    } else if (typeAttr === 'text') {
                        icon = '✏️';
                        labelType = 'Texte';
                    } else if (typeAttr === 'shape-rect') {
                        icon = '◼️';
                        labelType = 'Rectangle';
                    } else if (typeAttr === 'shape-circle') {
                        icon = '⚪';
                        labelType = 'Cercle';
                    }

                    iconSpan.textContent = icon;

                    const label = document.createElement('span');
                    const nodeName = meta.name || node.getAttr('amName') || ('Calque ' + (idx + 1));
                    label.textContent = nodeName;
                    label.style.fontWeight = '500';

                    titleRow.appendChild(iconSpan);
                    titleRow.appendChild(label);

                    const subRow = document.createElement('div');
                    subRow.className = 'layer-chip';
                    const idxSpan = document.createElement('span');
                    idxSpan.textContent = '#' + (idx + 1);
                    const typeSpan = document.createElement('span');
                    typeSpan.textContent = labelType;
                    subRow.appendChild(idxSpan);
                    subRow.appendChild(typeSpan);

                    left.appendChild(titleRow);
                    left.appendChild(subRow);

                    const right = document.createElement('div');
                    right.style.display = 'flex';
                    right.style.alignItems = 'center';
                    right.style.gap = '0.25rem';

                    const zLabel = document.createElement('span');
                    zLabel.style.fontSize = '0.7rem';
                    zLabel.style.color = '#6b7280';
                    zLabel.textContent = 'Z ' + idx;

                    const eyeBtn = document.createElement('button');
                    eyeBtn.type = 'button';
                    eyeBtn.style.fontSize = '0.8rem';
                    eyeBtn.style.border = 'none';
                    eyeBtn.style.background = 'transparent';
                    eyeBtn.style.cursor = 'pointer';
                    eyeBtn.textContent = node.visible() ? '👁️' : '🚫';

                    eyeBtn.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        node.visible(!node.visible());
                        eyeBtn.textContent = node.visible() ? '👁️' : '🚫';
                        mainLayer.draw();
                    });

                    const lockBtn = document.createElement('button');
                    lockBtn.type = 'button';
                    lockBtn.style.fontSize = '0.8rem';
                    lockBtn.style.border = 'none';
                    lockBtn.style.background = 'transparent';
                    lockBtn.style.cursor = 'pointer';
                    lockBtn.textContent = node.draggable() ? '🔓' : '🔒';

                    lockBtn.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        const newDraggable = !node.draggable();
                        node.draggable(newDraggable);
                        node.opacity(newDraggable ? 1 : 0.6);
                        lockBtn.textContent = newDraggable ? '🔓' : '🔒';
                        mainLayer.draw();
                    });

                    right.appendChild(zLabel);
                    right.appendChild(eyeBtn);
                    right.appendChild(lockBtn);

                    btn.appendChild(left);
                    btn.appendChild(right);

                    btn.addEventListener('click', () => {
                        setSelection(node);
                    });

                    layersList.appendChild(btn);
                });
            }

            function updateInspector() {
                if (!selectedNode) {
                    selectionPanel.classList.add('hidden');
                    noSelectionHint.classList.remove('hidden');
                    selectionTypeBadge.textContent = 'Aucun élément';
                    return;
                }

                selectionPanel.classList.remove('hidden');
                noSelectionHint.classList.add('hidden');

                const id = selectedNode.getAttr('amId');
                const meta = elementMeta[id] || {};
                const typeAttr = meta.type || selectedNode.getAttr('amType') || selectedNode.getClassName();
                let type = typeAttr;

                // Type badge
                let badgeLabel = 'Élément';
                if (type === 'image') badgeLabel = 'Image';
                else if (type === 'text') badgeLabel = 'Texte';
                else if (type === 'shape-rect') badgeLabel = 'Rectangle';
                else if (type === 'shape-circle') badgeLabel = 'Cercle';
                selectionTypeBadge.textContent = badgeLabel;

                // Name
                inputLayerName.value = meta.name || selectedNode.getAttr('amName') || '';

                // Opacity
                const opacity = Math.round((selectedNode.opacity() || 1) * 100);
                inputOpacity.value = opacity;
                opacityValue.textContent = opacity + '%';

                // Reset panel visibility
                shapeControls.classList.add('hidden');
                textControls.classList.add('hidden');
                imageControls.classList.add('hidden');

                // Shape controls
                if (type === 'shape-rect' || type === 'shape-circle') {
                    shapeControls.classList.remove('hidden');

                    let fill = selectedNode.fill() || '#e5f0c8';
                    if (typeof fill === 'string' && /^#([0-9A-F]{3}){1,2}$/i.test(fill)) {
                        shapeFillInput.value = fill;
                    } else {
                        shapeFillInput.value = '#e5f0c8';
                    }

                    let stroke = selectedNode.stroke() || '#647a0b';
                    if (typeof stroke === 'string' && /^#([0-9A-F]{3}){1,2}$/i.test(stroke)) {
                        shapeStrokeInput.value = stroke;
                    } else {
                        shapeStrokeInput.value = '#647a0b';
                    }

                    const sw = selectedNode.strokeWidth() || 1;
                    shapeStrokeWidth.value = sw;
                    strokeWidthValue.textContent = sw + ' px';
                }

                // Text controls
                if (type === 'text') {
                    textControls.classList.remove('hidden');

                    const textNode = selectedNode.findOne('Text') || selectedNode;

                    const family = textNode.fontFamily() || '';
                    if (family.includes('Playfair')) {
                        textFontFamily.value = 'serif';
                    } else if (family.includes('Raleway')) {
                        textFontFamily.value = 'sans';
                    } else {
                        textFontFamily.value = 'system';
                    }

                    textFontSize.value = Math.round(textNode.fontSize() || 26);

                    const style = textNode.fontStyle ? textNode.fontStyle() : '';
                    if (style && style.includes('bold')) {
                        btnToggleBold.classList.add('is-active');
                    } else {
                        btnToggleBold.classList.remove('is-active');
                    }

                    let fillText = textNode.fill() || '#111827';
                    if (typeof fillText === 'string' && /^#([0-9A-F]{3}){1,2}$/i.test(fillText)) {
                        textColor.value = fillText;
                    } else {
                        textColor.value = '#111827';
                    }
                }

                // Image controls
                if (type === 'image') {
                    imageControls.classList.remove('hidden');
                    const img = selectedNode;
                    img.cache();
                    img.filters([Konva.Filters.Brighten, Konva.Filters.Contrast]);
                    imgBrightness.value = img.brightness() || 0;
                    imgContrast.value   = img.contrast() || 0;
                }
            }

            // === Inspector events ===
            inputLayerName.addEventListener('input', () => {
                if (!selectedNode) return;
                const id = selectedNode.getAttr('amId');
                if (!id) return;
                elementMeta[id] = elementMeta[id] || {};
                elementMeta[id].name = inputLayerName.value;
                selectedNode.setAttr('amName', inputLayerName.value);
                refreshLayersList();
            });

            inputOpacity.addEventListener('input', () => {
                if (!selectedNode) return;
                const v = parseInt(inputOpacity.value, 10) || 100;
                selectedNode.opacity(v / 100);
                opacityValue.textContent = v + '%';
                mainLayer.batchDraw();
            });

            inputOpacity.addEventListener('change', () => {
                saveHistory();
            });

            // Shape
            shapeFillInput.addEventListener('input', () => {
                if (!selectedNode) return;
                selectedNode.fill(shapeFillInput.value);
                mainLayer.batchDraw();
            });

            shapeFillInput.addEventListener('change', () => saveHistory());

            shapeStrokeInput.addEventListener('input', () => {
                if (!selectedNode) return;
                selectedNode.stroke(shapeStrokeInput.value);
                mainLayer.batchDraw();
            });

            shapeStrokeInput.addEventListener('change', () => saveHistory());

            shapeStrokeWidth.addEventListener('input', () => {
                if (!selectedNode) return;
                const sw = parseInt(shapeStrokeWidth.value, 10) || 0;
                selectedNode.strokeWidth(sw);
                strokeWidthValue.textContent = sw + ' px';
                mainLayer.batchDraw();
            });

            shapeStrokeWidth.addEventListener('change', () => saveHistory());

            // Text
            textFontFamily.addEventListener('change', () => {
                withTextNode((textNode) => {
                    let font = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
                    if (textFontFamily.value === 'serif') {
                        font = '"Playfair Display", "Times New Roman", serif';
                    } else if (textFontFamily.value === 'sans') {
                        font = '"Raleway", system-ui, -apple-system, sans-serif';
                    }
                    textNode.fontFamily(font);
                });
            });

            textFontSize.addEventListener('change', () => {
                withTextNode((textNode) => {
                    const size = parseInt(textFontSize.value, 10) || 26;
                    textNode.fontSize(size);
                });
            });

            btnToggleBold.addEventListener('click', () => {
                withTextNode((textNode) => {
                    const current = textNode.fontStyle ? textNode.fontStyle() : 'normal';
                    const newStyle = current.includes('bold') ? 'normal' : 'bold';
                    textNode.fontStyle && textNode.fontStyle(newStyle);
                    btnToggleBold.classList.toggle('is-active');
                });
            });

            textColor.addEventListener('input', () => {
                withTextNode((textNode) => {
                    textNode.fill(textColor.value);
                });
            });

            textColor.addEventListener('change', () => saveHistory());

            btnAlignLeft.addEventListener('click', () => {
                withTextNode((t) => t.align('left'));
            });
            btnAlignCenter.addEventListener('click', () => {
                withTextNode((t) => t.align('center'));
            });
            btnAlignRight.addEventListener('click', () => {
                withTextNode((t) => t.align('right'));
            });

            // Image filters
            imgBrightness.addEventListener('input', () => {
                if (!selectedNode) return;
                selectedNode.brightness(parseFloat(imgBrightness.value));
                mainLayer.batchDraw();
            });
            imgContrast.addEventListener('input', () => {
                if (!selectedNode) return;
                selectedNode.contrast(parseFloat(imgContrast.value));
                mainLayer.batchDraw();
            });

            imgBrightness.addEventListener('change', () => saveHistory());
            imgContrast.addEventListener('change', () => saveHistory());

            // === Z-order & duplicate ===
            function updateZ(action) {
                if (!selectedNode) return;

                if (action === 'top') {
                    selectedNode.moveToTop();
                } else if (action === 'bottom') {
                    selectedNode.moveToBottom();
                    transformer.moveToTop();
                } else if (action === 'up') {
                    selectedNode.moveUp();
                } else if (action === 'down') {
                    selectedNode.moveDown();
                }

                transformer.moveToTop();
                mainLayer.draw();
                refreshLayersList();
                saveHistory();
            }

            btnZTop.addEventListener('click', () => updateZ('top'));
            btnZBottom.addEventListener('click', () => updateZ('bottom'));
            btnZUp.addEventListener('click', () => updateZ('up'));
            btnZDown.addEventListener('click', () => updateZ('down'));

            btnDuplicate.addEventListener('click', () => {
                if (!selectedNode) return;
                const id = selectedNode.getAttr('amId');
                const meta = elementMeta[id] || {};
                const clone = selectedNode.clone({
                    x: selectedNode.x() + 20,
                    y: selectedNode.y() + 20
                });
                mainLayer.add(clone);
                mainLayer.draw();
                registerElement(clone, meta.type || selectedNode.getAttr('amType') || 'element', (meta.name || 'Élément') + ' (copie)');
                setSelection(clone);
            });

            // === Templates ===
            function addQuoteTemplate() {
                clearCanvasKeepBg();

                // Bande centrale
                const band = new Konva.Rect({
                    x: stage.width() * 0.1,
                    y: stage.height() * 0.2,
                    width: stage.width() * 0.8,
                    height: stage.height() * 0.6,
                    cornerRadius: 32,
                    fill: '#ffffff',
                    stroke: '#e5e7eb',
                    strokeWidth: 1
                });
                mainLayer.add(band);
                registerElement(band, 'shape-rect', 'Bloc citation');

                // Texte citation
                const quoteText = new Konva.Text({
                    text: '« Votre bien-être est un voyage, pas une destination. »',
                    x: stage.width() * 0.15,
                    y: stage.height() * 0.27,
                    width: stage.width() * 0.7,
                    fontFamily: '"Playfair Display", "Times New Roman", serif',
                    fontSize: 28,
                    fill: '#111827',
                    align: 'center',
                    lineHeight: 1.3
                });
                const quoteGroup = new Konva.Group({
                    x: 0,
                    y: 0,
                    draggable: true
                });
                quoteGroup.add(quoteText);
                mainLayer.add(quoteGroup);
                registerElement(quoteGroup, 'text', 'Citation');

                // Auteur
                const authorText = new Konva.Text({
                    text: '— Votre nom / AromaMade',
                    x: stage.width() * 0.2,
                    y: stage.height() * 0.55,
                    width: stage.width() * 0.6,
                    fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
                    fontSize: 16,
                    fill: '#6b7280',
                    align: 'center'
                });
                const authorGroup = new Konva.Group({
                    x: 0,
                    y: 0,
                    draggable: true
                });
                authorGroup.add(authorText);
                mainLayer.add(authorGroup);
                registerElement(authorGroup, 'text', 'Auteur');

                mainLayer.draw();
            }

            function addPromoTemplate() {
                clearCanvasKeepBg();

                // Top bande
                const topBand = new Konva.Rect({
                    x: 0,
                    y: 0,
                    width: stage.width(),
                    height: stage.height() * 0.18,
                    fill: '#647a0b'
                });
                mainLayer.add(topBand);
                registerElement(topBand, 'shape-rect', 'Bande haute');

                const topText = new Konva.Text({
                    text: 'Offre spéciale bien-être',
                    x: 0,
                    y: stage.height() * 0.06,
                    width: stage.width(),
                    fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
                    fontSize: 22,
                    fill: '#fefce8',
                    align: 'center'
                });
                const topGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
                topGroup.add(topText);
                mainLayer.add(topGroup);
                registerElement(topGroup, 'text', 'Titre promo');

                // Bloc central pour image / visuel
                const centerRect = new Konva.Rect({
                    x: stage.width() * 0.09,
                    y: stage.height() * 0.22,
                    width: stage.width() * 0.82,
                    height: stage.height() * 0.45,
                    cornerRadius: 30,
                    fill: '#f9fafb',
                    stroke: '#e5e7eb',
                    strokeWidth: 1,
                    dash: [6, 4]
                });
                mainLayer.add(centerRect);
                registerElement(centerRect, 'shape-rect', 'Zone image');

                const hint = new Konva.Text({
                    text: 'Ajoutez votre photo ou visuel ici',
                    x: stage.width() * 0.12,
                    y: stage.height() * 0.37,
                    width: stage.width() * 0.76,
                    fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
                    fontSize: 16,
                    fill: '#9ca3af',
                    align: 'center'
                });
                const hintGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
                hintGroup.add(hint);
                mainLayer.add(hintGroup);
                registerElement(hintGroup, 'text', 'Texte visuel');

                // Bande CTA
                const ctaRect = new Konva.Rect({
                    x: stage.width() * 0.18,
                    y: stage.height() * 0.74,
                    width: stage.width() * 0.64,
                    height: stage.height() * 0.12,
                    cornerRadius: 999,
                    fill: '#f97316'
                });
                mainLayer.add(ctaRect);
                registerElement(ctaRect, 'shape-rect', 'Bouton CTA');

                const ctaText = new Konva.Text({
                    text: '-20% sur votre première séance',
                    x: stage.width() * 0.18,
                    y: stage.height() * 0.77,
                    width: stage.width() * 0.64,
                    fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
                    fontSize: 18,
                    fill: '#111827',
                    align: 'center'
                });
                const ctaGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
                ctaGroup.add(ctaText);
                mainLayer.add(ctaGroup);
                registerElement(ctaGroup, 'text', 'Texte CTA');

                mainLayer.draw();
            }

            function addEventTemplate() {
                clearCanvasKeepBg();

                // Badge date
                const dateRect = new Konva.Rect({
                    x: stage.width() * 0.08,
                    y: stage.height() * 0.08,
                    width: 110,
                    height: 110,
                    cornerRadius: 26,
                    fill: '#fef3c7',
                    stroke: '#f59e0b',
                    strokeWidth: 1.5
                });
                mainLayer.add(dateRect);
                registerElement(dateRect, 'shape-rect', 'Badge date');

                const dateText = new Konva.Text({
                    text: 'SAM\n12 OCT',
                    x: stage.width() * 0.08,
                    y: stage.height() * 0.1,
                    width: 110,
                    fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
                    fontSize: 18,
                    fill: '#92400e',
                    align: 'center',
                    lineHeight: 1.2
                });
                const dateGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
                dateGroup.add(dateText);
                mainLayer.add(dateGroup);
                registerElement(dateGroup, 'text', 'Texte date');

                // Titre atelier
                const titleText = new Konva.Text({
                    text: 'Atelier : Découvrir les huiles essentielles',
                    x: stage.width() * 0.25,
                    y: stage.height() * 0.12,
                    width: stage.width() * 0.65,
                    fontFamily: '"Playfair Display", "Times New Roman", serif',
                    fontSize: 24,
                    fill: '#0f172a',
                    align: 'left',
                    lineHeight: 1.2
                });
                const titleGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
                titleGroup.add(titleText);
                mainLayer.add(titleGroup);
                registerElement(titleGroup, 'text', 'Titre atelier');

                // Sous-texte
                const subText = new Konva.Text({
                    text: 'En ligne ou en cabinet · 2h de pratique\nPlaces limitées · Réservation obligatoire',
                    x: stage.width() * 0.25,
                    y: stage.height() * 0.27,
                    width: stage.width() * 0.65,
                    fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
                    fontSize: 14,
                    fill: '#4b5563',
                    align: 'left',
                    lineHeight: 1.4
                });
                const subGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
                subGroup.add(subText);
                mainLayer.add(subGroup);
                registerElement(subGroup, 'text', 'Infos atelier');

                // Bande bas URL
                const bottomRect = new Konva.Rect({
                    x: 0,
                    y: stage.height() * 0.84,
                    width: stage.width(),
                    height: stage.height() * 0.16,
                    fill: '#111827'
                });
                mainLayer.add(bottomRect);
                registerElement(bottomRect, 'shape-rect', 'Bande bas');

                const urlText = new Konva.Text({
                    text: 'Réserver sur aromamade.com/pro/votrenom',
                    x: 0,
                    y: stage.height() * 0.89,
                    width: stage.width(),
                    fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
                    fontSize: 16,
                    fill: '#f9fafb',
                    align: 'center'
                });
                const urlGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
                urlGroup.add(urlText);
                mainLayer.add(urlGroup);
                registerElement(urlGroup, 'text', 'URL');

                mainLayer.draw();
            }

            btnTemplateQuote.addEventListener('click', addQuoteTemplate);
            btnTemplatePromo.addEventListener('click', addPromoTemplate);
            btnTemplateEvent.addEventListener('click', addEventTemplate);

            // === Keyboard shortcuts ===
            document.addEventListener('keydown', (e) => {
                const tag = (document.activeElement && document.activeElement.tagName || '').toLowerCase();
                if (['input', 'textarea', 'select'].includes(tag)) return;

                if ((e.key === 'Delete' || e.key === 'Backspace') && selectedNode) {
                    e.preventDefault();
                    btnDeleteSelection.click();
                }

                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'd' && selectedNode) {
                    e.preventDefault();
                    btnDuplicate.click();
                }

                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
                    e.preventDefault();
                    btnUndo.click();
                }

                if (!selectedNode) return;

                const step = e.shiftKey ? 10 : 2;
                if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                    e.preventDefault();
                    const pos = selectedNode.position();
                    if (e.key === 'ArrowUp') selectedNode.y(pos.y - step);
                    if (e.key === 'ArrowDown') selectedNode.y(pos.y + step);
                    if (e.key === 'ArrowLeft') selectedNode.x(pos.x - step);
                    if (e.key === 'ArrowRight') selectedNode.x(pos.x + step);
                    mainLayer.batchDraw();
                    saveHistory();
                }
            });

            // === Export ===
            btnExport.addEventListener('click', () => {
                const previousScale = stage.scaleX();
                const previousPos = stage.position();

                stage.scale({ x: 1, y: 1 });
                stage.position({ x: 0, y: 0 });
                stage.draw();

                const dataURL = stage.toDataURL({ pixelRatio: 3 });

                stage.scale({ x: previousScale, y: previousScale });
                stage.position(previousPos);
                stage.draw();

                const link = document.createElement('a');
                link.download = 'aromamade-social-post.png';
                link.href = dataURL;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Initial state
            toggleGridVisibility();
            applyZoom(100);
            refreshLayersList();
            saveHistory();
        });
    </script>
</x-app-layout>
