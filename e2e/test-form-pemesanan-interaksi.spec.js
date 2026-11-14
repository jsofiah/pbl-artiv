import { test, expect } from "@playwright/test";
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

// ─────────────────────────────────────────────────────────────────
// TC-FP-005 — Tombol + menambah quantity
// ─────────────────────────────────────────────────────────────────
test("TC-FP-005 Tombol + menambah quantity dari 1 ke 2", async ({
    page,
}) => {
    const qty = page.locator('input[name="quantity"]');
    const plus = page.locator('button:has(path[d="M12 4v16m8-8H4"])');

    // Kondisi awal: qty = 1
    await expect(qty).toHaveValue("1");

    // Klik + → 2
    await plus.click();
    await expect(qty).toHaveValue("2");

    // 📸 Evidence: highlight nilai qty yang sekarang = 2
    await snapElementHighlight(
        page,
        "TC-FP-005_qty_bertambah_jadi_2",
        qty.locator("xpath=..") // div pembungkus qty (tampilan angka)
    );
});

// ─────────────────────────────────────────────────────────────────
// TC-FP-006 — Tombol − mengurangi quantity
// ─────────────────────────────────────────────────────────────────
test("TC-FP-006 Tombol mengurangi quantity dari 2 ke 1", async ({
    page,
}) => {
    const qty = page.locator('input[name="quantity"]');
    const plus = page.locator('button:has(path[d="M12 4v16m8-8H4"])');
    const minus = page.locator('button:has(path[d="M20 12H4"])');

    // Setup: naikkan ke 2 dulu
    await plus.click();
    await expect(qty).toHaveValue("2");

    // Test: klik − → 1
    await minus.click();
    await expect(qty).toHaveValue("1");

    // 📸 Evidence: highlight tombol − (yang dipakai menurunkan qty)
    await snapElementHighlight(
        page,
        "TC-FP-006_qty_berkurang_jadi_1",
        minus
    );

    // Bonus assertion: klik − saat sudah 1 → tetap 1 (min = 1)
    await minus.click();
    await expect(qty).toHaveValue("1");
});

// ─────────────────────────────────────────────────────────────────
// TC-FP-011 — Klik "+ Tambah Tautan" menambah input baru
// ─────────────────────────────────────────────────────────────────
test("TC-FP-011 Klik '+ Tambah Tautan' menambah input baru", async ({
    page,
}) => {
    const linkInputs = page.locator('input[name="reference_links[]"]');
    const countAwal = await linkInputs.count();
    expect(countAwal).toBe(1);

    const btnTambah = page.locator('button:has-text("Tambah Tautan")');
    await btnTambah.scrollIntoViewIfNeeded();
    await btnTambah.click();
    await page.waitForTimeout(200);

    await expect(linkInputs).toHaveCount(countAwal + 1);

    // 📸 Evidence: highlight input tautan BARU (yang ke-2)
    await snapElementHighlight(
        page,
        "TC-FP-011_tambah_input_tautan",
        linkInputs.nth(1)
    );

    // Klik lagi → total 3
    await btnTambah.click();
    await expect(linkInputs).toHaveCount(countAwal + 2);
});