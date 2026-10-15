import { test, expect } from '@playwright/test';
import { URL, USER, SEL, login, loginAsCustomer, expectBrowserInvalid } from './helpers/auth.js';

test.describe('Login (S1.1 - S1.3)', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(URL.login);
  });

  test('TC-LG-001 Login dengan kredensial valid', async ({ page }) => {
    await login(page, USER.customer);
    await expect(page).not.toHaveURL(/\/login/);
  });

  test('TC-LG-002 Login dengan kata sandi salah', async ({ page }) => {
    await login(page, { email: USER.customer.email, password: 'salah123' });
    await expect(page.getByText(/credentials do not match|tidak cocok/i)).toBeVisible();
    await expect(page).toHaveURL(/\/login/);
  });

  test('TC-LG-003 Login dengan email salah', async ({ page }) => {
    await login(page, { email: 'salah@gmail.com', password: USER.customer.password });
    await expect(page.getByText(/credentials do not match|tidak cocok/i)).toBeVisible();
    await expect(page).toHaveURL(/\/login/);
  });

  // TC-LG-004 s/d 007: di Blade, input email/password memakai `required` dan
  // type="email", jadi pesan yang muncul adalah tooltip bawaan browser, bukan
  // teks "Alamat email wajib diisi" seperti di dokumen. Test mengecek form
  // tidak terkirim + status validasi browsernya.
  test('TC-LG-004 Login tanpa email', async ({ page }) => {
    await SEL.password(page).fill(USER.customer.password);
    await SEL.btnMasuk(page).click();
    await expect(page).toHaveURL(/\/login/);
    await expectBrowserInvalid(SEL.email(page), 'valueMissing');
  });

  test('TC-LG-005 Login tanpa kata sandi', async ({ page }) => {
    await SEL.email(page).fill(USER.customer.email);
    await SEL.btnMasuk(page).click();
    await expect(page).toHaveURL(/\/login/);
    await expectBrowserInvalid(SEL.password(page), 'valueMissing');
  });

  test('TC-LG-006 Login tanpa email dan kata sandi', async ({ page }) => {
    await SEL.btnMasuk(page).click();
    await expect(page).toHaveURL(/\/login/);
    await expectBrowserInvalid(SEL.email(page), 'valueMissing');
    await expectBrowserInvalid(SEL.password(page), 'valueMissing');
  });

  test('TC-LG-007 Login dengan format email tidak valid', async ({ page }) => {
    await SEL.email(page).fill('userartiv.com');
    await SEL.password(page).fill('user123');
    await SEL.btnMasuk(page).click();
    await expect(page).toHaveURL(/\/login/);
    await expectBrowserInvalid(SEL.email(page), 'typeMismatch');
  });
});

test.describe('Login per peran (S1.2)', () => {
  test('TC-LG-008 Login sebagai Customer', async ({ page }) => {
    await login(page, USER.customer);
    await expect(page).not.toHaveURL(/\/login/);
  });

  test('TC-LG-009 Login sebagai Designer', async ({ page }) => {
    await login(page, USER.designer);
    await expect(page).toHaveURL(/designer/);
  });

  test('TC-LG-010 Login sebagai Admin', async ({ page }) => {
    await login(page, USER.admin);
    await expect(page).toHaveURL(/admin/);
  });

  test('TC-LG-011 Customer tidak bisa akses dashboard Designer', async ({ page }) => {
    await loginAsCustomer(page);
    await page.goto('/designer/dashboard');
    // Bisa 403 "Akses ditolak" atau redirect, sesuaikan dengan aplikasimu
    await expect(page.getByText(/akses ditolak|forbidden|403/i)).toBeVisible();
  });

  test('TC-LG-012 Designer tidak bisa akses dashboard Admin', async ({ page }) => {
    await login(page, USER.designer);
    await page.goto('/admin/dashboard');
    await expect(page.getByText(/akses ditolak|forbidden|403/i)).toBeVisible();
  });
});

test.describe('Login SSO Google (S1.3)', () => {
  // OAuth Google tidak bisa diotomasi dengan stabil (captcha, 2FA).
  // Cukup verifikasi tombolnya ada, sisanya diuji manual dan dilampirkan screenshot.
  test('TC-LG-013 Tombol "Masuk dengan Google" tampil', async ({ page }) => {
    await page.goto(URL.login);
    await expect(SEL.btnGoogle(page)).toBeVisible();
  });

  test.fixme('TC-LG-014 Login Google akun belum terdaftar (manual)', async () => {});
  test.fixme('TC-LG-015 Pembatalan login Google (manual)', async () => {});
});

test.describe('Logout (S4.1)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsCustomer(page);
  });

  async function logout(page) {
    // Kalau tombol logout ada di dalam dropdown user menu, buka dulu
    if (!(await SEL.btnLogout(page).isVisible())) {
      await SEL.userMenu(page).click();
    }
    await SEL.btnLogout(page).click();
  }

  test('TC-LO-001 Logout berhasil dan diarahkan ke beranda/login', async ({ page }) => {
    await logout(page);
    await expect(page).toHaveURL(/\/(login)?$/);
  });

  test('TC-LO-002 Session terhapus setelah logout', async ({ page }) => {
    await logout(page);
    // Laravel membuat session guest baru, jadi buktinya: halaman terproteksi ditolak
    await page.goto(URL.pesananSaya);
    await expect(page).toHaveURL(/\/login/);
  });

  test('TC-LO-003 Tombol back browser tidak membuka halaman terproteksi', async ({ page }) => {
    await page.goto(URL.pesananSaya);
    await logout(page);
    await page.goBack();
    await page.reload();
    await expect(page).toHaveURL(/\/login/);
  });

  test('TC-LO-004 Tidak bisa akses halaman customer setelah logout', async ({ page }) => {
    await logout(page);
    await page.goto(URL.halamanCustomer);
    await expect(page).toHaveURL(/\/login/);
  });
});