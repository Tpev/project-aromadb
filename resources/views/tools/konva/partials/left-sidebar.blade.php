{{-- resources/views/tools/konva/partials/left-sidebar.blade.php --}}
@php
    $konvaBranding = $konvaBranding ?? [];
    $konvaBrandingFonts = $konvaBrandingFonts ?? config('konva.branding_fonts', []);
    $konvaBrandingPresets = $konvaBrandingPresets ?? config('konva.branding_presets', []);
    $konvaContext = $konvaContext ?? ['events' => collect(), 'testimonials' => collect(), 'offers' => collect()];
@endphp

<aside class="editor-column editor-left">
    <div class="sidebar-stack space-y-4">
        <div class="toolbar-card glass-card workflow-card" id="workflowGuide">
            <div class="flex items-center justify-between gap-2">
                <span class="toolbar-title">Flux guide</span>
                <span class="badge-soft">Simple</span>
            </div>

            <div class="mt-2 flex items-center gap-2">
                <button id="btnModeQuick" type="button" class="mode-chip is-active">Mode rapide</button>
                <button id="btnModeExpert" type="button" class="mode-chip">Mode expert</button>
            </div>

            <div class="workflow-steps mt-3">
                <button type="button" class="workflow-step-btn" data-workflow-target="sectionTemplate">
                    1. Template + source
                </button>
                <button type="button" class="workflow-step-btn" data-workflow-target="sectionContent">
                    2. Contenu
                </button>
                <button type="button" class="workflow-step-btn" data-workflow-target="sectionStyle">
                    3. Style
                </button>
                <button type="button" class="workflow-step-btn" data-workflow-target="sectionHistory">
                    4. Export
                </button>
            </div>
        </div>

        <section id="sectionTemplate" class="toolbar-card glass-card">
            <div class="mb-2 flex items-center justify-between gap-2">
                <span class="toolbar-title">Etape 1 - Template + source</span>
                <span id="formatBadge"
                    class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                    Choisir un format
                </span>
            </div>

            <div class="mb-2">
                <button id="btnChooseFormatInline" type="button" class="pill-btn pill-btn-main w-full justify-center">
                    Choisir / changer le format
                </button>
            </div>

            <div class="space-y-2">
                <input id="quickTemplateSearch" type="text" class="template-search"
                    placeholder="Rechercher un template...">

                <div id="quickTemplatesGrid" class="grid grid-cols-1 gap-2 max-h-52 overflow-y-auto pr-1">
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
            </div>

            <details class="mt-3 rounded-xl border border-slate-200 bg-slate-50/80 p-2">
                <summary class="cursor-pointer text-[11px] font-semibold text-slate-700">
                    Templates enregistres (avance)
                </summary>

                <div class="mt-2">
                    <input id="dbTemplateSearch" type="text" class="template-search"
                        placeholder="Rechercher un template DB...">
                </div>

                <div id="templatesGridDb" class="mt-2 grid grid-cols-2 gap-2 max-h-52 overflow-y-auto pr-1">
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
                                <div class="text-[11px] font-semibold text-slate-900 truncate">{{ $tpl['name'] }}</div>
                                <div class="text-[10px] text-slate-500 truncate">{{ $tpl['category'] ?? 'general' }}</div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </details>

            <div class="mt-3 border-t border-dashed border-slate-200 pt-3">
                <div class="small-label mb-2">Source intelligente (auto-remplissage)</div>

                <div class="space-y-2">
                    <div>
                        <div class="small-label">Evenement</div>
                        <select id="eventSelector" class="small-select w-full">
                            <option value="">Aucun</option>
                            @foreach(($konvaContext['events'] ?? []) as $event)
                                <option value="{{ $event['id'] }}">{{ $event['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div class="small-label">Avis / temoignage</div>
                        <select id="testimonialSelector" class="small-select w-full">
                            <option value="">Aucun</option>
                            @foreach(($konvaContext['testimonials'] ?? []) as $review)
                                <option value="{{ $review['id'] }}">
                                    {{ $review['reviewer_name'] }} ({{ max(1, (int) ($review['rating'] ?? 5)) }}/5)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div class="small-label">Offre (prestation / formation)</div>
                        <select id="offerSelector" class="small-select w-full">
                            <option value="">Aucune</option>
                            @foreach(($konvaContext['offers'] ?? []) as $offer)
                                <option value="{{ $offer['id'] }}">
                                    {{ $offer['name'] }}@if(!empty($offer['price_label'])) - {{ $offer['price_label'] }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button id="btnAutofillTemplate" type="button" class="pill-btn pill-btn-main w-full justify-center">
                        Auto-remplir le template
                    </button>
                </div>
            </div>
        </section>

        <section id="sectionContent" class="toolbar-card glass-card">
            <div class="mb-2 flex items-center justify-between gap-2">
                <span class="toolbar-title">Etape 2 - Contenu</span>
                <span class="badge-soft">Edition</span>
            </div>

            <div class="flex flex-wrap gap-2">
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

            <p class="mt-2 text-[11px] text-slate-500">
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
                </div>
            </div>
        </section>

        <section id="sectionStyle" class="toolbar-card glass-card">
            <div class="mb-2 flex items-center justify-between gap-2">
                <span class="toolbar-title">Etape 3 - Style</span>
                <span class="badge-soft">Branding</span>
            </div>

            <div class="space-y-2">
                <div>
                    <div class="small-label">Preset</div>
                    <select id="brandPresetSelect" class="small-select w-full">
                        @foreach($konvaBrandingPresets as $preset)
                            <option value="{{ $preset['id'] }}"
                                {{ ($konvaBranding['preset'] ?? null) === $preset['id'] ? 'selected' : '' }}>
                                {{ $preset['label'] }}
                            </option>
                        @endforeach
                        <option value="manual">Manuel</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <div class="small-label">Police titre</div>
                        <select id="brandHeadingFont" class="small-select w-full">
                            @foreach($konvaBrandingFonts as $font)
                                <option value="{{ $font['key'] }}"
                                    {{ ($konvaBranding['fonts']['heading'] ?? null) === $font['key'] ? 'selected' : '' }}>
                                    {{ $font['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div class="small-label">Police texte</div>
                        <select id="brandBodyFont" class="small-select w-full">
                            @foreach($konvaBrandingFonts as $font)
                                <option value="{{ $font['key'] }}"
                                    {{ ($konvaBranding['fonts']['body'] ?? null) === $font['key'] ? 'selected' : '' }}>
                                    {{ $font['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-5 gap-1.5">
                    <label class="small-label text-center">
                        P
                        <input id="brandColorPrimary" type="color" class="h-7 w-full rounded-md border border-slate-200"
                            value="{{ $konvaBranding['colors']['primary'] ?? '#647A0B' }}">
                    </label>
                    <label class="small-label text-center">
                        S
                        <input id="brandColorSecondary" type="color" class="h-7 w-full rounded-md border border-slate-200"
                            value="{{ $konvaBranding['colors']['secondary'] ?? '#854F38' }}">
                    </label>
                    <label class="small-label text-center">
                        A
                        <input id="brandColorAccent" type="color" class="h-7 w-full rounded-md border border-slate-200"
                            value="{{ $konvaBranding['colors']['accent'] ?? '#D4A373' }}">
                    </label>
                    <label class="small-label text-center">
                        BG
                        <input id="brandColorBackground" type="color" class="h-7 w-full rounded-md border border-slate-200"
                            value="{{ $konvaBranding['colors']['background'] ?? '#F8F9F5' }}">
                    </label>
                    <label class="small-label text-center">
                        Txt
                        <input id="brandColorText" type="color" class="h-7 w-full rounded-md border border-slate-200"
                            value="{{ $konvaBranding['colors']['text'] ?? '#1F2937' }}">
                    </label>
                </div>

                <div class="flex flex-wrap gap-2 pt-1">
                    <button id="btnApplyBranding" type="button" class="pill-btn pill-btn-ghost">Appliquer</button>
                    <button id="btnSaveBranding" type="button" class="pill-btn pill-btn-main">Sauvegarder</button>
                    <button id="btnResetBranding" type="button" class="pill-btn pill-btn-ghost">Reset</button>
                </div>
                <p id="brandingStatusHint" class="text-[11px] text-slate-500">
                    Votre style est applique automatiquement aux templates.
                </p>
            </div>

            <details class="expert-only mt-3 rounded-xl border border-slate-200 bg-slate-50/80 p-2">
                <summary class="cursor-pointer text-[11px] font-semibold text-slate-700">
                    Reglages canvas avances
                </summary>

                <div class="mt-2">
                    <div class="small-label">Zoom</div>
                    <div class="range-row">
                        <input id="zoomSlider" type="range" min="40" max="140" value="100">
                        <div id="zoomValue" class="range-value">100%</div>
                    </div>
                </div>

                <div class="mt-3 border-t border-dashed border-slate-200 pt-3">
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

                <div class="mt-3 border-t border-dashed border-slate-200 pt-3">
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

                    <div class="mt-3">
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
            </details>
        </section>

        <section id="sectionHistory" class="toolbar-card glass-card">
            <div class="mb-2 flex items-center justify-between gap-2">
                <span class="toolbar-title">Etape 4 - Finalisation</span>
                <span class="badge-soft">Export</span>
            </div>

            <div class="flex flex-wrap gap-2">
                <button id="btnCenterSelection" type="button" class="pill-btn pill-btn-ghost">Centrer</button>
                <button id="btnDeleteSelection" type="button" class="pill-btn pill-btn-ghost">Supprimer</button>
                <button id="btnUndo" type="button" class="pill-btn pill-btn-ghost">Annuler</button>
                <button id="btnRedo" type="button" class="pill-btn pill-btn-ghost">Retablir</button>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
                <button id="btnExportInline" type="button" class="pill-btn pill-btn-main">
                    Exporter PNG
                </button>
                <button id="btnClearCanvasInline" type="button" class="pill-btn pill-btn-ghost">
                    Reinitialiser le canvas
                </button>
            </div>

            <p class="mt-3 text-[11px] leading-snug text-slate-500">
                Raccourcis: Ctrl/Cmd+Z, Ctrl/Cmd+Shift+Z, Suppr, fleches, Ctrl/Cmd+D.
            </p>
        </section>
    </div>
</aside>
