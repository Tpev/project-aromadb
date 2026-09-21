<section class="my-5 rounded-xl border border-[#dfe6c7] bg-[#f8faf2] p-4" data-event-email-notes>
    <h2 class="text-lg font-semibold text-[#526508]">{{ __('Messages aux participants') }}</h2>
    <p class="mb-4 text-sm text-gray-600">{{ __('Ces messages complètent les informations de l’événement. Les modifications s’appliquent aux prochains emails, sans renvoyer les confirmations déjà envoyées.') }}</p>
    @foreach(['confirmation_email_note' => 'Message ajouté à l’email de confirmation', 'reminder_email_note' => 'Message ajouté aux emails de rappel'] as $field => $label)
        <div class="mb-4">
            <label class="details-label block font-semibold" for="{{ $field }}">{{ __($label) }}</label>
            <textarea id="{{ $field }}" name="{{ $field }}" rows="4" maxlength="2000"
                      class="form-control mt-2 w-full rounded-lg border border-gray-300 p-3"
                      aria-describedby="{{ $field }}_help">{{ old($field, ($event ?? null)?->{$field}) }}</textarea>
            <p id="{{ $field }}_help" class="text-sm text-gray-600">{{ __('Facultatif, 2 000 caractères maximum. Les liens https:// sont cliquables dans l’email.') }}
                @if($field === 'reminder_email_note') {{ __('Utilisé pour les rappels à 24 h et à 1 h.') }} @endif
            </p>
            @error($field)<p class="text-red-600">{{ $message }}</p>@enderror
            <details class="mt-2 rounded-lg border border-gray-200 bg-white p-3">
                <summary class="cursor-pointer font-semibold">{{ __('Aperçu du message dans l’email') }}</summary>
                <p class="mt-3">{{ __('Bonjour [Prénom],') }}</p>
                <p>{{ $field === 'confirmation_email_note' ? __('Votre réservation est bien enregistrée.') : __('Ceci est un rappel concernant votre réservation.') }}</p>
                <p class="my-3 text-sm text-gray-600">{{ __('Les informations de l’événement et son éventuel lien de visio sont conservés.') }}</p>
                <div class="my-3 border-l-4 border-[#647a0b] pl-3" data-note-preview="{{ $field }}"></div>
                <p>{{ __('Si vous avez des questions, répondez simplement à cet email.') }}</p>
            </details>
        </div>
    @endforeach
</section>
<script>
    document.querySelectorAll('[data-event-email-notes] textarea').forEach(function (input) {
        const preview = input.closest('[data-event-email-notes]').querySelector('[data-note-preview="' + input.id + '"]');
        const refresh = () => {
            preview.replaceChildren();
            const text = input.value.trim() || 'Aucun message personnalisé.';
            text.split(/(https?:\/\/[^\s<>]+)/g).forEach(part => {
                if (/^https?:\/\//.test(part)) {
                    const link = document.createElement('a');
                    link.href = part; link.textContent = part; link.rel = 'noopener noreferrer';
                    link.style.textDecoration = 'underline'; link.style.color = '#526508';
                    preview.append(link);
                } else preview.append(document.createTextNode(part));
            });
            preview.style.whiteSpace = 'pre-line';
        };
        input.addEventListener('input', refresh);
        refresh();
    });
</script>
