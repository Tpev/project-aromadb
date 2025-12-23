{{-- resources/views/tools/konva/partials/scripts.blade.php --}}
<script src="https://unpkg.com/konva@9/konva.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('konva-container');
    const wrapper   = document.getElementById('konva-wrapper');
    if (!container || !wrapper) return;

    const FORMATS   = @json(config('konva.formats', []));
    const TEMPLATES = @json(config('konva.templates', []));

    // Top bar
    const btnChooseFormat  = document.getElementById('btnChooseFormat');
    const btnExport        = document.getElementById('btnExport');
    const btnClearCanvas   = document.getElementById('btnClearCanvas');

    // Modal
    const formatModal          = document.getElementById('formatModal');
    const formatsGrid          = document.getElementById('formatsGrid');
    const btnCloseFormatModal  = document.getElementById('btnCloseFormatModal');

    // Left sidebar controls
    const formatBadge        = document.getElementById('formatBadge');
    const imageUpload        = document.getElementById('imageUpload');
    const btnAddText         = document.getElementById('btnAddText');
    const btnAddRect         = document.getElementById('btnAddRect');
    const btnAddCircle       = document.getElementById('btnAddCircle');
    const btnCenterSelection = document.getElementById('btnCenterSelection');
    const btnDeleteSelection = document.getElementById('btnDeleteSelection');

    const zoomSlider     = document.getElementById('zoomSlider');
    const zoomValue      = document.getElementById('zoomValue');

    const bgColorPicker  = document.getElementById('bgColorPicker');
    const btnResetBg     = document.getElementById('btnResetBg');
    const toggleGrid     = document.getElementById('toggleGrid');
    const btnUndo        = document.getElementById('btnUndo');
	
	const btnToggleShapesDrawer = document.getElementById('btnToggleShapesDrawer');
	const shapesDrawer = document.getElementById('shapesDrawer');
	const shapesDrawerChevron = document.getElementById('shapesDrawerChevron');


    // Right sidebar
    const layersList         = document.getElementById('layersList');

    const selectionPanel     = document.getElementById('selectionPanel');
    const noSelectionHint    = document.getElementById('noSelectionHint');
    const selectionTypeBadge = document.getElementById('selectionTypeBadge');

    const inputLayerName     = document.getElementById('inputLayerName');
    const inputOpacity       = document.getElementById('inputOpacity');
    const opacityValue       = document.getElementById('opacityValue');

    const shapeControls      = document.getElementById('shapeControls');
    const shapeFill          = document.getElementById('shapeFill');
    const shapeStroke        = document.getElementById('shapeStroke');
    const shapeStrokeWidth   = document.getElementById('shapeStrokeWidth');
    const strokeWidthValue   = document.getElementById('strokeWidthValue');

    const textControls       = document.getElementById('textControls');
    const textFontFamily     = document.getElementById('textFontFamily');
    const textFontSize       = document.getElementById('textFontSize');
    const btnToggleBold      = document.getElementById('btnToggleBold');
    const btnAlignLeft       = document.getElementById('btnAlignLeft');
    const btnAlignCenter     = document.getElementById('btnAlignCenter');
    const btnAlignRight      = document.getElementById('btnAlignRight');
    const textColor          = document.getElementById('textColor');

    const imageControls      = document.getElementById('imageControls');
    const imgBrightness      = document.getElementById('imgBrightness');
    const imgContrast        = document.getElementById('imgContrast');

    const btnZTop            = document.getElementById('btnZTop');
    const btnZUp             = document.getElementById('btnZUp');
    const btnZDown           = document.getElementById('btnZDown');
    const btnZBottom         = document.getElementById('btnZBottom');
    const btnDuplicate       = document.getElementById('btnDuplicate');

    // --- State
    let stage = null, backgroundLayer = null, gridLayer = null, mainLayer = null, transformer = null, bgRect = null;
    let selectedFormat = null;
    let selectedNode   = null;
    let resizeObserver = null;
    let userZoom = 1; // 0.4 -> 1.4 display only

    let history = [];
    let elementCounter = 0;

    // --- helpers
    const clamp = (n, a, b) => Math.max(a, Math.min(b, n));
    const escapeHtml = (str) => String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));

    function openFormatModal(){ formatModal?.classList.remove('hidden'); }
    function closeFormatModal(){ formatModal?.classList.add('hidden'); }

    function ensureFormatChosen() {
        if (!selectedFormat) { openFormatModal(); return false; }
        return true;
    }

