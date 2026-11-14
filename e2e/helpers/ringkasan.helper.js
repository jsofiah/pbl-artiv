import { expect } from "@playwright/test";

/**
 * Lanjut dari halaman ringkasan ke pembayaran.
 * Return true jika berhasil ke pembayaran.
 * Return false jika order sudah punya payment (redirect ke /sukses).
 */
export async function lanjutKePembayaran(page) {
    await expect(page.locator("h1")).toHaveText("Ringkasan Pesanan");

    const formKonfirmasi = page.locator('form[action*="konfirmasi"]');
    const tombolKonfirmasi = formKonfirmasi.locator('button[type="submit"]');

    await expect(tombolKonfirmasi).toBeVisible();
    await expect(tombolKonfirmasi).toBeEnabled();

    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {}),
        tombolKonfirmasi.click(),
    ]);

    const currentUrl = page.url();
    console.log('URL setelah konfirmasi:', currentUrl);

    if (currentUrl.includes('/sukses')) {
        console.log('Order sudah punya payment — redirect ke sukses');
        return false;
    }

    if (currentUrl.includes('/pembayaran')) {
        await expect(page.locator("h1, h2").first()).toContainText(/Pembayaran/i);
        return true;
    }

    throw new Error(`Tidak redirect ke pembayaran atau sukses. URL: ${currentUrl}`);
}