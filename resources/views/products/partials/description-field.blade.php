@php
    $descriptionInput = array_filter(session()->getOldInput(), fn($value) => is_string($value) || $value === null);
    $initialDescription = \App\Support\ProductDescription::payload($descriptionInput, $product ?? null);
    $initialDescription['description_html'] = \App\Support\ProductDescription::sanitize($initialDescription['description_html']);
@endphp

<div class="product-description-field" style="min-width:0">
    <label for="description" id="description-label" class="block text-sm font-semibold text-gray-700">{{ __('Description') }}</label>
    <textarea id="description" name="description" rows="4" class="form-control mt-1 w-full rounded-lg border-gray-300 text-base">{{ $initialDescription['description'] }}</textarea>
    <textarea data-description-initial hidden>{{ $initialDescription['description_html'] }}</textarea>
    <input type="hidden" name="description_html" disabled>
    <div data-description-editor hidden></div>
    @error('description') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
    @error('description_html') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
</div>

@once
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <style>
        .product-description-field .ql-toolbar { margin-top: .25rem; border-radius: .5rem .5rem 0 0; background: white; }
        .product-description-field .ql-toolbar button { width: 44px; height: 44px; padding: 12px; }
        .product-description-field .ql-container { border-radius: 0 0 .5rem .5rem; background: white; font: inherit; }
        .product-description-field .ql-editor { min-height: 130px; font-size: 16px; overflow-wrap: anywhere; }
        .product-description-field .ql-editor p { margin: 0; }
        .product-description-field [hidden] { display: none !important; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof Quill === 'undefined') return;
            document.querySelectorAll('.product-description-field').forEach(field => {
                const textarea = field.querySelector('[name="description"]');
                const htmlInput = field.querySelector('[name="description_html"]');
                const editor = field.querySelector('[data-description-editor]');
                const initialHtml = field.querySelector('[data-description-initial]').value;
                const quill = new Quill(editor, {
                    theme: 'snow',
                    formats: ['bold', 'italic', 'underline'],
                    modules: { toolbar: [['bold', 'italic', 'underline']] },
                });
                if (initialHtml) quill.clipboard.dangerouslyPasteHTML(initialHtml);
                else quill.setText(textarea.value);
                quill.root.setAttribute('role', 'textbox');
                quill.root.setAttribute('aria-label', @json(__('Description')));
                quill.root.setAttribute('aria-multiline', 'true');
                const labels = { bold: @json(__('Gras')), italic: @json(__('Italique')), underline: @json(__('Souligné')) };
                Object.entries(labels).forEach(([format, label]) => {
                    const button = field.querySelector('.ql-' + format);
                    button.setAttribute('aria-label', label);
                    button.setAttribute('title', label);
                });
                field.querySelector('label').addEventListener('click', event => {
                    event.preventDefault();
                    quill.focus();
                });
                quill.on('text-change', () => {
                    textarea.value = quill.getText().replace(/\n$/, '');
                    htmlInput.value = quill.root.innerHTML;
                    htmlInput.disabled = false;
                });
                // Preserve submitted formatting after validation errors, even if not edited again.
                if (@json(session()->hasOldInput('description_html'))) {
                    htmlInput.value = quill.root.innerHTML;
                    htmlInput.disabled = false;
                }
                editor.hidden = false;
                textarea.hidden = true;
            });
        });
    </script>
@endonce
