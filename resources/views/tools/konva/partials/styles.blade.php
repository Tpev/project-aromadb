{{-- resources/views/tools/konva/partials/styles.blade.php --}}
<style>
    :root {
        --brand: #6d7f16;
        --brand-dark: #4f5f0f;
        --ink: #0f172a;
        --soft-ink: #334155;
        --panel-border: #e2e8f0;
        --panel-bg: rgba(255, 255, 255, 0.96);
    }

    .editor-shell {
        min-height: calc(100vh - 4.5rem);
        background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        background-image:
            radial-gradient(circle at 10% 5%, rgba(109, 127, 22, 0.12), transparent 45%),
            radial-gradient(circle at 90% 95%, rgba(15, 23, 42, 0.1), transparent 48%);
    }

    .editor-toolbar {
        border: 1px solid var(--panel-border);
        border-radius: 1.25rem;
        background: linear-gradient(140deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
        padding: 1rem 1.1rem;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.07);
    }

    .editor-meta-label {
        font-size: 0.66rem;
        line-height: 1;
        text-transform: uppercase;
        letter-spacing: 0.2em;
        color: #64748b;
        font-weight: 700;
    }

    .editor-toolbar p {
        color: #475569;
    }

    .editor-help-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.65rem;
    }

    .editor-help-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.28rem 0.62rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: #334155;
        background: #f1f5f9;
        border: 1px solid #dbe6f1;
    }

    .glass-card {
        backdrop-filter: blur(8px);
        background: var(--panel-bg);
    }

    .toolbar-card {
        border-radius: 1rem;
        border: 1px solid var(--panel-border);
        padding: 0.9rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
    }

    .toolbar-title {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        color: #64748b;
    }

    .badge-soft {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.18rem 0.55rem;
        font-size: 0.62rem;
        font-weight: 700;
        background: #edf9ef;
        color: #2f6d2d;
        border: 1px solid #d3eeda;
    }

    .pill-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.38rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        padding: 0.45rem 0.86rem;
        transition: all 0.15s ease;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .pill-btn-main {
        background: linear-gradient(120deg, var(--brand), #8ca52a);
        color: #f8fafc;
        box-shadow: 0 12px 24px rgba(109, 127, 22, 0.35);
    }

    .pill-btn-main:hover {
        filter: brightness(1.06);
        transform: translateY(-1px);
    }

    .pill-btn-ghost {
        background: #f8fafc;
        color: var(--ink);
        border-color: #dbe2eb;
    }

    .pill-btn-ghost:hover {
        background: #eef2f7;
        border-color: #c7d2de;
    }

    .pill-icon {
        font-size: 12px;
    }

    .editor-main-grid {
        display: grid;
        grid-template-columns: minmax(0, 320px) minmax(0, 1fr) minmax(0, 300px);
        gap: 1.1rem;
        align-items: flex-start;
    }

    @media (max-width: 1280px) {
        .editor-main-grid {
            grid-template-columns: minmax(0, 300px) minmax(0, 1fr) minmax(0, 280px);
        }
    }

    @media (max-width: 1080px) {
        .editor-main-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    .editor-column {
        position: sticky;
        top: 0.9rem;
    }

    @media (max-width: 1080px) {
        .editor-column {
            position: static;
        }
    }

    .sidebar-stack {
        max-height: calc(100vh - 7.5rem);
        overflow-y: auto;
        padding-right: 0.2rem;
    }

    @media (max-width: 1080px) {
        .sidebar-stack {
            max-height: none;
            overflow: visible;
        }
    }

    .sidebar-stack::-webkit-scrollbar {
        width: 9px;
    }

    .sidebar-stack::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #cbd5e1;
        border: 2px solid transparent;
        background-clip: content-box;
    }

    .quick-start-steps {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.35rem;
        margin-top: 0.5rem;
    }

    .quick-step {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
        font-size: 0.72rem;
        color: #334155;
        border-radius: 0.65rem;
        padding: 0.42rem 0.5rem;
        background: #f8fafc;
        border: 1px solid #e3e9f0;
    }

    .step-index {
        flex: 0 0 auto;
        width: 1.1rem;
        height: 1.1rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.66rem;
        font-weight: 800;
        color: #1e293b;
        background: #dce7f4;
    }

    .small-label {
        font-size: 0.69rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 0.14rem;
    }

    .small-input,
    .small-select,
    .template-search {
        width: 100%;
        border-radius: 0.58rem;
        border: 1px solid #dce3ec;
        padding: 0.38rem 0.52rem;
        font-size: 0.75rem;
        color: #0f172a;
        background: #fff;
    }

    .small-input:focus,
    .small-select:focus,
    .template-search:focus {
        outline: none;
        border-color: var(--brand);
        box-shadow: 0 0 0 2px rgba(109, 127, 22, 0.14);
    }

    .range-row {
        display: flex;
        align-items: center;
        gap: 0.48rem;
    }

    .range-row input[type="range"] {
        flex: 1;
    }

    .range-value {
        font-size: 0.69rem;
        color: #4b5563;
        min-width: 2.2rem;
        text-align: right;
    }

    .shape-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 2.1rem;
        border-radius: 0.72rem;
        border: 1px solid #dce3ec;
        background: #f8fafc;
        color: #334155;
        font-size: 0.72rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .shape-btn:hover {
        background: #eef2f7;
        border-color: #becbda;
        transform: translateY(-1px);
    }

    #konva-container {
        cursor: default;
    }

    #konva-wrapper.is-drop-target {
        border-color: rgba(109, 127, 22, 0.85);
        box-shadow: inset 0 0 0 2px rgba(109, 127, 22, 0.2);
        background: #f7fce9;
    }

    .canvas-shell {
        border: 1px solid #dde5ef;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.95));
        border-radius: 1.4rem;
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.11);
        padding: 0.95rem;
    }

    .canvas-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid #dbe6f1;
        padding: 0.33rem 0.68rem;
        font-size: 0.68rem;
        font-weight: 700;
        color: #475569;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    }

    .layer-list {
        max-height: 320px;
        overflow-y: auto;
        padding-right: 0.22rem;
    }

    .layer-row {
        display: flex;
        align-items: center;
        gap: 0.34rem;
    }

    .layer-item-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        border-radius: 0.72rem;
        padding: 0.38rem 0.5rem;
        font-size: 0.74rem;
        border: 1px solid transparent;
        background: transparent;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .layer-item-btn:hover {
        background: #f4f7fb;
        border-color: #dce5ef;
    }

    .layer-item-btn.is-active {
        background: rgba(109, 127, 22, 0.12);
        border-color: rgba(109, 127, 22, 0.45);
    }

    .layer-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.14rem 0.46rem;
        font-size: 0.61rem;
        font-weight: 700;
        background: #edf2f7;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .layer-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
    }

    .layer-icon-btn {
        border: 1px solid #dbe2eb;
        border-radius: 999px;
        background: #fff;
        color: #475569;
        font-size: 0.63rem;
        line-height: 1;
        padding: 0.2rem 0.42rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .layer-icon-btn:hover {
        border-color: #a6b6c9;
        background: #f8fafc;
    }

    .layer-icon-btn.is-active {
        background: rgba(109, 127, 22, 0.12);
        border-color: rgba(109, 127, 22, 0.45);
        color: #43620f;
    }

    .z-order-btn {
        padding: 0.28rem 0.52rem;
        border-radius: 999px;
        border: 1px solid #dce3ec;
        font-size: 0.7rem;
        font-weight: 600;
        background: #f8fafc;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s;
    }

    .z-order-btn:hover {
        background: #edf2f8;
        border-color: #bcc8d8;
    }

    .template-card {
        border-radius: 0.72rem;
        border: 1px solid #dce3ec;
        background: #fff;
        padding: 0.55rem 0.62rem;
        text-align: left;
        transition: all 0.16s ease;
    }

    .template-card:hover {
        border-color: #bed06e;
        box-shadow: 0 8px 18px rgba(109, 127, 22, 0.13);
        transform: translateY(-1px);
    }

    .template-card .template-title {
        font-size: 0.74rem;
        font-weight: 700;
        color: var(--ink);
        line-height: 1.2;
    }

    .template-card .template-hint {
        margin-top: 0.18rem;
        font-size: 0.67rem;
        color: #64748b;
        line-height: 1.3;
    }

    .template-card.opacity-40 {
        opacity: 0.4;
    }

    .workflow-card {
        border: 1px solid #cfe0ee;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .mode-chip {
        border: 1px solid #d9e2ec;
        background: #f8fafc;
        color: #334155;
        border-radius: 999px;
        padding: 0.3rem 0.72rem;
        font-size: 0.72rem;
        font-weight: 700;
        transition: all 0.15s ease;
    }

    .mode-chip:hover {
        background: #eef2f7;
    }

    .mode-chip.is-active {
        background: linear-gradient(120deg, var(--brand), #8ca52a);
        color: #f8fafc;
        border-color: transparent;
        box-shadow: 0 8px 20px rgba(109, 127, 22, 0.28);
    }

    .workflow-steps {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.38rem;
    }

    .workflow-step-btn {
        border: 1px solid #dbe4ed;
        background: #f8fafc;
        color: #334155;
        border-radius: 0.72rem;
        padding: 0.44rem 0.56rem;
        text-align: left;
        font-size: 0.74rem;
        font-weight: 700;
        transition: all 0.15s ease;
    }

    .workflow-step-btn:hover {
        border-color: rgba(109, 127, 22, 0.5);
        background: #f2f8e7;
    }

    .editor-shell.is-quick-mode .expert-only {
        display: none !important;
    }

    .editor-shell.is-quick-mode .editor-right {
        opacity: 0.98;
    }

    .editor-shell.is-expert-mode .expert-only {
        display: block;
    }

    details > summary::-webkit-details-marker {
        display: none;
    }

    details > summary {
        list-style: none;
    }

    .pill-btn:disabled,
    .z-order-btn:disabled {
        opacity: 0.48;
        cursor: not-allowed;
        transform: none;
        filter: none;
    }
</style>
