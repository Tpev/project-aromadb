// tests/playwright/onboarding-step1-to-step2.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Onboarding – Step 1 completion flows into Step 2', () => {
  test('After filling company info, dashboard shows Step 2 (and hides Step 1)', async ({ page }) => {
    // We assume storageState is already set to an authenticated therapist
    // (see auth.setup.ts and playwright.config.ts)

    // 1. Go to the company info page (Step 1 CTA target)
    await page.goto('/profile/company-info');

    // Sanity: correct page
    await expect(
      page.getByRole('heading', { name: /Informations de l'Entreprise/i })
    ).toBeVisible();

    // Kill any uf-modal/global overlays if they exist (same pattern as your other specs)
    await page.evaluate(() => {
      const modals = document.querySelectorAll('[data-uf-content="modal"], .uf-modal');
      modals.forEach(m => {
        (m as HTMLElement).style.display = 'none';
      });
    });

    // 2. Fill all fields that control Step 1 checks
    // (IDs are the ones we already use in company-info.spec.ts)

    await page.waitForSelector('#company_name');

    await page.fill('#company_name', 'Cabinet Onboarding Test');
    await page.fill('#company_address', '10 rue des Tests\n67000 Strasbourg');
    await page.fill('#company_email', 'onboarding.cabinet@example.com');
    await page.fill('#company_phone', '0612345678');

    // "À propos" (hidden input used by Quill, same trick as before)
    const aboutText =
      'Texte À propos de test, pour vérifier la complétion de la phase 1.';
    await page.evaluate((value) => {
      const input = document.querySelector('#about-input') as HTMLInputElement | null;
      if (input) {
        input.value = value;
      }
    }, aboutText);

    // Services (tag system)
    await page.fill('#service-input', 'Massage relaxant');
    await page.click('#add-service-btn');

    await page.fill('#service-input', 'Aromathérapie thérapeutique');
    await page.click('#add-service-btn');

    // Just check that at least one service tag is visible
    await expect(page.getByText('Massage relaxant')).toBeVisible();

    // Profile description
    await page.fill(
      '#profile_description',
      'Description de profil de test pour valider l’étape 1.'
    );

    // 3. Save company info
    await page
      .getByRole('button', { name: /Enregistrer les Modifications/i })
      .click();

    await page.waitForLoadState('networkidle');

    // 4. Go to the pro dashboard where onboarding widget lives
    await page.goto('/dashboard-pro');
    await page.waitForLoadState('networkidle');

    // 5. EXPECTED BEHAVIOUR:
    //    - Step 1 block (#step1) is NOT shown anymore
    //    - Step 2 block (#step2) IS visible

    const step1 = page.locator('#step1');
    const step2 = page.locator('#step2');

    // Step 1 should be gone (either not attached or zero count)
    await expect(step1).toHaveCount(0);

    // Step 2 should be present and show the correct title
    await expect(step2).toBeVisible();
    await expect(
      step2.getByText('Étape 2 — Prêt pour les réservations en ligne')
    ).toBeVisible();

    // (Optionally you can also assert the % text exists, but we don't care
    //  if it's 0% or something else yet, so this is enough.)
  });
});
