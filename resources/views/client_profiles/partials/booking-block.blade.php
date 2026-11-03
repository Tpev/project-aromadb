@php
    $bookingBlockHasEmail = trim($clientProfile->email ?? '') !== '';
    $bookingBlockRoute = request()->routeIs('mobile.*') ? 'mobile.clients.booking-block' : 'client_profiles.booking-block';
@endphp

<section class="client-booking-block" aria-labelledby="booking-block-title">
    <div class="client-booking-block-content">
        <div>
            <h2 id="booking-block-title">
                Réservation en ligne
                @if($bookingEmailBlocked)<span class="client-booking-block-status">· Bloquée</span>@endif
            </h2>
        </div>
        @if($bookingBlockHasEmail)
            <form method="POST" action="{{ route($bookingBlockRoute, $clientProfile) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="blocked" value="{{ $bookingEmailBlocked ? '0' : '1' }}">
                <button type="submit" class="client-booking-block-button {{ $bookingEmailBlocked ? 'is-blocked' : '' }}">
                    {{ $bookingEmailBlocked ? 'Débloquer ce client' : 'Bloquer ce client' }}
                </button>
            </form>
        @endif
    </div>
    @if($bookingBlockHasEmail)
        <p class="client-booking-block-help">Uniquement pour <strong>{{ $clientProfile->email }}</strong> sur votre agenda.</p>
    @else
        <p class="client-booking-block-help">Ajoutez une adresse email pour pouvoir bloquer les réservations.</p>
    @endif
    @if(session('booking_block_status'))
        <p role="status" class="client-booking-block-feedback">{{ session('booking_block_status') }}</p>
    @endif
    @error('booking_block')
        <p role="alert" class="client-booking-block-status">{{ $message }}</p>
    @enderror
</section>

@once
    <style>
        .client-booking-block { margin:12px 0 0;padding:12px 0 0;border-top:1px solid #e4e8d5;text-align:left; }
        .client-booking-block-content { display:flex;align-items:center;justify-content:space-between;gap:10px; }
        .client-booking-block-content > div { flex:1;min-width:0; }
        .client-booking-block-content form { flex:none;margin:0; }
        .client-booking-block h2 { margin:0;font-size:12px;font-weight:600;line-height:1.4;color:#374151; }
        .client-booking-block p { margin:5px 0 0;font-size:11px;line-height:1.5;color:#374151;overflow-wrap:anywhere; }
        .client-booking-block p.client-booking-block-help { color:#6b7280; }
        .client-booking-block .client-booking-block-status { color:#b91c1c;font-weight:600; }
        .client-booking-block p.client-booking-block-feedback { color:#526509; }
        .client-booking-block-button { padding:6px 10px;min-height:32px;border:1px solid #b91c1c;border-radius:6px;color:#b91c1c;background:white;font-size:12px;font-weight:600;cursor:pointer; }
        .client-booking-block-button.is-blocked { border-color:#647a0b;color:#647a0b; }
        .client-booking-block-button:focus-visible { outline:2px solid #374151;outline-offset:3px; }
    </style>
@endonce
