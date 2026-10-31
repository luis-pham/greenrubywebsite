import { defineConfig } from '@playwright/test';

// One worker, in order: the scenarios share one throwaway database (tests/e2e/run.sh).
export default defineConfig({
  testDir: '.',
  testMatch: /.*\.spec\.ts/,
  workers: 1,
  fullyParallel: false,
  timeout: 90_000,
  expect: { timeout: 15_000 },
  reporter: [['list']],
  use: { baseURL: 'http://127.0.0.1:8093', locale: 'vi-VN', trace: 'retain-on-failure' },
});
