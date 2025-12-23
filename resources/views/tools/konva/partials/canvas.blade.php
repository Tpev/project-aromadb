{{-- resources/views/tools/konva/partials/canvas.blade.php --}}
<section class="glass-card relative flex items-center justify-center rounded-3xl border border-slate-100 p-3 shadow-lg">
    <div class="absolute left-3 top-3 flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-[10px] font-medium text-slate-500 shadow-sm">
        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
        Mode maquette – non sauvegardé
    </div>

    <div class="w-full flex justify-center">
        <div
            id="konva-wrapper"
            class="relative overflow-hidden rounded-3xl border border-dashed border-slate-200 bg-slate-50"
            style="width:min(720px, 100%);"
        >
            <div id="konva-container"></div>
        </div>
    </div>
</section>
