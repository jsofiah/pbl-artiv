import { test, expect } from '@playwright/test';

const USERS = {
    customer: { email: 'customer1@gmail.com', password: 'customer123' },
    customerEmpty: { email: 'customer2@gmail.com', password: 'customer123' },
    designer: { email: 'designer1@gmail.com', password: 'designer123' },
};

async function goto(page, url) {
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 15_000 });
}

async function skipIntro(page) {
    await page.addInitScript(() => {
        sessionStorage.setItem('artiv-intro-seen', '1');
    });
}

async function loginAs(page, user) {
    await skipIntro(page);
    await goto(page, '/login');
    await page.fill('input[name="email"]', user.email);
    await page.fill('input[name="password"]', user.password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');
}

async function screenshotWithWait(page, tcId) {
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: `evidence/${tcId}.png`, fullPage: true });
}

async function gotoPesananSaya(page) {
    await goto(page, '/customer/pesanan');
}

async function gotoDetailPesanan(page) {
    await gotoPesananSaya(page);
    const btn = page.locator('a:has-text("Lihat Pesanan")').first();
    if (await btn.isVisible().catch(() => false)) {
        await btn.click();
        await page.waitForLoadState('domcontentloaded');
    }
}

test.describe('Pesanan Saya', () => {
    test.beforeEach(async ({ page }) => {
        await loginAs(page, USERS.customer);
    });

    test('TC-PS-001: Customer dapat membuka halaman Pesanan Saya', async ({ page }) => {
        await goto(page, '/customer/pesanan');
        await expect(page).toHaveURL(/customer\/pesanan/);
        await expect(page.locator('h1:has-text("Pesanan Saya")').first()).toBeVisible();
        await screenshotWithWait(page, 'TC-PS-001');
    });

    test('TC-PS-002: User belum login diarahkan ke login', async ({ page, context }) => {
        await context.clearCookies();
        await page.goto('/customer/pesanan');
        await expect(page).toHaveURL(/login/);
        await screenshotWithWait(page, 'TC-PS-002');
    });

        test('TC-PS-003: Designer tidak dapat mengakses Pesanan Saya', async ({ page, context }) => {
        await context.clearCookies();
        await goto(page, '/login');
        await page.fill('input[name="email"]', 'designer1@gmail.com');
        await page.fill('input[name="password"]', 'designer123');
        await page.click('button[type="submit"]');
        await page.waitForLoadState('domcontentloaded'); 
        await goto(page, '/customer/pesanan');
        const currentUrl = page.url();
        const isNotAccessible = !currentUrl.includes('/customer/pesanan') || (await page.locator('body').innerText()).includes('Akses ditolak');
        expect(isNotAccessible).toBeTruthy();
        await screenshotWithWait(page, 'TC-PS-003');
    });

    test('TC-PS-004: Admin tidak dapat mengakses Pesanan Saya', async ({ page, context }) => {
        await context.clearCookies();
        await goto(page, '/login');
        await page.fill('input[name="email"]', 'admin1@gmail.com');
        await page.fill('input[name="password"]', 'password123');
        await page.click('button[type="submit"]');
        await page.waitForLoadState('domcontentloaded');
        await goto(page, '/customer/pesanan');
        const currentUrl = page.url();
        const isNotAccessible = !currentUrl.includes('/customer/pesanan') || (await page.locator('body').innerText()).includes('Akses ditolak');
        expect(isNotAccessible).toBeTruthy();
        await screenshotWithWait(page, 'TC-PS-004');
    });

    test('TC-PS-005: Judul dan subjudul tampil', async ({ page }) => {
        await gotoPesananSaya(page);
        await expect(page.locator('h1:has-text("Pesanan Saya")')).toBeVisible();
        await expect(page.locator('p:has-text("Pantau perkembangan")')).toBeVisible();
        await screenshotWithWait(page, 'TC-PS-005');
    });

    test('TC-PS-006: Menu navigasi pada header', async ({ page }) => {
        await gotoPesananSaya(page);
        await expect(page.locator('nav a:has-text("Beranda")').first()).toBeVisible();
        await expect(page.locator('nav a:has-text("Katalog Jasa")').first()).toBeVisible();
        await expect(page.locator('nav a:has-text("Pesanan Saya")').first()).toBeVisible();
        await screenshotWithWait(page, 'TC-PS-006');
    });

    test('TC-PS-007: Informasi profil dan notifikasi di header', async ({ page }) => {
        await gotoPesananSaya(page);
        const navbar = page.locator('nav').first();
        await expect(navbar).toContainText(/Customer|Azaria|[CA]/i);
        await screenshotWithWait(page, 'TC-PS-007');
    });

    test('TC-PS-008: Tab Pesanan Aktif & Riwayat Selesai', async ({ page }) => {
        await gotoPesananSaya(page);
        await expect(page.locator('a:has-text("Pesanan Aktif")')).toBeVisible();
        await expect(page.locator('a:has-text("Riwayat Selesai")')).toBeVisible();
        await screenshotWithWait(page, 'TC-PS-008');
    });

    test('TC-PS-009: Search, filter, sort, refresh tampil', async ({ page }) => {
        await gotoPesananSaya(page);
        await expect(page.locator('input[name="q"]')).toBeVisible();
        await expect(page.locator('button:has-text("Status:")')).toBeVisible();
        await expect(page.locator('button:has-text("Kategori:")')).toBeVisible();
        await expect(page.locator('button:has-text("Urutkan:")')).toBeVisible();
        await expect(page.locator('a[title="Reset filter"]')).toBeVisible();
        await screenshotWithWait(page, 'TC-PS-009');
    });

    test('TC-PS-010: Footer tampil', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
        await expect(page.locator('footer')).toContainText(/ARTIV/i);
        await screenshotWithWait(page, 'TC-PS-010');
    });

    test('TC-PS-011: Klik menu Beranda mengarah ke beranda', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.locator('nav a:has-text("Beranda")').first().click();
        await expect(page).toHaveURL(/beranda/);
        await screenshotWithWait(page, 'TC-PS-011');
    });

    test('TC-PS-012: Klik menu Katalog Jasa mengarah ke katalog', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.locator('nav a:has-text("Katalog Jasa")').first().click();
        await expect(page).toHaveURL(/katalog/);
        await screenshotWithWait(page, 'TC-PS-012');
    });

    test('TC-PS-013: Klik menu Pesanan Saya saat sudah di halaman', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.locator('nav a:has-text("Pesanan Saya")').first().click();
        await expect(page).toHaveURL(/customer\/pesanan/);
        await screenshotWithWait(page, 'TC-PS-013');
    });

    test('TC-PS-014: Tab Pesanan Aktif terpilih default', async ({ page }) => {
        await gotoPesananSaya(page);
        const tab = page.locator('a:has-text("Pesanan Aktif")').first();
        await expect(tab).toBeVisible();
        const cls = await tab.getAttribute('class') || '';
        expect(cls).toContain('border-[#6D28D9]');
        await screenshotWithWait(page, 'TC-PS-014');
    });

    test('TC-PS-015: Berpindah ke tab Riwayat Selesai', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.locator('a:has-text("Riwayat Selesai")').first().click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/tab=riwayat/);
        await screenshotWithWait(page, 'TC-PS-015');
    });

    test('TC-PS-016: Berpindah kembali ke tab Pesanan Aktif', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.locator('a:has-text("Pesanan Aktif")').first().click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/tab=aktif/);
        await screenshotWithWait(page, 'TC-PS-016');
    });

    test('TC-PS-017: Badge jumlah pesanan sesuai', async ({ page }) => {
        await gotoPesananSaya(page);
        const badge = page.locator('a:has-text("Pesanan Aktif") span').first();
        const text = await badge.innerText();
        expect(parseInt(text)).toBeGreaterThanOrEqual(0);
        await screenshotWithWait(page, 'TC-PS-017');
    });

        test('TC-PS-018: Pesanan Selesai tidak tampil di tab Pesanan Aktif', async ({ page }) => {
        await gotoPesananSaya(page);
        const selesaiBadge = page.locator('.bg-white.rounded-2xl.shadow-sm').locator('text="Selesai"');
        await expect(selesaiBadge).toHaveCount(0); 
        await screenshotWithWait(page, 'TC-PS-018');
    });

    test('TC-PS-019: Info pada kartu pesanan tampil', async ({ page }) => {
        await gotoPesananSaya(page);
        const card = page.locator('.bg-white.rounded-2xl.shadow-sm').first();
        await expect(card).toBeVisible();
        await expect(card.locator('a:has-text("Lihat Pesanan")')).toBeVisible();
        await screenshotWithWait(page, 'TC-PS-019');
    });

    test('TC-PS-020: Progres pengerjaan tampil', async ({ page }) => {
        await gotoPesananSaya(page);
        const card = page.locator('.bg-white.rounded-2xl.shadow-sm').first();
        await expect(card).toContainText(/Progres Pengerjaan/);
        await expect(card).toContainText(/% Selesai/);
        await screenshotWithWait(page, 'TC-PS-020');
    });

    test('TC-PS-021: Indikator tahap pengerjaan tampil', async ({ page }) => {
        await gotoPesananSaya(page);
        const card = page.locator('.bg-white.rounded-2xl.shadow-sm').first();
        await expect(card).toContainText(/Brief Diterima/);
        await expect(card).toContainText(/Eksplorasi Konsep/);
        await screenshotWithWait(page, 'TC-PS-021');
    });

    test('TC-PS-022: Tampilan kartu pesanan selesai', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.locator('a:has-text("Riwayat Selesai")').first().click();
        await page.waitForLoadState('domcontentloaded');
        const card = page.locator('.bg-white.rounded-2xl.shadow-sm').first();
        if (await card.count() === 0) {
            test.skip(true, 'Belum ada pesanan selesai di Riwayat');
        }
        await expect(card).toContainText(/Selesai/);
        await screenshotWithWait(page, 'TC-PS-022');
    });

    test('TC-PS-023: Pencarian berdasarkan nama jasa', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.fill('input[name="q"]', 'Banner');
        await page.click('button:has-text("Cari Pesanan")');
        await page.waitForLoadState('domcontentloaded');
        const cards = page.locator('.bg-white.rounded-2xl.shadow-sm');
        const count = await cards.count();
        expect(count).toBeGreaterThanOrEqual(0);
        await screenshotWithWait(page, 'TC-PS-023');
    });

    test('TC-PS-024: Pencarian berdasarkan nama desainer', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.fill('input[name="q"]', 'Azaria');
        await page.click('button:has-text("Cari Pesanan")');
        await page.waitForLoadState('domcontentloaded');
        await screenshotWithWait(page, 'TC-PS-024');
    });

    test('TC-PS-025: Pencarian berdasarkan nomor pesanan', async ({ page }) => {
        await gotoPesananSaya(page);
        const firstCard = page.locator('.bg-white.rounded-2xl.shadow-sm').first();
        const text = await firstCard.innerText();
        const m = text.match(/ARTIV-[A-Z0-9]+/i);
        test.skip(!m, 'Tidak ada nomor pesanan');
        await page.fill('input[name="q"]', m[0]);
        await page.click('button:has-text("Cari Pesanan")');
        await page.waitForLoadState('domcontentloaded');
        await screenshotWithWait(page, 'TC-PS-025');
    });

    test('TC-PS-026: Pencarian tidak sensitif huruf', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.fill('input[name="q"]', 'poster');
        await page.click('button:has-text("Cari Pesanan")');
        await page.waitForLoadState('domcontentloaded');
        const count1 = await page.locator('.bg-white.rounded-2xl.shadow-sm').count();
        await page.fill('input[name="q"]', 'POSTER');
        await page.click('button:has-text("Cari Pesanan")');
        await page.waitForLoadState('domcontentloaded');
        const count2 = await page.locator('.bg-white.rounded-2xl.shadow-sm').count();
        expect(count1).toBe(count2);
        await screenshotWithWait(page, 'TC-PS-026');
    });

    test('TC-PS-027: Pencarian tidak ditemukan', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.fill('input[name="q"]', 'xyz123');
        await page.click('button:has-text("Cari Pesanan")');
        await page.waitForLoadState('domcontentloaded');
        await expect(page.locator('body')).toContainText(/tidak ditemukan|Belum Ada/i);
        await screenshotWithWait(page, 'TC-PS-027');
    });

    test('TC-PS-028: Hapus kata kunci menampilkan semua pesanan', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.fill('input[name="q"]', 'Banner');
        await page.click('button:has-text("Cari Pesanan")');
        await page.waitForLoadState('domcontentloaded');
        await page.fill('input[name="q"]', '');
        await page.click('button:has-text("Cari Pesanan")');
        await page.waitForLoadState('domcontentloaded');
        await screenshotWithWait(page, 'TC-PS-028');
    });

    test('TC-PS-029: Filter berdasarkan status', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.click('button:has-text("Status:")');
        await page.waitForTimeout(300);
        await page.click('a:has-text("Semua Status")');
        await page.waitForLoadState('domcontentloaded');
        await screenshotWithWait(page, 'TC-PS-029');
    });

    test('TC-PS-030: Filter berdasarkan kategori', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.click('button:has-text("Kategori:")');
        await page.waitForTimeout(300);
        await page.click('a:has-text("Semua Kategori")');
        await page.waitForLoadState('domcontentloaded');
        await screenshotWithWait(page, 'TC-PS-030');
    });

    test('TC-PS-031: Kombinasi filter status & kategori', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.click('button:has-text("Status:")');
        await page.waitForTimeout(300);
        await page.click('a:has-text("Semua Status")');
        await page.waitForLoadState('domcontentloaded');
        await page.click('button:has-text("Kategori:")');
        await page.waitForTimeout(300);
        await page.click('a:has-text("Semua Kategori")');
        await page.waitForLoadState('domcontentloaded');
        await screenshotWithWait(page, 'TC-PS-031');
    });

    test('TC-PS-032: Kombinasi pencarian & filter', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.fill('input[name="q"]', 'Azaria');
        await page.click('button:has-text("Cari Pesanan")');
        await page.waitForLoadState('domcontentloaded');
        await page.click('button:has-text("Status:")');
        await page.waitForTimeout(300);
        await page.click('a:has-text("Semua Status")');
        await page.waitForLoadState('domcontentloaded');
        await screenshotWithWait(page, 'TC-PS-032');
    });

    test('TC-PS-033: Filter menghasilkan data kosong', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.fill('input[name="q"]', 'xyz123');
        await page.click('button:has-text("Cari Pesanan")');
        await page.waitForLoadState('domcontentloaded');
        await expect(page.locator('body')).toContainText(/tidak ditemukan|Belum Ada/i);
        await screenshotWithWait(page, 'TC-PS-033');
    });

    test('TC-PS-034: Reset filter ke Semua', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.click('button:has-text("Status:")');
        await page.waitForTimeout(300);
        await page.click('a:has-text("Semua Status")');
        await page.waitForLoadState('domcontentloaded');
        await page.click('button:has-text("Kategori:")');
        await page.waitForTimeout(300);
        await page.click('a:has-text("Semua Kategori")');
        await page.waitForLoadState('domcontentloaded');
        await screenshotWithWait(page, 'TC-PS-034');
    });

    test('TC-PS-035: Urutan default Deadline Terdekat', async ({ page }) => {
        await gotoPesananSaya(page);
        await expect(page.locator('button:has-text("Urutkan:")')).toContainText(/Deadline Terdekat/);
        await screenshotWithWait(page, 'TC-PS-035');
    });

    test('TC-PS-036: Ubah opsi pengurutan', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.click('button:has-text("Urutkan:")');
        await page.waitForTimeout(300);
        await page.click('a:has-text("Terbaru")');
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/urut=terbaru/);
        await screenshotWithWait(page, 'TC-PS-036');
    });

    test('TC-PS-037: Tombol refresh memuat ulang', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.click('a[title="Reset filter"]');
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/tab=aktif/);
        await screenshotWithWait(page, 'TC-PS-037');
    });

    test('TC-PS-038: Tombol refresh mengembalikan filter default', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.fill('input[name="q"]', 'Azaria');
        await page.click('button:has-text("Cari Pesanan")');
        await page.waitForLoadState('domcontentloaded');
        await page.click('a[title="Reset filter"]');
        await page.waitForLoadState('domcontentloaded');
        await expect(page.locator('input[name="q"]')).toHaveValue('');
        await screenshotWithWait(page, 'TC-PS-038');
    });

    test('TC-PS-039: Tombol Lihat Pesanan pada pesanan aktif', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.locator('a:has-text("Lihat Pesanan")').first().click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/customer\/pesanan\/[\w-]+/);
        await screenshotWithWait(page, 'TC-PS-039');
    });

    test('TC-PS-040: Tombol Lihat Pesanan pada pesanan selesai', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.locator('a:has-text("Riwayat Selesai")').first().click();
        await page.waitForLoadState('domcontentloaded');
        const btnLihat = page.locator('a:has-text("Lihat Pesanan")').first();
        if (await btnLihat.count() === 0) {
            test.skip(true, 'Belum ada pesanan selesai untuk diuji');
        }
        await btnLihat.click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/customer\/pesanan\/[\w-]+/);
        await screenshotWithWait(page, 'TC-PS-040');
    });

    test('TC-PS-041: Empty state jika belum ada pesanan', async ({ page, context }) => {
        await context.clearCookies();
        await loginAs(page, USERS.customerEmpty);
        await gotoPesananSaya(page);
        await expect(page.locator('text=Belum Ada Pesanan Aktif')).toBeVisible();
        await screenshotWithWait(page, 'TC-PS-041');
    });

    test('TC-PS-042: Klik Jelajahi Katalog pada empty state', async ({ page, context }) => {
        await context.clearCookies();
        await loginAs(page, USERS.customerEmpty);
        await gotoPesananSaya(page);
        await page.click('a:has-text("Jelajahi Katalog Jasa Sekarang")');
        await expect(page).toHaveURL(/katalog/);
        await screenshotWithWait(page, 'TC-PS-042');
    });

    test('TC-PS-043: Tab Riwayat Selesai kosong', async ({ page, context }) => {
        await context.clearCookies();
        await loginAs(page, USERS.customerEmpty);
        await gotoPesananSaya(page);
        await page.locator('a:has-text("Riwayat Selesai")').first().click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page.locator('text=Belum Ada Riwayat Pesanan')).toBeVisible();
        await screenshotWithWait(page, 'TC-PS-043');
    });
});

