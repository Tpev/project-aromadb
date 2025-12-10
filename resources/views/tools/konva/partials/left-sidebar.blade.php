{{-- resources/views/tools/konva/partials/left-sidebar.blade.php --}}
<aside class="space-y-4">
    {{-- Section: Contenu --}}
    <div class="toolbar-card glass-card">
        <div class="mb-2 flex items-center justify-between gap-2">
            <span class="toolbar-title">Contenu</span>
            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                1080 × 1080
            </span>
        </div>

        <div class="flex flex-wrap gap-2 mb-2">
            {{-- Hidden file input --}}
            <input id="imageUpload" type="file" accept="image/*" class="hidden">

            <label for="imageUpload" class="pill-btn pill-btn-main cursor-pointer">
                <span class="pill-icon">🖼️</span>
                Importer une image
            </label>

            <button id="btnAddText" type="button" class="pill-btn pill-btn-ghost">
                <span class="pill-icon">✏️</span>
                Ajouter du texte
            </button>
        </div>

        <div class="flex flex-wrap gap-2">
            <button id="btnAddRect" type="button" class="pill-btn pill-btn-ghost">
                ◼️ Forme
            </button>
            <button id="btnAddCircle" type="button" class="pill-btn pill-btn-ghost">
                ⚪ Cercle
            </button>
        </div>
    </div>

    {{-- Section: Mise en page globale --}}
    <div class="toolbar-card glass-card space-y-3">
        <div class="flex items-center justify-between gap-2 mb-1">
            <span class="toolbar-title">Mise en page</span>
            <button id="btnCenterSelection" type="button"
                    class="pill-btn pill-btn-ghost px-2 py-1 text-[11px]">
                Aligner au centre
            </button>
        </div>

        <div class="space-y-2 text-[11px] text-slate-600">
            <div class="flex items-center justify-between gap-2">
                <span>Zoom</span>
                <span id="zoomValue" class="font-medium text-slate-800">100%</span>
            </div>
            <input id="zoomSlider"
                   type="range"
                   min="50"
                   max="200"
                   value="100"
                   class="w-full accent-[#647a0b]">
        </div>

        <div class="space-y-1 text-[11px] text-slate-600">
            <div class="flex items-center justify-between gap-2">
                <span>Couleur de fond</span>
                <div class="flex items-center gap-2">
                    <input id="bgColorPicker"
                           type="color"
                           value="#f9fafb"
                           class="h-7 w-12 cursor-pointer rounded-md border border-slate-200 bg-white p-0">
                    <button id="btnResetBg" type="button"
                            class="pill-btn-ghost pill-btn px-2 py-1 text-[10px]">
                        Réinitialiser
                    </button>
                </div>
            </div>

            {{-- Presets --}}
            <div class="flex flex-wrap gap-1.5">
                <button type="button" data-bg="#f9fafb"
                        class="h-5 w-5 rounded-full border border-slate-200"
                        style="background:#f9fafb;"></button>
                <button type="button" data-bg="#fefce8"
                        class="h-5 w-5 rounded-full border border-slate-200"
                        style="background:#fefce8;"></button>
                <button type="button" data-bg="#ecfccb"
                        class="h-5 w-5 rounded-full border border-slate-200"
                        style="background:#ecfccb;"></button>
                <button type="button" data-bg="#e0f2fe"
                        class="h-5 w-5 rounded-full border border-slate-200"
                        style="background:#e0f2fe;"></button>
                <button type="button" data-bg="#fef2f2"
                        class="h-5 w-5 rounded-full border border-slate-200"
                        style="background:#fef2f2;"></button>
            </div>
        </div>

        <div class="flex items-center justify-between gap-2 text-[11px] text-slate-600">
            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input id="toggleGrid" type="checkbox"
                       class="h-3.5 w-3.5 rounded border-slate-300 text-[#647a0b] focus:ring-[#647a0b]">
                <span>Afficher la grille</span>
            </label>

            <button id="btnDeleteSelection" type="button"
                    class="pill-btn-ghost pill-btn px-2 py-1 text-[10px]">
                Supprimer l’élément
            </button>
        </div>
    </div>

    {{-- Section: Événements (précharger un atelier) --}}
    @php
        $eventsForSelect = isset($events) ? $events : collect();
    @endphp

    @if($eventsForSelect->count())
        <div class="toolbar-card glass-card space-y-2">
            <div class="flex items-center justify-between gap-2 mb-1">
                <span class="toolbar-title">Événements</span>
                <span class="badge-soft">
                    📅 Atelier
                </span>
            </div>

            <label for="eventSelector" class="small-label">
                Précharger les infos d’un atelier
            </label>

            <select id="eventSelector" class="small-select">
                <option value="">— Choisir un événement —</option>
                @foreach($eventsForSelect as $event)
                    @php
                        // Essaie plusieurs noms de colonnes possibles
                        $rawStartsAt = $event->start_at
                            ?? $event->start_date_time
                            ?? $event->start_date
                            ?? null;

                        $dateLabel = '';

                        if ($rawStartsAt instanceof \Carbon\Carbon) {
                            $dateLabel = $rawStartsAt->format('d/m/Y H:i');
                        } elseif (!empty($rawStartsAt)) {
                            try {
                                $dateLabel = \Carbon\Carbon::parse($rawStartsAt)->format('d/m/Y H:i');
                            } catch (\Exception $e) {
                                // En dernier recours on affiche brut
                                $dateLabel = (string) $rawStartsAt;
                            }
                        }

                        $location = trim($event->location ?? '');
                        $title    = trim($event->title ?? 'Événement');

                        $labelParts = array_filter([$title, $dateLabel, $location]);
                        $label      = implode(' • ', $labelParts);
                    @endphp
                    <option
                        value="{{ $event->id }}"
                        data-title="{{ e($title) }}"
                        data-date="{{ e($dateLabel) }}"
                        data-location="{{ e($location) }}"
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <p class="mt-1 text-[11px] leading-snug text-slate-500">
                Sélectionnez un atelier pour pré-remplir le template “📅 Atelier”
                (titre, date, lieu…).
            </p>
        </div>
    @endif

    {{-- Section: Templates prêts à l’emploi --}}
    <div class="toolbar-card glass-card">
        <div class="mb-2 flex items-center justify-between gap-2">
            <span class="toolbar-title">Templates</span>
            <span class="badge-soft">
                🎨 Rapide
            </span>
        </div>

        <div class="grid grid-cols-2 gap-1.5 text-[11px]">
            @foreach($konvaTemplates ?? [] as $tpl)
                <button
                    type="button"
                    class="pill-btn pill-btn-ghost w-full justify-center js-template-btn"
                    data-template="{{ $tpl['id'] }}"
                    title="{{ $tpl['hint'] ?? '' }}"
                >
                    {{ $tpl['label'] }}
                </button>
            @endforeach
        </div>
    </div>


    {{-- Section: Historique / Infos --}}
    <div class="toolbar-card glass-card">
        <div class="mb-1 flex items-center justify-between">
            <span class="toolbar-title">Historique</span>
            <button id="btnUndo"
                    type="button"
                    class="pill-btn pill-btn-ghost px-2 py-1 text-[10px]">
                ⤺ Annuler
            </button>
        </div>
        <p class="text-[11px] leading-snug text-slate-500">
            Espace de test : expérimentez en toute liberté,
            rien n’est enregistré dans AromaMade.
        </p>
    </div>
</aside>
