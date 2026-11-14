// @ts-check
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  // workers: process.env.CI ? 1 : undefined,
  workers: 1,
  reporter: 'html',
  use: {
    baseURL: 'http://localhost:8000',
    trace: 'on-first-retry',
    // screenshot: 'only-on-failure',
    screenshot: 'on',
    navigationTimeout: 30000,
    actionTimeout: 15000,
  },

  projects: [
    // 1. Setup project: hanya auth-setup.spec.js
    {
      name: 'setup',
      testMatch: /auth-setup\.spec\.js/,
      use: { storageState: undefined },
    },

    // 2. Chromium: semua test KECUALI auth-setup.spec.js
    {
      name: 'chromium',
      testMatch: /^(?!.*auth-setup\.spec\.js).*\.spec\.js$/,
      use: {
        ...devices['Desktop Chrome'],
        // storageState: 'e2e/.auth/customer.json',
      },
      dependencies: ['setup'],
    },
  ],
});