function enableTextEditing(stage, layer) {
    function editTextNode(textNode) {
        if (!textNode || textNode.getAttr('amType') !== 'text') return;

        const absPos = textNode.getAbsolutePosition();
        const stageBox = stage.container().getBoundingClientRect();

        // If you use CSS transform scaling in wrapper, we must account for it.
        // We can read the current scale from the konvajs-content transform.
        const content = wrapper.querySelector('.konvajs-content');
        let cssScale = 1;
        if (content) {
            const t = window.getComputedStyle(content).transform;
            // matrix(a, b, c, d, e, f) -> a is scaleX when no rotation/shear
            if (t && t.startsWith('matrix(')) {
                const a = parseFloat(t.split('(')[1].split(',')[0]);
                if (!Number.isNaN(a) && a > 0) cssScale = a;
            }
        }

        // Hide Konva text and transformer while editing
        textNode.hide();
        transformer?.hide();
        layer.draw();

        const textarea = document.createElement('textarea');
        document.body.appendChild(textarea);

        textarea.value = textNode.text();
        textarea.style.position = 'absolute';
        textarea.style.top = (stageBox.top + absPos.y * cssScale) + 'px';
        textarea.style.left = (stageBox.left + absPos.x * cssScale) + 'px';

        // Size the textarea
        const width = Math.max(40, (textNode.width() || 200) * cssScale);
        const height = Math.max(28, (textNode.height() || 40) * cssScale);
        textarea.style.width = width + 'px';
        textarea.style.height = height + 'px';

        // Match styling
        textarea.style.fontSize = (textNode.fontSize() * cssScale) + 'px';
        textarea.style.fontFamily = textNode.fontFamily() || 'Arial';
        textarea.style.fontWeight = (textNode.fontStyle() || '').includes('bold') ? '700' : '400';
        textarea.style.lineHeight = textNode.lineHeight?.() ? String(textNode.lineHeight()) : '1.2';
        textarea.style.color = textNode.fill() || '#111827';
        textarea.style.background = 'rgba(255,255,255,0.92)';
        textarea.style.border = '1px solid rgba(100,122,11,0.35)';
        textarea.style.borderRadius = '10px';
        textarea.style.padding = '8px 10px';
        textarea.style.outline = 'none';
        textarea.style.resize = 'none';
        textarea.style.boxShadow = '0 10px 30px rgba(15,23,42,0.12)';
        textarea.style.transformOrigin = 'top left';
        textarea.style.zIndex = 999999;

        // Keep alignment
        textarea.style.textAlign = textNode.align ? textNode.align() : 'left';

        textarea.focus();
        textarea.select();

        function removeTextarea(commit) {
            if (commit) {
                textNode.text(textarea.value);
                saveHistory();
                refreshLayersList();
                updateInspector();
            }
            textarea.remove();
            textNode.show();
            transformer?.show();
            layer.draw();
        }

        // Enter = validate, Shift+Enter = new line
        textarea.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                removeTextarea(true);
            }
            if (e.key === 'Escape') {
                e.preventDefault();
                removeTextarea(false);
            }
        });

        // Click outside = validate
        setTimeout(() => {
            window.addEventListener('mousedown', function onDown(ev) {
                if (ev.target !== textarea) {
                    window.removeEventListener('mousedown', onDown);
                    removeTextarea(true);
                }
            });
        }, 0);
    }

    // Double click / double tap on any text node
    stage.on('dblclick dbltap', (e) => {
        const node = e.target;
        if (node && node.getClassName && node.getClassName() === 'Text') {
            editTextNode(node);
        }
    });
}



    // --- display scaling (fit-to-wrapper * userZoom)
    function applyDisplayScale() {
        if (!stage || !selectedFormat) return;

        const maxW = wrapper.clientWidth;
        const fitScale = maxW / selectedFormat.w;
        const scale = fitScale * userZoom;

        const scaledH = Math.round(selectedFormat.h * scale);
        wrapper.style.height = scaledH + 'px';

        const content = wrapper.querySelector('.konvajs-content');
        if (!content) return;

        content.style.transformOrigin = 'top left';
        content.style.transform = `scale(${scale})`;
        wrapper.style.overflow = 'hidden';
    }

    // --- grid
    function clearGrid() {
        if (!gridLayer) return;
        gridLayer.destroyChildren();
        gridLayer.draw();
    }

    function drawGrid(step = 80) {
        if (!gridLayer || !stage) return;
        clearGrid();

        const w = stage.width();
        const h = stage.height();

        for (let x = 0; x <= w; x += step) {
            gridLayer.add(new Konva.Line({
                points: [x, 0, x, h],
                stroke: 'rgba(148,163,184,0.35)',
                strokeWidth: 1,
                listening: false
            }));
        }
        for (let y = 0; y <= h; y += step) {
            gridLayer.add(new Konva.Line({
                points: [0, y, w, y],
                stroke: 'rgba(148,163,184,0.35)',
                strokeWidth: 1,
                listening: false
            }));
        }
        gridLayer.draw();
    }

    // --- history
    function saveHistory() {
        if (!stage) return;
        history.push(stage.toJSON());
        if (history.length > 25) history.shift();
    }

    function restoreFromJson(json) {
        if (!selectedFormat) return;

        // fully rebuild stage, then load JSON into same container
        const fmt = selectedFormat;
        destroyStage(false);
        initStageForFormat(fmt, false);

        stage.destroyChildren();
        Konva.Node.create(json, container);

        // re-find references
        backgroundLayer = stage.findOne('.backgroundLayer') || stage.children[0];
        gridLayer       = stage.findOne('.gridLayer') || stage.children[1];
        mainLayer       = stage.findOne('.mainLayer') || stage.children[2];
        bgRect          = stage.findOne('.bgRect');
        transformer     = stage.findOne('.mainTransformer');

        applyDisplayScale();
        clearSelection();
        refreshLayersList();
        updateInspector();
    }

    function undo() {
        if (!stage || history.length < 2) return;
        history.pop();
        const prev = history[history.length - 1];
        restoreFromJson(prev);
    }

    // --- selection
    function clearSelection() {
        selectedNode = null;
        transformer?.nodes([]);
        mainLayer?.draw();
        updateInspector();
        refreshLayersList();
    }

    function setSelection(node) {
        if (!node || node === bgRect) { clearSelection(); return; }
        selectedNode = node;
        transformer?.nodes([node]);
        transformer?.moveToTop();
        mainLayer?.draw();
        updateInspector();
        refreshLayersList();
    }

    // --- layers list
    function refreshLayersList() {
        if (!layersList || !mainLayer) return;
        layersList.innerHTML = '';

        const nodes = mainLayer.getChildren().filter(n => n !== transformer);
        const list = nodes.slice().reverse();

        list.forEach(node => {
            const name = node.getAttr('amName') || node.getClassName();
            const type = node.getAttr('amType') || node.getClassName();

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'layer-item-btn' + (selectedNode === node ? ' is-active' : '');
            btn.innerHTML = `
                <span class="truncate">${escapeHtml(name)}</span>
                <span class="layer-chip"><span>⬚</span>${escapeHtml(type)}</span>
            `;
            btn.addEventListener('click', () => setSelection(node));
            layersList.appendChild(btn);
        });
    }

    function newElementId(){ elementCounter++; return 'elem-' + elementCounter; }
    function registerElement(node, type, name) {
        node.setAttr('amId', newElementId());
        node.setAttr('amType', type);
        node.setAttr('amName', name || type);
        saveHistory();
        refreshLayersList();
        updateInspector();
    }

    // --- inspector
    function updateInspector() {
        if (!selectionPanel || !noSelectionHint || !selectionTypeBadge) return;

        if (!selectedNode) {
            selectionPanel.classList.add('hidden');
            noSelectionHint.classList.remove('hidden');
            selectionTypeBadge.textContent = 'Aucun élément';
            return;
        }

        selectionPanel.classList.remove('hidden');
        noSelectionHint.classList.add('hidden');

        const t = selectedNode.getAttr('amType') || selectedNode.getClassName();
        selectionTypeBadge.textContent = t;

        shapeControls?.classList.toggle('hidden', !(t === 'rect' || t === 'circle'));
        textControls?.classList.toggle('hidden', t !== 'text');
        imageControls?.classList.toggle('hidden', t !== 'image');

        if (inputLayerName) inputLayerName.value = selectedNode.getAttr('amName') || '';
        if (inputOpacity && opacityValue) {
            const op = Math.round((selectedNode.opacity() ?? 1) * 100);
            inputOpacity.value = op;
            opacityValue.textContent = op + '%';
        }

        // shape
        if ((t === 'rect' || t === 'circle') && shapeFill && shapeStroke && shapeStrokeWidth && strokeWidthValue) {
            shapeFill.value = (selectedNode.fill && selectedNode.fill()) ? selectedNode.fill() : '#e2e8f0';
            shapeStroke.value = (selectedNode.stroke && selectedNode.stroke()) ? selectedNode.stroke() : '#94a3b8';
            const sw = (selectedNode.strokeWidth && selectedNode.strokeWidth()) ?? 0;
            shapeStrokeWidth.value = sw;
            strokeWidthValue.textContent = sw + ' px';
        }

        // text
        if (t === 'text' && textFontSize && textColor) {
            textFontSize.value = selectedNode.fontSize?.() ?? 26;
            textColor.value = selectedNode.fill?.() ?? '#111827';
        }
    }

    // --- stage lifecycle
    function destroyStage(clearSelectedFormat = true) {
        if (resizeObserver) { resizeObserver.disconnect(); resizeObserver = null; }
        if (stage) { stage.destroy(); stage = null; }
        container.innerHTML = '';
        history = [];
        elementCounter = 0;
        selectedNode = null;
        wrapper.style.height = '';
        if (clearSelectedFormat) selectedFormat = null;
    }

    function refreshTemplateButtons() {
        document.querySelectorAll('.js-template-btn').forEach(btn => {
            const fmt = btn.getAttribute('data-format');
            const ok = selectedFormat && fmt === selectedFormat.id;
            btn.classList.toggle('opacity-40', !ok);
            btn.classList.toggle('pointer-events-none', !ok);
        });
    }

