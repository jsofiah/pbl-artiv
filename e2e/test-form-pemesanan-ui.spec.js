import { test, expect } from "@playwright/test";
import {
    snap,
    snapElementHighlight,
    saveEvidence,
} from "./helpers/evidence.js";

const PRODUCT_ID = "01a196f3-2bff-7333-904a-553b1004d27b";

// ⭐ Safety net: kalau test fail SEBELUM sempat snapshot manual,
//    afterEach ini tetap simpan screenshot terakhir ke evidence.
//    Kalau test pass, snapshot manual di dalam test yang jadi "evidence utama".
test.afterEach(async ({ page }, testInfo) => {
    if (testInfo.status !== "passed") {
        await saveEvidence(page, testInfo);
    }
});

// ═══════════════════════════════════════════════════════════════════
// TC-FP-001 — Customer membuka Form Pemesanan dari katalog
// ═══════════════════════════════════════════════════════════════════
test.describe("TC-FP-001 – Customer akses Form Pemesanan", () => {
    test.use({ storageState: "e2e/.auth/customer.json" });

    test("Customer membuka Form Pemesanan dari katalog", async ({ page }) => {
        // 1. Buka halaman Katalog
        await page.goto("/customer/katalog", {
            waitUntil: "domcontentloaded",
        });
        await expect(page).toHaveURL(/\/customer\/katalog/);

        // 2. Klik salah satu produk (Banner)
        const bannerCard = page
            .locator('a[href*="/customer/katalog/"]')
            .filter({ hasText: "Banner" })
            .first();
        await bannerCard.click();

        // 3. Verifikasi masuk ke Detail Produk
        await expect(page).toHaveURL(
            new RegExp(`/customer/katalog/${PRODUCT_ID}`)
        );

        // 4. Klik "Pesan Jasa Sekarang"
        await page.click('a:has-text("Pesan Jasa Sekarang")');

        // 5. Verifikasi tiba di Form Pemesanan
        await expect(page).toHaveURL(
            new RegExp(`/customer/pemesanan/${PRODUCT_ID}`),
            { timeout: 15000 }
        );
        await expect(page.locator("h1")).toHaveText("Form Pemesanan");

        // 6. Verifikasi semua section lengkap
        await expect(
            page.locator('h3:has-text("Pilih Paket Desain")')
        ).toBeVisible();
        await expect(
            page.locator('h3:has-text("Jumlah Pesanan")')
        ).toBeVisible();
        await expect(
            page.locator('h3:has-text("Pilih Deadline Pengerjaan")')
        ).toBeVisible();
        await expect(
            page.locator('h3:has-text("File & Tautan Referensi")')
        ).toBeVisible();
        await expect(
            page.locator('h3:has-text("Catatan Brief Desain")')
        ).toBeVisible();

        // 📸 Evidence: form pemesanan lengkap dari alur katalog
        await snap(page, "TC-FP-001_form_pemesanan_dari_katalog");
    });
});

// ═══════════════════════════════════════════════════════════════════
// TC-FP-002 — Guest diarahkan ke login
// ═══════════════════════════════════════════════════════════════════
test.describe("TC-FP-002 – Akses guest", () => {
    test.use({ storageState: { cookies: [], origins: [] } });

    test("Guest diarahkan ke halaman login", async ({ page }) => {
        await page.goto(`/customer/pemesanan/${PRODUCT_ID}`, {
            waitUntil: "domcontentloaded",
        });

        await expect(page).toHaveURL(/\/login/, { timeout: 10000 });
        await expect(page.locator('input[name="email"]')).toBeVisible();

        // 📸 Evidence: halaman login dengan input email di-highlight.
        //    Bukti user benar-benar di-redirect ke LOGIN, bukan form.
        await snapElementHighlight(
            page,
            "TC-FP-002_redirect_ke_login",
            page.locator('input[name="email"]')
        );
    });
});

