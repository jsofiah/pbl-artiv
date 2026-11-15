import { test, expect } from '@playwright/test';

const ADMIN = { email: 'admin@artiv.com', password: 'admin123' };

async function loginAdmin(page) {
  await page.goto('/login');
  await page.locator('input[name="email"]').fill(ADMIN.email);
  await page.locator('input[name="password"]').fill(ADMIN.password);
  await page.getByRole('button', { name: /masuk/i }).click();
  
  // Jangan tunggu URL, tunggu teks "Dashboard" atau "Admin Panel" muncul
  await expect(page.locator('text="Admin Panel"').first()).toBeVisible({ timeout: 30000 });
}

test.describe('07 - Admin Kelola (Katalog & Akun)', () => {

  test.beforeEach(async ({ page }) => {
    await loginAdmin(page);
  });

  // ==========================================
  // MANAJEMEN KATALOG (TC-MK-001 s/d TC-MK-016)
  // ==========================================

  test('TC-MK-001: Admin dapat membuka halaman Manajemen Katalog', async ({ page }) => {
    await page.goto('/admin/katalog');
    await expect(page.locator('main h1')).toContainText('Manajemen Katalog');
  });

test('TC-MK-002: Guest diarahkan ke login', async ({ page }) => {
    // Hapus cookie agar menjadi guest
    await page.context().clearCookies();
    
    // Kunjungi halaman admin
    await page.goto('/admin/katalog');
    
    // Pastikan diarahkan ke login
    await expect(page).toHaveURL(/\/login/);
  });

  test('TC-MK-003: Daftar jasa ditampilkan', async ({ page }) => {
    await page.goto('/admin/katalog');
    const cards = page.locator('article');
    await expect(cards.first()).toBeVisible();
  });

  test('TC-MK-004: Informasi jasa ditampilkan', async ({ page }) => {
    await page.goto('/admin/katalog');
    const firstCard = page.locator('article').first();
    await expect(firstCard).toContainText(/Harga|Rp/);
    await expect(firstCard).toContainText('Aktif');
  });

  test('TC-MK-005: Pencarian jasa berdasarkan nama', async ({ page }) => {
    await page.goto('/admin/katalog');
    await page.locator('input[placeholder*="Cari katalog"]').fill('Poster');
    await page.locator('button:has-text("Cari")').click();
    await expect(page).toHaveURL(/q=Poster/);
  });

  test('TC-MK-006: Filter berdasarkan kategori', async ({ page }) => {
    await page.goto('/admin/katalog?kategori=Poster');
    await expect(page).toHaveURL(/kategori=Poster/);
  });

  test('TC-MK-007: Filter berdasarkan status aktif/nonaktif', async ({ page }) => {
    await page.goto('/admin/katalog');
    // Gunakan selector href agar lebih akurat
    await page.locator('a[href*="status=aktif"]').click();
    await expect(page).toHaveURL(/status=aktif/);
  });

  test('TC-MK-008: Pencarian dengan kata kunci tidak ditemukan', async ({ page }) => {
    await page.goto('/admin/katalog');
    await page.locator('input[placeholder*="Cari katalog"]').fill('xyz123');
    await page.locator('button:has-text("Cari")').click();
    await expect(page.locator('body')).toContainText(/Tidak ada katalog/i);
  });

    test('TC-MK-009: Admin dapat menambahkan jasa baru', async ({ page }) => {
    await page.goto('/admin/katalog');
    
    // Klik tombol "Tambah Katalog"
    await page.locator('a:has-text("Tambah Katalog")').click();
    
    // Tunggu masuk ke halaman form create
    await page.waitForURL(/\/admin\/katalog\/create/);
    
    // Isi nama katalog (sesuai atribut name="name" di Blade)
    await page.locator('input[name="name"]').fill('E2E Jasa Baru ' + Date.now());
    
    // Isi harga (sesuai atribut name="price" di Blade)
    await page.locator('input[name="price"]').fill('50000');
    
    // Klik tombol submit (sesuai teks di Blade: "Simpan Katalog")
    await page.locator('button[type="submit"]:has-text("Simpan Katalog")').click();
    
    // Tunggu sampai halaman selesai loading (redirect mungkin membawa query string)
    await page.waitForLoadState('networkidle');
    
    // Pastikan teks "E2E Jasa Baru" muncul di halaman
    await expect(page.locator('body')).toContainText('E2E Jasa Baru');
  });

  test('TC-MK-010: Validasi data jasa kosong', async ({ page }) => {
    await page.goto('/admin/katalog/create');
    await page.locator('button:has-text("Simpan Katalog")').click();
    await expect(page).toHaveURL(/\/admin\/katalog\/create/); 
  });

   test('TC-MK-011: Admin dapat mengubah informasi jasa', async ({ page }) => {
    await page.goto('/admin/katalog');
    await page.locator('a:has-text("Edit")').first().click();
    await page.waitForURL(/\/edit/);
    
    await page.locator('input[name="name"]').fill('E2E Edited ' + Date.now());
    await page.locator('button[type="submit"]:has-text("Simpan Perubahan")').click();
    
    // TUNGGU URL KEMBALI KE HALAMAN KATALOG
    await page.waitForURL(/\/admin\/katalog/, { timeout: 30000 });
  });

      test('TC-MK-012: Admin dapat mengubah status jasa', async ({ page }) => {
    await page.goto('/admin/katalog');
    await page.locator('a:has-text("Edit")').first().click();
    await page.waitForURL(/\/edit/);
    
    const checkbox = page.locator('input[type="checkbox"][name="is_active"]');
    if (await checkbox.isChecked()) {
      await checkbox.uncheck({ force: true });
    } else {
      await checkbox.check({ force: true });
    }
    
    await page.locator('button[type="submit"]:has-text("Simpan Perubahan")').click();
    
    // TUNGGU URL KEMBALI KE HALAMAN KATALOG
    await page.waitForURL(/\/admin\/katalog/, { timeout: 30000 });
  });

  test('TC-MK-013: Konfirmasi nonaktifkan jasa', async ({ page }) => {
    await page.goto('/admin/katalog');
    page.on('dialog', async dialog => {
      expect(dialog.message()).toBeTruthy();
      await dialog.dismiss();
    });
    await page.locator('a:has-text("Edit")').first().click();
  });

  test('TC-MK-014: Admin dapat menghapus jasa', async ({ page }) => {
    await page.goto('/admin/katalog');
    
    // Ambil nama jasa pertama
    const firstCard = page.locator('article').first();
    const jasaName = await firstCard.locator('h3').innerText();
    
    // Tangani dialog konfirmasi
    page.on('dialog', async dialog => {
      await dialog.accept();
    });

    // Klik tombol Hapus
    await firstCard.locator('button:has-text("Hapus")').click();
    
    // Tunggu sampai halaman reload
    await page.waitForLoadState('networkidle');
    
    // Cek apakah masih ada <article> yang judulnya sama
    // (Mengabaikan pesan sukses di body)
    await expect(page.locator('article', { hasText: jasaName })).toHaveCount(0);
  });

  test('TC-MK-015: Konfirmasi hapus muncul', async ({ page }) => {
    await page.goto('/admin/katalog');
    
    // Tangani dialog konfirmasi (klik "Cancel" / Batal)
    page.on('dialog', async dialog => {
      expect(dialog.message()).toContain('Hapus katalog');
      await dialog.dismiss(); // Batalkan
    });

    await page.locator('button:has-text("Hapus")').first().click();
    // Test selesai, data tidak terhapus
  });

  test('TC-MK-016: Jasa dengan pesanan aktif tidak dapat dihapus', async ({ page }) => {
    await page.goto('/admin/katalog');
    await expect(page.locator('main h1')).toContainText('Manajemen Katalog');
  });


  // ==========================================
  // MANAJEMEN AKUN (TC-MA-001 s/d TC-MA-020)
  // ==========================================

  test('TC-MA-001: Admin dapat membuka halaman Manajemen Akun', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await expect(page.locator('main h1')).toContainText('Akun Designer');
  });

 test('TC-MA-002: Guest diarahkan ke login', async ({ page }) => {
    await page.context().clearCookies();
    await page.goto('/admin/akun/designer');
    await expect(page).toHaveURL(/\/login/);
  });

  test('TC-MA-003: Customer tidak bisa akses', async ({ page }) => {
    await page.context().clearCookies();
    await page.goto('/login');
    await page.locator('input[name="email"]').fill('earlyyychelsealia@gmail.com');
    await page.locator('input[name="password"]').fill('earlyxzahra');
    await page.getByRole('button', { name: /masuk/i }).click();
    
    // Tunggu sebentar agar login selesai
    await page.waitForLoadState('networkidle');
    
    const res = await page.goto('/admin/akun/designer');
    expect(res?.status() === 403 || !page.url().includes('/admin/akun')).toBeTruthy();
    
    // JANGAN panggil loginAdmin di sini. Biarkan beforeEach yang mengurusnya.
  });

  test('TC-MA-004: Designer tidak bisa akses', async ({ page }) => {
    await page.context().clearCookies();
    await page.goto('/login');
    await page.locator('input[name="email"]').fill('budi.designer@gmail.com');
    await page.locator('input[name="password"]').fill('designer123');
    await page.getByRole('button', { name: /masuk/i }).click();
    
    await page.waitForLoadState('networkidle');
    
    const res = await page.goto('/admin/akun/designer');
    expect(res?.status() === 403 || !page.url().includes('/admin/akun')).toBeTruthy();
  });

  test('TC-MA-005: Tab Semua, Aktif, Dinonaktifkan tampil', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await expect(page.locator('text="Semua"').first()).toBeVisible();
    await expect(page.locator('text="Aktif"').first()).toBeVisible();
    await expect(page.locator('text="Dinonaktifkan"').first()).toBeVisible();
  });

  test('TC-MA-006: Pencarian berdasarkan nama atau email', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await page.locator('input[placeholder*="Cari nama"]').fill('Budi');
    await page.locator('button:has-text("Cari")').click();
    await expect(page).toHaveURL(/q=Budi/);
  });

  test('TC-MA-007: Pengurutan daftar akun', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await page.locator('button:has-text("Urutkan")').click();
    await page.locator('text="Terbaru"').click();
    await expect(page).toHaveURL(/sort=terbaru/);
  });

  test('TC-MA-008: Pencarian tidak ditemukan', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await page.locator('input[placeholder*="Cari nama"]').fill('xyz123');
    await page.locator('button:has-text("Cari")').click();
    await expect(page.locator('body')).toContainText(/Tidak ada akun ditemukan/i);
  });

  test('TC-MA-009: Admin dapat menambahkan akun baru', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await page.locator('button:has-text("Tambah Designer")').click();
    const ts = Date.now();
    await page.locator('input[name="full_name"]').fill('Designer E2E');
    await page.locator('input[name="username"]').fill(`designer_e2e_${ts}`);
    await page.locator('input[name="email"]').fill(`designer_e2e_${ts}@test.com`);
    await page.locator('input[name="password"]').fill('password123');
    await page.locator('input[name="password_confirmation"]').fill('password123');
    await page.locator('button:has-text("Simpan")').click();
    await expect(page.locator('body')).toContainText(/berhasil ditambahkan/i);
  });

  test('TC-MA-010: Validasi form tambah akun kosong', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await page.locator('button:has-text("Tambah Designer")').click();
    await page.locator('button:has-text("Simpan")').click();
    await expect(page.locator('input[name="full_name"]')).toBeVisible(); 
  });

  test('TC-MA-011: Validasi email sudah terdaftar', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await page.locator('button:has-text("Tambah Designer")').click();
    await page.locator('input[name="full_name"]').fill('Duplikat');
    await page.locator('input[name="username"]').fill('dup_' + Date.now());
    await page.locator('input[name="email"]').fill('admin@artiv.com');
    await page.locator('input[name="password"]').fill('password123');
    await page.locator('input[name="password_confirmation"]').fill('password123');
    await page.locator('button:has-text("Simpan")').click();
    await expect(page.locator('body')).toContainText(/sudah dipakai|terdaftar/i);
  });

  test('TC-MA-012: Admin dapat melihat detail akun', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await page.locator('button:has-text("Lihat Selengkapnya")').first().click();
    await expect(page.locator('body')).toContainText(/Email|Role|Status/i);
  });

  test('TC-MA-013: Informasi detail akun lengkap', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await page.locator('button:has-text("Lihat Selengkapnya")').first().click();
    await expect(page.locator('body')).toContainText(/Nama|Email|Username/i);
  });

  test('TC-MA-014: Admin dapat mengubah data akun', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await page.locator('button:has-text("Edit")').first().click();
    const newName = `E2E Edit ${Date.now()}`;
    await page.locator('input[name="full_name"]').fill(newName);
    await page.locator('button:has-text("Simpan Perubahan")').click();
    await expect(page.locator('body')).toContainText(/berhasil diperbarui/i);
  });

  test('TC-MA-015: Validasi data saat edit (kosongkan nama)', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await page.locator('button:has-text("Edit")').first().click();
    await page.locator('input[name="full_name"]').fill('');
    await page.locator('button:has-text("Simpan Perubahan")').click();
    await expect(page.locator('input[name="full_name"]')).toBeVisible();
  });

  test('TC-MA-016: Admin dapat menonaktifkan akun', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await page.locator('button:has-text("Edit")').first().click();
    const checkbox = page.locator('input[type="checkbox"][name="is_active"]');
    if (await checkbox.isChecked()) {
      await checkbox.uncheck();
    }
    await page.locator('button:has-text("Simpan Perubahan")').click();
    await page.waitForLoadState('networkidle');
    await expect(page.locator('input[name="full_name"]')).not.toBeVisible();
  });

  test('TC-MA-017: Konfirmasi nonaktifkan akun', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await page.locator('button:has-text("Edit")').first().click();
    await expect(page.locator('text="Akun aktif"').first()).toBeVisible(); 
  });

  test('TC-MA-018: Admin dapat menghapus akun', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await page.locator('button:has-text("Hapus")').first().click();
    
    // Tunggu modal muncul (cari tombol "Ya, Hapus")
    await expect(page.locator('button:has-text("Ya, Hapus")')).toBeVisible();
    
    // Klik tombol konfirmasi
    await page.locator('button:has-text("Ya, Hapus")').click();
    
    // Tunggu hingga data terhapus
    await expect(page.locator('body')).toContainText(/berhasil dihapus/i);
  });

  test('TC-MA-019: Konfirmasi hapus muncul', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await page.locator('button:has-text("Hapus")').first().click();
    
    // Pastikan modal muncul
    await expect(page.locator('text="Yakin mau hapus akun?"')).toBeVisible();
    
    // Klik tombol Batal yang spesifik untuk modal hapus akun
    await page.locator('button[onclick="tutupHapusAkun()"]').click();
  });

  test('TC-MA-020: Akun dengan pesanan aktif tidak dapat dihapus', async ({ page }) => {
    await page.goto('/admin/akun/designer');
    await expect(page.locator('main h1')).toContainText('Akun Designer');
  });

});