import { test, expect } from "@playwright/test";
import {
    snap,
    snapElementHighlight,
    saveEvidence,
} from "./helpers/evidence.js";
import { siapkanRingkasan } from "./helpers/pemesanan.helper.js";
import { lanjutKePembayaran } from "./helpers/ringkasan.helper.js";

// UUID placeholder — untuk TC-PB-002 (guest), middleware auth redirect dulu,
// jadi ID apa pun valid (bahkan yang tidak ada di DB).
const ORDER_ID_PLACEHOLDER = "01a196f3-2bff-7333-904a-553b1004d27b";

test.afterEach(async ({ page }, testInfo) => {
    if (testInfo.status !== "passed") {
        await saveEvidence(page, testInfo);
    }
});

// ═══════════════════════════════════════════════════════════════════
// TC-PB-001 — Customer membuka halaman Pembayaran dari Ringkasan
// ═══════════════════════════════════════════════════════════════════
test.describe("TC-PB-001 – Akses halaman Pembayaran", () => {
    test.use({ storageState: "e2e/.auth/customer.json" });

    test("Customer membuka halaman Pembayaran dari Ringkasan", async ({
        page,
    }) => {
        test.setTimeout(90000);

        // Setup: sampai di Ringkasan, lalu klik "Lanjut ke Pembayaran"
        await siapkanRingkasan(page);
        await lanjutKePembayaran(page);

        // Assertion: halaman pembayaran tampil
        await expect(page).toHaveURL(
            /\/customer\/pemesanan\/[^/]+\/pembayaran$/,
            { timeout: 10000 }
        );
        await expect(page.locator("body")).toContainText(/Pembayaran Pesanan/i);
        await expect(page.locator("body")).toContainText(/TAHAP 3 dari 3/i);
        await expect(
            page.getByText("Konfirmasi Pembayaran", { exact: false })
        ).toBeVisible();

        // 📸 Evidence: seluruh halaman pembayaran
        await snap(
            page,
            "TC-PB-001_halaman_pembayaran_dari_ringkasan",
            { fullPage: true }
        );
    });
});

// ═══════════════════════════════════════════════════════════════════
// TC-PB-002 — Guest diarahkan ke login
// ═══════════════════════════════════════════════════════════════════
test.describe("TC-PB-002 – Guest diarahkan ke login", () => {
    test.use({ storageState: { cookies: [], origins: [] } });

    test("Guest diarahkan ke halaman login saat akses URL pembayaran", async ({
        page,
    }) => {
        await page.goto(
            `/customer/pemesanan/${ORDER_ID_PLACEHOLDER}/pembayaran`,
            { waitUntil: "domcontentloaded" }
        );

        await expect(page).toHaveURL(/\/login/, { timeout: 10000 });
        await expect(page.locator('input[name="email"]')).toBeVisible();

        // 📸 Evidence: highlight input email di halaman login
        await snapElementHighlight(
            page,
            "TC-PB-002_redirect_ke_login",
            page.locator('input[name="email"]')
        );
    });
});

// ═══════════════════════════════════════════════════════════════════
// TC-PB-003 — Detail tagihan tampil
// ═══════════════════════════════════════════════════════════════════
test.describe("TC-PB-003 – Detail tagihan tampil", () => {
    test.use({ storageState: "e2e/.auth/customer.json" });

    test("Halaman Pembayaran menampilkan detail tagihan", async ({ page }) => {
    test.setTimeout(90000);

    await siapkanRingkasan(page);
    await lanjutKePembayaran(page);

    // Total Nominal
    await expect(
        page.getByText("Total Nominal:", { exact: false })
    ).toBeVisible();
    await expect(page.locator("body")).toContainText(/Rp\s?[\d.]+/);

    // Merchant resmi
    await expect(
        page.getByText("Merchant Resmi:", { exact: false })
    ).toBeVisible();
    await expect(page.locator("body")).toContainText("ARTIV Official");

    // ⭐ Tunggu QR image benar-benar LOAD (dari api.qrserver.com)
    const qrImg = page.locator('img[alt="QRIS Code"]');
    await expect(qrImg).toBeAttached();

    try {
        // Tunggu sampai naturalWidth > 0 (artinya gambar render)
        await page.waitForFunction(
            () => {
                const img = document.querySelector('img[alt="QRIS Code"]');
                return img && img.complete && img.naturalWidth > 0;
            },
            { timeout: 20000 }   // 20 detik — cukup kalau internet normal
        );
        console.log("✅ QR image berhasil load dari API");
    } catch (err) {
        console.warn("⚠️ QR gagal load dalam 20 detik dari api.qrserver.com");
        // Kalau gagal, test tetap lanjut — screenshot diambil dengan QR blank
    }

    // 📸 Evidence
    const infoCard = page
        .getByText("Total Nominal:", { exact: false })
        .locator("xpath=ancestor::div[contains(@class,'bg-neutral-50')][1]");
    await snapElementHighlight(
        page,
        "TC-PB-003_detail_tagihan",
        infoCard
    );
});
});

// ═══════════════════════════════════════════════════════════════════
// TC-PB-004 — Instruksi pembayaran QRIS ditampilkan
// ═══════════════════════════════════════════════════════════════════
test.describe("TC-PB-004 – Instruksi pembayaran tampil", () => {
    test.use({ storageState: "e2e/.auth/customer.json" });

    test("Halaman Pembayaran menampilkan instruksi pembayaran QRIS", async ({
        page,
    }) => {
        test.setTimeout(90000);

        await siapkanRingkasan(page);
        await lanjutKePembayaran(page);

        // ── Assertion instruksi QRIS ─────────────────────────────
        await expect(
            page.getByText("Langkah Pembayaran QRIS:", { exact: false })
        ).toBeVisible();
        await expect(page.locator("body")).toContainText(
            /Scan|kamera ke kode QR/i
        );
        await expect(page.locator("body")).toContainText(/ARTIV Official/);

        // ── Assertion timer batas waktu ──────────────────────────
        await expect(
            page.getByText("Batas Waktu Pembayaran", { exact: false })
        ).toBeVisible();
        await expect(page.locator("body")).toContainText(
            /Selesaikan pembayaran sebelum waktu habis/i
        );

        // ⭐ Samakan dengan TC-PB-003: tunggu QR asli dari API load
        const qrImg = page.locator('img[alt="QRIS Code"]');
        await expect(qrImg).toBeAttached();

        try {
            await page.waitForFunction(
                () => {
                    const img = document.querySelector('img[alt="QRIS Code"]');
                    return img && img.complete && img.naturalWidth > 0;
                },
                { timeout: 20000 }
            );
            console.log("✅ QR image berhasil load dari API");
        } catch (err) {
            console.warn(
                "⚠️ QR gagal load dalam 20 detik dari api.qrserver.com"
            );
        }

        // 📸 Evidence 1: instruksi QRIS
        const instruksiBlock = page
            .getByText("Langkah Pembayaran QRIS:", { exact: false })
            .locator("xpath=ancestor::div[contains(@class,'mt-6')][1]");
        await snapElementHighlight(
            page,
            "TC-PB-004_1_instruksi_qris",
            instruksiBlock
        );

        // 📸 Evidence 2: timer batas waktu
        const timerBlock = page
            .getByText("Batas Waktu Pembayaran", { exact: false })
            .locator("xpath=ancestor::div[contains(@class,'bg-amber-50')][1]");
        await snapElementHighlight(
            page,
            "TC-PB-004_2_batas_waktu",
            timerBlock
        );
    });
});