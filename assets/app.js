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

  const canvas = document.querySelector(".signal-canvas");
  if (!canvas) return;

  const gl = canvas.getContext("webgl", { alpha: true, antialias: false, powerPreference: "high-performance" });
  if (!gl) {
    canvas.dataset.failed = "true";
    return;
  }

  const vertexSource = `
    attribute vec2 a_position;
    void main() { gl_Position = vec4(a_position, 0.0, 1.0); }
  `;
  const fragmentSource = `
    precision highp float;
    uniform vec2 u_resolution;
    uniform vec2 u_pointer;
    uniform float u_time;
    uniform float u_scroll;
    mat2 rot(float a) { float s = sin(a), c = cos(a); return mat2(c, -s, s, c); }
    float ribbonNoise(vec3 p) {
      float broad = sin(dot(p, vec3(3.7, 5.1, 4.3)) * 1.7 + sin(p.y * 4.6 - u_time * .12));
      float broken = sin(dot(p, vec3(-5.2, 2.8, 4.7)) * 2.4 - cos(p.x * 5.3 + u_time * .09));
      return .5 + broad * .28 + broken * .22;
    }
    vec3 thinFilm(float phase) {
      return .5 + .5 * cos(phase + vec3(0.0, 2.12, 4.24));
    }
    float field(vec3 p) {
      p.xz *= rot(u_time * .09 + u_scroll * 1.2);
      p.xy *= rot(-u_time * .06);
      float ripple = sin(p.x * 7.0 + u_time) * sin(p.y * 6.0 - u_time * .7) * sin(p.z * 8.0);
      float ridge = .04 * sin(atan(p.y, p.x) * 9.0 + p.z * 4.0 + u_time * .8);
      return length(p) - .78 - ripple * .045 - ridge;
    }
    vec3 normalAt(vec3 p) {
      vec2 e = vec2(.0025, 0.0);
      return normalize(vec3(
        field(p + e.xyy) - field(p - e.xyy),
        field(p + e.yxy) - field(p - e.yxy),
        field(p + e.yyx) - field(p - e.yyx)
      ));
    }
    float raymarch(vec3 ro, vec3 rd, out vec3 p) {
      float total = 0.0;
      for (int i = 0; i < 72; i++) {
        p = ro + rd * total;
        float d = field(p);
        if (abs(d) < .0015 || total > 6.0) break;
        total += d * .72;
      }
      return total;
    }
    void main() {
      vec2 uv = (gl_FragCoord.xy * 2.0 - u_resolution.xy) / min(u_resolution.x, u_resolution.y);
      uv.x -= .08;
      vec2 pointer = (u_pointer - .5) * .34;
      vec3 ro = vec3(pointer.x, pointer.y, 3.15);
      vec3 rd = normalize(vec3(uv, -2.2));
      vec3 p;
      float distanceTravelled = raymarch(ro, rd, p);
      vec3 colour = vec3(0.0);
      float alpha = 0.0;
      if (distanceTravelled < 6.0) {
        vec3 n = normalAt(p);
        vec3 viewDirection = normalize(-rd);
        vec3 lightDirection = normalize(vec3(-.48, .82, 1.0));
        float diffuse = max(dot(n, lightDirection), 0.0);
        float facing = clamp(dot(n, viewDirection), 0.0, 1.0);
        float fresnel = pow(1.0 - facing, 3.25);
        vec3 reflection = reflect(-lightDirection, n);
        float clearcoat = pow(max(dot(reflection, viewDirection), 0.0), 48.0);
        float softSpecular = pow(max(dot(reflection, viewDirection), 0.0), 12.0);
        float ribbon = smoothstep(.82, .985, abs(sin((p.y + p.x * .31 - p.z * .18) * 13.0 + u_time * .13)));
        float broken = smoothstep(.64, .88, ribbonNoise(p));
        float fold = smoothstep(.35, .88, 1.0 - abs(dot(n, normalize(vec3(.2, .92, -.34)))));
        float spectralMask = smoothstep(.18, .82, fresnel) * ribbon * broken * (.2 + fold * .8);
        float phase = 4.8 + p.y * 2.1 + p.x * .7 - p.z * 1.2 + fresnel * 7.2 + u_time * .035;
        vec3 film = thinFilm(phase);
        film = mix(film, vec3(1.0, .83, .54), smoothstep(.88, 1.0, ribbonNoise(p + vec3(1.7))));

        float redBody = .42 + diffuse * .78;
        vec3 signalRed = vec3(.96, .004, .032) * redBody;
        signalRed += vec3(.2, 0.0, .012) * (1.0 - facing) * .32;
        colour = signalRed;
        colour += vec3(1.0, .93, .82) * clearcoat * .54;
        colour += vec3(1.0, .19, .22) * softSpecular * .1;
        colour += film * spectralMask * .22;
        colour += vec3(1.0, .012, .045) * fresnel * ribbon * (1.0 - broken) * .16;
        colour = 1.0 - exp(-colour * 1.22);
        alpha = .94 + fresnel * .06;
      } else {
        float flare = .0026 / max(abs(uv.x * uv.y), .002);
        float halo = .016 / max(length(uv) - .04, .04);
        colour = vec3(1.0, .01, .075) * min(flare * .014 + halo * .017, .12);
        alpha = max(colour.r * .85, 0.0);
      }
      float grain = fract(sin(dot(gl_FragCoord.xy, vec2(12.9898, 78.233))) * 43758.5453);
      colour += (grain - .5) * .012;
      gl_FragColor = vec4(colour, alpha);
    }
  `;

  const compile = (type, source) => {
    const shader = gl.createShader(type);
    gl.shaderSource(shader, source);
    gl.compileShader(shader);
    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) return null;
    return shader;
  };
  const vertexShader = compile(gl.VERTEX_SHADER, vertexSource);
  const fragmentShader = compile(gl.FRAGMENT_SHADER, fragmentSource);
  if (!vertexShader || !fragmentShader) {
    canvas.dataset.failed = "true";
    return;
  }
  const program = gl.createProgram();
  gl.attachShader(program, vertexShader);
  gl.attachShader(program, fragmentShader);
  gl.linkProgram(program);
  if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
    canvas.dataset.failed = "true";
    return;
  }

  const buffer = gl.createBuffer();
  gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
  gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, -1, 1, 1, -1, 1, 1]), gl.STATIC_DRAW);
  const position = gl.getAttribLocation(program, "a_position");
  const resolution = gl.getUniformLocation(program, "u_resolution");
  const pointer = gl.getUniformLocation(program, "u_pointer");
  const time = gl.getUniformLocation(program, "u_time");
  const scroll = gl.getUniformLocation(program, "u_scroll");
  gl.enableVertexAttribArray(position);
  gl.vertexAttribPointer(position, 2, gl.FLOAT, false, 0, 0);

  let canvasBounds = null;
  let canvasFrame = 0;
  let canvasVisible = true;
  let pageVisible = !document.hidden;
  let contextLost = false;
  let sizeDirty = true;
  let elapsed = reduceMotion.matches ? 1.2 : 0;
  let previousFrame = performance.now();
  const pointerTarget = { x: 0.5, y: 0.5 };
  const pointerCurrent = { x: 0.5, y: 0.5 };

  const queueCanvasFrame = () => {
    if (!canvasFrame && canvasVisible && pageVisible && !contextLost) {
      previousFrame = performance.now();
      canvasFrame = requestAnimationFrame(draw);
    }
  };

  const updateCanvasPointer = (event) => {
    if (!canvasBounds) canvasBounds = canvas.getBoundingClientRect();
    pointerTarget.x = Math.min(1, Math.max(0, (event.clientX - canvasBounds.left) / canvasBounds.width));
    pointerTarget.y = 1 - Math.min(1, Math.max(0, (event.clientY - canvasBounds.top) / canvasBounds.height));
  };

  canvas.addEventListener("pointerenter", (event) => {
    canvasBounds = canvas.getBoundingClientRect();
    updateCanvasPointer(event);
  }, { passive: true });
  canvas.addEventListener("pointermove", updateCanvasPointer, { passive: true });
  canvas.addEventListener("pointerleave", () => {
    pointerTarget.x = 0.5;
    pointerTarget.y = 0.5;
  }, { passive: true });
  canvas.addEventListener("webglcontextlost", () => {
    contextLost = true;
    if (canvasFrame) cancelAnimationFrame(canvasFrame);
    canvasFrame = 0;
    canvas.dataset.failed = "true";
  });

  if ("ResizeObserver" in window) {
    new ResizeObserver(() => {
      sizeDirty = true;
      canvasBounds = null;
      queueCanvasFrame();
    }).observe(canvas);
  } else {
    addEventListener("resize", () => {
      sizeDirty = true;
      canvasBounds = null;
      queueCanvasFrame();
    }, { passive: true });
  }

  if ("IntersectionObserver" in window) {
    const canvasObserver = new IntersectionObserver((entries) => {
      canvasVisible = entries.some((entry) => entry.isIntersecting);
      if (canvasVisible) queueCanvasFrame();
    }, { threshold: 0.01 });
    canvasObserver.observe(canvas);
  }

  document.addEventListener("visibilitychange", () => {
    pageVisible = !document.hidden;
    if (pageVisible) queueCanvasFrame();
  });

  const draw = (now) => {
    canvasFrame = 0;
    if (!canvasVisible || !pageVisible || contextLost) return;

    if (sizeDirty) {
      const pixelRatioCap = innerWidth < 600 ? 1.25 : 1.5;
      const dpr = Math.min(devicePixelRatio || 1, pixelRatioCap);
      const width = Math.max(1, Math.floor(canvas.clientWidth * dpr));
      const height = Math.max(1, Math.floor(canvas.clientHeight * dpr));
      if (canvas.width !== width || canvas.height !== height) {
        canvas.width = width;
        canvas.height = height;
        gl.viewport(0, 0, width, height);
      }
      sizeDirty = false;
    }

    const delta = Math.min(Math.max((now - previousFrame) / 1000, 0), 0.05);
    previousFrame = now;
    if (!reduceMotion.matches) elapsed += delta;
    pointerCurrent.x += (pointerTarget.x - pointerCurrent.x) * 0.045;
    pointerCurrent.y += (pointerTarget.y - pointerCurrent.y) * 0.045;
    gl.useProgram(program);
    gl.uniform2f(resolution, canvas.width, canvas.height);
    gl.uniform2f(pointer, pointerCurrent.x, pointerCurrent.y);
    gl.uniform1f(time, reduceMotion.matches ? 1.2 : elapsed);
    gl.uniform1f(scroll, scrollY / pageScrollMax);
    gl.drawArrays(gl.TRIANGLES, 0, 6);
    if (!reduceMotion.matches) queueCanvasFrame();
  };
  queueCanvasFrame();
})();
