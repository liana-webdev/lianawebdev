(() => {
  "use strict";

  if (window.__wgsAnalyticsEventsBound) return;
  window.__wgsAnalyticsEventsBound = true;

  const pagePath = window.location.pathname;
  const schedulingHosts = new Set([
    "cal.com",
    "calendly.com",
    "calendar.google.com",
    "tidycal.com",
  ]);

  const cleanText = (value) => String(value || "").replace(/\s+/g, " ").trim().slice(0, 100);

  const linkLocation = (link) => {
    const labelled = link.closest("[data-analytics-location]");
    if (labelled) return cleanText(labelled.dataset.analyticsLocation) || "page";
    const region = link.closest("header, footer, section, main, nav");
    if (!region) return "page";
    if (region.id) return region.id;
    if (region.matches("header")) return "header";
    if (region.matches("footer")) return "footer";
    if (region.matches("nav")) return "navigation";
    return cleanText(region.classList[0]) || region.tagName.toLowerCase();
  };

  const send = (eventName, parameters) => {
    if (typeof window.gtag !== "function") return;
    window.gtag("event", eventName, parameters);
  };

  const caseStudySlug = (url) => {
    if (url.origin !== window.location.origin) return "";
    const match = url.pathname.match(/^\/projects\/([a-z0-9-]+)\/?$/i);
    return match ? match[1].toLowerCase() : "";
  };

  document.addEventListener("click", (event) => {
    const link = event.target.closest?.("a[href]");
    if (!link) return;

    let url;
    try {
      url = new URL(link.href, window.location.href);
    } catch {
      return;
    }

    const location = linkLocation(link);
    const common = { link_location: location, page_path: pagePath };

    if (url.protocol === "mailto:") {
      send("click_email", common);
      return;
    }

    if (url.protocol === "tel:") {
      send("click_phone", common);
      return;
    }

    const isBooking = link.matches("[data-analytics-book-call]") || schedulingHosts.has(url.hostname.replace(/^www\./i, ""));
    if (isBooking) {
      send("book_call", { ...common, cta_text: cleanText(link.textContent) });
      return;
    }

    if (url.origin === window.location.origin && url.pathname === "/" && url.hash === "#contact") {
      send("contact_cta_click", { ...common, cta_text: cleanText(link.textContent) });
      return;
    }

    const slug = caseStudySlug(url);
    if (slug) {
      send("view_case_study", { ...common, case_study_slug: slug });
    }
  });

  const leadSuccess = document.querySelector("[data-wgs-lead-success]");
  if (leadSuccess) {
    const receipt = cleanText(leadSuccess.dataset.wgsLeadSuccess);
    const storageKey = receipt ? `wgs_lead_${receipt}` : "";
    let alreadySent = false;

    if (storageKey) {
      try {
        alreadySent = window.sessionStorage.getItem(storageKey) === "1";
      } catch {
        alreadySent = leadSuccess.dataset.analyticsSent === "true";
      }
    }

    if (!alreadySent && receipt) {
      leadSuccess.dataset.analyticsSent = "true";
      try {
        window.sessionStorage.setItem(storageKey, "1");
      } catch {}
      send("generate_lead", { form_type: "project_enquiry", page_path: pagePath });
    }
  }
})();
