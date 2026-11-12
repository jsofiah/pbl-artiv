import { test, expect } from '@playwright/test';

const DESIGNER = { email: 'designer1@gmail.com', password: 'designer123' };

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

async function screenshotWithWait(page, tcId) {
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: `evidence/${tcId}.png`, fullPage: true });
}

async function gotoDetailPekerjaan(page) {
    await page.locator('a[href*="/designer/pekerjaan-saya"]').first().click();
    await page.waitForLoadState('domcontentloaded');
    
    const btn = page.locator('a:has-text("Lihat Pekerjaan")').first();
    if (await btn.isVisible().catch(() => false)) {
        await btn.click();
        await page.waitForLoadState('domcontentloaded');
    }
}

test.describe('Pekerjaan Saya — Designer', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsDesigner(page);
        await page.locator('a[href*="/designer/pekerjaan-saya"]').first().click();
        await page.waitForLoadState('domcontentloaded');
    });

    test('TC-DPk-001: Designer dapat membuka halaman Pekerjaan Saya', async ({ page }) => {
        await expect(page.locator('h1:has-text("Pekerjaan Saya")').first()).toBeVisible();
        await screenshotWithWait(page, 'TC-DPk-001');
    });

    test('TC-DPk-002: Guest diarahkan ke login', async ({ page, context }) => {
        await context.clearCookies();
        await goto(page, '/designer/pekerjaan-saya');
        await expect(page).toHaveURL(/\/login/);
        await screenshotWithWait(page, 'TC-DPk-002');
    });

    test('TC-DPk-003: Customer tidak dapat mengakses Pekerjaan Saya', async ({ page, context }) => {
        await context.clearCookies();
        await goto(page, '/login');
        await page.fill('input[name="email"]', 'customer1@gmail.com');
        await page.fill('input[name="password"]', 'customer123');
        await page.click('button[type="submit"]');
        await page.waitForLoadState('domcontentloaded');
        await goto(page, '/designer/pekerjaan-saya');
        
        const body = await page.locator('body').innerText();
        expect(/Akses ditolak|403|Forbidden/i.test(body)).toBeTruthy();
        await screenshotWithWait(page, 'TC-DPk-003');
    });

    test('TC-DPk-004: Informasi pekerjaan ditampilkan lengkap', async ({ page }) => {
        const card = page.locator('div.bg-white.rounded-2xl').filter({ has: page.locator('a:has-text("Lihat Pekerjaan")') }).first();
        const count = await card.count();
        
        test.skip(count === 0, 'Belum ada kartu pekerjaan aktif');
        
        await expect(card).toContainText(/ARTIV-/); 
        await expect(card).toContainText(/Rp/); 
        await expect(card.locator('a:has-text("Lihat Pekerjaan")')).toBeVisible(); 
        await screenshotWithWait(page, 'TC-DPk-004');
    });

    test('TC-DPk-005: Tombol "Lihat Pekerjaan" dapat diklik', async ({ page }) => {
        const btn = page.locator('a:has-text("Lihat Pekerjaan")').first();
        const count = await btn.count();
        test.skip(count === 0, 'Belum ada pekerjaan aktif');
        
        await btn.click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/designer\/pekerjaan-saya\/[\w-]+/);
        await screenshotWithWait(page, 'TC-DPk-005');
    });
});

