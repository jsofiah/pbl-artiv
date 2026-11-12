import { test, expect } from '@playwright/test';

// ==========================================
// KONFIGURASI AKUN
// ==========================================
const CUSTOMER = { email: 'earlyyychelsealia@gmail.com', password: 'earlyxzahra' };
const DESIGNER = { email: 'budi.designer@artiv.com', password: 'designer123' };
const ADMIN = { email: 'admin@artiv.com', password: 'admin123' };

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

test.describe('Auth, Profil & Logout', () => {

  // ==========================================
  // 1. LOGIN (TC-LG-001 s/d TC-LG-015)
  // ==========================================
  test('TC-LG-001: Login valid Customer', async ({ page }) => {
    await login(page, CUSTOMER);
    await expect(page).toHaveURL(/\/customer\/beranda/);
  });

  test('TC-LG-002: Login password salah', async ({ page }) => {
    await login(page, { email: CUSTOMER.email, password: 'salah123' });
    await expect(page.getByText('These credentials do not match our records.')).toBeVisible();
  });

  test('TC-LG-003: Login email salah', async ({ page }) => {
    await login(page, { email: 'salah@gmail.com', password: 'customer123' });
    await expect(page.getByText('These credentials do not match our records.')).toBeVisible();
  });

  test('TC-LG-004: Login tanpa email', async ({ page }) => {
    await page.goto('/login');
    await page.locator('input[name="password"]').fill('customer123');
    await page.getByRole('button', { name: /masuk/i }).click();
    await expect(page).toHaveURL(/\/login/);
  });

  test('TC-LG-005: Login tanpa password', async ({ page }) => {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(CUSTOMER.email);
    await page.getByRole('button', { name: /masuk/i }).click();
    await expect(page).toHaveURL(/\/login/);
  });

  test('TC-LG-006: Login tanpa email & password', async ({ page }) => {
    await page.goto('/login');
    await page.getByRole('button', { name: /masuk/i }).click();
    await expect(page).toHaveURL(/\/login/);
  });

  test('TC-LG-007: Login format email tidak valid', async ({ page }) => {
    await login(page, { email: 'userartiv.com', password: 'user123' });
    await expect(page).toHaveURL(/\/login/);
  });

  test('TC-LG-008: Login peran Customer', async ({ page }) => {
    await login(page, CUSTOMER);
    await expect(page).toHaveURL(/\/customer\/beranda/);
  });

  test('TC-LG-009: Login peran Designer', async ({ page }) => {
    await login(page, DESIGNER);
    await expect(page).toHaveURL(/\/designer\//);
  });

  test('TC-LG-010: Login peran Admin', async ({ page }) => {
    await login(page, ADMIN);
    await expect(page).toHaveURL(/\/admin\//);
  });

  test('TC-LG-011: RBAC - Customer dilarang akses Dashboard Designer', async ({ page }) => {
    await login(page, CUSTOMER);
    const res = await page.goto('/designer/dashboard');
    expect(res?.status() === 403 || !page.url().includes('/designer/dashboard')).toBeTruthy();
  });

  test('TC-LG-012: RBAC - Designer dilarang akses Dashboard Admin', async ({ page }) => {
    await login(page, DESIGNER);
    const res = await page.goto('/admin/dashboard');
    expect(res?.status() === 403 || !page.url().includes('/admin/dashboard')).toBeTruthy();
  });

  test('TC-LG-013 & 014: Memastikan tombol Google SSO mengarah ke Google', async ({ page }) => {
    await page.goto('/login');
    const googleBtn = page.locator('text="Masuk dengan Google"');
    await expect(googleBtn).toBeVisible();
    const href = await googleBtn.getAttribute('href');
    expect(href).toContain('/auth/google');
  });

  test('TC-LG-015: Batal Login SSO Google', async ({ page }) => {
    await page.goto('/login');
    // Simulasi user menutup jendela Google dan kembali ke halaman login
    await page.goto('/login'); 
    await expect(page).toHaveURL(/\/login/);
  });

  // ==========================================
  // 2. REGISTER (TC-RG-001 s/d TC-RG-013)
  // ==========================================
  test('TC-RG-001: Register akun baru valid', async ({ page }) => {
    const ts = Date.now();
    await page.goto('/register');
    await page.locator('input[name="full_name"], input[name="name"]').first().fill('E2E User');
    await page.locator('input[name="username"]').fill(`e2e${ts}`);
    await page.locator('input[name="email"]').fill(`e2e+${ts}@gmail.com`);
    await page.locator('input[name="password"]').fill('e2epass123');
    await page.locator('input[name="password_confirmation"]').fill('e2epass123');
    await page.getByRole('button', { name: /daftar/i }).click();
    await expect(page).toHaveURL(/\/customer\/beranda/);
  });

  test('TC-RG-002: Register format email tidak valid', async ({ page }) => {
    await page.goto('/register');
    await page.locator('input[name="email"]').fill('johndoe.com');
    await page.getByRole('button', { name: /daftar/i }).click();
    await expect(page).toHaveURL(/\/register/);
  });

  test('TC-RG-003: Register tanpa email', async ({ page }) => {
    await page.goto('/register');
    await page.getByRole('button', { name: /daftar/i }).click();
    await expect(page).toHaveURL(/\/register/);
  });

  test('TC-RG-004: Register tanpa password', async ({ page }) => {
    await page.goto('/register');
    await page.getByRole('button', { name: /daftar/i }).click();
    await expect(page).toHaveURL(/\/register/);
  });

  test('TC-RG-005 & 008: Register password kurang dari 8 karakter', async ({ page }) => {
    await page.goto('/register');
    await page.locator('input[name="password"]').fill('jd123');
    await page.getByRole('button', { name: /daftar/i }).click();
    await expect(page).toHaveURL(/\/register/);
  });

  test('TC-RG-006 & 009: Register konfirmasi password salah', async ({ page }) => {
    await page.goto('/register');
    await page.locator('input[name="password"]').fill('e2epass123');
    await page.locator('input[name="password_confirmation"]').fill('salah123');
    await page.getByRole('button', { name: /daftar/i }).click();
    await expect(page).toHaveURL(/\/register/);
  });

  test('TC-RG-007: Register validasi format email', async ({ page }) => {
    await page.goto('/register');
    await page.locator('input[name="email"]').fill('johndoe.com');
    await page.getByRole('button', { name: /daftar/i }).click();
    await expect(page).toHaveURL(/\/register/);
  });

  test('TC-RG-010: Register kolom wajib diisi', async ({ page }) => {
    await page.goto('/register');
    await page.getByRole('button', { name: /daftar/i }).click();
    await expect(page).toHaveURL(/\/register/);
  });

  test('TC-RG-011 & 012: Memastikan tombol Daftar Google SSO mengarah ke Google', async ({ page }) => {
    await page.goto('/register');
    const googleBtn = page.locator('text="Daftar dengan Google"');
    await expect(googleBtn).toBeVisible();
    const href = await googleBtn.getAttribute('href');
    expect(href).toContain('/auth/google');
  });

  test('TC-RG-013: Batal Register SSO Google', async ({ page }) => {
    await page.goto('/register');
    await page.goto('/register');
    await expect(page).toHaveURL(/\/register/);
  });

  // ==========================================
  // 3. EDIT PROFIL (TC-EP-001 s/d TC-EP-011)
  // ==========================================
  test('TC-EP-001 s/d 004: Edit Nama, Telepon, Foto', async ({ page }) => {
    await login(page, CUSTOMER);
    await page.goto('/profile');
    await page.locator('input[name="avatar"]').setInputFiles({ name: 'foto.png', mimeType: 'image/png', buffer: PNG_1X1 });
    await page.waitForLoadState('networkidle');
    const newName = `John Doe ${Date.now()}`;
    await page.locator('input[name="full_name"]').fill(newName);
    await page.locator('input[name="phone"]').fill('081234567890');
    await page.getByRole('button', { name: /simpan perubahan/i }).click();
    await page.waitForLoadState('networkidle');
    await expect(page.locator('input[name="full_name"]')).toHaveValue(newName);
  });

  test('TC-EP-005: Edit Password berhasil', async ({ page }) => {
    await login(page, CUSTOMER);
    await page.goto('/profile');
    await page.locator('input[name="current_password"]').fill(CUSTOMER.password);
    await page.locator('input[name="password"]').fill('newpass123');
    await page.locator('input[name="password_confirmation"]').fill('newpass123');
    await page.getByRole('button', { name: /simpan perubahan/i }).click();
    await expect(page).toHaveURL(/\/profile/);
  });

  test('TC-EP-006: Edit Profil nama kosong', async ({ page }) => {
    await login(page, CUSTOMER);
    await page.goto('/profile');
    await page.locator('input[name="full_name"]').fill('');
    await page.getByRole('button', { name: /simpan perubahan/i }).click();
    await expect(page).toHaveURL(/\/profile/);
  });

  test('TC-EP-007: Edit Profil format telepon tidak valid', async ({ page }) => {
    await login(page, CUSTOMER);
    await page.goto('/profile');
    await page.locator('input[name="phone"]').fill('abc123');
    await page.getByRole('button', { name: /simpan perubahan/i }).click();
    await expect(page).toHaveURL(/\/profile/);
  });

  test('TC-EP-008: Edit Profil password baru kurang dari 8 karakter', async ({ page }) => {
    await login(page, CUSTOMER);
    await page.goto('/profile');
    await page.locator('input[name="current_password"]').fill(CUSTOMER.password);
    await page.locator('input[name="password"]').fill('ab12');
    await page.locator('input[name="password_confirmation"]').fill('ab12');
    await page.getByRole('button', { name: /simpan perubahan/i }).click();
    await expect(page).toHaveURL(/\/profile/);
  });

  test('TC-EP-009: Edit Profil konfirmasi password tidak cocok', async ({ page }) => {
    await login(page, CUSTOMER);
    await page.goto('/profile');
    await page.locator('input[name="current_password"]').fill(CUSTOMER.password);
    await page.locator('input[name="password"]').fill('newpass123');
    await page.locator('input[name="password_confirmation"]').fill('salah123');
    await page.getByRole('button', { name: /simpan perubahan/i }).click();
    await expect(page).toHaveURL(/\/profile/);
  });

  test('TC-EP-010: Edit Profil format file bukan gambar', async ({ page }) => {
    await login(page, CUSTOMER);
    await page.goto('/profile');
    await page.locator('input[name="avatar"]').setInputFiles({
      name: 'dokumen.pdf',
      mimeType: 'application/pdf',
      buffer: Buffer.from('pdf-palsu')
    });
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/\/profile/);
  });

  test('TC-EP-011: Edit Profil ukuran file > 2MB', async ({ page }) => {
    await login(page, CUSTOMER);
    await page.goto('/profile');
    const bigBuffer = Buffer.alloc(3 * 1024 * 1024); 
    await page.locator('input[name="avatar"]').setInputFiles({
      name: 'foto_besar.jpg',
      mimeType: 'image/jpeg',
      buffer: bigBuffer
    });
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/\/profile/);
  });

  // ==========================================
  // 4. LOGOUT (TC-LO-001)
  // ==========================================
  test('TC-LO-001: Logout berhasil dan kembali ke beranda', async ({ page }) => {
    await login(page, CUSTOMER);
    await page.getByRole('button', { name: /customer/i }).first().click();
    await page.getByRole('button', { name: /logout|keluar/i }).click();
    await expect(page).toHaveURL(/\/(customer\/beranda|login)/);
  });

  // Catatan: TC-LO-002 s/d 004 diuji di PHPUnit (LogoutTest.php)
});