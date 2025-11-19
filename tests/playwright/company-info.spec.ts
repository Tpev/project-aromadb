// tests/playwright/company-info.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Therapist company info configuration', () => {
  test('Therapist can open company info page and save fake data', async ({ page }) => {
    // 1. We are already authenticated via storageState.
    //    Go directly to the company info page.
    await page.goto('/profile/company-info');

    // 2. Ensure we are on the correct page
    await expect(
      page.getByRole('heading', { name: /Informations de l'Entreprise/i })
    ).toBeVisible();

    // 3. Wait for form to be there (just to be safe with Livewire / JS)
    await page.waitForSelector('#company_name');

    // 4. Fill basic company fields
    await page.fill('#company_name', 'Cabinet Test Aroma');
    await page.fill('#company_address', '10 rue des Tests\n67000 Strasbourg');
    await page.fill('#company_email', 'cabinet.test@example.com');
    await page.fill('#company_phone', '0601020304');

    // 5. Bypass Quill: set the hidden input value via JS
    const aboutText =
      'Je suis un thérapeute de test utilisant AromaMade PRO pour gérer mes rendez-vous, mes clients et ma facturation.';
    await page.evaluate((value) => {
      const input = document.querySelector('#about-input') as HTMLInputElement | null;
      if (input) {
        input.value = value;
      }
    }, aboutText);

    // 6. Add some services using the tag system
    await page.fill('#service-input', 'Massage relaxant');
    await page.click('#add-service-btn');
    await page.fill('#service-input', 'Aromathérapie thérapeutique');
    await page.click('#add-service-btn');

    // Check that at least one tag is visible
    await expect(page.getByText('Massage relaxant')).toBeVisible();

    // 7. Profile description
    await page.fill(
      '#profile_description',
      'Aromathérapeute et coach bien-être spécialisé en gestion du stress.'
    );

    // 8. Legal mentions
    await page.fill(
      '#legal_mentions',
      'SIRET 123 456 789 00010 - EI - Capital 1 000 €'
    );

    // 9. Checkboxes (ensure they are enabled)
    const acceptOnline = page.locator('input[name="accept_online_appointments"]');
    if (!(await acceptOnline.isChecked())) {
      await acceptOnline.check();
    }

    const shareAddress = page.locator('input[name="share_address_publicly"]');
    if (!(await shareAddress.isChecked())) {
      await shareAddress.check();
    }

    const sharePhone = page.locator('input[name="share_phone_publicly"]');
    if (!(await sharePhone.isChecked())) {
      await sharePhone.check();
    }

    const shareEmail = page.locator('input[name="share_email_publicly"]');
    if (!(await shareEmail.isChecked())) {
      await shareEmail.check();
    }

    // 10. Minimum notice for booking
    await page.fill('#minimum_notice_hours', '24');

    // 11. Submit the form
    await page
      .getByRole('button', { name: /Enregistrer les Modifications/i })
      .click();

    await page.waitForLoadState('networkidle');

    // 12. Assert the new data is persisted (fields re-populated)
    await expect(page.locator('#company_name')).toHaveValue('Cabinet Test Aroma');
    await expect(page.locator('#company_email')).toHaveValue(
      'cabinet.test@example.com'
    );
    await expect(page.locator('#minimum_notice_hours')).toHaveValue('24');

    // Quick sanity check: one of the services is still visible
    await expect(page.getByText('Massage relaxant')).toBeVisible();
  });
});