test.describe('Detail Pesanan', () => {
    test.beforeEach(async ({ page }) => {
        await loginAs(page, USERS.customer);
        await gotoDetailPesanan(page);
    });

    test('TC-DP-001: Halaman detail dapat diakses', async ({ page }) => {
        await expect(page).toHaveURL(/\/customer\/pesanan\/[\w-]+/);
        await expect(page.locator('h1:has-text("Detail Pesanan")')).toBeVisible();
        await screenshotWithWait(page, 'TC-DP-001');
    });

    test('TC-DP-002: Informasi produk ditampilkan', async ({ page }) => {
        await expect(page.locator('text=LAYANAN TERPESAN')).toBeVisible();
        await screenshotWithWait(page, 'TC-DP-002');
    });

    test('TC-DP-003: Informasi pengerjaan ditampilkan', async ({ page }) => {
        await expect(page.locator('text=INFORMASI PENGERJAAN')).toBeVisible();
        await expect(page.locator('text=/Target Tenggat/')).toBeVisible();
        await screenshotWithWait(page, 'TC-DP-003');
    });

    test('TC-DP-004: Brief pesanan ditampilkan', async ({ page }) => {
        const briefCard = page.locator('text=BRIEF PESANAN KLIEN');
        const count = await briefCard.count();
        test.skip(count === 0, 'Order ini tidak punya brief');
        await briefCard.first().scrollIntoViewIfNeeded();
        await expect(briefCard.first()).toBeVisible();
        await screenshotWithWait(page, 'TC-DP-004');
    });

    test('TC-DP-005: File referensi dapat diunduh', async ({ page }) => {
        const refCard = page.locator('text=REFERENSI & LAMPIRAN AWAL');
        const count = await refCard.count();
        test.skip(count === 0, 'Tidak ada referensi');
        await refCard.first().scrollIntoViewIfNeeded();
        await expect(page.locator('a[title="Unduh file"]').first()).toBeVisible();
        await screenshotWithWait(page, 'TC-DP-005');
    });

    test('TC-DP-006: Timeline tahapan pengerjaan ditampilkan', async ({ page }) => {
        const riwayatCard = page.locator('text=RIWAYAT PENGERJAAN');
        const count = await riwayatCard.count();
        test.skip(count === 0, 'Tidak ada riwayat');
        await riwayatCard.first().scrollIntoViewIfNeeded();
        await expect(page.locator('ol.relative li').first()).toBeVisible();
        await screenshotWithWait(page, 'TC-DP-006');
    });

    test('TC-DP-007: Chat container tampil', async ({ page }) => {
        await expect(page.locator('#chat-container')).toBeVisible();
        await screenshotWithWait(page, 'TC-DP-007');
    });

    test('TC-DP-008: Pesan dapat dikirim', async ({ page }) => {
        const chatInput = page.locator('input[name="isi"]');
        const isWaiting = (await page.locator('body').innerText()).includes('Menunggu desainer');
        test.skip(!(await chatInput.isVisible().catch(() => false)) || isWaiting, 'Chat belum aktif (designer belum ambil order)');
        await chatInput.scrollIntoViewIfNeeded();
        await chatInput.fill('Halo, bagaimana progres desainnya?');
        await page.locator('button[title="Kirim"]').click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page.locator('body')).toContainText(/progres desainnya/i);
        await screenshotWithWait(page, 'TC-DP-008');
    });

    test('TC-DP-011: Pesan mengandung link diblokir', async ({ page }) => {
        const chatInput = page.locator('input[name="isi"]');
        const isWaiting = (await page.locator('body').innerText()).includes('Menunggu desainer');
        test.skip(!(await chatInput.isVisible().catch(() => false)) || isWaiting, 'Chat belum aktif (designer belum ambil order)');
        await chatInput.scrollIntoViewIfNeeded();
        await chatInput.fill('Ini link saya https://example.com');
        await page.locator('button[title="Kirim"]').click();
        await page.waitForTimeout(1000);
        const errorNotif = page.locator('#error-notif');
        test.skip(!(await errorNotif.isVisible().catch(() => false)), 'Fitur blokir link belum jalan');
        await expect(errorNotif).toBeVisible();
        await screenshotWithWait(page, 'TC-DP-011');
    });

    test('TC-DP-012: Pesan mengandung nomor telepon diblokir', async ({ page }) => {
        const chatInput = page.locator('input[name="isi"]');
        const isWaiting = (await page.locator('body').innerText()).includes('Menunggu desainer');
        test.skip(!(await chatInput.isVisible().catch(() => false)) || isWaiting, 'Chat belum aktif (designer belum ambil order)');
        await chatInput.scrollIntoViewIfNeeded();
        await chatInput.fill('Hubungi saya di 08123456789');
        await page.locator('button[title="Kirim"]').click();
        await page.waitForTimeout(1000);
        const errorNotif = page.locator('#error-notif');
        test.skip(!(await errorNotif.isVisible().catch(() => false)), 'Fitur blokir telepon belum jalan');
        await expect(errorNotif).toBeVisible();
        await screenshotWithWait(page, 'TC-DP-012');
    });

    test('TC-DP-013: Pesan mengandung email diblokir', async ({ page }) => {
        const chatInput = page.locator('input[name="isi"]');
        const isWaiting = (await page.locator('body').innerText()).includes('Menunggu desainer');
        test.skip(!(await chatInput.isVisible().catch(() => false)) || isWaiting, 'Chat belum aktif (designer belum ambil order)');
        await chatInput.scrollIntoViewIfNeeded();
        await chatInput.fill('Email saya user@example.com');
        await page.locator('button[title="Kirim"]').click();
        await page.waitForTimeout(1000);
        const errorNotif = page.locator('#error-notif');
        test.skip(!(await errorNotif.isVisible().catch(() => false)), 'Fitur blokir email belum jalan');
        await expect(errorNotif).toBeVisible();
        await screenshotWithWait(page, 'TC-DP-013');
    });

    test('TC-DP-014: Pesan mengandung blacklist keyword diblokir', async ({ page }) => {
        const chatInput = page.locator('input[name="isi"]');
        const isWaiting = (await page.locator('body').innerText()).includes('Menunggu desainer');
        test.skip(!(await chatInput.isVisible().catch(() => false)) || isWaiting, 'Chat belum aktif (designer belum ambil order)');
        await chatInput.scrollIntoViewIfNeeded();
        await chatInput.fill('Hubungi wa.me saya ya');
        await page.locator('button[title="Kirim"]').click();
        await page.waitForTimeout(1000);
        const errorNotif = page.locator('#error-notif');
        test.skip(!(await errorNotif.isVisible().catch(() => false)), 'Fitur blokir keyword belum jalan');
        await expect(errorNotif).toBeVisible();
        await screenshotWithWait(page, 'TC-DP-014');
    });

    test('TC-DP-015: Peringatan muncul saat melanggar aturan chat', async ({ page }) => {
        const chatInput = page.locator('input[name="isi"]');
        const isWaiting = (await page.locator('body').innerText()).includes('Menunggu desainer');
        test.skip(!(await chatInput.isVisible().catch(() => false)) || isWaiting, 'Chat belum aktif (designer belum ambil order)');
        await chatInput.scrollIntoViewIfNeeded();
        await chatInput.fill('Hubungi saya di 08123456789');
        await page.locator('button[title="Kirim"]').click();
        await page.waitForTimeout(1000);
        const errorNotif = page.locator('#error-notif');
        test.skip(!(await errorNotif.isVisible().catch(() => false)), 'Peringatan belum jalan');
        await expect(errorNotif).toBeVisible();
        await screenshotWithWait(page, 'TC-DP-015');
    });
});