// ═══════════════════════════════════════════════════════════════════
// TC-FP-003, 004, 016, 017 — UI Form Pemesanan (login)
// ═══════════════════════════════════════════════════════════════════
test.describe("TC-FP-003/004/016/017 – UI Form Pemesanan", () => {
    test.use({ storageState: "e2e/.auth/customer.json" });

    test.beforeEach(async ({ page }) => {
        await page.goto(`/customer/pemesanan/${PRODUCT_ID}`, {
            waitUntil: "domcontentloaded",
        });
        await expect(page.locator("h1")).toHaveText("Form Pemesanan");
        await page.waitForFunction(() => {
            const el = document.querySelector('[x-data*="pemesananForm"]');
            return el?._x_dataStack?.[0] !== undefined;
        });
    });

    // ─────────────────────────────────────────────────────────────
    // TC-FP-003
    // ─────────────────────────────────────────────────────────────
    test("TC-FP-003 – Section Pilih Paket Desain tampil dengan nama & harga", async ({
        page,
    }) => {
        const sectionHeading = page.locator(
            'h3:has-text("Pilih Paket Desain")'
        );
        await expect(sectionHeading).toBeVisible();

        const tiers = page.locator(
            'label:has(input[name="product_tier_id"])'
        );
        const count = await tiers.count();
        expect(count).toBeGreaterThan(0);

        for (let i = 0; i < count; i++) {
            await expect(tiers.nth(i)).toContainText(/Rp\s?\d/);
        }

        // 📸 Evidence: seluruh section Pilih Paket Desain (heading + semua tier + harga).
        //    Scroll ke section dulu, pakai fullPage: false supaya fokus.
        await sectionHeading.scrollIntoViewIfNeeded();
        await page.waitForTimeout(300);
        await snap(page, "TC-FP-003_section_paket_desain");
    });

    // ─────────────────────────────────────────────────────────────
    // TC-FP-004
    // ─────────────────────────────────────────────────────────────
    test("TC-FP-004 – Highlight border & background saat paket dipilih", async ({
        page,
    }) => {
        const tiers = page.locator(
            'label:has(input[name="product_tier_id"])'
        );
        const count = await tiers.count();

        if (count < 2) {
            test.skip(true, "Butuh minimal 2 tier untuk test highlight");
        }

        // Tier pertama terpilih default (dari $defaultTier)
        const firstClassBefore =
            (await tiers.nth(0).getAttribute("class")) ?? "";
        expect(firstClassBefore).toContain("bg-violet-50");

        // 📸 Evidence BEFORE: tier 1 ter-highlight (default)
        //    Outline merah di tier 1 untuk bukti "yang terpilih = tier 1".
        await snapElementHighlight(
            page,
            "TC-FP-004_1_sebelum_pilih_tier1",
            tiers.nth(0)
        );

        // Klik tier kedua
        await tiers.nth(1).click();
        await page.waitForTimeout(300);

        // Tier kedua sekarang highlight
        const secondClass = (await tiers.nth(1).getAttribute("class")) ?? "";
        expect(secondClass).toContain("bg-violet-50");
        expect(secondClass).not.toContain("border-slate-200");

        // Tier pertama tidak lagi highlight
        const firstClassAfter =
            (await tiers.nth(0).getAttribute("class")) ?? "";
        expect(firstClassAfter).not.toContain("bg-violet-50");
        expect(firstClassAfter).toContain("border-slate-200");

        // 📸 Evidence AFTER: tier 2 ter-highlight (highlight sudah pindah).
        //    Outline merah di tier 2 untuk bukti "yang terpilih sekarang = tier 2".
        await snapElementHighlight(
            page,
            "TC-FP-004_2_sesudah_pilih_tier2",
            tiers.nth(1)
        );
    });

    // ─────────────────────────────────────────────────────────────
    // TC-FP-016
    // ─────────────────────────────────────────────────────────────
    test("TC-FP-016 – Tombol submit disabled saat form belum lengkap", async ({
        page,
    }) => {
        const btn = page.locator(
            'form[action*="ringkasan"] button[type="submit"]'
        );

        await expect(btn).toBeDisabled();

        const classes = (await btn.getAttribute("class")) ?? "";
        expect(classes).toContain("cursor-not-allowed");
        expect(classes).toContain("opacity-50");

        // 📸 Evidence: tombol submit dalam state disabled, dengan outline merah.
        //    Tombol pudar (opacity-50) sulit dilihat tanpa penanda.
        await btn.scrollIntoViewIfNeeded();
        await page.waitForTimeout(200);
        await snapElementHighlight(
            page,
            "TC-FP-016_tombol_disabled",
            btn
        );
    });

    // ─────────────────────────────────────────────────────────────
    // TC-FP-017
    // ─────────────────────────────────────────────────────────────
    test("TC-FP-017 – Tombol submit aktif setelah form lengkap", async ({
        page,
    }) => {
        // Isi minimal yang dibutuhkan
        await page
            .locator('input[name="product_tier_id"]')
            .first()
            .check({ force: true });
        await page.fill(
            'input[name="reference_links[]"]',
            "https://www.figma.com/@test-reference"
        );
        await page.fill(
            "#brief_note",
            "Brief minimalis modern warna ungu dan putih."
        );
        await page.waitForTimeout(500);

        const btn = page.locator(
            'form[action*="ringkasan"] button[type="submit"]'
        );

        await expect(btn).toBeEnabled();

        const classes = (await btn.getAttribute("class")) ?? "";
        expect(classes).toContain("hover:bg-[#c5ec45]");

        // 📸 Evidence: tombol submit dalam state aktif (hijau terang),
        //    dengan outline merah. Kontras jelas vs TC-FP-016.
        await btn.scrollIntoViewIfNeeded();
        await page.waitForTimeout(200);
        await snapElementHighlight(
            page,
            "TC-FP-017_tombol_aktif",
            btn
        );
    });
});