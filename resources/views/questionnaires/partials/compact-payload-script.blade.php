<script>
    document.querySelectorAll('form[data-questionnaire-builder]').forEach((form) => {
        form.addEventListener('submit', () => {
            const questions = Array.from(form.querySelectorAll('[data-question-row]')).map((row) => ({
                id: row.querySelector('[name$="[id]"]')?.value || null,
                text: row.querySelector('[name$="[text]"]')?.value || '',
                type: row.querySelector('[name$="[type]"]')?.value || 'text',
                options: row.querySelector('[name$="[options]"]')?.value || '',
            }));

            const payload = form.querySelector('[name="questions_payload"]');
            if (!payload) return;

            payload.value = JSON.stringify(questions);
            form.querySelectorAll('[name^="questions["]').forEach((field) => {
                field.disabled = true;
            });
        });
    });
</script>
