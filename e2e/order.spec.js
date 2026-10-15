import { test, expect } from '@playwright/test';

// ============================================================
// KONFIGURASI — SESUAIKAN AKUN TEST MASING- MASING DI SINI
// ============================================================
const USERS = {
  customer: { email: 'azaryaamanda@gmail.com', password: 'bakmi123' },
  customerEmpty: { email: 'customer2@gmail.com', password: 'password123' },
  designer: { email: 'designer1@gmail.com', password: 'bakmi12345' },
  admin: { email: 'admin1@gmail.com', password: 'password123' },
};

const SEL = {
    navBeranda: 'nav a:has-text("Beranda")',
    navKatalog: 'nav a:has-text("Katalog")',
    navPesananSaya: 'nav a:has-text("Pesanan Saya")',

    judul: 'h1:has-text("Pesanan Saya")',
    subjudul: 'p.text-slate-500',
    searchInput: 'input[name="q"]',
    searchBtn: 'form[action*="/customer/pesanan"] button[type="submit"]',
    tabAktif: 'a.pb-3:has-text("Pesanan Aktif")',
    tabRiwayat: 'a.pb-3:has-text("Riwayat Selesai")',
    dropdownStatus: 'button:has-text("Status:")',
    dropdownKategori: 'button:has-text("Kategori:")',
    dropdownUrutkan: 'button:has-text("Urutkan:")',
    refreshBtn: 'a[title="Reset filter"]',
    orderCard: 'main .bg-white.rounded-2xl.shadow-sm',
    btnLihatPesanan: 'a:has-text("Lihat Pesanan")',

    judulDetail: 'h1:has-text("Detail Pesanan")',
    chatContainer: '#chat-container',
    chatInput: 'input[name="isi"]',
    chatSendBtn: 'form[action*="/pesan"] button[type="submit"]',
    errorNotif: '#error-notif',
    errorList: '#error-notif li',
    btnBeriReview: 'a:has-text("Beri Review")',
    reviewDone: 'text=/Review sudah dikirim/',

    cardLayanan: 'text=LAYANAN TERPESAN',
    cardPengerjaan: 'text=INFORMASI PENGERJAAN',
    cardBrief: 'text=BRIEF PESANAN KLIEN',
    cardReferensi: 'text=REFERENSI & LAMPIRAN AWAL',
    cardRiwayat: 'text=RIWAYAT PENGERJAAN',
    timelineLog: 'ol.relative li',
    btnDetailPenuh: 'button:has-text("Detail Penuh")',
    btnDownloadRef: 'a[title="Unduh file"]',
    btnPreviewImg: 'button[title="Lihat gambar"]',

    reviewJudul: 'h1:has-text("Pesanan Selesai! Bagaimana Pengalaman Anda?")',
    starsContainer: '#stars',
    starBtn: '#stars .star',
    ratingNumber: '#rating-number',
    reviewTextarea: 'textarea#comment',
    btnSubmitReview: '#btn-submit',
    btnLewati: 'a:has-text("Lewati")',
    thanksJudul: 'h1:has-text("Terima kasih atas ulasan")',
};

async function skipIntro(page) {
    await page.addInitScript(() => {
    sessionStorage.setItem('artiv-intro-seen', '1');
    });
}

