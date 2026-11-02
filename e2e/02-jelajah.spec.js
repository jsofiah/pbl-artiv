// @ts-check
import { test, expect } from '@playwright/test';

// ============================================================================
// KONSTANTA
// ============================================================================
const PRODUCT_WITH_TIER = '01a0fd63-bbcc-7368-9e94-580091555067';
const PRODUCT_NO_TIER   = '01a0fd63-bbdd-70c2-86f7-4a255707894b';
const DETAIL_URL_RE     = /\/customer\/katalog\/[A-Za-z0-9_-]+/;

const CUSTOMER_EMAIL    = 'customer1@gmail.com';
const CUSTOMER_PASSWORD = 'customer123';

// ============================================================================
// HELPER
// ============================================================================

async function goto(page, url) {
    await page.goto(url, {
        waitUntil: 'domcontentloaded',
        timeout: 15_000,
    });
}

async function skipIntro(context) {
    await context.addInitScript(() => {
        try {
            sessionStorage.setItem('artiv-intro-seen', '1');
        } catch {}

        const style = document.createElement('style');
        style.textContent = `
            *, *::before, *::after {
                animation-duration: 0s !important;
                animation-delay: 0s !important;
                transition-duration: 0s !important;
                transition-delay: 0s !important;
                scroll-behavior: auto !important;
            }
        `;

        document.addEventListener('DOMContentLoaded', () => {
            document.head.appendChild(style);
        });

        if (document.head) {
            document.head.appendChild(style);
        }
    });
}

/**
 * Login sebagai customer.
 */
async function loginAsCustomer(page) {
    await goto(page, '/login');
    await page.fill('input[name="email"]', CUSTOMER_EMAIL);
    await page.fill('input[name="password"]', CUSTOMER_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/customer\/beranda/, { timeout: 15_000 });
}

/**
 * Screenshot evidence — tunggu CSS/gambar load dulu.
 * Hasil: evidence/TC-XXX.png
 */
async function screenshotWithWait(page, tcId) {
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(1000);
    await page.screenshot({
        path: `evidence/${tcId}.png`,
        fullPage: true,
    });
}

