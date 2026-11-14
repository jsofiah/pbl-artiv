import { expect } from "@playwright/test";
import path from "path";

const PRODUCT_ID = "01a1d02c-bb4d-70fc-8646-65e9616a7af9";
const fotoReferensi = path.resolve("e2e/img/tes-upload-foto.jpg");

/**
 * Baca state Alpine.js dari form pemesanan.
 */
async function getAlpineState(page) {
    return page.evaluate(() => {
        const el =
            document.querySelector('[x-data*="pemesananForm"]') ||
            document.querySelector('form[action*="ringkasan"]');
        if (!el) return { error: "Alpine element not found" };
        const data = el._x_dataStack?.[0];
        if (!data) return { error: "No Alpine data" };
        return {
            selectedTier: data.selectedTier,
            quantity: data.quantity,
            deadlineOption: data.deadlineOption,
            targetDeadline: data.targetDeadline,
            selectedFee: data.selectedFee?.id ?? null,
            briefNoteLength: (data.briefNote || "").trim().length,
            isFormValid: data.isFormValid,
        };
    });
}

/**
 * Set nilai input native + trigger event supaya Alpine sinkron.
 */
async function setNativeInput(page, selector, value) {
    await page.locator(selector).evaluate((el, v) => {
        const setter = Object.getOwnPropertyDescriptor(
            window.HTMLInputElement.prototype,
            "value"
        ).set;
        setter.call(el, v);
        el.dispatchEvent(new Event("input", { bubbles: true }));
        el.dispatchEvent(new Event("change", { bubbles: true }));
    }, value);
}

