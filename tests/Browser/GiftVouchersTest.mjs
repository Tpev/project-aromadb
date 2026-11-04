import { after, before, test } from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { chromium, expect } from '@playwright/test';

const script = await readFile(new URL('../../resources/js/gift-vouchers.js', import.meta.url), 'utf8');
const png = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aB1cAAAAASUVORK5CYII=', 'base64');
let browser;
before(async () => { browser = await chromium.launch({ headless: true }); });
after(async () => { await browser?.close(); });

async function fixture(t) {
    const page = await browser.newPage();
    t.after(() => page.close());
    const state = { posts: 0 };
    await page.route('http://vouchers.test/**', async route => {
        if (route.request().method() === 'POST') {
            state.posts++;
            return route.fulfill({ body: 'Recorded' });
        }
        await route.fulfill({ contentType: 'text/html; charset=utf-8', body: `<!doctype html><meta charset="utf-8">
            <form data-voucher-background-form>
              <select name="gift_voucher_background_mode"><option value="default">Default</option><option value="custom_upload">Custom</option></select>
              <input type="file" name="gift_voucher_background"><input type="checkbox" name="remove_gift_voucher_background">
              <div id="giftVoucherBackgroundPreview" style="width:200px;aspect-ratio:794/1123"></div>
              <p id="giftVoucherBackgroundPreviewHint">Thème par défaut enregistré.</p><p id="giftVoucherBackgroundError" hidden></p>
            </form>
            <form action="/use" method="POST" data-voucher-redeem-form>
              <input type="hidden" name="expected_balance_cents" value="10000">
              <input name="amount_eur" type="number" min="0.01" max="100" step="0.01" value="30" required>
              <button>Utiliser ce bon cadeau</button>
            </form>` });
    });
    await page.goto('http://vouchers.test/fixture');
    await page.addScriptTag({ content: script });
    return { page, state };
}

test('choosing an image activates customization and marks the portrait preview as unsaved', async t => {
    const { page, state } = await fixture(t);
    await page.locator('[name=remove_gift_voucher_background]').check();
    await page.locator('[type=file]').setInputFiles({ name: 'portrait.png', mimeType: 'image/png', buffer: png });
    await expect(page.locator('#giftVoucherBackgroundPreviewHint')).toContainText('Aperçu non enregistré');
    await expect(page.locator('[name=gift_voucher_background_mode]')).toHaveValue('custom_upload');
    await expect(page.locator('[name=remove_gift_voucher_background]')).not.toBeChecked();
    assert.match(await page.locator('#giftVoucherBackgroundPreview').evaluate(el => el.style.backgroundImage), /blob:/);
    assert.equal(state.posts, 0);
    await page.locator('[name=gift_voucher_background_mode]').selectOption('default');
    assert.equal(await page.locator('[type=file]').evaluate(el => el.files.length), 0);
    assert.equal(await page.locator('#giftVoucherBackgroundPreview').evaluate(el => el.style.backgroundImage), '');
    await expect(page.locator('#giftVoucherBackgroundPreviewHint')).toHaveText('Thème par défaut enregistré.');
});

test('unsupported oversized and unreadable images leave the saved preview selected', async t => {
    const { page, state } = await fixture(t);
    for (const [file, message] of [
        [{ name: 'photo.heic', mimeType: 'image/heic', buffer: png }, 'JPG, PNG ou WebP'],
        [{ name: 'large.png', mimeType: 'image/png', buffer: Buffer.alloc(6 * 1024 * 1024 + 1) }, '6 Mo'],
        [{ name: 'broken.png', mimeType: 'image/png', buffer: Buffer.from('broken image') }, 'ne peut pas être lue'],
    ]) {
        await page.locator('[type=file]').setInputFiles(file);
        await expect(page.locator('#giftVoucherBackgroundError')).toBeVisible();
        await expect(page.locator('#giftVoucherBackgroundError')).toContainText(message);
        await expect(page.locator('[name=gift_voucher_background_mode]')).toHaveValue('default');
        assert.equal(await page.locator('[type=file]').evaluate(el => el.files.length), 0);
    }
    assert.equal(state.posts, 0);
});

test('cancelling voucher confirmation sends nothing and accepting displays the deduction and remaining balance', async t => {
    const { page, state } = await fixture(t);
    let message;
    page.once('dialog', async dialog => { message = dialog.message(); await dialog.dismiss(); });
    await page.getByRole('button', { name: 'Utiliser ce bon cadeau', exact: true }).click();
    assert.match(message, /30,00/);
    assert.match(message, /70,00/);
    assert.equal(state.posts, 0);
    page.once('dialog', dialog => dialog.accept());
    await page.getByRole('button', { name: 'Utiliser ce bon cadeau', exact: true }).click();
    await expect(page.locator('body')).toHaveText('Recorded');
    assert.equal(state.posts, 1);
});

test('a second submit is blocked while the confirmed request is in progress', async t => {
    const { page } = await fixture(t);
    page.on('dialog', dialog => dialog.accept());
    const result = await page.locator('[data-voucher-redeem-form]').evaluate(form => {
        const first = new Event('submit', { cancelable: true });
        const second = new Event('submit', { cancelable: true });
        form.dispatchEvent(first);
        form.dispatchEvent(second);
        return { firstPrevented: first.defaultPrevented, secondPrevented: second.defaultPrevented };
    });
    assert.deepEqual(result, { firstPrevented: false, secondPrevented: true });
});
