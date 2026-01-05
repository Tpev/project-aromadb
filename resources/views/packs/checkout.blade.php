{{-- resources/views/packs/checkout.blade.php --}}

@php
    $packs = $packs ?? collect();
    $trainings = $trainings ?? collect();

    $selectedType = $selectedType ?? 'pack';

    $therapistName = $therapist->company_name ?? $therapist->name;

    // Current URL for THIS checkout (pack is always in URL)
    $currentCheckoutUrl = route('packs.checkout.show', ['slug' => $therapist->slug, 'pack' => $pack->id]);

    // Base to build pack urls in JS
    $packBaseUrl = url("/pro/{$therapist->slug}/packs");

    $isPack = $selectedType === 'pack';

    // Current selector value
    $currentItemValue = $isPack
        ? ('pack:' . $pack->id)
        : ('training:' . ($training?->id ?? ''));
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
            <div class="subtle">
                {{ $therapistName }} • {{ __('Paiement sécurisé') }}
            </div>

            @if ($errors->any())
                <div class="alert alert-danger text-center">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form class="mt-4" method="POST"
                  action="{{ route('packs.checkout.store', ['slug'=>$therapist->slug, 'pack'=>$pack->id]) }}">
                @csrf

                {{-- SELECTOR + DETAILS --}}
                <div class="card-soft">
                    <div class="details-label">{{ __('Que souhaitez-vous acheter ?') }}</div>

                    <select class="form-control" name="item" id="item">
                        @if($packs->count())
                            <optgroup label="🎁 Packs">
                                @foreach($packs as $p)
                                    <option value="pack:{{ $p->id }}" @selected($currentItemValue === 'pack:'.$p->id)>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif

                        @if($trainings->count())
                            <optgroup label="🎓 Formations">
                                @foreach($trainings as $t)
                                    @php
                                        $isFree = (bool) ($t->is_free ?? false);
                                        $labelPrice = $isFree
                                            ? __('Gratuit')
                                            : (is_null($t->price_cents) ? __('Prix sur demande') : number_format($t->price_cents/100, 2, ',', ' ') . ' €');
                                    @endphp
                                    <option value="training:{{ $t->id }}" @selected($currentItemValue === 'training:'.$t->id)>
                                        {{ $t->title }} — {{ $labelPrice }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>

                    @error('item')<p class="text-red-500">{{ $message }}</p>@enderror

                    <div class="summary-row">
                        @if($isPack)
                            <div class="item">{{ __('Prix du pack') }} :
                                <b>{{ number_format((float)$packPriceTtc, 2, ',', ' ') }} €</b>
                            </div>

                            @if((float)$unitTotalTtc > 0 && (float)$saving > 0.01)
                                <div class="item">
                                    {{ __('À l’unité') }} :
                                    <b style="text-decoration:line-through; opacity:.85;">
                                        {{ number_format((float)$unitTotalTtc, 2, ',', ' ') }} €
                                    </b>
                                </div>
                                <div class="item">
                                    {{ __('Économie') }} :
                                    <b>{{ number_format((float)$saving, 2, ',', ' ') }} €</b>
                                    @if(!is_null($savingPct)) ({{ $savingPct }}%) @endif
                                </div>
                            @endif
                        @else
                            <div class="item">{{ __('Formation') }} : <b>{{ $training?->title }}</b></div>
                            <div class="item">
                                {{ __('Prix') }} :
                                <b>
                                    @if((bool)($training?->is_free ?? false))
                                        {{ __('Gratuit') }}
                                    @elseif(!is_null($trainingPriceStr))
                                        {{ $trainingPriceStr }}
                                    @else
                                        {{ __('Prix sur demande') }}
                                    @endif
                                </b>
                            </div>
                        @endif
                    </div>

                    <div class="mt-3">
                        @if($isPack)
                            <div class="details-label">{{ __('Contenu du pack') }}</div>
                            <ul class="hint" style="margin:0; padding-left:18px;">
                                @foreach($pack->items as $it)
                                    <li>
                                        <b>{{ (int)($it->quantity ?? 1) }}×</b>
                                        {{ $it->product?->name ?? __('Prestation supprimée') }}
                                    </li>
                                @endforeach
                            </ul>

                            @if(!empty($pack->description))
                                <div class="mt-3 hint">{!! nl2br(e($pack->description)) !!}</div>
                            @endif
                        @else
                            <div class="details-label">{{ __('À propos de la formation') }}</div>
                            @if(!empty($training?->description))
                                <div class="hint">{!! nl2br(e($training->description)) !!}</div>
                            @else
                                <div class="hint">{{ __('Formation digitale proposée par le thérapeute.') }}</div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- CUSTOMER INFO --}}
                <div class="card-soft mt-4">
                    <div class="details-label">{{ __('Vos informations') }}</div>

                    <div class="mt-3">
                        <label class="details-label" for="first_name">{{ __('Prénom') }}</label>
                        <input class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                        @error('first_name')<p class="text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-3">
                        <label class="details-label" for="last_name">{{ __('Nom') }}</label>
                        <input class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                        @error('last_name')<p class="text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-3">
                        <label class="details-label" for="email">{{ __('Email') }}</label>
                        <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}" required>
                        <p class="hint mt-2">{{ __("Nous retrouverons/créerons automatiquement votre fiche client avec cet email.") }}</p>
                        @error('email')<p class="text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-3">
                        <label class="details-label" for="phone">{{ __('Téléphone') }}</label>
                        <input class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                        @error('phone')<p class="text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-3">
                        <label class="details-label" for="notes">{{ __('Notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')<p class="text-red-500">{{ $message }}</p>@enderror
                    </div>

                    @php
                        $canPayTraining = $isPack
                            ? true
                            : (!(bool)($training?->is_free ?? false) && !is_null($training?->price_cents));
                    @endphp

                    <div class="mt-4 d-flex justify-content-center gap-2" style="flex-wrap:wrap;">
                        <a href="{{ route('therapist.show', $therapist->slug) }}" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('Retour') }}
                        </a>

                        <button type="submit" class="btn-primary"
                                @if(!$canPayTraining) disabled style="opacity:.6; cursor:not-allowed;" @endif>
                            <i class="fas fa-lock"></i> {{ __('Procéder au paiement') }}
                        </button>
                    </div>

                    @if(!$isPack && !$canPayTraining)
                        <p class="hint mt-3 text-center">
                            {{ __("Impossible de payer cette formation en ligne (gratuite ou prix non défini).") }}
                        </p>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const select = document.getElementById('item');
            if (!select) return;

            const currentCheckoutUrl = @json($currentCheckoutUrl);
            const packBaseUrl = @json($packBaseUrl);

            select.addEventListener('change', function () {
                const val = this.value || '';
                const m = val.match(/^(pack|training):(\d+)$/);
                if (!m) return;

                const type = m[1];
                const id   = m[2];

                if (type === 'pack') {
                    window.location = packBaseUrl + '/' + id + '/checkout';
                } else {
                    // keep current pack URL, just switch selection via query
                    window.location = currentCheckoutUrl + '?item=' + encodeURIComponent(val);
                }
            });
        })();
    </script>
</x-app-layout>
