import { expect } from "@playwright/test";

const PRODUCT_ID = "01a13f0c-a189-72ae-9846-363f89b1f2fb";

export async function lanjutKePembayaran(page) {
    // Pastikan sedang berada di halaman ringkasan
    await expect(page.locator("h1")).toHaveText("Ringkasan Pesanan");

    const formKonfirmasi = page.locator('form[action*="konfirmasi"]');
    const tombolKonfirmasi = formKonfirmasi.locator(
        'button[type="submit"]',
    );

    await expect(tombolKonfirmasi).toBeVisible();
    await expect(tombolKonfirmasi).toBeEnabled();

    // Pantau request konfirmasi
    const postResponsePromise = page.waitForResponse((response) => {
        return (
            response.request().method() === "POST" &&
            response.url().includes(
                `/customer/pemesanan/${PRODUCT_ID}/konfirmasi`,
            )
        );
    });

    await tombolKonfirmasi.click();

    const response = await postResponsePromise;

    console.log("POST konfirmasi:", response.url());
    console.log("Status konfirmasi:", response.status());

    expect(response.status()).toBeLessThan(400);

    // Pastikan masuk ke halaman pembayaran
    await expect(page).toHaveURL(
        /\/customer\/pemesanan\/[^/]+\/pembayaran$/,
        { timeout: 15000 },
    );

    await expect(page.locator("h1")).toHaveText(
        "Konfirmasi Pembayaran",
    );

    console.log("Berhasil masuk ke halaman pembayaran:", page.url());
}