// ============================================================================
// BAGIAN 1 — BERANDA (Guest)
// ============================================================================
test.describe('Beranda — Konten Lengkap (Guest)', () => {
    test.beforeEach(async ({ context, page }) => {
        await skipIntro(context);
        await goto(page, '/customer/beranda');
        await expect(page).toHaveURL(/\/customer\/beranda/);
    });

    test('TC-BR-001 · Hero tampil dengan judul, subjudul, dan CTA', async ({ page }) => {
        await expect(page).toHaveTitle(/Beranda — Artiv/i);
        await expect(page.locator('h1').first()).toContainText(/artiv/i);

        await expect(page.getByText(/tempat ide kreatifmu bertemu desainer/i)).toBeVisible();
        await expect(page.getByText(/platform jasa kreatif untuk kebutuhanmu/i)).toBeVisible();

        await expect(
            page.getByRole('link', { name: /pesan jasa sekarang/i }).first()
        ).toBeVisible();

        await expect(
            page.getByRole('link', { name: /lihat cara kerja/i })
        ).toBeVisible();

        await screenshotWithWait(page, 'TC-BR-001');
    });

    test('TC-BR-002 · Ticker kategori jasa berjalan (marquee)', async ({ page }) => {
        const ticker = page.locator('.artiv-marquee').first();
        await expect(ticker).toBeAttached();

        await expect(ticker.getByText(/banner/i).first()).toBeAttached();
        await expect(ticker.getByText(/poster/i).first()).toBeAttached();
        await expect(ticker.getByText(/logo/i).first()).toBeAttached();

        await screenshotWithWait(page, 'TC-BR-002');
    });

    test('TC-BR-003 · Section "Cara Kerja" tampil (fallback statis / GSAP)', async ({ page }) => {
        const howSection = page.locator('#artiv-how-scroll');
        await expect(howSection).toBeAttached();

        await expect(
            page.getByRole('heading', { name: /cara kerja/i })
        ).toBeVisible();

        await expect(
            page.getByRole('heading', { name: /pilih jasa.*buat pesanan/i })
        ).toBeAttached();

        await expect(
            page.getByRole('heading', { name: /dikerjakan desainer/i })
        ).toBeAttached();

        await expect(
            page.getByRole('heading', { name: /approval.*ulasan/i })
        ).toBeAttached();

        await screenshotWithWait(page, 'TC-BR-003');
    });

    test('TC-BR-004 · Section "Layanan Kategori Jasa" tampil', async ({ page }) => {
        await expect(
            page.getByRole('heading', { name: /layanan kategori jasa/i })
        ).toBeVisible();

        await expect(
            page.getByText(/jelajahi berbagai kategori jasa desain/i)
        ).toBeVisible();

        const kategoriLinks = page.locator('a[href*="categories="]');
        expect(await kategoriLinks.count()).toBeGreaterThan(0);

        await screenshotWithWait(page, 'TC-BR-004');
    });

    test('TC-BR-005 · Klik kategori → ke Katalog dengan filter', async ({ page }) => {
        const firstKategori = page.locator('a[href*="categories="]').first();
        const href = await firstKategori.getAttribute('href');
        expect(href).toContain('categories=');

        await screenshotWithWait(page, 'TC-BR-005');

        await firstKategori.click();
        await expect(page).toHaveURL(/categories=/);
        await expect(page).toHaveURL(/\/customer\/katalog/);
    });

    test('TC-BR-006 · Section "Katalog Jasa Desain Populer" tampil dengan kartu produk', async ({ page }) => {
        await expect(
            page.getByRole('heading', { name: /katalog jasa desain populer/i })
        ).toBeVisible();

        const cards = page.locator('a[href*="/customer/katalog/"]');
        expect(await cards.count()).toBeGreaterThan(0);

        const first = cards.first();
        await expect(first.locator('h3').first()).toBeVisible();
        await expect(first.getByText(/Rp\s?[\d.]+/).first()).toBeVisible();
        await expect(first).toContainText(/detail/i);

        await screenshotWithWait(page, 'TC-BR-006');
    });

    test('TC-BR-007 · Klik kartu populer → masuk Detail Katalog', async ({ page }) => {
        const firstCard = page.locator('a[href*="/customer/katalog/"]').first();
        const href = await firstCard.getAttribute('href');
        expect(href).toMatch(DETAIL_URL_RE);

        await screenshotWithWait(page, 'TC-BR-007');

        await firstCard.click();
        await expect(page).toHaveURL(DETAIL_URL_RE);
        await expect(page.locator('h1').first()).toBeVisible();
    });

    test('TC-BR-008 · Section testimoni "Dipercaya Kreator & Bisnis" tampil', async ({ page }) => {
        await expect(
            page.getByRole('heading', { name: /dipercaya kreator.*bisnis/i })
        ).toBeVisible();

        await expect(
            page.getByText(/ribuan klien sudah mewujudkan ide kreatifnya/i)
        ).toBeVisible();

        const testimonialsWrapper = page.locator('.animate-canopy-horizontal').first();
        await expect(testimonialsWrapper).toBeAttached({ timeout: 10_000 });

        const cards = testimonialsWrapper.locator('[class*="rounded-xl"]');
        const cardCount = await cards.count();
        expect(cardCount).toBeGreaterThan(0);

        const firstCard = cards.first();
        await expect(firstCard.locator('.font-bold').first()).toBeVisible();
        await expect(firstCard.locator('p').first()).toBeVisible();

        await screenshotWithWait(page, 'TC-BR-008');
    });

    test('TC-BR-009 · Section CTA "Siap Memulai Proyek Desainmu" tampil', async ({ page }) => {
        await expect(
            page.getByRole('heading', { name: /siap memulai proyek desainmu bersama kreator/i })
        ).toBeVisible();

        await expect(
            page.getByRole('link', { name: /jelajahi katalog sekarang/i })
        ).toBeVisible();

        await screenshotWithWait(page, 'TC-BR-009');
    });

    test('TC-BR-010 · Klik CTA "Jelajahi Katalog Sekarang" → masuk Katalog', async ({ page }) => {
        await screenshotWithWait(page, 'TC-BR-010');

        await page.getByRole('link', { name: /jelajahi katalog sekarang/i }).click();
        await expect(page).toHaveURL(/\/customer\/katalog$/);
    });

    test('TC-BR-011 · Footer tampil dengan informasi lengkap', async ({ page }) => {
        const footer = page.locator('footer').last();
        await expect(footer).toBeVisible();
        await expect(footer).toContainText(/artiv/i);

        const footerLinks = footer.getByRole('link');
        expect(await footerLinks.count()).toBeGreaterThan(0);

        await screenshotWithWait(page, 'TC-BR-011');
    });

    test('TC-BR-012 · Navbar tampil lengkap (logo, menu, tombol masuk)', async ({ page }) => {
        const navbar = page.locator('nav').first();
        await expect(navbar).toBeVisible();

        await expect(navbar.getByRole('link', { name: /beranda/i })).toBeVisible();
        await expect(navbar.getByRole('link', { name: /katalog/i })).toBeVisible();
        await expect(navbar.getByRole('link', { name: /masuk/i })).toBeVisible();
        await expect(navbar.getByRole('link', { name: /daftar/i })).toBeVisible();

        await screenshotWithWait(page, 'TC-BR-012');
    });
});

