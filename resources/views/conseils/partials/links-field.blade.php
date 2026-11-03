@php
    $defaultLinks = old('links_present') ? [] : (isset($conseil) ? $conseil->links : []);
    $formLinks = old('links', $defaultLinks);
    $formLinks = is_array($formLinks) ? array_values($formLinks) : [];
    $maxLinks = \App\Http\Requests\ConseilRequest::MAX_LINKS;
@endphp
<fieldset id="conseil-links" class="mb-6" data-max-links="{{ $maxLinks }}">
    <legend class="text-[#647a0b] font-semibold mb-2">Liens utiles (facultatif)</legend>
    <p id="conseil-links-help" class="text-gray-600 text-sm mb-3">
        Ajoutez une vidéo, un article ou une autre ressource. Copiez son adresse complète (https://…).
        Ces liens seront accessibles au client depuis le conseil, avec ou sans texte et PDF.
    </p>
    <input type="hidden" name="links_present" value="1">
    <div data-link-rows>
        @foreach($formLinks as $index => $link)
            @include('conseils.partials.link-row', ['index' => $index, 'link' => is_array($link) ? $link : []])
        @endforeach
    </div>
    <template data-link-template>
        @include('conseils.partials.link-row', ['index' => '__INDEX__', 'link' => []])
    </template>
    <button type="button" data-add-link class="inline-flex items-center rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b] hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
        + Ajouter un lien
    </button>
    <p data-link-limit class="text-sm text-gray-500 mt-2" role="status" hidden>Vous pouvez ajouter jusqu’à {{ $maxLinks }} liens par conseil.</p>
</fieldset>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fieldset = document.getElementById('conseil-links');
        const rows = fieldset.querySelector('[data-link-rows]');
        const addButton = fieldset.querySelector('[data-add-link]');
        const maxLinks = Number(fieldset.dataset.maxLinks);

        function updateRows() {
            Array.from(rows.children).forEach(function (row, index) {
                row.querySelector('[data-link-number]').textContent = 'Lien ' + (index + 1);
                row.querySelector('[data-remove-link]').setAttribute('aria-label', 'Retirer le lien ' + (index + 1));
                ['label', 'url'].forEach(function (field) {
                    const input = row.querySelector('[data-link-input="' + field + '"]');
                    input.name = 'links[' + index + '][' + field + ']';
                    input.id = 'conseil-link-' + index + '-' + field;
                    row.querySelector('[data-link-label="' + field + '"]').htmlFor = input.id;
                });
            });
            addButton.disabled = rows.children.length >= maxLinks;
            fieldset.querySelector('[data-link-limit]').hidden = !addButton.disabled;
        }

        addButton.addEventListener('click', function () {
            if (rows.children.length >= maxLinks) return;
            rows.append(fieldset.querySelector('[data-link-template]').content.cloneNode(true));
            updateRows();
            rows.lastElementChild.querySelector('[data-link-input="url"]').focus();
        });

        rows.addEventListener('click', function (event) {
            const removeButton = event.target.closest('[data-remove-link]');
            if (!removeButton) return;
            const row = removeButton.closest('[data-link-row]');
            const nextRow = row.nextElementSibling || row.previousElementSibling;
            row.remove();
            updateRows();
            (nextRow ? nextRow.querySelector('[data-link-input="url"]') : addButton).focus();
        });

        updateRows();
    });
</script>
