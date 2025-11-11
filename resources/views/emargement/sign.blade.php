<x-guest-layout>
    <div class="max-w-xl mx-auto p-6 bg-white rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-4">Signature de la feuille d’émargement</h1>

        <p class="mb-2"><strong>Praticien :</strong> {{ $em->meta['therapist']['name'] ?? '—' }}</p>
        <p class="mb-2"><strong>Client :</strong> {{ ($em->meta['client']['first'] ?? '') . ' ' . ($em->meta['client']['last'] ?? '') }}</p>
        <p class="mb-2"><strong>Prestation :</strong> {{ $em->meta['product']['name'] ?? '—' }}</p>
        <p class="mb-4"><strong>Date :</strong> {{ \Carbon\Carbon::parse($em->meta['appointment']['date'] ?? null)->format('d/m/Y H:i') }}</p>

        <form method="POST" action="{{ route('emargement.sign.submit', $em->token) }}" x-data="{ mode: 'checkbox'}">
            @csrf

            <div class="mb-4">
                <label class="block font-semibold mb-2">Mode de signature</label>
                <select name="mode" x-model="mode" class="w-full border rounded p-2">
                    <option value="checkbox">Je confirme ma présence</option>
                    <option value="canvas">Signature manuscrite</option>
                </select>
            </div>

            <div x-show="mode === 'checkbox'" class="mb-4">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="confirmed" value="1" class="border rounded">
                    <span>Je confirme avoir bien participé à la séance.</span>
                </label>
                @error('confirmed') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div x-show="mode === 'canvas'" class="mb-4" x-cloak>
                <canvas id="sig" width="500" height="160" class="border rounded w-full"></canvas>
                <input type="hidden" name="signature_data" id="signature_data">
                <div class="mt-2 flex gap-2">
                    <button type="button" class="px-3 py-2 border rounded" onclick="sigClear()">Effacer</button>
                </div>
            </div>

            <button class="bg-[#647a0b] text-white px-4 py-2 rounded">Signer</button>
        </form>
    </div>

    <script>
    let canvas = document.getElementById('sig');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let drawing = false;

        canvas.addEventListener('mousedown', e => { drawing = true; ctx.beginPath(); ctx.moveTo(e.offsetX, e.offsetY); });
        canvas.addEventListener('mousemove', e => { if(!drawing) return; ctx.lineTo(e.offsetX, e.offsetY); ctx.stroke(); });
        window.addEventListener('mouseup', () => {
            if(!drawing) return;
            drawing = false;
            document.getElementById('signature_data').value = canvas.toDataURL('image/png');
        });

        window.sigClear = () => {
            ctx.clearRect(0,0,canvas.width, canvas.height);
            document.getElementById('signature_data').value = '';
        };
    }
    </script>
</x-guest-layout>
