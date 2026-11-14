import { test, expect } from "@playwright/test";
import path from "path";
import { snap, snapElementHighlight, saveEvidence } from "./helpers/evidence.js";

const PRODUCT_ID = "01a196f3-2bff-7333-904a-553b1004d27b";
const CUSTOMER = { email: "customer@gmail.com", password: "password" };

async function loginAsCustomer(page) {
    await page.goto("/login", { waitUntil: "domcontentloaded" });
    await page.fill('input[name="email"]', CUSTOMER.email);
    await page.fill('input[name="password"]', CUSTOMER.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/customer\/beranda/, { timeout: 15000 });
}

test.afterEach(async ({ page }, testInfo) => {
    if (testInfo.status !== "passed") {
        await saveEvidence(page, testInfo);
    }
});

test.beforeEach(async ({ page }) => {
    await loginAsCustomer(page);

    await page.goto(`/customer/pemesanan/${PRODUCT_ID}`, {
        waitUntil: "domcontentloaded",
    });
    await expect(page.locator("h1")).toHaveText("Form Pemesanan", {
        timeout: 15000,
    });
    await page.waitForFunction(() => {
        const el = document.querySelector('[x-data*="pemesananForm"]');
        return el?._x_dataStack?.[0] !== undefined;
    });
});

function toYMD(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${y}-${m}-${day}`;
}

// ─────────────────────────────────────────────────────────────────
// TC-FP-007 — Kalender menolak tanggal < H+2
// ─────────────────────────────────────────────────────────────────
test("TC-FP-007 Kalender menolak tanggal < H+2", async ({ page }) => {
    await page
        .locator('label:has(input[name="deadline_option"][value="express"])')
        .click();
    await page.waitForTimeout(500);

    const input = page.locator('input[name="target_deadline"]');
    await expect(input).toBeVisible();

    const setDate = async (dateStr) => {
        await input.evaluate((el, v) => {
            const setter = Object.getOwnPropertyDescriptor(
                window.HTMLInputElement.prototype,
                "value"
            ).set;
            setter.call(el, v);
            el.dispatchEvent(new Event("input", { bubbles: true }));
            el.dispatchEvent(new Event("change", { bubbles: true }));
        }, dateStr);
    };

    // H+0 → invalid
    const h0 = new Date();
    await setDate(toYMD(h0));
    expect(await input.evaluate((el) => el.validity.rangeUnderflow)).toBe(true);

    // H+1 → invalid
    const h1 = new Date();
    h1.setDate(h1.getDate() + 1);
    await setDate(toYMD(h1));
    expect(await input.evaluate((el) => el.validity.rangeUnderflow)).toBe(true);

    // H+2 → valid
    const h2 = new Date();
    h2.setDate(h2.getDate() + 2);
    await setDate(toYMD(h2));
    expect(await input.evaluate((el) => el.validity.rangeUnderflow)).toBe(false);

    // 📸 Evidence: highlight input tanggal (nilai = H+2 valid)
    await snapElementHighlight(
        page,
        "TC-FP-007_kalender_h_min_2",
        input
    );
});

// ─────────────────────────────────────────────────────────────────
// TC-FP-008 — Biaya express tampil setelah tanggal dipilih
// ─────────────────────────────────────────────────────────────────
test("TC-FP-008 Biaya express tampil setelah tanggal dipilih", async ({
    page,
}) => {
    await page
        .locator('label:has(input[name="deadline_option"][value="express"])')
        .click();
    await page.waitForTimeout(500);

    // H+2 match "2 Hari" (Rp 60.000) di DB
    const h2 = new Date();
    h2.setDate(h2.getDate() + 2);
    const isoDate = toYMD(h2);

    await page
        .locator('input[name="target_deadline"]')
        .evaluate((el, v) => {
            const setter = Object.getOwnPropertyDescriptor(
                window.HTMLInputElement.prototype,
                "value"
            ).set;
            setter.call(el, v);
            el.dispatchEvent(new Event("input", { bubbles: true }));
            el.dispatchEvent(new Event("change", { bubbles: true }));
        }, isoDate);
    await page.waitForTimeout(700);

    // ⭐ FIX: exact:true → cuma match <p>Biaya Tambahan Express</p>
    const feeLabel = page.getByText("Biaya Tambahan Express", {
        exact: true,
    });
    await expect(feeLabel).toBeVisible();

    const feeState = await page.evaluate(() => {
        const el = document.querySelector('[x-data*="pemesananForm"]');
        const data = el?._x_dataStack?.[0];
        if (!data) return null;
        return {
            deadlineOption: data.deadlineOption,
            targetDeadline: data.targetDeadline,
            durationDays: data.durationDays,
            selectedFee: data.selectedFee,
        };
    });
    console.log("Fee state:", JSON.stringify(feeState, null, 2));

    expect(feeState?.deadlineOption).toBe("express");
    expect(feeState?.selectedFee).not.toBeNull();
    expect(feeState.selectedFee.fee).toBeGreaterThan(0);

    // 📸 Evidence: highlight card biaya express + nilai fee
    const feeCard = feeLabel.locator(
        "xpath=ancestor::div[contains(@class,'bg-white')][1]"
    );
    await snapElementHighlight(
        page,
        "TC-FP-008_biaya_express_tampil",
        feeCard
    );
});

// ─────────────────────────────────────────────────────────────────
// TC-FP-009 — File > 20 MB ditolak
// ─────────────────────────────────────────────────────────────────
test("TC-FP-009 File > 20 MB ditolak dengan pesan error", async ({
    page,
}) => {
    const fileBesar = path.resolve("e2e/img/file-besar-25mb.pdf");
    const input = page.locator('input[name="reference_files[]"]');
    await expect(input).toHaveCount(1);
    await input.setInputFiles(fileBesar);
    await page.waitForTimeout(300);

    const errorEl = page.locator("#file-error");
    await expect(errorEl).toBeVisible();
    await expect(errorEl).toContainText(/melebihi 20MB/i);
    await expect(errorEl).toContainText(/file-besar-25mb\.pdf/i);

    await expect(page.locator("#file-list")).not.toContainText(
        "file-besar-25mb.pdf"
    );

    const filesCount = await page.evaluate(() =>
        typeof selectedFiles !== "undefined" ? selectedFiles.length : 0
    );
    expect(filesCount).toBe(0);
    
    // 📸 Evidence: highlight pesan error merah
    await snapElementHighlight(
        page,
        "TC-FP-009_file_ditolak_error_20mb",
        errorEl
    );
});

// ─────────────────────────────────────────────────────────────────
// TC-FP-010 — File < 20 MB diterima
// ─────────────────────────────────────────────────────────────────
test("TC-FP-010 File < 20 MB diterima dan muncul di daftar", async ({
    page,
}) => {
    const fotoRef = path.resolve("e2e/img/tes-upload-foto.jpg");
    const input = page.locator('input[name="reference_files[]"]');

    await input.setInputFiles(fotoRef);
    await page.waitForTimeout(300);

    // File muncul di list dengan nama
    const fileList = page.locator("#file-list");
    await expect(fileList).toBeVisible();
    await expect(fileList).toContainText("tes-upload-foto.jpg");

    // File muncul dengan ukuran (format "0.5 MB" atau "509.42 KB")
    await expect(fileList).toContainText(/\d+(\.\d+)?\s?(MB|KB)/);

    // Tidak ada error
    await expect(page.locator("#file-error")).not.toBeVisible();

    // File tersimpan di selectedFiles
    const filesCount = await page.evaluate(() =>
        typeof selectedFiles !== "undefined" ? selectedFiles.length : 0
    );
    expect(filesCount).toBe(1);

    // 📸 Evidence: highlight #file-list dengan file ter-upload
    await snapElementHighlight(
        page,
        "TC-FP-010_file_500kb_diterima",
        fileList
    );
});