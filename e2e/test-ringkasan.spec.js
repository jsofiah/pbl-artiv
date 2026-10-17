
import { test, expect } from "@playwright/test";
import { siapkanRingkasan } from "./helpers/pemesanan.helper.spec.js";

test.use({ storageState: "e2e/.auth/customer.json" });

test("Customer menguji halaman ringkasan sampai pembayaran", async ({ page }) => {
    await siapkanRingkasan(page);

    await expect(page.locator("body")).toContainText("TOTAL TAGIHAN");

    // Pastikan halaman ringkasan tampil
    await expect(page).toHaveURL(/\/customer\/pemesanan\/.*\/ringkasan$/);
    await expect(
        page.getByRole("heading", { name: /ringkasan pesanan/i })
    ).toBeVisible();

    // Pastikan total tagihan ditampilkan
    await expect(page.locator("body")).toContainText("TOTAL TAGIHAN");

    // Pastikan tombol konfirmasi tersedia
    const tombolKonfirmasi = page.getByRole("button", {
        name: /konfirmasi pesanan|lanjut ke pembayaran|bayar sekarang/i,
    });

    await expect(tombolKonfirmasi).toBeVisible();
    await tombolKonfirmasi.click();

    // Pastikan berpindah ke halaman pembayaran
    await expect(page).toHaveURL(
        /\/customer\/pemesanan\/[^/]+\/pembayaran/,
        { timeout: 15000 }
    );
});
