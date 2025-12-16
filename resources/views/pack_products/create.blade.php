<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl" style="color:#647a0b;">
                Nouveau pack (forfait)
            </h2>
            <p class="mt-1 text-xs text-slate-600">
                Un pack regroupe <strong>une ou plusieurs prestations</strong> avec un nombre de crédits.
            </p>
        </div>
    </x-slot>

    <style>
        :root{
            --brand:#647a0b;
            --brown:#6b4f2a;
            --cream:#f7f2ea;
        }
    </style>

    <div class="container mt-6 max-w-5xl">

        {{-- Errors --}}
        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <div class="font-bold mb-1">Erreur</div>
                <ul class="list-disc ml-5 text-sm">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('pack-products.store') }}" method="POST"
              class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 p-6 space-y-6">
            @csrf

            {{-- Basic info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Nom du pack</label>
                    <input name="name" required
                           placeholder="Ex : Forfait Suivi Bien-Être"
                           value="{{ old('name') }}"
                           class="mt-1 w-full rounded-xl border-slate-300 focus:border-[var(--brand)] focus:ring-[var(--brand)]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Prix du pack (€)</label>
                        <input type="number" step="0.01" min="0"
                               name="price" value="{{ old('price', 0) }}" required
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-[var(--brand)] focus:ring-[var(--brand)]">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">TVA (%)</label>
                        <input type="number" step="0.01" min="0" max="100"
                               name="tax_rate" value="{{ old('tax_rate', 0) }}" required
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-[var(--brand)] focus:ring-[var(--brand)]">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">Description</label>
                    <textarea name="description" rows="3"
                              placeholder="Décrivez le contenu et l’objectif du forfait"
                              class="mt-1 w-full rounded-xl border-slate-300 focus:border-[var(--brand)] focus:ring-[var(--brand)]">{{ old('description') }}</textarea>
                </div>
            </div>

            {{-- Visibility --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 p-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span class="text-sm font-semibold text-slate-700">Pack actif</span>
                </label>

                <label class="flex items-center gap-2 rounded-xl border border-slate-200 p-3">
                    <input type="hidden" name="visible_in_portal" value="0">
                    <input type="checkbox" name="visible_in_portal" value="1" checked>
                    <span class="text-sm font-semibold text-slate-700">Visible dans le portail client</span>
                </label>

                <label class="flex items-center gap-2 rounded-xl border border-slate-200 p-3">
                    <input type="hidden" name="price_visible_in_portal" value="0">
                    <input type="checkbox" name="price_visible_in_portal" value="1" checked>
                    <span class="text-sm font-semibold text-slate-700">Afficher le prix</span>
                </label>
            </div>

            {{-- Pack content --}}
            <div class="pt-4 border-t border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            Contenu du pack
                        </h3>
                        <p class="text-xs text-slate-600">
                            Ajoutez une ou plusieurs prestations avec leur nombre de crédits.
                        </p>
                    </div>

                    <button type="button" id="addRow"
                            class="rounded-xl px-3 py-2 text-xs font-bold text-white shadow-sm"
                            style="background:var(--brand)">
                        + Ajouter une prestation
                    </button>
                </div>

                <div class="overflow-x-auto rounded-xl ring-1 ring-slate-200">
                    <table class="min-w-full text-sm">
                        <thead class="bg-[var(--cream)] text-slate-700">
                            <tr>
                                <th class="px-3 py-2 text-left font-bold">Prestation</th>
                                <th class="px-3 py-2 text-left font-bold">Crédits inclus</th>
                                <th class="px-3 py-2 text-right font-bold"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody" class="divide-y divide-slate-100 bg-white"></tbody>
                    </table>
                </div>

                <div class="mt-3 text-xs text-slate-600">
                    Exemple :
                    <span class="font-semibold">
                        5 × Massage 60 min + 1 × Bilan + 3 × Visio
                    </span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-2 pt-4">
                <a href="{{ route('pack-products.index') }}"
                   class="rounded-xl px-4 py-2 text-sm font-semibold ring-1 ring-slate-200 hover:bg-slate-50">
                    Annuler
                </a>
                <button class="rounded-xl px-4 py-2 text-sm font-bold text-white"
                        style="background:var(--brand)">
                    Créer le pack
                </button>
            </div>
        </form>
    </div>

    {{-- JS --}}
    <script>
        const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values());
        const body = document.getElementById('itemsBody');
        const addRowBtn = document.getElementById('addRow');

        function escapeHtml(str) {
            return String(str)
                .replaceAll('&','&amp;')
                .replaceAll('<','&lt;')
                .replaceAll('>','&gt;')
                .replaceAll('"','&quot;')
                .replaceAll("'","&#039;");
        }

        function rowHtml(index) {
            const options = products.map(p =>
                `<option value="${p.id}">${escapeHtml(p.name)}</option>`
            ).join('');

            return `
                <tr>
                    <td class="px-3 py-2">
                        <select name="items[${index}][product_id]" required
                            class="w-full rounded-xl border-slate-300 focus:border-[var(--brand)] focus:ring-[var(--brand)]">
                            <option value="">— Choisir une prestation —</option>
                            ${options}
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" min="1" max="999" value="1"
                            name="items[${index}][quantity]" required
                            class="w-32 rounded-xl border-slate-300 focus:border-[var(--brand)] focus:ring-[var(--brand)]">
                    </td>
                    <td class="px-3 py-2 text-right">
                        <button type="button"
                            class="removeRow text-xs font-bold text-red-700 hover:underline">
                            Retirer
                        </button>
                    </td>
                </tr>
            `;
        }

        function rebuildIndexes() {
            [...body.querySelectorAll('tr')].forEach((tr, idx) => {
                tr.querySelectorAll('select, input').forEach(el => {
                    el.name = el.name.replace(/items\[\d+]/, `items[${idx}]`);
                });
            });
        }

        addRowBtn.addEventListener('click', () => {
            body.insertAdjacentHTML('beforeend', rowHtml(body.children.length));
        });

        body.addEventListener('click', e => {
            if (e.target.classList.contains('removeRow')) {
                e.target.closest('tr').remove();
                rebuildIndexes();
            }
        });

        // Default row
        body.insertAdjacentHTML('beforeend', rowHtml(0));
    </script>
</x-app-layout>