function initStageForFormat(fmt, resetHistory = true) {
    destroyStage(false);

    selectedFormat = fmt;

    if (formatBadge) formatBadge.textContent = `${fmt.w} × ${fmt.h}`;

    stage = new Konva.Stage({ container: container, width: fmt.w, height: fmt.h });

    backgroundLayer = new Konva.Layer({ name: 'backgroundLayer' });
    gridLayer       = new Konva.Layer({ name: 'gridLayer' });
    mainLayer       = new Konva.Layer({ name: 'mainLayer' });

    stage.add(backgroundLayer);
    stage.add(gridLayer);
    stage.add(mainLayer);

    bgRect = new Konva.Rect({
        x: 0, y: 0, width: fmt.w, height: fmt.h,
        fill: '#f9fafb',
        name: 'bgRect',
        listening: true
    });
    backgroundLayer.add(bgRect);
    backgroundLayer.draw();

    transformer = new Konva.Transformer({
        name: 'mainTransformer',
        rotateEnabled: true,
        enabledAnchors: [
            'top-left', 'top-center', 'top-right',
            'middle-left',           'middle-right',
            'bottom-left','bottom-center','bottom-right'
        ],
        centeredScaling: true,
        boundBoxFunc: (oldBox, newBox) => {
            if (newBox.width < 30 || newBox.height < 30) return oldBox;
            return newBox;
        }
    });
    mainLayer.add(transformer);

    stage.on('click tap', (e) => {
        if (e.target === stage || e.target === bgRect) clearSelection();
        else setSelection(e.target);
    });

    // ✅ ENABLE dblclick editing for text nodes
    enableTextEditing(stage, mainLayer);

    // resize scale observer
    applyDisplayScale();
    resizeObserver = new ResizeObserver(() => applyDisplayScale());
    resizeObserver.observe(wrapper);

    // grid state
    if (toggleGrid?.checked) drawGrid(); else clearGrid();

    if (resetHistory) { history = []; saveHistory(); }
    refreshTemplateButtons();
    refreshLayersList();
    updateInspector();
}

    // --- primitives
    function addText(text = 'Votre texte') {
        if (!ensureFormatChosen()) return;
        const node = new Konva.Text({
            x: 100, y: 120, text,
            fontSize: 64,
            fontFamily: 'Arial',
            fill: '#111827',
            draggable: true
        });
        mainLayer.add(node);
        registerElement(node, 'text', 'Texte');
        setSelection(node);
        mainLayer.draw();
        saveHistory();
    }