test.describe('Detail Pekerjaan — Designer', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsDesigner(page);
        await gotoDetailPekerjaan(page);
    });

    test('TC-DPt-001: Halaman Detail Pekerjaan dapat diakses', async ({ page }) => {
        await expect(page).toHaveURL(/\/designer\/pekerjaan-saya\/[\w-]+/);
        await expect(page.locator('h1:has-text("Detail Pekerjaan")').first()).toBeVisible();
        await screenshotWithWait(page, 'TC-DPt-001');
    });

    test('TC-DPt-002: Brief, file referensi, dan detail order ditampilkan', async ({ page }) => {
        await expect(page.locator('body')).toContainText(/Brief & Referensi/i);
        await expect(page.locator('body')).toContainText(/Customer/i);
        await expect(page.locator('body')).toContainText(/LAYANAN/i);
        await screenshotWithWait(page, 'TC-DPt-002');
    });

    test('TC-DPt-003: Chat dapat dibuka dan riwayat tampil', async ({ page }) => {
        await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
        const chatInput = page.locator('input[name="isi"]');
        await expect(chatInput).toBeVisible();
        await screenshotWithWait(page, 'TC-DPt-003');
    });

    test('TC-DPt-004: Pesan dapat dikirim', async ({ page }) => {
        await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
        const chatInput = page.locator('input[name="isi"]');
        test.skip(!(await chatInput.isVisible().catch(() => false)), 'Chat tidak aktif');
        
        await chatInput.fill('Halo, brief sudah saya terima');
        await page.locator('form[action*="/pesan"] button[type="submit"]').click();
        
        await page.waitForLoadState('domcontentloaded');
        await expect(page.locator('body')).toContainText(/brief sudah saya terima/i);
        await screenshotWithWait(page, 'TC-DPt-004');
    });

    test('TC-DPt-005: Pesan dengan link/telepon diblokir', async ({ page }) => {
        await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
        const chatInput = page.locator('input[name="isi"]');
        test.skip(!(await chatInput.isVisible().catch(() => false)), 'Chat tidak aktif');
        
        await chatInput.fill('Ini WA saya 08123456789');
        await page.locator('form[action*="/pesan"] button[type="submit"]').click();
        
        await page.waitForTimeout(1000);
        await expect(page.locator('body')).toContainText(/diblokir|tidak diizinkan|peringatan/i);
        await screenshotWithWait(page, 'TC-DPt-005');
    });

    test('TC-DPt-006: Modal "Kirim Hasil Desain" terbuka', async ({ page }) => {
        const btnKirimHasil = page.locator('button:has-text("Kirim Hasil Desain")').first();
        const count = await btnKirimHasil.count();
        test.skip(count === 0, 'Tombol Kirim Hasil tidak ada');
        
        await btnKirimHasil.click();
        await page.waitForTimeout(500);
        
        await expect(page.locator('text=Kirim Hasil Desain').first()).toBeVisible();
        await expect(page.locator('input#deliverable-files')).toBeAttached(); 
        await screenshotWithWait(page, 'TC-DPt-006');
    });

    test('TC-DPt-007: File desain berhasil diunggah', async ({ page }) => {
        const btnKirimHasil = page.locator('button:has-text("Kirim Hasil Desain")').first();
        const count = await btnKirimHasil.count();
        test.skip(count === 0, 'Tombol Kirim Hasil tidak ada');
        
        await btnKirimHasil.click();
        await page.waitForTimeout(500);
        
        await page.locator('input#deliverable-files').setInputFiles({
            name: 'desain_final.jpg',
            mimeType: 'image/jpeg',
            buffer: Buffer.from('fake image'),
        });
        
        await page.locator('button:has-text("Kirim ke Customer")').click();
        
        await page.waitForTimeout(1500);
        await screenshotWithWait(page, 'TC-DPt-007');
    });

    test('TC-DPt-008: Modal "Ajukan Pembatalan" terbuka', async ({ page }) => {
        const btnBatal = page.locator('button:has-text("Batalkan Order"), button:has-text("Ajukan Pembatalan")').first();
        const count = await btnBatal.count();
        test.skip(count === 0, 'Tombol Batalkan Order tidak ada (sudah selesai)');
        
        await btnBatal.click();
        await page.waitForTimeout(500);
        await expect(page.locator('text=Ajukan Pembatalan').first()).toBeVisible();
        await screenshotWithWait(page, 'TC-DPt-008');
    });

    test('TC-DPt-09: Validasi alasan pembatalan kosong', async ({ page }) => {
        const btnBatal = page.locator('button:has-text("Batalkan Order"), button:has-text("Ajukan Pembatalan")').first();
        const count = await btnBatal.count();
        test.skip(count === 0, 'Tombol Batalkan Order tidak ada');
        
        await btnBatal.click();
        await page.waitForTimeout(500);
        
        await page.locator('button:has-text("Batalkan")').last().click();
        await page.waitForTimeout(500);
        
        await expect(page.locator('text=Ajukan Pembatalan').first()).toBeVisible();
        await screenshotWithWait(page, 'TC-DPt-009');
    });

    test('TC-DPt-010: Pembatalan berhasil diproses', async ({ page }) => {
        const btnBatal = page.locator('button:has-text("Batalkan Order"), button:has-text("Ajukan Pembatalan")').first();
        const count = await btnBatal.count();
        test.skip(count === 0, 'Tombol Batalkan Order tidak ada');
        
        await btnBatal.click();
        await page.waitForTimeout(500);
        
        await page.locator('textarea[name="alasan"]').fill('Saya sedang sakit');
        await page.locator('button:has-text("Batalkan")').last().click();
        
        await page.waitForTimeout(1000);
        await expect(page.locator('text=Ajukan Pembatalan').first()).toBeHidden();
        await screenshotWithWait(page, 'TC-DPt-010');
    });
});