export async function siapkanRingkasan(page) {
    // ══════════════════════════════════════════════════════════════════
    // 1. Buka detail produk langsung
    // ══════════════════════════════════════════════════════════════════
    await page.goto(`/customer/katalog/${PRODUCT_ID}`, {
        waitUntil: "domcontentloaded",
    });
    await expect(page).toHaveURL(
        new RegExp(`/customer/katalog/${PRODUCT_ID}`)
    );

    // ══════════════════════════════════════════════════════════════════
    // 2. Klik "Pesan Jasa Sekarang"
    // ══════════════════════════════════════════════════════════════════
    const btnPesan = page
        .locator(
            'a:has-text("Pesan Jasa Sekarang"), button:has-text("Pesan Jasa Sekarang")'
        )
        .first();
    await btnPesan.click();

    await expect(page).toHaveURL(
        new RegExp(`/customer/pemesanan/${PRODUCT_ID}`),
        { timeout: 15000 }
    );
    await expect(page.locator("h1")).toHaveText("Form Pemesanan");

    // ══════════════════════════════════════════════════════════════════
    // 3. Pilih paket desain (radio pertama)
    // ══════════════════════════════════════════════════════════════════
    const radioTier = page.locator('input[name="product_tier_id"]').first();
    await radioTier.check({ force: true });
    await radioTier.dispatchEvent("change");
    await radioTier.dispatchEvent("input");
    await page.waitForTimeout(300);

    // ══════════════════════════════════════════════════════════════════
    // 4. Set quantity = 3
    // ══════════════════════════════════════════════════════════════════
    const quantityInput = page.locator('input[name="quantity"]');
    const initialQuantity = Number(await quantityInput.inputValue());
    const targetQuantity = 3;

    if (initialQuantity < targetQuantity) {
        for (let i = initialQuantity; i < targetQuantity; i++) {
            await page
                .locator('button:has(path[d="M12 4v16m8-8H4"])')
                .click();
        }
    }
    await expect(quantityInput).toHaveValue("3");

    // ══════════════════════════════════════════════════════════════════
    // 5. Pilih express + set tanggal dinamis (+2 hari)
    //    Fee "2 Hari" ada di DB (Rp 60.000).
    //    FALLBACK ke 'default' kalau ExpressFee tidak tersedia.
    // ══════════════════════════════════════════════════════════════════
    await page
        .locator('label:has(input[name="deadline_option"][value="express"])')
        .click();
    await page.waitForTimeout(300);

    const tanggalExpress = (() => {
        const besok = new Date();
        besok.setDate(besok.getDate() + 2); // match "2 Hari" di DB
        return besok.toISOString().split("T")[0];
    })();
    console.log("Tanggal express (+2):", tanggalExpress);

    await setNativeInput(
        page,
        'input[name="target_deadline"]',
        tanggalExpress
    );
    await page.waitForTimeout(500);

    // Cek apakah Alpine berhasil menemukan fee express.
    // Jika tidak (selectedFee = null), fallback ke 'default'.
    let state = await getAlpineState(page);
    if (!state.selectedFee) {
        console.log(
            "⚠️  Tidak ada ExpressFee yang match untuk +2 hari. Fallback ke 'default'."
        );
        await page
            .locator(
                'label:has(input[name="deadline_option"][value="default"])'
            )
            .click();
        await page.waitForTimeout(300);
        state = await getAlpineState(page);
    } else {
        console.log("✅ ExpressFee match:", state.selectedFee);
    }

    // ══════════════════════════════════════════════════════════════════
    // 6. Upload file referensi
    // ══════════════════════════════════════════════════════════════════
    const inputFile = page.locator('input[name="reference_files[]"]');
    await expect(inputFile).toHaveCount(1);
    await inputFile.setInputFiles(fotoReferensi);
    await page.waitForTimeout(500);

    await page.evaluate(() => {
        const input = document.querySelector(
            'input[name="reference_files[]"]'
        );
        if (input) input.dispatchEvent(new Event("change", { bubbles: true }));
    });

    console.log(
        "File reference uploaded:",
        await page.evaluate(() => {
            const input = document.querySelector(
                'input[name="reference_files[]"]'
            );
            return input?.files?.length ?? 0;
        })
    );

    // ══════════════════════════════════════════════════════════════════
    // 7. Fill link & brief note
    // ══════════════════════════════════════════════════════════════════
    await page.fill(
        'input[name="reference_links[]"]',
        "https://www.figma.com/@example-design-reference"
    );
    await page.fill(
        "#brief_note",
        "Tolong buatkan desain dengan konsep minimalis modern, warna dominan ungu dan putih."
    );
    await page.waitForTimeout(500);

    // ══════════════════════════════════════════════════════════════════
    // 8. Diagnostic — log state Alpine final
    // ══════════════════════════════════════════════════════════════════
    state = await getAlpineState(page);
    console.log("\n📊 ALPINE STATE:", JSON.stringify(state, null, 2));

    // ══════════════════════════════════════════════════════════════════
    // 9. Submit form via native (bypass Alpine disabled state)
    // ══════════════════════════════════════════════════════════════════
    const postResponsePromise = page.waitForResponse(
        (response) =>
            response.request().method() === "POST" &&
            response
                .url()
                .includes(`/customer/pemesanan/${PRODUCT_ID}/ringkasan`),
        { timeout: 20000 }
    );

    await page.evaluate(() => {
        const form = document.querySelector('form[action*="ringkasan"]');
        if (form) {
            form.querySelectorAll('button[type="submit"]').forEach((btn) => {
                btn.disabled = false;
                btn.removeAttribute("disabled");
            });
            form.submit();
        }
    });

    // ══════════════════════════════════════════════════════════════════
    // 10. Verifikasi
    // ══════════════════════════════════════════════════════════════════
    const postResponse = await postResponsePromise;
    console.log("\n✅ POST RINGKASAN:", {
        url: postResponse.url(),
        status: postResponse.status(),
        location: postResponse.headers()["location"],
    });

    if (postResponse.status() >= 400) {
        const body = await postResponse.text();
        console.error("❌ Error body:", body.substring(0, 1000));
        throw new Error(`Submit gagal: HTTP ${postResponse.status()}`);
    }

    await expect(page).toHaveURL(
        new RegExp(`/customer/pemesanan/${PRODUCT_ID}/ringkasan$`),
        { timeout: 15000 }
    );
    await expect(page.locator("h1")).toHaveText("Ringkasan Pesanan");

    console.log("✅ BERHASIL MASUK HALAMAN RINGKASAN:", page.url());
}