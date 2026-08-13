const path = require("path");
const fs = require("fs");
const { chromium } = require(path.join(process.env.WGS_NODE_MODULES, "playwright"));

const base = "http://127.0.0.1:8099";
const routes = [
  "/", "/work/", "/culture/", "/projects/mira-silt/", "/projects/ninth-form/",
  "/projects/second-weather/", "/projects/sasha-mirev/", "/projects/quiet-signal/",
  "/projects/", "/projects/mira-silt/site/", "/projects/ninth-form/site/",
];

async function settleMedia(page) {
  await page.evaluate(async () => {
    const step = Math.max(window.innerHeight * 0.8, 500);
    for (let y = 0; y < document.documentElement.scrollHeight; y += step) {
      window.scrollTo(0, y);
      await new Promise((resolve) => setTimeout(resolve, 35));
    }
    window.scrollTo(0, 0);
    await Promise.all([...document.images].filter((image) => image.currentSrc).map((image) => image.decode?.().catch(() => {})));
  });
  await page.waitForTimeout(100);
}

(async () => {
  const browser = await chromium.launch({
    headless: true,
    executablePath: process.env.WGS_CHROME_PATH || "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe",
  });
  const errors = [];
  try {
    for (const viewport of [{ width: 390, height: 844 }, { width: 768, height: 1024 }, { width: 1440, height: 900 }]) {
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
        await page.locator('[data-project-filter="all"]').click();
        const visible = await page.locator("[data-project-card]:visible").count();
        if (visible !== 5) errors.push(`all-projects filter showed ${visible} projects`);
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
      await page.goto(base + "/work/", { waitUntil: "networkidle" });
      await settleMedia(page);
      if (viewport.width <= 560) {
        for (const slug of ["mira-silt", "ninth-form"]) {
          const currentSource = await page.locator(`[data-project-card][style*="--project-bg"] a[href*="${slug}"] img`).evaluate((image) => image.currentSrc);
          if (!currentSource.includes("cover-work-mobile-4x5.webp")) errors.push(`${slug} did not use its art-directed mobile cover`);
        }
      }
      await page.screenshot({ path: shot, fullPage: true });
      await page.goto(base + "/culture/", { waitUntil: "networkidle" });
      await settleMedia(page);
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `portfolio-culture-${viewport.width}.png`), fullPage: true });
      await page.goto(base + "/projects/mira-silt/", { waitUntil: "networkidle" });
      await settleMedia(page);
      const finalMedia = await page.locator('img[src*="/img/portfolio/mira-silt/"]').evaluateAll((images) => images.every((image) => image.complete && image.naturalWidth > 0));
      if (!finalMedia) errors.push(`Mira media did not fully decode at ${viewport.width}px`);
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `case-study-mira-silt-${viewport.width}.png`), fullPage: true });
      await page.goto(base + "/projects/ninth-form/", { waitUntil: "networkidle" });
      await settleMedia(page);
      const ninthMedia = await page.locator('img[src*="/img/portfolio/ninth-form/"]').evaluateAll((images) => images.every((image) => image.complete && image.naturalWidth > 0));
      if (!ninthMedia) errors.push(`Ninth Form media did not fully decode at ${viewport.width}px`);
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `case-study-ninth-form-${viewport.width}.png`), fullPage: true });
      await page.goto(base + "/projects/mira-silt/site/", { waitUntil: "networkidle" });
      await settleMedia(page);
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `lab-mira-silt-${viewport.width}.png`), fullPage: true });
      await page.locator('[data-era="tour"]').click();
      if ((await page.locator("[data-era-label]").textContent()) !== "Salt Memory — Live") errors.push("Mira campaign switcher failed");
      await page.locator("[data-player]").click();
      if ((await page.locator("[data-player]").getAttribute("aria-label")) !== "Pause album preview") errors.push("Mira player failed");
      await page.locator('[data-open-dialog="mira-epk"]:visible').first().click();
      if (!(await page.locator("#mira-epk").isVisible())) errors.push("Mira EPK failed to open");
      await page.keyboard.press("Escape");
      if (await page.locator("#mira-epk").isVisible()) errors.push("Mira EPK failed to close with Escape");
      await page.goto(base + "/projects/ninth-form/site/", { waitUntil: "networkidle" });
      await settleMedia(page);
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `lab-ninth-form-${viewport.width}.png`), fullPage: true });
      await page.locator('[data-mode="shop"]').click();
      if (!(await page.locator("[data-shop-view]").isVisible())) errors.push("Ninth Form Shop mode failed");
      const shopOverflow = await page.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth + 1);
      if (shopOverflow) errors.push(`Ninth Form Shop mode overflows at ${viewport.width}px`);
      await page.locator('[data-size="S"]').click();
      await page.locator("[data-add-bag]").click();
      if (!(await page.locator("#bag-drawer").isVisible())) errors.push("Ninth Form bag failed to open");
      if ((await page.locator("[data-bag-count]").textContent()) !== "1") errors.push("Ninth Form bag count failed");
      await page.locator("[data-qty-plus]").click();
      if ((await page.locator("[data-bag-count]").textContent()) !== "2") errors.push("Ninth Form bag quantity failed");
      await page.keyboard.press("Escape");
      if (await page.locator("#bag-drawer").isVisible()) errors.push("Ninth Form bag failed to close with Escape");
      await page.locator("[data-open-drawer=\"fit-drawer\"]").click();
      const expectedFitSurface = viewport.width <= 600 ? ".measurement-cards" : ".measurements";
      if (!(await page.locator(expectedFitSurface).isVisible())) errors.push(`Ninth Form fit guide is not mobile-safe at ${viewport.width}px`);
      await page.keyboard.press("Escape");
      await page.goto(base + "/", { waitUntil: "networkidle" });
      await settleMedia(page);
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `homepage-plain-language-${viewport.width}.png`), fullPage: true });
      await context.close();
    }
  } finally {
    await browser.close();
  }

  if (errors.length) {
    console.error(errors.join("\n"));
    process.exit(1);
  }
  console.log(`Portfolio smoke test passed for ${routes.length} routes at phone, tablet and desktop widths.`);
})();
