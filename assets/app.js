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

  let ticking = false;
  const updateScroll = () => {
    const max = Math.max(document.documentElement.scrollHeight - innerHeight, 1);
    document.documentElement.style.setProperty("--page-progress", String(scrollY / max));
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

  const canvas = document.querySelector(".signal-canvas");
  const gl = canvas?.getContext("webgl", { alpha: true, antialias: false, powerPreference: "high-performance" });
  if (!canvas || !gl) {
    if (canvas) canvas.dataset.failed = "true";
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
        vec3 lightDirection = normalize(vec3(-.45, .8, 1.0));
        float diffuse = max(dot(n, lightDirection), 0.0);
        float rim = pow(1.0 - max(dot(n, -rd), 0.0), 2.2);
        float bands = smoothstep(.76, .98, abs(sin((p.y + p.x * .18) * 24.0 + u_time * .35)));
        float longitude = smoothstep(.82, .99, abs(sin(atan(p.z, p.x) * 13.0)));
        vec3 red = vec3(1.0, .015, .12);
        vec3 hot = vec3(1.0, .34, .22);
        colour = red * (.12 + diffuse * .52) + hot * rim * 1.1;
        colour += red * (bands * .21 + longitude * .12) * (diffuse + .2);
        alpha = .78 + rim * .22;
      } else {
        float flare = .0026 / max(abs(uv.x * uv.y), .002);
        float halo = .016 / max(length(uv) - .04, .04);
        colour = vec3(1.0, .01, .08) * min(flare * .018 + halo * .022, .18);
        alpha = max(colour.r * .85, 0.0);
      }
      float grain = fract(sin(dot(gl_FragCoord.xy, vec2(12.9898, 78.233))) * 43758.5453);
      colour += (grain - .5) * .018;
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
  if (!vertexShader || !fragmentShader) return;
  const program = gl.createProgram();
  gl.attachShader(program, vertexShader);
  gl.attachShader(program, fragmentShader);
  gl.linkProgram(program);
  if (!gl.getProgramParameter(program, gl.LINK_STATUS)) return;

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

  const pointerTarget = { x: 0.5, y: 0.5 };
  const pointerCurrent = { x: 0.5, y: 0.5 };
  canvas.addEventListener("pointermove", (event) => {
    const rect = canvas.getBoundingClientRect();
    pointerTarget.x = (event.clientX - rect.left) / rect.width;
    pointerTarget.y = 1 - (event.clientY - rect.top) / rect.height;
  }, { passive: true });

  const started = performance.now();
  const reduceMotion = matchMedia("(prefers-reduced-motion: reduce)").matches;
  const draw = (now) => {
    const dpr = Math.min(devicePixelRatio || 1, 1.6);
    const width = Math.max(1, Math.floor(canvas.clientWidth * dpr));
    const height = Math.max(1, Math.floor(canvas.clientHeight * dpr));
    if (canvas.width !== width || canvas.height !== height) {
      canvas.width = width;
      canvas.height = height;
      gl.viewport(0, 0, width, height);
    }
    pointerCurrent.x += (pointerTarget.x - pointerCurrent.x) * 0.045;
    pointerCurrent.y += (pointerTarget.y - pointerCurrent.y) * 0.045;
    gl.useProgram(program);
    gl.uniform2f(resolution, width, height);
    gl.uniform2f(pointer, pointerCurrent.x, pointerCurrent.y);
    gl.uniform1f(time, reduceMotion ? 1.2 : (now - started) / 1000);
    gl.uniform1f(scroll, scrollY / Math.max(document.body.scrollHeight - innerHeight, 1));
    gl.drawArrays(gl.TRIANGLES, 0, 6);
    if (!reduceMotion) requestAnimationFrame(draw);
  };
  requestAnimationFrame(draw);
})();
