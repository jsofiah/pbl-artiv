import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './e2e',
  fullyParallel: true,
  retries: 0,
  reporter: 'html',
  use: {
    baseURL: 'http://127.0.0.1:8000',
    trace: 'on-first-retry',
    screenshot: 'on',
    actionTimeout: 60000,        // Tunggu 60 detik untuk setiap klik/isi form
    navigationTimeout: 60000,    // Tunggu 60 detik untuk setiap buka halaman
    launchOptions: {
      slowMo: 500,  
    }
  },
  projects: [
    // { name: 'setup', testMatch: '**/auth-setup.spec.js'},
            {
            name: "setup",
            testMatch: /auth\.setup\.js/,
            use: { storageState: undefined },
        },
        {
            name: "chromium",
            use: {
                ...devices["Desktop Chrome"],
                storageState: "e2e/.auth/customer.json",
            },
            dependencies: ["setup"],  
        },
    
    // {
    //   name: 'chromium',
    //   use: { ...devices['Desktop Chrome'],
    //     storageState: 'e2e/.auth/customer.json'
    //   },
    //   dependencies: ['setup'],
    // },

    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },

    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },

    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },

    /* Test against mobile viewports. */
    // {
    //   name: 'Mobile Chrome',
    //   use: { ...devices['Pixel 5'] },
    // },
    // {
    //   name: 'Mobile Safari',
    //   use: { ...devices['iPhone 12'] },
    // },

    /* Test against branded browsers. */
    // {
    //   name: 'Microsoft Edge',
    //   use: { ...devices['Desktop Edge'], channel: 'msedge' },
    // },
    // {
    //   name: 'Google Chrome',
    //   use: { ...devices['Desktop Chrome'], channel: 'chrome' },
    // },
  ],
  webServer: {
    command: 'php artisan serve',
    url: 'http://127.0.0.1:8000',
    reuseExistingServer: true,
    timeout: 120000,
     env: {
    APP_ENV: 'testing',
     },
  },
});