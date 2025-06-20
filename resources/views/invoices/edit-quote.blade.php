<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Modifier le devis') }} - #{{ $quote->quote_number ?? $quote->id }}
        </h2>
    </x-slot>

    <div class="container-fluid mt-5">
      <div class="details-container mx-auto p-4">
        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h1 class="details-title">{{ __('Modifier le Devis n°') }}{{ $quote->quote_number ?? $quote->id }}</h1>

        <form action="{{ route('invoices.updateQuote', $quote->id) }}" method="POST">
          @csrf @method('PUT')

          {{-- Données générales --}}
          <div class="details-box">
            <label class="details-label">{{ __('Client') }}</label>
            <select name="client_profile_id" class="form-control" required>
              @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ old('client_profile_id', $quote->client_profile_id)==$c->id?'selected':'' }}>
                  {{ $c->first_name }} {{ $c->last_name }}
                </option>
              @endforeach
            </select>
            @error('client_profile_id')<p class="text-red-500">{{ $message }}</p>@enderror
          </div>

          <div class="details-box">
            <label class="details-label">{{ __('Date du Devis') }}</label>
            <input type="date" name="quote_date" class="form-control"
                   value="{{ old('quote_date',$quote->invoice_date->format('Y-m-d')) }}" required>
            @error('quote_date')<p class="text-red-500">{{ $message }}</p>@enderror
          </div>

          <div class="details-box">
            <label class="details-label">{{ __('Valable Jusqu\'au') }}</label>
            <input type="date" name="valid_until" class="form-control"
                   value="{{ old('valid_until',optional($quote->due_date)->format('Y-m-d')) }}">
            @error('valid_until')<p class="text-red-500">{{ $message }}</p>@enderror
          </div>

          <div class="details-box">
            <label class="details-label">{{ __('Notes') }}</label>
            <textarea name="notes" class="form-control">{{ old('notes',$quote->notes) }}</textarea>
            @error('notes')<p class="text-red-500">{{ $message }}</p>@enderror
          </div>

          {{-- Lignes du devis --}}
          <div class="details-box">
            <label class="details-label">{{ __('Articles du devis') }}</label>
            <div class="table-responsive">
              <table class="table table-bordered" id="quote-items-table">
                <thead>
                  <tr>
                    <th>Type</th>
                    <th>{{ __('Produit/Inventaire') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Quantité') }}</th>
                    <th>{{ __('P.U. HT (€)') }}</th>
                    <th>{{ __('TVA (%)') }}</th>
                    <th>{{ __('Montant TVA (€)') }}</th>
                    <th>{{ __('Total TTC (€)') }}</th>
                    <th>{{ __('Action') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($quote->items as $i => $item)
                  <tr>
                    <td>
                      {{-- champ caché --}}
                      <input type="hidden" name="items[{{ $i }}][type]" value="{{ $item->type }}">
                      {{ ucfirst($item->type) }}
                    </td>

                    {{-- produit/inventaire --}}
                    <td>
                      @if($item->type==='product')
                        <select name="items[{{ $i }}][product_id]"
                                class="form-control product-select"
                                data-preload="true"
                                onchange="updateRow(this)">
                          <option value="">{{ __('Sélectionnez un produit') }}</option>
                          @foreach($products as $p)
                            <option value="{{ $p->id }}"
                                    data-price="{{ $p->price }}"
                                    data-tax="{{ $p->tax_rate }}"
                              {{ $item->product_id==$p->id?'selected':'' }}>
                              {{ $p->name }}
                            </option>
                          @endforeach
                        </select>

                      @elseif($item->type==='inventory')
                        <select name="items[{{ $i }}][inventory_item_id]"
                                class="form-control inventory-select"
                                data-preload="true"
                                onchange="updateRow(this)">
                          <option value="">{{ __('Sélectionnez un article') }}</option>
                          @foreach($inventoryItems as $inv)
                            <option value="{{ $inv->id }}"
                                    data-unit="{{ $inv->unit_type }}"
                                    data-ptt="{{ $inv->selling_price_per_ml }}"
                                    data-pt="{{ $inv->selling_price }}"
                                    data-tax="{{ $inv->vat_rate_sale }}"
                              {{ $item->inventory_item_id==$inv->id?'selected':'' }}>
                              {{ $inv->name }} ({{ $inv->unit_type }})
                            </option>
                          @endforeach
                        </select>

                      @else
                        —
                      @endif
                    </td>

                    {{-- description --}}
                    <td>
                      <input type="text"
                             name="items[{{ $i }}][description]"
                             class="form-control description-input"
                             value="{{ old("items.$i.description",$item->description) }}">
                    </td>
                    {{-- quantité --}}
                    <td>
                      <input type="number"
                             name="items[{{ $i }}][quantity]"
                             class="form-control quantity-input"
                             min="0.01"
                             step="0.01"
                             value="{{ old("items.$i.quantity",$item->quantity) }}"
                             onchange="updateRow(this)">
                    </td>
                    {{-- PU HT --}}
                    <td>
                      <input type="number"
                             name="items[{{ $i }}][unit_price]"
                             class="form-control unit-price-input"
                             step="0.01"
                             value="{{ old("items.$i.unit_price",$item->unit_price) }}"
                             onchange="updateRow(this)">
                    </td>
                    {{-- TVA --}}
                    <td>
                      <input type="number"
                             name="items[{{ $i }}][tax_rate]"
                             class="form-control tax-rate-input"
                             step="0.01"
                             value="{{ old("items.$i.tax_rate",$item->tax_rate) }}"
                             readonly>
                    </td>
                    {{-- Montant TVA --}}
                    <td>
                      <input type="number"
                             name="items[{{ $i }}][tax_amount]"
                             class="form-control tax-amount-input"
                             step="0.01"
                             value="{{ old("items.$i.tax_amount",$item->tax_amount) }}"
                             readonly>
                    </td>
                    {{-- Total TTC --}}
                    <td>
                      <input type="number"
                             name="items[{{ $i }}][total_price_with_tax]"
                             class="form-control total-price-with-tax-input readonly-field"
                             step="0.01"
                             value="{{ old("items.$i.total_price_with_tax",$item->total_price_with_tax) }}"
                             readonly>
                    </td>
                    <td>
                      <button type="button" class="btn btn-danger" onclick="removeRow(this)">-</button>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            {{-- boutons ajout --}}
            <button type="button" class="btn-primary mt-2" onclick="addProductLine()">
              {{ __('Ajouter une prestation') }}
            </button>
            <button type="button" class="btn-primary mt-2" onclick="openInventoryModal()">
              {{ __('Ajouter depuis l’inventaire') }}
            </button>
          </div>

          <button type="submit" class="btn-primary mt-4">{{ __('Mettre à jour le Devis') }}</button>
          <a href="{{ route('invoices.showQuote',$quote->id) }}" class="btn-secondary mt-4">{{ __('Annuler') }}</a>
        </form>
      </div>
    </div>

    {{-- Modal inventaire --}}
    <div id="inventoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-xl shadow-lg">
        <h2 class="text-xl font-semibold mb-4 text-[#647a0b]">{{ __('Ajouter depuis l’inventaire') }}</h2>
        <label class="block mb-2">{{ __('Article') }}</label>
        <select id="inventory_item_id" class="form-control mb-4">
          <option value="">{{ __('Sélectionnez un article') }}</option>
          @foreach($inventoryItems as $inv)
            <option value="{{ $inv->id }}"
                    data-name="{{ $inv->name }}"
                    data-unit="{{ $inv->unit_type }}"
                    data-ptt="{{ $inv->selling_price_per_ml }}"
                    data-pt="{{ $inv->selling_price }}"
                    data-tax="{{ $inv->vat_rate_sale }}">
              {{ $inv->name }} ({{ $inv->unit_type }})
            </option>
          @endforeach
        </select>
        <label class="block mb-2">{{ __('Quantité') }}</label>
        <input id="inventory_quantity" type="number" class="form-control mb-4" min="0.01" step="0.01" value="1">
        <div class="flex justify-end gap-3">
          <button class="btn-secondary" onclick="closeInventoryModal()">{{ __('Annuler') }}</button>
          <button class="btn-primary" onclick="addInventoryLine()">{{ __('Ajouter') }}</button>
        </div>
      </div>
    </div>

    {{-- Scripts --}}
    <script>
      let itemIndex = {{ $quote->items->count() }};

      function addProductLine() {
        const tbody = document.querySelector('#quote-items-table tbody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>
            <input type="hidden" name="items[${itemIndex}][type]" value="product">
            {{ __('Product') }}
          </td>
          <td>
            <select name="items[${itemIndex}][product_id]" class="form-control product-select" onchange="updateRow(this)">
              <option value="">{{ __('Sélectionnez un produit') }}</option>
              @foreach($products as $p)
                <option value="{{ $p->id }}" data-price="{{ $p->price }}" data-tax="{{ $p->tax_rate }}">
                  {{ $p->name }}
                </option>
              @endforeach
            </select>
          </td>
          <td><input type="text" name="items[${itemIndex}][description]" class="form-control description-input"></td>
          <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity-input" min="0.01" step="0.01" value="1" onchange="updateRow(this)"></td>
          <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price-input" step="0.01" onchange="updateRow(this)"></td>
          <td><input type="number" name="items[${itemIndex}][tax_rate]" class="form-control tax-rate-input" readonly></td>
          <td><input type="number" name="items[${itemIndex}][tax_amount]" class="form-control tax-amount-input" readonly></td>
          <td><input type="number" name="items[${itemIndex}][total_price_with_tax]" class="form-control total-price-with-tax-input readonly-field" readonly></td>
          <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">-</button></td>
        `;
        tbody.appendChild(tr);
        itemIndex++;
      }

      function openInventoryModal() {
        document.getElementById('inventoryModal').classList.remove('hidden');
      }
      function closeInventoryModal() {
        document.getElementById('inventoryModal').classList.add('hidden');
      }

      function addInventoryLine() {
        const sel = document.getElementById('inventory_item_id');
        const opt = sel.selectedOptions[0];
        if (!opt.value) return;
        const qty = parseFloat(document.getElementById('inventory_quantity').value) || 1;
        const name = opt.dataset.name;
        const tax  = parseFloat(opt.dataset.tax)||0;
        const unit = opt.dataset.unit;
        const ptt  = unit==='ml' ? parseFloat(opt.dataset.ptt) : parseFloat(opt.dataset.pt);
        const puHt = ptt/(1+tax/100);
        const sub  = puHt*qty;
        const tva  = sub*(tax/100);
        const ttc  = ptt*qty;

        const tbody = document.querySelector('#quote-items-table tbody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>
            <input type="hidden" name="items[${itemIndex}][type]" value="inventory">
            {{ __('Inventory') }}
          </td>
          <td>
            <select name="items[${itemIndex}][inventory_item_id]" class="form-control inventory-select" disabled>
              <option value="${opt.value}">${name} (${unit})</option>
            </select>
          </td>
          <td><input type="text" name="items[${itemIndex}][description]" class="form-control description-input" value="${name}" readonly></td>
          <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity-input" value="${qty}" readonly></td>
          <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price-input" value="${puHt.toFixed(2)}" readonly></td>
          <td><input type="number" name="items[${itemIndex}][tax_rate]" class="form-control tax-rate-input" value="${tax.toFixed(2)}" readonly></td>
          <td><input type="number" name="items[${itemIndex}][tax_amount]" class="form-control tax-amount-input" value="${tva.toFixed(2)}" readonly></td>
          <td><input type="number" name="items[${itemIndex}][total_price_with_tax]" class="form-control total-price-with-tax-input readonly-field" value="${ttc.toFixed(2)}" readonly></td>
          <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">-</button></td>
        `;
        tbody.appendChild(tr);
        itemIndex++;
        closeInventoryModal();
      }

      function removeRow(btn) {
        btn.closest('tr').remove();
      }

      function updateRow(el) {
        const tr = el.closest('tr');
        const prod = tr.querySelector('.product-select');
        const inv  = tr.querySelector('.inventory-select');
        const desc = tr.querySelector('.description-input');
        const qty  = tr.querySelector('.quantity-input');
        const pu   = tr.querySelector('.unit-price-input');
        const tx   = tr.querySelector('.tax-rate-input');
        const ta   = tr.querySelector('.tax-amount-input');
        const tt   = tr.querySelector('.total-price-with-tax-input');

        // si produit
        if (prod && el===prod) {
          const opt = prod.selectedOptions[0];
          const p   = parseFloat(opt.dataset.price)||0;
          const t   = parseFloat(opt.dataset.tax)||0;
          desc.value = opt.text;
          if (!pu.dataset.manual) pu.value = p.toFixed(2);
          tx.value = t.toFixed(2);
        }

        // si inventaire on ignore : on n’édite pas
        pu.addEventListener('input', ()=>pu.dataset.manual=1,{once:true});

        const q = parseFloat(qty.value)||0;
        const u = parseFloat(pu.value)||0;
        const r = parseFloat(tx.value)||0;
        const sub = q*u;
        const vat = sub*(r/100);
        ta.value = vat.toFixed(2);
        tt.value = (sub+vat).toFixed(2);
      }

      window.onload = ()=>{
        document.querySelectorAll('[data-preload="true"]').forEach(el=>{
          updateRow(el);
        });
      };
    </script>

    {{-- Styles (identiques à ceux existants) --}}
    <style>
      .container-fluid { max-width:1200px; }
      .details-container { background:#f9f9f9; border-radius:10px; padding:30px; box-shadow:0 5px 15px rgba(0,0,0,0.1); margin:0 auto; }
      .details-title { font-size:2rem; font-weight:bold; color:#647a0b; margin-bottom:20px; text-align:center; }
      .details-box { margin-bottom:15px; }
      .details-label { font-weight:bold; color:#647a0b; margin-bottom:5px; display:block; }
      .form-control { width:100%; padding:10px; border:1px solid #ccc; border-radius:5px; }
      .btn-primary { background:#647a0b; color:#fff; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; }
      .btn-secondary { background:transparent; color:#854f38; padding:10px 20px; border:1px solid #854f38; border-radius:5px; cursor:pointer; }
      .btn-danger { background:#e3342f; color:#fff; padding:5px 10px; border:none; border-radius:5px; cursor:pointer; }
      .btn-primary:hover { background:#854f38; }
      .btn-secondary:hover { background:#854f38; color:#fff; }
      .readonly-field { background:#e9ecef; cursor:not-allowed; }
      #quote-items-table th { background:#647a0b; color:#fff; white-space:nowrap; }
      #quote-items-table td, #quote-items-table th { padding:8px; text-align:left; border-bottom:1px solid #ccc; }
      @media (max-width:768px) {
        .details-title { font-size:1.5rem; }
        .btn-primary, .btn-secondary { width:100%; margin-bottom:10px; }
        #quote-items-table th, #quote-items-table td { padding:6px; }
      }
    </style>

<script>
  window.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    form.addEventListener('submit', (e) => {
      // Récupère toutes les lignes
      const rows = Array.from(document.querySelectorAll('#quote-items-table tbody tr'));
      rows.forEach(row => {
        // Cherche un sélecteur produit ou inventaire
        const prod = row.querySelector('select[name$="[product_id]"]');
        const inv  = row.querySelector('select[name$="[inventory_item_id]"]');
        // Si ni l’un ni l’autre n’a de valeur, on supprime la ligne
        if ((!prod || !prod.value) && (!inv || !inv.value)) {
          row.remove();
        }
      });
      // Si plus aucune ligne valide, on bloque l’envoi
      if (document.querySelectorAll('#quote-items-table tbody tr').length === 0) {
        e.preventDefault();
        alert('Vous devez au moins avoir une ligne de produit ou d’inventaire valide.');
      }
    });
  });
</script>


</x-app-layout>
