// e2e/01-auth.spec.js
// Alur Auth & Profil (6 skenario)
// Sumber: Test Case - Kelompok 4 -> sheet Login, Register, Edit Profil
//   1. Login customer valid          -> TC-LG-001 / TC-LG-008
//   2. Login password salah          -> TC-LG-002
//   3. Register akun baru            -> TC-RG-001
//   4. Edit profil                   -> TC-EP-001 / TC-EP-003 / TC-EP-004
//   5. RBAC customer -> /admin       -> TC-DA-003 / TC-LG-011
//   6. RBAC designer -> /customer    -> TC-LG-012 / TC-PS-003
//
// CATATAN: selector memakai atribut name bawaan Laravel (email, password, ...).
// Kalau beda dengan Blade kamu, sesuaikan (cek lewat: npx playwright codegen).

import { test, expect } from '@playwright/test';

const CUSTOMER = {
  email: process.env.E2E_CUSTOMER_EMAIL ?? 'customer1@gmail.com',
  password: process.env.E2E_CUSTOMER_PASSWORD ?? 'customer123',
};
const DESIGNER = {
  email: process.env.E2E_DESIGNER_EMAIL ?? 'designer1@gmail.com',
  password: process.env.E2E_DESIGNER_PASSWORD ?? 'designer123',
};

// PNG 1x1 untuk upload foto tanpa perlu file fixture
const PNG_1X1 = Buffer.from(
  'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
  'base64'
);

async function login(page, { email, password }) {
  await page.goto('/login');
  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);
  await page.getByRole('button', { name: /masuk/i }).click();
}

async function register(page) {
  const ts = Date.now();
  const user = {
    fullName: 'E2E User',
    username: `e2e${ts}`,
    email: `e2e+${ts}@gmail.com`,
    password: 'e2epass123',
  };
  await page.goto('/register');
  // TODO: sesuaikan name field dengan form register Artiv
  await page.locator('input[name="full_name"], input[name="name"]').first().fill(user.fullName);
  await page.locator('input[name="username"]').fill(user.username);
  await page.locator('input[name="email"]').fill(user.email);
  await page.locator('input[name="password"]').fill(user.password);
  await page.locator('input[name="password_confirmation"]').fill(user.password);
  await page.getByRole('button', { name: /daftar/i }).click();
  return user;
}

// Akses ditolak = status 403 ATAU dialihkan dari URL yang dituju
async function expectDenied(page, path) {
  const res = await page.goto(path);
  const denied = res?.status() === 403 || !page.url().includes(path);
  expect(denied, `Akses ke ${path} seharusnya ditolak`).toBeTruthy();
}

test.describe('Auth & Profil', () => {
  test('1. Login customer dengan kredensial valid', async ({ page }) => {
    await login(page, CUSTOMER);
    await expect(page).toHaveURL(/\/customer\/beranda/);
  });

  test('2. Login dengan password salah menampilkan error', async ({ page }) => {
    await login(page, { email: CUSTOMER.email, password: 'salah123' });
    await expect(page).toHaveURL(/\/login/);
    await expect(
      page.getByText('These credentials do not match our records.')
    ).toBeVisible();
  });

  test('3. Register akun baru berhasil', async ({ page }) => {
    await register(page);
    await expect(page).toHaveURL(/\/customer\/beranda/);
  });

  test('4. Edit profil (nama, telepon, foto) tersimpan', async ({ page }) => {
  // Pakai akun baru supaya data seed tidak berubah
  await register(page);
  await expect(page).toHaveURL(/\/customer\/beranda/);

  await page.goto('/profile');

  // 1) Foto dulu: input file auto-submit form avatar, halaman akan reload
  await Promise.all([
    page.waitForResponse(
      (r) => r.url().includes('/profile/avatar') && r.request().method() === 'POST'
    ),
    page.locator('input[name="avatar"]').setInputFiles({
      name: 'foto.png',
      mimeType: 'image/png',
      buffer: PNG_1X1,
    }),
  ]);
  await page.waitForLoadState('load');
  // Tombol "Hapus Foto" hanya muncul kalau avatar berhasil tersimpan
  await expect(page.getByRole('button', { name: /hapus foto/i })).toBeVisible();

  // 2) Nama & telepon (setelah reload, baru diisi)
  const newName = `John Doe Updated ${Date.now()}`;
  await page.locator('input[name="full_name"]').fill(newName);
  await page.locator('input[name="phone"]').fill('081234567890');

  await Promise.all([
    page.waitForResponse(
      (r) => new URL(r.url()).pathname === '/profile' && r.request().method() === 'POST'
    ),
    page.getByRole('button', { name: /simpan perubahan/i }).click(),
  ]);

  // Muat ulang, pastikan nilai benar-benar tersimpan
  await page.reload();
  await expect(page.locator('input[name="full_name"]')).toHaveValue(newName);
  await expect(page.locator('input[name="phone"]')).toHaveValue('081234567890');
});

  test('5. RBAC: customer tidak bisa membuka dashboard admin', async ({ page }) => {
    await login(page, CUSTOMER);
    await expect(page).toHaveURL(/\/customer\/beranda/);
    await expectDenied(page, '/admin/dashboard');
  });

  test('6. RBAC: designer tidak bisa membuka Pesanan Saya customer', async ({ page }) => {
    await login(page, DESIGNER);
    await expect(page).toHaveURL(/\/designer\//);
    await expectDenied(page, '/customer/pesanan');
  });

      test('7. Logout berhasil dan diarahkan ke halaman beranda', async ({ page }) => {
    // Login dulu sebagai customer
    await login(page, CUSTOMER);
    await expect(page).toHaveURL(/\/customer\/beranda/);

    // 1. Klik tombol profil (dropdown) di kanan atas
    // Berdasarkan snapshot: button "L lyly customer"
    await page.getByRole('button', { name: /customer/i }).first().click();

    // 2. Klik tombol Logout yang muncul di dropdown
    await page.getByRole('button', { name: /logout|keluar/i }).click();

    // 3. Pastikan diarahkan kembali ke halaman beranda atau login
    await expect(page).toHaveURL(/\/(customer\/beranda|login)/);
  });
});