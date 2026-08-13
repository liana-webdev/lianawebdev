const INSTANCES = new WeakMap();

export const PEARL_SAND_DEFAULTS = Object.freeze({
  hoverStrength: Object.freeze([0.014, 0.009]),
  hoverEaseSeconds: 4.8,
  motionSpeed: 1,
  spectralIntensity: 1,
  maxDpr: 1.5,
});

const vertexShader = `
  attribute vec2 a_position;

  void main() {
    gl_Position = vec4(a_position, 0.0, 1.0);
  }
`;

const fragmentShader = `
  precision highp float;

  uniform vec2 u_resolution;
  uniform vec2 u_pointer;
  uniform vec2 u_hover_strength;
  uniform float u_time;
  uniform float u_motion;
  uniform float u_motion_speed;
  uniform float u_spectral_strength;

  float hash(vec2 p) {
    p = fract(p * vec2(123.34, 456.21));
    p += dot(p, p + 45.32);
    return fract(p.x * p.y);
  }

  float noise(vec2 p) {
    vec2 i = floor(p);
    vec2 f = fract(p);
    f = f * f * (3.0 - 2.0 * f);
    return mix(
      mix(hash(i), hash(i + vec2(1.0, 0.0)), f.x),
      mix(hash(i + vec2(0.0, 1.0)), hash(i + vec2(1.0, 1.0)), f.x),
      f.y
    );
  }

  float fbm(vec2 p) {
    float v = 0.0;
    float a = 0.5;
    mat2 m = mat2(0.80, -0.60, 0.60, 0.80);
    for (int i = 0; i < 5; i++) {
      v += a * noise(p);
      p = m * p * 2.03 + 7.31;
      a *= 0.5;
    }
    return v;
  }

  float softEllipse(vec2 p, vec2 center, vec2 radius, float distortion, float phase) {
    vec2 q = (p - center) / radius;
    float warp = fbm(q * 1.72 + vec2(phase, -phase * 0.4)) - 0.5;
    float detail = fbm(q * 4.2 - vec2(phase * 0.25, phase * 0.13)) - 0.5;
    return length(q) - 1.0 + warp * distortion + detail * distortion * 0.22;
  }

  vec3 pearl(float x) {
    vec3 moon = vec3(0.91, 0.86, 0.77);
    vec3 aqua = vec3(0.35, 0.78, 0.79);
    vec3 lilac = vec3(0.61, 0.48, 0.75);
    vec3 blush = vec3(0.91, 0.48, 0.48);
    vec3 gold = vec3(1.00, 0.70, 0.36);
    vec3 cool = mix(aqua, lilac, smoothstep(0.08, 0.55, x));
    vec3 warm = mix(blush, gold, smoothstep(0.46, 0.95, x));
    return mix(moon, mix(cool, warm, smoothstep(0.40, 0.72, x)), 0.78);
  }

  vec3 addRim(vec3 color, float d, float phase, float warmth, float visibility) {
    float hairline = exp(-abs(d) * 128.0);
    float inner = exp(-abs(d + 0.014) * 38.0);
    float halo = exp(-abs(d) * 11.0);
    float volume = exp(-abs(d) * 3.8);
    float flicker = 0.82 + 0.18 * noise(vec2(phase * 2.8, d * 80.0 + u_time * 0.035));
    vec3 interference = pearl(fract(phase + d * 7.0));
    vec3 fire = mix(vec3(0.11, 0.42, 0.46), vec3(1.0, 0.25, 0.06), warmth);
    float spectral = clamp(u_spectral_strength, 0.0, 2.0);
    color += interference * (hairline * 1.24 + inner * 0.26) * visibility * flicker * spectral;
    color += mix(interference, fire, 0.58) * halo * (0.14 + warmth * 0.11) * visibility * spectral;
    color += fire * volume * (0.018 + warmth * 0.020) * visibility * spectral;
    return color;
  }

  void main() {
    vec2 frag = gl_FragCoord.xy;
    vec2 p = (frag * 2.0 - u_resolution.xy) / min(u_resolution.x, u_resolution.y);
    p.y *= -1.0;

    float t = u_time * 0.055 * u_motion * u_motion_speed;
    vec2 pointer = (u_pointer - 0.5) * u_hover_strength;
    p += pointer;

    float grain = hash(frag + floor(u_time * 8.0)) - 0.5;
    float lowField = fbm(p * 0.78 + vec2(t * 0.18, -t * 0.11));
    vec3 color = vec3(0.026, 0.025, 0.028);
    color += vec3(0.052, 0.018, 0.012) * smoothstep(1.18, -0.28, length(p - vec2(-0.72, 0.42)));
    color += vec3(0.012, 0.020, 0.026) * lowField * 0.38;

    float d1 = softEllipse(p, vec2(0.86, -0.30), vec2(1.18, 0.68), 0.15, t);
    float d2 = softEllipse(p, vec2(1.03, 0.64), vec2(0.76, 1.02), 0.19, t + 3.8);
    float d3 = softEllipse(p, vec2(-0.92, -0.88), vec2(1.06, 0.45), 0.12, -t + 8.2);

    float body1 = smoothstep(0.04, -0.16, d1);
    float body2 = smoothstep(0.05, -0.18, d2);
    float body3 = smoothstep(0.03, -0.12, d3);
    color += body1 * vec3(0.019, 0.014, 0.017) * 0.78;
    color += body2 * vec3(0.008, 0.015, 0.019) * 0.72;
    color += body3 * vec3(0.022, 0.014, 0.012) * 0.54;

    float phase1 = fract(0.12 + p.y * 0.18 + fbm(p * 1.3) * 0.26);
    float phase2 = fract(0.55 + p.x * 0.14 - p.y * 0.12 + fbm(p * 1.6) * 0.20);
    float phase3 = fract(0.83 - p.x * 0.12 + fbm(p * 1.8) * 0.18);

    color = addRim(color, d1, phase1, 0.75, 0.74);
    color = addRim(color, d2, phase2, 0.22, 0.62);
    color = addRim(color, d3, phase3, 0.95, 0.52);

    float contour1 = pow(1.0 - abs(sin((d1 * 8.5 + phase1 * 0.72) * 3.14159)), 16.0);
    float contour2 = pow(1.0 - abs(sin((d2 * 7.0 - phase2 * 0.58) * 3.14159)), 18.0);
    float contourMask1 = exp(-abs(d1) * 2.25) * smoothstep(0.42, -0.12, d1);
    float contourMask2 = exp(-abs(d2) * 2.55) * smoothstep(0.36, -0.10, d2);
    color += pearl(fract(phase1 + d1 * 1.7)) * contour1 * contourMask1 * 0.13 * u_spectral_strength;
    color += pearl(fract(phase2 + d2 * 1.9)) * contour2 * contourMask2 * 0.09 * u_spectral_strength;

    float dune = abs(sin((p.x * 2.15 + p.y * 1.26 + fbm(p * 2.2 + t) * 1.9) * 3.14159));
    float duneMask = smoothstep(0.54, -0.18, d1) * smoothstep(-0.62, 0.06, d1);
    float duneLine = pow(1.0 - dune, 18.0) * duneMask;
    color += pearl(fract(phase1 + 0.18)) * duneLine * 0.12 * u_spectral_strength;

    float vignette = 1.0 - smoothstep(0.72, 1.72, length(p * vec2(0.78, 0.92)));
    color *= 0.66 + vignette * 0.42;
    color += grain * 0.010;

    gl_FragColor = vec4(color, 1.0);
  }
`;

