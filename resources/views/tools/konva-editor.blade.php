{{-- resources/views/tools/konva-editor.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl" style="color:#647a0b;">
                    Studio Social Media (beta)
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Choisissez un format, appliquez un template, puis exportez en PNG.
                </p>
            </div>
        </div>
    </x-slot>

    @include('tools.konva.partials.styles')

    <div class="editor-shell">
        <div class="mx-auto max-w-[1600px] px-4 py-6 lg:px-6">
            <div class="editor-toolbar mb-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="editor-meta-label">AromaMade - Outil creatif</p>
                        <h1 class="mt-1 text-2xl font-semibold text-slate-900 md:text-3xl">
                            Studio visuel
                        </h1>
                        <p class="mt-1 text-sm">
                            Creez un visuel pro en quelques minutes avec format, template et export HD.
                        </p>
                        <div class="editor-help-pills">
                            <span class="editor-help-pill">1. Choisir le format</span>
                            <span class="editor-help-pill">2. Appliquer un template</span>
                            <span class="editor-help-pill">3. Personnaliser puis exporter</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button id="btnChooseFormat" type="button" class="pill-btn pill-btn-ghost">
                            Changer de format
                        </button>

                        <button id="btnExport" type="button" class="pill-btn pill-btn-main">
                            <span class="pill-icon">PNG</span>
                            Exporter
                        </button>

                        <button id="btnClearCanvas" type="button" class="pill-btn pill-btn-ghost">
                            Reinitialiser
                        </button>
                    </div>
                </div>
            </div>

            <div class="editor-main-grid">
                @include('tools.konva.partials.left-sidebar', ['events' => $events ?? collect()])
                @include('tools.konva.partials.canvas')
                @include('tools.konva.partials.right-sidebar')
            </div>
        </div>
    </div>

    <div id="formatModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-slate-900/50"></div>

        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="glass-card w-full max-w-2xl rounded-3xl border border-slate-200 p-5 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Etape 1</div>
                        <h3 class="mt-1 text-lg font-semibold text-slate-900">Choisir un format</h3>
                        <p class="mt-1 text-sm text-slate-600">
                            Le canvas et les templates s'adaptent au format choisi.
                        </p>
                    </div>
                    <button id="btnCloseFormatModal" type="button" class="pill-btn pill-btn-ghost">
                        Fermer
                    </button>
                </div>

                <div id="formatsGrid" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    {{-- JS inject --}}
                </div>

                <div class="mt-4 text-[11px] text-slate-500">
                    Astuce : changer de format reinitialise le canvas.
                </div>
            </div>
        </div>
    </div>

    @include('tools.konva.partials.scripts')
</x-app-layout>
