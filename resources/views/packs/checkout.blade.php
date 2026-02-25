{{-- resources/views/packs/checkout.blade.php --}}

@php
    $packs = $packs ?? collect();
    $trainings = $trainings ?? collect();

    $selectedType = $selectedType ?? 'pack';
    $selectedId   = $selectedId   ?? null;

    $therapistName = $therapist->company_name ?? $therapist->name;

    // Canonical checkout route (no pack in URL)
    $currentCheckoutUrl = route('public.checkout.show', ['slug' => $therapist->slug]);

    $isPack = ($selectedType === 'pack');
    $currentItemValue = $selectedId ? ($selectedType . ':' . (int) $selectedId) : null;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ $isPack ? __('Acheter un pack') : __('Acheter une formation') }}
        </h2>
    </x-slot>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <style>
        :root { --brand:#647a0b; --brown:#854f38; --bg:#f9f9f9; }
        .container { max-width: 900px; }
        .details-container{
            background:var(--bg); border-radius:14px; padding:22px;
            box-shadow:0 10px 25px rgba(0,0,0,.08);
            border:1px solid rgba(0,0,0,.04);
            margin: 0 auto;
        }
        .details-title{ font-size:2rem; font-weight:800; color:var(--brand); text-align:center; margin-bottom:6px;}
        .subtle{ color:#64748b; font-size:.92rem; text-align:center; margin-bottom:16px;}
        .card-soft{ background:rgba(255,255,255,.85); border:1px solid rgba(0,0,0,.05); border-radius:14px; padding:14px; }
        .details-label{ font-weight:800; color:var(--brand); display:block; margin-bottom:6px; font-size:.95rem;}
        .form-control{
            width:100%; padding:10px 12px; border:1px solid rgba(133,79,56,.55);
            border-radius:10px; background:#fff;
        }
        .form-control:focus{ border-color:var(--brand); outline:none; box-shadow:0 0 0 4px rgba(100,122,11,.14); }
        .btn-primary, .btn-secondary{
            border:none; padding:10px 16px; border-radius:12px; font-size:1rem; font-weight:800;
            display:inline-flex; align-items:center; gap:.5rem; text-decoration:none; user-select:none;
        }
        .btn-primary{ background:var(--brand); color:#fff; }
        .btn-primary:hover{ background:var(--brown); color:#fff; }
        .btn-secondary{ background:#fff; color:var(--brand); border:1px solid rgba(100,122,11,.35); }
        .btn-secondary:hover{ background:rgba(100,122,11,.08); color:var(--brand); }
        .summary-row{
            display:flex; flex-wrap:wrap; gap:10px; justify-content:space-between;
            background:rgba(100,122,11,.06); border:1px dashed rgba(100,122,11,.35);
            border-radius:12px; padding:10px 12px; margin-top:10px;
        }
        .summary-row .item{ font-size:.92rem; color:#334155; }
        .summary-row .item b{ color:#0f172a; }
        .hint{ font-size:.9rem; color:#64748b; }
        .text-red-500{ color:#e3342f; font-size:.9rem; margin-top:6px; }
    </style>

    <div class="container mt-5">
        <div class="details-container">

            <h1 class="details-title">
                {{ $isPack ? $pack->name : ($training?->title ?? __('Formation')) }}
            </h1>

            <p class="subtle">
                {{ __('Paiement sécurisé — vous recevrez un email avec les informations et l’accès après confirmation.') }}
            </p>

            {{-- Item selector --}}
            <div class="card-soft">
                <label class="details-label" for="item">
                    {{ __('Choisir un achat') }}
                </label>

                <select id="item" name="item" class="form-control">
                    @if($packs->count())
                        <optgroup label="{{ __('Packs') }}">
                            @foreach($packs as $p)
                                <option value="{{ 'pack:' . $p->id }}" {{ $currentItemValue === ('pack:' . $p->id) ? 'selected' : '' }}>
                                    {{ $p->name }}
                                    @if(!is_null($p->price_incl_tax))
                                        — {{ number_format($p->price_incl_tax, 2, ',', ' ') }} €
                                    @elseif(!is_null($p->price))
                                        — {{ number_format($p->price, 2, ',', ' ') }} €
                                    @endif
                                </option>
                            @endforeach
                        </optgroup>
                    @endif

                    @if($trainings->count())
                        <optgroup label="{{ __('Formations') }}">
                            @foreach($trainings as $t)
                                @php
                                    $tIsFree = (bool) ($t->is_free ?? false);
                                    $tPriceStr = null;
                                    if (!$tIsFree && !is_null($t->price_cents)) {
                                        $tPriceStr = number_format($t->price_cents / 100, 2, ',', ' ') . ' €';
                                    }
                                @endphp
                                <option value="{{ 'training:' . $t->id }}" {{ $currentItemValue === ('training:' . $t->id) ? 'selected' : '' }}>
                                    {{ $t->title ?? __('Formation') }}
                                    @if($tIsFree)
                                        — {{ __('Gratuit') }}
                                    @elseif($tPriceStr)
                                        — {{ $tPriceStr }}
                                    @endif
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>

                <p class="hint mt-2">
                    {{ __('Vous pouvez acheter un pack ou une formation digitale. Le prix affiché correspond à la sélection en cours.') }}
                </p>

                {{-- Summary --}}
                @if($isPack)
                    <div class="summary-row">
                        <div class="item">
                            <b>{{ __('Pack') }}:</b> {{ $pack->name }}
                        </div>
                        @if(!is_null($packPriceTtc))
                            <div class="item">
                                <b>{{ __('Prix TTC') }}:</b> {{ number_format($packPriceTtc, 2, ',', ' ') }} €
                            </div>
                        @endif
                        @if(!is_null($unitTotalTtc))
                            <div class="item">
                                <b>{{ __('Valeur unitaire') }}:</b> {{ number_format($unitTotalTtc, 2, ',', ' ') }} €
                            </div>
                        @endif
                        @if(!is_null($saving) && $saving > 0)
                            <div class="item">
                                <b>{{ __('Économie') }}:</b>
                                {{ number_format($saving, 2, ',', ' ') }} €
                                @if(!is_null($savingPct))
                                    ({{ $savingPct }}%)
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Pack contents --}}
                    @if($pack->items && $pack->items->count())
                        <div class="mt-4">
                            <div class="details-label">{{ __('Contenu du pack') }}</div>
                            <ul class="list-disc pl-5 text-sm text-slate-700 space-y-1">
                                @foreach($pack->items as $it)
                                    <li>
                                        {{ $it->quantity ?? 1 }} × {{ $it->product?->name ?? __('Produit') }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @else
                    <div class="summary-row">
                        <div class="item">
                            <b>{{ __('Formation') }}:</b> {{ $training?->title ?? __('Formation') }}
                        </div>
                        @if(!empty($trainingPriceStr))
                            <div class="item">
                                <b>{{ __('Prix') }}:</b> {{ $trainingPriceStr }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Checkout form --}}
            <div class="mt-6 card-soft">
                @if($errors->any())
                    <div class="mb-3 text-red-500">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ __('Veuillez corriger les erreurs ci-dessous.') }}
                    </div>
                @endif

                <form class="mt-4" method="POST" action="{{ route('public.checkout.store', ['slug' => $therapist->slug]) }}">
                    @csrf

                    {{-- IMPORTANT: the selected item is posted --}}
                    <input type="hidden" name="item" value="{{ $currentItemValue }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="details-label" for="first_name">{{ __('Prénom') }}</label>
                            <input id="first_name" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                            @error('first_name') <div class="text-red-500">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="details-label" for="last_name">{{ __('Nom') }}</label>
                            <input id="last_name" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                            @error('last_name') <div class="text-red-500">{{ $message }}</div> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="details-label" for="email">{{ __('Email') }}</label>
                            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            @error('email') <div class="text-red-500">{{ $message }}</div> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="details-label" for="phone">{{ __('Téléphone') }} <span class="hint">({{ __('optionnel') }})</span></label>
                            <input id="phone" name="phone" class="form-control" value="{{ old('phone') }}">
                            @error('phone') <div class="text-red-500">{{ $message }}</div> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="details-label" for="notes">{{ __('Notes') }} <span class="hint">({{ __('optionnel') }})</span></label>
                            <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                            @error('notes') <div class="text-red-500">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    @error('item') <div class="text-red-500 mt-3">{{ $message }}</div> @enderror
                    @error('payment') <div class="text-red-500 mt-3">{{ $message }}</div> @enderror

                    <div class="mt-5 flex flex-wrap gap-3 justify-center">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-lock"></i>
                            {{ __('Payer') }}
                        </button>

                        <a href="{{ route('therapist.show', $therapist->slug) }}" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            {{ __('Retour') }}
                        </a>
                    </div>

                    <p class="mt-3 text-center hint">
                        {{ __('En cliquant sur “Payer”, vous serez redirigé vers notre partenaire de paiement sécurisé.') }}
                    </p>
                </form>
            </div>

            <div class="mt-6 text-center hint">
                <div class="font-extrabold" style="color:var(--brand);">
                    {{ $therapistName }}
                </div>
                <div>
                    {{ __('Paiement sécurisé — AromaMade') }}
                </div>
            </div>

        </div>
    </div>

    <script>
        (function () {
            const select = document.getElementById('item');
            if (!select) return;

            const currentCheckoutUrl = @json($currentCheckoutUrl);

            select.addEventListener('change', function () {
                const val = this.value || '';
                const m = val.match(/^(pack|training):(\d+)$/);
                if (!m) return;

                const type = m[1];
                const id   = m[2];

                window.location = currentCheckoutUrl + '?item=' + encodeURIComponent(type + ':' + id);
            });
        })();
    </script>

</x-app-layout>