// ============================================================================
// BAGIAN 2 — KATALOG: Akses & Konten (Guest)
// ============================================================================
test.describe('Katalog — Akses & Konten (Guest)', () => {

    test('TC-KT-002 · Guest dapat mengakses halaman Katalog langsung via URL', async ({ page }) => {
        await goto(page, '/customer/katalog');
        await expect(page).toHaveURL(/\/customer\/katalog$/);
        await expect(page).toHaveTitle(/Katalog Jasa — Artiv/i);
        await expect(page.getByRole('heading', { name: /butuh desain/i })).toBeVisible();
        await expect(page).not.toHaveURL(/\/login/);

        await screenshotWithWait(page, 'TC-KT-002');
    });

    test('TC-KT-003 · Kartu produk menampilkan thumbnail, nama, dan harga', async ({ page }) => {
        await goto(page, '/customer/katalog');
        await expect(page.getByRole('heading', { name: /butuh desain/i })).toBeVisible();

        const cards = page.locator('a[href*="/customer/katalog/"]');
        await expect(cards.first()).toBeVisible();
        expect(await cards.count()).toBeGreaterThan(0);

        const first = cards.first();
        await expect(first.locator('h3').first()).toBeVisible();
        await expect(first.getByText(/Rp\s?[\d.]+/).first()).toBeVisible();

        const imgVisible  = await first.locator('img').first().isVisible().catch(() => false);
        const spanVisible = await first.locator('span.text-5xl').first().isVisible().catch(() => false);
        expect(imgVisible || spanVisible).toBeTruthy();

        await screenshotWithWait(page, 'TC-KT-003');
    });
});

