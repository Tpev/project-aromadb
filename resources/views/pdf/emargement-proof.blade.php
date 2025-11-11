// resources/views/emargements/pdf.blade.php
@php
  use Carbon\Carbon;
  Carbon::setLocale('fr');
  setlocale(LC_TIME, 'fr_FR.UTF-8'); // pour strftime si besoin
  $dtSigned = $em->signed_at ? Carbon::parse($em->signed_at)->timezone('Europe/Paris') : null;
  $dtAppt   = isset($meta['appointment']['date']) ? Carbon::parse($meta['appointment']['date']) : null;

  // Empreinte hash (simple & stable)
  $payload = json_encode([
      'emargement_id' => $em->id,
      'token_tail'    => substr($em->token, -8),
      'signed_at'     => optional($dtSigned)->toIso8601String(),
      'signer_ip'     => $em->signer_ip,
      'user_agent'    => $em->signer_user_agent,
  ], JSON_UNESCAPED_UNICODE);
  $sha256 = hash('sha256', $payload);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Feuille d’émargement #{{ $em->id }}</title>
<style>
  /* ---- PDF-safe CSS (Dompdf/KnP friendly) ---- */
  @page { margin: 28mm 18mm 24mm; }
  body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color:#222; font-size:12px; }
  h1 { font-size:20px; margin:0 0 10px; color:#647a0b; }
  h2 { font-size:14px; margin:18px 0 8px; color:#333; }
  .muted{ color:#666; }
  .small{ font-size:11px; }
  .wrap { }
  .header {
    border-bottom:1px solid #e5e5e5; padding-bottom:8px; margin-bottom:14px;
  }
  .brand { font-weight:800; color:#647a0b; }
  .grid-2 { width:100%; border-collapse:separate; border-spacing:0 6px; }
  .grid-2 td { vertical-align:top; width:50%; padding-right:10px; }
  .label { color:#647a0b; font-weight:700; display:block; margin-bottom:2px; }
  .box {
    border:1px solid #eaeaea; border-radius:8px; padding:10px 12px; margin-bottom:8px;
  }
  table.meta { width:100%; border-collapse:collapse; margin-top:6px; }
  table.meta th, table.meta td { text-align:left; border-bottom:1px solid #f0f0f0; padding:8px 6px; }
  table.meta th { width:28%; color:#647a0b; font-weight:700; }
  .signature-area {
    border:1px dashed #cfcfcf; border-radius:8px; padding:10px; min-height:120px; display:flex; align-items:center; justify-content:center; margin-top:8px;
  }
  .signature-img { max-width:320px; max-height:120px; }
  .footer {
    border-top:1px solid #e5e5e5; margin-top:14px; padding-top:8px; font-size:10px; color:#666;
  }
  .w50 { width:50%; }
</style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <h1>Feuille d’émargement <span class="muted">#{{ $em->id }}</span></h1>
      <div class="small muted">Document généré automatiquement – Preuve d’assiduité</div>
    </div>

    <table class="grid-2">
      <tr>
        <td class="w50">
          <div class="box">
            <span class="label">Thérapeute</span>
            <div><strong>{{ $meta['therapist']['name'] ?? 'N/A' }}</strong></div>
          </div>
          <div class="box">
            <span class="label">Client</span>
            <div>
              <strong>{{ ($meta['client']['first'] ?? '') . ' ' . ($meta['client']['last'] ?? '') }}</strong><br>
              <span class="small muted">{{ $meta['client']['email'] ?? '' }}</span>
            </div>
          </div>
        </td>
        <td class="w50">
          <div class="box">
            <span class="label">Prestation</span>
            <div><strong>{{ $meta['product']['name'] ?? 'Prestation' }}</strong></div>
            @if(($meta['product']['duration'] ?? null))
              <div class="small muted">Durée prévue : {{ $meta['product']['duration'] }} min</div>
            @endif
          </div>
          <div class="box">
            <span class="label">Rendez-vous</span>
            <table class="meta">
              <tr>
                <th>Date et heure</th>
                <td>
                  @if($dtAppt)
                    {{ $dtAppt->timezone('Europe/Paris')->isoFormat('dddd D MMMM YYYY, HH:mm') }}
                  @else
                    N/A
                  @endif
                </td>
              </tr>
              <tr>
                <th>Statut de la feuille</th>
                <td>{{ ucfirst($em->status) === 'Signed' ? 'Signé' : ucfirst($em->status) }}</td>
              </tr>
              <tr>
                <th>Signé le</th>
                <td>{{ $dtSigned ? $dtSigned->isoFormat('dddd D MMMM YYYY, HH:mm') : '—' }}</td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
    </table>

    <h2>Preuve de signature</h2>
    <div class="box">
      <table class="meta">
        <tr>
          <th>Adresse IP du signataire</th>
          <td>{{ $em->signer_ip ?? '—' }}</td>
        </tr>
        <tr>
          <th>Agent utilisateur (navigateur)</th>
          <td class="small">{{ $em->signer_user_agent ?? '—' }}</td>
        </tr>
        <tr>
          <th>Jeton (fin)</th>
          <td>{{ substr($em->token, -8) }}</td>
        </tr>
        <tr>
          <th>Empreinte SHA-256</th>
          <td class="small">{{ $sha256 }}</td>
        </tr>
      </table>

      <div class="signature-area" style="margin-top:10px;">
        @if($em->signature_image_path)
          <img class="signature-img" src="{{ public_path('storage/'.$em->signature_image_path) }}" alt="Signature">
        @else
          <span class="muted small">Signature par confirmation de présence (sans tracé manuscrit)</span>
        @endif
      </div>
    </div>

    <div class="footer">
      <div>Ce document est destiné à attester la présence du client au rendez-vous décrit ci-dessus.</div>
      <div class="small">Fuseau horaire : Europe/Paris — Tous les horaires sont indiqués en heure locale.</div>
    </div>
  </div>
</body>
</html>
