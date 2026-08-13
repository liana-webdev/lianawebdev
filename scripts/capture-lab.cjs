const path = require("path");
const { chromium } = require(path.join(process.env.WGS_NODE_MODULES, "playwright"));
const base = "http://127.0.0.1:8099";
const chrome = process.env.WGS_CHROME_PATH || "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe";

async function capture(project, viewport, filename, prepare = async () => {}) {
  const browser = await chromium.launch({ headless: true, executablePath: chrome });
  const context = await browser.newContext({ viewport, deviceScaleFactor: 2, reducedMotion: "reduce" });
  const page = await context.newPage();
  await page.goto(`${base}/projects/${project}/site/`, { waitUntil: "networkidle" });
  await page.evaluate(() => Promise.all([...document.images].filter((image) => image.loading !== "lazy").map((image) => image.decode?.().catch(() => {}))));
  await prepare(page);
  await page.evaluate(() => document.querySelector("[data-lab-status]")?.classList.remove("show"));
  await page.waitForTimeout(180);
  const target = path.join(process.cwd(), "img", "portfolio", project, filename);
  await page.screenshot({ path: target, type: "webp", quality: 92 });
  await context.close(); await browser.close();
}

(async () => {
  const desktop = { width: 1440, height: 900 }, mobile = { width: 390, height: 844 };
  await capture("mira-silt", desktop, "screen-desktop-01-8x5.webp");
  await capture("mira-silt", desktop, "screen-desktop-02-8x5.webp", async (page) => page.locator("#listen").evaluate((element) => scrollTo(0, element.offsetTop)));
  await capture("mira-silt", mobile, "screen-mobile-01.webp");
  await capture("mira-silt", mobile, "screen-mobile-02.webp", async (page) => page.locator("#live").evaluate((element) => scrollTo(0, element.offsetTop)));
  await capture("ninth-form", desktop, "screen-desktop-01-8x5.webp");
  await capture("ninth-form", desktop, "screen-desktop-02-8x5.webp", async (page) => page.locator('[data-mode="shop"]').click());
  await capture("ninth-form", mobile, "screen-mobile-01.webp");
  await capture("ninth-form", mobile, "screen-mobile-02.webp", async (page) => page.locator('[data-mode="shop"]').click());
  console.log("Captured 8 real Culture Lab interface views at native 2x output.");
})();
