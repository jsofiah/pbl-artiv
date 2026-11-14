// @ts-check
import { test as setup } from '@playwright/test';

const authFile = 'e2e/.auth/customer.json';

setup('authenticate as customer', async ({ page }) => {
  await page.goto('/login');
  await page.fill('input[name="email"]', 'customer1@gmail.com');
  await page.fill('input[name="password"]', 'customer123');
  await page.click('button[type="submit"]');

  await page.waitForURL(/\/customer\/beranda/);

  await page.context().storageState({ path: authFile });
});