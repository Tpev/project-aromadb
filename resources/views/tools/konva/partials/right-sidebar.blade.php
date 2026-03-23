{{-- resources/views/tools/konva/partials/right-sidebar.blade.php --}}
<aside class="editor-column editor-right">
    <div class="sidebar-stack space-y-4">
        <div class="toolbar-card glass-card">
            <div class="mb-2 flex items-center justify-between">
                <span class="toolbar-title">Inspection contextuelle</span>
                <span class="badge-soft">Selection</span>
            </div>
            <p class="text-[11px] text-slate-500">
                En mode rapide, gardez uniquement les reglages utiles. Activez le mode expert pour les options avancees.
            </p>
        </div>

        <details id="layersPanelDetails" class="toolbar-card glass-card expert-only" open>
            <summary class="cursor-pointer list-none">
                <div class="flex items-center justify-between">
                    <span class="toolbar-title">Calques</span>
                    <span class="badge-soft">Z-order</span>
                </div>
            </summary>

            <div id="layersList" class="layer-list space-y-1 text-[12px] text-slate-700 mt-2">
                {{-- Filled by JS --}}
            </div>
        </details>

        <div class="toolbar-card glass-card">
            <div class="mb-2 flex items-center justify-between">
                <span class="toolbar-title">Proprietes</span>
                <span id="selectionTypeBadge"
                    class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                    Aucun element
                </span>
            </div>

            <p id="noSelectionHint" class="text-[11px] text-slate-500">
                Selectionnez un element du canvas.
            </p>

            <div id="selectionPanel" class="space-y-3 hidden">
                <div>
                    <div class="small-label">Nom du calque</div>
                    <input id="inputLayerName" type="text"
                        class="small-input"
                        placeholder="Titre principal, Image 1, etc.">
                </div>

                <div>
                    <div class="small-label">Opacite</div>
                    <div class="range-row">
                        <input id="inputOpacity" type="range" min="20" max="100" value="100">
                        <div id="opacityValue" class="range-value">100%</div>
                    </div>
                </div>

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
                        <div class="small-label">Epaisseur contour</div>
                        <div class="range-row">
                            <input id="shapeStrokeWidth" type="range" min="0" max="10" value="1">
                            <div id="strokeWidthValue" class="range-value">1 px</div>
                        </div>
                    </div>
                </div>

                <div id="textControls" class="space-y-2 border-t border-dashed border-slate-200 pt-2 hidden">
                    <div class="small-label">Police et style</div>
                    <div class="flex items-center gap-2">
                        @php($konvaBrandingFonts = $konvaBrandingFonts ?? config('konva.branding_fonts', []))
                        <select id="textFontFamily" class="small-select">
                            @foreach($konvaBrandingFonts as $font)
                                <option value="{{ $font['key'] }}">{{ $font['label'] }}</option>
                            @endforeach
                        </select>
                        <div class="flex items-center gap-1">
                            <span class="small-label" style="margin-bottom:0;">Taille</span>
                            <input id="textFontSize" type="number" min="10" max="120" value="26"
                                class="small-input" style="width:4rem;">
                        </div>
                        <button id="btnToggleBold" type="button" class="z-order-btn" title="Gras / Normal">B</button>
                    </div>

                    <div class="flex items-center gap-2 mt-1">
                        <span class="small-label" style="margin-bottom:0;">Alignement</span>
                        <button type="button" class="z-order-btn text-[10px]" id="btnAlignLeft">G</button>
                        <button type="button" class="z-order-btn text-[10px]" id="btnAlignCenter">C</button>
                        <button type="button" class="z-order-btn text-[10px]" id="btnAlignRight">D</button>
                    </div>

                    <div class="flex items-center gap-2 mt-1">
                        <div class="flex flex-col flex-1 gap-1">
                            <span class="small-label">Couleur texte</span>
                            <input id="textColor" type="color"
                                class="h-7 w-full rounded-md border border-slate-200 bg-white">
                        </div>
                    </div>
                </div>

                <div id="imageControls" class="space-y-2 border-t border-dashed border-slate-200 pt-2 hidden">
                    <div class="small-label">Image</div>
                    <div class="range-row">
                        <span class="small-label" style="margin-bottom:0;">Luminosite</span>
                        <input id="imgBrightness" type="range" min="-1" max="1" step="0.05" value="0">
                    </div>
                    <div class="range-row">
                        <span class="small-label" style="margin-bottom:0;">Contraste</span>
                        <input id="imgContrast" type="range" min="-1" max="1" step="0.05" value="0">
                    </div>
                    <div>
                        <div class="small-label">Mode d'ajustement</div>
                        <select id="imgFitMode" class="small-select">
                            <option value="cover">Cover</option>
                            <option value="contain">Contain</option>
                            <option value="stretch">Stretch</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="btnReplaceImage" type="button" class="z-order-btn">Remplacer</button>
                        <input id="imageReplaceUpload" type="file" accept="image/*" class="hidden">
                    </div>
                </div>

                <div class="expert-only border-t border-dashed border-slate-200 pt-2">
                    <div class="small-label mb-1">Ordre des calques et actions</div>
                    <div class="flex flex-wrap gap-1.5 mb-1.5">
                        <button id="btnZTop" type="button" class="z-order-btn" title="Amener tout devant">Avant-plan</button>
                        <button id="btnZUp" type="button" class="z-order-btn" title="Monter d'un niveau">Monter</button>
                        <button id="btnZDown" type="button" class="z-order-btn" title="Descendre d'un niveau">Descendre</button>
                        <button id="btnZBottom" type="button" class="z-order-btn" title="Mettre tout derriere">Arriere-plan</button>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <button id="btnDuplicate" type="button" class="z-order-btn" title="Dupliquer l'element">Dupliquer</button>
                        <button id="btnToggleLock" type="button" class="z-order-btn" title="Verrouiller ou deverrouiller">Verrouiller</button>
                        <button id="btnToggleVisibility" type="button" class="z-order-btn" title="Afficher ou masquer">Masquer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>
