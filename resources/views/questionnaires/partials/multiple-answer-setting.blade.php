<input type="hidden" name="questions[{{ $index }}][allow_multiple]" value="0">
<label class="mt-3 flex items-start gap-2">
    <input type="checkbox" name="questions[{{ $index }}][allow_multiple]" value="1"
           class="mt-1 rounded border-gray-300 text-[#647a0b]" @checked((bool) ($row['allow_multiple'] ?? false))>
    <span>Autoriser plusieurs réponses <span class="text-sm text-gray-500">(facultatif)</span></span>
</label>
<p class="mt-1 text-sm text-gray-500">Si cette option est cochée, le client pourra sélectionner plusieurs réponses. Sinon, une seule réponse sera possible.</p>
