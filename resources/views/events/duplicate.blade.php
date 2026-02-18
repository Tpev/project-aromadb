{{-- resources/views/events/duplicate.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Dupliquer l\'Événement') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Dupliquer l\'Événement') }}</h1>

            <form action="{{ route('events.duplicate.store', $event->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                @php
                    $oldType = old('event_type', $event->event_type ?? 'in_person');
                    $oldProvider = old('visio_provider', $event->visio_provider ?? 'external');
                    $currentVisioLink = $event->visio_link ?? null; // accessor in Model if exists
                @endphp

                <!-- Name -->
                <div class="details-box">
                    <label class="details-label" for="name">{{ __('Nom de l\'Événement') }}</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $event->name) }}" required>
                    @error('name') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

{{-- Quill CSS --}}
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">

<!-- Description (Quill) -->
<div class="details-box">
    <label class="details-label">{{ __('Description') }}</label>

    {{-- This is what gets submitted --}}
    <input type="hidden" name="description" id="description_input"
           value="{{ old('description', $event->description) }}">

    {{-- Initial content (can be plain text OR HTML from Quill) --}}
    <textarea id="description_initial" class="hidden">{{ old('description', $event->description) }}</textarea>

    {{-- Quill mounts here --}}
    <div id="description_editor" class="bg-white" style="border-radius: 5px;"></div>

    @error('description') <p class="text-red-500">{{ $message }}</p> @enderror

    <noscript>
        <div class="mt-2">
            <textarea name="description" class="form-control">{{ old('description', $event->description) }}</textarea>
        </div>
    </noscript>
</div>

