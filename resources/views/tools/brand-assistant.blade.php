{{-- resources/views/tools/brand-assistant.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl" style="color:#647a0b;">
                    Assistant charte graphique (beta)
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Aidez-vous à définir vos couleurs, votre style et un mini guide de marque pour vos posts, flyers et site.
                </p>
            </div>
        </div>
    </x-slot>

    <style>
        :root {
            --brand: #647a0b;
        }

        .brand-shell {
            min-height: calc(100vh - 4.5rem);
            background: #f3f4f6;
            padding: 1.5rem 0;
        }

        .brand-card {
            background: white;
            border-radius: 1.25rem;
            box-shadow:
                0 18px 45px rgba(15, 23, 42, 0.08),
                0 0 0 1px rgba(148, 163, 184, 0.18);
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            padding: 0.15rem 0.55rem;
            font-size: 0.7rem;
            background: rgba(100, 122, 11, 0.06);
            color: #4b5563;
            border: 1px solid rgba(100, 122, 11, 0.14);
        }

        .color-pill {
            border-radius: 999px;
            width: 2.25rem;
            height: 2.25rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
        }

        .scrollbar-thin::-webkit-scrollbar {
            height: 6px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.8);
            border-radius: 999px;
        }

        .preview-card {
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.4);
            background: #f9fafb;
        }

        .preview-gradient {
            border-radius: 1rem;
        }

        .cta-button-preview {
            border-radius: 999px;
            padding: 0.5rem 1.4rem;
            font-size: 0.8rem;
            font-weight: 600;
            border: none;
        }

        .step-circle {
            width: 1.55rem;
            height: 1.55rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .input-label {
            font-size: 0.78rem;
            font-weight: 500;
            color: #4b5563;
        }

        .input-field {
            border-radius: 0.9rem;
            border: 1px solid #e5e7eb;
            padding: 0.55rem 0.8rem;
            font-size: 0.82rem;
            width: 100%;
            background: white;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 1px var(--brand), 0 0 0 4px rgba(100, 122, 11, 0.16);
        }

        .checkbox-chip {
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            padding: 0.4rem 0.85rem;
            font-size: 0.78rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .checkbox-chip input {
            display: none;
        }

        .checkbox-chip-dot {
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 999px;
            border: 1px solid #cbd5f5;
            background: white;
        }

        .checkbox-chip.active {
            background: rgba(100, 122, 11, 0.08);
            border-color: rgba(100, 122, 11, 0.7);
            color: #374151;
        }

        .checkbox-chip.active .checkbox-chip-dot {
            border-color: transparent;
            background: radial-gradient(circle at center, #647a0b 40%, #d9f99d 100%);
        }

        .palette-card {
            border-radius: 1.1rem;
            border: 1px solid rgba(148, 163, 184, 0.55);
            padding: 0.85rem;
            background: white;
            cursor: pointer;
            transition: all 0.18s ease;
        }

        .palette-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        }

        .palette-card.selected {
            border-color: var(--brand);
            box-shadow: 0 0 0 1px rgba(100, 122, 11, 0.7), 0 12px 30px rgba(100, 122, 11, 0.18);
        }

        .font-chip {
            border-radius: 999px;
            padding: 0.35rem 0.8rem;
            border: 1px dashed rgba(148, 163, 184, 0.9);
            font-size: 0.75rem;
        }

        .mini-title {
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #6b7280;
        }

        @media (min-width: 1024px) {
            .brand-grid {
                display: grid;
                grid-template-columns: minmax(0, 1.25fr) minmax(0, 1.1fr);
                gap: 1.75rem;
            }
        }

        /* Square social media preview */
        .square-post-shell {
            display: flex;
            justify-content: center;
        }

        .square-post {
            width: 100%;
            max-width: 280px;
            aspect-ratio: 1 / 1;
            border-radius: 1.2rem;
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, 0.1);
            background: #fff;
            display: flex;
            flex-direction: column;
            font-size: 11px;
        }

        .square-post-header,
        .square-post-footer {
            padding: 0.4rem 0.55rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .square-post-header-left {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .square-post-avatar {
            width: 22px;
            height: 22px;
            border-radius: 999px;
            background: #e5e7eb;
            flex-shrink: 0;
        }

        .square-post-username {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .square-post-body {
            flex: 1;
            padding: 0.55rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .square-post-card {
            width: 100%;
            height: 100%;
            border-radius: 0.9rem;
            padding: 0.7rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
        }

        .square-post-footer-left {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .square-post-dot {
            width: 3px;
            height: 3px;
            border-radius: 999px;
            background: #9ca3af;
        }
    </style>

    <div class="brand-shell" x-data="brandAssistant()">
        <div class="max-w-6xl mx-auto px-4 md:px-6">
            <div class="brand-card p-4 md:p-6 lg:p-7">
                {{-- INTRO + STEPS (full width) --}}
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
                    <div class="space-y-1">
                        <div class="badge-soft">
                            <span class="w-1.5 h-1.5 rounded-full" style="background: var(--brand);"></span>
                            Guidé, sans rien enregistrer
                        </div>
                        <h1 class="text-lg md:text-xl font-semibold text-slate-800">
                            Créez votre mini charte graphique en quelques clics
                        </h1>
                        <p class="text-xs md:text-sm text-slate-500">
                            Répondez à quelques questions, choisissez une palette, visualisez un post carré de réseau social,
                            un flyer et un bouton, puis récupérez un mini guide à copier-coller.
                        </p>
                    </div>

                    <div class="flex items-center gap-4 text-xs text-slate-500">
                        <div class="flex items-center gap-2">
                            <div class="step-circle"
                                 :style="step >= 1 ? 'background: var(--brand); color: white;' : 'border: 1px solid #cbd5e1; color:#6b7280;'">
                                1
                            </div>
                            <span>Identité</span>
                        </div>
                        <div class="h-px w-8 bg-slate-300 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <div class="step-circle"
                                 :style="step >= 2 ? 'background: var(--brand); color: white;' : 'border: 1px solid #cbd5e1; color:#6b7280;'">
                                2
                            </div>
                            <span>Palettes</span>
                        </div>
                        <div class="h-px w-8 bg-slate-300 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <div class="step-circle"
                                 :style="step >= 3 ? 'background: var(--brand); color: white;' : 'border: 1px solid #cbd5e1; color:#6b7280;'">
                                3
                            </div>
                            <span>Aperçu & mini guide</span>
                        </div>
                    </div>
                </div>

                {{-- MAIN GRID: LEFT (form+palettes) / RIGHT (preview+guide) --}}
                <div class="brand-grid gap-6">
                    {{-- LEFT: Form + palettes --}}
                    <div class="space-y-6">
                        {{-- Étape 1 : identité --}}
                        <section class="preview-card p-4 md:p-5 bg-white border border-slate-200">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="mini-title">Étape 1</p>
                                    <h2 class="text-sm font-semibold text-slate-800">
                                        Comprendre votre style de thérapeute
                                    </h2>
                                </div>
                                <span class="text-[10px] text-slate-400 hidden md:inline">Rien n’est enregistré</span>
                            </div>

                            <div class="space-y-4">
                                {{-- Ton général --}}
                                <div>
                                    <label class="input-label block mb-1.5">Ton général</label>
                                    <select class="input-field text-xs"
                                            x-model="identity.tone">
                                        <option value="">Choisissez une option…</option>
                                        <option value="calme_doux">Calme &amp; doux</option>
                                        <option value="nature_bio">Naturel &amp; orienté plantes</option>
                                        <option value="premium_minimaliste">Premium &amp; minimaliste</option>
                                        <option value="dynamique_energie">Dynamique &amp; énergisant</option>
                                    </select>
                                </div>

                                {{-- Clientèle --}}
                                <div>
                                    <label class="input-label block mb-1.5">Clientèle principale</label>
                                    <select class="input-field text-xs"
                                            x-model="identity.audience">
                                        <option value="">Choisissez une option…</option>
                                        <option value="femmes_25_45">Femmes 25–45 (stress, charge mentale…)</option>
                                        <option value="seniors">Seniors</option>
                                        <option value="sportifs">Sportifs / performance</option>
                                        <option value="familles_parents">Parents &amp; familles</option>
                                        <option value="mixte_generaliste">Mixte / généraliste</option>
                                    </select>
                                </div>

                                {{-- Valeurs --}}
                                <div>
                                    <label class="input-label block mb-1.5">Valeurs importantes</label>
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="value in availableValues" :key="value.id">
                                            <label class="checkbox-chip"
                                                   :class="identity.values.includes(value.id) ? 'active' : ''"
                                                   @click.prevent="toggleValue(value.id)">
                                                <span class="checkbox-chip-dot"></span>
                                                <span x-text="value.label"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                                {{-- CTA generate --}}
                                <div class="flex items-center justify-between pt-2 border-t border-dashed border-slate-200 mt-2">
                                    <p class="text-[11px] text-slate-500 pr-2">
                                        Cliquez sur “Générer mes palettes” pour voir plusieurs propositions cohérentes
                                        avec les réponses ci-dessus.
                                    </p>
                                    <button type="button"
                                            @click="generatePalettes()"
                                            class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold text-white"
                                            style="background:var(--brand); box-shadow:0 12px 25px rgba(100,122,11,0.35);">
                                        <span>Générer mes palettes</span>
                                        <span class="text-[10px] opacity-75">⏎</span>
                                    </button>
                                </div>
                            </div>
                        </section>

                        {{-- Étape 2 : palettes proposées --}}
                        <section class="preview-card p-4 md:p-5 bg-slate-50 border border-slate-200">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="mini-title">Étape 2</p>
                                    <h2 class="text-sm font-semibold text-slate-800">
                                        Choisissez une palette qui vous ressemble
                                    </h2>
                                </div>
                                <span x-show="palettes.length"
                                      class="text-[11px] text-slate-500">
                                    Cliquez sur une palette pour la sélectionner
                                </span>
                            </div>

                            <template x-if="!palettes.length">
                                <p class="text-xs text-slate-500">
                                    Une fois vos réponses remplies, cliquez sur “Générer mes palettes” pour voir plusieurs
                                    propositions de couleurs adaptées à votre style de pratique.
                                </p>
                            </template>

                            <div x-show="palettes.length"
                                 class="space-y-3">
                                <div class="flex items-center justify-between text-[11px] text-slate-500">
                                    <span x-text="'Adaptation aux valeurs : ' + fitScore + '%'"></span>
                                    <span x-text="fitComment"></span>
                                </div>

                                <div class="flex gap-3 overflow-x-auto scrollbar-thin pt-1 pb-1">
                                    <template x-for="palette in palettes" :key="palette.id">
                                        <button type="button"
                                                class="palette-card text-left min-w-[230px]"
                                                :class="selectedPalette && selectedPalette.id === palette.id ? 'selected' : ''"
                                                @click="selectPalette(palette)">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <div>
                                                    <p class="text-[11px] font-semibold text-slate-700" x-text="palette.name"></p>
                                                    <p class="text-[10px] text-slate-500" x-text="palette.description"></p>
                                                </div>
                                                <span class="text-[11px] font-medium text-slate-500"
                                                      x-text="palette.typeLabel"></span>
                                            </div>

                                            {{-- Swatches --}}
                                            <div class="flex items-center gap-2 mt-2 mb-2">
                                                <template x-for="role in ['primary','secondary','accent','neutral','dark']" :key="role">
                                                    <div class="flex flex-col items-center gap-0.5">
                                                        <div class="color-pill"
                                                             :style="'background:' + palette.colors[role]"></div>
                                                        <span class="text-[9px] text-slate-500 capitalize"
                                                              x-text="role"></span>
                                                    </div>
                                                </template>
                                            </div>

                                            <div class="mt-1 border-t border-dashed border-slate-200 pt-1.5">
                                                <p class="text-[10px] text-slate-500">
                                                    Usages&nbsp;:
                                                    <span class="font-medium text-slate-700" x-text="palette.usage"></span>
                                                </p>
                                            </div>
                                        </button>
                                    </template>
                                </div>

                                {{-- Load more / less --}}
                                <div class="flex items-center justify-between pt-2 border-t border-dashed border-slate-200 mt-1">
                                    <p class="text-[10px] text-slate-500">
                                        Vous pouvez explorer d’autres variations pour affiner votre style.
                                    </p>
                                    <button type="button"
                                            x-show="allPalettes.length > palettes.length || showAllPalettes"
                                            @click="toggleMorePalettes()"
                                            class="text-[11px] font-semibold rounded-full px-3 py-1 border border-slate-300 bg-white hover:bg-slate-50">
                                        <span x-text="showAllPalettes ? 'Afficher moins de palettes' : 'Voir plus de palettes'"></span>
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>

                    {{-- RIGHT: Live preview & mini guide --}}
                    <div class="space-y-6">
                        {{-- Aperçus (avec carré social media) --}}
                        <section class="preview-card p-4 md:p-5">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="mini-title">Aperçu</p>
                                    <h2 class="text-sm font-semibold text-slate-800">
                                        Comment vos couleurs vivront dans la vraie vie
                                    </h2>
                                </div>
                                <span class="text-[11px] text-slate-500">
                                    Post carré, flyer & bouton CTA
                                </span>
                            </div>

                            <template x-if="!selectedPalette">
                                <p class="text-xs text-slate-500">
                                    Choisissez d’abord une palette à gauche pour voir un exemple de post carré type Instagram,
                                    un mini flyer et un bouton de site.
                                </p>
                            </template>

                            <template x-if="selectedPalette">
                                <div class="space-y-4 mt-1">
                                    {{-- Post social carré --}}
                                    <div>
                                        <p class="mini-title mb-1">Post carré (Instagram / réseaux)</p>
                                        <div class="square-post-shell">
                                            <div class="square-post">
                                                {{-- Header style Instagram --}}
                                                <div class="square-post-header">
                                                    <div class="square-post-header-left">
                                                        <div class="square-post-avatar"
                                                             :style="'background:linear-gradient(135deg,'+selectedPalette.colors.primary+','+selectedPalette.colors.secondary+');'">
                                                        </div>
                                                        <div class="square-post-username">
                                                            <span class="font-semibold"
                                                                  :style="'color:' + selectedPalette.colors.dark">
                                                                @votre.therapeute
                                                            </span>
                                                            <span class="text-[9px] text-slate-400">
                                                                Aromathérapie · Bien-être
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="text-[14px] text-slate-400">
                                                        •••
                                                    </div>
                                                </div>

                                                {{-- Content area --}}
                                                <div class="square-post-body">
                                                    <div class="square-post-card"
                                                         :style="'background:radial-gradient(circle at top left,'+selectedPalette.colors.secondary+' 0,'+selectedPalette.colors.neutral+' 40%, white 100%);'">
                                                        <div>
                                                            <p class="text-[9px] uppercase tracking-wide font-semibold"
                                                               :style="'color:' + selectedPalette.colors.accent">
                                                                Respirez · Détendez-vous
                                                            </p>
                                                            <p class="text-[13px] font-semibold mt-1"
                                                               :style="'color:' + selectedPalette.colors.dark">
                                                                Libérez les tensions, apaisez votre système nerveux.
                                                            </p>
                                                            <p class="text-[10px] mt-1.5"
                                                               style="color:#4b5563;">
                                                                Séances d’accompagnement pour le stress, le sommeil et la charge mentale.
                                                            </p>
                                                        </div>
                                                        <div class="mt-2 flex justify-between items-center">
                                                            <button class="cta-button-preview"
                                                                    :style="'background:' + selectedPalette.colors.primary + '; color:white;'">
                                                                Prendre rendez-vous
                                                            </button>
                                                            <span class="text-[9px]"
                                                                  :style="'color:' + selectedPalette.colors.primary">
                                                                Nouvelle séance disponible
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Footer style Instagram --}}
                                                <div class="square-post-footer">
                                                    <div class="square-post-footer-left">
                                                        <span>♡</span>
                                                        <span>💬</span>
                                                        <span>⤴</span>
                                                        <span class="square-post-dot"></span>
                                                        <span class="text-[9px] text-slate-500">
                                                            124 j’aime
                                                        </span>
                                                    </div>
                                                    <span class="text-[9px] text-slate-400">
                                                        Voir les détails
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Flyer --}}
                                    <div>
                                        <p class="mini-title mb-1">Flyer / Affiche A5</p>
                                        <div class="preview-card p-3 bg-white">
                                            <div class="flex gap-3">
                                                <div class="w-[40%] rounded-xl"
                                                     :style="'background:linear-gradient(135deg,' + selectedPalette.colors.primary + ',' + selectedPalette.colors.secondary + ');'">
                                                </div>
                                                <div class="flex-1 flex flex-col justify-between">
                                                    <div>
                                                        <p class="text-[10px] font-medium uppercase tracking-wide"
                                                           :style="'color:' + selectedPalette.colors.primary">
                                                            Cabinet de naturopathie & aromathérapie
                                                        </p>
                                                        <p class="text-[13px] font-semibold mt-1"
                                                           :style="'color:' + selectedPalette.colors.dark">
                                                            Retrouver votre énergie naturellement
                                                        </p>
                                                        <p class="text-[11px] mt-1.5 text-slate-600">
                                                            Consultations individuelles, accompagnements stress & sommeil, ateliers bien-être.
                                                        </p>
                                                    </div>
                                                    <div class="mt-2">
                                                        <p class="text-[10px] text-slate-500">
                                                            <span class="font-semibold"
                                                                  :style="'color:' + selectedPalette.colors.primary">
                                                                Adresse :
                                                            </span> 12 rue des Plantes, 67000 Strasbourg
                                                        </p>
                                                        <p class="text-[10px] text-slate-500">
                                                            <span class="font-semibold"
                                                                  :style="'color:' + selectedPalette.colors.primary">
                                                                Contact :
                                                            </span> 06 00 00 00 00 • votremail@example.com
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bouton CTA web --}}
                                    <div>
                                        <p class="mini-title mb-1">Bouton CTA site web</p>
                                        <div class="flex items-center justify-between gap-3 flex-wrap">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <button class="cta-button-preview"
                                                        :style="'background:' + selectedPalette.colors.primary + '; color:white;'">
                                                    Prendre rendez-vous
                                                </button>
                                                <button class="cta-button-preview"
                                                        :style="'background:transparent; color:' + selectedPalette.colors.primary + '; border:1px solid ' + selectedPalette.colors.primary + ';'">
                                                    Découvrir mes services
                                                </button>
                                            </div>
                                            <span class="text-[10px] text-slate-500">
                                                Hover&nbsp;:
                                                <span :style="'color:' + selectedPalette.colors.accent">
                                                    accent / couleur légèrement éclaircie
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </section>

                        {{-- Mini guide récap --}}
                        <section class="preview-card p-4 md:p-5 bg-slate-50">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="mini-title">Mini guide de marque</p>
                                    <h2 class="text-sm font-semibold text-slate-800">
                                        Résumé à conserver ou à transférer dans vos outils (Canva, site…)
                                    </h2>
                                </div>
                            </div>

                            <template x-if="!selectedPalette">
                                <p class="text-xs text-slate-500">
                                    Dès qu’une palette est sélectionnée, nous vous proposons un résumé utilisable comme base de charte graphique.
                                </p>
                            </template>

                            <template x-if="selectedPalette">
                                <div class="space-y-3 text-xs">
                                    {{-- Couleurs --}}
                                    <div>
                                        <p class="font-semibold text-slate-700 mb-1.5">1. Couleurs & usages</p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <template x-for="(label, role) in colorRoleLabels" :key="role">
                                                <div class="flex items-start gap-2">
                                                    <div class="w-7 h-7 rounded-full border border-slate-300"
                                                         :style="'background:' + selectedPalette.colors[role]"></div>
                                                    <div>
                                                        <p class="text-[11px] font-semibold text-slate-700">
                                                            <span x-text="label"></span>
                                                            <span class="ml-1 text-[10px] text-slate-500"
                                                                  x-text="selectedPalette.colors[role]"></span>
                                                        </p>
                                                        <p class="text-[10px] text-slate-500"
                                                           x-text="colorUsage[role]"></p>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Typo --}}
                                    <div class="pt-1 border-t border-dashed border-slate-200">
                                        <p class="font-semibold text-slate-700 mb-1.5">2. Typographies suggérées</p>
                                        <div class="flex flex-wrap gap-1.5">
                                            <div class="font-chip">
                                                Titres : <span class="font-semibold" x-text="typography.titles"></span>
                                            </div>
                                            <div class="font-chip">
                                                Texte : <span class="font-semibold" x-text="typography.body"></span>
                                            </div>
                                            <div class="text-[10px] text-slate-500 basis-full mt-0.5">
                                                Vous pouvez configurer ces polices dans Canva, votre site ou vos documents.
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Ton & style images --}}
                                    <div class="pt-1 border-t border-dashed border-slate-200">
                                        <p class="font-semibold text-slate-700 mb-1.5">3. Style visuel & photos</p>
                                        <ul class="list-disc pl-4 space-y-0.5">
                                            <li class="text-[11px] text-slate-600"
                                                x-text="imageStyle"></li>
                                            <li class="text-[11px] text-slate-600">
                                                Utilisez un maximum de <span class="font-semibold">toujours les mêmes 2–3 filtres ou réglages</span>
                                                sur vos photos pour garder une cohérence.
                                            </li>
                                            <li class="text-[11px] text-slate-600">
                                                Conservez ces codes couleur pour vos posts, stories et flyers afin d’être reconnaissable au premier coup d’œil.
                                            </li>
                                        </ul>
                                    </div>

                                    {{-- Copy & paste --}}
                                    <div class="pt-1 border-t border-dashed border-slate-200">
                                        <p class="font-semibold text-slate-700 mb-1">4. À copier-coller dans vos outils</p>
                                        <p class="text-[10px] text-slate-500 mb-1">
                                            Vous pouvez copier la ligne ci-dessous et la coller dans vos notes, Notion ou un document&nbsp;:
                                        </p>
                                        <div class="text-[10px] bg-white border border-dashed border-slate-300 rounded-lg p-2 leading-relaxed">
                                            Palette sélectionnée :
                                            <span class="font-semibold" x-text="selectedPalette.name"></span>
                                            — Primary <span x-text="selectedPalette.colors.primary"></span>,
                                            Secondary <span x-text="selectedPalette.colors.secondary"></span>,
                                            Accent <span x-text="selectedPalette.colors.accent"></span>,
                                            Neutral <span x-text="selectedPalette.colors.neutral"></span>,
                                            Dark <span x-text="selectedPalette.colors.dark"></span>.
                                            Typo titres : <span class="font-semibold" x-text="typography.titles"></span>,
                                            texte : <span class="font-semibold" x-text="typography.body"></span>.
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function brandAssistant() {
            return {
                step: 1,
                identity: {
                    tone: '',
                    audience: '',
                    values: []
                },
                availableValues: [
                    { id: 'harmonie', label: 'Harmonie & douceur' },
                    { id: 'simplicite', label: 'Simplicité & clarté' },
                    { id: 'confiance', label: 'Confiance & sérieux' },
                    { id: 'vitalite', label: 'Vitalité & énergie' },
                    { id: 'nature', label: 'Nature & plantes' },
                    { id: 'modernite', label: 'Modernité & digital' }
                ],
                allPalettes: [],
                palettes: [],
                showAllPalettes: false,
                selectedPalette: null,
                fitScore: 0,
                fitComment: '',
                typography: {
                    titles: 'Playfair Display (ou équivalent serif)',
                    body: 'Inter / Lato / Open Sans'
                },
                imageStyle: 'Photos lumineuses, naturelles, avec touches végétales et ambiance douce.',
                colorRoleLabels: {
                    primary: 'Primary (couleur de marque)',
                    secondary: 'Secondary (soutien)',
                    accent: 'Accent (mise en avant)',
                    neutral: 'Neutral (fond & blocs)',
                    dark: 'Dark (texte principal)'
                },
                colorUsage: {
                    primary: 'Titres, boutons importants, éléments clés de votre identité.',
                    secondary: 'Arrière-plans doux, blocs de mise en valeur, badges.',
                    accent: 'Boutons secondaires, éléments interactifs, petits détails qui attirent l’œil.',
                    neutral: 'Fonds de posts, sections de site, zones de texte.',
                    dark: 'Texte principal, sous-titres importants, icônes.'
                },

                toggleValue(id) {
                    const idx = this.identity.values.indexOf(id);
                    if (idx === -1) {
                        this.identity.values.push(id);
                    } else {
                        this.identity.values.splice(idx, 1);
                    }
                },

                generatePalettes() {
                    if (!this.identity.tone) {
                        alert('Choisissez au moins un ton général pour générer des palettes.');
                        return;
                    }

                    let styleKey = 'nature';
                    if (this.identity.tone === 'calme_doux') styleKey = 'calm';
                    if (this.identity.tone === 'premium_minimaliste') styleKey = 'premium';
                    if (this.identity.tone === 'dynamique_energie') styleKey = 'dynamic';
                    if (this.identity.tone === 'nature_bio') styleKey = 'nature';

                    if (this.identity.values.includes('vitalite')) styleKey = 'dynamic';
                    if (this.identity.values.includes('confiance') && this.identity.values.includes('simplicite')) styleKey = 'premium';
                    if (this.identity.values.includes('nature')) styleKey = 'nature';

                    this.buildPalettes(styleKey);
                    this.computeFit();

                    if (this.allPalettes.length) {
                        this.showAllPalettes = false;
                        this.palettes = this.allPalettes.slice(0, 3);
                        this.selectedPalette = this.palettes[0];
                        this.updateTypographyAndImageStyle();
                        this.step = 2;
                    }
                },

                buildPalettes(styleKey) {
                    const library = {
                        calm: [
                            {
                                id: 'calm_analog',
                                name: 'Pastel eucalyptus',
                                typeLabel: 'Analogique',
                                colors: {
                                    primary: '#6BA292',
                                    secondary: '#9FC7B4',
                                    accent: '#F3B8A8',
                                    neutral: '#F6FAF9',
                                    dark: '#253237'
                                },
                                description: 'Tons doux inspirés des spas et de la détente.',
                                usage: 'Idéal pour des posts apaisants, sommeil, stress, accompagnement émotionnel.'
                            },
                            {
                                id: 'calm_compl',
                                name: 'Bleu sauge & pêche',
                                typeLabel: 'Complémentaire douce',
                                colors: {
                                    primary: '#5D7F9B',
                                    secondary: '#FEE4D4',
                                    accent: '#F8A37B',
                                    neutral: '#F4F6F9',
                                    dark: '#1F2933'
                                },
                                description: 'Contraste subtil mais chaleureux.',
                                usage: 'Parfait pour mettre en avant des appels à l’action sans agresser l’œil.'
                            },
                            {
                                id: 'calm_mono',
                                name: 'Bleu brume monochrome',
                                typeLabel: 'Monochrome',
                                colors: {
                                    primary: '#4F6F88',
                                    secondary: '#6C8599',
                                    accent: '#AEC2D3',
                                    neutral: '#F1F4F7',
                                    dark: '#1C2833'
                                },
                                description: 'Une seule famille de bleus pour une identité ultra cohérente.',
                                usage: 'Idéal pour une image très professionnelle et régulière sur vos supports.'
                            },
                            {
                                id: 'calm_soft_lilac',
                                name: 'Brume lilas',
                                typeLabel: 'Analogique',
                                colors: {
                                    primary: '#7E8FB8',
                                    secondary: '#B4C0E0',
                                    accent: '#F5C2E7',
                                    neutral: '#F8FAFF',
                                    dark: '#252641'
                                },
                                description: 'Bleus et lilas très doux pour un univers cocooning.',
                                usage: 'Post Instagram pour méditation guidée, rituels du soir, auto-soin.'
                            },
                            {
                                id: 'calm_sand',
                                name: 'Sable & lin',
                                typeLabel: 'Monochrome',
                                colors: {
                                    primary: '#A68A6B',
                                    secondary: '#CBB59F',
                                    accent: '#F3D9B1',
                                    neutral: '#F6F2EC',
                                    dark: '#3F2F25'
                                },
                                description: 'Palette beige/chair pour une ambiance très naturelle et minimaliste.',
                                usage: 'Flyers, brochures imprimées, accompagnements corps-esprit.'
                            }
                        ],
                        nature: [
                            {
                                id: 'nature_analog',
                                name: 'Vert feuille & mousse',
                                typeLabel: 'Analogique',
                                colors: {
                                    primary: '#647A0B',
                                    secondary: '#A3B73A',
                                    accent: '#F2C879',
                                    neutral: '#F5F7EC',
                                    dark: '#283118'
                                },
                                description: 'Palette verte organique très proche de la nature.',
                                usage: 'Parfait pour aromathérapie, phytothérapie, naturopathie, tisanes.'
                            },
                            {
                                id: 'nature_compl',
                                name: 'Vert sauge & terre cuite',
                                typeLabel: 'Complémentaire douce',
                                colors: {
                                    primary: '#5C7D5B',
                                    secondary: '#F4E6D4',
                                    accent: '#C97A4A',
                                    neutral: '#F7F4EE',
                                    dark: '#243126'
                                },
                                description: 'Mariage entre le végétal et la chaleur de la terre.',
                                usage: 'Flyers, affiches et posts “cocooning” autour des saisons & rituels.'
                            },
                            {
                                id: 'nature_mono',
                                name: 'Forêt profonde',
                                typeLabel: 'Monochrome',
                                colors: {
                                    primary: '#355C3A',
                                    secondary: '#4F7653',
                                    accent: '#95B89A',
                                    neutral: '#EDF3EE',
                                    dark: '#1C2B20'
                                },
                                description: 'Déclinaison de verts pour une cohérence très forte.',
                                usage: 'Idéal si vous voulez une image très identifiable autour du vert.'
                            },
                            {
                                id: 'nature_soft_sage',
                                name: 'Sauge & crème',
                                typeLabel: 'Analogique',
                                colors: {
                                    primary: '#6E8B74',
                                    secondary: '#B1C4A9',
                                    accent: '#E9CFA8',
                                    neutral: '#F6F5F0',
                                    dark: '#28352B'
                                },
                                description: 'Tons sages, très naturels et lumineux.',
                                usage: 'Posts éducatifs, carrousels, stories sur la phytothérapie.'
                            },
                            {
                                id: 'nature_herbier',
                                name: 'Herbier séché',
                                typeLabel: 'Complémentaire douce',
                                colors: {
                                    primary: '#7B8C4F',
                                    secondary: '#F4ECD8',
                                    accent: '#B9804A',
                                    neutral: '#F9F6EF',
                                    dark: '#2C3020'
                                },
                                description: 'Vert, beige et brun rappelant les herbiers et les plantes séchées.',
                                usage: 'Identité très “terre & plantes”, parfaite pour un cabinet de campagne.'
                            }
                        ],
                        premium: [
                            {
                                id: 'premium_analog',
                                name: 'Bleu encre & champagne',
                                typeLabel: 'Analogique',
                                colors: {
                                    primary: '#1F2937',
                                    secondary: '#4B5563',
                                    accent: '#E6C9A8',
                                    neutral: '#F5F5F4',
                                    dark: '#111827'
                                },
                                description: 'Style cabinet haut de gamme, très sérieux.',
                                usage: 'Sites web épurés, prises de rendez-vous, accompagnements premium.'
                            },
                            {
                                id: 'premium_compl',
                                name: 'Bleu nuit & or doux',
                                typeLabel: 'Complémentaire douce',
                                colors: {
                                    primary: '#020617',
                                    secondary: '#F5E9D8',
                                    accent: '#D4AF37',
                                    neutral: '#F3F4F6',
                                    dark: '#020617'
                                },
                                description: 'Contraste chic avec une touche dorée.',
                                usage: 'Programmes signature, packs premium, offres VIP.'
                            },
                            {
                                id: 'premium_mono',
                                name: 'Gris chaud minimal',
                                typeLabel: 'Monochrome',
                                colors: {
                                    primary: '#374151',
                                    secondary: '#6B7280',
                                    accent: '#D1D5DB',
                                    neutral: '#F9FAFB',
                                    dark: '#111827'
                                },
                                description: 'Monochrome moderne et minimaliste.',
                                usage: 'Branding très sobre, coaching, accompagnements professionnels.'
                            },
                            {
                                id: 'premium_ink',
                                name: 'Encre & ivoire',
                                typeLabel: 'Analogique',
                                colors: {
                                    primary: '#111827',
                                    secondary: '#4B5563',
                                    accent: '#FACC6B',
                                    neutral: '#F4F4ED',
                                    dark: '#020617'
                                },
                                description: 'Presque noir, idéal pour une image très éditoriale.',
                                usage: 'Newsletters, contenus écrits, ebooks, masterclasses premium.'
                            },
                            {
                                id: 'premium_rosé',
                                name: 'Rosé nude & charbon',
                                typeLabel: 'Complémentaire douce',
                                colors: {
                                    primary: '#262626',
                                    secondary: '#F4E1DE',
                                    accent: '#D291A3',
                                    neutral: '#FBF7F6',
                                    dark: '#171717'
                                },
                                description: 'Palette très tendance, féminine et haut de gamme.',
                                usage: 'Accompagnement femme entrepreneure, coaching, programmes signature.'
                            }
                        ],
                        dynamic: [
                            {
                                id: 'dyn_analog',
                                name: 'Corail & mandarine',
                                typeLabel: 'Analogique',
                                colors: {
                                    primary: '#F9735B',
                                    secondary: '#FDBA74',
                                    accent: '#22C55E',
                                    neutral: '#FFF7ED',
                                    dark: '#1F2933'
                                },
                                description: 'Palette vive pour l’énergie et la motivation.',
                                usage: 'Sport, programmes de remise en forme, challenges bien-être.'
                            },
                            {
                                id: 'dyn_compl',
                                name: 'Turquoise & corail',
                                typeLabel: 'Complémentaire douce',
                                colors: {
                                    primary: '#0EA5E9',
                                    secondary: '#FEF3C7',
                                    accent: '#F97316',
                                    neutral: '#EFF6FF',
                                    dark: '#0F172A'
                                },
                                description: 'Contraste très dynamique qui reste accessible.',
                                usage: 'Stories, bannières, promotions et lancements de programmes.'
                            },
                            {
                                id: 'dyn_mono',
                                name: 'Orange vitalité',
                                typeLabel: 'Monochrome',
                                colors: {
                                    primary: '#EA580C',
                                    secondary: '#FB923C',
                                    accent: '#FED7AA',
                                    neutral: '#FFF7ED',
                                    dark: '#1C1917'
                                },
                                description: 'Monochrome orange énergisant.',
                                usage: 'Parfait pour une image très “pep’s” autour du mouvement & de la joie.'
                            },
                            {
                                id: 'dyn_sunrise',
                                name: 'Lever de soleil',
                                typeLabel: 'Analogique',
                                colors: {
                                    primary: '#F97316',
                                    secondary: '#FACC15',
                                    accent: '#22C55E',
                                    neutral: '#FFFBEB',
                                    dark: '#1F2937'
                                },
                                description: 'Jaune/mandarine pour une énergie solaire.',
                                usage: 'Défis 7 jours, programmes de motivation, lancement de nouveaux services.'
                            },
                            {
                                id: 'dyn_pop',
                                name: 'Pop framboise',
                                typeLabel: 'Complémentaire douce',
                                colors: {
                                    primary: '#EC4899',
                                    secondary: '#FCE7F3',
                                    accent: '#6366F1',
                                    neutral: '#EEF2FF',
                                    dark: '#111827'
                                },
                                description: 'Couleurs pop pour une communication très moderne.',
                                usage: 'Public jeune, Instagram, reels, contenus très dynamiques.'
                            }
                        ]
                    };

                    this.allPalettes = library[styleKey] || library['nature'];
                },

                computeFit() {
                    let score = 50;
                    if (this.identity.values.includes('nature')) score += 20;
                    if (this.identity.values.includes('harmonie')) score += 10;
                    if (this.identity.values.includes('confiance')) score += 10;
                    if (this.identity.values.includes('vitalite')) score += 10;
                    if (this.identity.values.includes('simplicite')) score += 5;

                    if (score > 100) score = 100;
                    this.fitScore = score;

                    if (score >= 85) this.fitComment = 'Excellente cohérence avec ce que vous souhaitez transmettre.';
                    else if (score >= 70) this.fitComment = 'Très cohérent, vous pouvez ajuster quelques détails si besoin.';
                    else this.fitComment = 'Cohérence correcte, mais vous pouvez tester un autre style si cela vous parle plus.';
                },

                toggleMorePalettes() {
                    this.showAllPalettes = !this.showAllPalettes;
                    if (this.showAllPalettes) {
                        this.palettes = this.allPalettes;
                    } else {
                        this.palettes = this.allPalettes.slice(0, 3);
                    }
                },

                selectPalette(palette) {
                    this.selectedPalette = palette;
                    this.step = 3;
                    this.updateTypographyAndImageStyle();
                },

                updateTypographyAndImageStyle() {
                    if (!this.selectedPalette) return;

                    if (this.identity.tone === 'premium_minimaliste') {
                        this.typography.titles = 'Playfair Display / Cormorant / Marcellus';
                        this.typography.body = 'Inter / Source Sans / Open Sans';
                        this.imageStyle = 'Photos très épurées, beaucoup de blanc, quelques détails dorés ou gris foncé.';
                    } else if (this.identity.tone === 'dynamique_energie') {
                        this.typography.titles = 'Poppins / Montserrat (gras)';
                        this.typography.body = 'Inter / Raleway';
                        this.imageStyle = 'Photos lumineuses, mouvements, personnes en action, couleurs vives mais maîtrisées.';
                    } else if (this.identity.tone === 'calme_doux') {
                        this.typography.titles = 'Cormorant / Playfair Display léger';
                        this.typography.body = 'Nunito / Quicksand / Inter';
                        this.imageStyle = 'Images soft, flou léger, textures naturelles, bougies, plaids, ambiance détente.';
                    } else if (this.identity.tone === 'nature_bio') {
                        this.typography.titles = 'Cormorant / Lora';
                        this.typography.body = 'Nunito / Inter / Lato';
                        this.imageStyle = 'Macro de plantes, huiles essentielles, bois, céramique, lumière naturelle douce.';
                    } else {
                        this.typography.titles = 'Playfair Display (ou équivalent serif)';
                        this.typography.body = 'Inter / Lato / Open Sans';
                        this.imageStyle = 'Photos naturelles, lumière douce, éléments végétaux.';
                    }
                }
            }
        }
    </script>
</x-app-layout>
