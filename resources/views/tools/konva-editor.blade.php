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

    {{-- Your main Konva editor logic (all templates etc.) --}}
    @vite('resources/js/konva-editor.js')
</x-app-layout>
