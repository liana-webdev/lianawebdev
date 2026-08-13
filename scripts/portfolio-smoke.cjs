const path = require("path");
const fs = require("fs");
const { chromium } = require(path.join(process.env.WGS_NODE_MODULES, "playwright"));

const base = "http://127.0.0.1:8099";
const routes = [
  "/", "/work/", "/culture/", "/projects/mira-silt/", "/projects/ninth-form/",
  "/projects/second-weather/", "/projects/sasha-mirev/", "/projects/quiet-signal/",
  "/projects/", "/projects/mira-silt/site/", "/projects/ninth-form/site/",
  "/snapshot/?business=bramelle-partners",
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

async function auditImageRatios(page, label, errors) {
  const stretched = await page.locator("img").evaluateAll((images) => images.flatMap((image) => {
    const box = image.getBoundingClientRect();
    if (box.width < 1 || box.height < 1 || image.naturalWidth < 1 || image.naturalHeight < 1) return [];

    const objectFit = getComputedStyle(image).objectFit;
    if (objectFit !== "fill") return [];

    const renderedRatio = box.width / box.height;
    const naturalRatio = image.naturalWidth / image.naturalHeight;
    const ratioDrift = Math.abs(Math.log(renderedRatio / naturalRatio));
    if (ratioDrift <= 0.025) return [];

    return [{
      source: new URL(image.currentSrc || image.src, location.href).pathname,
      rendered: `${Math.round(box.width)}x${Math.round(box.height)}`,
      natural: `${image.naturalWidth}x${image.naturalHeight}`,
    }];
  }));

  stretched.forEach((image) => errors.push(`${label} stretches ${image.source} from ${image.natural} to ${image.rendered}`));
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
      await context.route("https://www.googletagmanager.com/gtag/js**", (route) => route.fulfill({
        status: 200,
        contentType: "application/javascript",
        body: "window.__wgsGoogleTagTestStub=true;",
      }));
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
        await auditImageRatios(page, `Desktop case viewer at ${viewport.width}px`, errors);
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
      await auditImageRatios(page, `Work at ${viewport.width}px`, errors);
      if (viewport.width <= 560) {
        for (const slug of ["mira-silt", "ninth-form"]) {
          const currentSource = await page.locator(`[data-project-card][style*="--project-bg"] a[href*="${slug}"] img`).evaluate((image) => image.currentSrc);
          if (!currentSource.includes("cover-work-mobile-4x5.webp")) errors.push(`${slug} did not use its art-directed mobile cover`);
        }
      }
      await page.screenshot({ path: shot, fullPage: true });
      await page.goto(base + "/culture/", { waitUntil: "networkidle" });
      await settleMedia(page);
      await auditImageRatios(page, `Culture at ${viewport.width}px`, errors);
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `portfolio-culture-${viewport.width}.png`), fullPage: true });
      await page.goto(base + "/projects/mira-silt/", { waitUntil: "networkidle" });
      await settleMedia(page);
      await auditImageRatios(page, `Mira case study at ${viewport.width}px`, errors);
      const finalMedia = await page.locator('img[src*="/img/portfolio/mira-silt/"]').evaluateAll((images) => images.every((image) => image.complete && image.naturalWidth > 0));
      if (!finalMedia) errors.push(`Mira media did not fully decode at ${viewport.width}px`);
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `case-study-mira-silt-${viewport.width}.png`), fullPage: true });
      await page.goto(base + "/projects/ninth-form/", { waitUntil: "networkidle" });
      await settleMedia(page);
      await auditImageRatios(page, `Ninth Form case study at ${viewport.width}px`, errors);
      const ninthMedia = await page.locator('img[src*="/img/portfolio/ninth-form/"]').evaluateAll((images) => images.every((image) => image.complete && image.naturalWidth > 0));
      if (!ninthMedia) errors.push(`Ninth Form media did not fully decode at ${viewport.width}px`);
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `case-study-ninth-form-${viewport.width}.png`), fullPage: true });
      await page.goto(base + "/projects/mira-silt/site/", { waitUntil: "networkidle" });
      await settleMedia(page);
      await auditImageRatios(page, `Mira site at ${viewport.width}px`, errors);
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `lab-mira-silt-${viewport.width}.png`), fullPage: true });
      await page.locator('[data-era="tour"]').click();
      if ((await page.locator("[data-era-label]").textContent()) !== "Salt Memory — Live") errors.push("Mira campaign switcher failed");
      await page.locator("[data-player]").click();
      if ((await page.locator("[data-player]").getAttribute("aria-label")) !== "Pause album preview") errors.push("Mira player failed");
      await page.locator('[data-open-dialog="mira-epk"]:visible').first().click();
      if (!(await page.locator("#mira-epk").isVisible())) errors.push("Mira EPK failed to open");
      await auditImageRatios(page, `Mira EPK at ${viewport.width}px`, errors);
      const epkColumns = await page.locator(".epk-grid").evaluate((element) => getComputedStyle(element).gridTemplateColumns.split(/\s+/).length);
      const expectedEpkColumns = viewport.width <= 900 ? 1 : 2;
      if (epkColumns !== expectedEpkColumns) errors.push(`Mira EPK uses ${epkColumns} columns instead of ${expectedEpkColumns} at ${viewport.width}px`);
      await page.keyboard.press("Escape");
      if (await page.locator("#mira-epk").isVisible()) errors.push("Mira EPK failed to close with Escape");
      await page.goto(base + "/projects/ninth-form/site/", { waitUntil: "networkidle" });
      await settleMedia(page);
      await auditImageRatios(page, `Ninth Form World at ${viewport.width}px`, errors);
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `lab-ninth-form-${viewport.width}.png`), fullPage: true });
      await page.locator('[data-mode="shop"]').click();
      if (!(await page.locator("[data-shop-view]").isVisible())) errors.push("Ninth Form Shop mode failed");
      await auditImageRatios(page, `Ninth Form Shop at ${viewport.width}px`, errors);
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
      const pearlCanvases = await page.locator("#top > .pearl-sand-canvas").count();
      if (pearlCanvases !== 1) {
        errors.push(`Homepage has ${pearlCanvases} Pearl Signal canvases instead of 1 at ${viewport.width}px`);
      } else {
        const pearlPointerEvents = await page.locator("#top > .pearl-sand-canvas").evaluate((canvas) => getComputedStyle(canvas).pointerEvents);
        if (pearlPointerEvents !== "none") errors.push(`Pearl Signal canvas intercepts pointer events at ${viewport.width}px`);
      }
      const founderPortraits = await page.locator(".about-portrait img").count();
      if (founderPortraits !== 1) errors.push(`Homepage has ${founderPortraits} founder portraits instead of 1 at ${viewport.width}px`);
      const founderSource = await page.locator(".about-portrait img").getAttribute("src");
      if (!founderSource?.includes("wgs-liana-founder-red-signal-closeup-looking-right-sideview.jpg")) errors.push(`Homepage does not use the right-facing founder portrait at ${viewport.width}px`);
      const founderLabel = await page.locator(".about-portrait figcaption strong").textContent();
      if (founderLabel?.trim() !== "Liana / Founder / Creative Director") errors.push(`Homepage founder portrait label is unclear at ${viewport.width}px`);
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `homepage-plain-language-${viewport.width}.png`), fullPage: true });
      await context.close();
    }

    const fallbackContext = await browser.newContext({ viewport: { width: 768, height: 1024 }, reducedMotion: "reduce" });
    await fallbackContext.route("https://www.googletagmanager.com/gtag/js**", (route) => route.fulfill({
      status: 200,
      contentType: "application/javascript",
      body: "window.__wgsGoogleTagTestStub=true;",
    }));
    await fallbackContext.addInitScript(() => {
      const getContext = HTMLCanvasElement.prototype.getContext;
      HTMLCanvasElement.prototype.getContext = function patchedGetContext(type, ...args) {
        if (type === "webgl" || type === "experimental-webgl") return null;
        return getContext.call(this, type, ...args);
      };
    });
    const fallbackPage = await fallbackContext.newPage();
    await fallbackPage.goto(base + "/", { waitUntil: "networkidle" });
    const fallbackCanvases = await fallbackPage.locator("#top > .pearl-sand-canvas[data-fallback=\"true\"]").count();
    if (fallbackCanvases !== 1) errors.push(`Pearl Signal CSS fallback rendered ${fallbackCanvases} canvases instead of 1`);
    const fallbackOverflow = await fallbackPage.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth + 1);
    if (fallbackOverflow) errors.push("Pearl Signal CSS fallback causes horizontal overflow");
    await fallbackContext.close();
  } finally {
    await browser.close();
  }

  if (errors.length) {
    console.error(errors.join("\n"));
    process.exit(1);
  }
  console.log(`Portfolio smoke test passed for ${routes.length} routes at phone, tablet and desktop widths.`);
})();
