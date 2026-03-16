{{-- resources/views/tools/konva/partials/left-sidebar.blade.php --}}
<aside class="editor-column">
    <div class="sidebar-stack space-y-4">
        <div class="toolbar-card glass-card">
            <div class="flex items-center justify-between gap-2">
                <span class="toolbar-title">Demarrage rapide</span>
                <span class="badge-soft">Guide</span>
            </div>
            <div class="quick-start-steps">
                <div class="quick-step">
                    <span class="step-index">1</span>
                    <span>Selectionnez un format pour debloquer les templates adaptes.</span>
                </div>
                <div class="quick-step">
                    <span class="step-index">2</span>
                    <span>Ajoutez image, texte, formes puis alignez en un clic.</span>
                </div>
                <div class="quick-step">
                    <span class="step-index">3</span>
                    <span>Exportez en PNG HD quand le visuel est pret.</span>
                </div>
            </div>
        </div>

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
                    <span class="pill-icon">Img</span>
                    Importer une image
                </label>

                <button id="btnAddText" type="button" class="pill-btn pill-btn-ghost">
                    <span class="pill-icon">Txt</span>
                    Ajouter du texte
                </button>
            </div>

            <p class="text-[11px] text-slate-500">
                Astuce : glissez-deposez une image directement sur le canvas.
            </p>

            <div class="mt-3">
                <button id="btnToggleShapesDrawer" type="button" class="pill-btn pill-btn-ghost w-full justify-between">
                    <span class="flex items-center gap-2">Ajouter une forme</span>
                    <span id="shapesDrawerChevron">v</span>
                </button>

                <div id="shapesDrawer" class="mt-2 hidden">
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" class="shape-btn" data-shape="rect" title="Rectangle">Rect</button>
                        <button type="button" class="shape-btn" data-shape="roundRect" title="Rectangle arrondi">Rond</button>
                        <button type="button" class="shape-btn" data-shape="circle" title="Cercle">Cercle</button>
                        <button type="button" class="shape-btn" data-shape="ellipse" title="Ellipse">Ellipse</button>

                        <button type="button" class="shape-btn" data-shape="triangle" title="Triangle">Tri</button>
                        <button type="button" class="shape-btn" data-shape="rightTriangle" title="Triangle droit">Tri D</button>
                        <button type="button" class="shape-btn" data-shape="diamond" title="Losange">Losange</button>
                        <button type="button" class="shape-btn" data-shape="parallelogram" title="Parallelogramme">Para</button>

                        <button type="button" class="shape-btn" data-shape="trapezoid" title="Trapeze">Trap</button>
                        <button type="button" class="shape-btn" data-shape="pentagon" title="Pentagone">Penta</button>
                        <button type="button" class="shape-btn" data-shape="hexagon" title="Hexagone">Hexa</button>
                        <button type="button" class="shape-btn" data-shape="octagon" title="Octogone">Octa</button>

                        <button type="button" class="shape-btn" data-shape="star5" title="Etoile 5">S5</button>
                        <button type="button" class="shape-btn" data-shape="star6" title="Etoile 6">S6</button>
                        <button type="button" class="shape-btn" data-shape="star8" title="Etoile 8">S8</button>
                        <button type="button" class="shape-btn" data-shape="burst" title="Burst">Burst</button>

                        <button type="button" class="shape-btn" data-shape="arrowRight" title="Fleche droite">-></button>
                        <button type="button" class="shape-btn" data-shape="arrowLeft" title="Fleche gauche">Left</button>
                        <button type="button" class="shape-btn" data-shape="arrowUp" title="Fleche haut">Haut</button>
                        <button type="button" class="shape-btn" data-shape="arrowDown" title="Fleche bas">Bas</button>
                    </div>

                    <p class="mt-2 text-[11px] text-slate-500">
                        Cliquez une forme puis ajustez couleur et contour a droite.
                    </p>
                </div>
            </div>
        </div>

        <div class="toolbar-card glass-card">
            <div class="mb-2 flex items-center justify-between">
                <span class="toolbar-title">Mise en page</span>
                <span class="badge-soft">Canvas</span>
            </div>

            <div class="mb-3">
                <div class="small-label">Zoom</div>
                <div class="range-row">
                    <input id="zoomSlider" type="range" min="40" max="140" value="100">
                    <div id="zoomValue" class="range-value">100%</div>
                </div>
                <p class="mt-1 text-[11px] text-slate-500">
                    Le zoom n'affecte pas la qualite d'export.
                </p>
            </div>

            <div class="mb-3 border-t border-dashed border-slate-200 pt-3">
                <div class="small-label mb-1">Fond</div>

                <div class="flex items-center gap-2">
                    <input id="bgColorPicker" type="color"
                           class="h-8 w-12 rounded-lg border border-slate-200 bg-white"
                           value="#f9fafb">
                    <button id="btnResetBg" type="button" class="pill-btn pill-btn-ghost px-3 py-1 text-[11px]">
                        Reinitialiser
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

            <div class="border-t border-dashed border-slate-200 pt-3">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="small-label" style="margin-bottom:0;">Grille</div>
                    <label class="flex items-center gap-2 text-[11px] text-slate-600">
                        <input id="toggleGrid" type="checkbox" class="rounded border-slate-300">
                        Afficher
                    </label>
                </div>

                <label class="flex items-center gap-2 text-[11px] text-slate-600">
                    <input id="toggleSnapGrid" type="checkbox" class="rounded border-slate-300" checked>
                    Aimantation (snap)
                </label>

                <div class="mt-2 flex items-center gap-2 text-[11px] text-slate-600">
                    <span class="whitespace-nowrap">Pas</span>
                    <select id="gridStepSelect" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-[11px]">
                        <option value="10">10 px</option>
                        <option value="20">20 px</option>
                        <option value="40" selected>40 px</option>
                        <option value="80">80 px</option>
                    </select>
                </div>

                <div class="mt-2 flex items-center gap-2 text-[11px] text-slate-600">
                    <span class="whitespace-nowrap">Tol.</span>
                    <input id="snapThresholdRange" type="range" min="0" max="20" value="6" class="w-full">
                    <span id="snapThresholdValue" class="w-7 text-right tabular-nums">6</span>
                </div>

                <div class="flex flex-wrap gap-2 mt-3">
                    <button id="btnCenterSelection" type="button" class="pill-btn pill-btn-ghost">Centrer</button>
                    <button id="btnDeleteSelection" type="button" class="pill-btn pill-btn-ghost">Supprimer</button>
                </div>

                <div class="mt-3 border-t border-dashed border-slate-200 pt-3">
                    <div class="small-label mb-1">Alignement dans le canvas</div>
                    <div class="grid grid-cols-3 gap-1.5">
                        <button id="btnAlignCanvasLeft" type="button" class="z-order-btn text-[11px]">Gauche</button>
                        <button id="btnAlignCanvasCenterX" type="button" class="z-order-btn text-[11px]">Centre H</button>
                        <button id="btnAlignCanvasRight" type="button" class="z-order-btn text-[11px]">Droite</button>
                        <button id="btnAlignCanvasTop" type="button" class="z-order-btn text-[11px]">Haut</button>
                        <button id="btnAlignCanvasCenterY" type="button" class="z-order-btn text-[11px]">Centre V</button>
                        <button id="btnAlignCanvasBottom" type="button" class="z-order-btn text-[11px]">Bas</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="toolbar-card glass-card">
            <div class="mb-2 flex items-center justify-between">
                <span class="toolbar-title">Evenement</span>
                <span class="badge-soft">Optionnel</span>
            </div>

            <select id="eventSelector" class="small-select w-full">
                <option value="">Aucun</option>
                @foreach(($events ?? collect()) as $event)
                    <option value="{{ $event->id }}">{{ $event->title ?? ('Evenement #' . $event->id) }}</option>
                @endforeach
            </select>

            <p class="mt-2 text-[11px] text-slate-500">
                Permet de pre-remplir des textes a partir de vos evenements.
            </p>
        </div>

        <div class="toolbar-card glass-card">
            <div class="mb-2 flex items-center justify-between gap-2">
                <span class="toolbar-title">Templates rapides</span>
                <span class="badge-soft">Pret a publier</span>
            </div>

            <input
                id="quickTemplateSearch"
                type="text"
                class="template-search"
                placeholder="Rechercher un template..."
            >

            <div id="quickTemplatesGrid" class="mt-2 grid grid-cols-1 gap-2 max-h-72 overflow-y-auto pr-1">
                @foreach(config('konva.templates', []) as $tpl)
                    <button
                        type="button"
                        class="template-card js-template-btn opacity-40 pointer-events-none"
                        data-template="{{ $tpl['id'] }}"
                        data-format="{{ $tpl['format_id'] }}"
                        title="{{ $tpl['label'] }}"
                    >
                        <div class="template-title">{{ $tpl['label'] }}</div>
                        <div class="template-hint">{{ $tpl['hint'] ?? '' }}</div>
                    </button>
                @endforeach
            </div>

            <p class="mt-2 text-[11px] leading-snug text-slate-500">
                Ces templates se debloquent selon le format selectionne.
            </p>
        </div>

        <div class="toolbar-card glass-card">
            <div class="mb-2 flex items-center justify-between gap-2">
                <span class="toolbar-title">Templates enregistres</span>
                <span class="badge-soft">DB</span>
            </div>

            <input
                id="dbTemplateSearch"
                type="text"
                class="template-search"
                placeholder="Rechercher un template DB..."
            >

            <div id="templatesGridDb" class="mt-2 grid grid-cols-2 gap-2">
                @foreach(($templatesDb ?? collect()) as $tpl)
                    <button
                        type="button"
                        class="group rounded-xl border border-slate-200 bg-white overflow-hidden hover:shadow-sm transition js-template-db-btn opacity-40 pointer-events-none"
                        data-template-id="{{ $tpl['id'] }}"
                        data-format="{{ $tpl['format_id'] }}"
                        title="{{ $tpl['name'] }}"
                    >
                        <div class="aspect-square bg-slate-100 overflow-hidden">
                            @if(!empty($tpl['preview_url']))
                                <img src="{{ $tpl['preview_url'] }}" class="h-full w-full object-cover" alt="">
                            @endif
                        </div>
                        <div class="px-2 py-2 text-left">
                            <div class="text-[11px] font-semibold text-slate-900 truncate">
                                {{ $tpl['name'] }}
                            </div>
                            <div class="text-[10px] text-slate-500 truncate">
                                {{ $tpl['category'] ?? 'general' }}
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>

            <p class="mt-2 text-[11px] leading-snug text-slate-500">
                Les templates s'activent apres selection du format.
            </p>
        </div>

        <div class="toolbar-card glass-card">
            <div class="mb-1 flex items-center justify-between">
                <span class="toolbar-title">Historique</span>
                <div class="flex items-center gap-1">
                    <button id="btnUndo" type="button" class="pill-btn pill-btn-ghost px-2 py-1 text-[10px]">Annuler</button>
                    <button id="btnRedo" type="button" class="pill-btn pill-btn-ghost px-2 py-1 text-[10px]">Retablir</button>
                </div>
            </div>
            <p class="text-[11px] leading-snug text-slate-500">
                Espace de test : rien n'est enregistre automatiquement.
            </p>
            <p class="mt-2 text-[11px] leading-snug text-slate-500">
                Raccourcis : Ctrl/Cmd+Z, Ctrl/Cmd+Shift+Z, Suppr, fleches, Ctrl/Cmd+D.
            </p>
        </div>
    </div>
</aside>
