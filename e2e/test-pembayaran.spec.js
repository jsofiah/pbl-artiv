import { test, expect } from "@playwright/test";
import path from "path";

import { siapkanRingkasan } from "./helpers/pemesanan.helper.spec.js";
import { lanjutKePembayaran } from "./helpers/ringkasan.helper.spec.js";

test.use({
    storageState: "e2e/.auth/customer.json",
});

test("Customer mengunggah bukti pembayaran", async ({ page }) => {
    // 1. Siapkan pesanan sampai halaman ringkasan
    await siapkanRingkasan(page);

    // 2. Konfirmasi ringkasan sampai halaman pembayaran
    await lanjutKePembayaran(page);

    // 3. Pastikan elemen pembayaran tampil
    await expect(
        page.getByRole("heading", {
            name: "Upload Bukti Pembayaran",
            exact: true,
        }),
    ).toBeVisible();

    // 4. Siapkan file gambar untuk bukti pembayaran
    const buktiPembayaran = path.resolve("e2e/img/tes-upload-foto.jpg");

    const inputFile = page.locator('input[name="proof"]');

    await expect(inputFile).toHaveCount(1);

    // 5. Upload file
    await inputFile.setInputFiles(buktiPembayaran);

    // 6. Verifikasi file benar-benar masuk ke input
    const fileInfo = await inputFile.evaluate((input) => {
        const file = input.files?.[0];

        if (!file) return null;

        return {
            nama: file.name,
            ukuranByte: file.size,
            tipe: file.type,
        };
    });

    console.log("\n========== BUKTI PEMBAYARAN ==========");
    console.log(JSON.stringify(fileInfo, null, 2));

    expect(fileInfo).not.toBeNull();
    expect(fileInfo.nama).toBe("tes-upload-foto.jpg");
    expect(fileInfo.tipe).toBe("image/jpeg");
    expect(fileInfo.ukuranByte).toBeGreaterThan(0);
    expect(fileInfo.ukuranByte).toBeLessThanOrEqual(2 * 1024 * 1024);

    // 7. Verifikasi nama file ditampilkan oleh Alpine.js
    await expect(
        page.locator("p").filter({
            hasText: "File Dipilih: tes-upload-foto.jpg",
        }),
    ).toBeVisible();

    // 8. Pantau request POST pembayaran sebelum klik Kirim
    const formPembayaran = page.locator('form[action*="/pembayaran"]');

    const tombolKirim = formPembayaran.locator('button[type="submit"]');

    await expect(tombolKirim).toBeVisible();
    await expect(tombolKirim).toBeEnabled();

    const postResponsePromise = page.waitForResponse((response) => {
        return (
            response.request().method() === "POST" &&
            /\/customer\/pemesanan\/[^/]+\/pembayaran(?:\?.*)?$/.test(
                new URL(response.url()).pathname +
                    new URL(response.url()).search,
            )
        );
    });

    // 9. Klik tombol Kirim
    await tombolKirim.click();

    // 10. Tunggu respons dari backend
    const postResponse = await postResponsePromise;

    console.log("\n========== HASIL SUBMIT PEMBAYARAN ==========");
    console.log("POST URL:", postResponse.url());
    console.log("Status HTTP:", postResponse.status());
    console.log(
        "Redirect Location:",
        postResponse.headers()["location"] ?? "Tidak ada",
    );

    expect(postResponse.status()).toBeLessThan(400);

    // 11. Tunggu navigasi/redirect selesai jika backend melakukan redirect
    await page.waitForLoadState("domcontentloaded").catch(() => {});

    console.log("URL setelah submit:", page.url());
    console.log("Status akhir: FORM PEMBAYARAN BERHASIL DISUBMIT");
});
