@php
    $voucherService = app(\App\Services\AppointmentGiftVoucherService::class);
    $voucherLabel = $appointment->gift_voucher_payment_label;
    $voucherUses = $voucherService->applied($appointment);
    $voucherUnavailable = $voucherService->unavailableReason($appointment);
    $voucherPreview = session('appointment_voucher_preview');
    if (($voucherPreview['appointment_id'] ?? null) !== $appointment->id) {
        $voucherPreview = null;
    }
    $canUseVoucher = auth()->user()->canUseFeature('gift_vouchers');
@endphp

@if(!$appointment->external && ($canUseVoucher || $voucherLabel))
<section class="appointment-voucher" aria-label="Règlement par bon cadeau">
    <div class="appointment-voucher-heading"><i class="fas fa-gift" aria-hidden="true"></i> Bon cadeau Olithea</div>
    @if(session('success'))
        <p class="appointment-voucher-success" role="status">{{ session('success') }}</p>
    @endif
    @foreach(['gift_voucher_code', 'amount_cents', 'total_cents', 'appointment_id'] as $voucherErrorField)
        @error($voucherErrorField)<p class="appointment-voucher-error" role="alert">{{ $message }}</p>@enderror
    @endforeach

    @if($voucherLabel)
        <p class="appointment-voucher-label">{{ $voucherLabel }}</p>
        @foreach($voucherUses->groupBy('gift_voucher_id') as $uses)
            <p class="appointment-voucher-help">
                {{ $uses->first()->voucher?->code ?? 'Bon cadeau' }}
                · {{ number_format($uses->sum('amount_cents') / 100, 2, ',', ' ') }} € utilisés
            </p>
        @endforeach
        @if($voucherUses->isNotEmpty())
            <p class="appointment-voucher-help">Utilisation déjà enregistrée : aucune nouvelle déduction du bon cadeau.</p>
        @endif
    @endif

    @if($canUseVoucher && !$voucherUnavailable)
        <details @if($voucherPreview || $errors->has('gift_voucher_code')) open @endif>
            <summary class="appointment-voucher-action">Régler par bon cadeau</summary>
            <p class="appointment-voucher-help">Saisissez le code du bon cadeau Olithea de votre cabinet. Le montant sera vérifié avant confirmation.</p>
            @if($appointment->billingInvoices->isEmpty())
                <p class="appointment-voucher-help">Le montant à régler est calculé à partir du tarif actuel de la prestation, TTC. Pour un tarif différent, créez d’abord la facture.</p>
            @endif
            <form method="POST" action="{{ route('appointments.gift-voucher.preview', $appointment) }}" class="appointment-voucher-form">
                @csrf
                <div class="appointment-voucher-field">
                    <label for="appointment-voucher-code">Code du bon cadeau</label>
                    <input id="appointment-voucher-code" name="gift_voucher_code" value="{{ old('gift_voucher_code') }}" maxlength="64" required autocomplete="off" placeholder="Code figurant sur le bon cadeau">
                </div>
                <button type="submit" class="appointment-voucher-button secondary">Vérifier le bon</button>
            </form>

            @if($voucherPreview)
                <div class="appointment-voucher-preview" aria-live="polite">
                    <strong>{{ $voucherPreview['code'] }}</strong>
                    <dl>
                        <div><dt>Montant déduit du bon</dt><dd>{{ number_format($voucherPreview['amount_cents'] / 100, 2, ',', ' ') }} €</dd></div>
                        <div><dt>Reste à régler pour ce rendez-vous</dt><dd>{{ number_format($voucherPreview['remaining_cents'] / 100, 2, ',', ' ') }} €</dd></div>
                        <div><dt>Solde du bon après utilisation</dt><dd>{{ number_format($voucherPreview['balance_after_cents'] / 100, 2, ',', ' ') }} €</dd></div>
                    </dl>
                    <form method="POST" action="{{ route('appointments.gift-voucher.store', $appointment) }}">
                        @csrf
                        <input type="hidden" name="gift_voucher_code" value="{{ $voucherPreview['code'] }}">
                        <input type="hidden" name="amount_cents" value="{{ $voucherPreview['amount_cents'] }}">
                        <input type="hidden" name="total_cents" value="{{ $voucherPreview['total_cents'] }}">
                        <button type="submit" class="appointment-voucher-button">Confirmer l’utilisation de {{ number_format($voucherPreview['amount_cents'] / 100, 2, ',', ' ') }} €</button>
                    </form>
                </div>
            @endif
        </details>
    @elseif(!$voucherLabel && $voucherUnavailable && !$appointment->isCancelled())
        <p class="appointment-voucher-help">{{ $voucherUnavailable }}</p>
    @endif
</section>
@endif

@once
<style>
    .appointment-voucher { margin: 1.25rem 0; padding: 1rem 1.15rem; border: 1px solid #dfe5ce; border-radius: 12px; background: #fafbf7; color: #37412c; font-size: 14px; }
    .appointment-voucher-heading { display: flex; gap: .5rem; align-items: center; font-weight: 600; margin-bottom: .6rem; color: #52640b; }
    .appointment-voucher-label { font-weight: 600; margin: .5rem 0; }
    .appointment-voucher-help { font-size: 12px; color: #626956; margin: .5rem 0; line-height: 1.6; }
    .appointment-voucher-action { cursor: pointer; color: #52640b; font-weight: 600; padding: .35rem 0; }
    .appointment-voucher-form { display: flex; align-items: end; flex-wrap: wrap; gap: .6rem; margin-top: .8rem; }
    .appointment-voucher-field { flex: 1 1 220px; max-width: 380px; }
    .appointment-voucher-field label { display: block; font-size: 12px; margin-bottom: .3rem; }
    .appointment-voucher-field input { display: block; width: 100%; min-width: 0; border: 1px solid #cbd3b9; border-radius: 7px; background: white; padding: .6rem .7rem; font-size: 14px; }
    .appointment-voucher-button { border: 1px solid #647a0b; background: #647a0b; color: white; border-radius: 7px; padding: .6rem .85rem; font-size: 13px; font-weight: 600; cursor: pointer; }
    .appointment-voucher-button.secondary { background: white; color: #52640b; }
    .appointment-voucher-preview { max-width: 640px; margin-top: 1rem; padding: 1rem; background: white; border: 1px solid #e0e5d4; border-radius: 8px; }
    .appointment-voucher-preview dl { margin: .65rem 0 1rem; font-size: 13px; }
    .appointment-voucher-preview dl div { display: flex; justify-content: space-between; flex-wrap: wrap; gap: .5rem; padding: .3rem 0; }
    .appointment-voucher-preview dt { font-weight: 400; }
    .appointment-voucher-preview dd { margin: 0; font-weight: 600; white-space: nowrap; }
    .appointment-voucher-error { color: #a12b29; margin: .6rem 0; }
    .appointment-voucher-success { color: #426014; margin: .6rem 0; }
</style>
@endonce
