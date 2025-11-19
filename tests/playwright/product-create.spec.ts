// tests/playwright/product-create.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Therapist products (prestations)', () => {
  test('Therapist can create a new prestation', async ({ page }) => {
    // 1. Already authenticated via storageState (see playwright.config.ts)
    await page.goto('/products/create');

    // 2. Basic sanity check on URL
    await expect(page).toHaveURL(/\/products\/create/);

    // 3. Kill any global modal overlays (uf-modal, etc.)
    await page.evaluate(() => {
      const modals = document.querySelectorAll('[data-uf-content="modal"], .uf-modal');
      modals.forEach(m => {
        (m as HTMLElement).style.display = 'none';
      });
    });

    // 4. Wait for form to be ready
    await page.waitForSelector('#name');

    // 5. Fill basic fields
    const productName = 'Consultation test Playwright';

    await page.fill('#name', productName);
    await page.fill(
      '#description',
      'Une prestation de test créée automatiquement par Playwright pour vérifier le flux de création.'
    );
    await page.fill('#price', '60');
    await page.fill('#tax_rate', '0');
    await page.fill('#duration', '60');

    // 6. Mode de prestation (Visio / À domicile / Dans le cabinet)
    await page.selectOption('#mode', 'visio'); // or 'dans_le_cabinet', 'adomicile'

    // 7. Collecter le paiement durant la prise de RDV
    const collectPayment = page.locator('#collect_payment');
    if (!(await collectPayment.isChecked())) {
      await collectPayment.check();
    }

    // 8. Visible sur le portail
    const visibleInPortal = page.locator('#visible_in_portal');
    if (!(await visibleInPortal.isChecked())) {
      await visibleInPortal.check();
    }

    // 9. Peut être réservé en ligne
    const canBeBookedOnline = page.locator('#can_be_booked_online');
    if (!(await canBeBookedOnline.isChecked())) {
      await canBeBookedOnline.check();
    }

    // 10. Ordre d’affichage
    await page.fill('#display_order', '1');

    // 11. Options avancées
    // Ouvre le bloc "Options avancées"
    await page.getByRole('button', { name: /Options avancées/i }).click();

    // Attendre que les champs avancés apparaissent
    await page.waitForSelector('#max_per_day');

    // Remplir quelques options avancées
    await page.fill('#max_per_day', '3');
    const requiresEmargement = page.locator('#requires_emargement');
    if (!(await requiresEmargement.isChecked())) {
      await requiresEmargement.check();
    }

    // 12. Submit the form
    await page
      .getByRole('button', { name: /Créer la Prestation/i })
      .click();

    // 13. Wait for redirect
    await page.waitForLoadState('networkidle');

    // 14. Assert we are on the products index
    await expect(page).toHaveURL(/\/products/);

    // 15. Check that the new prestation name is visible – use a specific element
    // Option A: the heading
    await expect(
      page.getByRole('heading', { name: productName })
    ).toBeVisible();

    // (If you prefer the value in the list, you could instead do:)
    // await expect(page.getByText(productName, { exact: true }).first()).toBeVisible();
  });
});
