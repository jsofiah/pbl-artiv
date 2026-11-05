import { test, expect } from "@playwright/test";
import path from "path";
import {
    snap,
    snapElementHighlight,
    saveEvidence,
} from "./helpers/evidence.js";
import { siapkanRingkasan } from "./helpers/pemesanan.helper.js";
import { lanjutKePembayaran } from "./helpers/ringkasan.helper.js";

const buktiValid = path.resolve("e2e/img/tes-upload-foto.jpg");
const buktiExe = path.resolve("e2e/img/test-data.exe");
const buktiBesar = path.resolve("e2e/img/test-data-5mb.jpg");

test.use({ storageState: "e2e/.auth/customer.json" });

test.afterEach(async ({ page }, testInfo) => {
    if (testInfo.status !== "passed") {
        await saveEvidence(page, testInfo);
    }
});

// ═══════════════════════════════════════════════════════════════════
// TC-PB-005 — Upload bukti transfer berhasil
// ═══════════════════════════════════════════════════════════════════
test("TC-PB-005 – Upload bukti transfer berhasil", async ({ page }) => {
    test.setTimeout(90000);

    await siapkanRingkasan(page);
    await lanjutKePembayaran(page);

    // Pilih file
    const input = page.locator('input[name="proof"]');
    await input.setInputFiles(buktiValid);
    await page.waitForTimeout(400);

    // UI konfirmasi file terpilih
    await expect(page.locator("body")).toContainText(
        "File Dipilih: tes-upload-foto.jpg"
    );

    // 📸 Evidence BEFORE submit: file terpilih
    const fileCard = page
        .getByText("File Dipilih:", { exact: false })
        .locator("xpath=ancestor::div[contains(@class,'border')][1]");
    await snapElementHighlight(
        page,
        "TC-PB-005_file_terpilih",
        fileCard
    );

    // Submit form
    const postPromise = page.waitForResponse(
        (r) =>
            r.request().method() === "POST" &&
            r.url().includes("/pembayaran")
    );
    await page
        .locator('form[action*="/pembayaran"] button[type="submit"]')
        .click();
    const resp = await postPromise;

    expect(resp.status()).toBe(302);
    expect(resp.headers()["location"]).toContain("/sukses");

    // Redirect ke halaman sukses
    await page.waitForURL(/\/pembayaran\/sukses/, { timeout: 15000 });
    await expect(page.locator("body")).toContainText(
        /Pembayaran Sedang Diproses/i
    );

    // 📸 Evidence AFTER submit: halaman sukses
    await snap(page, "TC-PB-005_upload_bukti_berhasil", { fullPage: true });
});

// ═══════════════════════════════════════════════════════════════════
// TC-PB-006 — Format .exe ditolak
// ═══════════════════════════════════════════════════════════════════
test("TC-PB-006 – Format .exe ditolak", async ({ page }) => {
    test.setTimeout(90000);

    await siapkanRingkasan(page);
    await lanjutKePembayaran(page);
    const urlPembayaran = page.url();

    // Pilih file .exe
    const input = page.locator('input[name="proof"]');
    await input.setInputFiles(buktiExe);
    await page.waitForTimeout(300);

    // Submit
    await page
        .locator('form[action*="/pembayaran"] button[type="submit"]')
        .click();
    await page.waitForTimeout(3000);

    // Assertion: TIDAK redirect ke sukses (server reject via mimes)
    expect(page.url()).not.toContain("/sukses");
    // Masih di halaman pembayaran
    await expect(page.locator("body")).toContainText(/Pembayaran Pesanan/i);

    // 📸 Evidence: masih di form (upload ditolak)
    await snap(page, "TC-PB-006_format_exe_ditolak", { fullPage: true });
});

// ═══════════════════════════════════════════════════════════════════
// TC-PB-007 — File > 2 MB ditolak
// ═══════════════════════════════════════════════════════════════════
test("TC-PB-007 – File > 2 MB ditolak", async ({ page }) => {
    test.setTimeout(90000);

    await siapkanRingkasan(page);
    await lanjutKePembayaran(page);

    // Pilih file 5 MB
    const input = page.locator('input[name="proof"]');
    await input.setInputFiles(buktiBesar);
    await page.waitForTimeout(300);

    // Submit
    await page
        .locator('form[action*="/pembayaran"] button[type="submit"]')
        .click();
    await page.waitForTimeout(3000);

    // Assertion: TIDAK redirect ke sukses (server reject via max:2048)
    expect(page.url()).not.toContain("/sukses");
    await expect(page.locator("body")).toContainText(/Pembayaran Pesanan/i);

    // 📸 Evidence
    await snap(page, "TC-PB-007_ukuran_5mb_ditolak", { fullPage: true });
});

// ═══════════════════════════════════════════════════════════════════
// TC-PB-008 — Status pembayaran berubah jadi Pending setelah upload
// ═══════════════════════════════════════════════════════════════════
test("TC-PB-008 – Status pembayaran berubah jadi Pending setelah upload", async ({
    page,
}) => {
    test.setTimeout(90000);

    await siapkanRingkasan(page);
    await lanjutKePembayaran(page);

    // Upload file valid
    const input = page.locator('input[name="proof"]');
    await input.setInputFiles(buktiValid);
    await page.waitForTimeout(300);

    // Submit
    await page
        .locator('form[action*="/pembayaran"] button[type="submit"]')
        .click();
    await page.waitForURL(/\/pembayaran\/sukses/, { timeout: 15000 });

    // Assertion: status "pending" tampil
    await expect(page.locator("body")).toContainText(/Status Pembayaran:/i);
    await expect(page.locator("body")).toContainText(/pending/i);

    // 📸 Evidence: highlight baris "Status Pembayaran: pending"
    const statusRow = page
        .getByText("Status Pembayaran:", { exact: false })
        .locator("xpath=..");
    await snapElementHighlight(
        page,
        "TC-PB-008_status_pending",
        statusRow
    );
});