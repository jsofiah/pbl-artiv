import { test, expect } from '@playwright/test';

test.describe('07 - Admin Kelola', () => {

  test.beforeEach(async ({ page }) => {
    const adminEmail = process.env.E2E_ADMIN_EMAIL || 'admin@artiv.com';
    const adminPassword = process.env.E2E_ADMIN_PASSWORD || 'admin123';

    await page.goto('/login');
    await page.fill('input[name="email"]', adminEmail);
    await page.fill('input[name="password"]', adminPassword);
    await page.click('button[type="submit"]');
    
    const errorMsg = page.locator('text="These credentials do not match our records."');
    if (await errorMsg.isVisible()) {
        throw new Error(`LOGIN GAGAL: Email (${adminEmail}) atau Password salah!`);
    }

    await page.waitForURL(/admin\/dashboard/);
  });

  test('Skenario 1: Admin bisa membuka halaman Katalog', async ({ page }) => {
    await page.goto('/admin/katalog');
    // Spesifik ke main h1 karena ada 2 h1 (header & main)
    await expect(page.locator('main h1')).toContainText('Manajemen Katalog');
  });

    test('Skenario 2: Admin bisa menambah katalog baru', async ({ page }) => {
    await page.goto('/admin/katalog');
    await page.click('text="Tambah Katalog"');
    
    // Tunggu halaman form terbuka
    await page.waitForURL(/admin\/katalog\/create/);
    
    // Isi Nama Katalog (pakai placeholder sesuai snapshot)
    await page.fill('input[placeholder="Contoh: Desain Logo Premium"]', 'Desain Banner');
    
    // Isi Harga (pakai tipe input number/spinbutton sesuai snapshot)
    await page.fill('input[type="number"]', '100000');
    
    // Klik tombol simpan
    await page.click('button:has-text("Simpan Katalog")');
    
    // Kembali ke halaman katalog dan pastikan data muncul
    await page.waitForURL(/admin\/katalog/);
    await expect(page.locator('body')).toContainText('Desain Banner');
  });

  test('Skenario 3: Admin bisa membuka Manajemen Akun', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    // Teks asli di main adalah "Akun Designer", bukan "Manajemen Akun"
    await expect(page.locator('main h1')).toContainText('Akun Designer');
  });

    test('Skenario 4: Admin bisa membuka modal edit akun', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    
    // Klik tombol Edit pertama yang ditemukan
    await page.click('button:has-text("Edit") >> nth=0');
    
    // Pastikan modal terbuka. Cukup cek apakah teks "Edit" atau "Perbarui" muncul di dalam modal/dialog
    await expect(page.locator('body')).toContainText(/Edit Akun|Perbarui/i);
  });
});