// ============================================================================
// BAGIAN 3 — KATALOG: Search & Filter (Guest)
// ============================================================================
test.describe('Katalog — Search & Filter (Guest)', () => {
    test.beforeEach(async ({ page }) => {
        await goto(page, '/customer/katalog');
        await expect(page.getByRole('heading', { name: /butuh desain/i })).toBeVisible();
    });

    test('TC-KT-004 · Search produk → URL berubah & hasil tampil', async ({ page }) => {
        await page.getByPlaceholder(/cari jasa desain/i).fill('banner');
        await page.getByRole('button', { name: /cari jasa/i }).click();

        await expect(page).toHaveURL(/[?&]q=banner/);
        await expect(page.getByText(/menampilkan/i).first()).toBeVisible();

        const cards = page.locator('a[href*="/customer/katalog/"]');
        expect(await cards.count()).toBeGreaterThan(0);

        await screenshotWithWait(page, 'TC-KT-004');
    });

    test('TC-KT-005 · Search tanpa hasil → empty state muncul', async ({ page }) => {
        await goto(page, '/customer/katalog?q=zzzxxyyqq_notexist_123');

        await expect(page.getByText(/jasa tidak ditemukan/i)).toBeVisible();
        await expect(page.getByRole('link', { name: /lihat semua jasa/i })).toBeVisible();

        const cards = page.locator('a[href*="/customer/katalog/"]');
        expect(await cards.count()).toBe(0);

        await screenshotWithWait(page, 'TC-KT-005');
    });

    test('TC-KT-006 · Tombol "Reset filter" mengembalikan ke katalog bersih', async ({ page }) => {
        await goto(page, '/customer/katalog?q=banner');
        await expect(page.getByText(/menampilkan/i).first()).toBeVisible();

        await page.getByRole('link', { name: /reset filter/i }).click();
        await expect(page).toHaveURL(/\/customer\/katalog$/);

        const cards = page.locator('a[href*="/customer/katalog/"]');
        expect(await cards.count()).toBeGreaterThan(0);

        await screenshotWithWait(page, 'TC-KT-006');
    });

    test('TC-KT-007 · Klik kategori → filter kategori di URL & hasil muncul', async ({ page }) => {
        const kategoriLinks = page.locator('a[href*="category="]');
        const count = await kategoriLinks.count();
        test.skip(count === 0, 'Tidak ada kategori di DB test');

        const firstCat = kategoriLinks.first();
        const catText = (await firstCat.textContent())?.trim() ?? '';
        expect(catText.length).toBeGreaterThan(0);

        await firstCat.click();
        await expect(page).toHaveURL(/[?&]category=/);
        await expect(page.getByText(/menampilkan/i).first()).toBeVisible();

        await expect(firstCat).toHaveClass(/bg-\[#6D28D9\]/);

        await screenshotWithWait(page, 'TC-KT-007');
    });

    test('TC-KT-008 · Tombol "Semua" menghapus filter kategori', async ({ page }) => {
        await goto(page, '/customer/katalog?category=Logo');

        const semuaBtn = page.getByRole('link', { name: /^semua$/i }).first();
        await semuaBtn.click();
        await expect(page).not.toHaveURL(/[?&]category=/);

        await screenshotWithWait(page, 'TC-KT-008');
    });

    test('TC-KT-009 · Sort dropdown → "Termurah" berubah di URL & label', async ({ page }) => {
        const sortBtn = page.getByRole('button', { name: /urutkan:/i });
        await sortBtn.click();

        const termurahLink = page.getByRole('link', { name: /^termurah$/i });
        await termurahLink.click();

        await expect(page).toHaveURL(/[?&]sort=cheapest/);
        await expect(sortBtn).toContainText(/termurah/i);

        await screenshotWithWait(page, 'TC-KT-009');
    });

    test('TC-KT-010 · Sort dropdown → "Termahal" berubah di URL & label', async ({ page }) => {
        const sortBtn = page.getByRole('button', { name: /urutkan:/i });
        await sortBtn.click();

        const termahalLink = page.getByRole('link', { name: /^termahal$/i });
        await termahalLink.click();

        await expect(page).toHaveURL(/[?&]sort=expensive/);
        await expect(sortBtn).toContainText(/termahal/i);

        await screenshotWithWait(page, 'TC-KT-010');
    });

    test('TC-KT-011 · Sort "Termurah" benar-benar mengurutkan harga naik', async ({ page }) => {
        await goto(page, '/customer/katalog?sort=cheapest');

        const priceTexts = await page
            .locator('a[href*="/customer/katalog/"] .font-extrabold.text-\\[\\#6D28D9\\]')
            .allTextContents();

        test.skip(priceTexts.length < 2, 'Butuh minimal 2 produk untuk cek urutan');

        const prices = priceTexts
            .map(t => Number(t.replace(/[^\d]/g, '')))
            .filter(n => n > 0);

        for (let i = 1; i < prices.length; i++) {
            expect(prices[i]).toBeGreaterThanOrEqual(prices[i - 1]);
        }

        await screenshotWithWait(page, 'TC-KT-011');
    });

    test('TC-KT-012 · Kombinasi search + kategori + sort', async ({ page }) => {
        await goto(page, '/customer/katalog?q=banner&sort=cheapest');
        await expect(page).toHaveURL(/[?&]q=banner/);
        await expect(page).toHaveURL(/[?&]sort=cheapest/);

        await expect(page.getByText(/menampilkan/i).first()).toBeVisible();

        await screenshotWithWait(page, 'TC-KT-012');
    });
});

// ============================================================================
// BAGIAN 4 — DETAIL KATALOG: Tier vs Non-Tier (Guest)
// ============================================================================
test.describe('Detail Katalog — Tier vs Non-Tier (Guest)', () => {

    test('TC-DK-001 · Klik kartu produk membuka halaman Detail Katalog', async ({ page }) => {
        await goto(page, '/customer/katalog');
        await expect(page.getByRole('heading', { name: /butuh desain/i })).toBeVisible();

        const firstCard = page.locator('a[href*="/customer/katalog/"]').first();
        const href = await firstCard.getAttribute('href');
        expect(href).toMatch(DETAIL_URL_RE);

        await firstCard.click();
        await expect(page).toHaveURL(DETAIL_URL_RE);
        await expect(page.locator('nav').nth(1)).toContainText(/katalog jasa/i);
        await expect(page.locator('h1').first()).toBeVisible();

        await screenshotWithWait(page, 'TC-DK-001');
    });

    test('TC-DK-002a · Produk BERTIER menampilkan section "Level Desain"', async ({ page }) => {
        await goto(page, `/customer/katalog/${PRODUCT_WITH_TIER}`);
        await expect(page.locator('h1').first()).toBeVisible();

        await expect(
            page.getByRole('heading', { name: /level desain/i })
        ).toBeVisible();

        const tierCards = page.locator('div')
            .filter({ hasText: /PAKET DASAR|REKOMENDASI|SOLUSI LENGKAP/ });
        await expect(tierCards.first()).toBeVisible();
        await expect(tierCards.first().getByText(/Rp\s?[\d.]+/).first()).toBeVisible();

        await screenshotWithWait(page, 'TC-DK-002a');
    });

    test('TC-DK-002b · Produk NON-TIER TIDAK menampilkan section "Level Desain"', async ({ page }) => {
        await goto(page, `/customer/katalog/${PRODUCT_NO_TIER}`);
        await expect(page.locator('h1').first()).toBeVisible();

        await expect(
            page.getByRole('heading', { name: /level desain/i })
        ).toHaveCount(0);

        await expect(page.getByText(/mulai dari/i).first()).toBeVisible();
        await expect(
            page.getByRole('link', { name: /pesan jasa sekarang/i })
        ).toBeVisible();

        await screenshotWithWait(page, 'TC-DK-002b');
    });

});

// ============================================================================
// BAGIAN 5 — DETAIL KATALOG: Interaksi & Info (Guest)
// ============================================================================
test.describe('Detail Katalog — Interaksi & Info (Guest)', () => {
    test('TC-DK-004 · Klik kartu tier mengganti gambar utama', async ({ page }) => {
        await goto(page, `/customer/katalog/${PRODUCT_WITH_TIER}`);
        await expect(page.getByRole('heading', { name: /level desain/i })).toBeVisible();

        const mainImage = page.locator('.aspect-\\[4\\/3\\] img:visible').first();
        const beforeSrc = await mainImage.getAttribute('src');

        const tierCards = page.locator('div.cursor-pointer')
            .filter({ has: page.locator('span', { hasText: /PAKET DASAR|REKOMENDASI|SOLUSI LENGKAP/ }) });

        await tierCards.nth(1).click();

        await expect(async () => {
            const currentSrc = await mainImage.getAttribute('src');
            expect(currentSrc).not.toBe(beforeSrc);
        }).toPass({ timeout: 10_000 });

        await screenshotWithWait(page, 'TC-DK-004');
    });

    test('TC-DK-004b · Setiap tier bisa diklik dan gambar utama berubah', async ({ page }) => {
        await goto(page, `/customer/katalog/${PRODUCT_WITH_TIER}`);
        await expect(
            page.getByRole('heading', { name: /level desain/i })
        ).toBeVisible();

        const tierCards = page
            .locator('div.cursor-pointer')
            .filter({ has: page.locator('span', { hasText: /PAKET DASAR|REKOMENDASI|SOLUSI LENGKAP/ }) });

        const tierCount = await tierCards.count();
        expect(tierCount).toBeGreaterThan(0);

        const mainImage = page.locator('.aspect-\\[4\\/3\\] img:visible').first();
        const seenSrcs = new Set();
        seenSrcs.add(await mainImage.getAttribute('src'));

        for (let i = 0; i < tierCount; i++) {
            await tierCards.nth(i).click();

            await page.waitForFunction(
                (prevSrcs) => {
                    const img = document.querySelector('.aspect-\\[4\\/3\\] img[style*="display: block"], .aspect-\\[4\\/3\\] img:not([style*="display: none"])');
                    return img && img.getAttribute('src');
                },
                Array.from(seenSrcs)
            );

            const src = await mainImage.getAttribute('src');
            seenSrcs.add(src);
        }

        expect(seenSrcs.size, 'Minimal 2 gambar berbeda setelah klik semua tier').toBeGreaterThan(1);

        await screenshotWithWait(page, 'TC-DK-004b');
    });

    test('TC-DK-005 · Section "Informasi Pengerjaan & Ketentuan" tampil lengkap', async ({ page }) => {
        await goto(page, `/customer/katalog/${PRODUCT_WITH_TIER}`);
        await expect(page.locator('h1').first()).toBeVisible();

        await expect(
            page.getByRole('heading', { name: /informasi pengerjaan.*ketentuan/i })
        ).toBeVisible();

        await expect(page.getByText(/estimasi pengerjaan/i).first()).toBeVisible();
        await expect(page.getByText(/revisi & brief/i).first()).toBeVisible();
        await expect(page.getByText(/deadline & express/i).first()).toBeVisible();

        await expect(page.getByText(/7 hari kerja/i).first()).toBeVisible();
        await expect(page.getByText(/maksimal 3 kali/i).first()).toBeVisible();
        await expect(page.getByText(/biaya express/i).first()).toBeVisible();

        await screenshotWithWait(page, 'TC-DK-005');
    });

    test('TC-DK-006 · Section "Informasi Pengerjaan" juga tampil di produk non-tier', async ({ page }) => {
        await goto(page, `/customer/katalog/${PRODUCT_NO_TIER}`);
        await expect(page.locator('h1').first()).toBeVisible();

        await expect(
            page.getByRole('heading', { name: /informasi pengerjaan/i })
        ).toBeVisible();

        await expect(page.getByText(/estimasi pengerjaan/i).first()).toBeVisible();
        await expect(page.getByText(/revisi & brief/i).first()).toBeVisible();
        await expect(page.getByText(/deadline & express/i).first()).toBeVisible();

        await screenshotWithWait(page, 'TC-DK-006');
    });

    test('TC-DK-007 · Breadcrumb "Katalog Jasa" mengarah kembali ke Katalog', async ({ page }) => {
        await goto(page, `/customer/katalog/${PRODUCT_WITH_TIER}`);
        await expect(page.locator('h1').first()).toBeVisible();

        const breadcrumb = page.locator('nav').nth(1);
        await expect(breadcrumb).toContainText(/beranda/i);
        await expect(breadcrumb).toContainText(/katalog jasa/i);

        await screenshotWithWait(page, 'TC-DK-007');

        await breadcrumb.getByRole('link', { name: /katalog jasa/i }).click();
        await expect(page).toHaveURL(/\/customer\/katalog$/);
    });

    test('TC-DK-008 · Breadcrumb "Beranda" mengarah ke Beranda', async ({ page }) => {
        await goto(page, `/customer/katalog/${PRODUCT_WITH_TIER}`);
        await expect(page.locator('h1').first()).toBeVisible();

        await screenshotWithWait(page, 'TC-DK-008');

        await page.locator('nav').nth(1)
            .getByRole('link', { name: /^beranda$/i }).click();
        await expect(page).toHaveURL(/\/customer\/beranda/);
    });
});

// ============================================================================
// BAGIAN 6 — CUSTOMER LOGIN (sederhana)
// ============================================================================
test.describe('Katalog — Customer Login', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsCustomer(page);
    });

    test('TC-KT-001 · Customer membuka Katalog dari menu navigasi (Beranda → Katalog)', async ({ page }) => {
        await goto(page, '/customer/beranda');
        await expect(page).toHaveURL(/\/customer\/beranda/);

        await page.getByRole('link', { name: /katalog/i }).first().click();
        await expect(page).toHaveURL(/\/customer\/katalog$/);
        await expect(page.getByRole('heading', { name: /butuh desain/i })).toBeVisible();

        await screenshotWithWait(page, 'TC-KT-001');
    });
});