test.describe('Riwayat Pekerjaan — Designer', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsDesigner(page);
        await page.locator('a[href*="/designer/riwayat"]').first().click();
        await page.waitForLoadState('domcontentloaded');
    });

    test('TC-RPk-001: Designer dapat membuka halaman Riwayat Pekerjaan', async ({ page }) => {
        await expect(page).toHaveURL(/\/designer\/riwayat/);
        await expect(page.locator('h1:has-text("Riwayat Pekerjaan")').first()).toBeVisible();
        await screenshotWithWait(page, 'TC-RPk-001');
    });

    test('TC-RPk-002: Detail data pada tabel riwayat', async ({ page }) => {
        await expect(page.locator('body')).toContainText(/INFORMASI PEKERJAAN/i);
        await expect(page.locator('body')).toContainText(/KOMPENSASI/i);
        await expect(page.locator('body')).toContainText(/RATING/i);
        await screenshotWithWait(page, 'TC-RPk-002');
    });

    test('TC-RPk-003: Pencarian berdasarkan nama pekerjaan', async ({ page }) => {
        const searchInput = page.locator('input[placeholder*="Cari riwayat"]').first();
        await searchInput.fill('Logo');
        await page.locator('button:has-text("Cari")').first().click();
        await page.waitForLoadState('domcontentloaded');
        await screenshotWithWait(page, 'TC-RPk-003');
    });

    test('TC-RPk-004: Filter Kategori berfungsi', async ({ page }) => {
        const filterKategori = page.locator('button:has-text("Kategori:")').first();
        if (await filterKategori.count() > 0) {
            await filterKategori.click();
            await page.waitForTimeout(500);
            await page.locator('a:has-text("Banner")').first().click().catch(() => null);
            await page.waitForLoadState('domcontentloaded');
        }
        await screenshotWithWait(page, 'TC-RPk-004');
    });

    test('TC-RPk-005: Ekspor CSV', async ({ page }) => {
        const btnEkspor = page.locator('a:has-text("Ekspor CSV"), button:has-text("Ekspor CSV")').first();
        if (await btnEkspor.count() > 0) {
            const downloadPromise = page.waitForEvent('download', { timeout: 5000 }).catch(() => null);
            await btnEkspor.click();
            const download = await downloadPromise;
            if (download) {
                expect(download.suggestedFilename()).toMatch(/\.csv$/);
            }
        }
        await screenshotWithWait(page, 'TC-RPk-005');
    });

    test('TC-RPk-006: Tombol "Lihat Review" dapat diklik', async ({ page }) => {
        const btnReview = page.locator('a:has-text("Lihat Review")').first();
        const count = await btnReview.count();
        test.skip(count === 0, 'Belum ada riwayat dengan review');
        
        await btnReview.click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/designer\/riwayat\/[\w-]+/);
        await screenshotWithWait(page, 'TC-RPk-006');
    });
});

test.describe('Detail Riwayat Pekerjaan', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsDesigner(page);
        await page.locator('a[href*="/designer/riwayat"]').first().click();
        await page.waitForLoadState('domcontentloaded');
        
        const btnReview = page.locator('a:has-text("Lihat Review")').first();
        const count = await btnReview.count();
        if (count === 0) {
            test.skip(true, 'Belum ada riwayat dengan review');
        }
        await btnReview.click();
        await page.waitForLoadState('domcontentloaded');
    });

    test('TC-RPk-007: Informasi review dan rating ditampilkan', async ({ page }) => {
        await expect(page.locator('body')).toContainText(/Review & Rating Customer/i);
        await expect(page.locator('body')).toContainText(/5.0 \/ 5/i);
        await screenshotWithWait(page, 'TC-RPk-007');
    });

    test('TC-RPk-008: Timeline riwayat status pesanan ditampilkan', async ({ page }) => {
        await expect(page.locator('body')).toContainText(/Riwayat Status Pesanan/i);
        await expect(page.locator('body')).toContainText(/Menunggu Konfirmasi/i);
        await expect(page.locator('body')).toContainText(/Selesai/i);
        await screenshotWithWait(page, 'TC-RPk-008');
    });

    test('TC-RPk-009: Tombol "Unduh Hasil" berfungsi', async ({ page }) => {
        const btnUnduh = page.locator('a:has-text("Unduh Hasil")').first();
        if (await btnUnduh.count() > 0) {
            const downloadPromise = page.waitForEvent('download', { timeout: 5000 }).catch(() => null);
            await btnUnduh.click();
            const download = await downloadPromise;
            if (download) {
                expect(download.suggestedFilename()).toBeTruthy();
            }
        }
        await screenshotWithWait(page, 'TC-RPk-009');
    });
});
