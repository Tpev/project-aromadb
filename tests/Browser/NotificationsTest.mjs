import { after, before, test } from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { chromium, expect } from '@playwright/test';

// An isolated DOM and mocked HTTP responses exercise the production script without an app database.
const script = await readFile(new URL('../../resources/js/notifications.js', import.meta.url), 'utf8');
let browser;
before(async () => { browser = await chromium.launch({ headless: true }); });
after(async () => { await browser?.close(); });

const notification = (id, message = 'Nouveau rendez-vous') => ({
    id, open_url: `http://notifications.test/notifications/${id}/open`,
    data: { message, amount_paid: '60,00 €', appointment_date: '10/11/2026' },
});

async function fixture(t, initial = []) {
    const page = await browser.newPage();
    t.after(() => page.close());
    const state = { notifications: initial, failMark: false, marks: 0, holdFetch: null, fetches: 0 };
    await page.clock.install();
    await page.route('http://notifications.test/**', async route => {
        const path = new URL(route.request().url()).pathname;
        if (path === '/notifications/fetch') {
            state.fetches++;
            const data = { unreadCount: state.notifications.length, notifications: state.notifications };
            if (state.holdFetch) await state.holdFetch;
            await route.fulfill({ json: data }).catch(() => {});
        } else if (path === '/notifications/mark-all-as-read') {
            assert.equal(route.request().method(), 'POST');
            assert.match(route.request().postData(), /fixture-token/);
            state.marks++;
            if (state.failMark) return route.fulfill({ status: 503, json: { message: 'Temporary failure' } });
            state.notifications = [];
            await route.fulfill({ json: { unreadCount: 0, notifications: [] } });
        } else {
            await route.fulfill({ contentType: 'text/html', body: `<!doctype html><style>.hidden {display:none}</style>
                <button id="notificationButton" aria-expanded="false">Notifications${initial.length ? `<span class="badge" data-notification-count>${initial.length}</span>` : ''}</button>
                <div id="notificationsDropdown" data-fetch-url="/notifications/fetch" class="hidden">
                  <form id="markAllNotificationsAsReadForm" action="/notifications/mark-all-as-read" method="post" class="${initial.length ? '' : 'hidden'}">
                    <input name="_token" type="hidden" value="fixture-token"><button type="submit">Tout marquer comme lu</button>
                  </form>
                  <p data-notification-error class="hidden" role="alert"></p><div data-notification-list></div>
                </div><table><tr><td data-notification-read-state>Marquer comme lu</td></tr></table>` });
        }
    });
    await page.goto('http://notifications.test/fixture');
    await page.addScriptTag({ content: script });
    return { page, state };
}

test('polling creates one badge, updates it, and removes it when the count reaches zero', async t => {
    const { page, state } = await fixture(t);
    state.notifications = [notification('one')];
    await page.clock.fastForward(30000);
    await expect(page.locator('[data-notification-count]')).toHaveText('1');
    for (let i = 0; i < 3; i++) {
        await page.clock.fastForward(30000);
        await expect(page.locator('[data-notification-count]')).toHaveCount(1);
    }
    state.notifications = [notification('one'), notification('two')];
    await page.clock.fastForward(30000);
    await expect(page.locator('[data-notification-count]')).toHaveText('2');
    state.notifications = [];
    await page.clock.fastForward(30000);
    await expect(page.locator('[data-notification-count]')).toHaveCount(0);
    await expect(page.locator('[data-notification-list]')).toHaveText('Aucune notification');
});

test('mark all read clears the badge and visible row states without navigating or deleting history', async t => {
    const { page, state } = await fixture(t, [notification('one'), notification('two')]);
    await page.locator('#notificationButton').click();
    await expect(page.locator('[data-notification-list] form')).toHaveCount(2);
    await page.getByRole('button', { name: 'Tout marquer comme lu', exact: true }).click();
    await expect(page.locator('[data-notification-count]')).toHaveCount(0);
    await expect(page.locator('[data-notification-read-state]')).toHaveText('Lu');
    assert.equal(state.marks, 1);
    assert.equal(page.url(), 'http://notifications.test/fixture');
    await page.clock.fastForward(30000);
    await expect(page.locator('[data-notification-count]')).toHaveCount(0);
});

test('failed writes retain the unread badge and allow retry', async t => {
    const { page, state } = await fixture(t, [notification('one')]);
    state.failMark = true;
    await page.locator('#notificationButton').click();
    await page.getByRole('button', { name: 'Tout marquer comme lu', exact: true }).click();
    await expect(page.locator('[data-notification-error]')).toBeVisible();
    await expect(page.locator('[data-notification-count]')).toHaveText('1');
    await expect(page.locator('[data-notification-read-state]')).toHaveText('Marquer comme lu');
    state.failMark = false;
    await page.getByRole('button', { name: 'Tout marquer comme lu', exact: true }).click();
    await expect(page.locator('[data-notification-count]')).toHaveCount(0);
    await expect(page.locator('[data-notification-error]')).toBeHidden();
});

test('an older pending poll cannot restore the badge after marking notifications read', async t => {
    const { page, state } = await fixture(t, [notification('one')]);
    let release;
    state.holdFetch = new Promise(resolve => { release = resolve; });
    await page.locator('#notificationButton').click();
    await expect.poll(() => state.fetches).toBeGreaterThan(0);
    await page.getByRole('button', { name: 'Tout marquer comme lu', exact: true }).click();
    await expect(page.locator('[data-notification-count]')).toHaveCount(0);
    release();
    state.holdFetch = null;
    await page.clock.fastForward(30000);
    await expect(page.locator('[data-notification-count]')).toHaveCount(0);
});

test('opening the bell does not dismiss messages and fetched text is safely rendered', async t => {
    const message = '<img src=x onerror="window.injected=true">';
    const { page, state } = await fixture(t, [notification('one', message)]);
    await page.locator('#notificationButton').click();
    await expect(page.locator('[data-notification-list] button')).toContainText(message);
    await expect(page.locator('[data-notification-list] img')).toHaveCount(0);
    await expect(page.locator('[data-notification-list] form')).toHaveAttribute('action', 'http://notifications.test/notifications/one/open');
    await expect(page.locator('[data-notification-list] small')).toHaveText('60,00 € – 10/11/2026');
    await expect(page.locator('[data-notification-count]')).toHaveText('1');
    assert.equal(state.marks, 0);
    state.notifications = [];
    await page.evaluate(() => window.dispatchEvent(new Event('focus')));
    await expect(page.locator('[data-notification-count]')).toHaveCount(0);
});
