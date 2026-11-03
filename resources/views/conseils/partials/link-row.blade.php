<div data-link-row class="rounded-md border border-gray-200 bg-gray-50 p-3 mb-3">
    <div class="flex items-center justify-between gap-3 mb-2">
        <span data-link-number class="text-sm font-semibold text-gray-700">Lien {{ is_numeric($index) ? $index + 1 : '' }}</span>
        <button type="button" data-remove-link class="text-sm text-red-700 underline">Retirer ce lien</button>
    </div>
    <div class="mb-3">
        <label data-link-label="label" for="conseil-link-{{ $index }}-label" class="block text-sm font-medium text-gray-700 mb-1">Titre du lien (facultatif)</label>
        <input data-link-input="label" id="conseil-link-{{ $index }}-label" name="links[{{ $index }}][label]"
               type="text" maxlength="255" value="{{ is_string($link['label'] ?? null) ? $link['label'] : '' }}"
               placeholder="Ex. Voir la vidéo" class="border border-[#854f38] rounded-md w-full py-2 px-3">
    </div>
    <div>
        <label data-link-label="url" for="conseil-link-{{ $index }}-url" class="block text-sm font-medium text-gray-700 mb-1">Adresse du lien</label>
        <input data-link-input="url" id="conseil-link-{{ $index }}-url" name="links[{{ $index }}][url]"
               type="url" maxlength="2048" inputmode="url" spellcheck="false" autocapitalize="off"
               value="{{ is_string($link['url'] ?? null) ? $link['url'] : '' }}"
               placeholder="https://…" aria-describedby="conseil-links-help" class="border border-[#854f38] rounded-md w-full py-2 px-3">
    </div>
</div>
