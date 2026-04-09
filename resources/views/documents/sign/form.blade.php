<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Signature du document</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{ --brand:#647a0b; --ink:#222; --muted:#666; --bg:#f8f9fb; }
    *{ box-sizing:border-box; }
    body{ font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; color:var(--ink); margin:0; background:var(--bg); }
    .wrap{ max-width:1000px; margin:0 auto; padding:1rem; }
    header{ display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
    h1{ font-size:1.35rem; font-weight:800; color:var(--brand); margin:1rem 0 .25rem; }
    .muted{ color:var(--muted); }
    .card{ background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 8px 24px rgba(0,0,0,.06); }
    .viewer{ height:70vh; overflow:hidden; }
    .viewer iframe{ width:100%; height:100%; border:0; border-radius:12px; }
    .section{ padding:1rem; }
    .bar{ display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }
    .btn{ background:var(--brand); color:#fff; padding:.6rem 1rem; border-radius:10px; text-decoration:none; border:0; cursor:pointer; font-weight:700; }
    .btn.alt{ background:transparent; color:var(--brand); border:1px solid var(--brand); }
    .btn:disabled{ opacity:.6; cursor:not-allowed; }
    .row{ display:flex; gap:1rem; align-items:center; flex-wrap:wrap; margin:.5rem 0; }
    label{ font-weight:600; }
    .sig-pad{ border:1px dashed #bbb; height:180px; border-radius:10px; display:none; background:#fff; }
    .choice-row label{ font-weight:700; }
    .choice-help{ display:block; font-size:.9rem; font-weight:500; color:#5b6472; margin-top:.15rem; margin-left:1.4rem; }
    .confirm-panel{
      margin-top:.75rem;
      padding:.85rem 1rem;
      border-radius:12px;
      border:1px solid #dbe4c0;
      background:#f7fbef;
    }
    .confirm-panel label{ display:flex; align-items:flex-start; gap:.6rem; font-weight:700; line-height:1.45; }
    .confirm-panel input[type="checkbox"]{ margin-top:.2rem; }
    .helper-text{ color:#5f6b7a; font-size:.9rem; margin-top:.35rem; }
    .alert{ padding:.75rem 1rem; border-radius:10px; margin:.75rem 0; }
    .alert.error{ background:#fde8e8; color:#7f1d1d; border:1px solid #fecaca; }
    .alert.info{ background:#eef2ff; color:#1e3a8a; border:1px solid #c7d2fe; }
    .meta{ font-size:.9rem; color:#444; line-height:1.45; }
    .kv{ display:grid; grid-template-columns: 170px 1fr; gap:.35rem .75rem; }
    .kv div:nth-child(odd){ color:#555; }
    .kv div:nth-child(even){ font-weight:600; }
    @media (max-width:640px){
      .kv{ grid-template-columns:1fr; }
      .viewer{ height:60vh; }
    }
  </style>
</head>
<body>
@php
    /** @var \App\Models\Document $doc */
    /** @var \App\Models\DocumentSigning $signing */
    /** @var string $role */
    use Illuminate\Support\Facades\Storage;

    $pdfUrl = Storage::disk('public')->url($doc->storage_path);
    $isTherapist = ($role ?? 'client') === 'therapist';
@endphp

<div class="wrap">
  <header>
    <div>
      <h1>{{ $isTherapist ? 'Contre-signature du praticien' : 'Signature du document (client)' }}</h1>
      <p class="muted">
        Client&nbsp;: <strong>{{ $doc->clientProfile?->first_name }} {{ $doc->clientProfile?->last_name }}</strong>
        &nbsp;—&nbsp;Praticien&nbsp;: <strong>{{ $doc->owner?->name }}</strong>
      </p>
      <p class="muted">Document&nbsp;: <strong>{{ $doc->original_name }}</strong></p>
    </div>
  </header>

  <div class="card section">
    <div class="viewer">
      <iframe src="{{ $pdfUrl }}" title="PDF à signer"></iframe>
    </div>

    @if(session('error'))
      <div class="alert error">{{ session('error') }}</div>
    @endif

    <div class="alert info" role="status">
      {{ $isTherapist
          ? 'Étape praticien : relisez le document, confirmez votre acceptation, puis choisissez votre mode de signature.'
          : 'Étape client : relisez le document, confirmez votre acceptation, puis choisissez votre mode de signature.' }}
    </div>

    <form method="POST" action="{{ route('documents.sign.submit', $signing->token) }}" class="section" id="signForm">
      @csrf

      @if($errors->any())
        <div class="alert error">
          <ul style="margin:0; padding-left:1.1rem;">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="row choice-row">
        <label>
          <input type="radio" name="mode" value="checkbox" {{ old('mode', 'checkbox') !== 'canvas' ? 'checked' : '' }}>
          {{ $isTherapist
              ? 'Signer sans dessin'
              : 'Signer sans dessin' }}
          <span class="choice-help">
            {{ $isTherapist
                ? 'Validez simplement votre accord, sans tracer de signature manuscrite.'
                : 'Validez simplement votre accord, sans tracer de signature manuscrite.' }}
          </span>
        </label>
        <label>
          <input type="radio" name="mode" value="canvas" id="modeCanvas" {{ old('mode') === 'canvas' ? 'checked' : '' }}>
          Dessiner ma signature
          <span class="choice-help">Tracez votre signature directement dans la zone prévue.</span>
        </label>
      </div>

      <div id="confirmBox" class="confirm-panel" aria-live="polite">
        <label>
          <input type="checkbox" name="confirmed" value="1" {{ old('confirmed') ? 'checked' : '' }}>
          {{ $isTherapist ? 'J’atteste avoir lu ce document et je confirme ma contre-signature.' : 'J’atteste avoir lu et accepté ce document avant de le signer.' }}
        </label>
        <div class="helper-text">
          Cette confirmation reste obligatoire, que vous choisissiez une signature simple ou un dessin manuscrit.
        </div>
      </div>

      <div id="canvasWrap" class="sig-pad" aria-hidden="true">
        <canvas id="sig" width="940" height="180"></canvas>
      </div>

      <input type="hidden" name="signature_data" id="signature_data" />

      <div class="bar" style="margin-top:.75rem;">
        <button class="btn" type="submit" id="submitBtn">{{ $isTherapist ? 'Contre-signer le document' : 'Signer le document' }}</button>
        <button class="btn alt" type="button" id="clearSig" style="display:none;">Effacer le dessin</button>
      </div>
    </form>

    <div class="section meta">
      <div class="kv">
        <div>Rôle en cours</div>
        <div>{{ $isTherapist ? 'Praticien' : 'Client' }}</div>

        <div>Statut</div>
        <div>{{ ucfirst(str_replace('_', ' ', $signing->status)) }}</div>

        <div>Expire le</div>
        <div>{{ optional($signing->expires_at)?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}</div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const modeRadios = document.querySelectorAll('input[name="mode"]');
  const modeCanvas = document.getElementById('modeCanvas');
  const sigWrap    = document.getElementById('canvasWrap');
  const confirmBox = document.getElementById('confirmBox');
  const sigCanvas  = document.getElementById('sig');
  const ctx        = sigCanvas.getContext('2d');
  const clearBtn   = document.getElementById('clearSig');
  const sigData    = document.getElementById('signature_data');
  const submitBtn  = document.getElementById('submitBtn');

  // nicer pen style
  ctx.lineWidth   = 2;
  ctx.lineCap     = 'round';
  ctx.lineJoin    = 'round';
  ctx.strokeStyle = '#111';

  let drawing = false;
  let anyStroke = false;

  function showCanvas(on){
    sigWrap.style.display = on ? 'block' : 'none';
    sigWrap.setAttribute('aria-hidden', on ? 'false':'true');
    clearBtn.style.display = on ? 'inline-block' : 'none';
    if(!on){
      // clear canvas data if switching back
      ctx.clearRect(0,0,sigCanvas.width, sigCanvas.height);
      sigData.value = '';
      anyStroke = false;
    }
  }

  modeRadios.forEach(r => {
    r.addEventListener('change', e => showCanvas(e.target.value === 'canvas'));
  });

  showCanvas(document.querySelector('input[name="mode"]:checked')?.value === 'canvas');

  // Pointer & mouse support for signature
  function getPos(evt){
    const rect = sigCanvas.getBoundingClientRect();
    const x = (evt.touches ? evt.touches[0].clientX : evt.clientX) - rect.left;
    const y = (evt.touches ? evt.touches[0].clientY : evt.clientY) - rect.top;
    return { x: x * (sigCanvas.width/rect.width), y: y * (sigCanvas.height/rect.height) };
  }

  function startDraw(evt){
    drawing = true;
    anyStroke = true;
    const p = getPos(evt);
    ctx.beginPath(); ctx.moveTo(p.x, p.y);
  }
  function moveDraw(evt){
    if(!drawing) return;
    const p = getPos(evt);
    ctx.lineTo(p.x, p.y); ctx.stroke();
  }
  function endDraw(){
    drawing = false;
    if (anyStroke) {
      sigData.value = sigCanvas.toDataURL('image/png');
    }
  }

  sigCanvas.addEventListener('mousedown', startDraw);
  sigCanvas.addEventListener('mousemove', moveDraw);
  window.addEventListener('mouseup', endDraw);

  sigCanvas.addEventListener('touchstart', (e)=>{ e.preventDefault(); startDraw(e); }, {passive:false});
  sigCanvas.addEventListener('touchmove',  (e)=>{ e.preventDefault(); moveDraw(e); }, {passive:false});
  sigCanvas.addEventListener('touchend',   (e)=>{ e.preventDefault(); endDraw(); },   {passive:false});

  clearBtn.addEventListener('click',()=>{
    ctx.clearRect(0,0,sigCanvas.width,sigCanvas.height);
    sigData.value='';
    anyStroke=false;
  });

  // Basic client-side guard: if mode=canvas, require a drawn signature
  document.getElementById('signForm').addEventListener('submit', function(e){
    const mode = document.querySelector('input[name="mode"]:checked')?.value || 'checkbox';
    if(mode === 'canvas' && !sigData.value){
      e.preventDefault();
      alert('Merci de dessiner votre signature avant de valider, ou choisissez l’option de signature sans dessin.');
      return false;
    }
    submitBtn.disabled = true;
  });
})();
</script>
</body>
</html>
