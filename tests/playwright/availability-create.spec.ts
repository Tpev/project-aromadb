// tests/playwright/availability-create.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Therapist availabilities', () => {
  test('Therapist can create a new availability', async ({ page }) => {
    // 1. Already authenticated via storageState.
    await page.goto('/availabilities/create');

    // 2. Sanity check: URL is correct
    await expect(page).toHaveURL(/\/availabilities\/create/);

    // 3. Kill any global modals/overlays (uf-modal etc.)
    await page.evaluate(() => {
      const modals = document.querySelectorAll('[data-uf-content="modal"], .uf-modal');
      modals.forEach(m => {
        (m as HTMLElement).style.display = 'none';
      });
    });

    // 4. Wait for the form fields
    await page.waitForSelector('#day_of_week');
    await page.waitForSelector('#start_time');
    await page.waitForSelector('#end_time');

    // 5. Select day of week: Lundi → value "0"
    await page.selectOption('#day_of_week', '0');

    // 6. Fill start and end time
    await page.fill('#start_time', '09:00');
    await page.fill('#end_time', '12:00');

    // 7. Close/hide the jQuery timepicker dropdown so it doesn't block clicks
    await page.evaluate(() => {
      const tps = document.querySelectorAll('.ui-timepicker-container');
      tps.forEach(el => {
        (el as HTMLElement).style.display = 'none';
      });
    });

    // 8. Check "Appliquer cette disponibilité à tous les produits"
    const appliesToAll = page.locator('#applies_to_all');
    if (!(await appliesToAll.isChecked())) {
      await appliesToAll.check();
    }

    // 9. Submit the form
    await page
      .getByRole('button', { name: /Ajouter la Disponibilité/i })
      .click();

    // 10. Wait for redirect after save
    await page.waitForLoadState('networkidle');

    // 11. Assert we are back on the availabilities index
    await expect(page).toHaveURL(/\/availabilities/);
  });
});