// ============================================================================
// BAGIAN 6B — DETAIL KATALOG: Customer Login
// ============================================================================
test.describe('Detail Katalog — Customer Login', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsCustomer(page);
    });

    test('TC-DK-003 · Tombol "Pesan Sekarang" mengarah ke Form Pemesanan', async ({ page }) => {
        await goto(page, `/customer/katalog/${PRODUCT_WITH_TIER}`);
        await expect(page.locator('h1').first()).toBeVisible();

        const pesanBtn = page.getByRole('link', { name: /pesan jasa sekarang/i });
        await expect(pesanBtn).toBeVisible();

        const pesanHref = await pesanBtn.getAttribute('href');
        expect(pesanHref).toMatch(/\/customer\/pemesanan\/[A-Za-z0-9_-]+/);

        await screenshotWithWait(page, 'TC-DK-003');

        await pesanBtn.click();
        await expect(page).toHaveURL(/\/customer\/pemesanan\/[A-Za-z0-9_-]+/);
        await expect(page).not.toHaveURL(/\/login/);
    });
});

// ============================================================================
// BAGIAN 7 — ALUR UTUH: Login → Beranda → Katalog → Detail → FORM PEMESANAN
// ============================================================================
test.describe('Alur Utuh (Customer) — Beranda → Katalog → Detail → Form Pemesanan', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsCustomer(page);
    });

    test('Alur end-to-end sampai Form Pemesanan dapat diakses', async ({ page }) => {
        let detailHref;

        // ---------- STEP 1: Beranda ----------
        await test.step('Buka Beranda & verifikasi section utama', async () => {
            await goto(page, '/customer/beranda');
            await expect(page).toHaveURL(/\/customer\/beranda/);
            await expect(page).toHaveTitle(/Beranda — Artiv/i);
            await expect(page.locator('h1').first()).toContainText(/artiv/i);

            await expect(
                page.getByRole('heading', { name: /layanan kategori jasa/i })
            ).toBeVisible();

            await expect(
                page.getByRole('heading', { name: /katalog jasa desain populer/i })
            ).toBeVisible();

            await expect(
                page.getByRole('heading', { name: /dipercaya kreator.*bisnis/i })
            ).toBeVisible();

            const navbar = page.locator('nav').first();
            await expect(
                navbar.getByRole('link', { name: /pesanan saya/i })
            ).toBeVisible();
        });

        // ---------- STEP 2: Katalog ----------
        await test.step('Klik menu "Katalog" → halaman Katalog terbuka', async () => {
            await page.getByRole('link', { name: /katalog/i }).first().click();
            await expect(page).toHaveURL(/\/customer\/katalog$/);
            await expect(page.getByRole('heading', { name: /butuh desain/i })).toBeVisible();
        });

        await test.step('Verifikasi search bar, sort, dan kartu produk', async () => {
            await expect(page.getByPlaceholder(/cari jasa desain/i)).toBeVisible();
            await expect(page.getByRole('button', { name: /cari jasa/i })).toBeVisible();
            await expect(page.getByRole('button', { name: /urutkan:/i })).toBeVisible();

            const cards = page.locator('a[href*="/customer/katalog/"]');
            await expect(cards.first()).toBeVisible();
            expect(await cards.count()).toBeGreaterThan(0);
        });

        // ---------- STEP 3: Detail ----------
        await test.step('Klik kartu pertama → halaman Detail Katalog', async () => {
            const firstCard = page.locator('a[href*="/customer/katalog/"]').first();
            detailHref = await firstCard.getAttribute('href');
            await firstCard.click();
            await expect(page).toHaveURL(DETAIL_URL_RE);
        });

        await test.step('Verifikasi elemen detail (breadcrumb, judul, harga, info pengerjaan)', async () => {
            await expect(page.locator('nav').nth(1)).toContainText(/katalog jasa/i);
            await expect(page.locator('h1').first()).toBeVisible();
            await expect(page.getByText(/mulai dari/i).first()).toBeVisible();
            await expect(
                page.getByRole('heading', { name: /informasi pengerjaan/i })
            ).toBeVisible();
        });

        // ---------- STEP 4: Form Pemesanan ----------
        await test.step('Klik "Pesan Jasa Sekarang" → masuk Form Pemesanan', async () => {
            const pesanBtn = page.getByRole('link', { name: /pesan jasa sekarang/i });
            await expect(pesanBtn).toBeVisible();

            const pesanHref = await pesanBtn.getAttribute('href');
            expect(pesanHref).toMatch(/\/customer\/pemesanan\/[A-Za-z0-9_-]+/);

            await pesanBtn.click();
            await expect(page).toHaveURL(/\/customer\/pemesanan\/[A-Za-z0-9_-]+/);

            await expect(page).not.toHaveURL(/\/login/);
        });

        await test.step('Form Pemesanan terbuka & elemen form tampil', async () => {
            const hasForm = await page.locator('form').first().isVisible().catch(() => false);
            const hasHeading = await page.locator('h1, h2').first().isVisible().catch(() => false);
            expect(hasForm || hasHeading).toBeTruthy();

            await screenshotWithWait(page, 'TC-ALUR-001');
        });

        // ---------- STEP 5: Balik ke Katalog via breadcrumb ----------
        await test.step('Balik ke Katalog via breadcrumb di halaman Detail', async () => {
            await goto(page, detailHref);
            await expect(page.locator('h1').first()).toBeVisible();

            await page.locator('nav').nth(1)
                .getByRole('link', { name: /katalog jasa/i }).click();
            await expect(page).toHaveURL(/\/customer\/katalog$/);
        });

        // ---------- STEP 6: Footer ----------
        await test.step('Footer tampil di halaman Katalog', async () => {
            const footer = page.locator('footer').last();
            await expect(footer).toBeVisible();
            await expect(footer).toContainText(/artiv/i);
        });
    });
});