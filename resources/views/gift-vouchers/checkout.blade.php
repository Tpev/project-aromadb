<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Offrir un bon cadeau') }}
        </h2>
    </x-slot>

    <style>
        :root { --brand:#647a0b; --brown:#854f38; }
        .am-wrap {
            max-width: 900px;
            margin: 2rem auto;
            background: #f9f9f9;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 8px 20px rgba(15,23,42,.08);
        }
        .am-title { font-size: 1.8rem; font-weight: 800; color: var(--brand); text-align: center; }
        .am-sub { text-align: center; color:#64748b; margin-top: .3rem; }
        .am-grid { display:grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 1rem; }
        .am-field label { display:block; font-weight: 700; color: var(--brand); margin-bottom: .35rem; }
        .am-input, .am-textarea {
            width: 100%;
            border: 1px solid rgba(133,79,56,.35);
            border-radius: 10px;
            padding: .65rem .8rem;
            background: white;
        }
        .am-textarea { min-height: 110px; }
        .am-btn {
            display:inline-flex; align-items:center; justify-content:center;
            width:100%;
            border:none; border-radius:12px; padding:.8rem 1rem;
            font-weight:800; color:white; background: var(--brand);
            margin-top: 1rem;
        }
        .am-btn:hover { background: var(--brown); }
        .am-error { color:#b91c1c; font-size:.85rem; margin-top:.25rem; }
        @media (max-width: 800px) { .am-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="am-wrap">
        <h1 class="am-title">{{ __('Bon cadeau') }} — {{ $therapist->company_name ?? $therapist->name }}</h1>
        <p class="am-sub">{{ __('Paiement sécurisé en ligne. Le bon est envoyé automatiquement par email en PDF.') }}</p>

        @if($errors->any())
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-900">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('gift-vouchers.checkout.store', ['slug' => $therapist->slug]) }}">
            @csrf

            <div class="am-grid">
                <div class="am-field">
                    <label for="amount_eur">{{ __('Montant (€)') }}</label>
                    <input id="amount_eur" class="am-input" type="number" step="0.01" min="5" max="5000" name="amount_eur" value="{{ old('amount_eur', '50') }}" required>
                    @error('amount_eur') <div class="am-error">{{ $message }}</div> @enderror
                </div>

                <div class="am-field">
                    <label for="expires_at">{{ __('Date d’expiration (optionnel)') }}</label>
                    <input id="expires_at" class="am-input" type="date" name="expires_at" value="{{ old('expires_at') }}">
                    @error('expires_at') <div class="am-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="am-grid">
                <div class="am-field">
                    <label for="buyer_name">{{ __('Votre nom') }}</label>
                    <input id="buyer_name" class="am-input" type="text" name="buyer_name" value="{{ old('buyer_name') }}" required>
                    @error('buyer_name') <div class="am-error">{{ $message }}</div> @enderror
                </div>

                <div class="am-field">
                    <label for="buyer_email">{{ __('Votre email') }}</label>
                    <input id="buyer_email" class="am-input" type="email" name="buyer_email" value="{{ old('buyer_email') }}" required>
                    @error('buyer_email') <div class="am-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="am-grid">
                <div class="am-field">
                    <label for="recipient_name">{{ __('Nom du bénéficiaire (optionnel)') }}</label>
                    <input id="recipient_name" class="am-input" type="text" name="recipient_name" value="{{ old('recipient_name') }}">
                    @error('recipient_name') <div class="am-error">{{ $message }}</div> @enderror
                </div>

                <div class="am-field">
                    <label for="recipient_email">{{ __('Email du bénéficiaire (optionnel)') }}</label>
                    <input id="recipient_email" class="am-input" type="email" name="recipient_email" value="{{ old('recipient_email') }}">
                    @error('recipient_email') <div class="am-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="am-field">
                <label for="buyer_phone">{{ __('Téléphone (optionnel)') }}</label>
                <input id="buyer_phone" class="am-input" type="text" name="buyer_phone" value="{{ old('buyer_phone') }}">
                @error('buyer_phone') <div class="am-error">{{ $message }}</div> @enderror
            </div>

            <div class="am-field mt-3">
                <label for="message">{{ __('Message personnalisé (optionnel)') }}</label>
                <textarea id="message" class="am-textarea" name="message">{{ old('message') }}</textarea>
                @error('message') <div class="am-error">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="am-btn">{{ __('Payer et envoyer le bon cadeau') }}</button>
        </form>
    </div>
</x-app-layout>

