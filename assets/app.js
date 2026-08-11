(() => {
  "use strict";

  document.documentElement.classList.add("js");

  const header = document.querySelector(".site-header");
  const menu = document.querySelector(".site-nav");
  const menuToggle = document.querySelector(".menu-toggle");
  const reveals = [...document.querySelectorAll(".reveal")];
  const parallax = [...document.querySelectorAll("[data-parallax]")];

  menuToggle?.addEventListener("click", () => {
    const open = menu?.classList.toggle("is-open") ?? false;
    menuToggle.setAttribute("aria-expanded", String(open));
  });

  menu?.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      menu.classList.remove("is-open");
      menuToggle?.setAttribute("aria-expanded", "false");
    });
  });

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: "0px 0px -7% 0px" });
  reveals.forEach((element) => observer.observe(element));

  let pageScrollMax = 1;
  let ticking = false;
  const updateScroll = () => {
    pageScrollMax = Math.max(document.documentElement.scrollHeight - innerHeight, 1);
    document.documentElement.style.setProperty("--page-progress", String(scrollY / pageScrollMax));
    header?.classList.toggle("is-scrolled", scrollY > 24);
    parallax.forEach((element) => {
      const speed = Number(element.dataset.parallax || 0.08);
      const rect = element.getBoundingClientRect();
      const offset = (rect.top + rect.height * 0.5 - innerHeight * 0.5) * speed;
      element.style.setProperty("--parallax-y", `${offset.toFixed(2)}px`);
    });
    ticking = false;
  };
  addEventListener("scroll", () => {
    if (!ticking) {
      ticking = true;
      requestAnimationFrame(updateScroll);
    }
  }, { passive: true });
  updateScroll();

  const diagnosticContent = {
    none: {
      title: "Build the foundation.",
      body: "You need a clear offer, trust structure and a direct path from discovery to enquiry—without adding complexity before it earns its place.",
      route: "Foundation or Growth System",
    },
    weak: {
      title: "Find the leak before rebuilding.",
      body: "An audit separates surface-level visual issues from deeper problems in messaging, trust, mobile experience and conversion flow.",
      route: "$400 Website Diagnosis → targeted rebuild",
    },
    quiet: {
      title: "The issue may not be the design.",
      body: "A good-looking site can still fail when the offer is vague, proof arrives too late or the visitor has no compelling reason to act now.",
      route: "Conversion + messaging diagnosis",
    },
  };

  const diagnosticButtons = [...document.querySelectorAll("[data-diagnostic]")];
  const diagnosticTitle = document.querySelector("[data-diagnostic-title]");
  const diagnosticBody = document.querySelector("[data-diagnostic-body]");
  const diagnosticRoute = document.querySelector("[data-diagnostic-route]");
  diagnosticButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const key = button.dataset.diagnostic;
      const content = diagnosticContent[key];
      if (!content) return;
      diagnosticButtons.forEach((entry) => {
        const selected = entry === button;
        entry.classList.toggle("is-active", selected);
        entry.setAttribute("aria-selected", String(selected));
      });
      diagnosticTitle.textContent = content.title;
      diagnosticBody.textContent = content.body;
      diagnosticRoute.textContent = content.route;
    });
  });

  document.querySelectorAll(".faq-item").forEach((item) => {
    const button = item.querySelector("button");
    const indicator = button?.querySelector("i");
    button?.addEventListener("click", () => {
      const open = item.classList.toggle("is-open");
      button.setAttribute("aria-expanded", String(open));
      if (indicator) indicator.textContent = open ? "−" : "+";
    });
  });

  const reduceMotion = matchMedia("(prefers-reduced-motion: reduce)");
  const finePointer = matchMedia("(hover: hover) and (pointer: fine)");
  const iridescentCard = document.querySelector("[data-iridescent-card]");

  if (iridescentCard) {
    const revealFoil = (entryObserver) => {
      if (!reduceMotion.matches) iridescentCard.classList.add("is-foil-revealed");
      entryObserver?.unobserve(iridescentCard);
    };

    if ("IntersectionObserver" in window) {
      const foilObserver = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) revealFoil(foilObserver);
      }, { threshold: 0.22 });
      foilObserver.observe(iridescentCard);
    } else {
      revealFoil();
    }

    if (finePointer.matches && !reduceMotion.matches) {
      let bounds = null;
      let materialFrame = 0;
      let pointerInside = false;
      const current = { x: 0.5, y: 0.5, rotateX: 0, rotateY: 0 };
      const target = { x: 0.5, y: 0.5, rotateX: 0, rotateY: 0 };

      const queueMaterialFrame = () => {
        if (!materialFrame) materialFrame = requestAnimationFrame(updateMaterial);
      };

      const updateMaterial = () => {
        materialFrame = 0;
        const ease = pointerInside ? 0.12 : 0.09;
        current.x += (target.x - current.x) * ease;
        current.y += (target.y - current.y) * ease;
        current.rotateX += (target.rotateX - current.rotateX) * ease;
        current.rotateY += (target.rotateY - current.rotateY) * ease;

        iridescentCard.style.setProperty("--ir-x", `${(current.x * 100).toFixed(2)}%`);
        iridescentCard.style.setProperty("--ir-y", `${(current.y * 100).toFixed(2)}%`);
        iridescentCard.style.setProperty("--ir-rotate-x", `${current.rotateX.toFixed(3)}deg`);
        iridescentCard.style.setProperty("--ir-rotate-y", `${current.rotateY.toFixed(3)}deg`);
        iridescentCard.style.setProperty("--ir-shift-x", `${((current.x - 0.5) * 18).toFixed(2)}px`);
        iridescentCard.style.setProperty("--ir-shift-y", `${((current.y - 0.5) * 12).toFixed(2)}px`);

        const moving =
          Math.abs(target.x - current.x) > 0.002 ||
          Math.abs(target.y - current.y) > 0.002 ||
          Math.abs(target.rotateX - current.rotateX) > 0.01 ||
          Math.abs(target.rotateY - current.rotateY) > 0.01;

        if (moving) {
          queueMaterialFrame();
        } else if (!pointerInside) {
          iridescentCard.classList.remove("is-iridescent-active");
        }
      };

      const updateMaterialTarget = (event) => {
        if (!bounds) bounds = iridescentCard.getBoundingClientRect();
        const x = Math.min(1, Math.max(0, (event.clientX - bounds.left) / bounds.width));
        const y = Math.min(1, Math.max(0, (event.clientY - bounds.top) / bounds.height));
        target.x = x;
        target.y = y;
        target.rotateX = (0.5 - y) * 2.2;
        target.rotateY = (x - 0.5) * 2.2;
        queueMaterialFrame();
      };

      iridescentCard.addEventListener("pointerenter", (event) => {
        bounds = iridescentCard.getBoundingClientRect();
        pointerInside = true;
        iridescentCard.classList.add("is-iridescent-active");
        updateMaterialTarget(event);
      }, { passive: true });

      iridescentCard.addEventListener("pointermove", updateMaterialTarget, { passive: true });

      iridescentCard.addEventListener("pointerleave", () => {
        pointerInside = false;
        target.x = 0.5;
        target.y = 0.5;
        target.rotateX = 0;
        target.rotateY = 0;
        queueMaterialFrame();
      }, { passive: true });

      addEventListener("resize", () => {
        bounds = null;
      }, { passive: true });
    }
  }

  const clarityEngine = document.querySelector("[data-clarity-engine]");
  const clarityStage = clarityEngine?.querySelector("[data-clarity-stage]");

  if (clarityEngine && clarityStage) {
    const setClarityActivity = (active) => {
      clarityEngine.classList.toggle("is-active", active && !document.hidden);
    };

    let engineInView = false;
    const engineObserver = new IntersectionObserver((entries) => {
      engineInView = entries[0]?.isIntersecting ?? false;
      setClarityActivity(engineInView);
    }, { threshold: 0.08 });
    engineObserver.observe(clarityEngine);

    document.addEventListener("visibilitychange", () => setClarityActivity(engineInView));

    if (finePointer.matches && !reduceMotion.matches) {
      let bounds = null;
      let frame = 0;
      const current = { x: 0, y: 0, rx: 0, ry: 0 };
      const target = { x: 0, y: 0, rx: 0, ry: 0 };

      const renderClarityDepth = () => {
        frame = 0;
        current.x += (target.x - current.x) * 0.16;
        current.y += (target.y - current.y) * 0.16;
        current.rx += (target.rx - current.rx) * 0.16;
        current.ry += (target.ry - current.ry) * 0.16;

        clarityEngine.style.setProperty("--clarity-x", `${current.x.toFixed(2)}px`);
        clarityEngine.style.setProperty("--clarity-y", `${current.y.toFixed(2)}px`);
        clarityEngine.style.setProperty("--clarity-rx", `${current.rx.toFixed(3)}deg`);
        clarityEngine.style.setProperty("--clarity-ry", `${current.ry.toFixed(3)}deg`);

        const moving =
          Math.abs(target.x - current.x) > 0.02 ||
          Math.abs(target.y - current.y) > 0.02 ||
          Math.abs(target.rx - current.rx) > 0.004 ||
          Math.abs(target.ry - current.ry) > 0.004;
        if (moving) frame = requestAnimationFrame(renderClarityDepth);
      };

      const queueClarityFrame = () => {
        if (!frame && engineInView && !document.hidden) frame = requestAnimationFrame(renderClarityDepth);
      };

      clarityStage.addEventListener("pointermove", (event) => {
        if (!bounds) bounds = clarityStage.getBoundingClientRect();
        const x = Math.min(1, Math.max(0, (event.clientX - bounds.left) / bounds.width));
        const y = Math.min(1, Math.max(0, (event.clientY - bounds.top) / bounds.height));
        target.x = (x - 0.5) * 12;
        target.y = (y - 0.5) * 8;
        target.rx = (0.5 - y) * 1.0;
        target.ry = (x - 0.5) * 1.0;
        queueClarityFrame();
      }, { passive: true });

      clarityStage.addEventListener("pointerleave", () => {
        target.x = 0;
        target.y = 0;
        target.rx = 0;
        target.ry = 0;
        queueClarityFrame();
      }, { passive: true });

      addEventListener("resize", () => { bounds = null; }, { passive: true });
    }
  }

})();