async function login(page, user) {
    await page.goto('/login');
    await page.fill('input[name="email"]', user.email);
    await page.fill('input[name="password"]', user.password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');
}

async function gotoPesananSaya(page) {
    await page.goto('/customer/pesanan');
    await page.waitForLoadState('domcontentloaded');
}

async function gotoDetailPesanan(page) {
    await gotoPesananSaya(page);
    const btn = page.locator(SEL.btnLihatPesanan).first();
    if (await btn.isVisible().catch(() => false)) {
        await btn.click();
        await page.waitForLoadState('domcontentloaded');
    }
}

async function countCards(page) {
    return await page.locator(SEL.orderCard).count();
}

test.beforeEach(async ({ page }) => {
    await skipIntro(page);
});

test.describe('Pesanan Saya', () => {

    test('TC-PS-001: Customer dapat membuka halaman Pesanan Saya', async ({ page }) => {
        await login(page, USERS.customer);
        await page.click(SEL.navPesananSaya);
        await expect(page).toHaveURL(/customer\/pesanan/);
        await expect(page.locator(SEL.judul)).toBeVisible();
    });

    test('TC-PS-002: User belum login diarahkan ke login', async ({ page }) => {
        await page.goto('/customer/pesanan');
        await expect(page).toHaveURL(/login/);
    });

    test('TC-PS-005: Judul dan subjudul tampil', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        await expect(page.locator(SEL.judul)).toContainText('Pesanan Saya');
        await expect(page.locator(SEL.subjudul)).toContainText('Pantau perkembangan');
    });

    test('TC-PS-008: Tab Pesanan Aktif & Riwayat Selesai tampil', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        await expect(page.locator(SEL.tabAktif)).toBeVisible();
        await expect(page.locator(SEL.tabRiwayat)).toBeVisible();
    });

    test('TC-PS-009: Search, filter, sort, refresh tampil', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        await expect(page.locator(SEL.searchInput)).toBeVisible();
        await expect(page.locator(SEL.dropdownStatus)).toBeVisible();
        await expect(page.locator(SEL.dropdownKategori)).toBeVisible();
        await expect(page.locator(SEL.dropdownUrutkan)).toBeVisible();
        await expect(page.locator(SEL.refreshBtn)).toBeVisible();
    });

    test('TC-PS-014: Tab Pesanan Aktif terpilih default', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);

        const tab = page.locator(SEL.tabAktif);
        await expect(tab).toBeVisible();

        const cls = await tab.getAttribute('class') || '';
        expect(cls).toContain('border-[#6D28D9]');
    });

    test('TC-PS-015: Berpindah ke tab Riwayat Selesai', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        await page.locator(SEL.tabRiwayat).first().click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/tab=riwayat/);
    });

    test('TC-PS-019: Info pada kartu pesanan tampil', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        const card = page.locator(SEL.orderCard).first();
        await expect(card).toBeVisible();
        await expect(card.locator(SEL.btnLihatPesanan)).toBeVisible();
    });

    test('TC-PS-020: Progres pengerjaan tampil', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        const card = page.locator(SEL.orderCard).first();
        await expect(card).toContainText(/Progres Pengerjaan/);
        await expect(card).toContainText(/% Selesai/);
    });

    test('TC-PS-023: Pencarian berdasarkan nama jasa', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        await page.fill(SEL.searchInput, 'Banner');
        await page.click(SEL.searchBtn);
        await page.waitForLoadState('domcontentloaded');
        const cards = page.locator(SEL.orderCard);
        const count = await cards.count();
        expect(count).toBeGreaterThan(0);
        for (let i = 0; i < count; i++) {
        await expect(cards.nth(i)).toContainText(/Banner/i);
        }
    });

    test('TC-PS-025: Pencarian berdasarkan nomor pesanan', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        const firstCard = page.locator(SEL.orderCard).first();
        const text = await firstCard.innerText();
        const m = text.match(/ARTIV-[A-Z0-9]+/i);
        test.skip(!m, 'Tidak ada nomor pesanan');
        await page.fill(SEL.searchInput, m[0]);
        await page.click(SEL.searchBtn);
        await page.waitForLoadState('domcontentloaded');
        expect(await countCards(page)).toBeGreaterThanOrEqual(1);
    });

    test('TC-PS-027: Pencarian tidak ditemukan', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        await page.fill(SEL.searchInput, 'xyz123');
        await page.click(SEL.searchBtn);
        await page.waitForLoadState('domcontentloaded');
        expect(await countCards(page)).toBe(0);
    });

    test('TC-PS-029: Filter berdasarkan status', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        await page.click(SEL.dropdownStatus);
        await page.waitForTimeout(300);
        await page.click('a:has-text("Menunggu Konfirmasi")');
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/status=/);
    });

    test('TC-PS-030: Filter berdasarkan kategori', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        await page.click(SEL.dropdownKategori);
        await page.waitForTimeout(300);
        await page.click('a:has-text("Poster")');
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/kategori=Poster/);
    });

    test('TC-PS-035: Urutan default Deadline Terdekat', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        await expect(page.locator(SEL.dropdownUrutkan)).toContainText(/Deadline Terdekat/);
    });

    test('TC-PS-036: Ubah opsi pengurutan', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        await page.click(SEL.dropdownUrutkan);
        await page.waitForTimeout(300);
        await page.click('a:has-text("Terbaru")');
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/urut=terbaru/);
    });

    test('TC-PS-037: Tombol refresh memuat ulang', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        await page.click(SEL.refreshBtn);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/tab=aktif/);
    });

    test('TC-PS-039: Tombol Lihat Pesanan pada pesanan aktif', async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        await page.locator(SEL.btnLihatPesanan).first().click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/customer\/pesanan\/[\w-]+/);
    });
});


