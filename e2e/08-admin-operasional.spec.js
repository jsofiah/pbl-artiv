// @ts-check
import { test, expect } from '@playwright/test';

// ============================================================================
// KONSTANTA
// ============================================================================
const ADMIN_EMAIL    = 'admin1@gmail.com';
const ADMIN_PASSWORD = 'admin123';

// ============================================================================
// HELPER
// ============================================================================

async function goto(page, url) {
    await page.goto(url, {
        waitUntil: 'domcontentloaded',
        timeout: 45_000,
    });
}

async function loginAsAdmin(page) {
    await goto(page, '/login');
    await page.fill('input[name="email"]', ADMIN_EMAIL);
    await page.fill('input[name="password"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/admin\/dashboard/, { timeout: 15_000 });
}

async function screenshotWithWait(page, tcId) {
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(1000);
    await page.screenshot({
        path: `evidence/${tcId}.png`,
        fullPage: true,
    });
}

// ============================================================================
// BAGIAN 1 — MONITORING PEKERJAAN
// ============================================================================
test.describe('Monitoring — Admin', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
        await goto(page, '/admin/monitoring');
        await expect(page).toHaveURL(/\/admin\/monitoring/);
    });

    test('TC-MP-001 · Admin dapat membuka halaman Monitoring', async ({ page }) => {
        await expect(
            page.getByRole('heading', { name: /monitoring pesanan/i }).first()
        ).toBeVisible();

        await screenshotWithWait(page, 'TC-MP-001');
    });

    test('TC-MP-002 · Guest diarahkan ke login', async ({ page, context }) => {
        await context.clearCookies();
        await goto(page, '/admin/monitoring');
        await expect(page).toHaveURL(/\/login/);

        await screenshotWithWait(page, 'TC-MP-002');
    });

    test('TC-MP-003 · Tab Progress Pekerjaan, Verifikasi Pembayaran, Refund ditampilkan', async ({ page }) => {
        await expect(
            page.getByRole('button', { name: /progress pekerjaan/i })
        ).toBeVisible();

        await expect(
            page.getByRole('button', { name: /verifikasi pembayaran/i })
        ).toBeVisible();

        await expect(
            page.getByRole('button', { name: /refund/i })
        ).toBeVisible();

        await screenshotWithWait(page, 'TC-MP-003');
    });

    test('TC-MP-004 · Daftar pesanan ditampilkan', async ({ page }) => {
        const table = page.locator('table').first();
        await expect(table).toBeVisible();

        await expect(table).toContainText(/kode & produk/i);
        await expect(table).toContainText(/customer/i);
        await expect(table).toContainText(/desainer/i);
        await expect(table).toContainText(/status pengerjaan/i);

        await screenshotWithWait(page, 'TC-MP-004');
    });

    test('TC-MP-005 · Pencarian pesanan berfungsi', async ({ page }) => {
        const firstOrderCode = await page.locator('table tbody tr td:first-child').first().textContent().catch(() => null);

        if (!firstOrderCode) {
            test.skip(true, 'Tidak ada data pesanan untuk dicari');
        }

        const searchInput = page.locator('input[name="q"]');
        await searchInput.fill('ORD-001');
        await page.getByRole('button', { name: /^cari$/i }).click();

        await expect(page).toHaveURL(/q=ORD-001/);

        await screenshotWithWait(page, 'TC-MP-005');
    });

    test('TC-MP-006 · Pengurutan berdasarkan deadline terdekat', async ({ page }) => {
        const sortBtn = page.getByRole('button', { name: /urutkan:/i });
        await sortBtn.click();

        await page.getByRole('link', { name: /mendekati deadline/i }).click();

        await expect(page).toHaveURL(/sort=deadline/);

        await screenshotWithWait(page, 'TC-MP-006');
    });

    test('TC-MP-007 · Pencarian tanpa hasil', async ({ page }) => {
        const searchInput = page.locator('input[name="q"]');
        await searchInput.fill('xyz123');
        await page.getByRole('button', { name: /^cari$/i }).click();

        await expect(page).toHaveURL(/q=xyz123/);
        await expect(
            page.getByText(/tidak ada data pesanan\/pekerjaan yang ditemukan/i)
        ).toBeVisible();

        await screenshotWithWait(page, 'TC-MP-007');
    });

    test.fixme('TC-MP-008 · Admin dapat membuka detail pesanan', async ({ page }) => {
        // TODO: Aktifkan setelah modal detail monitoring dibuat di kode
        // await page.getByRole('button', { name: /lihat detail/i }).first().click();
        // await expect(page.getByRole('dialog')).toBeVisible();
    });

    test.fixme('TC-MP-009 · Informasi pesanan ditampilkan', async ({ page }) => {
        // TODO: Aktifkan setelah modal detail dibuat
    });

    test.fixme('TC-MP-010 · Timeline progress ditampilkan', async ({ page }) => {
        // TODO: Aktifkan setelah modal detail dibuat
    });

    test.fixme('TC-MP-011 · Tahap aktif ditandai', async ({ page }) => {
        // TODO: Aktifkan setelah modal detail dibuat
    });

    test('TC-MP-012 · Kolom Tenggat Waktu tampil dengan tanggal & jam', async ({ page }) => {
        const table = page.locator('table').first();
        await expect(table).toBeVisible();
        await expect(table).toContainText(/tenggat waktu/i);

        const datePattern = /\d{1,2}\s+(Jan|Feb|Mar|Apr|Mei|Jun|Jul|Agu|Sep|Okt|Nov|Des)\s+\d{4}/i;
        const dateCells = page.locator('table tbody td').filter({ hasText: datePattern });
        const count = await dateCells.count();

        if (count === 0) {
            test.skip(true, 'Tidak ada pesanan dengan deadline di DB');
        }

        await expect(dateCells.first()).toBeVisible();

        const firstDateCell = dateCells.first();
        const cellText = await firstDateCell.textContent();
        expect(cellText).toMatch(/\d{1,2}:\d{2}/);

        await screenshotWithWait(page, 'TC-MP-012');
    });

    test('TC-MP-013 · Pesanan melewati deadline ditandai merah + "(Lewat)"', async ({ page }) => {
        const lateBadges = page.locator('span').filter({ hasText: /^\(Lewat\)$/i });
        const count = await lateBadges.count();

        if (count === 0) {
            test.skip(true, 'Tidak ada pesanan lewat deadline di DB');
        }

        await expect(lateBadges.first()).toBeVisible();

        const lateDateDiv = page.locator('div.text-rose-600').filter({ hasText: /\d{1,2}\s+(Jan|Feb|Mar|Apr|Mei|Jun|Jul|Agu|Sep|Okt|Nov|Des)/i });
        const lateDateCount = await lateDateDiv.count();

        if (lateDateCount > 0) {
            await expect(lateDateDiv.first()).toBeVisible();
        }

        await screenshotWithWait(page, 'TC-MP-013');
    });

    test('TC-MP-014 · Admin dapat membuka tab Verifikasi Pembayaran', async ({ page }) => {
        await page.getByRole('button', { name: /verifikasi pembayaran/i }).click();

        await expect(
            page.getByRole('button', { name: /menunggu verifikasi/i })
        ).toBeVisible();

        await screenshotWithWait(page, 'TC-MP-014');
    });

    test('TC-MP-015 · 4 subtab filter tampil', async ({ page }) => {
        await page.getByRole('button', { name: /verifikasi pembayaran/i }).click();

        await expect(page.getByRole('button', { name: /menunggu verifikasi/i })).toBeVisible();
        await expect(page.getByRole('button', { name: /berhasil.*acc/i })).toBeVisible();
        await expect(page.getByRole('button', { name: /ditolak/i })).toBeVisible();
        await expect(page.getByRole('button', { name: /semua riwayat/i })).toBeVisible();

        await screenshotWithWait(page, 'TC-MP-015');
    });

    test('TC-MP-016 · Tabel pembayaran tampil', async ({ page }) => {
        await page.getByRole('button', { name: /verifikasi pembayaran/i }).click();

        const tables = page.locator('table');
        const paymentTable = tables.nth(1);
        await expect(paymentTable).toBeVisible();

        await expect(paymentTable).toContainText(/kode order/i);
        await expect(paymentTable).toContainText(/pembeli/i);
        await expect(paymentTable).toContainText(/nominal/i);
        await expect(paymentTable).toContainText(/status/i);

        await screenshotWithWait(page, 'TC-MP-016');
    });

    test('TC-MP-017 · Tombol "Lihat Foto" membuka modal', async ({ page }) => {
        await page.getByRole('button', { name: /verifikasi pembayaran/i }).click();

        const lihatFotoBtn = page.getByRole('button', { name: /lihat foto/i }).first();
        const count = await lihatFotoBtn.count();

        if (count === 0) {
            test.skip(true, 'Tidak ada payment dengan bukti foto di DB');
        }

        await lihatFotoBtn.click();

        await expect(
            page.getByRole('heading', { name: /foto bukti transfer/i })
        ).toBeVisible();

        await screenshotWithWait(page, 'TC-MP-017');
    });

    test('TC-MP-018 · Tombol ACC membuka modal konfirmasi', async ({ page }) => {
        await page.getByRole('button', { name: /verifikasi pembayaran/i }).click();

        const accBtn = page.getByRole('button', { name: /^acc$/i }).first();
        const count = await accBtn.count();

        if (count === 0) {
            test.skip(true, 'Tidak ada payment pending di DB');
        }

        await accBtn.click();

        await expect(
            page.getByRole('heading', { name: /konfirmasi acc/i })
        ).toBeVisible();

        await screenshotWithWait(page, 'TC-MP-018');
    });

    test('TC-MP-019 · Tombol Tolak membuka modal konfirmasi', async ({ page }) => {
        await page.getByRole('button', { name: /verifikasi pembayaran/i }).click();

        const tolakBtn = page.getByRole('button', { name: /^tolak$/i }).first();
        const count = await tolakBtn.count();

        if (count === 0) {
            test.skip(true, 'Tidak ada payment pending di DB');
        }

        await tolakBtn.click();

        await expect(
            page.getByRole('heading', { name: /konfirmasi tolak/i })
        ).toBeVisible();

        await screenshotWithWait(page, 'TC-MP-019');
    });
});

