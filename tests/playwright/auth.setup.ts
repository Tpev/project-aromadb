// tests/playwright/auth.setup.ts
import { test } from '@playwright/test';

test('authenticate as therapist and save storage state', async ({ page }) => {
  // Go to login page
  await page.goto('/login');

  // Fill credentials for the seeded therapist user
  await page.fill('input[name="email"]', 'therapist@test.aromamade.local');
  await page.fill('input[name="password"]', 'password123');

  // Submit the form
  await page.click('button[type="submit"]');

  // Wait for the login redirect to complete
  await page.waitForLoadState('networkidle');

  // Save the authenticated browser state (cookies, localStorage, etc.)
  await page.context().storageState({
    path: 'tests/playwright/auth/therapist-auth.json',
  });
});
