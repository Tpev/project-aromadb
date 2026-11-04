function initializeGiftVouchers() {
    const backgroundForm = document.querySelector('[data-voucher-background-form]');
    if (backgroundForm) {
        const input = backgroundForm.querySelector('[name="gift_voucher_background"]');
        const mode = backgroundForm.querySelector('[name="gift_voucher_background_mode"]');
        const remove = backgroundForm.querySelector('[name="remove_gift_voucher_background"]');
        const preview = backgroundForm.querySelector('#giftVoucherBackgroundPreview');
        const hint = backgroundForm.querySelector('#giftVoucherBackgroundPreviewHint');
        const error = backgroundForm.querySelector('#giftVoucherBackgroundError');
        const saved = { image: preview.style.backgroundImage, mode: mode.value, hint: hint.textContent.trim() };
        let generation = 0;
        let pendingUrl = null;

        const clearPending = () => {
            generation++;
            if (pendingUrl) URL.revokeObjectURL(pendingUrl);
            pendingUrl = null;
        };
        const setPreview = (image, text) => {
            preview.style.backgroundImage = image;
            preview.style.backgroundSize = 'cover';
            preview.style.backgroundPosition = 'center';
            hint.textContent = text;
        };
        const reject = message => {
            clearPending();
            input.value = '';
            mode.value = saved.mode;
            remove.checked = false;
            setPreview(saved.image, saved.hint);
            error.textContent = message;
            error.hidden = false;
        };

        input.addEventListener('change', () => {
            clearPending();
            error.hidden = true;
            const file = input.files?.[0];
            if (!file) return;
            if (!/\.(jpe?g|png|webp)$/i.test(file.name)
                || (file.type && !['image/jpeg', 'image/png', 'image/webp'].includes(file.type))) {
                reject('Choisissez une image JPG, PNG ou WebP.');
                return;
            }
            if (file.size > 6 * 1024 * 1024) {
                reject('L’image ne doit pas dépasser 6 Mo.');
                return;
            }
            mode.value = 'custom_upload';
            remove.checked = false;
            const currentGeneration = generation;
            pendingUrl = URL.createObjectURL(file);
            const imageUrl = pendingUrl;
            const image = new Image();
            hint.textContent = 'Vérification de l’image…';
            image.onload = () => {
                if (currentGeneration !== generation) return;
                setPreview(`url("${imageUrl}")`, 'Aperçu non enregistré. Cliquez sur « Enregistrer » pour utiliser cette image sur les futurs bons.');
            };
            image.onerror = () => {
                if (currentGeneration === generation) reject('Cette image ne peut pas être lue. Essayez une autre image JPG, PNG ou WebP.');
            };
            image.src = imageUrl;
        });
        mode.addEventListener('change', () => {
            clearPending();
            error.hidden = true;
            input.value = '';
            remove.checked = false;
            setPreview(mode.value === 'custom_upload' ? saved.image : '',
                mode.value === saved.mode ? saved.hint : (mode.value === 'default'
                    ? 'Thème par défaut sélectionné · modification non enregistrée.'
                    : 'Choisissez une image, puis cliquez sur « Enregistrer ».'));
        });
        remove.addEventListener('change', () => {
            clearPending();
            input.value = '';
            error.hidden = true;
            mode.value = remove.checked ? 'default' : saved.mode;
            setPreview(remove.checked ? '' : saved.image, remove.checked
                ? 'Thème par défaut sélectionné · modification non enregistrée. Les bons déjà créés gardent leur image.'
                : saved.hint);
        });
    }

    const money = cents => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(cents / 100);
    document.querySelectorAll('[data-voucher-redeem-form]').forEach(form => {
        form.addEventListener('submit', event => {
            const amount = Math.round(Number(form.elements.amount_eur.value) * 100);
            const balance = Number(form.elements.expected_balance_cents.value);
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }
            if (!Number.isFinite(amount) || amount <= 0 || amount > balance || !form.reportValidity()) {
                event.preventDefault();
                return;
            }
            if (!window.confirm(`Utiliser ${money(amount)} de ce bon cadeau pour régler une prestation ?\n\nSolde disponible après utilisation : ${money(balance - amount)}.\n\nCette action déduit le montant du bon cadeau.`)) {
                event.preventDefault();
                return;
            }
            form.dataset.submitting = 'true';
        });
    });
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('[data-voucher-redeem-form]').forEach(form => delete form.dataset.submitting);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeGiftVouchers);
} else {
    initializeGiftVouchers();
}