function addShape(kind) {
    if (!ensureFormatChosen()) return;

    const common = {
        x: 140, y: 200,
        draggable: true,
        fill: '#e2e8f0',
        stroke: '#94a3b8',
        strokeWidth: 2,
    };

    let node = null;

    // Helper: regular polygon points for Konva.Line (closed)
    const poly = (cx, cy, r, sides, rotationDeg = -90) => {
        const rot = rotationDeg * Math.PI / 180;
        const pts = [];
        for (let i = 0; i < sides; i++) {
            const a = rot + (i * 2 * Math.PI / sides);
            pts.push(cx + r * Math.cos(a), cy + r * Math.sin(a));
        }
        return pts;
    };

    // Helper: star points
    const star = (cx, cy, outerR, innerR, spikes, rotationDeg = -90) => {
        const rot = rotationDeg * Math.PI / 180;
        const pts = [];
        const step = Math.PI / spikes;
        for (let i = 0; i < spikes * 2; i++) {
            const r = (i % 2 === 0) ? outerR : innerR;
            const a = rot + i * step;
            pts.push(cx + r * Math.cos(a), cy + r * Math.sin(a));
        }
        return pts;
    };

    switch (kind) {
        case 'rect':
            node = new Konva.Rect({ ...common, width: 620, height: 360, cornerRadius: 0 });
            registerElement(node, 'rect', 'Rectangle');
            break;

        case 'roundRect':
            node = new Konva.Rect({ ...common, width: 620, height: 360, cornerRadius: 36 });
            registerElement(node, 'rect', 'Rectangle arrondi');
            break;

        case 'circle':
            node = new Konva.Circle({ ...common, x: 360, y: 420, radius: 180 });
            registerElement(node, 'circle', 'Cercle');
            break;

        case 'ellipse':
            node = new Konva.Ellipse({ ...common, x: 360, y: 420, radiusX: 260, radiusY: 160 });
            registerElement(node, 'ellipse', 'Ellipse');
            break;

        case 'triangle':
            node = new Konva.Line({ ...common, points: poly(360, 420, 200, 3), closed: true });
            registerElement(node, 'shape', 'Triangle');
            break;

        case 'rightTriangle':
            node = new Konva.Line({ ...common, points: [160, 640, 560, 640, 160, 240], closed: true });
            registerElement(node, 'shape', 'Triangle droit');
            break;

        case 'diamond':
            node = new Konva.Line({ ...common, points: [360, 220, 560, 420, 360, 620, 160, 420], closed: true });
            registerElement(node, 'shape', 'Losange');
            break;

        case 'parallelogram':
            node = new Konva.Line({ ...common, points: [200, 260, 680, 260, 560, 620, 80, 620], closed: true });
            registerElement(node, 'shape', 'Parallélogramme');
            break;

        case 'trapezoid':
            node = new Konva.Line({ ...common, points: [220, 260, 640, 260, 720, 620, 140, 620], closed: true });
            registerElement(node, 'shape', 'Trapèze');
            break;

        case 'pentagon':
            node = new Konva.Line({ ...common, points: poly(360, 420, 200, 5), closed: true });
            registerElement(node, 'shape', 'Pentagone');
            break;

        case 'hexagon':
            node = new Konva.Line({ ...common, points: poly(360, 420, 200, 6), closed: true });
            registerElement(node, 'shape', 'Hexagone');
            break;

        case 'octagon':
            node = new Konva.Line({ ...common, points: poly(360, 420, 200, 8), closed: true });
            registerElement(node, 'shape', 'Octogone');
            break;

        case 'star5':
            node = new Konva.Line({ ...common, points: star(360, 420, 210, 95, 5), closed: true });
            registerElement(node, 'shape', 'Étoile 5');
            break;

        case 'star6':
            node = new Konva.Line({ ...common, points: star(360, 420, 210, 105, 6), closed: true });
            registerElement(node, 'shape', 'Étoile 6');
            break;

        case 'star8':
            node = new Konva.Line({ ...common, points: star(360, 420, 210, 120, 8), closed: true });
            registerElement(node, 'shape', 'Étoile 8');
            break;

        case 'burst':
            node = new Konva.Line({ ...common, points: star(360, 420, 220, 160, 12), closed: true });
            registerElement(node, 'shape', 'Burst');
            break;

        case 'arrowRight':
            node = new Konva.Line({
                ...common,
                points: [160, 420, 560, 420, 560, 320, 740, 480, 560, 640, 560, 540, 160, 540],
                closed: true
            });
            registerElement(node, 'shape', 'Flèche droite');
            break;

        case 'arrowLeft':
            node = new Konva.Line({
                ...common,
                points: [740, 420, 340, 420, 340, 320, 160, 480, 340, 640, 340, 540, 740, 540],
                closed: true
            });
            registerElement(node, 'shape', 'Flèche gauche');
            break;

        case 'arrowUp':
            node = new Konva.Line({
                ...common,
                points: [360, 180, 520, 360, 420, 360, 420, 720, 300, 720, 300, 360, 200, 360],
                closed: true
            });
            registerElement(node, 'shape', 'Flèche haut');
            break;

        case 'arrowDown':
            node = new Konva.Line({
                ...common,
                points: [360, 780, 520, 600, 420, 600, 420, 240, 300, 240, 300, 600, 200, 600],
                closed: true
            });
            registerElement(node, 'shape', 'Flèche bas');
            break;

        default:
            return;
    }

    mainLayer.add(node);
    setSelection(node);
    mainLayer.draw();
    saveHistory();
}

    function addRect() {
        if (!ensureFormatChosen()) return;
        const node = new Konva.Rect({
            x: 120, y: 160,
            width: 600, height: 360,
            fill: '#e2e8f0',
            stroke: '#94a3b8',
            strokeWidth: 2,
            cornerRadius: 28,
            draggable: true
        });
        mainLayer.add(node);
        registerElement(node, 'rect', 'Forme');
        setSelection(node);
        mainLayer.draw();
        saveHistory();
    }

    function addCircle() {
        if (!ensureFormatChosen()) return;
        const node = new Konva.Circle({
            x: 380, y: 420,
            radius: 180,
            fill: '#e2e8f0',
            stroke: '#94a3b8',
            strokeWidth: 2,
            draggable: true
        });
        mainLayer.add(node);
        registerElement(node, 'circle', 'Cercle');
        setSelection(node);
        mainLayer.draw();
        saveHistory();
    }

    function setBgColor(hex) {
        if (!bgRect) return;
        bgRect.fill(hex);
        backgroundLayer.draw();
        saveHistory();
    }

