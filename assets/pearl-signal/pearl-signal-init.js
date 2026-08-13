import { mountPearlSand } from "./pearl-sand.js";

const host = document.querySelector("[data-pearl-sand]");

if (host) {
  const pearl = mountPearlSand(host, {
    hoverStrength: [0.014, 0.009],
    hoverEaseSeconds: 4.8,
    motionSpeed: 1,
    spectralIntensity: 1,
    maxDpr: 1.5,
  });

  window.addEventListener("pagehide", () => pearl.destroy(), { once: true });
}
