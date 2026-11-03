function initializeNotifications() {
    const button = document.getElementById('notificationButton');
    const dropdown = document.getElementById('notificationsDropdown');
    if (!button || !dropdown) return;

    const list = dropdown.querySelector('[data-notification-list]');
    const markAllForm = document.getElementById('markAllNotificationsAsReadForm');
    const errorMessage = dropdown.querySelector('[data-notification-error]');
    const csrfToken = markAllForm.querySelector('[name="_token"]').value;
    let revision = 0;
    let pendingRefresh;
    let markingAsRead = false;

    function render(data) {
        if (!Number.isInteger(data.unreadCount) || !Array.isArray(data.notifications)) {
            throw new Error('Invalid notification response');
        }
        let badge = button.querySelector('[data-notification-count]');
        if (data.unreadCount > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.dataset.notificationCount = '';
                badge.className = 'badge absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full transform translate-x-1/2 -translate-y-1/2';
                button.appendChild(badge);
            }
            badge.textContent = data.unreadCount;
        } else {
            badge?.remove();
        }
        markAllForm.classList.toggle('hidden', data.unreadCount === 0);
        list.replaceChildren();
        for (const notification of data.notifications) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = notification.open_url;
            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = csrfToken;
            const item = document.createElement('button');
            item.type = 'submit';
            item.className = 'block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100';
            item.textContent = notification.data?.message || 'Notification';
            const detail = document.createElement('small');
            detail.className = 'block text-gray-500';
            detail.textContent = [notification.data?.amount_paid, notification.data?.appointment_date].filter(Boolean).join(' – ');
            item.appendChild(detail);
            form.append(token, item);
            list.appendChild(form);
        }
        if (!list.children.length) {
            const empty = document.createElement('div');
            empty.className = 'px-4 py-2 text-sm text-gray-500';
            empty.textContent = 'Aucune notification';
            list.appendChild(empty);
        }
    }

    async function refresh() {
        if (markingAsRead || document.hidden) return;
        const currentRevision = ++revision;
        pendingRefresh?.abort();
        pendingRefresh = new AbortController();
        try {
            const response = await fetch(dropdown.dataset.fetchUrl, {
                headers: { Accept: 'application/json' },
                cache: 'no-store', signal: pendingRefresh.signal,
            });
            if (!response.ok) return;
            const data = await response.json();
            if (currentRevision === revision) render(data);
        } catch (error) {
            // Preserve the last known state if the connection or session is unavailable.
        }
    }

    function close() {
        dropdown.classList.add('hidden');
        button.setAttribute('aria-expanded', 'false');
    }

    button.addEventListener('click', () => {
        const opening = dropdown.classList.contains('hidden');
        dropdown.classList.toggle('hidden');
        button.setAttribute('aria-expanded', String(opening));
        if (opening) refresh();
    });
    document.addEventListener('click', event => {
        if (!button.contains(event.target) && !dropdown.contains(event.target)) close();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !dropdown.classList.contains('hidden')) {
            close();
            button.focus();
        }
    });

    markAllForm.addEventListener('submit', async event => {
        event.preventDefault();
        if (markingAsRead) return;
        markingAsRead = true;
        ++revision;
        pendingRefresh?.abort();
        const submitButton = markAllForm.querySelector('button');
        submitButton.disabled = true;
        errorMessage.classList.add('hidden');
        try {
            const response = await fetch(markAllForm.action, {
                method: 'POST', headers: { Accept: 'application/json' }, body: new FormData(markAllForm),
            });
            if (!response.ok) throw new Error('Unable to mark notifications as read');
            render(await response.json());
            document.querySelectorAll('[data-notification-read-state]').forEach(cell => { cell.textContent = 'Lu'; });
        } catch (error) {
            errorMessage.textContent = 'Impossible de marquer les notifications comme lues. Réessayez.';
            errorMessage.classList.remove('hidden');
        } finally {
            markingAsRead = false;
            submitButton.disabled = false;
        }
    });

    setInterval(refresh, 30000);
    window.addEventListener('focus', refresh);
    window.addEventListener('pageshow', event => { if (event.persisted) refresh(); });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeNotifications);
} else {
    initializeNotifications();
}