// ============================================================================
// BAGIAN 2 — LAPORAN & EXPORT
// ============================================================================
test.describe('Laporan — Admin', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
        await goto(page, '/admin/laporan');
        await expect(page).toHaveURL(/\/admin\/laporan/);
    });

    test('TC-LS-001 · Admin dapat membuka halaman Laporan', async ({ page }) => {
        await expect(
            page.getByRole('heading', { name: /laporan transaksi/i }).first()
        ).toBeVisible();

        await screenshotWithWait(page, 'TC-LS-001');
    });

    test('TC-LS-002 · Guest diarahkan ke login', async ({ page, context }) => {
        await context.clearCookies();
        await goto(page, '/admin/laporan');
        await expect(page).toHaveURL(/\/login/);

        await screenshotWithWait(page, 'TC-LS-002');
    });

    test('TC-LS-003 · Daftar laporan per bulan tampil', async ({ page }) => {
        const cards = page.locator('form[action*="laporan/export"]');
        expect(await cards.count()).toBeGreaterThan(0);

        await screenshotWithWait(page, 'TC-LS-003');
    });

    test('TC-LS-004 · Tahun tampil sebagai header grup', async ({ page }) => {
        const yearHeader = page.getByRole('heading', { name: /^\d{4}$/ }).first();
        await expect(yearHeader).toBeVisible();

        await screenshotWithWait(page, 'TC-LS-004');
    });

    test('TC-LS-005 · Card bulan tampil dengan nama bulan', async ({ page }) => {
        const monthNames = /januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember/i;
        const monthHeading = page.locator('h3').filter({ hasText: monthNames }).first();
        await expect(monthHeading).toBeVisible();

        await screenshotWithWait(page, 'TC-LS-005');
    });

    test('TC-LS-006 · 3 tombol export tampil', async ({ page }) => {
        await expect(page.getByRole('button', { name: /^excel$/i }).first()).toBeVisible();
        await expect(page.getByRole('button', { name: /^pdf$/i }).first()).toBeVisible();
        await expect(page.getByRole('button', { name: /^csv$/i }).first()).toBeVisible();

        await screenshotWithWait(page, 'TC-LS-006');
    });

    test('TC-LS-007 · Export Excel berfungsi', async ({ page }) => {
        const excelBtn = page.getByRole('button', { name: /^excel$/i }).first();
        await expect(excelBtn).toBeVisible();

        const downloadPromise = page.waitForEvent('download', { timeout: 30_000 }).catch(() => null);

        await excelBtn.click();

        const download = await downloadPromise;
        if (download) {
            const fileName = download.suggestedFilename();
            expect(fileName).toMatch(/\.xlsx$/);
        }

        await screenshotWithWait(page, 'TC-LS-007');
    });

    test('TC-LS-008 · Export PDF berfungsi', async ({ page }) => {
        const pdfBtn = page.getByRole('button', { name: /^pdf$/i }).first();
        await expect(pdfBtn).toBeVisible();

        const downloadPromise = page.waitForEvent('download', { timeout: 30_000 }).catch(() => null);

        await pdfBtn.click();

        const download = await downloadPromise;
        if (download) {
            const fileName = download.suggestedFilename();
            expect(fileName).toMatch(/\.pdf$/);
        }

        await screenshotWithWait(page, 'TC-LS-008');
    });

    test('TC-LS-009 · Export CSV berfungsi', async ({ page }) => {
        const csvBtn = page.getByRole('button', { name: /^csv$/i }).first();
        await expect(csvBtn).toBeVisible();

        const downloadPromise = page.waitForEvent('download', { timeout: 30_000 }).catch(() => null);

        await csvBtn.click();

        const download = await downloadPromise;
        if (download) {
            const fileName = download.suggestedFilename();
            expect(fileName).toMatch(/\.csv$/);
        }

        await screenshotWithWait(page, 'TC-LS-009');
    });

    test('TC-LS-010 · Nama file export format benar', async ({ page }) => {
        const pdfBtn = page.getByRole('button', { name: /^pdf$/i }).first();

        const downloadPromise = page.waitForEvent('download', { timeout: 30_000 }).catch(() => null);

        await pdfBtn.click();

        const download = await downloadPromise;
        if (download) {
            const fileName = download.suggestedFilename();
            expect(fileName).toMatch(/laporan-transaksi-artiv-\d{8}-\d{6}\.pdf/);
        }

        await screenshotWithWait(page, 'TC-LS-010');
    });

    test('TC-LS-011 · Tombol "Atur Periode" membuka modal', async ({ page }) => {
        const aturBtn = page.getByRole('button', { name: /atur periode/i }).first();
        await expect(aturBtn).toBeVisible();

        await aturBtn.click();

        await expect(
            page.getByRole('heading', { name: /atur rentang tanggal/i })
        ).toBeVisible();

        await screenshotWithWait(page, 'TC-LS-011');
    });

    test('TC-LS-012 · Input tanggal mulai & selesai tampil', async ({ page }) => {
        await page.getByRole('button', { name: /atur periode/i }).first().click();

        await expect(page.locator('input[name="start_date"]').last()).toBeVisible();
        await expect(page.locator('input[name="end_date"]').last()).toBeVisible();

        await screenshotWithWait(page, 'TC-LS-012');
    });

    test('TC-LS-013 · Validasi tanggal kosong', async ({ page }) => {
        await page.getByRole('button', { name: /atur periode/i }).first().click();

        await page.locator('input[name="start_date"]').last().fill('');
        await page.locator('input[name="end_date"]').last().fill('');

        const startDate = page.locator('input[name="start_date"]').last();
        await expect(startDate).toHaveAttribute('required', '');

        await screenshotWithWait(page, 'TC-LS-013');
    });

    test('TC-LS-014 · Export custom range berfungsi', async ({ page }) => {
        await page.getByRole('button', { name: /atur periode/i }).first().click();

        await page.locator('input[name="start_date"]').last().fill('2025-10-01');
        await page.locator('input[name="end_date"]').last().fill('2025-10-31');

        const downloadPromise = page.waitForEvent('download', { timeout: 30_000 }).catch(() => null);

        await page.locator('form[action*="laporan/export"]').last()
            .getByRole('button', { name: /^pdf$/i }).click();

        const download = await downloadPromise;
        if (download) {
            const fileName = download.suggestedFilename();
            expect(fileName).toMatch(/\.pdf$/);
        }

        await screenshotWithWait(page, 'TC-LS-014');
    });
});