test.describe('Detail Pesanan', () => {

    test.beforeEach(async ({ page }) => {
        await login(page, USERS.customer);
        await gotoDetailPesanan(page);
    });

    test('TC-DP-001: Halaman detail dapat diakses', async ({ page }) => {
        await expect(page).toHaveURL(/\/customer\/pesanan\/[\w-]+/);
        await expect(page.locator(SEL.judulDetail)).toBeVisible();
    });

    test('TC-DP-002: Informasi produk ditampilkan', async ({ page }) => {
        await expect(page.locator(SEL.cardLayanan)).toBeVisible();
        const body = await page.locator('body').innerText();
        expect(/Rp\s?[\d.,]+/.test(body)).toBeTruthy();
    });

    test('TC-DP-003: Informasi pengerjaan ditampilkan', async ({ page }) => {
        await expect(page.locator(SEL.cardPengerjaan)).toBeVisible();
        await expect(page.locator('text=/Target Tenggat/')).toBeVisible();
        await expect(page.locator('text=/Sisa Waktu/')).toBeVisible();
    });

    test('TC-DP-004: Brief pesanan ditampilkan', async ({ page }) => {
        const briefCard = page.locator(SEL.cardBrief);
        const count = await briefCard.count();
        test.skip(count === 0, 'Order ini tidak punya brief');
        await briefCard.first().scrollIntoViewIfNeeded();
        await expect(briefCard.first()).toBeVisible();
    });

    test('TC-DP-005: File referensi dapat diunduh', async ({ page }) => {
        const refCard = page.locator(SEL.cardReferensi);
        const count = await refCard.count();
        test.skip(count === 0, 'Tidak ada referensi');

        await refCard.first().scrollIntoViewIfNeeded();
        const download = page.locator(SEL.btnDownloadRef).first();
        if (await download.count() > 0) {
            await expect(download).toBeVisible();
        }
    });

        test('TC-DP-006: Timeline tahapan pengerjaan ditampilkan', async ({ page }) => {
        const riwayatCard = page.locator(SEL.cardRiwayat);
        const count = await riwayatCard.count();
        test.skip(count === 0, 'Tidak ada riwayat');

        await riwayatCard.first().scrollIntoViewIfNeeded();
        await expect(page.locator(SEL.timelineLog).first()).toBeVisible();
    });

    test('TC-DP-007: Chat container tampil', async ({ page }) => {
        await expect(page.locator(SEL.chatContainer)).toBeVisible();
    });

    test('TC-DP-008: Pesan dapat dikirim', async ({ page }) => {
        const chatInput = page.locator(SEL.chatInput);
        test.skip(!(await chatInput.isVisible().catch(() => false)), 'Chat belum aktif');

        await chatInput.scrollIntoViewIfNeeded();
        await chatInput.fill('Halo, bagaimana progres desainnya?');

        const sendBtn = page.locator(SEL.chatSendBtn);
        await sendBtn.scrollIntoViewIfNeeded();
        await sendBtn.click();

        await page.waitForLoadState('domcontentloaded');
        await expect(page.locator('body')).toContainText(/progres desainnya/i);
    });

    test('TC-DP-010: Chat tidak dapat diakses sebelum order diambil Designer', async ({ page }) => {
        const body = await page.locator('body').innerText();
        const waiting = /Menunggu desainer mengambil pesanan/i.test(body);

        if (waiting) {
            const chatInput = page.locator(SEL.chatInput);
            const visible = await chatInput.isVisible().catch(() => false);
            expect(visible).toBeFalsy();
        } else {
            test.skip(true, 'Order sudah diambil designer');
        }
    });

    const blacklistCases = [
        { id: 'TC-DP-011', msg: 'Ini link saya https://example.com' },
        { id: 'TC-DP-012', msg: 'Hubungi saya di 08123456789' },
        { id: 'TC-DP-013', msg: 'Email saya user@example.com' },
        { id: 'TC-DP-014', msg: 'Hubungi WA saya ya' },
    ];

    for (const c of blacklistCases) {
        test(`${c.id}: Pesan "${c.msg.slice(0, 30)}..." diblokir`, async ({ page }) => {
            const chatInput = page.locator(SEL.chatInput);
            test.skip(!(await chatInput.isVisible().catch(() => false)), 'Chat belum aktif');

            await chatInput.scrollIntoViewIfNeeded();
            await chatInput.fill(c.msg);

            const sendBtn = page.locator(SEL.chatSendBtn);
            await sendBtn.scrollIntoViewIfNeeded();
            await sendBtn.click();

            await page.waitForTimeout(1000);

            await expect(page.locator(SEL.errorNotif)).toBeVisible();
            await expect(page.locator(SEL.errorList).first()).toBeVisible();
        });
    }

    test('TC-DP-015: Peringatan muncul saat chat melanggar aturan', async ({ page }) => {
        const chatInput = page.locator(SEL.chatInput);
        test.skip(!(await chatInput.isVisible().catch(() => false)), 'Chat belum aktif');

        await chatInput.scrollIntoViewIfNeeded();
        await chatInput.fill('Hubungi saya di 08123456789');

        const sendBtn = page.locator(SEL.chatSendBtn);
        await sendBtn.scrollIntoViewIfNeeded();
        await sendBtn.click();

        await page.waitForTimeout(1000);
        await expect(page.locator(SEL.errorNotif)).toBeVisible();
    });
});

