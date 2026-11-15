import { expect } from "@playwright/test";

/**
 * Setujui modal Ketentuan Layanan kalau muncul.
 * Modal ini harus disetujui sebelum tombol "Lanjut ke Pembayaran" bisa diklik.
 */
async function setujuiToSSaja(page) {
    // Modal ToS: cari tombol "Setujui & Lanjutkan" (mungkin disabled)
    const tombolSetuju = page.getByRole("button", { name: /Setujui & Lanjutkan/i });

    // Kalau modal tidak muncul, langsung return
    const muncul = await tombolSetuju.isVisible({ timeout: 3000 }).catch(() => false);
    if (!muncul) return;

    // Centang checkbox persetujuan
    const checkbox = page.getByRole("checkbox", {
        name: /Saya telah membaca dan menyetujui Ketentuan Layanan/i,
    });
    await expect(checkbox).toBeVisible();
    await checkbox.check();   // .check() akan memastikan checkbox tercentang

    // Sekarang tombol harus enabled
    await expect(tombolSetuju).toBeEnabled();

    // Klik setuju, tunggu modal hilang
    await tombolSetuju.click();
    await expect(tombolSetuju).toBeHidden({ timeout: 5000 });
}

/**
 * Lanjut dari halaman ringkasan ke pembayaran.
 * Return true jika berhasil ke pembayaran.
 * Return false jika order sudah punya payment (redirect ke /sukses).
 */
export async function lanjutKePembayaran(page) {
    await expect(page.locator("h1")).toHaveText("Ringkasan Pesanan");

    // ⭐ FIX: setujui modal ToS dulu (kalau ada)
    await setujuiToSSaja(page);

    const formKonfirmasi = page.locator('form[action*="konfirmasi"]');
    const tombolKonfirmasi = formKonfirmasi.locator('button[type="submit"]');

    await expect(tombolKonfirmasi).toBeVisible();
    await expect(tombolKonfirmasi).toBeEnabled();

    // Tunggu navigasi sungguhan (jangan di-catch kosong)
    await Promise.all([
        page.waitForURL(
            (url) =>
                url.pathname.includes("/pembayaran") ||
                url.pathname.includes("/sukses"),
            { timeout: 15000 }
        ),
        tombolKonfirmasi.click(),
    ]);

    const currentUrl = page.url();
    console.log("URL setelah konfirmasi:", currentUrl);

    if (currentUrl.includes("/sukses")) {
        console.log("Order sudah punya payment — redirect ke sukses");
        return false;
    }

    if (currentUrl.includes("/pembayaran")) {
        await expect(page.locator("h1, h2").first()).toContainText(/Pembayaran/i);
        return true;
    }

    throw new Error(
        `Tidak redirect ke pembayaran atau sukses. URL: ${currentUrl}`
    );
}