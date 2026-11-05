import { expect } from "@playwright/test";

export async function lanjutKePembayaran(page) {
    // Pastikan sedang berada di halaman ringkasan
    await expect(page.locator("h1")).toHaveText("Ringkasan Pesanan");

    const formKonfirmasi = page.locator('form[action*="konfirmasi"]');
    const tombolKonfirmasi = formKonfirmasi.locator('button[type="submit"]');

    await expect(tombolKonfirmasi).toBeVisible();
    await expect(tombolKonfirmasi).toBeEnabled();

    await tombolKonfirmasi.click();

    // Pastikan masuk ke halaman pembayaran
    await expect(page).toHaveURL(
        /\/customer\/pemesanan\/[^/]+\/pembayaran$/,
        { timeout: 15000 },
    );

    // Cek heading halaman pembayaran
    await expect(page.locator("h1, h2").first()).toContainText(/Pembayaran/i);
}