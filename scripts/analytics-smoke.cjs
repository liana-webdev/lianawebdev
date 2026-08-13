const path = require("path");
const { chromium } = require(path.join(process.env.WGS_NODE_MODULES, "playwright"));

const base = "http://127.0.0.1:8099";
const measurementId = "G-LPBCC632PW";
const routes = [
  "/",
  "/work/",
  "/culture/",
  "/projects/mira-silt/",
  "/projects/ninth-form/",
  "/projects/second-weather/",
  "/projects/sasha-mirev/",
  "/projects/quiet-signal/",
  "/projects/mira-silt/site/",
  "/projects/ninth-form/site/",
];

const fail = (message) => {
  throw new Error(message);
};

async function analyticsEvents(page, eventName) {
  return page.evaluate((name) => window.dataLayer
    .map((entry) => Array.from(entry))
    .filter((entry) => entry[0] === "event" && entry[1] === name)
    .map((entry) => entry[2]), eventName);
}

(async () => {
  const browser = await chromium.launch({
    headless: true,
    executablePath: process.env.WGS_CHROME_PATH || "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe",
  });

  try {
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    await context.route("https://www.googletagmanager.com/gtag/js**", (route) => route.fulfill({
      status: 200,
      contentType: "application/javascript",
      body: "window.__wgsGoogleTagLoaded=(window.__wgsGoogleTagLoaded||0)+1;",
    }));
    const page = await context.newPage();

    for (const route of routes) {
      await page.goto(base + route, { waitUntil: "networkidle" });
      const googleTags = await page.locator(`script[src="https://www.googletagmanager.com/gtag/js?id=${measurementId}"]`).count();
      if (googleTags !== 1) fail(`${route} contains ${googleTags} Google tags instead of one`);
      const eventScripts = await page.locator('script[src^="/assets/analytics-events.js?"]').count();
      if (eventScripts !== 1) fail(`${route} contains ${eventScripts} WGS analytics event scripts instead of one`);
      const configs = await page.evaluate((id) => window.dataLayer
        .map((entry) => Array.from(entry))
        .filter((entry) => entry[0] === "config" && entry[1] === id).length, measurementId);
      if (configs !== 1) fail(`${route} queues ${configs} GA4 configs instead of one`);
      const disabledLocally = await page.evaluate((id) => window[`ga-disable-${id}`], measurementId);
      if (disabledLocally !== true) fail(`${route} does not disable GA4 outside production`);
    }

    const utm = "?utm_source=cold_email&utm_medium=email&utm_campaign=ga4-test&utm_content=liana-test";
    await page.goto(base + "/" + utm, { waitUntil: "networkidle" });
    if (new URL(page.url()).search !== utm) fail("UTM parameters were not preserved through the page load");

    await page.goto(base + "/?status=sent", { waitUntil: "networkidle" });
    if ((await analyticsEvents(page, "generate_lead")).length !== 0) fail("generate_lead fired for an unconfirmed success query");

    await page.goto(base + "/" + utm, { waitUntil: "networkidle" });

    await page.evaluate(() => {
      document.addEventListener("click", (event) => {
        const link = event.target.closest?.("a");
        if (
          link?.protocol === "mailto:" ||
          link?.protocol === "tel:" ||
          link?.pathname.startsWith("/projects/")
        ) event.preventDefault();
      }, true);
    });

    await page.locator('a[href^="mailto:"]').dispatchEvent("click", { button: 0 });
    await page.locator('a[href^="tel:"]').dispatchEvent("click", { button: 0 });
    await page.locator('.header-cta[href="#contact"]').click();
    if (new URL(page.url()).hash !== "#contact") fail("Contact CTA tracking blocked normal anchor navigation");
    await page.locator('.home-featured-project__actions a[href^="/projects/"]').first().dispatchEvent("click", { button: 0 });
    await page.evaluate(() => {
      const booking = document.createElement("a");
      booking.href = "https://cal.com/web-girl-studio/discovery";
      booking.dataset.analyticsBookCall = "";
      booking.textContent = "Book a discovery call";
      document.body.append(booking);
      booking.addEventListener("click", (event) => event.preventDefault());
      booking.click();
    });

    const expectedEvents = ["click_email", "click_phone", "contact_cta_click", "view_case_study", "book_call"];
    for (const eventName of expectedEvents) {
      const events = await analyticsEvents(page, eventName);
      if (events.length !== 1) fail(`${eventName} fired ${events.length} times instead of once`);
    }

    const customEventPayloads = await page.evaluate(() => window.dataLayer
      .map((entry) => Array.from(entry))
      .filter((entry) => entry[0] === "event")
      .map((entry) => entry[2]));
    const allowedParameters = new Set(["link_location", "cta_text", "form_type", "case_study_slug", "page_path"]);
    for (const payload of customEventPayloads) {
      for (const parameter of Object.keys(payload)) {
        if (!allowedParameters.has(parameter)) fail(`Unexpected analytics parameter: ${parameter}`);
      }
    }

    const leadContext = await browser.newContext({ viewport: { width: 768, height: 1024 } });
    await leadContext.route("https://www.googletagmanager.com/gtag/js**", (route) => route.fulfill({
      status: 200,
      contentType: "application/javascript",
      body: "window.__wgsGoogleTagLoaded=1;",
    }));
    const leadPage = await leadContext.newPage();
    await leadPage.goto(base + "/", { waitUntil: "networkidle" });
    await leadPage.fill('[name="name"]', "Analytics Test Lead");
    await leadPage.fill('[name="email"]', "analytics-test@example.com");
    await leadPage.fill('[name="business"]', "Example Test Business");
    await leadPage.selectOption('[name="project_type"]', { label: "Website Diagnosis" });
    await leadPage.selectOption('[name="budget"]', { label: "$400 audit" });
    await leadPage.fill('[name="message"]', "Automated analytics success-state verification only.");
    await Promise.all([
      leadPage.waitForURL(/status=sent&lead=[a-f0-9]+#contact$/),
      leadPage.locator(".enquiry-form").evaluate((form) => form.requestSubmit()),
    ]);

    const leadEvents = await analyticsEvents(leadPage, "generate_lead");
    if (leadEvents.length !== 1) fail(`generate_lead fired ${leadEvents.length} times after confirmed success`);
    const leadPayload = JSON.stringify(leadEvents[0]);
    for (const personalValue of ["Analytics Test Lead", "analytics-test@example.com", "Example Test Business", "Automated analytics success-state verification only."]) {
      if (leadPayload.includes(personalValue)) fail("Personal form data appeared in generate_lead");
    }
    if (Object.keys(leadEvents[0]).sort().join(",") !== "form_type,page_path") fail("generate_lead contains unexpected parameters");

    await leadPage.reload({ waitUntil: "networkidle" });
    if ((await analyticsEvents(leadPage, "generate_lead")).length !== 0) fail("generate_lead fired again after refresh");
    await leadContext.close();
    await context.close();
  } finally {
    await browser.close();
  }

  console.log(`GA4 smoke test passed for ${routes.length} rendered routes and all WGS custom events.`);
})().catch((error) => {
  console.error(error.stack || error.message);
  process.exit(1);
});
