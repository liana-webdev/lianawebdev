(() => {
  "use strict";
  const site = document.body.dataset.labSite;
  const status = document.querySelector("[data-lab-status]");
  let activeDialog = null;
  let dialogTrigger = null;

  const announce = (message) => {
    if (!status) return;
    status.textContent = message;
    status.classList.add("show");
    clearTimeout(announce.timer);
    announce.timer = setTimeout(() => status.classList.remove("show"), 2600);
  };
  const focusable = (root) => [...root.querySelectorAll('button:not([disabled]), a[href], input:not([disabled]), [tabindex]:not([tabindex="-1"])')].filter((el) => !el.hidden);
  const openDialog = (dialog, trigger) => {
    if (!dialog) return;
    activeDialog = dialog; dialogTrigger = trigger; dialog.hidden = false;
    document.body.classList.add("lab-dialog-open");
    (dialog.querySelector("[data-close-dialog]") || focusable(dialog)[0])?.focus();
  };
  const closeDialog = () => {
    if (!activeDialog) return;
    activeDialog.hidden = true; activeDialog = null;
    document.body.classList.remove("lab-dialog-open"); dialogTrigger?.focus(); dialogTrigger = null;
  };
  document.querySelectorAll("[data-open-dialog],[data-open-drawer]").forEach((button) => button.addEventListener("click", () => openDialog(document.getElementById(button.dataset.openDialog || button.dataset.openDrawer), button)));
  document.querySelectorAll("[data-close-dialog]").forEach((button) => button.addEventListener("click", closeDialog));
  document.addEventListener("keydown", (event) => {
    if (!activeDialog) return;
    if (event.key === "Escape") { event.preventDefault(); closeDialog(); return; }
    if (event.key !== "Tab") return;
    const items = focusable(activeDialog); if (!items.length) return;
    if (event.shiftKey && document.activeElement === items[0]) { event.preventDefault(); items.at(-1).focus(); }
    else if (!event.shiftKey && document.activeElement === items.at(-1)) { event.preventDefault(); items[0].focus(); }
  });
  document.querySelectorAll("[data-demo-status]").forEach((button) => button.addEventListener("click", () => announce(button.dataset.demoStatus)));
  document.querySelectorAll("[data-menu-toggle]").forEach((button) => button.addEventListener("click", () => {
    const menu = document.querySelector("[data-lab-menu]"); const open = menu?.classList.toggle("open") || false;
    button.setAttribute("aria-expanded", String(open)); button.textContent = open ? "Close" : "Menu";
  }));
  document.querySelectorAll("[data-lab-menu] a,[data-lab-menu] button").forEach((item) => item.addEventListener("click", () => {
    document.querySelector("[data-lab-menu]")?.classList.remove("open");
    const toggle = document.querySelector("[data-menu-toggle]"); if (toggle) { toggle.setAttribute("aria-expanded", "false"); toggle.textContent = "Menu"; }
  }));

  if (site === "mira") {
    const eras = {
      announce: ["Salt Memory", "A debut album · 18 September", "Hear the first song", "One song is out now. The full record arrives in September.", "#listen"],
      release: ["Salt Memory", "The debut album · Out now", "Listen to the album", "Nine songs about what the body remembers after the story changes.", "#listen"],
      tour: ["Salt Memory — Live", "East coast dates · October", "See the shows", "Mira Silt performs Salt Memory with piano, electronics and a four-piece ensemble.", "#live"],
    };
    document.querySelectorAll("[data-era]").forEach((button) => button.addEventListener("click", () => {
      const [label,date,action,note,href] = eras[button.dataset.era];
      document.querySelector("[data-era-label]").textContent = label; document.querySelector("[data-era-date]").textContent = date;
      document.querySelector("[data-era-action]").textContent = action; document.querySelector("[data-era-note]").textContent = note; document.querySelector("[data-era-link]").href = href;
      document.querySelectorAll("[data-era]").forEach((entry) => entry.classList.toggle("active", entry === button)); announce(`${button.textContent} campaign state selected`);
    }));
    const player = document.querySelector("[data-player]"); let playing = false, seconds = 28, timer;
    const renderTime = () => { document.querySelector("[data-player-time]").textContent = `${Math.floor(seconds/60)}:${String(seconds%60).padStart(2,"0")} / 3:18`; document.querySelector("[data-progress]").style.width = `${seconds/198*100}%`; };
    player?.addEventListener("click", () => { playing = !playing; player.textContent = playing ? "Ⅱ" : "▶"; player.setAttribute("aria-label", playing ? "Pause album preview" : "Play album preview"); clearInterval(timer); if (playing) timer = setInterval(() => { seconds = seconds >= 198 ? 0 : seconds + 1; renderTime(); }, 1000); announce(playing ? "Album preview playing" : "Album preview paused"); }); renderTime();
    document.querySelector("[data-video-demo]")?.addEventListener("click", (event) => { const active = event.currentTarget.getAttribute("aria-pressed") === "true"; event.currentTarget.setAttribute("aria-pressed", String(!active)); event.currentTarget.querySelector("span").textContent = active ? "Watch demo" : "Preview playing"; announce(active ? "Video preview paused" : "Video preview playing without sound"); });
    document.querySelector("[data-demo-form]")?.addEventListener("submit", (event) => { event.preventDefault(); event.currentTarget.innerHTML = "<h2>You’re on the demo list.</h2><p>No information was submitted or stored.</p>"; announce("Demo signup complete; no information stored"); });
  }

  if (site === "ninth") {
    const world = document.querySelector("[data-world-view]"), shop = document.querySelector("[data-shop-view]");
    const showMode = (mode, scroll = false) => { const isShop = mode === "shop"; world.hidden = isShop; shop.hidden = !isShop; document.body.classList.toggle("shop", isShop); document.body.classList.toggle("world", !isShop); document.querySelectorAll("[data-mode]").forEach((button) => button.classList.toggle("active", button.dataset.mode === mode)); if (scroll) scrollTo({ top: 30, behavior: matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth" }); announce(`${isShop ? "Shop" : "World"} mode selected`); };
    document.querySelectorAll("[data-mode]").forEach((button) => button.addEventListener("click", () => showMode(button.dataset.mode, true)));
    document.querySelectorAll("[data-show-shop]").forEach((button) => button.addEventListener("click", () => showMode("shop", true)));
    const image = document.querySelector("[data-product-image]");
    document.querySelectorAll("[data-gallery-src]").forEach((button) => button.addEventListener("click", () => { image.src = button.dataset.gallerySrc; document.querySelectorAll("[data-gallery-src]").forEach((entry) => entry.classList.toggle("active", entry === button)); announce(`Product image ${button.textContent} selected`); }));
    let size = "", added = false, quantity = 1;
    const renderBag = () => { const total = 680 * quantity; document.querySelector("[data-bag-count]").textContent = added ? String(quantity) : "0"; document.querySelector("[data-qty]").textContent = String(quantity); document.querySelector("[data-subtotal]").textContent = `$${total} AUD`; document.querySelector("[data-checkout]").textContent = added ? `Demo checkout · $${total} AUD` : "Demo checkout"; document.querySelector("[data-bag-summary]").hidden = !added; document.querySelector("[data-bag-item]").hidden = !added; document.querySelector("[data-empty-bag]").hidden = added; document.querySelector("[data-checkout]").disabled = !added; };
    const add = document.querySelector("[data-add-bag]");
    document.querySelectorAll("[data-size]").forEach((button) => button.addEventListener("click", () => { size = button.dataset.size; document.querySelectorAll("[data-size]").forEach((entry) => entry.classList.toggle("active", entry === button)); add.textContent = "Add to bag"; announce(`Size ${size} selected`); }));
    add?.addEventListener("click", () => { if (!size) { openDialog(document.getElementById("fit-drawer"), add); announce("Choose a size before adding to bag"); return; } added = true; quantity = Math.max(quantity, 1); document.querySelector("[data-bag-variant]").textContent = `Ink / Tobacco / Bone · ${size}`; renderBag(); openDialog(document.getElementById("bag-drawer"), add); announce("Demo product added to bag"); });
    document.querySelector("[data-qty-plus]")?.addEventListener("click", () => { quantity += 1; renderBag(); announce(`Quantity ${quantity}`); });
    document.querySelector("[data-qty-minus]")?.addEventListener("click", () => { quantity -= 1; if (quantity <= 0) { quantity = 1; added = false; announce("Product removed from demo bag"); } else announce(`Quantity ${quantity}`); renderBag(); });
    document.querySelector("[data-checkout]")?.addEventListener("click", () => announce(added ? "Checkout is disabled; no payment will be taken" : "Your bag is empty"));
  }
})();
