{{-- resources/views/tools/konva/partials/canvas.blade.php --}}
<section class="canvas-shell relative flex items-center justify-center">
    <div class="absolute left-3 top-3 canvas-status">
        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
        Mode maquette - non sauvegarde
    </div>

    <div class="w-full flex justify-center">
        <div
            id="konva-wrapper"
            class="relative overflow-hidden rounded-3xl border border-dashed border-slate-200 bg-slate-50"
            style="width:min(860px, 100%);"
        >
            <div id="konva-container"></div>
        </div>
    </div>
</section>
