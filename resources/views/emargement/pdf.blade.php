{{-- resources/views/emargement/pdf.blade.php --}}
@php
    use Carbon\Carbon;
    Carbon::setLocale('fr');

    $clientNom    = trim(($client->first_name ?? '').' '.($client->last_name ?? ''));
    // libellé “Praticien” uniquement, variable inchangée
    $praticienNom = $therapist->name ?? '—';
    $prodNom      = $product->name ?? '—';
    $dureeMin     = $product->duration ?? $appointment->duration ?? null;
    $apptDate     = isset($apptAt)   && $apptAt   ? $apptAt->translatedFormat('l j F Y \à H\hi')   : '—';
    $signedDate   = isset($signedAt) && $signedAt ? $signedAt->translatedFormat('l j F Y \à H\hi') : '—';
    $ua           = $em->signer_user_agent ?? '—';
    $ip           = $em->signer_ip ?? '—';
    $tokenTail    = $tokenTail ?? '—';
    $hashTail     = $hashTail ?? '—';
@endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Preuve d’émargement #{{ $em->id }}</title>
    <style>
        @page { margin: 28mm 22mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color:#1f2937; font-size:12pt; }
        h1,h2,h3 { margin:0 0 6mm 0; color:#0f5132; }
        .header { margin-bottom:10mm; border-bottom:2px solid #e5e7eb; padding-bottom:6mm; }
        .muted  { color:#6b7280; }
        .grid   { display:block; width:100%; }
        .row    { display:flex; gap:12mm; margin-bottom:6mm; }
        .col    { flex:1 1 0; }
        .box    { border:1px solid #e5e7eb; border-radius:8px; padding:6mm 7mm; margin-bottom:6mm; }
        .kv     { margin:2mm 0; line-height:1.35; }
        .k      { display:inline-block; min-width:56mm; color:#374151; }
        .v      { font-weight:600; }
        .footer { margin-top:12mm; padding-top:6mm; border-top:1px dashed #d1d5db; font-size:10pt; color:#6b7280; }
        .badge  { display:inline-block; padding:2mm 4mm; border-radius:999px; font-size:10pt; border:1px solid #c7eed8; background:#d1fae5; color:#065f46; }
        .title  { font-size:22pt; font-weight:800; letter-spacing:0.2px; }
        .subtitle { font-size:12pt; }
        .note  { background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; border-radius:8px; padding:6mm 7mm; margin-bottom:6mm; }
        .sig-img { max-height:60mm; max-width:100%; border:1px solid #e5e7eb; border-radius:6px; margin-top:4mm; }
        .h-sep { height:1px; background:#e5e7eb; margin:5mm 0; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Preuve d’émargement</div>
        <div class="subtitle muted">Document généré automatiquement par AromaMade PRO</div>
    </div>

    <div class="note">
        @if($em->status === 'signed')
            <strong>Ce document a été signé par le client.</strong>
            <div class="muted">Date de signature : {{ $signedDate }} — IP : {{ $ip }} — Navigateur : {{ $ua }}</div>
        @elseif($em->status === 'pending')
            <strong>Signature en attente.</strong>
            <div class="muted">Le client n’a pas encore signé ce document.</div>
        @elseif($em->status === 'expired')
            <strong>Le lien de signature a expiré.</strong>
            <div class="muted">Veuillez renvoyer une nouvelle demande si nécessaire.</div>
        @endif
    </div>

    <div class="grid">
        <div class="row">
            <div class="col">
                <div class="box">
                    <h3>Informations du rendez-vous</h3>
                    <div class="kv"><span class="k">Prestation :</span> <span class="v">{{ $prodNom }}</span></div>
                    <div class="kv"><span class="k">Date & heure :</span> <span class="v">{{ $apptDate }}</span></div>
                    <div class="kv"><span class="k">Durée :</span> <span class="v">{{ $dureeMin ? $dureeMin.' min' : '—' }}</span></div>
                    <div class="kv"><span class="k">Identifiant RDV :</span> <span class="v">#{{ $appointment->id ?? '—' }}</span></div>
                </div>
            </div>
            <div class="col">
                <div class="box">
                    <h3>Praticien</h3>
                    <div class="kv"><span class="k">Nom :</span> <span class="v">{{ $praticienNom }}</span></div>
                    <div class="kv"><span class="k">Identifiant :</span> <span class="v">#{{ $therapist->id ?? '—' }}</span></div>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top:2mm;">
            <div class="col">
                <div class="box">
                    <h3>Client</h3>
                    <div class="kv"><span class="k">Nom :</span> <span class="v">{{ $clientNom ?: '—' }}</span></div>
                    <div class="kv"><span class="k">Email :</span> <span class="v">{{ $client->email ?? '—' }}</span></div>
                    <div class="kv"><span class="k">Identifiant :</span> <span class="v">#{{ $client->id ?? '—' }}</span></div>
                </div>
            </div>
            <div class="col">
                <div class="box">
                    <h3>Statut de la feuille</h3>
                    <div class="kv">
                        <span class="k">État :</span>
                        <span class="v">
                            @if($em->status === 'signed')
                                <span class="badge">Signé</span>
                            @elseif($em->status === 'pending')
                                En attente
                            @elseif($em->status === 'expired')
                                Expiré
                            @else
                                —
                            @endif
                        </span>
                    </div>
                    <div class="kv"><span class="k">Signé le :</span> <span class="v">{{ $signedDate }}</span></div>
                    <div class="kv"><span class="k">IP du signataire :</span> <span class="v">{{ $ip }}</span></div>
                    <div class="kv"><span class="k">Navigateur (User-Agent) :</span> <span class="v">{{ $ua }}</span></div>
                </div>
            </div>
        </div>

        @if(!empty($signatureB64))
            <div class="box">
                <h3>Signature manuscrite du client</h3>
                <img src="{{ $signatureB64 }}" alt="Signature du client" class="sig-img">
            </div>
        @endif

        <div class="box">
            <h3>Références & vérifiabilité</h3>
            <div class="kv"><span class="k">Référence document :</span> <span class="v">EMG-{{ $em->id }}</span></div>
            <div class="kv"><span class="k">Empreinte (hash, extrait) :</span> <span class="v">{{ $hashTail }}</span></div>
            <div class="kv"><span class="k">Jeton (fin) :</span> <span class="v">{{ $tokenTail }}</span></div>
            <div class="kv">
                <span class="k">Expiration du lien :</span>
                <span class="v">
                    {{ $em->expires_at ? \Carbon\Carbon::parse($em->expires_at)->translatedFormat('l j F Y \à H\hi') : '—' }}
                </span>
            </div>
        </div>

        <div class="footer">
            Ce document atteste de la présence du client au rendez-vous indiqué ci-dessus.
            Il a été généré automatiquement par le système AromaMade PRO à partir des informations
            enregistrées au moment de la signature.
        </div>
    </div>
</body>
</html>