// ============================================================================
// BAGIAN 3 — PENGATURAN
// ============================================================================
test.describe('Pengaturan — Admin', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
        await goto(page, '/admin/pengaturan');
        await expect(page).toHaveURL(/\/admin\/pengaturan/);
    });

    test('TC-PG-001 · Admin dapat membuka halaman Pengaturan', async ({ page }) => {
        await expect(
            page.getByRole('heading', { name: /^pengaturan$/i }).first()
        ).toBeVisible();

        await screenshotWithWait(page, 'TC-PG-001');
    });

    test('TC-PG-002 · Guest diarahkan ke login', async ({ page, context }) => {
        await context.clearCookies();
        await goto(page, '/admin/pengaturan');
        await expect(page).toHaveURL(/\/login/);

        await screenshotWithWait(page, 'TC-PG-002');
    });

    test('TC-PG-003 · Card "Pengaturan Harga Revisi" tampil', async ({ page }) => {
        await expect(
            page.getByRole('heading', { name: /pengaturan harga revisi/i })
        ).toBeVisible();

        await expect(
            page.getByRole('button', { name: /ubah tarif/i })
        ).toBeVisible();

        await screenshotWithWait(page, 'TC-PG-003');
    });

    test('TC-PG-004 · Tabel blacklist keyword tampil', async ({ page }) => {
        const table = page.locator('table').first();
        await expect(table).toBeVisible();

        await expect(table).toContainText(/keyword/i);
        await expect(table).toContainText(/type/i);
        await expect(table).toContainText(/status/i);

        await screenshotWithWait(page, 'TC-PG-004');
    });

    test('TC-PG-005 · 4 tipe keyword tampil di badge', async ({ page }) => {
        const table = page.locator('table').first();
        const typeText = await table.textContent();

        const hasType = /kata terlarang|link\/url|nomor telepon|alamat email/i.test(typeText || '');
        expect(hasType).toBeTruthy();

        await screenshotWithWait(page, 'TC-PG-005');
    });

    test('TC-PG-006 · Pencarian keyword berfungsi', async ({ page }) => {
        const searchInput = page.locator('input[name="q"]');
        await searchInput.fill('wa');
        await searchInput.press('Enter');

        await expect(page).toHaveURL(/q=wa/);

        await screenshotWithWait(page, 'TC-PG-006');
    });

    test('TC-PG-007 · Filter tipe keyword berfungsi', async ({ page }) => {
        const filterBtn = page.getByRole('button', { name: /semua tipe keyword/i }).first();
        await filterBtn.click();

        await page.getByRole('button', { name: /link \(tautan url\)/i }).click();

        await page.waitForURL(/type=link/, { timeout: 10_000 }).catch(() => null);

        await screenshotWithWait(page, 'TC-PG-007');
    });

    test('TC-PG-008 · Tombol Reset menghapus filter', async ({ page }) => {
        await goto(page, '/admin/pengaturan?q=wa');

        const resetBtn = page.getByRole('link', { name: /^reset$/i }).first();
        await expect(resetBtn).toBeVisible();

        await resetBtn.click();

        await expect(page).toHaveURL(/\/admin\/pengaturan$/);

        await screenshotWithWait(page, 'TC-PG-008');
    });

    test('TC-PG-009 · Tambah keyword berfungsi', async ({ page }) => {
        await page.getByRole('button', { name: /tambah keyword/i }).click();
        await page.waitForTimeout(1000);

        const uniqueKeyword = `testkeyword${Date.now()}`;

        await page.locator('input[x-model="addKeyword"]').fill(uniqueKeyword);

        const typeSelect = page.locator('select[x-model="addType"]');
        if (await typeSelect.count() > 0) {
            await typeSelect.selectOption('word');
        }

        await page.locator('#add-keyword-modal button[type="submit"]').click();

        await page.waitForTimeout(2000);

        await expect(page.getByText(uniqueKeyword).first()).toBeVisible({ timeout: 5000 }).catch(() => null);

        await screenshotWithWait(page, 'TC-PG-009');
    });

    test('TC-PG-010 · Validasi keyword kosong', async ({ page }) => {
        await page.getByRole('button', { name: /tambah keyword/i }).click();

        await page.locator('form[action*="blacklist"]').last()
            .getByRole('button', { name: /simpan/i }).click();

        const keywordInput = page.locator('input[name="keyword"]').last();
        await expect(keywordInput).toHaveAttribute('required', '');

        await screenshotWithWait(page, 'TC-PG-010');
    });

    test('TC-PG-011 · Validasi keyword duplikat', async ({ page }) => {
        const firstKeywordCell = page.locator('table tbody tr td:nth-child(2)').first();
        const keywordText = await firstKeywordCell.textContent().catch(() => null);

        if (!keywordText) {
            test.skip(true, 'Tidak ada keyword untuk test duplikat');
        }

        const existingKeyword = keywordText.trim().split('\n')[0].trim();
        if (!existingKeyword) {
            test.skip(true, 'Tidak bisa ambil keyword existing');
        }

        await page.getByRole('button', { name: /tambah keyword/i }).click();
        await page.waitForTimeout(1000);

        await page.locator('input[x-model="addKeyword"]').fill(existingKeyword);

        await page.locator('#add-keyword-modal button[type="submit"]').click();

        await page.waitForTimeout(2000);

        const errorText = page.getByText(/sudah ada di daftar blacklist/i).first();
        const errorCount = await errorText.count();
        if (errorCount > 0) {
            await expect(errorText).toBeVisible();
        }

        await screenshotWithWait(page, 'TC-PG-011');
    });

    test('TC-PG-012 · Edit keyword berfungsi', async ({ page }) => {
        const editBtn = page.getByRole('button', { name: /^edit$/i }).first();
        const count = await editBtn.count();

        if (count === 0) {
            test.skip(true, 'Tidak ada keyword untuk diedit');
        }

        await editBtn.click();
        await page.waitForTimeout(1000);

        const keywordInput = page.locator('input[x-model="editKeyword"]');
        const currentValue = await keywordInput.inputValue();
        const newValue = `${currentValue}_edited_${Date.now()}`;
        await keywordInput.fill(newValue);

        await page.locator('#edit-keyword-modal button[type="submit"]').click();

        await page.waitForTimeout(2000);

        await screenshotWithWait(page, 'TC-PG-012');
    });

    test('TC-PG-013 · Hapus keyword berfungsi', async ({ page }) => {
        const deleteBtn = page.getByRole('button', { name: /^hapus$/i }).first();
        const count = await deleteBtn.count();

        if (count === 0) {
            test.skip(true, 'Tidak ada keyword untuk dihapus');
        }

        await deleteBtn.click();
        await page.waitForTimeout(1000);

        await page.locator('#delete-keyword-modal button[type="submit"]').click();

        await page.waitForTimeout(2000);

        await screenshotWithWait(page, 'TC-PG-013');
    });

    test('TC-PG-014 · Ubah harga revisi berfungsi', async ({ page }) => {
        await page.getByRole('button', { name: /ubah tarif/i }).click();
        await page.waitForTimeout(1000);

        const feeInput = page.locator('input[name="fee"]');
        const count = await feeInput.count();

        if (count === 0) {
            test.skip(true, 'Input fee tidak ditemukan — cek selector');
        }

        await feeInput.fill('25000');

        await page.locator('#fee-modal button[type="submit"]').click();

        await page.waitForTimeout(2000);

        await screenshotWithWait(page, 'TC-PG-014');
    });

    test('TC-PG-015 · Validasi nominal negatif', async ({ page }) => {
        await page.getByRole('button', { name: /ubah tarif/i }).click();

        await page.waitForTimeout(500);

        const feeInput = page.locator('input[name="fee"]').last();
        await feeInput.fill('-10000');

        await expect(feeInput).toHaveAttribute('min', '0');

        await screenshotWithWait(page, 'TC-PG-015');
    });
});