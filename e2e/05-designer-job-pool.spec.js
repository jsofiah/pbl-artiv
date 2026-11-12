import { test, expect } from '@playwright/test';

const DESIGNER = { email: 'designer1@gmail.com', password: 'designer123' };
const CUSTOMER = { email: 'customer1@gmail.com', password: 'customer123' };

async function goto(page, url) {
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
}

async function loginAsDesigner(page) {
    await goto(page, '/login');
    await page.fill('input[name="email"]', DESIGNER.email);
    await page.fill('input[name="password"]', DESIGNER.password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');
}

async function loginAsCustomer(page) {
    await goto(page, '/login');
    await page.fill('input[name="email"]', CUSTOMER.email);
    await page.fill('input[name="password"]', CUSTOMER.password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');
}

async function screenshotWithWait(page, tcId) {
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: `evidence/${tcId}.png`, fullPage: true });
}

test.describe('Job Pool — Designer', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsDesigner(page);
        await page.locator('a[href*="/designer/job-pool"]').first().click();
        await page.waitForLoadState('domcontentloaded');
    });

    test('TC-JP-001: Designer dapat membuka halaman Job Pool', async ({ page }) => {
        await expect(page.locator('h1:has-text("Job Pool")').first()).toBeVisible();
        await screenshotWithWait(page, 'TC-JP-001');
    });

    test('TC-JP-002: Guest diarahkan ke login', async ({ page, context }) => {
        await context.clearCookies();
        await goto(page, '/designer/job-pool');
        await expect(page).toHaveURL(/\/login/);
        await screenshotWithWait(page, 'TC-JP-002');
    });

    test('TC-JP-003: Customer tidak dapat mengakses Job Pool', async ({ page, context }) => {
        await context.clearCookies();
        await loginAsCustomer(page);
         await goto(page, '/designer/job-pool');
        await expect(page.locator('body')).toContainText(/Akses ditolak|403|Forbidden/i, { timeout: 10000 });
        await screenshotWithWait(page, 'TC-JP-003');
    });

    test('TC-JP-004: Daftar job tersedia ditampilkan', async ({ page }) => {
        const listExists = await page.locator('article').count();
        test.skip(listExists === 0, 'Belum ada job tersedia');
        await expect(page.locator('article').first()).toContainText(/Rp/i);
        await screenshotWithWait(page, 'TC-JP-004');
    });

    test('TC-JP-005: Pencarian berdasarkan nama job', async ({ page }) => {
        await page.fill('input[name="q"]', 'Poster');
        await page.click('button:has-text("Cari")');
        await page.waitForLoadState('domcontentloaded');
        await screenshotWithWait(page, 'TC-JP-005');
    });

    test('TC-JP-006: Filter berdasarkan kategori', async ({ page }) => {
        await page.click('button:has-text("Kategori:")');
        await page.waitForTimeout(500);
        await page.locator('div[x-show="open"] a:has-text("Semua")').first().click();
        await page.waitForLoadState('domcontentloaded');
        await screenshotWithWait(page, 'TC-JP-006');
    });

    test('TC-JP-007: Filter berdasarkan deadline', async ({ page }) => {
        await page.click('button:has-text("Urutkan:")');
        await page.waitForTimeout(500);
        await page.locator('div[x-show="open"] a:has-text("Deadline terdekat")').first().click();
        await page.waitForLoadState('domcontentloaded');
        await screenshotWithWait(page, 'TC-JP-007');
    });

    test('TC-JP-008: Filter berdasarkan harga', async ({ page }) => {
        await page.click('button:has-text("Urutkan:")');
        await page.waitForTimeout(500);
        await page.locator('div[x-show="open"] a:has-text("Harga terendah")').first().click();
        await page.waitForLoadState('domcontentloaded');
        await screenshotWithWait(page, 'TC-JP-008');
    });

    test('TC-JP-009: Pencarian tidak ditemukan', async ({ page }) => {
        await page.fill('input[name="q"]', 'xyz123');
        await page.click('button:has-text("Cari")');
        await page.waitForLoadState('domcontentloaded');
        await expect(page.locator('body')).toContainText(/tidak ditemukan|Belum ada job/i);
        await screenshotWithWait(page, 'TC-JP-009');
    });

    test('TC-JP-010: Designer dapat membuka detail job', async ({ page }) => {
        const detailBtn = page.locator('a:has-text("Lihat Detail")').first();
        const count = await detailBtn.count();
        test.skip(count === 0, 'Tidak ada job tersedia');
        await detailBtn.click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/designer\/job-pool\/\w+/);
        await screenshotWithWait(page, 'TC-JP-010');
    });

    test('TC-JP-011: Job sudah diambil tidak muncul', async ({ page }) => {
        const takenJob = page.locator('text=/Sudah Diambil|Taken/i');
        await expect(takenJob).toBeHidden();
        await screenshotWithWait(page, 'TC-JP-011');
    });

    test('TC-JP-012: Job berhasil diambil masuk ke Daftar Pekerjaan', async ({ page }) => {
        await page.locator('a[href*="/designer/pekerjaan-saya"]').first().click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page.locator('h1:has-text("Pekerjaan Saya")').first()).toBeVisible();
        await screenshotWithWait(page, 'TC-JP-012');
    });
});

