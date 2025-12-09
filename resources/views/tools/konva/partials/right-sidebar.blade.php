{{-- resources/views/tools/konva/partials/right-sidebar.blade.php --}}
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
