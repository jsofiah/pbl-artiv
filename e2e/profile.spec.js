import { test, expect } from '@playwright/test';
import { URL, SEL, loginAsCustomer, fakeFile, expectBrowserInvalid } from './helpers/auth.js';

// Form di halaman profil ada dua: "Informasi Pribadi" (tombol Simpan Perubahan)
// dan "Keamanan Akun" (tombol Perbarui Password). Upload foto punya form
// sendiri yang otomatis submit begitu file dipilih (tidak ada tombol Simpan).

async function klik(page, locator) {
  await locator.click();
  await page.waitForLoadState('load');
}

test.describe('Edit Profil (S3.1 - S3.2)', () => {
  test.beforeEach(async ({ page }) => {
  await loginAsCustomer(page);

  await page.goto(URL.profil, {
    waitUntil: 'domcontentloaded',
  });

  await expect(SEL.namaLengkap(page)).toBeVisible();
});

  test('TC-EP-001 Edit nama lengkap', async ({ page }) => {
    await SEL.namaLengkap(page).fill('John Doe Updated');
    await klik(page, SEL.btnSimpan(page));
    await page.goto(URL.profil);
    await expect(SEL.namaLengkap(page)).toHaveValue('John Doe Updated');
  });

  test('TC-EP-002 Edit username', async ({ page }) => {
    const baru = `johndoe${Date.now() % 100000}`;
    await SEL.username(page).fill(baru);
    await klik(page, SEL.btnSimpan(page));
    await page.goto(URL.profil);
    await expect(SEL.username(page)).toHaveValue(baru);
  });

  test('TC-EP-003 Edit nomor telepon', async ({ page }) => {
    await SEL.noTelepon(page).fill('081234567890');
    await klik(page, SEL.btnSimpan(page));
    await page.goto(URL.profil);
    await expect(SEL.noTelepon(page)).toHaveValue('081234567890');
  });

  test('TC-EP-004 Unggah foto profil', async ({ page }) => {
    // Form avatar auto-submit saat file dipilih. Hasil: tombol "Hapus Foto" muncul.
    await SEL.inputFoto(page).setInputFiles(fakeFile('foto.jpg', 'image/jpeg', 100 * 1024));
    await expect(SEL.btnHapusFoto(page)).toBeVisible();
  });

  // Mengganti password akun testing beneran. Aktifkan sekali, lalu kembalikan
  // ke customer123 lewat form yang sama.
  test.skip('TC-EP-005 Edit kata sandi', async ({ page }) => {
    await SEL.passwordLama(page).fill('customer123');
    await SEL.passwordBaru(page).fill('customer456');
    await SEL.konfirmasiBaru(page).fill('customer456');
    await klik(page, SEL.btnPerbaruiPassword(page));
    await expect(page).toHaveURL(new RegExp(URL.profil));
  });

  test('TC-EP-006 Nama lengkap kosong', async ({ page }) => {
    await SEL.namaLengkap(page).fill('');
    await SEL.btnSimpan(page).click();
    await expect(page).toHaveURL(new RegExp(URL.profil));
    await expectBrowserInvalid(SEL.namaLengkap(page), 'valueMissing');
  });

  test('TC-EP-007 Format nomor telepon tidak valid', async ({ page }) => {
    // Input phone membuang huruf secara otomatis (oninput), jadi "abc123" -> "123",
    // lalu pattern 10-15 digit gagal -> validasi browser.
    await SEL.noTelepon(page).fill('abc123');
    await expect(SEL.noTelepon(page)).toHaveValue('123');
    await SEL.btnSimpan(page).click();
    await expect(page).toHaveURL(new RegExp(URL.profil));
    await expectBrowserInvalid(SEL.noTelepon(page), 'patternMismatch');
  });

  test('TC-EP-008 Kata sandi baru kurang dari 8 karakter', async ({ page }) => {
    await SEL.passwordLama(page).fill('customer123');
    await SEL.passwordBaru(page).fill('ab12');
    await SEL.konfirmasiBaru(page).fill('ab12');
    await klik(page, SEL.btnPerbaruiPassword(page));
    await expect(SEL.pesanError(page).first()).toBeVisible();
    await expect(SEL.pesanError(page).first()).toContainText(/8|minimal/i);
  });

  test('TC-EP-009 Konfirmasi kata sandi baru tidak sesuai', async ({ page }) => {
    await SEL.passwordLama(page).fill('customer123');
    await SEL.passwordBaru(page).fill('customer456');
    await SEL.konfirmasiBaru(page).fill('customer789');
    await klik(page, SEL.btnPerbaruiPassword(page));
    await expect(SEL.pesanError(page).first()).toBeVisible();
    await expect(SEL.pesanError(page).first()).toContainText(/confirmation|konfirmasi|match|cocok|sesuai/i);
  });

  test('TC-EP-010 Format file foto tidak didukung (PDF)', async ({ page }) => {
    await SEL.inputFoto(page).setInputFiles(fakeFile('dokumen.pdf', 'application/pdf', 50 * 1024));
    await expect(SEL.pesanError(page).first()).toBeVisible();
  });

  test('TC-EP-011 Ukuran file foto melebihi 2 MB', async ({ page }) => {
    await SEL.inputFoto(page).setInputFiles(fakeFile('foto_besar.jpg', 'image/jpeg', 3 * 1024 * 1024));
    await expect(SEL.pesanError(page).first()).toBeVisible();
  });
});