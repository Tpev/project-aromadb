<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Acheter un pack') }}
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

            <h1 class="details-title">{{ $pack->name }}</h1>
            <div class="subtle">
                {{ $therapist->company_name ?? $therapist->name }} • {{ __('Paiement sécurisé') }}
            </div>

            @if ($errors->any())
                <div class="alert alert-danger text-center">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="card-soft">
                <div class="summary-row">
                    <div class="item">{{ __('Prix du pack') }} : <b>{{ number_format($packPriceTtc, 2, ',', ' ') }} €</b></div>

                    @if($unitTotalTtc > 0 && $saving > 0.01)
                        <div class="item">
                            {{ __('À l’unité') }} :
                            <b style="text-decoration:line-through; opacity:.85;">
                                {{ number_format($unitTotalTtc, 2, ',', ' ') }} €
                            </b>
                        </div>
                        <div class="item">
                            {{ __('Économie') }} : <b>{{ number_format($saving, 2, ',', ' ') }} €</b>
                            @if(!is_null($savingPct)) ({{ $savingPct }}%) @endif
                        </div>
                    @endif
                </div>

                <div class="mt-3">
                    <div class="details-label">{{ __('Contenu du pack') }}</div>
                    <ul class="hint" style="margin:0; padding-left:18px;">
                        @foreach($pack->items as $it)
                            <li><b>{{ (int)($it->quantity ?? 1) }}×</b> {{ $it->product?->name ?? 'Prestation supprimée' }}</li>
                        @endforeach
                    </ul>
                </div>

                @if(!empty($pack->description))
                    <div class="mt-3 hint">{!! nl2br(e($pack->description)) !!}</div>
                @endif
            </div>

            <form class="mt-4" method="POST" action="{{ route('packs.checkout.store', ['slug'=>$therapist->slug, 'pack'=>$pack->id]) }}">
                @csrf

                <div class="card-soft">
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

                    <div class="mt-4 d-flex justify-content-center gap-2" style="flex-wrap:wrap;">
                        <a href="{{ route('therapist.show', $therapist->slug) }}" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('Retour') }}
                        </a>

                        <button type="submit" class="btn-primary">
                            <i class="fas fa-lock"></i> {{ __('Procéder au paiement') }}
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