test.describe('Review & Rating', () => {

    test.beforeEach(async ({ page }) => {
        await login(page, USERS.customer);
        await gotoPesananSaya(page);
        await page.locator(SEL.tabRiwayat).first().click();
        await page.waitForLoadState('domcontentloaded');
        await page.waitForTimeout(700);
    });

    test('TC-RR-001: Tombol "Beri Review" muncul di pesanan selesai', async ({ page }) => {
        const reviewBtn = page.locator(SEL.btnBeriReview).first();
        await expect(reviewBtn).toBeVisible({ timeout: 10_000 });
    });

    test('TC-RR-002: Halaman review dapat diakses dari tombol Beri Review', async ({ page }) => {
        const reviewBtn = page.locator(SEL.btnBeriReview).first();
        await expect(reviewBtn).toBeVisible({ timeout: 10_000 });
        await reviewBtn.click();
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/review/);
        await expect(page.locator(SEL.reviewJudul)).toBeVisible();
    });

    test('TC-RR-003: Elemen form review ditampilkan', async ({ page }) => {
        const reviewBtn = page.locator(SEL.btnBeriReview).first();
        await expect(reviewBtn).toBeVisible({ timeout: 10_000 });
        await reviewBtn.click();
        await page.waitForLoadState('domcontentloaded');

        await expect(page.locator(SEL.starsContainer)).toBeVisible();
        await expect(page.locator(SEL.reviewTextarea)).toBeVisible();
        await expect(page.locator(SEL.btnSubmitReview)).toBeVisible();
    });

    test('TC-RR-004: Customer dapat mengirim review', async ({ page }) => {
        const reviewBtn = page.locator(SEL.btnBeriReview).first();
        await expect(reviewBtn).toBeVisible({ timeout: 10_000 });
        await reviewBtn.click();
        await page.waitForLoadState('domcontentloaded');

        await page.locator(SEL.starBtn).nth(4).click();
        await expect(page.locator(SEL.ratingNumber)).toContainText('5');

        await page.fill(SEL.reviewTextarea, 'Hasil desain sangat memuaskan');

        await page.click(SEL.btnSubmitReview);
        await page.waitForURL(/sukses/, { timeout: 15_000 });

        await expect(page.locator(SEL.thanksJudul)).toBeVisible({ timeout: 10_000 });
    });

    test('TC-RR-006: Customer dapat melewati review', async ({ page }) => {
        const reviewBtn = page.locator(SEL.btnBeriReview).first();
        const visible = await reviewBtn.isVisible().catch(() => false);
        test.skip(!visible, 'Tombol Beri Review tidak ada');

        await reviewBtn.click();
        await page.waitForLoadState('domcontentloaded');

        await page.click(SEL.btnLewati);
        await page.waitForURL(/\/customer\/pesanan\?tab=riwayat/, { timeout: 10_000 });
    });

    test('TC-RR-007: Review tidak terkirim jika bintang kosong', async ({ page }) => {
        const reviewBtn = page.locator(SEL.btnBeriReview).first();
        await expect(reviewBtn).toBeVisible({ timeout: 10_000 });
        await reviewBtn.click();
        await page.waitForLoadState('domcontentloaded');

        await expect(page.locator(SEL.btnSubmitReview)).toBeDisabled();

        await page.fill(SEL.reviewTextarea, 'Review tanpa bintang');

        await expect(page.locator(SEL.btnSubmitReview)).toBeDisabled();
    });

    test('TC-RR-008: Review sudah dikirim tampil ringkasan', async ({ page }) => {
        const reviewDone = page.locator(SEL.reviewDone);
        if (await reviewDone.isVisible().catch(() => false)) {
        await expect(reviewDone).toBeVisible();
        } else {
        const btn = page.locator(SEL.btnLihatPesanan).first();
        if (await btn.isVisible().catch(() => false)) {
            await btn.click();
            await page.waitForLoadState('domcontentloaded');
            const hasReview = await page.locator('text=/Review sudah dikirim/').isVisible().catch(() => false);
            test.skip(!hasReview, 'Review belum ada — jalankan TC-RR-004 dulu');
        }
        }
    });
});