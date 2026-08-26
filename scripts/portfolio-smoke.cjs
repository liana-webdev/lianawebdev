const path = require("path");
const fs = require("fs");
const { chromium } = require(path.join(process.env.WGS_NODE_MODULES, "playwright"));

const base = "http://127.0.0.1:8099";
const routes = [
  "/", "/work/", "/culture/", "/projects/fortepiano-academy/", "/projects/",
];
const hiddenRoutes = [
  "/projects/mira-silt/", "/projects/ninth-form/",
  "/projects/mira-silt/site/", "/projects/ninth-form/site/",
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
        const visible = await page.locator("[data-project-card]:visible").count();
        if (visible !== 1) errors.push(`Work showed ${visible} public projects instead of 1`);
        if (await page.locator("[data-project-filter]").count()) errors.push("Work still exposes portfolio filters");
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
      await page.screenshot({ path: shot, fullPage: true });
      await page.goto(base + "/culture/", { waitUntil: "networkidle" });
      await settleMedia(page);
      await auditImageRatios(page, `Culture at ${viewport.width}px`, errors);
      await page.screenshot({ path: path.join(process.cwd(), "artifacts", `portfolio-culture-${viewport.width}.png`), fullPage: true });
      for (const route of hiddenRoutes) {
        const response = await page.goto(base + route, { waitUntil: "networkidle" });
        if (response?.status() !== 404) errors.push(`${route} returned ${response?.status()} instead of 404`);
      }
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
