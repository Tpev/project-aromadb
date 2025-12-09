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
                @include('tools.konva.partials.left-sidebar')

                {{-- CENTER – Canvas --}}
                @include('tools.konva.partials.canvas')

                {{-- RIGHT SIDEBAR – Layers & Inspector --}}
                @include('tools.konva.partials.right-sidebar')
            </div>
        </div>
    </div>

    {{-- Konva core (global) --}}
    <script src="https://unpkg.com/konva@9/konva.min.js"></script>

<script>
// resources/js/konva-editor.js

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('konva-container');
    const wrapper   = document.getElementById('konva-wrapper');

    if (!container || !wrapper) {
        return;
    }

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

    const btnTemplateQuote        = document.getElementById('btnTemplateQuote');
    const btnTemplatePromo        = document.getElementById('btnTemplatePromo');
    const btnTemplateEvent        = document.getElementById('btnTemplateEvent');
    const btnTemplateTestimonial  = document.getElementById('btnTemplateTestimonial');
    const btnTemplateTip          = document.getElementById('btnTemplateTip');
    const btnTemplateBeforeAfter  = document.getElementById('btnTemplateBeforeAfter');
    const btnTemplateChecklist    = document.getElementById('btnTemplateChecklist');

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
	const eventSelector       = document.getElementById('eventSelector');

    // === Konva setup ===
    const size = wrapper.clientWidth;
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

    const transformer = new Konva.Transformer({
        name: 'mainTransformer',
        rotateEnabled: true,
        enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right'],
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

        elementMeta[id] = { id, type, name: finalName };
        refreshLayersList();
        saveHistory();
    }

    function removeElementMetaForNode(node) {
        const id = node.getAttr('amId');
        if (id && elementMeta[id]) {
            delete elementMeta[id];
        }
    }

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
        if (!textNode) return;

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
        const spacing = stage.width() / 10;
        const color = 'rgba(148, 163, 184, 0.4)';

        for (let i = 1; i < 10; i++) {
            gridLayer.add(new Konva.Line({
                points: [i * spacing, 0, i * spacing, stage.height()],
                stroke: color,
                strokeWidth: 0.5
            }));
            gridLayer.add(new Konva.Line({
                points: [0, i * spacing, stage.width(), i * spacing],
                stroke: color,
                strokeWidth: 0.5
            }));
        }

        gridLayer.draw();
    }

    function toggleGridVisibility() {
        if (toggleGrid && toggleGrid.checked) {
            drawGrid();
            gridLayer.visible(true);
        } else {
            gridLayer.visible(false);
        }
        gridLayer.draw();
    }

    if (toggleGrid) {
        toggleGrid.addEventListener('change', () => {
            toggleGridVisibility();
            saveHistory();
        });
    }

    // === Background ===
    if (bgColorPicker) {
        bgColorPicker.addEventListener('input', (e) => {
            bgRect.fill(e.target.value);
            backgroundLayer.draw();
        });

        bgColorPicker.addEventListener('change', () => {
            saveHistory();
        });
    }

    if (btnResetBg) {
        btnResetBg.addEventListener('click', () => {
            bgRect.fill('#f9fafb');
            backgroundLayer.draw();
            if (bgColorPicker) bgColorPicker.value = '#f9fafb';
            saveHistory();
        });
    }

    document.querySelectorAll('[data-bg]').forEach(btn => {
        btn.addEventListener('click', () => {
            const color = btn.getAttribute('data-bg');
            bgRect.fill(color);
            backgroundLayer.draw();
            if (bgColorPicker) bgColorPicker.value = color;
            saveHistory();
        });
    });

    // === Zoom ===
    function applyZoom(value) {
        const scale = value / 100;
        if (zoomValue) zoomValue.textContent = value + '%';
        stage.scale({ x: scale, y: scale });

        stage.position({
            x: (stage.width() - stage.width() * scale) / 2,
            y: (stage.height() - stage.height() * scale) / 2
        });

        stage.batchDraw();
    }

    if (zoomSlider) {
        zoomSlider.addEventListener('input', (e) => {
            applyZoom(parseInt(e.target.value, 10));
        });

        zoomSlider.addEventListener('change', () => {
            saveHistory();
        });
    }

    window.addEventListener('resize', () => {
        const newSize = wrapper.clientWidth;
        const scale = newSize / stage.width();

        stage.width(newSize);
        stage.height(newSize);

        bgRect.width(newSize);
        bgRect.height(newSize);
        backgroundLayer.draw();

        if (toggleGrid && toggleGrid.checked) drawGrid();

        stage.scale({ x: stage.scaleX() * scale, y: stage.scaleY() * scale });
        stage.draw();
    });

    // === Image ===
    if (imageUpload) {
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
                        width,
                        height,
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
            e.target.value = '';
        });
    }

    // === Text ===
    if (btnAddText) {
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
    }

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
            if (textarea.parentNode) textarea.parentNode.removeChild(textarea);
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
    if (btnAddRect) {
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
    }

    if (btnAddCircle) {
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
    }

    // === Stage click select ===
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
    if (btnCenterSelection) {
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
    }

    // === Delete ===
    if (btnDeleteSelection) {
        btnDeleteSelection.addEventListener('click', () => {
            if (!selectedNode) return;
            removeElementMetaForNode(selectedNode);
            selectedNode.destroy();
            clearSelection();
            mainLayer.draw();
            saveHistory();
        });
    }

    // === Clear canvas ===
    if (btnClearCanvas) {
        btnClearCanvas.addEventListener('click', () => {
            mainLayer.destroyChildren();
            mainLayer.add(transformer);
            Object.keys(elementMeta).forEach(k => delete elementMeta[k]);
            clearSelection();

            bgRect.fill('#f9fafb');
            backgroundLayer.draw();
            if (bgColorPicker) bgColorPicker.value = '#f9fafb';

            gridLayer.destroyChildren();
            if (toggleGrid) toggleGrid.checked = false;
            gridLayer.visible(false);

            if (zoomSlider) zoomSlider.value = 100;
            applyZoom(100);

            saveHistory();
        });
    }

    // === Undo ===
    if (btnUndo) {
        btnUndo.addEventListener('click', () => {
            if (history.length < 2) return;
            history.pop();
            const previous = history[history.length - 1];
            // simple approach: reload
            window.location.reload();
        });
    }

    // === Layers list + inspector ===
    function refreshLayersList() {
        if (!layersList) return;
        layersList.innerHTML = '';

        const children = mainLayer.getChildren(n => n !== transformer);

        const ordered = children
            .map(node => {
                const id = node.getAttr('amId');
                const meta = elementMeta[id] || null;
                return { node, id, meta };
            })
            .filter(item => item.id);

        if (ordered.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'text-[11px] text-slate-400';
            empty.textContent = 'Aucun calque pour le moment. Ajoutez du texte, une forme ou une image.';
            layersList.appendChild(empty);
            return;
        }

        ordered.forEach((item, idx) => {
            const node = item.node;
            const meta = item.meta || {};
            const typeAttr = meta.type || node.getAttr('amType') || node.getClassName();

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
            label.textContent = meta.name || node.getAttr('amName') || ('Calque ' + (idx + 1));
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

            btn.addEventListener('click', () => setSelection(node));

            layersList.appendChild(btn);
        });
    }

    function updateInspector() {
        if (!selectionPanel || !selectionTypeBadge) return;

        if (!selectedNode) {
            selectionPanel.classList.add('hidden');
            if (noSelectionHint) noSelectionHint.classList.remove('hidden');
            selectionTypeBadge.textContent = 'Aucun élément';
            return;
        }

        selectionPanel.classList.remove('hidden');
        if (noSelectionHint) noSelectionHint.classList.add('hidden');

        const id = selectedNode.getAttr('amId');
        const meta = elementMeta[id] || {};
        const typeAttr = meta.type || selectedNode.getAttr('amType') || selectedNode.getClassName();
        let type = typeAttr;

        let badgeLabel = 'Élément';
        if (type === 'image') badgeLabel = 'Image';
        else if (type === 'text') badgeLabel = 'Texte';
        else if (type === 'shape-rect') badgeLabel = 'Rectangle';
        else if (type === 'shape-circle') badgeLabel = 'Cercle';
        selectionTypeBadge.textContent = badgeLabel;

        if (inputLayerName) {
            inputLayerName.value = meta.name || selectedNode.getAttr('amName') || '';
        }

        const opacity = Math.round((selectedNode.opacity() || 1) * 100);
        if (inputOpacity) inputOpacity.value = opacity;
        if (opacityValue) opacityValue.textContent = opacity + '%';

        if (shapeControls) shapeControls.classList.add('hidden');
        if (textControls)  textControls.classList.add('hidden');
        if (imageControls) imageControls.classList.add('hidden');

        if ((type === 'shape-rect' || type === 'shape-circle') && shapeControls) {
            shapeControls.classList.remove('hidden');

            let fill = selectedNode.fill() || '#e5f0c8';
            if (shapeFillInput) {
                if (typeof fill === 'string' && /^#([0-9A-F]{3}){1,2}$/i.test(fill)) {
                    shapeFillInput.value = fill;
                } else {
                    shapeFillInput.value = '#e5f0c8';
                }
            }

            let stroke = selectedNode.stroke() || '#647a0b';
            if (shapeStrokeInput) {
                if (typeof stroke === 'string' && /^#([0-9A-F]{3}){1,2}$/i.test(stroke)) {
                    shapeStrokeInput.value = stroke;
                } else {
                    shapeStrokeInput.value = '#647a0b';
                }
            }

            const sw = selectedNode.strokeWidth() || 1;
            if (shapeStrokeWidth) shapeStrokeWidth.value = sw;
            if (strokeWidthValue) strokeWidthValue.textContent = sw + ' px';
        }

        if (type === 'text' && textControls) {
            textControls.classList.remove('hidden');

            const textNode = selectedNode.findOne('Text') || selectedNode;

            if (textFontFamily) {
                const family = textNode.fontFamily() || '';
                if (family.includes('Playfair')) {
                    textFontFamily.value = 'serif';
                } else if (family.includes('Raleway')) {
                    textFontFamily.value = 'sans';
                } else {
                    textFontFamily.value = 'system';
                }
            }

            if (textFontSize) {
                textFontSize.value = Math.round(textNode.fontSize() || 26);
            }

            if (btnToggleBold) {
                const style = textNode.fontStyle ? textNode.fontStyle() : '';
                if (style && style.includes('bold')) {
                    btnToggleBold.classList.add('is-active');
                } else {
                    btnToggleBold.classList.remove('is-active');
                }
            }

            let fillText = textNode.fill() || '#111827';
            if (textColor) {
                if (typeof fillText === 'string' && /^#([0-9A-F]{3}){1,2}$/i.test(fillText)) {
                    textColor.value = fillText;
                } else {
                    textColor.value = '#111827';
                }
            }
        }

        if (type === 'image' && imageControls) {
            imageControls.classList.remove('hidden');
            const img = selectedNode;
            img.cache();
            img.filters([Konva.Filters.Brighten, Konva.Filters.Contrast]);
            if (imgBrightness) imgBrightness.value = img.brightness() || 0;
            if (imgContrast)   imgContrast.value   = img.contrast()   || 0;
        }
    }

    // Inspector events
    if (inputLayerName) {
        inputLayerName.addEventListener('input', () => {
            if (!selectedNode) return;
            const id = selectedNode.getAttr('amId');
            if (!id) return;
            elementMeta[id] = elementMeta[id] || {};
            elementMeta[id].name = inputLayerName.value;
            selectedNode.setAttr('amName', inputLayerName.value);
            refreshLayersList();
        });
    }

    if (inputOpacity) {
        inputOpacity.addEventListener('input', () => {
            if (!selectedNode) return;
            const v = parseInt(inputOpacity.value, 10) || 100;
            selectedNode.opacity(v / 100);
            if (opacityValue) opacityValue.textContent = v + '%';
            mainLayer.batchDraw();
        });
        inputOpacity.addEventListener('change', () => saveHistory());
    }

    if (shapeFillInput) {
        shapeFillInput.addEventListener('input', () => {
            if (!selectedNode) return;
            selectedNode.fill(shapeFillInput.value);
            mainLayer.batchDraw();
        });
        shapeFillInput.addEventListener('change', () => saveHistory());
    }

    if (shapeStrokeInput) {
        shapeStrokeInput.addEventListener('input', () => {
            if (!selectedNode) return;
            selectedNode.stroke(shapeStrokeInput.value);
            mainLayer.batchDraw();
        });
        shapeStrokeInput.addEventListener('change', () => saveHistory());
    }

    if (shapeStrokeWidth) {
        shapeStrokeWidth.addEventListener('input', () => {
            if (!selectedNode) return;
            const sw = parseInt(shapeStrokeWidth.value, 10) || 0;
            selectedNode.strokeWidth(sw);
            if (strokeWidthValue) strokeWidthValue.textContent = sw + ' px';
            mainLayer.batchDraw();
        });
        shapeStrokeWidth.addEventListener('change', () => saveHistory());
    }

    if (textFontFamily) {
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
    }

    if (textFontSize) {
        textFontSize.addEventListener('change', () => {
            withTextNode((textNode) => {
                const size = parseInt(textFontSize.value, 10) || 26;
                textNode.fontSize(size);
            });
        });
    }

    if (btnToggleBold) {
        btnToggleBold.addEventListener('click', () => {
            withTextNode((textNode) => {
                const current = textNode.fontStyle ? textNode.fontStyle() : 'normal';
                const newStyle = current.includes('bold') ? 'normal' : 'bold';
                if (textNode.fontStyle) textNode.fontStyle(newStyle);
                btnToggleBold.classList.toggle('is-active');
            });
        });
    }

    if (textColor) {
        textColor.addEventListener('input', () => {
            withTextNode((textNode) => textNode.fill(textColor.value));
        });
        textColor.addEventListener('change', () => saveHistory());
    }

    if (btnAlignLeft) {
        btnAlignLeft.addEventListener('click', () => {
            withTextNode((t) => t.align('left'));
        });
    }
    if (btnAlignCenter) {
        btnAlignCenter.addEventListener('click', () => {
            withTextNode((t) => t.align('center'));
        });
    }
    if (btnAlignRight) {
        btnAlignRight.addEventListener('click', () => {
            withTextNode((t) => t.align('right'));
        });
    }

    if (imgBrightness) {
        imgBrightness.addEventListener('input', () => {
            if (!selectedNode) return;
            selectedNode.brightness(parseFloat(imgBrightness.value));
            mainLayer.batchDraw();
        });
        imgBrightness.addEventListener('change', () => saveHistory());
    }

    if (imgContrast) {
        imgContrast.addEventListener('input', () => {
            if (!selectedNode) return;
            selectedNode.contrast(parseFloat(imgContrast.value));
            mainLayer.batchDraw();
        });
        imgContrast.addEventListener('change', () => saveHistory());
    }

    // Z-order + duplicate
    function updateZ(action) {
        if (!selectedNode) return;

        if (action === 'top') selectedNode.moveToTop();
        else if (action === 'bottom') {
            selectedNode.moveToBottom();
            transformer.moveToTop();
        } else if (action === 'up') selectedNode.moveUp();
        else if (action === 'down') selectedNode.moveDown();

        transformer.moveToTop();
        mainLayer.draw();
        refreshLayersList();
        saveHistory();
    }

    if (btnZTop)    btnZTop.addEventListener('click', () => updateZ('top'));
    if (btnZBottom) btnZBottom.addEventListener('click', () => updateZ('bottom'));
    if (btnZUp)     btnZUp.addEventListener('click', () => updateZ('up'));
    if (btnZDown)   btnZDown.addEventListener('click', () => updateZ('down'));

    if (btnDuplicate) {
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
            registerElement(
                clone,
                meta.type || selectedNode.getAttr('amType') || 'element',
                (meta.name || 'Élément') + ' (copie)'
            );
            setSelection(clone);
        });
    }

    // === Templates base (existing) ===
    function addQuoteTemplate() {
        clearCanvasKeepBg();

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
        const quoteGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
        quoteGroup.add(quoteText);
        mainLayer.add(quoteGroup);
        registerElement(quoteGroup, 'text', 'Citation');

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
        const authorGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
        authorGroup.add(authorText);
        mainLayer.add(authorGroup);
        registerElement(authorGroup, 'text', 'Auteur');

        mainLayer.draw();
    }

    function addPromoTemplate() {
        clearCanvasKeepBg();

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

function addEventTemplate(eventData = null) {
    clearCanvasKeepBg();

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

    const dateTextStr = eventData && eventData.date
        ? eventData.date
        : 'SAM\n12 OCT';

    const dateText = new Konva.Text({
        text: dateTextStr,
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

    const titleText = new Konva.Text({
        text: (eventData && eventData.title) || 'Atelier : Découvrir les huiles essentielles',
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

    const infoLines = [];
    if (eventData && eventData.date) infoLines.push(eventData.date);
    if (eventData && eventData.location) infoLines.push(eventData.location);

    const subText = new Konva.Text({
        text: infoLines.length
            ? infoLines.join('\n')
            : 'En ligne ou en cabinet · 2h de pratique\nPlaces limitées · Réservation obligatoire',
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
        text: (eventData && eventData.url) || 'Réserver sur aromamade.com/pro/votrenom',
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
function applyEventToEventTemplate(eventData) {
    // Si la maquette n’est pas encore sur le canvas, on la crée avec ces données
    let titleGroup = mainLayer.findOne(n => n.getAttr && n.getAttr('amName') === 'Titre atelier');

    if (!titleGroup) {
        addEventTemplate(eventData);
        return;
    }

    // Sinon, on met juste à jour les différents blocs
    const titleTextNode = titleGroup.findOne('Text');
    if (titleTextNode && eventData.title) {
        titleTextNode.text(eventData.title);
    }

    const infoGroup = mainLayer.findOne(n => n.getAttr && n.getAttr('amName') === 'Infos atelier');
    const infoTextNode = infoGroup && infoGroup.findOne('Text');

    if (infoTextNode) {
        const infoLines = [];
        if (eventData.date) infoLines.push(eventData.date);
        if (eventData.location) infoLines.push(eventData.location);
        if (infoLines.length) {
            infoTextNode.text(infoLines.join('\n'));
        }
    }

    const dateGroup = mainLayer.findOne(n => n.getAttr && n.getAttr('amName') === 'Texte date');
    const dateTextNode = dateGroup && dateGroup.findOne('Text');
    if (dateTextNode && eventData.date) {
        // Simple: on met la date sur une seule ligne
        dateTextNode.text(eventData.date);
    }

    const urlGroup = mainLayer.findOne(n => n.getAttr && n.getAttr('amName') === 'URL');
    const urlTextNode = urlGroup && urlGroup.findOne('Text');
    if (urlTextNode && eventData.url) {
        urlTextNode.text(eventData.url);
    }

    mainLayer.batchDraw();
    saveHistory();
}


    // ⭐ Avis client
    function addTestimonialTemplate() {
        clearCanvasKeepBg();

        const card = new Konva.Rect({
            x: stage.width() * 0.1,
            y: stage.height() * 0.16,
            width: stage.width() * 0.8,
            height: stage.height() * 0.6,
            cornerRadius: 32,
            fill: '#ffffff',
            stroke: '#e5e7eb',
            strokeWidth: 1
        });
        mainLayer.add(card);
        registerElement(card, 'shape-rect', 'Carte avis');

        const avatarCircle = new Konva.Circle({
            x: stage.width() * 0.5,
            y: stage.height() * 0.23,
            radius: 36,
            fill: '#e5f0c8',
            stroke: '#647a0b',
            strokeWidth: 1.5
        });
        mainLayer.add(avatarCircle);
        registerElement(avatarCircle, 'shape-circle', 'Avatar');

        const nameText = new Konva.Text({
            text: 'Prénom Nom',
            x: stage.width() * 0.2,
            y: stage.height() * 0.3,
            width: stage.width() * 0.6,
            fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
            fontSize: 18,
            fill: '#111827',
            align: 'center'
        });
        const nameGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
        nameGroup.add(nameText);
        mainLayer.add(nameGroup);
        registerElement(nameGroup, 'text', 'Nom client');

        const roleText = new Konva.Text({
            text: 'Cliente accompagnée en naturopathie',
            x: stage.width() * 0.2,
            y: stage.height() * 0.34,
            width: stage.width() * 0.6,
            fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
            fontSize: 14,
            fill: '#6b7280',
            align: 'center'
        });
        const roleGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
        roleGroup.add(roleText);
        mainLayer.add(roleGroup);
        registerElement(roleGroup, 'text', 'Rôle client');

        const bodyText = new Konva.Text({
            text: '« J’ai retrouvé de l’énergie et des habitudes plus saines au quotidien. ' +
                  'L’accompagnement m’a permis de passer à l’action en douceur. »',
            x: stage.width() * 0.15,
            y: stage.height() * 0.43,
            width: stage.width() * 0.7,
            fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
            fontSize: 16,
            fill: '#111827',
            align: 'left',
            lineHeight: 1.4
        });
        const bodyGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
        bodyGroup.add(bodyText);
        mainLayer.add(bodyGroup);
        registerElement(bodyGroup, 'text', 'Texte avis');

        mainLayer.draw();
    }

    // 🌿 Astuce bien-être
    function addTipTemplate() {
        clearCanvasKeepBg();

        const band = new Konva.Rect({
            x: stage.width() * 0.07,
            y: stage.height() * 0.1,
            width: stage.width() * 0.86,
            height: stage.height() * 0.2,
            cornerRadius: 999,
            fill: '#ecfccb'
        });
        mainLayer.add(band);
        registerElement(band, 'shape-rect', 'Bande astuce');

        const titleText = new Konva.Text({
            text: 'Astuce bien-être du jour',
            x: stage.width() * 0.1,
            y: stage.height() * 0.15,
            width: stage.width() * 0.8,
            fontFamily: '"Playfair Display", "Times New Roman", serif',
            fontSize: 24,
            fill: '#365314',
            align: 'center'
        });
        const titleGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
        titleGroup.add(titleText);
        mainLayer.add(titleGroup);
        registerElement(titleGroup, 'text', 'Titre astuce');

        const bodyText = new Konva.Text({
            text: '🕯️ Inspirez 5 minutes un mélange doux (lavande vraie, orange douce) ' +
                  'avant le coucher pour aider votre système nerveux à se détendre.\n\n' +
                  '⚠️ Toujours diluer les huiles et demander conseil à un professionnel.',
            x: stage.width() * 0.12,
            y: stage.height() * 0.35,
            width: stage.width() * 0.76,
            fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
            fontSize: 16,
            fill: '#111827',
            align: 'left',
            lineHeight: 1.4
        });
        const bodyGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
        bodyGroup.add(bodyText);
        mainLayer.add(bodyGroup);
        registerElement(bodyGroup, 'text', 'Texte astuce');

        mainLayer.draw();
    }

    // 🔁 Avant / Après
    function addBeforeAfterTemplate() {
        clearCanvasKeepBg();

        const splitRect = new Konva.Rect({
            x: stage.width() * 0.06,
            y: stage.height() * 0.13,
            width: stage.width() * 0.88,
            height: stage.height() * 0.64,
            cornerRadius: 32,
            fill: '#ffffff',
            stroke: '#e5e7eb',
            strokeWidth: 1
        });
        mainLayer.add(splitRect);
        registerElement(splitRect, 'shape-rect', 'Bloc avant/après');

        const middleLine = new Konva.Line({
            points: [
                stage.width() * 0.5,
                stage.height() * 0.16,
                stage.width() * 0.5,
                stage.height() * 0.74
            ],
            stroke: '#e5e7eb',
            strokeWidth: 2,
            dash: [6, 4]
        });
        mainLayer.add(middleLine);
        registerElement(middleLine, 'shape-rect', 'Séparateur');

        const beforeText = new Konva.Text({
            text: 'Avant',
            x: stage.width() * 0.11,
            y: stage.height() * 0.16,
            width: stage.width() * 0.33,
            fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
            fontSize: 16,
            fill: '#6b7280',
            align: 'center'
        });
        const beforeGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
        beforeGroup.add(beforeText);
        mainLayer.add(beforeGroup);
        registerElement(beforeGroup, 'text', 'Label avant');

        const afterText = new Konva.Text({
            text: 'Après',
            x: stage.width() * 0.56,
            y: stage.height() * 0.16,
            width: stage.width() * 0.33,
            fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
            fontSize: 16,
            fill: '#6b7280',
            align: 'center'
        });
        const afterGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
        afterGroup.add(afterText);
        mainLayer.add(afterGroup);
        registerElement(afterGroup, 'text', 'Label après');

        const bottomText = new Konva.Text({
            text: 'Glissez vos photos ou illustrez le changement (habitudes, énergie, peau...).',
            x: stage.width() * 0.1,
            y: stage.height() * 0.78,
            width: stage.width() * 0.8,
            fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
            fontSize: 14,
            fill: '#4b5563',
            align: 'center'
        });
        const bottomGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
        bottomGroup.add(bottomText);
        mainLayer.add(bottomGroup);
        registerElement(bottomGroup, 'text', 'Texte bas');

        mainLayer.draw();
    }

    // ✅ Checklist
    function addChecklistTemplate() {
        clearCanvasKeepBg();

        const card = new Konva.Rect({
            x: stage.width() * 0.08,
            y: stage.height() * 0.12,
            width: stage.width() * 0.84,
            height: stage.height() * 0.7,
            cornerRadius: 32,
            fill: '#ffffff',
            stroke: '#e5e7eb',
            strokeWidth: 1
        });
        mainLayer.add(card);
        registerElement(card, 'shape-rect', 'Carte checklist');

        const titleText = new Konva.Text({
            text: 'Routine bien-être du soir',
            x: stage.width() * 0.12,
            y: stage.height() * 0.16,
            width: stage.width() * 0.76,
            fontFamily: '"Playfair Display", "Times New Roman", serif',
            fontSize: 24,
            fill: '#111827',
            align: 'left'
        });
        const titleGroup = new Konva.Group({ x: 0, y: 0, draggable: true });
        titleGroup.add(titleText);
        mainLayer.add(titleGroup);
        registerElement(titleGroup, 'text', 'Titre checklist');

        const items = [
            'Boire un grand verre d’eau',
            'Couper les écrans 30 minutes avant le coucher',
            'Respirer profondément avec une huile relaxante',
            'Noter 3 gratitudes du jour'
        ];

        items.forEach((txt, i) => {
            const y = stage.height() * 0.24 + i * 40;

            const box = new Konva.Rect({
                x: stage.width() * 0.12,
                y,
                width: 18,
                height: 18,
                cornerRadius: 4,
                stroke: '#9ca3af',
                strokeWidth: 1
            });
            mainLayer.add(box);
            registerElement(box, 'shape-rect', 'Case ' + (i + 1));

            const text = new Konva.Text({
                text: txt,
                x: stage.width() * 0.16,
                y: y - 2,
                width: stage.width() * 0.7,
                fontFamily: '"Raleway", system-ui, -apple-system, sans-serif',
                fontSize: 15,
                fill: '#111827',
                align: 'left'
            });
            const group = new Konva.Group({ x: 0, y: 0, draggable: true });
            group.add(text);
            mainLayer.add(group);
            registerElement(group, 'text', 'Item ' + (i + 1));
        });

        mainLayer.draw();
    }

    // === Hook template buttons ===
    if (btnTemplateQuote)       btnTemplateQuote.addEventListener('click', addQuoteTemplate);
    if (btnTemplatePromo)       btnTemplatePromo.addEventListener('click', addPromoTemplate);
    if (btnTemplateEvent)       btnTemplateEvent.addEventListener('click', addEventTemplate);
    if (btnTemplateTestimonial) btnTemplateTestimonial.addEventListener('click', addTestimonialTemplate);
    if (btnTemplateTip)         btnTemplateTip.addEventListener('click', addTipTemplate);
    if (btnTemplateBeforeAfter) btnTemplateBeforeAfter.addEventListener('click', addBeforeAfterTemplate);
    if (btnTemplateChecklist)   btnTemplateChecklist.addEventListener('click', addChecklistTemplate);


    // === Keyboard shortcuts ===
    document.addEventListener('keydown', (e) => {
        const tag = (document.activeElement && document.activeElement.tagName || '').toLowerCase();
        if (['input', 'textarea', 'select'].includes(tag)) return;

        if ((e.key === 'Delete' || e.key === 'Backspace') && selectedNode) {
            e.preventDefault();
            if (btnDeleteSelection) btnDeleteSelection.click();
        }

        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'd' && selectedNode) {
            e.preventDefault();
            if (btnDuplicate) btnDuplicate.click();
        }

        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
            e.preventDefault();
            if (btnUndo) btnUndo.click();
        }

        if (!selectedNode) return;

        const step = e.shiftKey ? 10 : 2;
        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
            e.preventDefault();
            const pos = selectedNode.position();
            if (e.key === 'ArrowUp')    selectedNode.y(pos.y - step);
            if (e.key === 'ArrowDown')  selectedNode.y(pos.y + step);
            if (e.key === 'ArrowLeft')  selectedNode.x(pos.x - step);
            if (e.key === 'ArrowRight') selectedNode.x(pos.x + step);
            mainLayer.batchDraw();
            saveHistory();
        }
    });

    // === Export ===
    if (btnExport) {
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
    }
if (eventSelector) {
    eventSelector.addEventListener('change', () => {
        const option = eventSelector.options[eventSelector.selectedIndex];
        if (!option || !option.value) return;

        const eventData = {
            title: option.dataset.title || '',
            date: option.dataset.date || '',
            location: option.dataset.location || '',
            url: option.dataset.url || ''
        };

        applyEventToEventTemplate(eventData);
    });
}

    // Initial state
    toggleGridVisibility();
    applyZoom(100);
    refreshLayersList();
    saveHistory();
});




</script>
</x-app-layout>
