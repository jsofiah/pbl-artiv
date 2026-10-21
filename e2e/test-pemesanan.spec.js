import { test, expect } from "@playwright/test";
import path from "path";

test.use({ storageState: "e2e/.auth/customer.json" });

const PRODUCT_ID = "01a13f0c-a189-72ae-9846-363f89b1f2fb";
const fotoReferensi = path.resolve("e2e/img/tes-upload-foto.jpg");

test.describe("Alur E2E ARTIV: Katalog ke Form Pemesanan", () => {
    test("Customer langsung akses katalog, pilih produk, dan isi form pemesanan", async ({
        page,
    }) => {
        // 1. Langsung kunjungi halaman Katalog Jasa dengan domcontentloaded
        await page.goto("/customer/katalog", { waitUntil: "domcontentloaded" });
        await expect(page).toHaveURL(/\/customer\/katalog/);
        await expect(page.locator("h1")).toHaveText("Butuh desain?");

        // 2. Pilih produk Banner di katalog
        const bannerProductCard = page
            .locator('a[href*="/customer/katalog/"]')
            .filter({ hasText: "Banner" });

        await bannerProductCard.click();

        // 3. Verifikasi masuk ke Detail Produk & klik Pesan
        await expect(page).toHaveURL(
            new RegExp(`/customer/katalog/${PRODUCT_ID}`),
        );
        await page.click('a:has-text("Pesan Jasa Sekarang")');

        // 4. Verifikasi tiba di Form Pemesanan
        await expect(page).toHaveURL(
            new RegExp(`/customer/pemesanan/${PRODUCT_ID}`),
        );
        await expect(page.locator("h1")).toHaveText("Form Pemesanan");

        // 5. Pengisian Form Pemesanan

        // a. Pilih Paket Desain (Klik label pembungkus karena input-nya hidden)
        await page
            .locator('label:has(input[name="product_tier_id"])')
            .first()
            .click();

        // b. Atur Kuantitas Pesanan
        const quantityInput = page.locator('input[name="quantity"]');

        const btnPlus = page.locator("button").filter({
            has: page.locator('path[d="M12 4v16m8-8H4"]'),
        });

        const btnMinus = page.locator("button").filter({
            has: page.locator('path[d="M20 12H4"]'),
        });

        // Baca nilai awal dari halaman
        const initialQuantity = Number(await quantityInput.inputValue());

        console.log("Kuantitas awal:", initialQuantity);

        // Tentukan kuantitas akhir yang diinginkan
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

        console.log("Kuantitas akhir:", await quantityInput.inputValue());

        // c. Pilih Deadline (Pilih 'standar' atau 'express')
        await page.locator("label").filter({ hasText: "express" }).click();
        const targetInput = page.locator('input[name="target_deadline"]');
        await targetInput.evaluate((el) => {
            el.value = "2026-10-19"; // Ganti dengan tanggal yang valid sesuai rentang express
            el.dispatchEvent(new Event("input", { bubbles: true }));
            el.dispatchEvent(new Event("change", { bubbles: true }));
        });

        // d. Unggah File Referensi
        const fotoReferensi = path.resolve("e2e/img/tes-upload-foto.jpg");
        const inputFile = page.locator('input[name="reference_files[]"]');

        // Pantau error JavaScript pada halaman
        page.on("pageerror", (error) => {
            console.log("[PAGE ERROR]", error.message);
        });

        // Pantau console browser
        page.on("console", (message) => {
            if (message.type() === "error") {
                console.log("[BROWSER ERROR]", message.text());
            }
        });

        console.log("Path foto:", fotoReferensi);

        await expect(inputFile).toHaveCount(1);
        await inputFile.setInputFiles(fotoReferensi);

        // Pastikan handler Blade selesai menyimpan file ke selectedFiles
        await expect(page.locator("#file-list")).toContainText(
            "tes-upload-foto.jpg",
        );

        // Periksa file yang tersimpan di selectedFiles
        const selectedFilesInfo = await page.evaluate(() => {
            return {
                selectedFilesExists: typeof selectedFiles !== "undefined",
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

        console.log("Isi selectedFiles:", selectedFilesInfo);

        // Pastikan file tersimpan di array
        expect(selectedFilesInfo.files.length).toBeGreaterThan(0);
        expect(selectedFilesInfo.files[0].name).toBe("tes-upload-foto.jpg");

        // Kembalikan file ke input sebelum submit.
        // Handler Blade sebelumnya mengosongkan input.value,
        // sehingga file perlu dimasukkan kembali.
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

        // Verifikasi file benar-benar berada di input saat ini
        const filesInInput = await inputFile.evaluate((input) =>
            Array.from(input.files).map((file) => ({
                name: file.name,
                size: file.size,
                type: file.type,
            })),
        );

        console.log("File di input sebelum submit:", filesInInput);

        expect(filesInInput.length).toBeGreaterThan(0);
        expect(filesInInput[0].name).toBe("tes-upload-foto.jpg");

        console.log("Upload siap dikirim saat form disubmit!");

        // e. Isi Tautan Referensi Eksternal (Opsional/Alternatif jika tidak upload file)
        await page.fill(
            'input[name="reference_links[]"]',
            "https://www.figma.com/@example-design-reference",
        );

        // f. Isi Catatan Brief Desain (Minimal 10 karakter sesuai validasi controller)
        await page.fill(
            "#brief_note",
            "Tolong buatkan desain dengan konsep minimalis modern, warna dominan ungu dan putih.",
        );

        // =========================================================================
        // DIAGNOSTIK STATE ALPINE.JS
        // =========================================================================
        const alpineState = await page.evaluate(() => {
            // Cari elemen apa saja di dalam halaman yang memiliki x-data pemesananForm
            const alpineEl =
                document.querySelector('[x-data*="pemesananForm"]') ||
                document.querySelector('form[action*="ringkasan"]');
            if (!alpineEl)
                return {
                    error: "Elemen Alpine x-data tidak ditemukan sama sekali",
                };

            const data = alpineEl._x_dataStack
                ? alpineEl._x_dataStack[0]
                : null;
            if (!data)
                return {
                    error: "Data Alpine (_x_dataStack) kosong pada elemen",
                };

            return {
                selectedTier: data.selectedTier,
                quantity: data.quantity,
                deadlineOption: data.deadlineOption,
                targetDeadline: data.targetDeadline,
                durationDays: data.durationDays,
                selectedFee: data.selectedFee,
                briefNoteLength: (data.briefNote || "").trim().length,
                isFormValid: data.isFormValid,
            };
        });

        // g. Submit Form ke Halaman Ringkasan
        const submitButton = page.locator(
            'form[action*="ringkasan"] button[type="submit"]',
        );

        await expect(submitButton).toBeVisible();
        await submitButton.scrollIntoViewIfNeeded();
        await expect(submitButton).toBeEnabled();

        // Ambil semua data yang diinput sebelum submit
        const dataPemesanan = {
            produk: "Banner",

            paketDesain: await page
                .locator('input[name="product_tier_id"]:checked')
                .evaluate((input) => {
                    const label = input.closest("label");
                    return label?.innerText.trim() ?? input.value;
                })
                .catch(() => "Paket tidak terbaca"),

            kuantitas: await page
                .locator('input[name="quantity"]')
                .inputValue()
                .catch(() => "Selector quantity perlu disesuaikan"),

            deadline: await page
                .locator('input[name="deadline_option"]:checked')
                .inputValue()
                .catch(() => "Deadline tidak terbaca"),

            fileReferensi: await page
                .locator('input[name="reference_files[]"]')
                .evaluate((input) =>
                    Array.from(input.files ?? []).map((file) => ({
                        nama: file.name,
                        ukuranKB: Number((file.size / 1024).toFixed(2)),
                        tipe: file.type,
                    })),
                ),

            tautanReferensi: await page
                .locator('input[name="reference_links[]"]')
                .inputValue()
                .catch(() => ""),

            catatanBrief: await page.locator("#brief_note").inputValue(),
        };

        // Pantau response POST sebelum submit
        const postResponsePromise = page.waitForResponse((response) => {
            return (
                response.request().method() === "POST" &&
                response
                    .url()
                    .includes(`/customer/pemesanan/${PRODUCT_ID}/ringkasan`)
            );
        });

        // ==========================================
        // LOG DATA FORM PEMESANAN
        // ==========================================
        const orderForm = page.locator('form[action*="ringkasan"]');

        const formData = await orderForm.evaluate((form) => {
            const result = {};

            for (const input of form.querySelectorAll(
                "input, select, textarea",
            )) {
                const name = input.name;

                // Abaikan input tanpa nama dan token keamanan
                if (!name || ["_token", "_method"].includes(name)) {
                    continue;
                }

                let value;

                if (input.type === "file") {
                    value = Array.from(input.files ?? []).map((file) => ({
                        nama: file.name,
                        ukuranByte: file.size,
                        tipe: file.type,
                    }));
                } else if (
                    input.type === "checkbox" ||
                    input.type === "radio"
                ) {
                    if (!input.checked) continue;

                    value = {
                        value: input.value,
                        label:
                            input.closest("label")?.innerText.trim() ??
                            input.labels?.[0]?.innerText.trim() ??
                            null,
                    };
                } else if (input.tagName === "SELECT") {
                    const selected = Array.from(input.selectedOptions);

                    value = selected.map((option) => ({
                        value: option.value,
                        label: option.textContent.trim(),
                    }));

                    if (!input.multiple) {
                        value = value[0] ?? null;
                    }
                } else {
                    value = input.value;
                }

                // Mendukung field dengan nama berulang seperti reference_links[]
                if (Object.hasOwn(result, name)) {
                    if (!Array.isArray(result[name])) {
                        result[name] = [result[name]];
                    }

                    result[name].push(value);
                } else {
                    result[name] = value;
                }
            }

            return result;
        });

        // Petakan input form ke field model Order
        const orderPreview = {
            order_code: "Dibuat oleh backend",
            customer_id: "Dari akun customer yang login",
            designer_id: "Ditentukan oleh alur penugasan",
            product_id: PRODUCT_ID,
            product_tier_id: formData.product_tier_id ?? "Tidak ditemukan",
            unit_price: "Perlu diambil dari harga paket/backend",
            quantity:
                formData.quantity ??
                formData.qty ??
                "Periksa nama input kuantitas",
            deadline: {
                pilihan: formData.deadline_option ?? "Tidak ditemukan",
                tanggal:
                    formData.target_deadline ?? "Tidak diisi / tidak ditemukan",
            },
            assigned_at: "Ditentukan backend",
            is_express:
                formData.deadline_option === "express"
                    ? true
                    : formData.deadline_option === "default"
                      ? false
                      : "Perlu diverifikasi dari backend",
            express_fee_id: "Ditentukan backend jika express",
            express_fee: "Dihitung backend jika express",
            brief_note: formData.brief_note ?? "Tidak ditemukan",
            total_price: "Dihitung backend",
            status: "Perlu diverifikasi dari backend",
            revision_count: "Nilai awal dari backend",
            is_late: "Nilai awal dari backend",
            late_at: null,
            cannot_continue_reason: null,
            cannot_continue_at: null,
            customer_decision: null,
            customer_decision_at: null,
            is_urgent: "Perlu diverifikasi dari backend",
            original_deadline: "Perlu diverifikasi dari backend",
            reassign_count: "Nilai awal dari backend",
            completed_at: null,
            cancelled_at: null,
        };

        console.log("\n");
        console.log("==================================================");
        console.log("       DATA FORM PEMESANAN ARTIV");
        console.log("==================================================");
        console.log(JSON.stringify(formData, null, 2));

        console.log("\n==================================================");
        console.log("       PEMETAAN KE MODEL ORDER");
        console.log("==================================================");
        console.log(JSON.stringify(orderPreview, null, 2));

        await submitButton.click();

        const postResponse = await postResponsePromise;

        console.log("POST URL:", postResponse.url());
        console.log("STATUS:", postResponse.status());
        console.log("REDIRECT LOCATION:", postResponse.headers()["location"]);

        // Verifikasi halaman Ringkasan Pesanan
        await expect(page).toHaveURL(
            new RegExp(`/customer/pemesanan/${PRODUCT_ID}/ringkasan$`),
            { timeout: 15000 },
        );

        await expect(page.locator("h1")).toHaveText("Ringkasan Pesanan");

        await expect(page.locator("body")).toContainText(
            "Tolong buatkan desain dengan konsep minimalis modern",
        );

        console.log("\n==================================================");
        console.log("       DIAGNOSTIK STATE ALPINE.JS");
        console.log("==================================================");
        console.log(JSON.stringify(alpineState, null, 2));
        console.log("==================================================\n");

        // Tampilkan rangkuman data pemesanan
        console.log("\n========== HASIL DATA PEMESANAN ==========");
        console.log(JSON.stringify(dataPemesanan, null, 2));
        console.log("==========================================");
        console.log("Status akhir: BERHASIL");
        console.log("Halaman akhir:", page.url());
    });
});
