
import { expect } from "@playwright/test";
import path from "path";

const PRODUCT_ID = "01a13f0c-a189-72ae-9846-363f89b1f2fb";
const fotoReferensi = path.resolve("e2e/img/tes-upload-foto.jpg");

export async function siapkanRingkasan(page) {
    // 1. Buka katalog
    await page.goto("/customer/katalog", {
        waitUntil: "domcontentloaded",
    });

    await expect(page).toHaveURL(/\/customer\/katalog/);
    await expect(page.locator("h1")).toHaveText("Butuh desain?");

    // 2. Pilih produk Banner
    const bannerProductCard = page
        .locator('a[href*="/customer/katalog/"]')
        .filter({ hasText: "Banner" });

    await bannerProductCard.click();

    // 3. Buka detail produk dan klik tombol pesan
    await expect(page).toHaveURL(
        new RegExp(`/customer/katalog/${PRODUCT_ID}`),
    );

    await page.click('a:has-text("Pesan Jasa Sekarang")');

    // 4. Pastikan masuk ke form pemesanan
    await expect(page).toHaveURL(
        new RegExp(`/customer/pemesanan/${PRODUCT_ID}`),
    );

    await expect(page.locator("h1")).toHaveText("Form Pemesanan");

    // 5. Pilih paket desain
    await page
        .locator('label:has(input[name="product_tier_id"])')
        .first()
        .click();

    // 6. Atur kuantitas menjadi 3
    const quantityInput = page.locator('input[name="quantity"]');

    const btnPlus = page.locator("button").filter({
        has: page.locator('path[d="M12 4v16m8-8H4"]'),
    });

    const btnMinus = page.locator("button").filter({
        has: page.locator('path[d="M20 12H4"]'),
    });

    const initialQuantity = Number(await quantityInput.inputValue());
    const targetQuantity = 3;

    if (initialQuantity < targetQuantity) {
        for (let i = initialQuantity; i < targetQuantity; i++) {
            await btnPlus.click();
        }
    } else if (initialQuantity > targetQuantity) {
        for (let i = initialQuantity; i > targetQuantity; i--) {
            await btnMinus.click();
        }
    }

    await expect(quantityInput).toHaveValue("3");

    // 7. Pilih deadline express dan isi tanggal
    await page.locator("label").filter({ hasText: "express" }).click();

    const targetInput = page.locator('input[name="target_deadline"]');

    await targetInput.evaluate((el) => {
        el.value = "2026-10-19";
        el.dispatchEvent(new Event("input", { bubbles: true }));
        el.dispatchEvent(new Event("change", { bubbles: true }));
    });

    // 8. Upload file referensi
    const inputFile = page.locator('input[name="reference_files[]"]');

    await expect(inputFile).toHaveCount(1);
    await inputFile.setInputFiles(fotoReferensi);

    // Tunggu handler Blade menyimpan file ke selectedFiles
    await expect(page.locator("#file-list")).toContainText(
        "tes-upload-foto.jpg",
    );

    // Pastikan file masuk ke selectedFiles
    const selectedFilesInfo = await page.evaluate(() => {
        return {
            exists: typeof selectedFiles !== "undefined",
            files:
                typeof selectedFiles !== "undefined"
                    ? selectedFiles.map((file) => ({
                          name: file.name,
                          size: file.size,
                          type: file.type,
                      }))
                    : [],
        };
    });

    console.log("File referensi terpilih:", selectedFilesInfo);

    expect(selectedFilesInfo.files.length).toBeGreaterThan(0);
    expect(selectedFilesInfo.files[0].name).toBe("tes-upload-foto.jpg");

    // Pulihkan file ke input karena handler Blade mengosongkan input.value
    await page.evaluate(() => {
        const input = document.querySelector(
            'input[name="reference_files[]"]',
        );

        const transfer = new DataTransfer();

        selectedFiles.forEach((file) => {
            transfer.items.add(file);
        });

        input.files = transfer.files;
    });

    // Verifikasi file siap dikirim
    const filesInInput = await inputFile.evaluate((input) =>
        Array.from(input.files).map((file) => ({
            name: file.name,
            size: file.size,
            type: file.type,
        })),
    );

    console.log("File sebelum submit:", filesInInput);

    expect(filesInInput.length).toBeGreaterThan(0);
    expect(filesInInput[0].name).toBe("tes-upload-foto.jpg");

    // 9. Isi tautan referensi
    await page.fill(
        'input[name="reference_links[]"]',
        "https://www.figma.com/@example-design-reference",
    );

    // 10. Isi brief desain
    await page.fill(
        "#brief_note",
        "Tolong buatkan desain dengan konsep minimalis modern, warna dominan ungu dan putih.",
    );

    // 11. Submit form ke ringkasan
    const submitButton = page.locator(
        'form[action*="ringkasan"] button[type="submit"]',
    );

    await expect(submitButton).toBeVisible();
    await submitButton.scrollIntoViewIfNeeded();
    await expect(submitButton).toBeEnabled();

    const postResponsePromise = page.waitForResponse((response) => {
        return (
            response.request().method() === "POST" &&
            response.url().includes(
                `/customer/pemesanan/${PRODUCT_ID}/ringkasan`,
            )
        );
    });

    await submitButton.click();

    const postResponse = await postResponsePromise;

    console.log("POST ringkasan:", {
        url: postResponse.url(),
        status: postResponse.status(),
        location: postResponse.headers()["location"],
    });

    expect(postResponse.status()).toBeLessThan(400);

    // 12. Verifikasi halaman ringkasan
    await expect(page).toHaveURL(
        new RegExp(`/customer/pemesanan/${PRODUCT_ID}/ringkasan$`),
        { timeout: 15000 },
    );

    await expect(page.locator("h1")).toHaveText("Ringkasan Pesanan");

    await expect(page.locator("body")).toContainText(
        "Tolong buatkan desain dengan konsep minimalis modern",
    );

    console.log("Berhasil menyiapkan ringkasan:", page.url());
}
