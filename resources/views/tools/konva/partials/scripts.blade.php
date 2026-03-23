{{-- resources/views/tools/konva/partials/scripts.blade.php --}}
<script src="https://unpkg.com/konva@9/konva.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('konva-container');
    const wrapper   = document.getElementById('konva-wrapper');
    const editorShell = document.querySelector('.editor-shell');
    if (!container || !wrapper) return;

    // Blade-provided flags for Admin mode
    const ADMIN_MODE = @json(isset($adminMode) && $adminMode === true);
    const ADMIN_EDITING_TEMPLATE = @json($adminEditingTemplate ?? null);

    const FORMATS   = @json(config('konva.formats', []));
    // Kept for retro-compat (your old config templates). You can delete later.
    const TEMPLATES = @json(config('konva.templates', []));
    const BRANDING_PRESETS = @json($konvaBrandingPresets ?? config('konva.branding_presets', []));
    const BRANDING_FONTS = @json($konvaBrandingFonts ?? config('konva.branding_fonts', []));
    const KONVA_BRANDING = @json($konvaBranding ?? []);
    const KONVA_CONTEXT = @json($konvaContext ?? ['events' => [], 'testimonials' => [], 'offers' => []]);
    const BRANDING_SAVE_URL = @json(url('/beta/editor/branding'));

    // Top bar
    const btnChooseFormat  = document.getElementById('btnChooseFormat');
    const btnExport        = document.getElementById('btnExport');
    const btnClearCanvas   = document.getElementById('btnClearCanvas');
    const btnChooseFormatInline = document.getElementById('btnChooseFormatInline');
    const btnExportInline = document.getElementById('btnExportInline');
    const btnClearCanvasInline = document.getElementById('btnClearCanvasInline');

    const btnModeQuick = document.getElementById('btnModeQuick');
    const btnModeExpert = document.getElementById('btnModeExpert');
    const workflowStepButtons = Array.from(document.querySelectorAll('.workflow-step-btn'));
    const layersPanelDetails = document.getElementById('layersPanelDetails');

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

    // Sticky grid UI (optional controls; safe if missing)
    const toggleSnapGrid = document.getElementById('toggleSnapGrid');
    const gridStepSelect = document.getElementById('gridStepSelect');
    const snapThresholdRange = document.getElementById('snapThresholdRange');
    const snapThresholdValue = document.getElementById('snapThresholdValue');

    const btnUndo        = document.getElementById('btnUndo');
    const btnRedo        = document.getElementById('btnRedo');

    const btnToggleShapesDrawer = document.getElementById('btnToggleShapesDrawer');
    const shapesDrawer = document.getElementById('shapesDrawer');
    const shapesDrawerChevron = document.getElementById('shapesDrawerChevron');
    const btnAlignCanvasLeft = document.getElementById('btnAlignCanvasLeft');
    const btnAlignCanvasCenterX = document.getElementById('btnAlignCanvasCenterX');
    const btnAlignCanvasRight = document.getElementById('btnAlignCanvasRight');
    const btnAlignCanvasTop = document.getElementById('btnAlignCanvasTop');
    const btnAlignCanvasCenterY = document.getElementById('btnAlignCanvasCenterY');
    const btnAlignCanvasBottom = document.getElementById('btnAlignCanvasBottom');

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
    const imgFitMode         = document.getElementById('imgFitMode');
    const btnReplaceImage    = document.getElementById('btnReplaceImage');
    const imageReplaceUpload = document.getElementById('imageReplaceUpload');

    const btnZTop            = document.getElementById('btnZTop');
    const btnZUp             = document.getElementById('btnZUp');
    const btnZDown           = document.getElementById('btnZDown');
    const btnZBottom         = document.getElementById('btnZBottom');
    const btnDuplicate       = document.getElementById('btnDuplicate');
    const btnToggleLock      = document.getElementById('btnToggleLock');
    const btnToggleVisibility = document.getElementById('btnToggleVisibility');
    const quickTemplateSearch = document.getElementById('quickTemplateSearch');
    const dbTemplateSearch    = document.getElementById('dbTemplateSearch');
    const eventSelector       = document.getElementById('eventSelector');
    const testimonialSelector = document.getElementById('testimonialSelector');
    const offerSelector       = document.getElementById('offerSelector');
    const btnAutofillTemplate = document.getElementById('btnAutofillTemplate');
    const brandPresetSelect   = document.getElementById('brandPresetSelect');
    const brandHeadingFont    = document.getElementById('brandHeadingFont');
    const brandBodyFont       = document.getElementById('brandBodyFont');
    const brandColorPrimary   = document.getElementById('brandColorPrimary');
    const brandColorSecondary = document.getElementById('brandColorSecondary');
    const brandColorAccent    = document.getElementById('brandColorAccent');
    const brandColorBackground = document.getElementById('brandColorBackground');
    const brandColorText      = document.getElementById('brandColorText');
    const btnApplyBranding    = document.getElementById('btnApplyBranding');
    const btnSaveBranding     = document.getElementById('btnSaveBranding');
    const btnResetBranding    = document.getElementById('btnResetBranding');
    const brandingStatusHint  = document.getElementById('brandingStatusHint');

    // --- State
    let stage = null, backgroundLayer = null, gridLayer = null, mainLayer = null, transformer = null, bgRect = null;
    let selectedFormat = null;
    let selectedNode   = null;
    let resizeObserver = null;
    let userZoom = 1; // 0.4 -> 1.4 display only

    // --- sticky grid (snap)
    let snapGridEnabled = true;
    let gridStep = 40;      // px
    let snapThreshold = 6;  // px tolerance

    let history = [];
    let redoStack = [];
    let elementCounter = 0;
    let clipboardNode = null;
    let pasteOffset = 0;

    // --- helpers
    const clamp = (n, a, b) => Math.max(a, Math.min(b, n));
    const escapeHtml = (str) => String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    const FONT_MAP = BRANDING_FONTS.reduce((acc, font) => {
        if (font?.key && font?.family) acc[font.key] = font.family;
        return acc;
    }, {});
    const FONT_KEYS = Object.keys(FONT_MAP);
    const DEFAULT_TRANSFORMER_ANCHORS = [
        'top-left', 'top-center', 'top-right',
        'middle-left', 'middle-right',
        'bottom-left', 'bottom-center', 'bottom-right',
    ];

    function sanitizeLayerName(name) {
        return (name || 'Element').trim() || 'Element';
    }

    function isTextNode(node) {
        return !!node && node.getAttr?.('amType') === 'text';
    }

    function normalizeTextNodeTransform(node) {
        if (!isTextNode(node)) return;

        const sx = Number(node.scaleX?.() ?? 1);
        const sy = Number(node.scaleY?.() ?? 1);
        const hasScaleX = Math.abs(sx - 1) > 0.001;
        const hasScaleY = Math.abs(sy - 1) > 0.001;

        if (!hasScaleX && !hasScaleY) return;

        if (hasScaleX && typeof node.width === 'function') {
            const nextWidth = Math.max(40, (Number(node.width()) || 40) * sx);
            node.width(nextWidth);
            node.scaleX(1);
        }

        // Convert vertical scale into font-size change, so text does not squash/stretch.
        if (hasScaleY && typeof node.fontSize === 'function') {
            const currentFontSize = Number(node.fontSize()) || 26;
            const nextFontSize = Math.max(10, Math.round(currentFontSize * sy));
            node.fontSize(nextFontSize);
            node.scaleY(1);
        }
    }

    function applyTransformerConfigForNode(node) {
        if (!transformer) return;

        const textMode = isTextNode(node);
        if (textMode) {
            transformer.enabledAnchors(['middle-left', 'middle-right']);
            transformer.centeredScaling(false);
            transformer.keepRatio(false);
            return;
        }

        transformer.enabledAnchors(DEFAULT_TRANSFORMER_ANCHORS);
        transformer.centeredScaling(true);
        transformer.keepRatio(false);
    }

    function bindTransformerBehavior() {
        if (!transformer) return;

        transformer.off('transform.textBehavior');
        transformer.off('transformend.textBehavior');

        transformer.on('transform.textBehavior', () => {
            const node = transformer.nodes?.()[0] || selectedNode;
            if (!node) return;
            if (isTextNode(node)) {
                normalizeTextNodeTransform(node);
                mainLayer?.batchDraw();
                updateInspector();
            }
        });

        transformer.on('transformend.textBehavior', () => {
            const node = transformer.nodes?.()[0] || selectedNode;
            if (!node) return;
            if (isTextNode(node)) {
                normalizeTextNodeTransform(node);
                mainLayer?.draw();
                saveHistory();
                refreshLayersList();
                updateInspector();
            }
        });
    }

    function setUiMode(mode, persist = true) {
        const normalized = mode === 'expert' ? 'expert' : 'quick';
        if (!editorShell) return;

        editorShell.classList.toggle('is-quick-mode', normalized === 'quick');
        editorShell.classList.toggle('is-expert-mode', normalized === 'expert');

        if (btnModeQuick) btnModeQuick.classList.toggle('is-active', normalized === 'quick');
        if (btnModeExpert) btnModeExpert.classList.toggle('is-active', normalized === 'expert');

        if (layersPanelDetails) {
            layersPanelDetails.open = normalized === 'expert';
        }

        if (persist) {
            try {
                localStorage.setItem('am_konva_ui_mode', normalized);
            } catch (e) {}
        }
    }

    function initUiMode() {
        let savedMode = 'quick';
        try {
            savedMode = localStorage.getItem('am_konva_ui_mode') || 'quick';
        } catch (e) {}
        setUiMode(savedMode, false);
    }

    function focusWorkflowSection(targetId) {
        if (!targetId) return;
        const target = document.getElementById(targetId);
        if (!target) return;

        target.scrollIntoView({ behavior: 'smooth', block: 'start' });

        const detailsParent = target.closest('details');
        if (detailsParent && !detailsParent.open) {
            detailsParent.open = true;
        }
    }

    function fontKeyToFamily(key, fallbackKey = null) {
        if (key && FONT_MAP[key]) return FONT_MAP[key];
        if (fallbackKey && FONT_MAP[fallbackKey]) return FONT_MAP[fallbackKey];
        if (FONT_KEYS.length) return FONT_MAP[FONT_KEYS[0]];
        return 'Arial, sans-serif';
    }

    function normalizeHexColor(color, fallback) {
        const val = String(color || '').trim().toUpperCase();
        return /^#([A-F0-9]{6})$/.test(val) ? val : fallback;
    }

    function hexToRgb(hex) {
        const clean = String(hex || '').replace('#', '');
        if (!/^[A-Fa-f0-9]{6}$/.test(clean)) return null;
        return {
            r: parseInt(clean.slice(0, 2), 16),
            g: parseInt(clean.slice(2, 4), 16),
            b: parseInt(clean.slice(4, 6), 16),
        };
    }

    function rgbToHex(r, g, b) {
        const norm = (n) => Math.max(0, Math.min(255, Math.round(n))).toString(16).padStart(2, '0');
        return `#${norm(r)}${norm(g)}${norm(b)}`.toUpperCase();
    }

    function mixColor(hexA, hexB, ratio = 0.5) {
        const a = hexToRgb(hexA);
        const b = hexToRgb(hexB);
        if (!a || !b) return normalizeHexColor(hexA, '#647A0B');
        const t = clamp(Number(ratio), 0, 1);
        return rgbToHex(
            a.r + (b.r - a.r) * t,
            a.g + (b.g - a.g) * t,
            a.b + (b.b - a.b) * t
        );
    }

    function getPresetById(id) {
        return BRANDING_PRESETS.find((preset) => String(preset?.id) === String(id)) || null;
    }

    function getDefaultBranding() {
        const firstPreset = BRANDING_PRESETS[0] || {};
        const firstFontKey = FONT_KEYS[0] || 'poppins';
        const secondFontKey = FONT_KEYS[1] || firstFontKey;

        return {
            preset: firstPreset.id || 'manual',
            fonts: {
                heading: firstPreset?.fonts?.heading || firstFontKey,
                body: firstPreset?.fonts?.body || secondFontKey,
            },
            colors: {
                primary: normalizeHexColor(firstPreset?.colors?.primary, '#647A0B'),
                secondary: normalizeHexColor(firstPreset?.colors?.secondary, '#854F38'),
                accent: normalizeHexColor(firstPreset?.colors?.accent, '#D4A373'),
                background: normalizeHexColor(firstPreset?.colors?.background, '#F8F9F5'),
                text: normalizeHexColor(firstPreset?.colors?.text, '#1F2937'),
            }
        };
    }

    function normalizeBrandingSettings(raw) {
        const defaults = getDefaultBranding();
        const src = raw && typeof raw === 'object' ? raw : {};
        const srcFonts = src.fonts && typeof src.fonts === 'object' ? src.fonts : {};
        const srcColors = src.colors && typeof src.colors === 'object' ? src.colors : {};

        return {
            preset: src.preset || defaults.preset,
            fonts: {
                heading: FONT_MAP[srcFonts.heading] ? srcFonts.heading : defaults.fonts.heading,
                body: FONT_MAP[srcFonts.body] ? srcFonts.body : defaults.fonts.body,
            },
            colors: {
                primary: normalizeHexColor(srcColors.primary, defaults.colors.primary),
                secondary: normalizeHexColor(srcColors.secondary, defaults.colors.secondary),
                accent: normalizeHexColor(srcColors.accent, defaults.colors.accent),
                background: normalizeHexColor(srcColors.background, defaults.colors.background),
                text: normalizeHexColor(srcColors.text, defaults.colors.text),
            },
        };
    }

    let activeBranding = normalizeBrandingSettings(KONVA_BRANDING);

    function setBrandingStatus(message, isError = false) {
        if (!brandingStatusHint) return;
        brandingStatusHint.textContent = message;
        brandingStatusHint.classList.toggle('text-red-500', !!isError);
        brandingStatusHint.classList.toggle('text-emerald-600', !isError);
    }

    function hydrateBrandingControls(settings) {
        const normalized = normalizeBrandingSettings(settings);
        activeBranding = normalized;

        if (brandPresetSelect) brandPresetSelect.value = normalized.preset || 'manual';
        if (brandHeadingFont) brandHeadingFont.value = normalized.fonts.heading;
        if (brandBodyFont) brandBodyFont.value = normalized.fonts.body;
        if (brandColorPrimary) brandColorPrimary.value = normalized.colors.primary;
        if (brandColorSecondary) brandColorSecondary.value = normalized.colors.secondary;
        if (brandColorAccent) brandColorAccent.value = normalized.colors.accent;
        if (brandColorBackground) brandColorBackground.value = normalized.colors.background;
        if (brandColorText) brandColorText.value = normalized.colors.text;
    }

    function getBrandingFromControls() {
        const defaults = getDefaultBranding();
        return normalizeBrandingSettings({
            preset: brandPresetSelect?.value || defaults.preset,
            fonts: {
                heading: brandHeadingFont?.value || defaults.fonts.heading,
                body: brandBodyFont?.value || defaults.fonts.body,
            },
            colors: {
                primary: brandColorPrimary?.value || defaults.colors.primary,
                secondary: brandColorSecondary?.value || defaults.colors.secondary,
                accent: brandColorAccent?.value || defaults.colors.accent,
                background: brandColorBackground?.value || defaults.colors.background,
                text: brandColorText?.value || defaults.colors.text,
            }
        });
    }

    function applyPresetToControls(presetId) {
        const preset = getPresetById(presetId);
        if (!preset) return;
        hydrateBrandingControls({
            preset: preset.id,
            fonts: preset.fonts || {},
            colors: preset.colors || {},
        });
    }

    function safeLower(value) {
        return String(value || '').toLowerCase();
    }

    function shouldUseHeadingStyle(name, text) {
        const source = `${safeLower(name)} ${safeLower(text)}`;
        return /(titre|title|headline|sur titre|badge texte|tag texte|cover|countdown|masterclass|cta texte)/.test(source);
    }

    function isCtaElement(name, text) {
        const source = `${safeLower(name)} ${safeLower(text)}`;
        return /(cta|reserver|inscription|contact|appel|action)/.test(source);
    }

    function applyBrandingToCanvas(options = {}) {
        if (!stage || !mainLayer) return;

        const settings = getBrandingFromControls();
        activeBranding = settings;

        const colors = settings.colors;
        const headingFamily = fontKeyToFamily(settings.fonts.heading, getDefaultBranding().fonts.heading);
        const bodyFamily = fontKeyToFamily(settings.fonts.body, getDefaultBranding().fonts.body);
        const softSurface = mixColor(colors.background, '#FFFFFF', 0.55);
        const softStroke = mixColor(colors.primary, colors.text, 0.45);

        if (bgRect && options.skipBackground !== true) {
            bgRect.fill(colors.background);
            backgroundLayer?.draw();
            if (bgColorPicker) bgColorPicker.value = colors.background;
        }

        getEditableNodes().forEach((node) => {
            const amType = node.getAttr('amType');
            const name = String(node.getAttr('amName') || '');
            const currentText = amType === 'text' ? String(node.text?.() || '') : '';

            if (amType === 'text') {
                const heading = shouldUseHeadingStyle(name, currentText);
                node.fontFamily(heading ? headingFamily : bodyFamily);

                if (isCtaElement(name, currentText)) {
                    node.fill('#FFFFFF');
                } else if (/(etoiles|star|badge|tag)/.test(safeLower(name))) {
                    node.fill(colors.accent);
                } else if (heading) {
                    node.fill(colors.primary);
                } else {
                    node.fill(colors.text);
                }
            }

            if (['rect', 'circle', 'shape', 'ellipse'].includes(amType)) {
                const lowered = safeLower(name);
                if (/(cta|action)/.test(lowered)) {
                    node.fill?.(colors.primary);
                    node.stroke?.(mixColor(colors.primary, '#000000', 0.1));
                } else if (/(badge|tag)/.test(lowered)) {
                    node.fill?.(colors.secondary);
                    node.stroke?.(mixColor(colors.secondary, '#000000', 0.15));
                } else if (/(fond|header|bande)/.test(lowered)) {
                    node.fill?.(colors.background);
                    node.stroke?.(mixColor(colors.background, colors.text, 0.2));
                } else {
                    node.fill?.(softSurface);
                    node.stroke?.(softStroke);
                }
            }
        });

        mainLayer.draw();
        if (!options.skipHistory) saveHistory();
        refreshLayersList();
        updateInspector();
    }

    function getContextList(key) {
        const list = KONVA_CONTEXT && Array.isArray(KONVA_CONTEXT[key]) ? KONVA_CONTEXT[key] : [];
        return list;
    }

    function findContextItem(key, id) {
        if (!id) return null;
        return getContextList(key).find((item) => String(item?.id) === String(id)) || null;
    }

    function truncateText(text, maxLen = 170) {
        const normalized = String(text || '').trim().replace(/\s+/g, ' ');
        if (normalized.length <= maxLen) return normalized;
        return normalized.slice(0, Math.max(0, maxLen - 1)).trim() + '…';
    }

    function applyTextToNodeByKeywords(node, keywords, value) {
        if (!node || node.getAttr('amType') !== 'text' || !value) return false;
        const name = safeLower(node.getAttr('amName'));
        const text = safeLower(node.text?.());
        const hit = keywords.some((keyword) => name.includes(keyword) || text.includes(keyword));
        if (!hit) return false;
        node.text(String(value));
        return true;
    }

    function autoFillTemplateFromContext(templateHint = '') {
        if (!mainLayer) return;

        const eventData = findContextItem('events', eventSelector?.value);
        const reviewData = findContextItem('testimonials', testimonialSelector?.value);
        const offerData = findContextItem('offers', offerSelector?.value);
        const hint = safeLower(templateHint);
        const nodes = getEditableNodes().filter((node) => node.getAttr('amType') === 'text');

        nodes.forEach((node) => {
            let updated = false;

            if (!updated && eventData && (/(event|story|webinar|masterclass|countdown)/.test(hint) || /(date|event|masterclass|story|countdown|intervenant|sur titre)/.test(safeLower(node.getAttr('amName'))))) {
                updated = applyTextToNodeByKeywords(node, ['titre', 'title', 'sur titre', 'headline'], eventData.name);
                if (!updated) updated = applyTextToNodeByKeywords(node, ['date', 'countdown', 'sous titre', 'details', 'intro'], eventData.date_label || eventData.location);
                if (!updated) updated = applyTextToNodeByKeywords(node, ['intervenant', 'footer', 'description'], truncateText(eventData.description || eventData.location || ''));
                if (!updated) updated = applyTextToNodeByKeywords(node, ['cta texte', 'cta'], 'Je reserve ma place');
            }

            if (!updated && reviewData && (/(testimonial|review|avis|proof)/.test(hint) || /(avis|etoiles|nom|auteur|review)/.test(safeLower(node.getAttr('amName'))))) {
                const stars = '★'.repeat(clamp(Number(reviewData.rating || 5), 1, 5));
                updated = applyTextToNodeByKeywords(node, ['etoiles', 'star'], stars);
                if (!updated) updated = applyTextToNodeByKeywords(node, ['avis', 'review', 'citation', 'quote', 'texte'], `"${truncateText(reviewData.testimonial || '', 200)}"`);
                if (!updated) updated = applyTextToNodeByKeywords(node, ['nom', 'auteur', 'client'], reviewData.reviewer_name || 'Client');
                if (!updated) updated = applyTextToNodeByKeywords(node, ['cta'], 'Voir tous les avis');
            }

            if (!updated && offerData && (/(promo|flash|offer|pack|checklist|carousel)/.test(hint) || /(offre|prix|pack|details|titre|cta)/.test(safeLower(node.getAttr('amName'))))) {
                updated = applyTextToNodeByKeywords(node, ['titre', 'title', 'cover', 'headline'], offerData.name);
                if (!updated) updated = applyTextToNodeByKeywords(node, ['details', 'description', 'sous titre'], truncateText(offerData.description || '', 130));
                if (!updated) updated = applyTextToNodeByKeywords(node, ['prix', 'price', 'remise'], offerData.price_label || '');
                if (!updated) updated = applyTextToNodeByKeywords(node, ['cta'], 'Reserver maintenant');
            }
        });

        mainLayer.draw();
        saveHistory();
        refreshLayersList();
        updateInspector();
    }

    async function saveBrandingSettings() {
        if (!BRANDING_SAVE_URL) {
            setBrandingStatus('Sauvegarde indisponible: route manquante (deploiement incomplet).', true);
            return;
        }

        const settings = getBrandingFromControls();
        const payload = {
            preset: settings.preset === 'manual' ? null : settings.preset,
            font_heading: settings.fonts.heading,
            font_body: settings.fonts.body,
            color_primary: settings.colors.primary,
            color_secondary: settings.colors.secondary,
            color_accent: settings.colors.accent,
            color_background: settings.colors.background,
            color_text: settings.colors.text,
        };

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        if (!csrf) {
            setBrandingStatus('Impossible de sauvegarder (token CSRF manquant).', true);
            return;
        }

        try {
            setBrandingStatus('Sauvegarde en cours...');
            const res = await fetch(BRANDING_SAVE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            let data = null;
            try {
                data = await res.json();
            } catch (jsonErr) {
                data = null;
            }

            if (!res.ok || !data?.ok) {
                if (res.status === 401) throw new Error('Session non authentifiee. Rechargez la page.');
                if (res.status === 419) throw new Error('Session expiree. Rechargez la page.');
                throw new Error(data?.message || 'save_failed');
            }

            hydrateBrandingControls(data.settings || settings);
            setBrandingStatus('Branding sauvegarde. Vos prochains templates seront pre-marques.');
        } catch (err) {
            console.error(err);
            const message = (err && typeof err.message === 'string' && err.message !== 'save_failed')
                ? err.message
                : 'Echec de sauvegarde. Reessayez.';
            setBrandingStatus(message, true);
        }
    }

    function getEditableNodes() {
        if (!mainLayer) return [];
        return mainLayer.getChildren().filter(n => n !== transformer);
    }

    function isNodeLocked(node) {
        return !!node?.getAttr?.('amLocked');
    }

    function isNodeVisible(node) {
        if (!node) return false;
        return typeof node.visible === 'function' ? node.visible() : true;
    }

    function normalizeNodeState(node) {
        if (!node || node === transformer) return;
        node.draggable(!isNodeLocked(node));
        if (typeof node.listening === 'function') {
            node.listening(isNodeVisible(node));
        }
    }

    function normalizeAllNodeStates() {
        getEditableNodes().forEach(normalizeNodeState);
    }

    function openFormatModal(){ formatModal?.classList.remove('hidden'); }
    function closeFormatModal(){ formatModal?.classList.add('hidden'); }

    function ensureFormatChosen() {
        if (!selectedFormat) { openFormatModal(); return false; }
        return true;
    }

    // ----------------------------
    // Text inline editing
    // ----------------------------
    function enableTextEditing(stage, layer) {
        function editTextNode(textNode) {
            if (!textNode || textNode.getAttr('amType') !== 'text') return;
            if (isNodeLocked(textNode)) return;

            const absPos = textNode.getAbsolutePosition();
            const stageBox = stage.container().getBoundingClientRect();

            // Account for CSS scaling on konvajs-content
            const content = wrapper.querySelector('.konvajs-content');
            let cssScale = 1;
            if (content) {
                const t = window.getComputedStyle(content).transform;
                if (t && t.startsWith('matrix(')) {
                    const a = parseFloat(t.split('(')[1].split(',')[0]);
                    if (!Number.isNaN(a) && a > 0) cssScale = a;
                }
            }

            textNode.hide();
            transformer?.hide();
            layer.draw();

            const textarea = document.createElement('textarea');
            document.body.appendChild(textarea);

            textarea.value = textNode.text();
            textarea.style.position = 'absolute';
            textarea.style.top = (stageBox.top + absPos.y * cssScale) + 'px';
            textarea.style.left = (stageBox.left + absPos.x * cssScale) + 'px';

            const width = Math.max(40, (textNode.width() || 200) * cssScale);
            const height = Math.max(28, (textNode.height() || 40) * cssScale);
            textarea.style.width = width + 'px';
            textarea.style.height = height + 'px';

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

            setTimeout(() => {
                window.addEventListener('mousedown', function onDown(ev) {
                    if (ev.target !== textarea) {
                        window.removeEventListener('mousedown', onDown);
                        removeTextarea(true);
                    }
                });
            }, 0);
        }

        stage.off('dblclick dbltap');
        stage.on('dblclick dbltap', (e) => {
            const node = e.target;
            if (node && node.getClassName && node.getClassName() === 'Text') {
                editTextNode(node);
            }
        });
    }

    // ----------------------------
    // Display scaling (fit-to-wrapper * userZoom)
    // ----------------------------
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

    // ----------------------------
    // Grid
    // ----------------------------
    function clearGrid() {
        if (!gridLayer) return;
        gridLayer.destroyChildren();
        gridLayer.draw();
    }

    function drawGrid(step = gridStep) {
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

    // ----------------------------
    // Sticky grid (snap)
    // ----------------------------
    function persistStickyGridState() {
        try {
            localStorage.setItem('am_konva_snap_grid', JSON.stringify({
                enabled: !!snapGridEnabled,
                step: Number(gridStep) || 40,
                threshold: Number(snapThreshold) || 6,
            }));
        } catch (e) {}
    }

    function loadStickyGridState() {
        try {
            const raw = localStorage.getItem('am_konva_snap_grid');
            if (!raw) return;
            const st = JSON.parse(raw);
            if (typeof st.enabled === 'boolean') snapGridEnabled = st.enabled;
            if (typeof st.step === 'number' && !Number.isNaN(st.step)) gridStep = st.step;
            if (typeof st.threshold === 'number' && !Number.isNaN(st.threshold)) snapThreshold = st.threshold;
        } catch (e) {}
    }

    function syncStickyGridUI() {
        if (toggleSnapGrid) toggleSnapGrid.checked = !!snapGridEnabled;
        if (gridStepSelect) gridStepSelect.value = String(gridStep);
        if (snapThresholdRange) snapThresholdRange.value = String(snapThreshold);
        if (snapThresholdValue) snapThresholdValue.textContent = String(snapThreshold);
    }

    function snapNodeToGrid(node) {
        if (!snapGridEnabled || !stage || !node) return;
        if (node === bgRect) return;
        if (node.getClassName && node.getClassName() === 'Transformer') return;
        if (isNodeLocked(node)) return;

        const step = Number(gridStep) || 40;
        const tol  = Number(snapThreshold) || 0;

        const box = node.getClientRect({ relativeTo: stage });
        const xCandidates = [box.x, box.x + box.width / 2, box.x + box.width];
        const yCandidates = [box.y, box.y + box.height / 2, box.y + box.height];

        let bestDx = null;
        for (const x of xCandidates) {
            const snapped = Math.round(x / step) * step;
            const dx = snapped - x;
            if (Math.abs(dx) <= tol && (bestDx === null || Math.abs(dx) < Math.abs(bestDx))) bestDx = dx;
        }

        let bestDy = null;
        for (const y of yCandidates) {
            const snapped = Math.round(y / step) * step;
            const dy = snapped - y;
            if (Math.abs(dy) <= tol && (bestDy === null || Math.abs(dy) < Math.abs(bestDy))) bestDy = dy;
        }

        if (bestDx !== null) node.x(node.x() + bestDx);
        if (bestDy !== null) node.y(node.y() + bestDy);
    }

    function bindStickyGridHandlers() {
        if (!stage) return;

        stage.off('dragmove.stickyGrid');
        stage.off('dragend.stickyGrid');
        stage.off('transformend.stickyGrid');

        stage.on('dragmove.stickyGrid', (e) => {
            const n = e.target;
            if (!n || n === bgRect || isNodeLocked(n)) return;
            snapNodeToGrid(n);
            mainLayer?.batchDraw();
        });

        stage.on('dragend.stickyGrid', (e) => {
            const n = e.target;
            if (!n || n === bgRect || isNodeLocked(n)) return;
            snapNodeToGrid(n);
            mainLayer?.draw();
            saveHistory();
            refreshLayersList();
            updateInspector();
        });

        stage.on('transformend.stickyGrid', (e) => {
            const n = e.target;
            if (!n || n === bgRect || isNodeLocked(n)) return;
            snapNodeToGrid(n);
            mainLayer?.draw();
            saveHistory();
            refreshLayersList();
            updateInspector();
        });
    }

    // ----------------------------
    // History
    // ----------------------------
    function updateHistoryButtons() {
        if (btnUndo) btnUndo.disabled = history.length < 2;
        if (btnRedo) btnRedo.disabled = redoStack.length < 1;
    }

    function saveHistory(clearRedo = true) {
        if (!stage) return;
        const snapshot = stage.toJSON();
        if (history.length && history[history.length - 1] === snapshot) {
            updateHistoryButtons();
            return;
        }
        history.push(snapshot);
        if (history.length > 60) history.shift();
        if (clearRedo) redoStack = [];
        updateHistoryButtons();
    }

    function restoreFromJson(json) {
        if (!json) return;
        const restoredStage = Konva.Node.create(json, container);
        if (!(restoredStage instanceof Konva.Stage)) return;

        if (resizeObserver) {
            resizeObserver.disconnect();
            resizeObserver = null;
        }
        if (stage) stage.destroy();

        stage = restoredStage;
        backgroundLayer = stage.findOne('.backgroundLayer') || stage.find('Layer')[0] || null;
        gridLayer = stage.findOne('.gridLayer') || stage.find('Layer')[1] || null;
        mainLayer = stage.findOne('.mainLayer') || stage.find('Layer')[2] || null;
        bgRect = stage.findOne('.bgRect');
        transformer = stage.findOne('.mainTransformer');

        if (!mainLayer) {
            mainLayer = new Konva.Layer({ name: 'mainLayer' });
            stage.add(mainLayer);
        }
        if (!transformer || transformer.isDestroyed?.()) {
            transformer = new Konva.Transformer({
                name: 'mainTransformer',
                rotateEnabled: true,
                enabledAnchors: DEFAULT_TRANSFORMER_ANCHORS,
                centeredScaling: true,
                boundBoxFunc: (oldBox, newBox) => {
                    if (newBox.width < 30 || newBox.height < 30) return oldBox;
                    return newBox;
                }
            });
            mainLayer.add(transformer);
        }
        bindTransformerBehavior();

        if (selectedFormat) {
            selectedFormat = {
                ...selectedFormat,
                w: stage.width(),
                h: stage.height(),
            };
            if (formatBadge) formatBadge.textContent = `${selectedFormat.w} x ${selectedFormat.h}`;
        }

        stage.off('click tap');
        stage.on('click tap', (e) => {
            if (e.target === stage || e.target === bgRect) clearSelection();
            else setSelection(e.target);
        });

        enableTextEditing(stage, mainLayer);
        bindStickyGridHandlers();
        hydrateImageNodes();
        normalizeAllNodeStates();

        if (toggleGrid?.checked) drawGrid(); else clearGrid();

        applyDisplayScale();
        resizeObserver = new ResizeObserver(() => applyDisplayScale());
        resizeObserver.observe(wrapper);

        clearSelection();
        refreshLayersList();
        updateInspector();
        updateHistoryButtons();
    }

    function undo() {
        if (!stage || history.length < 2) return;
        const current = history.pop();
        redoStack.push(current);
        restoreFromJson(history[history.length - 1]);
        updateHistoryButtons();
    }

    function redo() {
        if (!stage || redoStack.length < 1) return;
        const next = redoStack.pop();
        history.push(next);
        restoreFromJson(next);
        updateHistoryButtons();
    }

    // ----------------------------
    // Selection
    // ----------------------------
    function clearSelection() {
        selectedNode = null;
        if (transformer) transformer.visible(true);
        applyTransformerConfigForNode(null);
        transformer?.nodes([]);
        mainLayer?.draw();
        updateInspector();
        refreshLayersList();
    }

    function setSelection(node) {
        if (!node || node === bgRect || !isNodeVisible(node)) { clearSelection(); return; }
        selectedNode = node;
        if (isNodeLocked(node)) {
            transformer?.nodes([]);
        } else {
            applyTransformerConfigForNode(node);
            if (isTextNode(node)) {
                normalizeTextNodeTransform(node);
            }
            transformer?.nodes([node]);
            transformer?.moveToTop();
        }
        mainLayer?.draw();
        updateInspector();
        refreshLayersList();
    }

    // ----------------------------
    // Layers list
    // ----------------------------
    function refreshLayersList() {
        if (!layersList || !mainLayer) return;
        layersList.innerHTML = '';

        const list = getEditableNodes().slice().reverse();

        list.forEach(node => {
            const name = sanitizeLayerName(node.getAttr('amName') || node.getClassName());
            const type = node.getAttr('amType') || node.getClassName();
            const visible = isNodeVisible(node);
            const locked = isNodeLocked(node);

            const row = document.createElement('div');
            row.className = 'layer-row';

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'layer-item-btn' + (selectedNode === node ? ' is-active' : '');
            btn.innerHTML = `
                <span class="truncate">${escapeHtml(name)}</span>
                <span class="layer-chip">${escapeHtml(type)}</span>
            `;
            btn.addEventListener('click', () => setSelection(node));

            const actions = document.createElement('div');
            actions.className = 'layer-actions';

            const visBtn = document.createElement('button');
            visBtn.type = 'button';
            visBtn.className = 'layer-icon-btn' + (visible ? ' is-active' : '');
            visBtn.title = visible ? 'Masquer' : 'Afficher';
            visBtn.textContent = visible ? 'Vis' : 'Hide';
            visBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                node.visible(!visible);
                normalizeNodeState(node);
                if (!node.visible() && selectedNode === node) clearSelection();
                mainLayer?.draw();
                saveHistory();
                refreshLayersList();
                updateInspector();
            });

            const lockBtn = document.createElement('button');
            lockBtn.type = 'button';
            lockBtn.className = 'layer-icon-btn' + (locked ? ' is-active' : '');
            lockBtn.title = locked ? 'Deverrouiller' : 'Verrouiller';
            lockBtn.textContent = locked ? 'Lock' : 'Free';
            lockBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                node.setAttr('amLocked', !locked);
                normalizeNodeState(node);
                if (selectedNode === node) setSelection(node);
                mainLayer?.draw();
                saveHistory();
                refreshLayersList();
                updateInspector();
            });

            actions.appendChild(visBtn);
            actions.appendChild(lockBtn);
            row.appendChild(btn);
            row.appendChild(actions);
            layersList.appendChild(row);
        });
    }
    function newElementId(){ elementCounter++; return 'elem-' + elementCounter; }
    function registerElement(node, type, name) {
        node.setAttr('amId', newElementId());
        node.setAttr('amType', type);
        node.setAttr('amName', sanitizeLayerName(name || type));
        if (typeof node.getAttr('amLocked') === 'undefined') {
            node.setAttr('amLocked', false);
        }
        normalizeNodeState(node);
        saveHistory();
        refreshLayersList();
        updateInspector();
    }

    // ----------------------------
    // Inspector
    // ----------------------------
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

        shapeControls?.classList.toggle('hidden', !['rect', 'circle', 'shape', 'ellipse'].includes(t));
        textControls?.classList.toggle('hidden', t !== 'text');
        imageControls?.classList.toggle('hidden', t !== 'image');

        if (inputLayerName) inputLayerName.value = selectedNode.getAttr('amName') || '';
        if (inputOpacity && opacityValue) {
            const op = Math.round((selectedNode.opacity() ?? 1) * 100);
            inputOpacity.value = op;
            opacityValue.textContent = op + '%';
        }

        if (['rect', 'circle', 'shape', 'ellipse'].includes(t) && shapeFill && shapeStroke && shapeStrokeWidth && strokeWidthValue) {
            shapeFill.value = (selectedNode.fill && selectedNode.fill()) ? selectedNode.fill() : '#e2e8f0';
            shapeStroke.value = (selectedNode.stroke && selectedNode.stroke()) ? selectedNode.stroke() : '#94a3b8';
            const sw = (selectedNode.strokeWidth && selectedNode.strokeWidth()) ?? 0;
            shapeStrokeWidth.value = sw;
            strokeWidthValue.textContent = sw + ' px';
        }

        if (t === 'text' && textFontSize && textColor) {
            textFontSize.value = selectedNode.fontSize?.() ?? 26;
            textColor.value = selectedNode.fill?.() ?? '#111827';
            if (textFontFamily) {
                const family = String(selectedNode.fontFamily?.() || '').toLowerCase();
                const matched = BRANDING_FONTS.find((font) => {
                    const label = String(font?.label || '').toLowerCase();
                    const familyValue = String(font?.family || '').replace(/['"]/g, '').toLowerCase();
                    return (label && family.includes(label)) || (familyValue && family.includes(familyValue.split(',')[0]));
                });
                textFontFamily.value = matched?.key || activeBranding?.fonts?.body || FONT_KEYS[0] || '';
            }
        }

        if (t === 'image') {
            if (imgBrightness) imgBrightness.value = String(clamp(Number(selectedNode.getAttr('amBrightness') ?? 0), -1, 1));
            if (imgContrast) imgContrast.value = String(clamp(Number(selectedNode.getAttr('amContrast') ?? 0), -1, 1));
            if (imgFitMode) imgFitMode.value = selectedNode.getAttr('amFitMode') || 'cover';
        }

        if (btnToggleLock) btnToggleLock.textContent = isNodeLocked(selectedNode) ? 'Deverrouiller' : 'Verrouiller';
        if (btnToggleVisibility) btnToggleVisibility.textContent = isNodeVisible(selectedNode) ? 'Masquer' : 'Afficher';
    }

    // ----------------------------
    // Stage lifecycle
    // ----------------------------
    function destroyStage(clearSelectedFormat = true, clearHistory = true) {
        if (resizeObserver) { resizeObserver.disconnect(); resizeObserver = null; }
        if (stage) { stage.destroy(); stage = null; }
        container.innerHTML = '';
        if (clearHistory) {
            history = [];
            redoStack = [];
            elementCounter = 0;
        }
        selectedNode = null;
        wrapper.style.height = '';
        if (clearSelectedFormat) selectedFormat = null;
        updateHistoryButtons();
    }

    function matchesTemplateSearch(button, term) {
        if (!term) return true;
        const title = String(button.getAttribute('title') || '').toLowerCase();
        const body = String(button.textContent || '').toLowerCase();
        return title.includes(term) || body.includes(term);
    }

    function refreshTemplateButtons() {
        const searchTerm = String(quickTemplateSearch?.value || '').trim().toLowerCase();

        document.querySelectorAll('.js-template-btn').forEach(btn => {
            const fmt = btn.getAttribute('data-format');
            const formatOk = selectedFormat && fmt === selectedFormat.id;
            const searchOk = matchesTemplateSearch(btn, searchTerm);
            const enabled = formatOk && searchOk;

            btn.classList.toggle('hidden', !searchOk);
            btn.classList.toggle('opacity-40', !enabled);
            btn.classList.toggle('pointer-events-none', !enabled);
        });
    }

    function refreshTemplateButtonsDb() {
        const searchTerm = String(dbTemplateSearch?.value || '').trim().toLowerCase();

        document.querySelectorAll('.js-template-db-btn').forEach(btn => {
            const fmt = btn.getAttribute('data-format');
            const formatOk = selectedFormat && fmt === selectedFormat.id;
            const searchOk = matchesTemplateSearch(btn, searchTerm);
            const enabled = formatOk && searchOk;

            btn.classList.toggle('hidden', !searchOk);
            btn.classList.toggle('opacity-40', !enabled);
            btn.classList.toggle('pointer-events-none', !enabled);
        });
    }

    function bindTemplateSearch() {
        quickTemplateSearch?.addEventListener('input', refreshTemplateButtons);
        dbTemplateSearch?.addEventListener('input', refreshTemplateButtonsDb);
    }

    function initStageForFormat(fmt, resetHistory = true) {
        destroyStage(false, resetHistory);
        selectedFormat = fmt;

        if (formatBadge) formatBadge.textContent = `${fmt.w} x ${fmt.h}`;

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
            enabledAnchors: DEFAULT_TRANSFORMER_ANCHORS,
            centeredScaling: true,
            boundBoxFunc: (oldBox, newBox) => {
                if (newBox.width < 30 || newBox.height < 30) return oldBox;
                return newBox;
            }
        });
        mainLayer.add(transformer);
        bindTransformerBehavior();

        stage.on('click tap', (e) => {
            if (e.target === stage || e.target === bgRect) clearSelection();
            else setSelection(e.target);
        });

        enableTextEditing(stage, mainLayer);
        bindStickyGridHandlers();

        applyDisplayScale();
        resizeObserver = new ResizeObserver(() => applyDisplayScale());
        resizeObserver.observe(wrapper);

        if (toggleGrid?.checked) drawGrid(); else clearGrid();

        if (resetHistory) {
            history = [];
            redoStack = [];
            saveHistory();
        }
        applyBrandingToCanvas({ skipHistory: true });
        refreshTemplateButtons();
        refreshTemplateButtonsDb();
        refreshLayersList();
        updateInspector();
        updateHistoryButtons();
    }

    // ----------------------------
    // Primitives
    // ----------------------------
    function addText(text = 'Votre texte') {
        if (!ensureFormatChosen()) return;
        const settings = getBrandingFromControls();
        const node = new Konva.Text({
            x: 100, y: 120, text,
            fontSize: 64,
            fontFamily: fontKeyToFamily(settings.fonts.heading, settings.fonts.body),
            fill: settings.colors.primary,
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

        const poly = (cx, cy, r, sides, rotationDeg = -90) => {
            const rot = rotationDeg * Math.PI / 180;
            const pts = [];
            for (let i = 0; i < sides; i++) {
                const a = rot + (i * 2 * Math.PI / sides);
                pts.push(cx + r * Math.cos(a), cy + r * Math.sin(a));
            }
            return pts;
        };

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
                node = new Konva.Line({ ...common, points: [160, 420, 560, 420, 560, 320, 740, 480, 560, 640, 560, 540, 160, 540], closed: true });
                registerElement(node, 'shape', 'Flèche droite');
                break;
            case 'arrowLeft':
                node = new Konva.Line({ ...common, points: [740, 420, 340, 420, 340, 320, 160, 480, 340, 640, 340, 540, 740, 540], closed: true });
                registerElement(node, 'shape', 'Flèche gauche');
                break;
            case 'arrowUp':
                node = new Konva.Line({ ...common, points: [360, 180, 520, 360, 420, 360, 420, 720, 300, 720, 300, 360, 200, 360], closed: true });
                registerElement(node, 'shape', 'Flèche haut');
                break;
            case 'arrowDown':
                node = new Konva.Line({ ...common, points: [360, 780, 520, 600, 420, 600, 420, 240, 300, 240, 300, 600, 200, 600], closed: true });
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

    function ensureImageFilters(node) {
        if (!node || node.getAttr('amType') !== 'image' || !node.image?.()) return;
        node.filters([Konva.Filters.Brighten, Konva.Filters.Contrast]);
        try {
            node.clearCache();
            node.cache();
        } catch (e) {}
    }

    function applyImageAdjustments(node, shouldSave = false) {
        if (!node || node.getAttr('amType') !== 'image') return;
        const brightness = clamp(Number(node.getAttr('amBrightness') ?? 0), -1, 1);
        const contrast = clamp(Number(node.getAttr('amContrast') ?? 0), -1, 1);
        node.setAttr('amBrightness', brightness);
        node.setAttr('amContrast', contrast);
        ensureImageFilters(node);
        node.brightness(brightness);
        node.contrast(contrast * 100);
        mainLayer?.draw();
        if (shouldSave) saveHistory();
    }

    function fitImageNode(node, mode = 'cover', shouldSave = true) {
        if (!selectedFormat || !node || node.getAttr('amType') !== 'image') return;

        const image = node.image?.();
        const naturalW = Number(node.getAttr('amImageOriginalW')) || image?.naturalWidth || image?.width || node.width();
        const naturalH = Number(node.getAttr('amImageOriginalH')) || image?.naturalHeight || image?.height || node.height();
        if (!naturalW || !naturalH) return;

        const canvasW = selectedFormat.w;
        const canvasH = selectedFormat.h;
        let width = canvasW;
        let height = canvasH;
        let x = 0;
        let y = 0;

        if (mode === 'contain') {
            const scale = Math.min(canvasW / naturalW, canvasH / naturalH);
            width = naturalW * scale;
            height = naturalH * scale;
            x = (canvasW - width) / 2;
            y = (canvasH - height) / 2;
        } else if (mode === 'cover') {
            const scale = Math.max(canvasW / naturalW, canvasH / naturalH);
            width = naturalW * scale;
            height = naturalH * scale;
            x = (canvasW - width) / 2;
            y = (canvasH - height) / 2;
        }

        node.setAttr('amFitMode', mode);
        node.x(x);
        node.y(y);
        node.width(width);
        node.height(height);
        applyImageAdjustments(node, false);
        mainLayer?.draw();
        if (shouldSave) saveHistory();
    }

    function loadImageFromFile(file) {
        return new Promise((resolve, reject) => {
            if (!file || !file.type?.startsWith('image/')) {
                reject(new Error('Invalid image file'));
                return;
            }
            const reader = new FileReader();
            reader.onload = () => {
                const img = new Image();
                img.onload = () => resolve(img);
                img.onerror = reject;
                img.src = String(reader.result);
            };
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    async function handleImageFile(file, options = {}) {
        if (!ensureFormatChosen() || !file) return;

        const replaceSelected = !!options.replaceSelected
            && selectedNode
            && selectedNode.getAttr('amType') === 'image';

        try {
            const imgObj = await loadImageFromFile(file);
            const src = imgObj.currentSrc || imgObj.src || '';
            if (replaceSelected) {
                selectedNode.image(imgObj);
                selectedNode.setAttr('amImageSrc', src);
                selectedNode.setAttr('amImageOriginalW', imgObj.naturalWidth || imgObj.width || selectedNode.width());
                selectedNode.setAttr('amImageOriginalH', imgObj.naturalHeight || imgObj.height || selectedNode.height());
                fitImageNode(selectedNode, selectedNode.getAttr('amFitMode') || 'cover', false);
                applyImageAdjustments(selectedNode, false);
                mainLayer?.draw();
                saveHistory();
                updateInspector();
                return;
            }

            const kImg = new Konva.Image({
                image: imgObj,
                x: 0,
                y: 0,
                width: selectedFormat.w,
                height: selectedFormat.h,
                draggable: true
            });
            kImg.setAttr('amImageSrc', src);
            kImg.setAttr('amImageOriginalW', imgObj.naturalWidth || imgObj.width || selectedFormat.w);
            kImg.setAttr('amImageOriginalH', imgObj.naturalHeight || imgObj.height || selectedFormat.h);
            kImg.setAttr('amBrightness', 0);
            kImg.setAttr('amContrast', 0);
            kImg.setAttr('amFitMode', 'cover');
            mainLayer.add(kImg);
            registerElement(kImg, 'image', 'Image');
            fitImageNode(kImg, 'cover', false);
            setSelection(kImg);
            mainLayer.draw();
            saveHistory();
        } catch (err) {
            console.error(err);
            alert("Impossible de charger l'image.");
        }
    }

    function hydrateImageNodes() {
        getEditableNodes().forEach((node) => {
            if (node.getAttr('amType') !== 'image') return;
            const src = node.getAttr('amImageSrc');
            if (!src) return;
            const img = new Image();
            img.onload = () => {
                node.image(img);
                applyImageAdjustments(node, false);
                mainLayer?.draw();
            };
            img.src = src;
        });
    }

    function clearCanvas() {
        if (!mainLayer) return;

        const children = mainLayer.getChildren();
        const arr = Array.isArray(children)
            ? children
            : (typeof children.toArray === 'function' ? children.toArray() : Array.from(children || []));

        arr.forEach((n) => {
            if (transformer && n === transformer) return;
            if (n && typeof n.destroy === 'function') n.destroy();
        });

        if (!transformer || transformer.isDestroyed?.()) {
            transformer = new Konva.Transformer({
                name: 'mainTransformer',
                rotateEnabled: true,
                enabledAnchors: DEFAULT_TRANSFORMER_ANCHORS,
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
        bindTransformerBehavior();

        transformer.nodes([]);
        transformer.moveToTop();

        clearSelection();
        mainLayer.draw();
        saveHistory();
        refreshLayersList();
        updateInspector();
    }

    function alignSelection(position) {
        if (!selectedNode || !stage || isNodeLocked(selectedNode)) return;
        const rect = selectedNode.getClientRect({ relativeTo: stage });
        let dx = 0;
        let dy = 0;

        if (position === 'left') dx = -rect.x;
        if (position === 'centerX') dx = (stage.width() / 2) - (rect.x + rect.width / 2);
        if (position === 'right') dx = stage.width() - (rect.x + rect.width);
        if (position === 'top') dy = -rect.y;
        if (position === 'centerY') dy = (stage.height() / 2) - (rect.y + rect.height / 2);
        if (position === 'bottom') dy = stage.height() - (rect.y + rect.height);

        selectedNode.x(selectedNode.x() + dx);
        selectedNode.y(selectedNode.y() + dy);
        mainLayer.draw();
        saveHistory();
        updateInspector();
    }

    function deleteSelection() {
        if (!selectedNode) return;
        selectedNode.destroy();
        clearSelection();
        mainLayer?.draw();
        saveHistory();
    }

    function duplicateSelection() {
        if (!selectedNode || !mainLayer) return;
        const clone = selectedNode.clone({
            x: selectedNode.x() + 30,
            y: selectedNode.y() + 30,
            draggable: true
        });
        clone.setAttr('amLocked', false);
        clone.visible(true);
        mainLayer.add(clone);
        registerElement(
            clone,
            clone.getAttr('amType') || clone.getClassName(),
            `${sanitizeLayerName(clone.getAttr('amName') || 'Element')} (copie)`
        );
        setSelection(clone);
        mainLayer.draw();
        saveHistory();
    }

    function copySelection() {
        if (!selectedNode) return;
        clipboardNode = selectedNode.clone({ draggable: true });
        clipboardNode.setAttr('amLocked', false);
        pasteOffset = 0;
    }

    function pasteSelection() {
        if (!clipboardNode || !mainLayer) return;
        pasteOffset += 24;
        const clone = clipboardNode.clone({
            x: (clipboardNode.x() || 60) + pasteOffset,
            y: (clipboardNode.y() || 60) + pasteOffset,
            draggable: true
        });
        clone.setAttr('amLocked', false);
        clone.visible(true);
        mainLayer.add(clone);
        registerElement(
            clone,
            clone.getAttr('amType') || clone.getClassName(),
            `${sanitizeLayerName(clone.getAttr('amName') || 'Element')} (copie)`
        );
        setSelection(clone);
        mainLayer.draw();
        saveHistory();
    }

    function nudgeSelection(dx, dy) {
        if (!selectedNode || isNodeLocked(selectedNode)) return;
        selectedNode.x(selectedNode.x() + dx);
        selectedNode.y(selectedNode.y() + dy);
        mainLayer.draw();
        saveHistory();
        updateInspector();
    }

    // ----------------------------
    // DB Templates (load from API)
    // ----------------------------
    async function applyDbTemplate(templateId) {
        if (!ensureFormatChosen()) return;

        try {
            const res = await fetch(`/api/design-templates/${templateId}`, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Template introuvable');

            const data = await res.json();
            if (!data || !data.konva_json) throw new Error('Template invalide');
            if (data.format_id !== selectedFormat.id) return;

            const json = (typeof data.konva_json === 'string') ? data.konva_json : JSON.stringify(data.konva_json);
            restoreFromJson(json);
            autoFillTemplateFromContext(data.category || data.name || data.id || '');
            applyBrandingToCanvas({ skipHistory: false });
            saveHistory();

        } catch (err) {
            console.error(err);
            alert('Impossible de charger le template.');
        }
    }

    // ----------------------------
    // Admin save template button
    // ----------------------------
    function mountAdminSaveButton() {
        if (!ADMIN_MODE) return;

        const exportBtn = document.getElementById('btnExport');

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'btnAdminSaveTemplate';
        btn.className = 'pill-btn pill-btn-main';
        btn.textContent = ADMIN_EDITING_TEMPLATE ? '💾 Mettre à jour le template' : '💾 Enregistrer comme template';

        btn.addEventListener('click', async () => {
            if (!ensureFormatChosen() || !stage) return;

            const defaultName = ADMIN_EDITING_TEMPLATE?.name || 'Template';
            const name = prompt('Nom du template :', defaultName);
            if (!name) return;

            const defaultCat = ADMIN_EDITING_TEMPLATE?.category || 'event';
            const category = prompt('Catégorie (event/promo/quote/...) :', defaultCat) || defaultCat;

            const payload = {
                name,
                category,
                format_id: selectedFormat.id,
                konva_json: stage.toJSON(),
                preview_base64: stage.toDataURL({ pixelRatio: 0.25 }),
                is_active: true,
            };

            const url = ADMIN_EDITING_TEMPLATE
                ? `/admin/design-templates/${ADMIN_EDITING_TEMPLATE.id}`
                : `/admin/design-templates`;

            const method = ADMIN_EDITING_TEMPLATE ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) {
                    const txt = await res.text();
                    console.error(txt);
                    alert('Erreur lors de l’enregistrement.');
                    return;
                }

                const data = await res.json();
                alert('Template enregistré ✅');

                if (!ADMIN_EDITING_TEMPLATE && data?.id) {
                    window.location.href = `/admin/design-templates/${data.id}/edit`;
                }

            } catch (e) {
                console.error(e);
                alert('Erreur réseau.');
            }
        });

        if (exportBtn && exportBtn.parentElement) {
            exportBtn.parentElement.insertBefore(btn, exportBtn);
        } else {
            document.body.prepend(btn);
        }
    }

    // ----------------------------
    // Retro templates (config) — kept as-is
    // ----------------------------
    function applyTemplate(templateId) {
        if (!ensureFormatChosen()) return;

        const aliases = {
            quote: 'quote_minimal_square',
            promo: 'promo_flash_square',
            event: 'event_masterclass_square',
            testimonial: 'testimonial_proof_square',
            before_after: 'before_after_square',
            tip_story: 'story_tip_vertical',
            event_story: 'story_announcement_vertical',
            checklist_landscape: 'checklist_landscape_pro',
        };

        const resolvedId = aliases[templateId] || templateId;
        const tpl = TEMPLATES.find(t => t.id === resolvedId);
        if (!tpl || tpl.format_id !== selectedFormat.id) return;

        clearCanvas();

        const W = selectedFormat.w;
        const H = selectedFormat.h;

        const addR = (o) => {
            const node = new Konva.Rect({
                x: o.x ?? 0,
                y: o.y ?? 0,
                width: o.w ?? 200,
                height: o.h ?? 120,
                fill: o.fill ?? '#e2e8f0',
                stroke: o.stroke,
                strokeWidth: o.strokeWidth ?? 0,
                cornerRadius: o.r ?? 0,
                shadowColor: o.shadowColor,
                shadowBlur: o.shadowBlur ?? 0,
                shadowOffset: o.shadowOffset ?? { x: 0, y: 0 },
                shadowOpacity: o.shadowOpacity ?? 0,
                draggable: true
            });
            mainLayer.add(node);
            registerElement(node, 'rect', o.name || 'Bloc');
            return node;
        };

        const addC = (o) => {
            const node = new Konva.Circle({
                x: o.x ?? 200,
                y: o.y ?? 200,
                radius: o.radius ?? 80,
                fill: o.fill ?? '#e2e8f0',
                stroke: o.stroke,
                strokeWidth: o.strokeWidth ?? 0,
                opacity: o.opacity ?? 1,
                draggable: true
            });
            mainLayer.add(node);
            registerElement(node, 'circle', o.name || 'Cercle');
            return node;
        };

        const addT = (o) => {
            const node = new Konva.Text({
                x: o.x ?? 0,
                y: o.y ?? 0,
                width: o.w ?? (W - 80),
                text: o.text ?? 'Texte',
                fontSize: o.size ?? 48,
                fontFamily: o.font ?? 'Verdana',
                fontStyle: o.style ?? 'normal',
                fill: o.fill ?? '#111827',
                align: o.align ?? 'left',
                lineHeight: o.lh ?? 1.15,
                draggable: true
            });
            mainLayer.add(node);
            registerElement(node, 'text', o.name || 'Texte');
            return node;
        };

        const addTag = (text, options = {}) => {
            const x = options.x ?? 70;
            const y = options.y ?? 60;
            const w = options.w ?? 360;
            const h = options.h ?? 58;
            const bg = options.bg ?? '#dcfce7';
            const color = options.color ?? '#14532d';

            addR({ x, y, w, h, fill: bg, r: 999, name: 'Tag' });
            addT({
                x: x + 20,
                y: y + 15,
                w: w - 40,
                text,
                size: 24,
                style: 'bold',
                fill: color,
                align: 'center',
                name: 'Tag texte'
            });
        };

        const addFooter = (text) => {
            addT({
                x: 60,
                y: H - 74,
                w: W - 120,
                text,
                size: 24,
                fill: '#64748b',
                align: 'center',
                name: 'Footer'
            });
        };

        switch (resolvedId) {
            case 'quote_minimal_square': {
                setBgColor('#f8f5ef');
                addC({ x: W - 120, y: 130, radius: 230, fill: '#d9efe2', opacity: 0.8, name: 'Accent cercle' });
                addR({
                    x: 62, y: 120, w: W - 124, h: H - 220,
                    fill: '#ffffff', r: 34,
                    shadowColor: 'rgba(15,23,42,0.12)', shadowBlur: 24, shadowOffset: { x: 0, y: 10 }, shadowOpacity: 0.35,
                    name: 'Carte'
                });
                addTag('CITATION DU JOUR');
                addT({
                    x: 130, y: 280, w: W - 260,
                    text: '"La constance est la cle d une progression durable."',
                    size: 72, font: 'Georgia', fill: '#0f172a', align: 'center', lh: 1.2, name: 'Citation'
                });
                addT({
                    x: 140, y: 785, w: W - 280,
                    text: '- Votre nom / marque',
                    size: 34, style: 'bold', fill: '#334155', align: 'center', name: 'Auteur'
                });
                addFooter('Personnalisez le texte puis exportez');
                break;
            }

            case 'promo_flash_square': {
                setBgColor('#111827');
                addR({ x: 0, y: 0, w: W, h: 260, fill: '#0f172a', r: 0, name: 'Header' });
                addR({ x: 50, y: 86, w: 350, h: 64, fill: '#facc15', r: 999, name: 'Badge promo' });
                addT({ x: 82, y: 106, w: 286, text: 'OFFRE LIMITEE', size: 28, style: 'bold', fill: '#111827', align: 'center', name: 'Badge texte' });
                addT({
                    x: 70, y: 220, w: W - 140,
                    text: 'Pack Bien-etre\nSession decouverte',
                    size: 88, style: 'bold', fill: '#f8fafc', align: 'left', lh: 0.95, name: 'Titre'
                });
                addR({ x: 70, y: 540, w: 340, h: 220, fill: '#22c55e', r: 24, name: 'Bloc remise' });
                addT({ x: 80, y: 585, w: 320, text: '-30%', size: 116, style: 'bold', fill: '#052e16', align: 'center', name: 'Remise' });
                addT({
                    x: 440, y: 575, w: W - 520,
                    text: 'Code: BIENVENUE30\nValable jusqu au 30/09',
                    size: 42, style: 'bold', fill: '#e2e8f0', lh: 1.2, name: 'Details'
                });
                addR({ x: 430, y: 790, w: 580, h: 130, fill: '#f8fafc', r: 22, name: 'CTA' });
                addT({ x: 460, y: 835, w: 520, text: 'Reserver maintenant', size: 46, style: 'bold', fill: '#0f172a', align: 'center', name: 'CTA texte' });
                addFooter('Sans engagement - modifiez le prix selon votre offre');
                break;
            }

            case 'event_masterclass_square': {
                setBgColor('#f4efe7');
                addR({ x: 0, y: 0, w: W, h: 220, fill: '#1e293b', r: 0, name: 'Header' });
                addT({ x: 70, y: 56, w: W - 140, text: 'MASTERCLASS EN LIGNE', size: 46, style: 'bold', fill: '#e2e8f0', align: 'center', name: 'Sur titre' });
                addR({ x: 90, y: 270, w: W - 180, h: 670, fill: '#ffffff', r: 36, shadowColor: 'rgba(15,23,42,0.15)', shadowBlur: 24, shadowOffset: { x: 0, y: 12 }, shadowOpacity: 0.35, name: 'Carte' });
                addTag('MARDI 12 NOVEMBRE - 19H00', { x: 200, y: 330, w: W - 400, h: 68, bg: '#dcfce7', color: '#166534' });
                addT({
                    x: 150, y: 455, w: W - 300,
                    text: 'Comment structurer\nvotre activite en 2026',
                    size: 84, font: 'Georgia', fill: '#0f172a', align: 'center', lh: 1.04, name: 'Titre'
                });
                addT({
                    x: 170, y: 700, w: W - 340,
                    text: 'Avec Prenom Nom - Session interactive',
                    size: 34, fill: '#475569', align: 'center', name: 'Intervenant'
                });
                addR({ x: 220, y: 790, w: W - 440, h: 108, fill: '#1e293b', r: 24, name: 'CTA' });
                addT({ x: 245, y: 826, w: W - 490, text: 'Inscription gratuite', size: 42, style: 'bold', fill: '#f8fafc', align: 'center', name: 'CTA texte' });
                break;
            }

            case 'testimonial_proof_square': {
                setBgColor('#f8fafc');
                addTag('AVIS CLIENT', { x: 70, y: 60, w: 250, h: 58, bg: '#dbeafe', color: '#1d4ed8' });
                addC({ x: 215, y: 300, radius: 120, fill: '#cbd5e1', name: 'Photo profil' });
                addT({ x: 145, y: 430, w: 140, text: 'Photo', size: 30, style: 'bold', fill: '#334155', align: 'center', name: 'Placeholder photo' });
                addR({ x: 360, y: 180, w: 650, h: 640, fill: '#ffffff', r: 28, shadowColor: 'rgba(15,23,42,0.12)', shadowBlur: 18, shadowOffset: { x: 0, y: 8 }, shadowOpacity: 0.3, name: 'Carte avis' });
                addT({
                    x: 420, y: 260, w: 530,
                    text: '★★★★★',
                    size: 54, style: 'bold', fill: '#f59e0b', align: 'left', name: 'Etoiles'
                });
                addT({
                    x: 420, y: 350, w: 530,
                    text: '"Resultat visible en 4 semaines,\norganisation simplifiee et clients ravis."',
                    size: 48, fill: '#0f172a', lh: 1.2, name: 'Avis'
                });
                addT({ x: 420, y: 660, w: 530, text: 'Camille - Lyon', size: 34, style: 'bold', fill: '#334155', name: 'Nom client' });
                addFooter('Ajoutez votre vrai avis et votre preuve sociale');
                break;
            }

            case 'before_after_square': {
                setBgColor('#eef2ff');
                addTag('AVANT / APRES', { x: 70, y: 60, w: 320, h: 58, bg: '#c7d2fe', color: '#3730a3' });
                addR({ x: 70, y: 170, w: (W - 170) / 2, h: 680, fill: '#e2e8f0', r: 24, name: 'Avant image' });
                addR({ x: 100 + (W - 170) / 2, y: 170, w: (W - 170) / 2, h: 680, fill: '#e2e8f0', r: 24, name: 'Apres image' });
                addT({ x: 90, y: 880, w: (W - 170) / 2 - 40, text: 'AVANT', size: 36, style: 'bold', fill: '#334155', align: 'center', name: 'Label avant' });
                addT({ x: 120 + (W - 170) / 2, y: 880, w: (W - 170) / 2 - 40, text: 'APRES', size: 36, style: 'bold', fill: '#334155', align: 'center', name: 'Label apres' });
                addFooter('Ajoutez vos visuels et votre resultat en 1 phrase');
                break;
            }

            case 'carousel_cover_square': {
                setBgColor('#0f172a');
                addC({ x: 930, y: 170, radius: 220, fill: '#14532d', opacity: 0.55, name: 'Accent cercle 1' });
                addC({ x: 170, y: 980, radius: 300, fill: '#1d4ed8', opacity: 0.25, name: 'Accent cercle 2' });
                addR({ x: 70, y: 80, w: 200, h: 54, fill: '#facc15', r: 999, name: 'Badge slide' });
                addT({ x: 92, y: 97, w: 156, text: 'SLIDE 1/5', size: 22, style: 'bold', fill: '#111827', align: 'center', name: 'Badge texte' });
                addT({
                    x: 90, y: 220, w: W - 180,
                    text: '5 actions concretes\npour gagner 5h\nchaque semaine',
                    size: 96, style: 'bold', fill: '#f8fafc', lh: 0.97, name: 'Titre cover'
                });
                addT({
                    x: 95, y: 705, w: W - 190,
                    text: 'Simple, sans outils supplementaires, applicable aujourd hui.',
                    size: 34, fill: '#cbd5e1', lh: 1.25, name: 'Sous titre'
                });
                addR({ x: 90, y: 860, w: W - 180, h: 120, fill: '#f8fafc', r: 22, name: 'CTA cover' });
                addT({ x: 120, y: 900, w: W - 240, text: 'Glissez pour voir les 4 etapes suivantes', size: 36, style: 'bold', fill: '#0f172a', align: 'center', name: 'CTA texte' });
                break;
            }

            case 'story_announcement_vertical': {
                setBgColor('#0f172a');
                addC({ x: 940, y: 170, radius: 220, fill: '#22c55e', opacity: 0.35, name: 'Accent haut' });
                addC({ x: 120, y: 1730, radius: 260, fill: '#38bdf8', opacity: 0.28, name: 'Accent bas' });
                addTag('NOUVELLE SESSION', { x: 90, y: 120, w: 420, h: 70, bg: '#22c55e', color: '#052e16' });
                addT({
                    x: 100, y: 360, w: W - 200,
                    text: 'Ouverture\ndes reservations',
                    size: 118, style: 'bold', fill: '#f8fafc', align: 'center', lh: 0.95, name: 'Titre story'
                });
                addT({ x: 140, y: 860, w: W - 280, text: 'Mardi 18h30 - Places limitees', size: 44, fill: '#cbd5e1', align: 'center', name: 'Date story' });
                addR({ x: 110, y: 1530, w: W - 220, h: 130, fill: '#f8fafc', r: 26, name: 'CTA story' });
                addT({ x: 140, y: 1575, w: W - 280, text: 'Repondre: JE RESERVE', size: 44, style: 'bold', fill: '#0f172a', align: 'center', name: 'CTA texte' });
                addFooter('Modifiez date, titre et appel action');
                break;
            }

            case 'story_tip_vertical': {
                setBgColor('#f0fdf4');
                addTag('ASTUCE RAPIDE', { x: 90, y: 110, w: 360, h: 66, bg: '#dcfce7', color: '#14532d' });
                addT({ x: 90, y: 250, w: W - 180, text: 'Routine anti-stress\nen 3 etapes', size: 88, style: 'bold', fill: '#14532d', lh: 1.03, name: 'Titre astuce story' });
                addR({ x: 90, y: 620, w: W - 180, h: 240, fill: '#ffffff', r: 26, name: 'Etape 1 bloc' });
                addR({ x: 90, y: 900, w: W - 180, h: 240, fill: '#ffffff', r: 26, name: 'Etape 2 bloc' });
                addR({ x: 90, y: 1180, w: W - 180, h: 240, fill: '#ffffff', r: 26, name: 'Etape 3 bloc' });
                addT({ x: 130, y: 680, w: W - 260, text: '1. Respirez 4-4-6 pendant 2 minutes', size: 44, fill: '#0f172a', lh: 1.2, name: 'Etape 1 texte' });
                addT({ x: 130, y: 960, w: W - 260, text: '2. Etirez nuque et epaules 60 secondes', size: 44, fill: '#0f172a', lh: 1.2, name: 'Etape 2 texte' });
                addT({ x: 130, y: 1240, w: W - 260, text: '3. Notez 1 action prioritaire du jour', size: 44, fill: '#0f172a', lh: 1.2, name: 'Etape 3 texte' });
                addFooter('Contenu editable ligne par ligne');
                break;
            }

            case 'story_countdown_vertical': {
                setBgColor('#111827');
                addTag('JOURNEE SPECIALE', { x: 90, y: 120, w: 390, h: 64, bg: '#facc15', color: '#111827' });
                addT({ x: 90, y: 280, w: W - 180, text: 'Demarrage dans', size: 64, fill: '#cbd5e1', align: 'center', name: 'Intro countdown' });
                addR({ x: 120, y: 430, w: W - 240, h: 360, fill: '#f8fafc', r: 28, name: 'Bloc countdown' });
                addT({ x: 150, y: 520, w: W - 300, text: 'J-05', size: 180, style: 'bold', fill: '#0f172a', align: 'center', name: 'Countdown' });
                addT({ x: 120, y: 860, w: W - 240, text: 'Lundi 14 Octobre - 20h00', size: 52, style: 'bold', fill: '#f8fafc', align: 'center', name: 'Date countdown' });
                addR({ x: 160, y: 1510, w: W - 320, h: 130, fill: '#22c55e', r: 24, name: 'CTA countdown' });
                addT({ x: 190, y: 1554, w: W - 380, text: 'Activer le rappel', size: 44, style: 'bold', fill: '#052e16', align: 'center', name: 'CTA texte' });
                break;
            }

            case 'story_client_review_vertical': {
                setBgColor('#fff7ed');
                addTag('AVIS 5 ETOILES', { x: 90, y: 110, w: 360, h: 64, bg: '#fed7aa', color: '#9a3412' });
                addT({ x: 95, y: 260, w: W - 190, text: '★★★★★', size: 88, style: 'bold', fill: '#f59e0b', align: 'center', name: 'Etoiles story' });
                addR({ x: 80, y: 430, w: W - 160, h: 780, fill: '#ffffff', r: 30, shadowColor: 'rgba(15,23,42,0.10)', shadowBlur: 16, shadowOffset: { x: 0, y: 8 }, shadowOpacity: 0.28, name: 'Carte avis story' });
                addT({
                    x: 130, y: 560, w: W - 260,
                    text: '"Experience claire, accompagnement humain,\net resultat concret en quelques semaines."',
                    size: 56, font: 'Georgia', fill: '#0f172a', align: 'center', lh: 1.25, name: 'Texte avis story'
                });
                addT({ x: 130, y: 980, w: W - 260, text: 'Emma - Marseille', size: 42, style: 'bold', fill: '#334155', align: 'center', name: 'Auteur story' });
                addR({ x: 130, y: 1460, w: W - 260, h: 120, fill: '#0f172a', r: 24, name: 'CTA avis story' });
                addT({ x: 160, y: 1500, w: W - 320, text: 'Voir tous les avis', size: 40, style: 'bold', fill: '#f8fafc', align: 'center', name: 'CTA texte' });
                break;
            }

            case 'webinar_banner_landscape': {
                setBgColor('#0f172a');
                addC({ x: 1710, y: 160, radius: 210, fill: '#22c55e', opacity: 0.4, name: 'Accent 1' });
                addC({ x: 1620, y: 920, radius: 240, fill: '#2563eb', opacity: 0.28, name: 'Accent 2' });
                addTag('WEBINAR EN DIRECT', { x: 90, y: 70, w: 400, h: 60, bg: '#facc15', color: '#111827' });
                addT({
                    x: 100, y: 185, w: 1100,
                    text: '3 leviers pour remplir\nvotre agenda sans pub',
                    size: 104, style: 'bold', fill: '#f8fafc', lh: 0.98, name: 'Titre webinar'
                });
                addT({ x: 105, y: 505, w: 930, text: 'Mardi 21 octobre - 19h00 - Places limitees', size: 46, fill: '#cbd5e1', name: 'Sous titre webinar' });
                addR({ x: 100, y: 640, w: 560, h: 140, fill: '#f8fafc', r: 24, name: 'CTA webinar' });
                addT({ x: 135, y: 690, w: 490, text: 'Je reserve ma place', size: 48, style: 'bold', fill: '#0f172a', align: 'center', name: 'CTA texte webinar' });
                addR({ x: 1280, y: 170, w: 560, h: 760, fill: '#e2e8f0', r: 34, name: 'Zone visuel' });
                addT({ x: 1380, y: 510, w: 360, text: 'Visuel\nintervenant', size: 54, style: 'bold', fill: '#475569', align: 'center', lh: 1.05, name: 'Placeholder visuel' });
                break;
            }

            case 'youtube_thumbnail_landscape': {
                setBgColor('#0b1120');
                addR({ x: 0, y: 0, w: W, h: H, fill: '#0b1120', r: 0, name: 'Fond dark' });
                addR({ x: 0, y: H - 210, w: W, h: 210, fill: '#22c55e', r: 0, name: 'Bande basse' });
                addR({ x: 70, y: 70, w: 260, h: 64, fill: '#ef4444', r: 10, name: 'Badge live' });
                addT({ x: 90, y: 90, w: 220, text: 'NOUVEAU', size: 34, style: 'bold', fill: '#ffffff', align: 'center', name: 'Badge texte' });
                addT({
                    x: 80, y: 180, w: 1120,
                    text: 'GAGNEZ 5H\nPAR SEMAINE',
                    size: 150, style: 'bold', fill: '#f8fafc', lh: 0.9, name: 'Titre thumbnail'
                });
                addT({
                    x: 90, y: 640, w: 1060,
                    text: 'Sans outils supplementaires - methode concrete',
                    size: 52, style: 'bold', fill: '#d1fae5', name: 'Sous titre thumbnail'
                });
                addR({ x: 1240, y: 120, w: 620, h: 840, fill: '#334155', r: 28, name: 'Zone portrait' });
                addT({ x: 1380, y: 500, w: 340, text: 'Photo', size: 84, style: 'bold', fill: '#cbd5e1', align: 'center', name: 'Placeholder portrait' });
                break;
            }

            case 'checklist_landscape_pro': {
                setBgColor('#f8fafc');
                addTag('CHECKLIST ACTION', { x: 80, y: 60, w: 320, h: 56, bg: '#dbeafe', color: '#1e3a8a' });
                addT({
                    x: 90, y: 160, w: W - 180,
                    text: 'Plan de la semaine',
                    size: 86, style: 'bold', fill: '#0f172a', name: 'Titre checklist'
                });
                addR({ x: 80, y: 300, w: W - 160, h: 640, fill: '#ffffff', r: 24, shadowColor: 'rgba(15,23,42,0.10)', shadowBlur: 14, shadowOffset: { x: 0, y: 8 }, shadowOpacity: 0.25, name: 'Carte checklist' });
                addT({
                    x: 130, y: 370, w: (W / 2) - 180,
                    text: '1. Mettre a jour vos disponibilites\n2. Envoyer 1 relance douce\n3. Publier 1 contenu preuve',
                    size: 46, fill: '#0f172a', lh: 1.5, name: 'Checklist gauche'
                });
                addT({
                    x: (W / 2) + 20, y: 370, w: (W / 2) - 150,
                    text: '4. Demander 1 avis client\n5. Analyser vos resultats\n6. Planifier la semaine suivante',
                    size: 46, fill: '#0f172a', lh: 1.5, name: 'Checklist droite'
                });
                addR({ x: 130, y: 810, w: W - 260, h: 92, fill: '#0f172a', r: 18, name: 'Footer checklist' });
                addT({ x: 170, y: 840, w: W - 340, text: 'Cochez, adaptez, publiez', size: 40, style: 'bold', fill: '#f8fafc', align: 'center', name: 'Footer texte checklist' });
                break;
            }

            default:
                return;
        }

        mainLayer.draw();
        autoFillTemplateFromContext(resolvedId);
        applyBrandingToCanvas({ skipHistory: false });
        saveHistory();
        refreshLayersList();
        updateInspector();
    }
    // ----------------------------
    // Export
    // ----------------------------
    function exportPng() {
        if (!ensureFormatChosen() || !stage) return;
        const dataURL = stage.toDataURL({ pixelRatio: 2 });
        const a = document.createElement('a');
        a.href = dataURL;
        a.download = `aromamade-${selectedFormat.id}.png`;
        document.body.appendChild(a);
        a.click();
        a.remove();
    }

    // ----------------------------
    // Format modal rendering
    // ----------------------------
    function renderFormatCards() {
        if (!formatsGrid) return;
        formatsGrid.innerHTML = '';

        FORMATS.forEach(fmt => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'toolbar-card text-left hover:shadow-md transition';
            btn.innerHTML = `
                <div class="text-sm font-semibold text-slate-900">${escapeHtml(fmt.label)}</div>
                <div class="mt-1 text-[12px] text-slate-600">${fmt.w} x ${fmt.h}</div>
                <div class="mt-1 text-[11px] text-slate-500">${escapeHtml(fmt.hint || '')}</div>
            `;
            btn.addEventListener('click', () => {
                initStageForFormat(fmt);
                closeFormatModal();
            });
            formatsGrid.appendChild(btn);
        });
    }

    function bindCanvasDragDrop() {
        const prevent = (e) => {
            e.preventDefault();
            e.stopPropagation();
        };

        wrapper.addEventListener('dragenter', (e) => {
            prevent(e);
            wrapper.classList.add('is-drop-target');
        });
        wrapper.addEventListener('dragover', (e) => {
            prevent(e);
            wrapper.classList.add('is-drop-target');
        });
        wrapper.addEventListener('dragleave', (e) => {
            prevent(e);
            if (e.target === wrapper) wrapper.classList.remove('is-drop-target');
        });
        wrapper.addEventListener('drop', async (e) => {
            prevent(e);
            wrapper.classList.remove('is-drop-target');
            const file = e.dataTransfer?.files?.[0];
            if (!file || !file.type?.startsWith('image/')) return;
            await handleImageFile(file, { replaceSelected: selectedNode?.getAttr?.('amType') === 'image' });
        });
    }

    function bindKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            const tag = (e.target?.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'textarea' || tag === 'select' || e.target?.isContentEditable) return;

            const mod = e.ctrlKey || e.metaKey;
            const key = e.key.toLowerCase();

            if (mod && key === 'z') {
                e.preventDefault();
                if (e.shiftKey) redo();
                else undo();
                return;
            }
            if (mod && key === 'y') {
                e.preventDefault();
                redo();
                return;
            }
            if (mod && key === 'd') {
                e.preventDefault();
                duplicateSelection();
                return;
            }
            if (mod && key === 'c') {
                if (!selectedNode) return;
                e.preventDefault();
                copySelection();
                return;
            }
            if (mod && key === 'v') {
                if (!clipboardNode) return;
                e.preventDefault();
                pasteSelection();
                return;
            }

            if (e.key === 'Delete' || e.key === 'Backspace') {
                if (!selectedNode) return;
                e.preventDefault();
                deleteSelection();
                return;
            }

            if (!selectedNode) return;
            const step = e.shiftKey ? 10 : 1;
            if (e.key === 'ArrowLeft') { e.preventDefault(); nudgeSelection(-step, 0); }
            if (e.key === 'ArrowRight') { e.preventDefault(); nudgeSelection(step, 0); }
            if (e.key === 'ArrowUp') { e.preventDefault(); nudgeSelection(0, -step); }
            if (e.key === 'ArrowDown') { e.preventDefault(); nudgeSelection(0, step); }
        });
    }

    // ----------------------------
    // Wire UI
    // ----------------------------
    btnModeQuick?.addEventListener('click', () => setUiMode('quick'));
    btnModeExpert?.addEventListener('click', () => setUiMode('expert'));

    workflowStepButtons.forEach((btn) => {
        btn.addEventListener('click', () => focusWorkflowSection(btn.getAttribute('data-workflow-target')));
    });

    btnChooseFormat?.addEventListener('click', openFormatModal);
    btnChooseFormatInline?.addEventListener('click', openFormatModal);
    btnCloseFormatModal?.addEventListener('click', closeFormatModal);

    btnExport?.addEventListener('click', exportPng);
    btnExportInline?.addEventListener('click', exportPng);
    btnClearCanvas?.addEventListener('click', () => {
        clearCanvas();
        applyBrandingToCanvas({ skipHistory: false });
    });
    btnClearCanvasInline?.addEventListener('click', () => {
        clearCanvas();
        applyBrandingToCanvas({ skipHistory: false });
    });

    btnAddText?.addEventListener('click', () => addText());
    btnAddRect?.addEventListener('click', addRect);
    btnAddCircle?.addEventListener('click', addCircle);

    btnUndo?.addEventListener('click', undo);
    btnRedo?.addEventListener('click', redo);

    btnToggleShapesDrawer?.addEventListener('click', () => {
        if (!shapesDrawer) return;
        const isOpen = !shapesDrawer.classList.contains('hidden');
        shapesDrawer.classList.toggle('hidden', isOpen);
        if (shapesDrawerChevron) shapesDrawerChevron.textContent = isOpen ? 'v' : '^';
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
        const resetColor = getBrandingFromControls().colors.background;
        if (bgColorPicker) bgColorPicker.value = resetColor;
        setBgColor(resetColor);
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

    // sticky grid (snap)
    loadStickyGridState();
    syncStickyGridUI();

    toggleSnapGrid?.addEventListener('change', (e) => {
        snapGridEnabled = !!e.target.checked;
        persistStickyGridState();
    });

    gridStepSelect?.addEventListener('change', (e) => {
        gridStep = Number(e.target.value) || 40;
        persistStickyGridState();
        if (toggleGrid?.checked) drawGrid();
    });

    snapThresholdRange?.addEventListener('input', (e) => {
        snapThreshold = Number(e.target.value) || 0;
        if (snapThresholdValue) snapThresholdValue.textContent = String(snapThreshold);
        persistStickyGridState();
    });

    // center / delete
    btnCenterSelection?.addEventListener('click', () => {
        alignSelection('centerX');
        alignSelection('centerY');
    });

    btnDeleteSelection?.addEventListener('click', deleteSelection);
    btnAlignCanvasLeft?.addEventListener('click', () => alignSelection('left'));
    btnAlignCanvasCenterX?.addEventListener('click', () => alignSelection('centerX'));
    btnAlignCanvasRight?.addEventListener('click', () => alignSelection('right'));
    btnAlignCanvasTop?.addEventListener('click', () => alignSelection('top'));
    btnAlignCanvasCenterY?.addEventListener('click', () => alignSelection('centerY'));
    btnAlignCanvasBottom?.addEventListener('click', () => alignSelection('bottom'));
    // Templates (DB)
    document.querySelectorAll('.js-template-db-btn').forEach(btn => {
        btn.addEventListener('click', () => applyDbTemplate(btn.getAttribute('data-template-id')));
    });

    // Templates (old config, optional)
    document.querySelectorAll('.js-template-btn').forEach(btn => {
        btn.addEventListener('click', () => applyTemplate(btn.getAttribute('data-template')));
    });

    btnAutofillTemplate?.addEventListener('click', () => {
        const hint = selectedNode?.getAttr?.('amName') || '';
        autoFillTemplateFromContext(hint);
        applyBrandingToCanvas({ skipHistory: false });
    });

    brandPresetSelect?.addEventListener('change', () => {
        if (brandPresetSelect.value !== 'manual') {
            applyPresetToControls(brandPresetSelect.value);
        }
        applyBrandingToCanvas({ skipHistory: false });
    });

    [brandHeadingFont, brandBodyFont, brandColorPrimary, brandColorSecondary, brandColorAccent, brandColorBackground, brandColorText]
        .forEach((input) => {
            input?.addEventListener('input', () => {
                if (brandPresetSelect && brandPresetSelect.value !== 'manual') {
                    brandPresetSelect.value = 'manual';
                }
                applyBrandingToCanvas({ skipHistory: false });
            });
        });

    btnApplyBranding?.addEventListener('click', () => {
        applyBrandingToCanvas({ skipHistory: false });
        setBrandingStatus('Branding applique au canvas.');
    });

    btnSaveBranding?.addEventListener('click', () => {
        saveBrandingSettings();
    });

    btnResetBranding?.addEventListener('click', () => {
        const defaults = getDefaultBranding();
        hydrateBrandingControls(defaults);
        applyBrandingToCanvas({ skipHistory: false });
        setBrandingStatus('Retour au preset par defaut.');
    });

    // image upload
    imageUpload?.addEventListener('change', async (e) => {
        const file = e.target.files?.[0];
        await handleImageFile(file);
        e.target.value = '';
    });

    // inspector events
    inputLayerName?.addEventListener('input', (e) => {
        if (!selectedNode) return;
        selectedNode.setAttr('amName', sanitizeLayerName(e.target.value));
        refreshLayersList();
    });
    inputLayerName?.addEventListener('change', () => saveHistory());

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

    textFontFamily?.addEventListener('change', (e) => {
        if (!selectedNode || selectedNode.getAttr('amType') !== 'text') return;
        selectedNode.fontFamily(fontKeyToFamily(e.target.value, activeBranding?.fonts?.body));
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

    imgBrightness?.addEventListener('input', (e) => {
        if (!selectedNode || selectedNode.getAttr('amType') !== 'image') return;
        selectedNode.setAttr('amBrightness', Number(e.target.value || 0));
        applyImageAdjustments(selectedNode, false);
    });
    imgBrightness?.addEventListener('change', () => {
        if (!selectedNode || selectedNode.getAttr('amType') !== 'image') return;
        saveHistory();
    });

    imgContrast?.addEventListener('input', (e) => {
        if (!selectedNode || selectedNode.getAttr('amType') !== 'image') return;
        selectedNode.setAttr('amContrast', Number(e.target.value || 0));
        applyImageAdjustments(selectedNode, false);
    });
    imgContrast?.addEventListener('change', () => {
        if (!selectedNode || selectedNode.getAttr('amType') !== 'image') return;
        saveHistory();
    });

    imgFitMode?.addEventListener('change', (e) => {
        if (!selectedNode || selectedNode.getAttr('amType') !== 'image') return;
        fitImageNode(selectedNode, e.target.value, true);
        updateInspector();
    });

    btnReplaceImage?.addEventListener('click', () => {
        if (!selectedNode || selectedNode.getAttr('amType') !== 'image') return;
        imageReplaceUpload?.click();
    });

    imageReplaceUpload?.addEventListener('change', async (e) => {
        const file = e.target.files?.[0];
        await handleImageFile(file, { replaceSelected: true });
        e.target.value = '';
    });

    // z-order + duplicate
    btnZTop?.addEventListener('click', () => { if (!selectedNode || isNodeLocked(selectedNode)) return; selectedNode.moveToTop(); transformer.moveToTop(); mainLayer.draw(); saveHistory(); refreshLayersList(); });
    btnZUp?.addEventListener('click', () => { if (!selectedNode || isNodeLocked(selectedNode)) return; selectedNode.moveUp(); transformer.moveToTop(); mainLayer.draw(); saveHistory(); refreshLayersList(); });
    btnZDown?.addEventListener('click', () => { if (!selectedNode || isNodeLocked(selectedNode)) return; selectedNode.moveDown(); transformer.moveToTop(); mainLayer.draw(); saveHistory(); refreshLayersList(); });
    btnZBottom?.addEventListener('click', () => { if (!selectedNode || isNodeLocked(selectedNode)) return; selectedNode.moveToBottom(); transformer.moveToTop(); mainLayer.draw(); saveHistory(); refreshLayersList(); });

    btnDuplicate?.addEventListener('click', duplicateSelection);

    btnToggleLock?.addEventListener('click', () => {
        if (!selectedNode) return;
        selectedNode.setAttr('amLocked', !isNodeLocked(selectedNode));
        normalizeNodeState(selectedNode);
        setSelection(selectedNode);
        mainLayer.draw();
        saveHistory();
        updateInspector();
    });

    btnToggleVisibility?.addEventListener('click', () => {
        if (!selectedNode) return;
        selectedNode.visible(!isNodeVisible(selectedNode));
        normalizeNodeState(selectedNode);
        mainLayer.draw();
        saveHistory();
        if (!isNodeVisible(selectedNode)) clearSelection();
        else refreshLayersList();
        updateInspector();
    });
    // ----------------------------
    // Initial
    // ----------------------------
    initUiMode();
    hydrateBrandingControls(activeBranding);
    setBrandingStatus('Style charge. Selectionnez un template pour generation rapide.');
    renderFormatCards();
    openFormatModal();
    refreshTemplateButtons();
    refreshTemplateButtonsDb();
    mountAdminSaveButton();
    bindCanvasDragDrop();
    bindKeyboardShortcuts();
    bindTemplateSearch();
    updateHistoryButtons();

    // If admin is editing an existing DB template, preload it
    if (ADMIN_MODE && ADMIN_EDITING_TEMPLATE?.konva_json) {
        const fmt = FORMATS.find(f => f.id === ADMIN_EDITING_TEMPLATE.format_id);
        if (fmt) {
            initStageForFormat(fmt);
            closeFormatModal();
            const json = (typeof ADMIN_EDITING_TEMPLATE.konva_json === 'string')
                ? ADMIN_EDITING_TEMPLATE.konva_json
                : JSON.stringify(ADMIN_EDITING_TEMPLATE.konva_json);
            restoreFromJson(json);
            applyBrandingToCanvas({ skipHistory: true });
            history = [stage.toJSON()];
            redoStack = [];
            updateHistoryButtons();
        }
    }
});
</script>




