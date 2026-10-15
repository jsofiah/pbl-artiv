import { test, expect } from '@playwright/test';
import { URL, SEL, uniqueEmail, expectBrowserInvalid } from './helpers/auth.js';

async function isiForm(page, data = {}) {
  const d = {
    nama: 'John Doe',
    username: `johndoe${Date.now()}`,
    email: uniqueEmail(),
    password: 'johndoe123',
    konfirmasi: 'johndoe123',
    ...data,
  };
  await SEL.namaLengkap(page).fill(d.nama);
  await SEL.username(page).fill(d.username);
  await SEL.email(page).fill(d.email);
  await SEL.password(page).fill(d.password);
  await SEL.konfirmasiPassword(page).fill(d.konfirmasi);
  return d;
}

test.describe('Register (S2.1 - S2.3)', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(URL.register);
  });

  test('TC-RG-001 Registrasi dengan data valid', async ({ page }) => {
    await isiForm(page);
    await SEL.btnDaftar(page).click();
    await expect(page).not.toHaveURL(/\/register/);
  });

  // Input register memakai `required` + type="email": validasi pertama
  // dilakukan browser (tooltip), jadi yang dicek adalah status validity-nya.
  test('TC-RG-002 Registrasi dengan format email tidak valid', async ({ page }) => {
    await isiForm(page, { email: 'johndoe.com' });
    await SEL.btnDaftar(page).click();
    await expect(page).toHaveURL(/\/register/);
    await expectBrowserInvalid(SEL.email(page), 'typeMismatch');
  });

  test('TC-RG-003 Registrasi tanpa email', async ({ page }) => {
    await isiForm(page, { email: '' });
    await SEL.btnDaftar(page).click();
    await expect(page).toHaveURL(/\/register/);
    await expectBrowserInvalid(SEL.email(page), 'valueMissing');
  });

  test('TC-RG-004 Registrasi tanpa kata sandi', async ({ page }) => {
    await isiForm(page, { password: '', konfirmasi: '' });
    await SEL.btnDaftar(page).click();
    await expect(page).toHaveURL(/\/register/);
    await expectBrowserInvalid(SEL.password(page), 'valueMissing');
  });

  test('TC-RG-005 / TC-RG-008 Kata sandi kurang dari 8 karakter', async ({ page }) => {
    await isiForm(page, { password: 'jd123', konfirmasi: 'jd123' });
    await SEL.btnDaftar(page).click();
    await expect(page.getByText(/minimal 8|at least 8/i)).toBeVisible();
    await expect(page).toHaveURL(/\/register/);
  });

  test('TC-RG-006 / TC-RG-009 Konfirmasi kata sandi tidak sesuai', async ({ page }) => {
    await isiForm(page, { password: 'johndoe123', konfirmasi: 'johndoe321' });
    await SEL.btnDaftar(page).click();
    await expect(page.getByText(/confirmation does not match|tidak sesuai|tidak cocok/i)).toBeVisible();
    await expect(page).toHaveURL(/\/register/);
  });

  test('TC-RG-007 Validasi format email', async ({ page }) => {
    await isiForm(page, { email: 'johndoe.com' });
    await SEL.btnDaftar(page).click();
    await expect(page).toHaveURL(/\/register/);
    await expectBrowserInvalid(SEL.email(page), 'typeMismatch');
  });

  test('TC-RG-010 Semua kolom kosong', async ({ page }) => {
    await SEL.btnDaftar(page).click();
    await expect(page).toHaveURL(/\/register/);
    // Semua input required: cek masing-masing ditandai kosong oleh browser
    for (const field of [SEL.namaLengkap, SEL.username, SEL.email, SEL.password, SEL.konfirmasiPassword]) {
      await expectBrowserInvalid(field(page), 'valueMissing');
    }
  });

  test('TC-RG-011 Tombol "Daftar dengan Google" tampil', async ({ page }) => {
    await expect(SEL.btnGoogle(page)).toBeVisible();
  });

  // OAuth Google diuji manual (tidak stabil untuk otomasi)
  test.fixme('TC-RG-012 Registrasi Google akun sudah terdaftar (manual)', async () => {});
  test.fixme('TC-RG-013 Pembatalan registrasi Google (manual)', async () => {});
});