function compileShader(gl, type, source) {
  const shader = gl.createShader(type);
  if (!shader) return null;
  gl.shaderSource(shader, source);
  gl.compileShader(shader);
  if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
    gl.deleteShader(shader);
    return null;
  }
  return shader;
}

function resolveHost(target) {
  if (target instanceof HTMLElement) return target;
  if (typeof target === "string") return document.querySelector(target);
  return null;
}

function normalizeOptions(options) {
  const hover = Array.isArray(options.hoverStrength)
    ? options.hoverStrength
    : PEARL_SAND_DEFAULTS.hoverStrength;

  return {
    hoverStrength: [
      Number.isFinite(hover[0]) ? hover[0] : PEARL_SAND_DEFAULTS.hoverStrength[0],
      Number.isFinite(hover[1]) ? hover[1] : PEARL_SAND_DEFAULTS.hoverStrength[1],
    ],
    hoverEaseSeconds: Math.max(
      0.1,
      Number.isFinite(options.hoverEaseSeconds)
        ? options.hoverEaseSeconds
        : PEARL_SAND_DEFAULTS.hoverEaseSeconds,
    ),
    motionSpeed: Math.max(
      0,
      Number.isFinite(options.motionSpeed)
        ? options.motionSpeed
        : PEARL_SAND_DEFAULTS.motionSpeed,
    ),
    spectralIntensity: Math.max(
      0,
      Number.isFinite(options.spectralIntensity)
        ? options.spectralIntensity
        : PEARL_SAND_DEFAULTS.spectralIntensity,
    ),
    maxDpr: Math.max(
      1,
      Number.isFinite(options.maxDpr) ? options.maxDpr : PEARL_SAND_DEFAULTS.maxDpr,
    ),
  };
}