function clearCanvas() {
    if (!mainLayer) return;

    const children = mainLayer.getChildren();
    const arr = Array.isArray(children)
        ? children
        : (typeof children.toArray === 'function' ? children.toArray() : Array.from(children || []));

    // Destroy everything except transformer
    arr.forEach((n) => {
        if (transformer && n === transformer) return;
        if (n && typeof n.destroy === 'function') n.destroy();
    });

    // Ensure transformer exists and is on mainLayer
    if (!transformer || transformer.isDestroyed?.()) {
        transformer = new Konva.Transformer({
            name: 'mainTransformer',
            rotateEnabled: true,
            enabledAnchors: [
                'top-left', 'top-center', 'top-right',
                'middle-left',           'middle-right',
                'bottom-left','bottom-center','bottom-right'
            ],
            centeredScaling: true,
            boundBoxFunc: (oldBox, newBox) => {
                if (newBox.width < 30 || newBox.height < 30) return oldBox;
                return newBox;
            }
        });
        mainLayer.add(transformer);
    } else {
        if (transformer.getLayer && transformer.getLayer() !== mainLayer) {
            mainLayer.add(transformer);
        }
    }

    transformer.nodes([]);
    transformer.moveToTop();

    clearSelection();
    mainLayer.draw();
    saveHistory();
    refreshLayersList();
    updateInspector();
}




    // --- templates: IMPORTANT: use your config IDs (quote/promo/event/testimonial/tip/before_after/checklist)
    function applyTemplate(templateId) {
        if (!ensureFormatChosen()) return;

        const tpl = TEMPLATES.find(t => t.id === templateId);
        if (!tpl || tpl.format_id !== selectedFormat.id) return;

        clearCanvas();

        // Helpers to create consistent blocks
        const title = (txt) => {
            addText(txt);
            selectedNode.fontSize(72);
            selectedNode.fill('#0f172a');
            selectedNode.x(90); selectedNode.y(110);
        };

        const subtitle = (txt) => {
            addText(txt);
            selectedNode.fontSize(40);
            selectedNode.fill('#334155');
            selectedNode.x(90); selectedNode.y(220);
        };

        const badge = (txt) => {
            const pill = new Konva.Rect({
                x: 90, y: 60, width: 420, height: 70,
                fill: '#ecfccb', cornerRadius: 999, draggable: true
            });
            mainLayer.add(pill);
            registerElement(pill, 'rect', 'Badge');

            addText(txt);
            selectedNode.fontSize(34);
            selectedNode.fill('#365314');
            selectedNode.x(120); selectedNode.y(78);
        };

        // ===== Templates =====
        switch (templateId) {
            case 'quote': {
                setBgColor('#fefce8');
                badge('CITATION');
                addText('“Une petite routine, tous les jours, change tout.”');
                selectedNode.fontSize(64);
                selectedNode.fill('#0f172a');
                selectedNode.x(90); selectedNode.y(240);
                selectedNode.width(selectedFormat.w - 180);
                selectedNode.align('center');
                break;
            }

            case 'promo': {
                setBgColor('#f1f5f9');
                badge('OFFRE');
                title('Promo du mois');
                subtitle('–20% sur votre pack découverte');
                addRect();
                selectedNode.x(90); selectedNode.y(360);
                selectedNode.width(selectedFormat.w - 180);
                selectedNode.height(520);
                selectedNode.fill('#e2e8f0');
                selectedNode.strokeWidth(0);

                addText('Code: NOEL2025');
                selectedNode.fontSize(56);
                selectedNode.fill('#647a0b');
                selectedNode.x(90); selectedNode.y(selectedFormat.h - 180);
                selectedNode.width(selectedFormat.w - 180);
                selectedNode.align('center');
                break;
            }

case 'event': {
    // ===== Facebook Square 1080 - Poster Webinar/Atelier (Premium) =====
    const W = selectedFormat.w;
    const H = selectedFormat.h;

    // Colors (tweak freely)
    const BROWN = '#7b5646';
    const SAGE  = '#5e7415';
    const CREAM = '#f3eadf';

    setBgColor('#ffffff');

    // Helpers
    const addR = (o) => {
        const r = new Konva.Rect({
            x:o.x, y:o.y, width:o.w, height:o.h,
            fill:o.fill ?? '#e2e8f0',
            cornerRadius:o.r ?? 0,
            shadowColor:o.shadowColor,
            shadowBlur:o.shadowBlur ?? 0,
            shadowOffset:o.shadowOffset ?? {x:0,y:0},
            shadowOpacity:o.shadowOpacity ?? 0,
            draggable: true
        });
        mainLayer.add(r);
        registerElement(r, 'rect', o.name || 'Bloc');
        return r;
    };

    const addT = (o) => {
        const t = new Konva.Text({
            x:o.x, y:o.y, width:o.w,
            text:o.text,
            fontSize:o.size ?? 32,
            fontFamily:o.font ?? 'Arial',
            fontStyle:o.style ?? 'normal',
            fill:o.fill ?? '#111827',
            align:o.align ?? 'left',
            lineHeight:o.lh ?? 1.05,
            draggable: true
        });
        mainLayer.add(t);
        registerElement(t, 'text', o.name || 'Texte');
        return t;
    };

    // ===== Layout constants =====
    const topBarH = 140;

    // background card area (the big rounded “paper”)
    const paperX = 40;
    const paperY = topBarH + 30;
    const paperW = W - 80;
    const paperH = H - paperY - 40;

    // inner padding
    const pad = 60;

    // zones inside paper
    const datePillH   = 92;
    const titleBandH  = 340;
    const ctaBoxH     = 170;

    // vertical rhythm
    const gap1 = 36;
    const gap2 = 44;

    // computed positions
    const dateY  = paperY + 140;
    const titleY = dateY + datePillH + gap1;
    const ctaY   = paperY + paperH - pad - ctaBoxH;

    // ===== TOP BAR =====
    addR({ x:0, y:0, w:W, h:topBarH, fill:BROWN, r:0, name:'Header' });

    // Logo placeholder (simple)
    addR({
        x: 50, y: 34, w: 84, h: 84,
        fill: 'rgba(255,255,255,0.10)',
        r: 999,
        name: 'Logo'
    });

    addT({
        x: 50, y: 54, w: 84,
        text: "AromaMa\nde\nPRO",
        size: 16,
        font: 'Arial',
        style: 'bold',
        fill: CREAM,
        align: 'center',
        lh: 1.0,
        name: 'Logo texte'
    });

    addT({
        x: 160, y: 36, w: W - 190,
        text: "AromaMade PRO - La plateforme\ndes praticiens du bien-être",
        size: 40,
        font: 'Arial',
        style: 'bold',
        fill: CREAM,
        lh: 1.1,
        name: 'Header titre'
    });

    // ===== PAPER BACKGROUND (rounded card) =====
    addR({
        x: paperX, y: paperY, w: paperW, h: paperH,
        fill: '#f2ede4',
        r: 44,
        shadowColor: 'rgba(15,23,42,0.18)',
        shadowBlur: 24,
        shadowOffset: { x: 0, y: 10 },
        shadowOpacity: 0.25,
        name: 'Fond papier'
    });

    // subtle top-to-bottom light gradient overlay (smooth, no stripes)
    const paperOverlay = new Konva.Rect({
        x: paperX, y: paperY, width: paperW, height: paperH,
        cornerRadius: 44,
        listening: false,
        fillLinearGradientStartPoint: { x: 0, y: 0 },
        fillLinearGradientEndPoint:   { x: 0, y: paperH },
        fillLinearGradientColorStops: [
            0.0, 'rgba(255,255,255,0.55)',
            0.6, 'rgba(255,255,255,0.18)',
            1.0, 'rgba(0,0,0,0.06)'
        ]
    });
    mainLayer.add(paperOverlay);

    // ===== DATE PILL =====
    const dateX = paperX + pad;
    const dateW = paperW - (pad * 2);

    addR({
        x: dateX,
        y: dateY,
        w: dateW,
        h: datePillH,
        fill: SAGE,
        r: 26,
        name: 'Bandeau date'
    });

    addT({
        x: dateX + 30,
        y: dateY + 24,
        w: dateW - 60,
        text: "Webinaire - Lundi 12 Janvier 2026 à 18h30",
        size: 42,
        font: 'Arial',
        style: 'bold',
        fill: CREAM,
        lh: 1.0,
        name: 'Texte date'
    });

    // ===== TITLE BAND =====
    addR({
        x: paperX,
        y: titleY,
        w: paperW,
        h: titleBandH,
        fill: SAGE,
        r: 0,
        name: 'Bandeau titre'
    });

    // Title (serif, huge, but padded and not too high)
    addT({
        x: paperX + pad,
        y: titleY + 56,
        w: paperW - (pad * 2),
        text: "Comprendre comment\nêtre visible sur Internet",
        size: 90,
        font: 'Georgia',
        style: 'normal',
        fill: CREAM,
        lh: 0.98,
        name: 'Titre principal'
    });

    // ===== CTA BOX (bottom-right, fully readable) =====
    const ctaW = 420;
    const ctaX = paperX + paperW - pad - ctaW;

    addR({
        x: ctaX,
        y: ctaY,
        w: ctaW,
        h: ctaBoxH,
        fill: SAGE,
        r: 22,
        name: 'CTA'
    });

    addT({
        x: ctaX + 22,
        y: ctaY + 26,
        w: ctaW - 44,
        text: "Inscrivez vous\ngratuitement",
        size: 50,
        font: 'Arial',
        style: 'bold',
        fill: CREAM,
        align: 'center',
        lh: 1.0,
        name: 'CTA texte'
    });

    addT({
        x: ctaX + 22,
        y: ctaY + 132,
        w: ctaW - 44,
        text: "(Lien en description)",
        size: 26,
        font: 'Arial',
        style: 'bold',
        fill: 'rgba(243,234,223,0.90)',
        align: 'center',
        name: 'CTA sous-texte'
    });

    mainLayer.draw();
    saveHistory();
    refreshLayersList();
    updateInspector();
    break;
}




            case 'testimonial': {
                setBgColor('#f8fafc');
                badge('AVIS CLIENT');
                addText('“J’ai enfin retrouvé une routine simple et efficace.”');
                selectedNode.fontSize(62);
                selectedNode.fill('#0f172a');
                selectedNode.x(90); selectedNode.y(240);
                selectedNode.width(selectedFormat.w - 180);
                selectedNode.align('center');

                addText('— Prénom, Ville');
                selectedNode.fontSize(36);
                selectedNode.fill('#334155');
                selectedNode.x(90); selectedNode.y(selectedFormat.h - 200);
                selectedNode.width(selectedFormat.w - 180);
                selectedNode.align('center');
                break;
            }

            case 'tip': {
                setBgColor('#ecfccb');
                badge('ASTUCE');
                title('Astuce du jour');
                addText('Respirez 4 secondes • Bloquez 4 • Expirez 6\nRépétez 5 fois');
                selectedNode.fontSize(56);
                selectedNode.fill('#0f172a');
                selectedNode.x(90); selectedNode.y(320);
                selectedNode.width(selectedFormat.w - 180);
                selectedNode.align('center');
                break;
            }

            case 'before_after': {
                setBgColor('#f1f5f9');
                badge('AVANT / APRÈS');
                addRect();
                const left = selectedNode;
                left.x(90); left.y(220);
                left.width((selectedFormat.w - 200)/2);
                left.height(selectedFormat.h - 340);
                left.fill('#e2e8f0'); left.strokeWidth(0);

                const right = left.clone({ x: left.x() + left.width() + 20 });
                mainLayer.add(right);
                registerElement(right, 'rect', 'Après');

                addText('AVANT');
                selectedNode.fontSize(44); selectedNode.fill('#334155');
                selectedNode.x(left.x()); selectedNode.y(selectedFormat.h - 120);
                selectedNode.width(left.width()); selectedNode.align('center');

                addText('APRÈS');
                selectedNode.fontSize(44); selectedNode.fill('#334155');
                selectedNode.x(right.x()); selectedNode.y(selectedFormat.h - 120);
                selectedNode.width(right.width()); selectedNode.align('center');

                break;
            }

            case 'checklist': {
                setBgColor('#ffffff');
                badge('CHECKLIST');
                title('Routine du matin');
                addText('✅ Eau\n✅ 5 minutes de respiration\n✅ Étirement\n✅ Intention du jour');
                selectedNode.fontSize(52);
                selectedNode.fill('#0f172a');
                selectedNode.x(120); selectedNode.y(320);
                break;
            }
        }

        mainLayer.draw();
        saveHistory();
        refreshLayersList();
        updateInspector();
    }

    // --- export
    function exportPng() {
        if (!ensureFormatChosen() || !stage) return;
        const dataURL = stage.toDataURL({ pixelRatio: 1 });
        const a = document.createElement('a');
        a.href = dataURL;
        a.download = `aromamade-${selectedFormat.id}.png`;
        document.body.appendChild(a);
        a.click();
        a.remove();
    }

    // --- format modal rendering
    function renderFormatCards() {
        if (!formatsGrid) return;
        formatsGrid.innerHTML = '';

        FORMATS.forEach(fmt => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'toolbar-card text-left hover:shadow-md transition';
            btn.innerHTML = `
                <div class="text-sm font-semibold text-slate-900">${escapeHtml(fmt.label)}</div>
                <div class="mt-1 text-[12px] text-slate-600">${fmt.w} × ${fmt.h}</div>
                <div class="mt-1 text-[11px] text-slate-500">${escapeHtml(fmt.hint || '')}</div>
            `;
            btn.addEventListener('click', () => {
                initStageForFormat(fmt);
                closeFormatModal();
            });
            formatsGrid.appendChild(btn);
        });
    }

    // --- wire UI
    btnChooseFormat?.addEventListener('click', openFormatModal);
    btnCloseFormatModal?.addEventListener('click', closeFormatModal);

    btnExport?.addEventListener('click', exportPng);
    btnClearCanvas?.addEventListener('click', () => clearCanvas());

    btnAddText?.addEventListener('click', () => addText());
    btnAddRect?.addEventListener('click', addRect);
    btnAddCircle?.addEventListener('click', addCircle);

    btnUndo?.addEventListener('click', undo);
	btnToggleShapesDrawer?.addEventListener('click', () => {
		if (!shapesDrawer) return;
		const isOpen = !shapesDrawer.classList.contains('hidden');
		shapesDrawer.classList.toggle('hidden', isOpen);
		if (shapesDrawerChevron) shapesDrawerChevron.textContent = isOpen ? '▾' : '▴';
	});
