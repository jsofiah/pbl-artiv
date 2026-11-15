// e2e/helpers/evidence.js
import path from "path";
import fs from "fs";

const EVIDENCE_DIR = path.resolve("e2e/evidence");

function ensureDir() {
    if (!fs.existsSync(EVIDENCE_DIR)) fs.mkdirSync(EVIDENCE_DIR, { recursive: true });
}

/**
 * Screenshot biasa (viewport-only).
 */
export async function snap(page, name, { fullPage = false } = {}) {
    ensureDir();
    const filePath = path.join(EVIDENCE_DIR, `${name}.png`);
    await page.screenshot({ path: filePath, fullPage });
    console.log(`📸 Evidence: ${name}.png`);
    return filePath;
}

/**
 * Screenshot dengan outline merah di elemen (Playwright Locator).
 * Lebih fleksibel dari CSS selector karena tidak peduli CSS specificity.
 */
export async function snapElementHighlight(
    page,
    name,
    locator,
    { fullPage = false } = {}
) {
    ensureDir();

    // Simpan style lama, lalu pasang outline merah + scroll ke tengah
    await locator.evaluate((el) => {
        el.setAttribute("data-ev-old-outline", el.style.outline || "");
        el.setAttribute(
            "data-ev-old-outline-offset",
            el.style.outlineOffset || ""
        );
        el.style.outline = "4px solid #ef4444";
        el.style.outlineOffset = "4px";
        el.scrollIntoView({ block: "center", behavior: "instant" });
    });
    await page.waitForTimeout(200);

    const filePath = path.join(EVIDENCE_DIR, `${name}.png`);
    await page.screenshot({ path: filePath, fullPage });

    // Restore supaya tidak mengganggu assertion/screenshot berikutnya
    await locator.evaluate((el) => {
        el.style.outline = el.getAttribute("data-ev-old-outline") || "";
        el.style.outlineOffset =
            el.getAttribute("data-ev-old-outline-offset") || "";
        el.removeAttribute("data-ev-old-outline");
        el.removeAttribute("data-ev-old-outline-offset");
    });

    console.log(`📸 Evidence (highlight): ${name}.png`);
    return filePath;
}

/**
 * Auto-evidence di afterEach (safety net kalau test fail di tengah).
 * Nama file: TC-FP-XXX_pass.png / TC-FP-XXX_fail.png
 */
export async function saveEvidence(page, testInfo) {
    const match = testInfo.title.match(/TC-[A-Z]+-\d+/);
    const tcId = match
        ? match[0]
        : testInfo.title.replace(/\s+/g, "_").slice(0, 50);
    const status = testInfo.status === "passed" ? "pass" : "fail";

    const filePath = await snap(page, `${tcId}_${status}`, {
        fullPage: true,
    });

    await testInfo.attach(`screenshot-${tcId}`, {
        path: filePath,
        contentType: "image/png",
    });

    return filePath;
}