{{-- Quill JS --}}
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const editorEl = document.getElementById('description_editor');
    const inputEl  = document.getElementById('description_input');
    const initEl   = document.getElementById('description_initial');
    if (!editorEl || !inputEl) return;

    const quill = new Quill(editorEl, {
        theme: 'snow',
        placeholder: "Décrivez votre événement (programme, infos pratiques, etc.)",
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote'],
                ['link'],
                ['clean']
            ]
        }
    });

    const initial = (initEl?.value || '').trim();
    if (initial) {
        const looksHtml = /<\/?[a-z][\s\S]*>/i.test(initial);
        if (looksHtml) quill.clipboard.dangerouslyPasteHTML(initial);
        else quill.setText(initial);
    }

    const form = editorEl.closest('form');
    if (form) {
        form.addEventListener('submit', () => {
            const html = quill.root.innerHTML || '';
            const normalized = html.replace(/\s+/g, '').toLowerCase();
            inputEl.value = (normalized === '<p><br></p>') ? '' : html;
        });
    }
});
</script>


                <!-- Start Date and Time -->
                <div class="details-box">
                    <label class="details-label" for="start_date_time">{{ __('Date et Heure de Début') }}</label>
                    <input type="datetime-local"
                           id="start_date_time"
                           name="start_date_time"
                           class="form-control"
                           value="{{ old('start_date_time', \Carbon\Carbon::parse($event->start_date_time)->format('Y-m-d\TH:i')) }}"
                           required>
                    @error('start_date_time') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Duration -->
                <div class="details-box">
                    <label class="details-label" for="duration">{{ __('Durée (minutes)') }}</label>
                    <input type="number" id="duration" name="duration" class="form-control" value="{{ old('duration', $event->duration) }}" required>
                    @error('duration') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Format -->
                <div class="details-box">
                    <label class="details-label">{{ __('Format') }}</label>
                    <div class="d-flex gap-3 flex-wrap">
                        <label class="d-flex align-items-center gap-2">
                            <input type="radio" name="event_type" value="in_person" {{ $oldType === 'in_person' ? 'checked' : '' }}>
                            <span>{{ __('Présentiel') }}</span>
                        </label>

                        <label class="d-flex align-items-center gap-2">
                            <input type="radio" name="event_type" value="visio" {{ $oldType === 'visio' ? 'checked' : '' }}>
                            <span>{{ __('Visio') }}</span>
                        </label>
                    </div>
                    @error('event_type') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Visio options -->
                <div id="visioOptions" class="details-box" style="display:none;">
                    <label class="details-label">{{ __('Options Visio') }}</label>

                    <div class="d-flex gap-3 flex-wrap">
                        <label class="d-flex align-items-center gap-2">
                            <input type="radio" name="visio_provider" value="external" {{ $oldProvider === 'external' ? 'checked' : '' }}>
                            <span>{{ __('Lien externe (Zoom, Meet, Teams, etc.)') }}</span>
                        </label>

                        <label class="d-flex align-items-center gap-2">
                            <input type="radio" name="visio_provider" value="aromamade" {{ $oldProvider === 'aromamade' ? 'checked' : '' }}>
                            <span>{{ __('Créer un lien AromaMade') }}</span>
                        </label>
                    </div>

                    <div id="visioUrlWrap" style="margin-top: 12px;">
                        <label class="details-label" for="visio_url">{{ __('Lien de visio') }}</label>
                        <input type="url"
                               id="visio_url"
                               name="visio_url"
                               class="form-control"
                               value="{{ old('visio_url', $event->visio_url) }}"
                               placeholder="https://...">
                        @error('visio_url') <p class="text-red-500">{{ $message }}</p> @enderror

                        @if(!empty($currentVisioLink))
                            <p class="text-xs text-slate-500 mt-2">
                                {{ __('Lien actuel:') }}
                                <a href="{{ $currentVisioLink }}" target="_blank" rel="noopener noreferrer">{{ $currentVisioLink }}</a>
                            </p>
                        @endif

                        <div class="callout callout-info mt-3">
                            <div class="callout-title">
                                <i class="fas fa-info-circle"></i>
                                <span>{{ __('Info') }}</span>
                            </div>
                            <p class="callout-text">
                                {{ __('Si vous choisissez "Créer un lien AromaMade", un NOUVEAU lien sera généré pour le duplicata (même si l\'événement d\'origine en avait déjà un).') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Booking Required -->
                <div class="details-box">
                    <label class="details-label">{{ __('Réservation Requise') }}</label>
                    <div class="form-check">
                        <input type="radio" id="booking_required_yes" name="booking_required" value="1"
                               {{ old('booking_required', $event->booking_required) == '1' ? 'checked' : '' }} required>
                        <label for="booking_required_yes">{{ __('Oui') }}</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" id="booking_required_no" name="booking_required" value="0"
                               {{ old('booking_required', $event->booking_required) == '0' ? 'checked' : '' }}>
                        <label for="booking_required_no">{{ __('Non') }}</label>
                    </div>
                    @error('booking_required') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Limited Spots -->
                <div class="details-box">
                    <label class="details-label">{{ __('Places Limitées') }}</label>
                    <div class="form-check">
                        <input type="radio" id="limited_spot_yes" name="limited_spot" value="1"
                               {{ old('limited_spot', $event->limited_spot) == '1' ? 'checked' : '' }} required>
                        <label for="limited_spot_yes">{{ __('Oui') }}</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" id="limited_spot_no" name="limited_spot" value="0"
                               {{ old('limited_spot', $event->limited_spot) == '0' ? 'checked' : '' }}>
                        <label for="limited_spot_no">{{ __('Non') }}</label>
                    </div>
                    @error('limited_spot') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Number of Spots -->
                <div class="details-box" id="number_of_spot_container">
                    <label class="details-label" for="number_of_spot">{{ __('Nombre de Places') }}</label>
                    <input type="number" id="number_of_spot" name="number_of_spot" class="form-control"
                           value="{{ old('number_of_spot', $event->number_of_spot) }}">
                    @error('number_of_spot') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Associated Product -->
                <div class="details-box">
                    <label class="details-label" for="associated_product">{{ __('Produit Associé') }}</label>
                    <select id="associated_product" name="associated_product" class="form-control">
                        <option value="">{{ __('Aucun') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                {{ old('associated_product', $event->associated_product) == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('associated_product') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Image -->
                <div class="details-box">
                    <label class="details-label" for="image">{{ __('Image') }}</label>
                    <input type="file" id="image" name="image" class="form-control">
                    @if($event->image)
                        <p class="text-xs text-slate-500 mt-2">
                            {{ __('Image actuelle reprise si vous ne changez rien.') }}
                        </p>
                    @endif
                    @error('image') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Show on Portal -->
                <div class="details-box">
                    <label class="details-label">{{ __('Afficher sur le Portail') }}</label>
                    <div class="form-check">
                        <input type="radio" id="showOnPortail_yes" name="showOnPortail" value="1"
                               {{ old('showOnPortail', $event->showOnPortail) == '1' ? 'checked' : '' }} required>
                        <label for="showOnPortail_yes">{{ __('Oui') }}</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" id="showOnPortail_no" name="showOnPortail" value="0"
                               {{ old('showOnPortail', $event->showOnPortail) == '0' ? 'checked' : '' }}>
                        <label for="showOnPortail_no">{{ __('Non') }}</label>
                    </div>
                    @error('showOnPortail') <p class="text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Location -->
                <div class="details-box" id="locationBox">
                    <label class="details-label" for="location">{{ __('Lieu') }}</label>
                    <input type="text" id="location" name="location" class="form-control" value="{{ old('location', $event->location) }}">
                    @error('location') <p class="text-red-500">{{ $message }}</p> @enderror

                    <p class="text-xs text-slate-500 mt-2" id="locationHint" style="display:none;">
                        {{ __('Pour un événement en visio, vous pouvez laisser vide : on affichera automatiquement "En ligne (Visio)".') }}
                    </p>
                </div>

                <!-- Participants -->
                <div class="details-box">
                    <label class="details-label">{{ __('Participants') }}</label>

                    <div class="rounded-card">
                        <label class="d-flex align-items-start gap-2">
                            <input type="checkbox" id="duplicate_participants" name="duplicate_participants" value="1"
                                   {{ old('duplicate_participants') ? 'checked' : '' }}>
                            <div>
                                <div class="font-semibold text-slate-900">{{ __('Dupliquer aussi la liste des participants (réservations)') }}</div>
                                <div class="text-xs text-slate-600 mt-0.5">
                                    {{ __('Les réservations seront copiées uniquement au moment où vous créez le duplicata.') }}
                                </div>
                            </div>
                        </label>

                        <div id="sendBlock" class="send-block mt-3">
                            <div class="send-block-head">
                                <span class="send-badge">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <div>
                                    <div class="send-title">{{ __('Emails aux participants copiés') }}</div>
                                    <div class="send-subtitle">{{ __('Optionnel — utile si vous souhaitez prévenir tout le monde immédiatement.') }}</div>
                                </div>
                            </div>

                            <label class="d-flex align-items-start gap-2 mt-3" id="send_confirmation_wrap">
                                <input type="checkbox" id="send_confirmation_to_copied_participants"
                                       name="send_confirmation_to_copied_participants" value="1"
                                       {{ old('send_confirmation_to_copied_participants') ? 'checked' : '' }}>
                                <div>
                                    <div class="font-semibold text-slate-900">
                                        {{ __('Envoyer un email de confirmation aux participants copiés') }}
                                    </div>
                                    <div class="text-xs text-slate-600 mt-0.5" id="send_confirmation_hint">
                                        {{ __('Attention : cela enverra un email à chaque participant copié.') }}
                                    </div>
                                </div>
                            </label>

                            <div class="callout callout-warn mt-3" id="send_warning">
                                <div class="callout-title">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>{{ __('Attention') }}</span>
                                </div>
                                <p class="callout-text">
                                    {{ __('À activer seulement si vous voulez réellement prévenir les participants. Sinon, gardez cette option désactivée pour éviter un envoi massif.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary mt-4">{{ __('Créer le duplicata') }}</button>
                <a href="{{ route('events.show', $event->id) }}" class="btn-secondary mt-4">{{ __('Annuler') }}</a>
            </form>
        </div>
    </div>

    <style>
        .container { max-width: 800px; }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .details-title {
            font-size: 2rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        .details-box { margin-bottom: 15px; }

        .details-label {
            font-weight: bold;
            color: #647a0b;
            display: block;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-check { display: flex; align-items: center; margin-bottom: 5px; }
        .form-check input { margin-right: 10px; }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }
        .btn-primary:hover { background-color: #854f38; }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            padding: 10px 20px;
            border: 1px solid #854f38;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }
        .btn-secondary:hover { background-color: #854f38; color: #fff; }

        .text-red-500 { color: #e3342f; font-size: 0.875rem; }

        /* Hide the number_of_spot_container by default */
        #number_of_spot_container { display: none; }

        /* Disabled look */
        .disabled-row { opacity: 0.55; pointer-events: none; }

        /* Nice blocks */
        .rounded-card{
            border: 1px solid #e2ecc3;
            background: #fbfff6;
            border-radius: 12px;
            padding: 14px;
        }

        .send-block{
            border: 1px solid #e2ecc3;
            background: #ffffff;
            border-radius: 12px;
            padding: 12px;
        }

        .send-block-head{
            display:flex;
            gap:10px;
            align-items:flex-start;
        }

        .send-badge{
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display:flex;
            align-items:center;
            justify-content:center;
            background: rgba(100,122,11,0.12);
            color: #647a0b;
            flex: 0 0 auto;
        }

        .send-title{
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }
        .send-subtitle{
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        .callout{
            border-radius: 12px;
            padding: 10px 12px;
            border: 1px solid transparent;
        }
        .callout-title{
            display:flex;
            align-items:center;
            gap:8px;
            font-weight: 800;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        .callout-text{
            margin-top: 6px;
            font-size: 12px;
            color: #475569;
            line-height: 1.4;
        }
        .callout-info{
            border-color: rgba(100,122,11,0.25);
            background: rgba(243,249,221,0.65);
        }
        .callout-info .callout-title{ color:#647a0b; }

        .callout-warn{
            border-color: rgba(234,179,8,0.35);
            background: rgba(254,243,199,0.75);
        }
        .callout-warn .callout-title{ color:#92400e; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ---- limited spots -> show number_of_spot ----
            function toggleNumberOfSpots() {
                var checked = document.querySelector('input[name="limited_spot"]:checked');
                if (!checked) return;

                var limitedSpot = checked.value;
                var numberOfSpotContainer = document.getElementById('number_of_spot_container');
                if (limitedSpot == '1') {
                    numberOfSpotContainer.style.display = 'block';
                } else {
                    numberOfSpotContainer.style.display = 'none';
                }
            }

            // ---- visio / in-person UI ----
            const typeRadios = document.querySelectorAll('input[name="event_type"]');
            const providerRadios = document.querySelectorAll('input[name="visio_provider"]');
            const visioOptions = document.getElementById('visioOptions');
            const locationHint = document.getElementById('locationHint');
            const locationInput = document.getElementById('location');
            const visioUrlInput = document.getElementById('visio_url');

            function currentType() {
                const checked = document.querySelector('input[name="event_type"]:checked');
                return checked ? checked.value : 'in_person';
            }

            function currentProvider() {
                const checked = document.querySelector('input[name="visio_provider"]:checked');
                return checked ? checked.value : 'external';
            }

            function refreshVisioUI() {
                const t = currentType();
                const p = currentProvider();
                const isVisio = (t === 'visio');

                if (visioOptions) visioOptions.style.display = isVisio ? '' : 'none';
                if (locationHint) locationHint.style.display = isVisio ? '' : 'none';

                // Location required only for in-person
                if (locationInput) locationInput.required = !isVisio;

                // External url required only if visio + external
                if (visioUrlInput) {
                    visioUrlInput.required = (isVisio && p === 'external');
                    if (!isVisio) visioUrlInput.required = false;
                }
            }

            // ---- Duplicate participants -> enable/disable email block ----
            const dup = document.getElementById('duplicate_participants');
            const sendBlock = document.getElementById('sendBlock');
            const sendWrap = document.getElementById('send_confirmation_wrap');
            const sendBox = document.getElementById('send_confirmation_to_copied_participants');
            const sendWarning = document.getElementById('send_warning');

            function refreshSendConfirmationUI() {
                const enabled = !!(dup && dup.checked);

                if (sendBlock) {
                    sendBlock.style.display = enabled ? '' : 'none';
                }

                if (!enabled && sendBox) sendBox.checked = false;

                if (sendWrap) sendWrap.classList.toggle('disabled-row', !enabled);
                if (sendWarning) sendWarning.classList.toggle('disabled-row', !enabled);
            }

            // init
            toggleNumberOfSpots();
            refreshVisioUI();
            refreshSendConfirmationUI();

            // listeners
            document.querySelectorAll('input[name="limited_spot"]').forEach(function(radio) {
                radio.addEventListener('change', toggleNumberOfSpots);
            });

            typeRadios.forEach(r => r.addEventListener('change', refreshVisioUI));
            providerRadios.forEach(r => r.addEventListener('change', refreshVisioUI));

            if (dup) dup.addEventListener('change', refreshSendConfirmationUI);
        });
    </script>
</x-app-layout>
