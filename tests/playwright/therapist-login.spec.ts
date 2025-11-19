// tests/playwright/therapist-login.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Therapist area', () => {
  test('Therapist can access the dashboard when authenticated', async ({ page }) => {
    // Because we use storageState in the config,
    // the therapist is already logged in here.
    await page.goto('/dashboard-pro'); // adjust if your route is different

    // Verify URL
    await expect(page).toHaveURL(/\/dashboard-pro/i);

    // Verify the dashboard nav link is visible
    await expect(
      page.getByRole('link', { name: /Tableau de Bord/i })
    ).toBeVisible();
  });
});
