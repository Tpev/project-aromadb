// tests/playwright/client-create.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Therapist client profiles', () => {
  test('Therapist can create a new client profile', async ({ page }) => {
    // 1. Already authenticated via storageState.
    await page.goto('/client_profiles/create');

    // 2. Sanity check: URL
    await expect(page).toHaveURL(/\/client_profiles\/create/);

    // 3. Kill any global modal overlays (uf-modal etc.)
    await page.evaluate(() => {
      const modals = document.querySelectorAll('[data-uf-content="modal"], .uf-modal');
      modals.forEach(m => {
        (m as HTMLElement).style.display = 'none';
      });
    });

    // 4. Wait for the form
    await page.waitForSelector('#first_name');

    // 5. Fill basic identity fields
    const firstName = 'ClientTest';
    const lastName = 'Playwright';
    const fullName = `${firstName} ${lastName}`;

    await page.fill('#first_name', firstName);
    await page.fill('#last_name', lastName);
    await page.fill('#email', 'client.playwright@example.com');
    await page.fill('#phone', '0600000000');
    await page.fill('#birthdate', '1990-01-01');
    await page.fill('#address', '12 rue des Tests, 67000 Strasbourg');

    // 6. Use same names for billing
    const useSameNames = page.locator('#use_same_names');
    await useSameNames.check();

    // Check that billing fields got copied and are readonly
    const firstNameBilling = page.locator('#first_name_billing');
    const lastNameBilling = page.locator('#last_name_billing');

    await expect(firstNameBilling).toHaveValue(firstName);
    await expect(lastNameBilling).toHaveValue(lastName);

    await expect(firstNameBilling).toHaveAttribute('readonly', 'true');
    await expect(lastNameBilling).toHaveAttribute('readonly', 'true');

    // 7. Submit the form
    await page.getByRole('button', { name: /Créer le Profil/i }).click();

    // 8. Wait for redirect to index
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/\/client_profiles/);

    // 9. Check that the new client appears in the list
    await expect(page.getByText(fullName).first()).toBeVisible();
  });
});
