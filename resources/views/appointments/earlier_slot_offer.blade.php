<x-app-layout>
    @section('title', 'Créneau plus tôt | Olithea')
    @section('meta_description', 'Consultez et confirmez votre proposition privée de rendez-vous plus tôt.')
    @section('meta_og')
        <meta name="robots" content="noindex,nofollow">
    @endsection

    @php
        $appointment = $offer->appointment;
        $opportunity = $offer->opportunity;
        $location = $appointment?->getResolvedLocationString();
    @endphp

    <style>
        .earlier-page { min-height:72vh; padding:36px 16px 64px; background:#f5f6f1; }
        .earlier-shell { width:100%; max-width:760px; margin:0 auto; }
        .earlier-card { overflow:hidden; border:1px solid #dfe5c9; border-radius:8px; background:#fff; box-shadow:0 12px 30px rgba(37,49,25,.07); }
        .earlier-header { padding:28px 30px 22px; border-bottom:1px solid #e7ebdc; }
        .earlier-header .eyebrow { margin:0 0 7px; color:#647a0b; font-size:12px; font-weight:800; text-transform:uppercase; }
        .earlier-header h1 { margin:0; color:#26351f; font-size:28px; font-weight:750; }
        .earlier-header p { margin:9px 0 0; color:#687064; line-height:1.55; }
        .earlier-content { padding:26px 30px 30px; }
        .earlier-comparison { display:grid; grid-template-columns:1fr 1fr; border:1px solid #e3e7da; border-radius:8px; overflow:hidden; }
        .earlier-slot { min-width:0; padding:20px; background:#fafbf7; }
        .earlier-slot + .earlier-slot { border-left:1px solid #e3e7da; background:#f3f7e7; }
        .earlier-slot span { color:#737b6d; font-size:12px; font-weight:750; text-transform:uppercase; }
        .earlier-slot strong { display:block; margin-top:7px; color:#26351f; font-size:19px; }
        .earlier-details { display:grid; grid-template-columns:1fr 1fr; gap:17px 24px; margin:24px 0 0; }
        .earlier-details dt { color:#777f72; font-size:12px; font-weight:750; text-transform:uppercase; }
        .earlier-details dd { margin:4px 0 0; color:#26351f; overflow-wrap:anywhere; }
        .earlier-note { margin:22px 0 0; padding:14px 16px; border-left:3px solid #647a0b; background:#f8faef; color:#56604f; font-size:14px; line-height:1.55; }
        .earlier-action { width:100%; min-height:48px; margin-top:24px; border:0; border-radius:6px; background:#647a0b; color:#fff; font-weight:750; cursor:pointer; }
        .earlier-action:hover { background:#566a09; }
        .earlier-alert { margin-bottom:16px; padding:13px 15px; border-radius:6px; }
        .earlier-alert.success { background:#eaf4df; color:#365b24; }
        .earlier-alert.error { background:#fbe9e5; color:#843d2e; }
        .earlier-state { padding:8px 0 2px; text-align:center; }
        .earlier-state i { color:#647a0b; font-size:34px; }
        .earlier-state h2 { margin:15px 0 7px; color:#26351f; font-size:24px; }
        .earlier-state p { margin:0 auto; max-width:560px; color:#687064; line-height:1.6; }
        .earlier-link { display:inline-flex; min-height:44px; align-items:center; justify-content:center; margin-top:22px; border:1px solid #647a0b; border-radius:6px; padding:9px 15px; color:#526508; font-weight:700; text-decoration:none; }
        @media(max-width:640px) {
            .earlier-page { padding:18px 12px 44px; }
            .earlier-header, .earlier-content { padding:22px 18px; }
            .earlier-header h1 { font-size:23px; }
            .earlier-comparison, .earlier-details { grid-template-columns:1fr; }
            .earlier-slot + .earlier-slot { border-top:1px solid #e3e7da; border-left:0; }
        }
    </style>

    <main class="earlier-page">
        <div class="earlier-shell">
            @if(session('success'))<div class="earlier-alert success" role="status">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="earlier-alert error" role="alert">{{ session('error') }}</div>@endif

            <article class="earlier-card">
                <header class="earlier-header">
                    <p class="eyebrow">Votre rendez-vous</p>
                    <h1>{{ $state === 'available' ? 'Un créneau plus tôt est disponible' : ($state === 'claimed' ? 'Votre rendez-vous a bien été avancé' : ($state === 'taken' ? 'Ce créneau a déjà été réservé' : 'Ce créneau n’est plus disponible')) }}</h1>
                    <p>{{ $appointment?->user?->company_name ?: $appointment?->user?->name }}</p>
                </header>

                <div class="earlier-content">
                    @if($state === 'available')
                        <div class="earlier-comparison">
                            <div class="earlier-slot">
                                <span>Rendez-vous actuel</span>
                                <strong>{{ $appointment->appointment_date?->format('d/m/Y à H:i') }}</strong>
                            </div>
                            <div class="earlier-slot">
                                <span>Créneau proposé</span>
                                <strong>{{ $opportunity->slot_start?->format('d/m/Y à H:i') }}</strong>
                            </div>
                        </div>

                        <dl class="earlier-details">
                            <div><dt>Prestation</dt><dd>{{ $appointment->product?->name ?? 'Rendez-vous' }}</dd></div>
                            <div><dt>Durée</dt><dd>{{ \App\Support\EventDuration::format((int) $appointment->duration) }}</dd></div>
                            <div><dt>Mode</dt><dd>{{ ['cabinet' => 'Au cabinet', 'visio' => 'En visio', 'domicile' => 'À domicile', 'entreprise' => 'En entreprise'][$opportunity->mode] ?? $appointment->getResolvedModeLabel() }}</dd></div>
                            @if($opportunity->mode !== 'visio' && $location)
                                <div><dt>Lieu</dt><dd>{{ $location }}</dd></div>
                            @endif
                        </dl>

                        <p class="earlier-note">
                            Votre rendez-vous actuel reste réservé jusqu’à la confirmation. Ce créneau est proposé à plusieurs personnes et sera attribué à la première personne qui le confirme. Aucun nouveau paiement ne vous sera demandé.
                        </p>

                        <form method="POST" action="{{ route('appointments.earlier-slot.claim', $offer->token) }}">
                            @csrf
                            <input type="hidden" name="confirmation" value="1">
                            <button class="earlier-action" type="submit">Choisir ce nouveau créneau</button>
                        </form>
                    @elseif($state === 'claimed')
                        <div class="earlier-state">
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                            <h2>Le nouveau créneau est confirmé</h2>
                            <p>Votre rendez-vous est maintenant prévu le <strong>{{ $appointment->appointment_date?->format('d/m/Y à H:i') }}</strong>. Vos informations et votre paiement éventuel ont été conservés.</p>
                            <a class="earlier-link" href="{{ route('appointments.showPatient', $appointment->token) }}">Voir mon rendez-vous</a>
                        </div>
                    @elseif($state === 'taken')
                        <div class="earlier-state">
                            <i class="fas fa-user-check" aria-hidden="true"></i>
                            <h2>Une autre personne a confirmé ce créneau</h2>
                            <p>Ce créneau a été attribué avant votre confirmation. Cette proposition n’a apporté aucun changement à votre rendez-vous.</p>
                            @if($appointment)
                                <a class="earlier-link" href="{{ route('appointments.showPatient', $appointment->token) }}">Voir l’état de mon rendez-vous</a>
                            @endif
                        </div>
                    @else
                        <div class="earlier-state">
                            <i class="fas fa-clock" aria-hidden="true"></i>
                            <h2>Cette proposition n’est plus disponible</h2>
                            <p>Le créneau a été réservé, a expiré ou n’est plus compatible avec votre rendez-vous. Aucun changement n’a été effectué depuis ce lien.</p>
                            @if($appointment)
                                <a class="earlier-link" href="{{ route('appointments.showPatient', $appointment->token) }}">Voir l’état de mon rendez-vous</a>
                            @endif
                        </div>
                    @endif
                </div>
            </article>
        </div>
    </main>
</x-app-layout>