export function mountPearlSand(target, options = {}) {
  const host = resolveHost(target);
  if (!host) {
    throw new Error("Pearl Sand: target element was not found.");
  }

  const existing = INSTANCES.get(host);
  if (existing) return existing;

  let settings = normalizeOptions({ ...PEARL_SAND_DEFAULTS, ...options });
  const canvas = document.createElement("canvas");
  canvas.className = "pearl-sand-canvas";
  canvas.setAttribute("aria-hidden", "true");
  canvas.dataset.pearlOwned = "true";
  host.classList.add("pearl-sand-host");
  host.prepend(canvas);

  const gl = canvas.getContext("webgl", {
    alpha: false,
    antialias: false,
    powerPreference: "low-power",
  });

  const fallbackApi = {
    canvas,
    destroy() {
      canvas.remove();
      INSTANCES.delete(host);
    },
    setOptions(next = {}) {
      settings = normalizeOptions({ ...settings, ...next });
    },
  };

  if (!gl) {
    canvas.dataset.fallback = "true";
    INSTANCES.set(host, fallbackApi);
    return fallbackApi;
  }

  const vertex = compileShader(gl, gl.VERTEX_SHADER, vertexShader);
  const fragment = compileShader(gl, gl.FRAGMENT_SHADER, fragmentShader);
  if (!vertex || !fragment) {
    canvas.dataset.fallback = "true";
    INSTANCES.set(host, fallbackApi);
    return fallbackApi;
  }

  const program = gl.createProgram();
  if (!program) {
    canvas.dataset.fallback = "true";
    INSTANCES.set(host, fallbackApi);
    return fallbackApi;
  }

  gl.attachShader(program, vertex);
  gl.attachShader(program, fragment);
  gl.linkProgram(program);
  if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
    canvas.dataset.fallback = "true";
    INSTANCES.set(host, fallbackApi);
    return fallbackApi;
  }

  gl.useProgram(program);
  const buffer = gl.createBuffer();
  gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
  gl.bufferData(
    gl.ARRAY_BUFFER,
    new Float32Array([-1, -1, 1, -1, -1, 1, -1, 1, 1, -1, 1, 1]),
    gl.STATIC_DRAW,
  );

  const position = gl.getAttribLocation(program, "a_position");
  gl.enableVertexAttribArray(position);
  gl.vertexAttribPointer(position, 2, gl.FLOAT, false, 0, 0);

  const uniforms = {
    resolution: gl.getUniformLocation(program, "u_resolution"),
    pointer: gl.getUniformLocation(program, "u_pointer"),
    hoverStrength: gl.getUniformLocation(program, "u_hover_strength"),
    time: gl.getUniformLocation(program, "u_time"),
    motion: gl.getUniformLocation(program, "u_motion"),
    motionSpeed: gl.getUniformLocation(program, "u_motion_speed"),
    spectralIntensity: gl.getUniformLocation(program, "u_spectral_strength"),
  };

  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
  const pointerTarget = { x: 0.5, y: 0.5 };
  const pointerCurrent = { x: 0.5, y: 0.5 };
  let frame = 0;
  let visible = true;
  let destroyed = false;
  let lastFrame = performance.now();

  const resize = () => {
    const dpr = Math.min(window.devicePixelRatio || 1, settings.maxDpr);
    const width = Math.max(1, Math.floor(canvas.clientWidth * dpr));
    const height = Math.max(1, Math.floor(canvas.clientHeight * dpr));
    if (canvas.width !== width || canvas.height !== height) {
      canvas.width = width;
      canvas.height = height;
      gl.viewport(0, 0, width, height);
    }
  };

  const handlePointer = (event) => {
    if (event.pointerType === "touch" || reduceMotion.matches) return;
    const bounds = host.getBoundingClientRect();
    if (!bounds.width || !bounds.height) return;
    pointerTarget.x = (event.clientX - bounds.left) / bounds.width;
    pointerTarget.y = 1 - (event.clientY - bounds.top) / bounds.height;
  };

  const handlePointerLeave = () => {
    pointerTarget.x = 0.5;
    pointerTarget.y = 0.5;
  };

  const observer =
    "IntersectionObserver" in window
      ? new IntersectionObserver(([entry]) => {
          visible = entry.isIntersecting;
        })
      : null;

  observer?.observe(host);

  const render = (now) => {
    if (destroyed) return;
    resize();
    const deltaSeconds = Math.min((now - lastFrame) / 1000, 0.05);
    lastFrame = now;
    const pointerEase = 1 - Math.exp(-deltaSeconds / settings.hoverEaseSeconds);
    pointerCurrent.x += (pointerTarget.x - pointerCurrent.x) * pointerEase;
    pointerCurrent.y += (pointerTarget.y - pointerCurrent.y) * pointerEase;

    const pointerX = reduceMotion.matches ? 0.5 : pointerCurrent.x;
    const pointerY = reduceMotion.matches ? 0.5 : pointerCurrent.y;
    gl.uniform2f(uniforms.resolution, canvas.width, canvas.height);
    gl.uniform2f(uniforms.pointer, pointerX, pointerY);
    gl.uniform2f(
      uniforms.hoverStrength,
      settings.hoverStrength[0],
      settings.hoverStrength[1],
    );
    gl.uniform1f(uniforms.time, reduceMotion.matches ? 0 : now / 1000);
    gl.uniform1f(uniforms.motion, reduceMotion.matches ? 0 : 1);
    gl.uniform1f(uniforms.motionSpeed, settings.motionSpeed);
    gl.uniform1f(uniforms.spectralIntensity, settings.spectralIntensity);

    if (visible && !document.hidden) {
      gl.drawArrays(gl.TRIANGLES, 0, 6);
    }
    frame = requestAnimationFrame(render);
  };

  host.addEventListener("pointermove", handlePointer, { passive: true });
  host.addEventListener("pointerleave", handlePointerLeave, { passive: true });
  frame = requestAnimationFrame(render);

  const api = {
    canvas,
    setOptions(next = {}) {
      settings = normalizeOptions({ ...settings, ...next });
    },
    destroy() {
      if (destroyed) return;
      destroyed = true;
      cancelAnimationFrame(frame);
      observer?.disconnect();
      host.removeEventListener("pointermove", handlePointer);
      host.removeEventListener("pointerleave", handlePointerLeave);
      gl.deleteProgram(program);
      gl.deleteShader(vertex);
      gl.deleteShader(fragment);
      if (buffer) gl.deleteBuffer(buffer);
      canvas.remove();
      INSTANCES.delete(host);
    },
  };

  INSTANCES.set(host, api);
  return api;
}
