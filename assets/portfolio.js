(() => {
  "use strict";

  const filters = [...document.querySelectorAll("[data-project-filter]")];
  const cards = [...document.querySelectorAll("[data-project-card]")];
  const count = document.querySelector("#project-count");

  if (filters.length && cards.length) {
    const validFilters = new Set(filters.map((button) => button.dataset.projectFilter));
    const params = new URLSearchParams(location.search);
    const requested = params.get("filter");
    const initial = requested && validFilters.has(requested) ? requested : "all";

    const applyFilter = (key, updateUrl = true) => {
      let visible = 0;
      filters.forEach((button) => button.setAttribute("aria-pressed", String(button.dataset.projectFilter === key)));
      cards.forEach((card) => {
        const tags = (card.dataset.tags || "").split(/\s+/);
        const show = tags.includes(key);
        card.hidden = !show;
        if (show) visible += 1;
      });
      if (count) count.textContent = `${visible} project${visible === 1 ? "" : "s"} shown`;
      if (updateUrl) {
        const next = new URL(location.href);
        if (key === "all") next.searchParams.delete("filter");
        else next.searchParams.set("filter", key);
        history.replaceState({ ...(history.state || {}), gallery: true, filter: key }, "", next);
      }
    };

    filters.forEach((button) => button.addEventListener("click", () => applyFilter(button.dataset.projectFilter)));
    applyFilter(initial, false);
    history.replaceState({ ...(history.state || {}), gallery: true, filter: initial }, "", location.href);
  }

  const viewer = document.querySelector("#case-study-viewer");
  const galleryMain = document.querySelector("main");
  const pageChrome = [document.querySelector(".site-header"), document.querySelector(".portfolio-footer")].filter(Boolean);
  let viewerTrigger = null;
  let viewerOpen = false;
  let viewerController = null;

  const setBackgroundInert = (inert) => {
    [galleryMain, ...pageChrome].filter(Boolean).forEach((element) => {
      element.inert = inert;
      if (inert) element.setAttribute("aria-hidden", "true");
      else element.removeAttribute("aria-hidden");
    });
  };

  const closeViewer = ({ restoreFocus = true } = {}) => {
    if (!viewer || !viewerOpen) return;
    viewerOpen = false;
    viewerController?.abort();
    viewerController = null;
    viewer.hidden = true;
    viewer.innerHTML = "";
    viewer.removeAttribute("style");
    document.body.classList.remove("has-case-viewer");
    document.body.style.removeProperty("--scrollbar-width");
    setBackgroundInert(false);
    if (restoreFocus) viewerTrigger?.focus({ preventScroll: true });
  };

  const trapViewerFocus = (event) => {
    if (!viewerOpen || event.key !== "Tab" || !viewer) return;
    const focusable = [...viewer.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])')].filter((element) => !element.hidden);
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
  };

  const openViewer = async (link) => {
    if (!viewer || matchMedia("(max-width: 819px)").matches) return false;
    viewerTrigger = link;
    viewerOpen = true;
    viewer.hidden = false;
    viewer.innerHTML = '<div class="case-viewer__loading" role="status">Loading case study…</div>';
    document.body.style.setProperty("--scrollbar-width", `${innerWidth - document.documentElement.clientWidth}px`);
    document.body.classList.add("has-case-viewer");
    setBackgroundInert(true);

    viewerController = new AbortController();
    try {
      const response = await fetch(link.href, { signal: viewerController.signal, headers: { "X-WGS-Viewer": "1" } });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const documentCopy = new DOMParser().parseFromString(await response.text(), "text/html");
      const projectPage = documentCopy.querySelector("[data-project-page]");
      if (!projectPage) throw new Error("Case study markup missing");
      projectPage.removeAttribute("id");
      projectPage.querySelectorAll(".reveal").forEach((element) => element.classList.add("is-visible"));
      const bodyStyle = documentCopy.body.getAttribute("style") || "";
      viewer.setAttribute("style", bodyStyle);
      viewer.innerHTML = '<div class="case-viewer__bar"><span class="case-viewer__label">Case study / Web Girl Studio</span><button class="case-viewer__close" type="button" aria-label="Close case study">×</button></div><div class="case-viewer__document" role="dialog" aria-modal="true" aria-label="Case study"></div>';
      viewer.querySelector(".case-viewer__document").append(projectPage);
      viewer.querySelector(".case-viewer__close").addEventListener("click", () => history.back());
      viewer.scrollTop = 0;
      viewer.querySelector(".case-viewer__close").focus();
    } catch (error) {
      if (error.name === "AbortError") return true;
      location.assign(link.href);
    }
    return true;
  };

  document.querySelectorAll("[data-viewer-link]").forEach((link) => {
    link.addEventListener("click", async (event) => {
      if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || matchMedia("(max-width: 819px)").matches) return;
      event.preventDefault();
      const opened = await openViewer(link);
      if (opened && viewerOpen) history.pushState({ caseViewer: true, href: link.href }, "", link.href);
    });
  });

  addEventListener("popstate", () => {
    if (viewerOpen) closeViewer();
  });

  document.addEventListener("keydown", (event) => {
    if (viewerOpen && event.key === "Escape") {
      event.preventDefault();
      history.back();
      return;
    }
    trapViewerFocus(event);
  });
})();
