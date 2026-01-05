<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl" style="color:#647a0b;">Ajouter une écriture</h2>
  </x-slot>

  @php
    $PAYMENT_METHOD_FR = [
      'transfer' => 'Virement',
      'card'     => 'Carte',
      'check'    => 'Chèque',
      'cash'     => 'Espèces',
      'other'    => 'Autre',
    ];

    // ✅ match ton enum DB: service|goods|other
    $NATURE_FR = [
      'service' => 'Service / Prestation',
      'goods'   => 'Vente de biens',
      'other'   => 'Autre',
    ];
  @endphp

  <div class="container-fluid mt-5 details-container mx-auto p-4" style="max-width: 900px;">

    <div class="mb-3">
      <h1 class="details-title">Ajouter une écriture manuelle</h1>
      <p class="text-muted">
        Cette écriture sera <strong>verrouillée</strong> après création. Pour corriger, utilisez une contre-passation.
      </p>
    </div>

    @if($errors->any())
      <div class="alert-error">
        <strong>Erreur :</strong>
        <ul style="margin:8px 0 0 18px;">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('receipts.store') }}" class="card-form">
      @csrf

      <div class="grid">
        <div class="field">
          <label>Date d’encaissement *</label>
          <input
            type="date"
            name="encaissement_date"
            class="form-control"
            value="{{ old('encaissement_date', now()->format('Y-m-d')) }}"
            required
          >
        </div>

        <div class="field">
          <label>Direction *</label>
          <select name="direction" class="form-control" required>
            <option value="credit" @selected(old('direction','credit')==='credit')>
              Crédit (entrée)
            </option>
            <option value="debit"  @selected(old('direction')==='debit')>
              Débit (sortie / remboursement / charge)
            </option>
          </select>
        </div>

        <div class="field">
          <label>Montant TTC (€) *</label>
          <input
            type="number"
            step="0.01"
            min="0.01"
            name="amount_ttc"
            class="form-control"
            value="{{ old('amount_ttc') }}"
            required
            placeholder="ex: 50.00"
          >
        </div>

        <div class="field">
          <label>Montant HT (€)</label>
          <input
            type="number"
            step="0.01"
            min="0"
            name="amount_ht"
            class="form-control"
            value="{{ old('amount_ht') }}"
            placeholder="Si vide, HT = TTC"
          >
          <small class="hint">Astuce : si TVA non applicable, tu peux laisser vide.</small>
        </div>

        <div class="field">
          <label>Mode de règlement *</label>
          <select name="payment_method" class="form-control" required>
            @foreach($PAYMENT_METHOD_FR as $k => $label)
              <option value="{{ $k }}" @selected(old('payment_method','card')===$k)>
                {{ $label }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="field">
          <label>Nature *</label>
          {{-- ✅ match enum DB: service|goods|other --}}
          <select name="nature" class="form-control" required>
            @foreach($NATURE_FR as $k => $label)
              <option value="{{ $k }}" @selected(old('nature','service')===$k)>
                {{ $label }}
              </option>
            @endforeach
          </select>
          <small class="hint">Choisissez “Service / Prestation” pour la majorité des cas.</small>
        </div>

        <div class="field">
          <label>Client (optionnel)</label>
          <input
            type="text"
            name="client_name"
            class="form-control"
            value="{{ old('client_name') }}"
            placeholder="Nom du client (facultatif)"
          >
        </div>

        <div class="field">
          <label>N° facture (optionnel)</label>
          <input
            type="text"
            name="invoice_number"
            class="form-control"
            value="{{ old('invoice_number') }}"
            placeholder="Si lié à une facture externe"
          >
        </div>

        <div class="field full">
          <label>Note (optionnel)</label>
          <input
            type="text"
            name="note"
            class="form-control"
            value="{{ old('note') }}"
            maxlength="255"
            placeholder="Ex: encaissement salon, acompte, régularisation..."
          >
        </div>

        <div class="field full info-box">
          <div class="info-title">Rappel</div>
          <div class="info-text">
            Le livre de recettes est <strong>immuable</strong>. Si vous faites une erreur, utilisez une <strong>contre-passation</strong>
            depuis la liste des écritures.
          </div>
        </div>
      </div>

      <div class="actions">
        <a href="{{ route('receipts.index') }}" class="btn-secondary">Retour</a>
        <button type="submit" class="btn-primary">Enregistrer</button>
      </div>
    </form>

  </div>

  <style>
    .details-container {
      background:#f9f9f9;
      border-radius:10px;
      padding:30px;
      box-shadow:0 5px 15px rgba(0,0,0,0.08);
      margin:0 auto;
    }

    .details-title {
      font-size:2rem;
      font-weight:700;
      color:#647a0b;
      margin:0;
    }

    .text-muted { color:#555; font-size:.95rem; }

    .alert-error {
      padding:12px 14px;
      border-radius:10px;
      margin:14px 0;
      background:#fff;
      box-shadow:0 2px 10px rgba(0,0,0,.05);
      border:1px solid #f3c7c7;
    }

    .card-form {
      background:#fff;
      border-radius:12px;
      padding:18px 20px;
      box-shadow:0 2px 10px rgba(0,0,0,.05);
      border:1px solid #e5e7eb;
    }

    .grid {
      display:grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap:14px;
      margin-top:10px;
    }

    .field { display:flex; flex-direction:column; gap:6px; }
    .field.full { grid-column: 1 / -1; }

    label { font-weight:700; color:#647a0b; font-size:.95rem; }

    .form-control {
      padding:10px 12px;
      border-radius:10px;
      border:1px solid #e5e7eb;
      outline:none;
      background:#fff;
    }
    .form-control:focus {
      border-color:#647a0b;
      box-shadow:0 0 0 3px rgba(100,122,11,.12);
    }

    .hint { color:#6b7280; font-size:.85rem; }

    .info-box{
      background:#f9faf5;
      border:1px solid #e3e8d1;
      border-radius:12px;
      padding:12px 14px;
    }
    .info-title{
      font-weight:800;
      color:#647a0b;
      margin-bottom:4px;
    }
    .info-text{
      color:#4b5563;
      font-size:.92rem;
      line-height:1.35rem;
    }

    .actions {
      display:flex;
      justify-content:flex-end;
      gap:10px;
      margin-top:18px;
    }

    .btn-primary, .btn-secondary {
      padding:10px 16px;
      border-radius:8px;
      text-decoration:none;
      display:inline-flex;
      align-items:center;
      gap:8px;
      cursor:pointer;
      transition:all .25s ease;
      font-weight:600;
      border:none;
    }
    .btn-primary { background:#647a0b; color:#fff; }
    .btn-primary:hover { background:#566f09; }
    .btn-secondary {
      background:transparent;
      color:#854f38;
      border:1px solid #854f38;
    }
    .btn-secondary:hover { background:#854f38; color:#fff; }

    @media (max-width:768px){
      .grid { grid-template-columns: 1fr; }
      .actions { flex-direction:column; }
      .btn-primary, .btn-secondary { width:100%; justify-content:center; }
    }
  </style>
</x-app-layout>