document.querySelectorAll('.shape-btn').forEach(btn => {
    btn.addEventListener('click', () => addShape(btn.getAttribute('data-shape')));
});

    // zoom
    zoomSlider?.addEventListener('input', (e) => {
        const v = Number(e.target.value || 100);
        userZoom = clamp(v / 100, 0.4, 1.4);
        if (zoomValue) zoomValue.textContent = v + '%';
        applyDisplayScale();
    });

    // bg
    bgColorPicker?.addEventListener('input', (e) => setBgColor(e.target.value));
    btnResetBg?.addEventListener('click', () => {
        if (bgColorPicker) bgColorPicker.value = '#f9fafb';
        setBgColor('#f9fafb');
    });
    document.querySelectorAll('[data-bg]').forEach(btn => {
        btn.addEventListener('click', () => {
            const hex = btn.getAttribute('data-bg');
            if (!hex) return;
            if (bgColorPicker) bgColorPicker.value = hex;
            setBgColor(hex);
        });
    });

    // grid
    toggleGrid?.addEventListener('change', (e) => {
        if (!ensureFormatChosen()) return;
        if (e.target.checked) drawGrid(); else clearGrid();
    });

    // center / delete
    btnCenterSelection?.addEventListener('click', () => {
        if (!selectedNode || !stage) return;
        const centerX = stage.width() / 2;
        const centerY = stage.height() / 2;

        const rect = selectedNode.getClientRect({ relativeTo: stage });
        const dx = centerX - (rect.x + rect.width / 2);
        const dy = centerY - (rect.y + rect.height / 2);

        selectedNode.x(selectedNode.x() + dx);
        selectedNode.y(selectedNode.y() + dy);

        mainLayer.draw();
        saveHistory();
        refreshLayersList();
    });

    btnDeleteSelection?.addEventListener('click', () => {
        if (!selectedNode) return;
        selectedNode.destroy();
        clearSelection();
        mainLayer.draw();
        saveHistory();
        refreshLayersList();
    });

    // templates
    document.querySelectorAll('.js-template-btn').forEach(btn => {
        btn.addEventListener('click', () => applyTemplate(btn.getAttribute('data-template')));
    });

    // image upload
    imageUpload?.addEventListener('change', (e) => {
        if (!ensureFormatChosen()) return;
        const file = e.target.files?.[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = () => {
            const imgObj = new Image();
            imgObj.onload = () => {
                const kImg = new Konva.Image({
                    image: imgObj,
                    x: 0, y: 0,
                    width: selectedFormat.w,
                    height: selectedFormat.h,
                    draggable: true
                });
                mainLayer.add(kImg);
                registerElement(kImg, 'image', 'Image');
                setSelection(kImg);
                mainLayer.draw();
                saveHistory();
            };
            imgObj.src = reader.result;
        };
        reader.readAsDataURL(file);
    });

    // inspector events
    inputLayerName?.addEventListener('input', (e) => {
        if (!selectedNode) return;
        selectedNode.setAttr('amName', e.target.value);
        refreshLayersList();
    });

    inputOpacity?.addEventListener('input', (e) => {
        if (!selectedNode) return;
        const v = clamp(Number(e.target.value || 100), 20, 100);
        selectedNode.opacity(v / 100);
        if (opacityValue) opacityValue.textContent = v + '%';
        mainLayer.draw();
        saveHistory();
    });

    shapeFill?.addEventListener('input', (e) => {
        if (!selectedNode || !selectedNode.fill) return;
        selectedNode.fill(e.target.value);
        mainLayer.draw(); saveHistory();
    });

    shapeStroke?.addEventListener('input', (e) => {
        if (!selectedNode || !selectedNode.stroke) return;
        selectedNode.stroke(e.target.value);
        mainLayer.draw(); saveHistory();
    });

    shapeStrokeWidth?.addEventListener('input', (e) => {
        if (!selectedNode || !selectedNode.strokeWidth) return;
        const v = clamp(Number(e.target.value || 0), 0, 10);
        selectedNode.strokeWidth(v);
        if (strokeWidthValue) strokeWidthValue.textContent = v + ' px';
        mainLayer.draw(); saveHistory();
    });

    textFontSize?.addEventListener('input', (e) => {
        if (!selectedNode || selectedNode.getAttr('amType') !== 'text') return;
        selectedNode.fontSize(clamp(Number(e.target.value || 26), 10, 120));
        mainLayer.draw(); saveHistory();
    });

    textColor?.addEventListener('input', (e) => {
        if (!selectedNode || selectedNode.getAttr('amType') !== 'text') return;
        selectedNode.fill(e.target.value);
        mainLayer.draw(); saveHistory();
    });

    btnAlignLeft?.addEventListener('click', () => {
        if (!selectedNode || selectedNode.getAttr('amType') !== 'text') return;
        selectedNode.align('left'); mainLayer.draw(); saveHistory();
    });
    btnAlignCenter?.addEventListener('click', () => {
        if (!selectedNode || selectedNode.getAttr('amType') !== 'text') return;
        selectedNode.align('center'); mainLayer.draw(); saveHistory();
    });
    btnAlignRight?.addEventListener('click', () => {
        if (!selectedNode || selectedNode.getAttr('amType') !== 'text') return;
        selectedNode.align('right'); mainLayer.draw(); saveHistory();
    });

    btnToggleBold?.addEventListener('click', () => {
        if (!selectedNode || selectedNode.getAttr('amType') !== 'text') return;
        const cur = selectedNode.fontStyle?.() || 'normal';
        selectedNode.fontStyle(cur.includes('bold') ? 'normal' : 'bold');
        mainLayer.draw(); saveHistory();
    });










    // z-order + duplicate
    btnZTop?.addEventListener('click', () => { if (!selectedNode) return; selectedNode.moveToTop(); transformer.moveToTop(); mainLayer.draw(); saveHistory(); refreshLayersList(); });
    btnZUp?.addEventListener('click', () => { if (!selectedNode) return; selectedNode.moveUp(); transformer.moveToTop(); mainLayer.draw(); saveHistory(); refreshLayersList(); });
    btnZDown?.addEventListener('click', () => { if (!selectedNode) return; selectedNode.moveDown(); transformer.moveToTop(); mainLayer.draw(); saveHistory(); refreshLayersList(); });
    btnZBottom?.addEventListener('click', () => { if (!selectedNode) return; selectedNode.moveToBottom(); transformer.moveToTop(); mainLayer.draw(); saveHistory(); refreshLayersList(); });

    btnDuplicate?.addEventListener('click', () => {
        if (!selectedNode) return;
        const clone = selectedNode.clone({ x: selectedNode.x() + 30, y: selectedNode.y() + 30, draggable: true });
        mainLayer.add(clone);
        registerElement(clone, clone.getAttr('amType') || clone.getClassName(), (clone.getAttr('amName') || 'Élément') + ' (copie)');
        setSelection(clone);
        mainLayer.draw();
        saveHistory();
    });

    // initial
    renderFormatCards();
    openFormatModal();
    refreshTemplateButtons();
});
</script>
