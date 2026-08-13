const path = require("path");
const fs = require("fs");
const { chromium } = require(path.join(process.env.WGS_NODE_MODULES, "playwright"));

const base = "http://127.0.0.1:8099";
const routes = [
  "/", "/work/", "/culture/", "/projects/mira-silt/", "/projects/ninth-form/",
  "/projects/second-weather/", "/projects/sasha-mirev/", "/projects/quiet-signal/",
];

(async () => {
  const browser = await chromium.launch({
    headless: true,
    executablePath: process.env.WGS_CHROME_PATH || "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe",
  });
  const errors = [];
  try {
    for (const viewport of [{ width: 390, height: 844 }, { width: 1440, height: 900 }]) {
      const context = await browser.newContext({ viewport, reducedMotion: "reduce" });
      const page = await context.newPage();
      page.on("console", (message) => {
        if (message.type() === "error") errors.push(`${viewport.width}px console: ${message.text()}`);
      });
      page.on("pageerror", (error) => errors.push(`${viewport.width}px page: ${error.message}`));

      for (const route of routes) {
        const response = await page.goto(base + route, { waitUntil: "networkidle" });
        if (!response || !response.ok()) errors.push(`${route} returned ${response?.status()}`);
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth + 1);
        if (overflow) errors.push(`${route} overflows at ${viewport.width}px`);
      }

      await page.goto(base + "/work/", { waitUntil: "networkidle" });
      if (viewport.width >= 820) {
        await page.locator('[data-project-filter="culture"]').click();
        const visible = await page.locator("[data-project-card]:visible").count();
        if (visible !== 5) errors.push(`culture filter showed ${visible} projects`);
        await page.locator("[data-viewer-link]:visible").first().click();
        await page.locator(".case-viewer__close").waitFor({ state: "visible" });
        if (!page.url().includes("/projects/")) errors.push("viewer did not update canonical URL");
        await page.keyboard.press("Escape");
        await page.waitForURL(/\/work\/?/);
      } else {
        const href = await page.locator("[data-viewer-link]:visible").first().getAttribute("href");
        await page.locator("[data-viewer-link]:visible").first().click();
        await page.waitForURL(new RegExp(href.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")));
      }

      const shot = path.join(process.cwd(), "artifacts", `portfolio-work-${viewport.width}.png`);
      await page.goto(base + "/work/?filter=culture", { waitUntil: "networkidle" });
      await page.screenshot({ path: shot, fullPage: true });
      await page.goto(base + "/culture/", { waitUntil: "networkidle" });
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `portfolio-culture-${viewport.width}.png`), fullPage: true });
      await page.goto(base + "/projects/mira-silt/", { waitUntil: "networkidle" });
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `case-study-mira-silt-${viewport.width}.png`), fullPage: true });
      await context.close();
    }
  } finally {
    await browser.close();
  }

  if (errors.length) {
    console.error(errors.join("\n"));
    process.exit(1);
  }
  console.log(`Portfolio smoke test passed for ${routes.length} routes at mobile and desktop widths.`);
})();
