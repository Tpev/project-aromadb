// tests/playwright/appointment-create-therapist.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Therapist-created appointments', () => {
  test('Therapist can create an appointment from the therapist UI using real availabilities', async ({ page }) => {
    // 1. Already authenticated via storageState
    await page.goto('/appointments/create');

    // 2. Sanity check
    await expect(page).toHaveURL(/\/appointments\/create/);

    // 3. Kill any global overlays (uf-modal, etc.)
    await page.evaluate(() => {
      const modals = document.querySelectorAll('[data-uf-content="modal"], .uf-modal');
      modals.forEach(m => {
        (m as HTMLElement).style.display = 'none';
      });
    });

    // 4. Wait for form to be ready
    await page.waitForSelector('select[name="client_profile_id"]');
    await page.waitForSelector('#product_name');

    // -----------------------------
    // CLIENT
    // -----------------------------
    const clientOptions = page.locator('select[name="client_profile_id"] option');
    const clientCount = await clientOptions.count();
    let clientValue: string | null = null;
    let clientLabel: string | null = null;

    for (let i = 0; i < clientCount; i++) {
      const option = clientOptions.nth(i);
      const val = await option.getAttribute('value');
      const label = (await option.innerText()).trim();
      if (val && val !== '' && val !== 'new') {
        clientValue = val;
        clientLabel = label;
        break;
      }
    }

    if (!clientValue || !clientLabel) {
      throw new Error('No existing client found in client_profile_id select.');
    }

    await page.selectOption('select[name="client_profile_id"]', clientValue);

    // -----------------------------
    // PRESTATION
    // -----------------------------
    const productOptions = page.locator('#product_name option');
    const productCount = await productOptions.count();
    let productNameValue: string | null = null;
    let productLabel: string | null = null;

    for (let i = 0; i < productCount; i++) {
      const option = productOptions.nth(i);
      const val = await option.getAttribute('value');
      const label = (await option.innerText()).trim();
      if (val && val !== '') {
        productNameValue = val;
        productLabel = label;
        break;
      }
    }

    if (!productNameValue || !productLabel) {
      throw new Error('No product found in #product_name select.');
    }

    await page.selectOption('#product_name', productNameValue);

    // Wait for consultation mode to appear
    await page.waitForSelector('#consultation_mode');

    // -----------------------------
    // MODE (Visio / Domicile / Cabinet)
    // -----------------------------
    const modeOptions = page.locator('#consultation_mode option');
    const modeCount = await modeOptions.count();
    let modeValue: string | null = null;
    let modeSlug: string | null = null;

    for (let i = 0; i < modeCount; i++) {
      const opt = modeOptions.nth(i);
      const v = await opt.getAttribute('value');
      const s = await opt.getAttribute('data-slug');
      if (!v) continue;

      // Prefer non-cabinet modes when possible
      if (s && s !== 'cabinet') {
        modeValue = v;
        modeSlug = s;
        break;
      }
      // Fallback to first mode if nothing better found
      if (!modeValue) {
        modeValue = v;
        modeSlug = s || null;
      }
    }

    if (!modeValue) {
      throw new Error('No consultation mode found in #consultation_mode.');
    }

    await page.selectOption('#consultation_mode', modeValue);

    // -----------------------------
    // MODE-SPECIFIC FIELDS
    // -----------------------------
    if (modeSlug === 'cabinet') {
      // Must pick a cabinet so dates are fetched
      await page.waitForSelector('#practice_location_id', { state: 'visible' });

      const locOptions = page.locator('#practice_location_id option');
      const locCount = await locOptions.count();
      let locValue: string | null = null;

      for (let i = 0; i < locCount; i++) {
        const val = await locOptions.nth(i).getAttribute('value');
        if (val && val !== '') {
          locValue = val;
          break;
        }
      }

      if (!locValue) {
        throw new Error('Mode cabinet selected but no practice location available.');
      }

      await page.selectOption('#practice_location_id', locValue);
    } else if (modeSlug === 'domicile') {
      // Domicile needs an address
      await page.fill('#address', '3 rue des Rendez-Vous, 67000 Strasbourg');
    }

    // -----------------------------
    // DATE (Flatpickr alt input)
    // -----------------------------
    // Flatpickr:
    //  - original #appointment_date input is now type="hidden", with class "flatpickr-input"
    //  - altInput is a *visible* input with placeholder "Sélectionner une date"
    //
    // So we target: input[placeholder="Sélectionner une date"]:not([type="hidden"])
    const dateInput = page
      .locator('input[placeholder="Sélectionner une date"]:not([type="hidden"])')
      .first();

    await expect(dateInput).toBeVisible({ timeout: 10000 });
    await dateInput.click();

    // Wait for at least one enabled day (not disabled / prevMonth / nextMonth)
    const enabledDay = page.locator(
      '.flatpickr-day:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay)'
    ).first();

    await enabledDay.waitFor({ state: 'visible', timeout: 10000 });
    await enabledDay.click();

    // -----------------------------
    // TIME SLOT (real slots)
    // -----------------------------
    // Your JS calls fetchSlots on date change, which should create .time-slot-btn buttons.
    await page.waitForSelector('.time-slot-btn', { timeout: 15000 });

    const firstSlot = page.locator('.time-slot-btn').first();
    const slotTime = await firstSlot.getAttribute('data-time');
    await firstSlot.click();

    if (slotTime) {
      await expect(page.locator('#appointment_time')).toHaveValue(slotTime);
    }

    // -----------------------------
    // NOTES + SUBMIT
    // -----------------------------
    await page.fill(
      '#notes',
      'RDV créé automatiquement par Playwright avec dispo réelle.'
    );

    await page.getByRole('button', { name: /Créer le RDV/i }).click();

    // Wait for redirect
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/\/appointments/);

    // -----------------------------
    // VERIFY ROW IN APPOINTMENTS INDEX
    // -----------------------------
    if (!clientLabel || !productLabel) {
      throw new Error('Missing clientLabel or productLabel for verification.');
    }

    const row = page
      .locator('#appointmentTable tbody tr')
      .filter({ hasText: clientLabel })
      .filter({ hasText: productLabel })
      .first();

    await expect(row).toBeVisible();
    await expect(row).toContainText(clientLabel);
    await expect(row).toContainText(productLabel);
  });
});
