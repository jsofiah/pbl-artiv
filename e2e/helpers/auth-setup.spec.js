import { test as setup, expect } from "@playwright/test";

setup("authenticate customer", async ({ page }) => {
    page.on("pageerror", (error) => {
        console.log("JS ERROR:", error.message);
    });

    page.on("console", (message) => {
        if (message.type() === "error") {
            console.log("CONSOLE ERROR:", message.text());
        }
    });

    // 1. Buka halaman login
    await page.goto("/login", {
        waitUntil: "domcontentloaded",
    });

    // 2. Isi kredensial
    await page.locator("#email").fill("customer@gmail.com");
    await page.locator("#password").fill("customer1");

    // 3. Klik tombol login
    await page.getByRole("button", { name: "Masuk" }).click();

    // 4. Tunggu sampai URL beranda
    await expect(page).toHaveURL("http://localhost:8000/customer/beranda", {
        timeout: 10000,
    });

    // 5. Simpan sesi login
    await page.context().storageState({
        path: "e2e/.auth/customer.json",
    });

    console.log("Login berhasil:", page.url());
});
