<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Créer un Événement') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Nouvel Événement') }}</h1>

            <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Name -->
                <div class="details-box">
                    <label class="details-label" for="name">{{ __('Nom de l\'Événement') }}</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Quill (Description) --}}
                <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">

                <div class="details-box">
                    <label class="details-label">{{ __('Description') }}</label>

                    {{-- Hidden field actually submitted --}}
                    <input type="hidden" name="description" id="description_input" value="{{ old('description') }}">

                    {{-- Initial content holder (safe for HTML) --}}
                    <textarea id="description_initial" class="hidden">{{ old('description') }}</textarea>

                    {{-- Quill editor container --}}
                    <div id="description_editor" class="bg-white" style="border-radius: 10px;"></div>

                    @error('description')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror

                    <noscript>
                        <div class="mt-2">
                            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                        </div>
                    </noscript>
                </div>

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
                    <input type="datetime-local" id="start_date_time" name="start_date_time" class="form-control" value="{{ old('start_date_time') }}" required>
                    @error('start_date_time')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bloquer l'agenda -->
                <div class="details-box">
                    <label class="details-label" for="block_calendar">
                        {{ __("Bloquer mon agenda sur cette plage horaire") }}
                    </label>

                    <label class="d-flex align-items-center gap-2">
                        <input
                            type="checkbox"
                            id="block_calendar"
                            name="block_calendar"
                            value="1"
                            {{ old('block_calendar') ? 'checked' : '' }}
                        >
                        <span>{{ __("Créer une indisponibilité temporaire (indispo) au nom de l'événement") }}</span>
                    </label>

                    @error('block_calendar')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Duration -->
                <div class="details-box">
                    <label class="details-label" for="duration">{{ __('Durée (minutes)') }}</label>
                    <input type="number" id="duration" name="duration" class="form-control" value="{{ old('duration') }}" required>
                    @error('duration')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NEW: Event Type -->
                <div class="details-box">
                    <label class="details-label">{{ __('Format') }}</label>

                    @php
                        $oldType = old('event_type', 'in_person');
                        $oldProvider = old('visio_provider', 'external');
                    @endphp

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

                    @error('event_type')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NEW: Visio options -->
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
                        <input type="url" id="visio_url" name="visio_url" class="form-control" value="{{ old('visio_url') }}" placeholder="https://...">
                        @error('visio_url')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-slate-500 mt-2">
                            {{ __('Si vous choisissez "Créer un lien AromaMade", le lien sera généré automatiquement après création.') }}
                        </p>
                    </div>
                </div>

                <!-- Booking Required -->
                <div class="details-box" id="bookingRequiredBox">
                    <label class="details-label">{{ __('Réservation Obligatoire') }}</label>
                    <div>
                        <input type="radio" id="booking_required_yes" name="booking_required" value="1" {{ old('booking_required') == '1' ? 'checked' : '' }} required>
                        <label for="booking_required_yes">{{ __('Oui') }}</label>
                    </div>
                    <div>
                        <input type="radio" id="booking_required_no" name="booking_required" value="0" {{ old('booking_required') == '0' ? 'checked' : '' }} required>
                        <label for="booking_required_no">{{ __('Non') }}</label>
                    </div>
                    @error('booking_required')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ✅ NEW: Payment / Stripe -->
                @php
                    $stripeConnected = !empty(auth()->user()->stripe_account_id);
                    $oldCollectPayment = old('collect_payment') == '1';
                @endphp

                <div class="details-box" id="paymentBox">
                    <label class="details-label">{{ __('Paiement avant réservation (Stripe)') }}</label>

                    <div class="p-3" style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 10px;">
                        <label class="d-flex align-items-center gap-2" style="margin-bottom: 6px;">
                            <input
                                type="checkbox"
                                id="collect_payment"
                                name="collect_payment"
                                value="1"
                                {{ $oldCollectPayment ? 'checked' : '' }}
                                {{ $stripeConnected ? '' : 'disabled' }}
                            >
                            <span style="font-weight: 600;">
                                {{ __("Activer le paiement pour valider la réservation") }}
                            </span>
                        </label>

                        @if(!$stripeConnected)
                            <p class="text-red-500" style="margin: 6px 0 0 0;">
                                {{ __("Vous devez d’abord connecter votre compte Stripe (Stripe Connect) pour activer le paiement.") }}
                            </p>
                        @else
                            <p class="text-xs text-slate-500" style="margin: 6px 0 0 0;">
                                {{ __("Le paiement est collecté sur votre compte Stripe connecté. La réservation est confirmée uniquement après paiement.") }}
                            </p>
                        @endif

                        <div id="paymentFields" style="display:none; margin-top: 12px;">
                            <div class="d-flex gap-3 flex-wrap">
                                <div style="flex: 1; min-width: 200px;">
                                    <label class="details-label" for="price">{{ __('Prix TTC (€)') }}</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        id="price"
                                        name="price"
                                        class="form-control"
                                        value="{{ old('price') }}"
                                        placeholder="Ex: 25.00"
                                    >
                                    @error('price')
                                        <p class="text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div style="flex: 1; min-width: 200px;">
                                    <label class="details-label" for="tax_rate">{{ __('TVA (%) (optionnel)') }}</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        id="tax_rate"
                                        name="tax_rate"
                                        class="form-control"
                                        value="{{ old('tax_rate') }}"
                                        placeholder="Ex: 20"
                                    >
                                    @error('tax_rate')
                                        <p class="text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <p class="text-xs text-slate-500" style="margin-top: 8px;">
                                {{ __("Si vous ne gérez pas la TVA, laissez à 0. Le prix TTC doit être > 0.") }}
                            </p>

                            <p class="text-red-500" id="paymentBookingHint" style="display:none; margin-top: 8px;">
                                {{ __("Pour activer le paiement, vous devez sélectionner “Réservation Obligatoire : Oui”.") }}
                            </p>
                        </div>

                        @error('collect_payment')
                            <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <!-- ✅ END Payment -->

                <!-- Limited Spot -->
                <div class="details-box">
                    <label class="details-label">{{ __('Places Limitées') }}</label>
                    <div>
                        <input type="radio" id="limited_spot_yes" name="limited_spot" value="1" {{ old('limited_spot') == '1' ? 'checked' : '' }} required>
                        <label for="limited_spot_yes">{{ __('Oui') }}</label>
                    </div>
                    <div>
                        <input type="radio" id="limited_spot_no" name="limited_spot" value="0" {{ old('limited_spot') == '0' ? 'checked' : '' }} required>
                        <label for="limited_spot_no">{{ __('Non') }}</label>
                    </div>
                    @error('limited_spot')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Number of Spot -->
                <div class="details-box">
                    <label class="details-label" for="number_of_spot">{{ __('Nombre de Places') }}</label>
                    <input type="number" id="number_of_spot" name="number_of_spot" class="form-control" value="{{ old('number_of_spot') }}">
                    @error('number_of_spot')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Associated Product -->
                <div class="details-box">
                    <label class="details-label" for="associated_product">{{ __('Produit Associé') }}</label>
                    <select id="associated_product" name="associated_product" class="form-control">
                        <option value="">{{ __('Aucun') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('associated_product') == $product->id ? 'selected' : '' }}>
                                {{ $product->name ?? ('Produit #' . $product->id) }}
                            </option>
                        @endforeach
                    </select>
                    @error('associated_product')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image -->
                <div class="details-box">
                    <label class="details-label" for="image">{{ __('Image') }}</label>
                    <input type="file" id="image" name="image" class="form-control">
                    @error('image')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Show On Portail -->
                <div class="details-box">
                    <label class="details-label">{{ __('Afficher sur le portail') }}</label>
                    <div>
                        <input type="radio" id="showOnPortail_yes" name="showOnPortail" value="1" {{ old('showOnPortail') == '1' ? 'checked' : '' }} required>
                        <label for="showOnPortail_yes">{{ __('Oui') }}</label>
                    </div>
                    <div>
                        <input type="radio" id="showOnPortail_no" name="showOnPortail" value="0" {{ old('showOnPortail') == '0' ? 'checked' : '' }} required>
                        <label for="showOnPortail_no">{{ __('Non') }}</label>
                    </div>
                    @error('showOnPortail')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location (only meaningful for in-person, but we keep it for compatibility) -->
                <div class="details-box" id="locationBox">
                    <label class="details-label" for="location">{{ __('Lieu') }}</label>
                    <input type="text" id="location" name="location" class="form-control" value="{{ old('location') }}">
                    @error('location')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-500 mt-2" id="locationHint" style="display:none;">
                        {{ __('Pour un événement en visio, laissez vide : on affichera automatiquement "En ligne (Visio)".') }}
                    </p>
                </div>

                <button type="submit" class="btn-primary mt-4">{{ __('Créer l\'Événement') }}</button>

            </form>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 800px;
        }

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

        .details-box {
            margin-bottom: 15px;
        }

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

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .form-check input {
            margin-right: 10px;
        }

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

        .btn-primary:hover {
            background-color: #854f38;
        }

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

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
        }

        .text-red-500 {
            color: #e3342f;
            font-size: 0.875rem;
        }

        /* Hide the number_of_spot_container by default */
        #number_of_spot_container {
            display: none;
        }
    </style>

    <script>
        (function() {
            const typeRadios = document.querySelectorAll('input[name="event_type"]');
            const providerRadios = document.querySelectorAll('input[name="visio_provider"]');
            const visioOptions = document.getElementById('visioOptions');
            const visioUrlWrap = document.getElementById('visioUrlWrap');
            const locationHint = document.getElementById('locationHint');

            function currentType() {
                const checked = document.querySelector('input[name="event_type"]:checked');
                return checked ? checked.value : 'in_person';
            }
            function currentProvider() {
                const checked = document.querySelector('input[name="visio_provider"]:checked');
                return checked ? checked.value : 'external';
            }

            function refresh() {
                const t = currentType();
                const p = currentProvider();

                const isVisio = (t === 'visio');
                visioOptions.style.display = isVisio ? '' : 'none';
                locationHint.style.display = isVisio ? '' : 'none';

                // If aromamade, URL is optional and visually less important
                visioUrlWrap.style.display = (isVisio && p === 'external') ? '' : '';
                const urlInput = document.getElementById('visio_url');
                if (urlInput) {
                    urlInput.required = (isVisio && p === 'external');
                    if (!isVisio) urlInput.required = false;
                }
            }

            typeRadios.forEach(r => r.addEventListener('change', refresh));
            providerRadios.forEach(r => r.addEventListener('change', refresh));
            refresh();
        })();
    </script>

    {{-- ✅ NEW: Payment JS (Stripe connected + booking_required gate) --}}
    <script>
        (function() {
            const stripeConnected = {{ !empty(auth()->user()->stripe_account_id) ? 'true' : 'false' }};

            const cb = document.getElementById('collect_payment');
            const fields = document.getElementById('paymentFields');
            const hint = document.getElementById('paymentBookingHint');

            const price = document.getElementById('price');
            const tax = document.getElementById('tax_rate');

            const bookingYes = document.getElementById('booking_required_yes');
            const bookingNo  = document.getElementById('booking_required_no');

            if (!cb || !fields || !bookingYes || !bookingNo) return;

            function bookingRequiredIsYes() {
                return bookingYes.checked === true;
            }

            function showFields(show) {
                fields.style.display = show ? '' : 'none';
            }

            function setPriceRequired(required) {
                if (price) price.required = !!required;
            }

            function cleanupIfOff() {
                if (!cb.checked) {
                    if (price) price.value = '';
                    if (tax) tax.value = '';
                    setPriceRequired(false);
                    hint.style.display = 'none';
                }
            }

            function refreshPaymentUI() {
                // If not connected, force off
                if (!stripeConnected) {
                    cb.checked = false;
                    showFields(false);
                    cleanupIfOff();
                    return;
                }

                // If booking_required is "No", payment cannot be on
                if (!bookingRequiredIsYes()) {
                    if (cb.checked) cb.checked = false;
                    showFields(false);
                    cleanupIfOff();
                    hint.style.display = 'none';
                    return;
                }

                // booking_required yes
                const on = cb.checked;
                showFields(on);
                setPriceRequired(on);

                // show hint only if user tries to enable while booking no (handled earlier)
                hint.style.display = 'none';
            }

            cb.addEventListener('change', () => {
                // If booking is not yes -> revert and show hint
                if (cb.checked && !bookingRequiredIsYes()) {
                    cb.checked = false;
                    showFields(false);
                    cleanupIfOff();
                    hint.style.display = '';
                    return;
                }
                refreshPaymentUI();
            });

            bookingYes.addEventListener('change', refreshPaymentUI);
            bookingNo.addEventListener('change', refreshPaymentUI);

            // initial
            refreshPaymentUI();
        })();
    </script>
</x-app-layout>