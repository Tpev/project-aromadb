// playwright.config.ts
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  // All Playwright tests live here
  testDir: './tests/playwright',

  // Global timeouts
  timeout: 30 * 1000,
  expect: {
    timeout: 5000,
  },

  // Global defaults for all projects
  use: {
    // Your local dev URL (adapt if you use Valet etc.)
    baseURL: 'http://127.0.0.1:8000',
    headless: true,
  },

  projects: [
    /**
     * 1) Project that runs ONLY the auth setup test.
     * It logs in as the therapist and saves the session
     * into tests/playwright/auth/therapist-auth.json
     */
    {
      name: 'setup-therapist-auth',
      testMatch: /auth\.setup\.ts/,
      use: {
        ...devices['Desktop Chrome'],
      },
    },

    /**
     * 2) Main project for all your real tests.
     * It depends on the setup project so auth runs first,
     * and then reuses the saved storageState (already logged in).
     */
    {
      name: 'therapist',
      dependencies: ['setup-therapist-auth'],
      testMatch: /.*\.spec\.ts/,
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'tests/playwright/auth/therapist-auth.json',
      },
    },
  ],
});