test.describe('Detail Job — Designer', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsDesigner(page);
        await page.locator('a[href*="/designer/job-pool"]').first().click();
        await page.waitForLoadState('domcontentloaded');
        const detailBtn = page.locator('a:has-text("Lihat Detail")').first();
        const count = await detailBtn.count();
        if (count === 0) {
            test.skip(true, 'Tidak ada job tersedia');
        }
        await detailBtn.click();
        await page.waitForLoadState('domcontentloaded');
    });

    test('TC-DJ-001: Halaman Detail Job dapat diakses', async ({ page }) => {
        await expect(page).toHaveURL(/\/designer\/job-pool\/\w+/);
        await screenshotWithWait(page, 'TC-DJ-001');
    });

    test('TC-DJ-002: Informasi job, brief, & unduh file referensi (Opsional)', async ({ page }) => {
        await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
        await expect(page.locator('body')).toContainText(/Brief|Referensi|Deadline|Biaya/i);
        const fileRef = page.locator('text=File Referensi').first();
        if (await fileRef.isVisible().catch(() => false)) {
            const downloadBtn = page.locator('a[title="Unduh"], a[title="Unduh file"], button[title="Unduh"]').first();
            const linkRef = page.locator('a[target="_blank"]').first();
            if (await downloadBtn.isVisible().catch(() => false)) {
                await expect(downloadBtn).toBeVisible();
            } else if (await linkRef.isVisible().catch(() => false)) {
                await expect(linkRef).toBeVisible();
            }
        }
        await screenshotWithWait(page, 'TC-DJ-002');
    });

    test('TC-DJ-003: Tombol "Ambil Order" ditampilkan', async ({ page }) => {
        const ambilBtn = page.locator('main button:has-text("Ambil"), main a:has-text("Ambil"), form button:has-text("Ambil")').first();
        const count = await ambilBtn.count();
        test.skip(count === 0, 'Tombol Ambil tidak ada / Job sudah diambil / Lewat Deadline');
        await expect(ambilBtn).toBeVisible();
        await screenshotWithWait(page, 'TC-DJ-003');
    });

    test('TC-DJ-004: Designer dapat mengambil order', async ({ page }) => {
        const ambilBtn = page.locator('main button:has-text("Ambil"), main a:has-text("Ambil"), form button:has-text("Ambil")').first();
        const count = await ambilBtn.count();
        test.skip(count === 0, 'Tombol Ambil tidak ada');
        await ambilBtn.click();
        await page.waitForTimeout(1000);
        await screenshotWithWait(page, 'TC-DJ-004');
    });

    test('TC-DJ-005: Konfirmasi pengambilan order muncul', async ({ page }) => {
        const ambilBtn = page.locator('main button:has-text("Ambil"), main a:has-text("Ambil"), form button:has-text("Ambil")').first();
        const count = await ambilBtn.count();
        test.skip(count === 0, 'Tombol Ambil tidak ada');
        await ambilBtn.click();
        await page.waitForTimeout(1000);
        await screenshotWithWait(page, 'TC-DJ-005');
    });

    test('TC-DJ-006: Status order berubah setelah pengambilan', async ({ page }) => {
        const ambilBtn = page.locator('main button:has-text("Ambil"), main a:has-text("Ambil"), form button:has-text("Ambil")').first();
        const count = await ambilBtn.count();
        test.skip(count === 0, 'Tombol Ambil tidak ada');
        await ambilBtn.click();
        await page.waitForTimeout(1000); 
        const confirmBtn = page.locator('button:has-text("Ya"), button:has-text("Konfirmasi")').first();
        if (await confirmBtn.isVisible().catch(() => false)) {
            await confirmBtn.click();
        }
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/sukses/); 
        
        await screenshotWithWait(page, 'TC-DJ-006');
    });

    test('TC-DJ-007: Designer diarahkan ke halaman Pekerjaan Saya', async ({ page }) => {
        const ambilBtn = page.locator('main button:has-text("Ambil"), main a:has-text("Ambil"), form button:has-text("Ambil")').first();
        const count = await ambilBtn.count();
        test.skip(count === 0, 'Tombol Ambil tidak ada');
        await ambilBtn.click();
        await page.waitForTimeout(1000);
        const confirmBtn = page.locator('button:has-text("Ya"), button:has-text("Konfirmasi")').first();
        if (await confirmBtn.isVisible().catch(() => false)) {
            await confirmBtn.click();
        }
        await page.waitForLoadState('domcontentloaded');
        await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
        const lihatPekerjaanBtn = page.locator('a:has-text("Lihat Pekerjaan"), button:has-text("Lihat Pekerjaan")').first();
        if (await lihatPekerjaanBtn.isVisible().catch(() => false)) {
            await lihatPekerjaanBtn.click();
        } else {
            await goto(page, '/designer/pekerjaan-saya');
        }
        
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/designer\/pekerjaan-saya/);
        await screenshotWithWait(page, 'TC-DJ-007');
    });

    test('TC-DJ-008: Designer dapat membatalkan pengambilan order', async ({ page }) => {
        const ambilBtn = page.locator('main button:has-text("Ambil"), main a:has-text("Ambil"), form button:has-text("Ambil")').first();
        const count = await ambilBtn.count();
        test.skip(count === 0, 'Tombol Ambil tidak ada');
        await ambilBtn.click();
        await page.waitForTimeout(500);
        const cancelBtn = page.locator('button:has-text("Batal"), a:has-text("Batal")').first();
        if (await cancelBtn.isVisible().catch(() => false)) {
            await cancelBtn.click();
        }
        await page.waitForLoadState('domcontentloaded');
        await screenshotWithWait(page, 'TC-DJ-008');
    });
});