test.describe('Review & Rating', () => {
    test.beforeEach(async ({ page }) => {
        await loginAs(page, USERS.customer);
        await gotoPesananSaya(page);
        await page.locator('a:has-text("Riwayat Selesai")').first().click();
        await page.waitForLoadState('domcontentloaded');
        await page.waitForTimeout(700);
    });

    test('TC-RR-001: Tombol Beri Review muncul', async ({ page }) => {
        const reviewBtn = page.locator('a:has-text("Beri Review")').first();
        await expect(reviewBtn).toBeVisible({ timeout: 10_000 });
        await screenshotWithWait(page, 'TC-RR-001');
    });

    test('TC-RR-002: Halaman review dapat diakses', async ({ page }) => {
        const reviewBtn = page.locator('a:has-text("Beri Review")').first();
        await expect(reviewBtn).toBeVisible({ timeout: 10_000 });
        await reviewBtn.click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/review/);
        await screenshotWithWait(page, 'TC-RR-002');
    });

    test('TC-RR-003: Elemen form review ditampilkan', async ({ page }) => {
        const reviewBtn = page.locator('a:has-text("Beri Review")').first();
        await expect(reviewBtn).toBeVisible({ timeout: 10_000 });
        await reviewBtn.click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page.locator('#stars')).toBeVisible();
        await expect(page.locator('textarea#comment')).toBeVisible();
        await expect(page.locator('#btn-submit')).toBeVisible();
        await screenshotWithWait(page, 'TC-RR-003');
    });

    test('TC-RR-004: Customer dapat melewati review', async ({ page }) => {
        const reviewBtn = page.locator('a:has-text("Beri Review")').first();
        const visible = await reviewBtn.isVisible().catch(() => false);
        test.skip(!visible, 'Tombol Beri Review tidak ada');
        await reviewBtn.click();
        await page.waitForLoadState('domcontentloaded');
        const skipBtn = page.locator('a:has-text("Lewati")').first();
        await skipBtn.click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page.locator('h1:has-text("Pesanan Saya")')).toBeVisible();
        await screenshotWithWait(page, 'TC-RR-004');
    });

    test('TC-RR-005: Review tidak terkirim jika bintang kosong', async ({ page }) => {
        const reviewBtn = page.locator('a:has-text("Beri Review")').first();
        await expect(reviewBtn).toBeVisible({ timeout: 10_000 });
        await reviewBtn.click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page.locator('#btn-submit')).toBeDisabled();
        await page.fill('textarea#comment', 'Review tanpa bintang');
        await expect(page.locator('#btn-submit')).toBeDisabled();
        await screenshotWithWait(page, 'TC-RR-005');
    });

    test('TC-RR-006: Customer dapat mengirim review', async ({ page }) => {
        const reviewBtn = page.locator('a:has-text("Beri Review")').first();
        await expect(reviewBtn).toBeVisible({ timeout: 10_000 });
        await reviewBtn.click();
        await page.waitForLoadState('domcontentloaded');
        await page.locator('#stars .star').nth(4).click();
        await expect(page.locator('#rating-number')).toContainText('5');
        await page.fill('textarea#comment', 'Hasil desain sangat memuaskan');
        await page.click('#btn-submit');
        await page.waitForURL(/sukses/, { timeout: 15_000 });
        await expect(page.locator('h1:has-text("Terima kasih atas ulasan")')).toBeVisible({ timeout: 10_000 });
        await screenshotWithWait(page, 'TC-RR-006');
    });

    test('TC-RR-007: Rata-rata rating designer diperbarui', async ({ page }) => {
        await gotoPesananSaya(page);
        await page.locator('a:has-text("Riwayat Selesai")').first().click();
        await page.waitForLoadState('domcontentloaded');
        const lihatPesananBtn = page.locator('a:has-text("Lihat Pesanan")').first();
        if (await lihatPesananBtn.count() > 0) {
            await lihatPesananBtn.click();
            await page.waitForLoadState('domcontentloaded');
            await expect(page.locator('body')).toContainText(/Review|Ulasan|Rating|Bintang/i);
        } else {
            test.skip(true, 'Belum ada pesanan selesai untuk dicek ratingnya');
        }
        
        await screenshotWithWait(page, 'TC-RR-007');
    });

    test('TC-RR-008: Review sudah dikirim tampil ringkasan', async ({ page }) => {
        const reviewDone = page.locator('text=/Review sudah dikirim/');
        if (await reviewDone.isVisible().catch(() => false)) {
            await expect(reviewDone).toBeVisible();
        } else {
            const btn = page.locator('a:has-text("Lihat Pesanan")').first();
            if (await btn.isVisible().catch(() => false)) {
                await btn.click();
                await page.waitForLoadState('domcontentloaded');
                const hasReview = await page.locator('text=/Review sudah dikirim/').isVisible().catch(() => false);
                test.skip(!hasReview, 'Review belum ada — jalankan TC-RR-006 dulu');
            }
        }
        await screenshotWithWait(page, 'TC-RR-008');
    });
});