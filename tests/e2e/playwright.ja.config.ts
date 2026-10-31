import { defineConfig, devices } from '@playwright/test';

// Japanese guest booking flow (i18n phase 2B), against the running local web (:3138) and CMS (:8098, fake gateway,
// clone green_ruby_i18n_jae2e). One worker, in order: the scenarios restart the CMS with different payment locales.
//   npx playwright test -c playwright.ja.config.ts
export default defineConfig({
  testDir: '.',
  testMatch: /ja-guest\.spec\.ts/,
  workers: 1,
  fullyParallel: false,
  timeout: 120_000,
  expect: { timeout: 20_000 },
  reporter: [['list']],
  use: { baseURL: 'http://127.0.0.1:3138', locale: 'ja-JP', trace: 'retain-on-failure' },
  projects: [
    { name: 'desktop', use: { viewport: { width: 1280, height: 900 } } },
    { name: 'mobile-375', use: { ...devices['iPhone 13'], defaultBrowserType: 'chromium', viewport: { width: 375, height: 812 } } },
  ],
});
