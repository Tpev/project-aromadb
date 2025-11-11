@php
    use Carbon\Carbon;
    Carbon::setLocale('fr');

    $clientName = trim(($client->first_name ?? '').' '.($client->last_name ?? ''));
    $therName   = $therapist->name ?? '—';
    $docTitle   = $doc->original_name ?? 'Document';
@endphp
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 28mm 18mm; }
  body { font-family: DejaVu Sans, sans-serif; color:#111; font-size:12px; }
  h1 { font-size:18px; margin:0 0 6px; color:#2f3b10; }
  h2 { font-size:14px; margin:18px 0 6px; color:#333; }
  .muted{ color:#555; }
  .grid{ width:100%; border-collapse:collapse; }
  .grid td{ padding:6px 8px; vertical-align:top; border-top:1px solid #eee; }
  .label{ width:34%; color:#3b4a0c; font-weight:700; }
  .sig{ margin-top:8px; }
  .sig img{ max-height:80px; }
  .small{ font-size:11px; color:#555; }
  .sep{ height:10px; }
</style>
</head>
<body>
  <h1>Annexe de signature</h1>
  <p class="muted">Ce document récapitule les éléments de preuve liés à la signature électronique du PDF ci-joint.</p>

  <h2>Document</h2>
  <table class="grid" width="100%">
    <tr><td class="label">Nom du fichier</td><td>{{ $docTitle }}</td></tr>
    <tr><td class="label">Identifiant</td><td>#{{ $doc->id }}</td></tr>
    <tr><td class="label">Empreinte SHA-256 (original)</td><td><span class="small">{{ $hashOriginal ?? '—' }}</span></td></tr>
    <tr><td class="label">Empreinte SHA-256 (final)</td><td><span class="small">{{ $hashFinal ?? '—' }}</span></td></tr>
    <tr><td class="label">Référence de lien</td><td>…{{ $tokenTail }}</td></tr>
  </table>

  <div class="sep"></div>

  <h2>Parties</h2>
  <table class="grid" width="100%">
    <tr><td class="label">Client</td><td>{{ $clientName }} — {{ $client->email ?? '—' }}</td></tr>
    <tr><td class="label">Praticien</td><td>{{ $therName }}</td></tr>
  </table>

  <div class="sep"></div>

  <h2>Événements de signature</h2>
  <table class="grid" width="100%">
    @forelse($events as $ev)
      <tr>
        <td class="label">
          Rôle
        </td>
        <td>
          {{ $ev->role === 'client' ? 'Client' : 'Praticien' }}<br>
          <span class="small">
            Signé le {{ optional($ev->signed_at)->translatedFormat('l j F Y \à H\hi') ?? '—' }}
          </span>
          <div class="small">
            IP : {{ $ev->signer_ip ?? '—' }}<br>
            Navigateur : {{ $ev->signer_user_agent ? \Illuminate\Support\Str::limit($ev->signer_user_agent,120) : '—' }}
          </div>
          @if($ev->signature_image_path && \Storage::disk('public')->exists($ev->signature_image_path))
            <div class="sig">
              <span class="small">Signature graphique :</span><br>
              <img src="{{ public_path('storage/'.$ev->signature_image_path) }}" alt="signature">
            </div>
          @endif
        </td>
      </tr>
    @empty
      <tr><td class="label">—</td><td>Aucun évènement enregistré.</td></tr>
    @endforelse
  </table>

  <p class="small" style="margin-top:18px;">
    Ce justificatif est généré automatiquement et conservé par le praticien dans le dossier du client.
  </p>
</body>
</html>
