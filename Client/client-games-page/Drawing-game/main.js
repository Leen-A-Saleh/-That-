const cv = document.getElementById("cv");
const ctx = cv.getContext("2d");

const COLORS = [
  "#FF0000",
  "#FF3D3D",
  "#FF7070",
  "#FFB3B3",
  "#FF1493",
  "#FF69B4",
  "#FFB6C1",
  "#FF006E",
  "#FF4500",
  "#FF6B35",
  "#FF8C00",
  "#FFA500",
  "#FFB84D",
  "#FFCC80",
  "#E65100",
  "#FF6F00",
  "#FFD700",
  "#FFEC1A",
  "#FFF176",
  "#F9A825",
  "#ADFF2F",
  "#4CAF50",
  "#00C800",
  "#81C784",
  "#1B5E20",
  "#388E3C",
  "#00E676",
  "#69F0AE",
  "#00BFA5",
  "#26A69A",
  "#00E5FF",
  "#00BCD4",
  "#0097A7",
  "#006064",
  "#1565C0",
  "#1E88E5",
  "#64B5F6",
  "#BBDEFB",
  "#0D47A1",
  "#2979FF",
  "#82B1FF",
  "#448AFF",
  "#3F51B5",
  "#5C6BC0",
  "#6A1B9A",
  "#8E24AA",
  "#CE93D8",
  "#4A148C",
  "#D500F9",
  "#E040FB",
  "#311B92",
  "#9FA8DA",
  "#880E4F",
  "#D81B60",
  "#F48FB1",
  "#FCE4EC",
  "#3E2723",
  "#5D4037",
  "#8D6E63",
  "#BCAAA4",
  "#000000",
  "#424242",
  "#757575",
  "#BDBDBD",
  "#E0E0E0",
  "#F5F5F5",
  "#FFFFFF",
  "#FF8C42",
];
function goBack() {
  window.location.href = "../index.php";
}

const SVG_SHAPES = [
  {
    id: "line",
    name: "خط",
    svg: '<line x1="5" y1="27" x2="53" y2="3" stroke="FILL" stroke-width="3.5" stroke-linecap="round"/>',
  },
  {
    id: "circle",
    name: "دائرة",
    svg: '<ellipse cx="28" cy="15" rx="22" ry="12" stroke="FILL" stroke-width="2.8" fill="BGFILL"/>',
  },
  {
    id: "square",
    name: "مربع",
    svg: '<rect x="4" y="3" width="48" height="27" rx="2" stroke="FILL" stroke-width="2.8" fill="BGFILL"/>',
  },
  {
    id: "roundrect",
    name: "مستطيل",
    svg: '<rect x="4" y="3" width="48" height="27" rx="9" stroke="FILL" stroke-width="2.8" fill="BGFILL"/>',
  },
  {
    id: "triangle",
    name: "مثلث",
    svg: '<polygon points="28,2 53,28 3,28" stroke="FILL" stroke-width="2.8" stroke-linejoin="round" fill="BGFILL"/>',
  },
  {
    id: "star",
    name: "نجمة",
    svg: '<polygon points="28,2 32,16 47,16 35,24 40,38 28,30 16,38 21,24 9,16 24,16" stroke="FILL" stroke-width="2.2" stroke-linejoin="round" fill="BGFILL"/>',
  },
  {
    id: "heart",
    name: "قلب",
    svg: '<path d="M28,32C28,32 6,20 6,10C6,4 11,1 17,3C21,4 25,8 28,12C31,8 35,4 39,3C45,1 50,4 50,10C50,20 28,32 28,32Z" stroke="FILL" stroke-width="2.2" fill="BGFILL"/>',
  },
  {
    id: "diamond",
    name: "معين",
    svg: '<polygon points="28,2 52,15 28,28 4,15" stroke="FILL" stroke-width="2.8" stroke-linejoin="round" fill="BGFILL"/>',
  },
  {
    id: "arrow",
    name: "سهم",
    svg: '<path d="M4,11 L30,11 L30,4 L52,15 L30,26 L30,19 L4,19 Z" stroke="FILL" stroke-width="2.2" stroke-linejoin="round" fill="BGFILL"/>',
  },
  {
    id: "star6",
    name: "نجمة",
    svg: '<polygon points="28,2 32,14 44,10 37,20 44,30 32,26 28,38 24,26 12,30 19,20 12,10 24,14" stroke="FILL" stroke-width="2.2" stroke-linejoin="round" fill="BGFILL"/>',
  },
  {
    id: "pentagon",
    name: "خماسي",
    svg: '<polygon points="28,2 51,18 43,42 13,42 5,18" stroke="FILL" stroke-width="2.8" stroke-linejoin="round" fill="BGFILL"/>',
  },
  {
    id: "hexagon",
    name: "سداسي",
    svg: '<polygon points="28,2 50,14 50,28 28,40 6,28 6,14" stroke="FILL" stroke-width="2.8" stroke-linejoin="round" fill="BGFILL"/>',
  },
];

const BGS = [
  { c: "#FFFFFF", n: "أبيض" },
  { c: "#FFFDE7", n: "أصفر فاتح" },
  { c: "#E3F2FD", n: "أزرق فاتح" },
  { c: "#FCE4EC", n: "وردي" },
  { c: "#E8F5E9", n: "أخضر" },
  { c: "#EDE7F6", n: "بنفسجي" },
  { c: "#FFF8E1", n: "برتقالي" },
  { c: "#1A1A2E", n: "ليلي" },
];

let color = "#FF5252",
  brushSize = 8,
  tool = "pen";
let drawing = false,
  lx = 0,
  ly = 0;
let hist = [],
  activeShape = null,
  fillShapes = false,
  bgColor = "#FFFFFF";
let shStart = null,
  shSnap = null;

function initCv() {
  const a = document.getElementById("cvarea");
  cv.width = a.clientWidth - 28;
  cv.height = a.clientHeight - 28;
  ctx.fillStyle = bgColor;
  ctx.fillRect(0, 0, cv.width, cv.height);
  pushHist();
}

function buildColors() {
  const g = document.getElementById("colGrid");
  COLORS.forEach((c) => {
    const b = document.createElement("button");
    b.className = "csw" + (c === color ? " on" : "");
    b.style.background = c;
    if (["#FFFFFF", "#F5F5F5", "#E0E0E0"].includes(c))
      b.style.border = "2px solid #DDD";
    b.onclick = () => {
      color = c;
      document
        .querySelectorAll(".csw")
        .forEach((s) => s.classList.remove("on"));
      b.classList.add("on");
      document.getElementById("curcol").style.background = c;
      updSz();
      if (tool === "eraser") setTool("pen");
      updShapeColors();
    };
    g.appendChild(b);
  });
}

function shapesvg(sh) {
  const alpha = fillShapes ? "FF" : "18";
  const hex = color.replace("#", "");
  const rr = parseInt(hex.slice(0, 2), 16);
  const gg = parseInt(hex.slice(2, 4), 16);
  const bb = parseInt(hex.slice(4, 6), 16);
  const bg = fillShapes ? color : `rgba(${rr},${gg},${bb},0.08)`;
  return sh.svg.replace(/FILL/g, color).replace(/BGFILL/g, bg);
}

function buildShapes() {
  const g = document.getElementById("shgrid");
  SVG_SHAPES.forEach((sh) => {
    const btn = document.createElement("button");
    btn.className = "shbtn";
    btn.id = "shbtn-" + sh.id;
    btn.title = sh.name;
    btn.innerHTML = `<svg viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg">${shapesvg(sh)}</svg><span class="shl">${sh.name}</span>`;
    btn.onclick = () => {
      if (activeShape === sh.id) {
        activeShape = null;
        setTool("pen");
        btn.classList.remove("on");
      } else {
        document
          .querySelectorAll(".shbtn")
          .forEach((b) => b.classList.remove("on"));
        activeShape = sh.id;
        tool = "shape";
        updateToolBtns();
        btn.classList.add("on");
        cv.style.cursor = "crosshair";
      }
    };
    g.appendChild(btn);
  });
}

function updShapeColors() {
  SVG_SHAPES.forEach((sh) => {
    const btn = document.getElementById("shbtn-" + sh.id);
    if (btn) {
      const svg = btn.querySelector("svg");
      if (svg) svg.innerHTML = shapesvg(sh);
    }
  });
}

function buildBg() {
  const cont = document.getElementById("bglist");
  BGS.forEach((bg, i) => {
    const b = document.createElement("button");
    b.className = "bgsw" + (i === 0 ? " sel" : "");
    b.style.background = bg.c;
    b.title = bg.n;
    b.onclick = () => {
      bgColor = bg.c;
      ctx.fillStyle = bgColor;
      ctx.fillRect(0, 0, cv.width, cv.height);
      document
        .querySelectorAll(".bgsw")
        .forEach((x) => x.classList.remove("sel"));
      b.classList.add("sel");
      pushHist();
    };
    cont.appendChild(b);
  });
}

function setTool(t) {
  tool = t;
  activeShape = null;
  document.querySelectorAll(".shbtn").forEach((b) => b.classList.remove("on"));
  updateToolBtns();
  cv.style.cursor =
    t === "eraser" ? "cell" : t === "fill" ? "copy" : "crosshair";
}

function updateToolBtns() {
  ["pen", "eraser", "fill"].forEach((id) => {
    const el = document.getElementById("tbtn-" + id);
    if (el) el.classList.toggle("on", tool === id);
  });
}

function setFill(v) {
  fillShapes = v;
  document.getElementById("fo-out").classList.toggle("sel", !v);
  document.getElementById("fo-fill").classList.toggle("sel", v);
  updShapeColors();
}

function updSz() {
  const sz = Math.min(brushSize, 48);
  const dot = document.getElementById("szdot");
  dot.style.width = sz + "px";
  dot.style.height = sz + "px";
  dot.style.background = color;
  document.getElementById("sznum").textContent = brushSize + "px";
}

function getPos(e) {
  const r = cv.getBoundingClientRect();
  const sx = cv.width / r.width,
    sy = cv.height / r.height;
  const s = e.touches ? e.touches[0] : e;
  return [(s.clientX - r.left) * sx, (s.clientY - r.top) * sy];
}

function pushHist() {
  if (hist.length >= 40) hist.shift();
  hist.push(cv.toDataURL());
}

function undo() {
  if (hist.length < 2) return;
  hist.pop();
  const img = new Image();
  img.src = hist[hist.length - 1];
  img.onload = () => ctx.drawImage(img, 0, 0);
}

function clearAll() {
  ctx.clearRect(0, 0, cv.width, cv.height);

  bgColor = "#FFFFFF";

  ctx.fillStyle = bgColor;
  ctx.fillRect(0, 0, cv.width, cv.height);

  document.querySelectorAll(".bgsw").forEach((b) => {
    b.classList.remove("sel");
  });

  document.querySelector(".bgsw").classList.add("sel");

  pushHist();
}

function saveImg() {
  const a = document.createElement("a");
  a.download = "رسمتي.png";
  a.href = cv.toDataURL("image/png");
  a.click();
}

function hexToRgb(h) {
  h = h.replace(/^#/, "");
  return [
    parseInt(h.slice(0, 2), 16),
    parseInt(h.slice(2, 4), 16),
    parseInt(h.slice(4, 6), 16),
  ];
}

function floodFill(sx, sy, fh) {
  const w = cv.width,
    h = cv.height;
  const id = ctx.getImageData(0, 0, w, h);
  const d = id.data;
  const [fr, fg, fb] = hexToRgb(fh);
  const xi = Math.round(sx),
    yi = Math.round(sy);
  const base = (yi * w + xi) * 4;
  const tr = d[base],
    tg = d[base + 1],
    tb = d[base + 2];
  if (tr === fr && tg === fg && tb === fb) return;
  const tol = 35;
  const match = (i) =>
    Math.abs(d[i] - tr) <= tol &&
    Math.abs(d[i + 1] - tg) <= tol &&
    Math.abs(d[i + 2] - tb) <= tol;
  const stack = [xi + yi * w];
  const seen = new Uint8Array(w * h);
  while (stack.length) {
    const pos = stack.pop();
    if (seen[pos]) continue;
    seen[pos] = 1;
    const x = pos % w,
      y = (pos - x) / w;
    if (x < 0 || x >= w || y < 0 || y >= h) continue;
    const i = pos * 4;
    if (!match(i)) continue;
    d[i] = fr;
    d[i + 1] = fg;
    d[i + 2] = fb;
    d[i + 3] = 255;
    if (x > 0) stack.push(pos - 1);
    if (x < w - 1) stack.push(pos + 1);
    if (y > 0) stack.push(pos - w);
    if (y < h - 1) stack.push(pos + w);
  }
  ctx.putImageData(id, 0, 0);
}

function drawShape(id, x1, y1, x2, y2, prev) {
  ctx.save();
  const [r, g, b] = hexToRgb(color);
  ctx.strokeStyle = color;
  ctx.lineWidth = brushSize;
  ctx.lineCap = "round";
  ctx.lineJoin = "round";
  ctx.fillStyle = fillShapes ? color : `rgba(${r},${g},${b},0.12)`;
  if (prev) {
    ctx.setLineDash([7, 5]);
    ctx.globalAlpha = 0.7;
  }
  const cx = (x1 + x2) / 2,
    cy = (y1 + y2) / 2,
    w = x2 - x1,
    h = y2 - y1;
  const rd = Math.min(Math.abs(w), Math.abs(h)) / 2;
  ctx.beginPath();
  if (id === "line") {
    ctx.moveTo(x1, y1);
    ctx.lineTo(x2, y2);
    ctx.stroke();
    ctx.restore();
    return;
  }
  if (id === "circle")
    ctx.ellipse(cx, cy, Math.abs(w / 2), Math.abs(h / 2), 0, 0, Math.PI * 2);
  else if (id === "square") ctx.rect(x1, y1, w, h);
  else if (id === "roundrect") {
    const rad = Math.min(Math.abs(w), Math.abs(h)) * 0.18;
    ctx.roundRect(x1, y1, w, h, rad);
  } else if (id === "triangle") {
    ctx.moveTo(cx, y1);
    ctx.lineTo(x2, y2);
    ctx.lineTo(x1, y2);
    ctx.closePath();
  } else if (id === "star") {
    for (let i = 0; i < 10; i++) {
      const a = (Math.PI / 5) * i - Math.PI / 2,
        r2 = i % 2 === 0 ? rd : rd * 0.4;
      i === 0
        ? ctx.moveTo(cx + Math.cos(a) * r2, cy + Math.sin(a) * r2)
        : ctx.lineTo(cx + Math.cos(a) * r2, cy + Math.sin(a) * r2);
    }
    ctx.closePath();
  } else if (id === "star6") {
    for (let i = 0; i < 12; i++) {
      const a = (Math.PI / 6) * i - Math.PI / 2,
        r2 = i % 2 === 0 ? rd : rd * 0.55;
      i === 0
        ? ctx.moveTo(cx + Math.cos(a) * r2, cy + Math.sin(a) * r2)
        : ctx.lineTo(cx + Math.cos(a) * r2, cy + Math.sin(a) * r2);
    }
    ctx.closePath();
  } else if (id === "heart") {
    const s = rd;
    ctx.moveTo(cx, cy + s * 0.85);
    ctx.bezierCurveTo(
      cx - s * 1.35,
      cy + s * 0.1,
      cx - s * 1.35,
      cy - s * 0.9,
      cx,
      cy - s * 0.4,
    );
    ctx.bezierCurveTo(
      cx + s * 1.35,
      cy - s * 0.9,
      cx + s * 1.35,
      cy + s * 0.1,
      cx,
      cy + s * 0.85,
    );
  } else if (id === "diamond") {
    ctx.moveTo(cx, y1);
    ctx.lineTo(x2, cy);
    ctx.lineTo(cx, y2);
    ctx.lineTo(x1, cy);
    ctx.closePath();
  } else if (id === "arrow") {
    const hw = Math.abs(h) * 0.28;
    ctx.moveTo(x1, cy - hw);
    ctx.lineTo(cx - hw, cy - hw);
    ctx.lineTo(cx - hw, y1);
    ctx.lineTo(x2, cy);
    ctx.lineTo(cx - hw, y2);
    ctx.lineTo(cx - hw, cy + hw);
    ctx.lineTo(x1, cy + hw);
    ctx.closePath();
  } else if (id === "pentagon") {
    for (let i = 0; i < 5; i++) {
      const a = ((Math.PI * 2) / 5) * i - Math.PI / 2;
      i === 0
        ? ctx.moveTo(cx + Math.cos(a) * rd, cy + Math.sin(a) * rd)
        : ctx.lineTo(cx + Math.cos(a) * rd, cy + Math.sin(a) * rd);
    }
    ctx.closePath();
  } else if (id === "hexagon") {
    for (let i = 0; i < 6; i++) {
      const a = (Math.PI / 3) * i - Math.PI / 6;
      i === 0
        ? ctx.moveTo(cx + Math.cos(a) * rd, cy + Math.sin(a) * rd)
        : ctx.lineTo(cx + Math.cos(a) * rd, cy + Math.sin(a) * rd);
    }
    ctx.closePath();
  }
  ctx.fill();
  ctx.stroke();
  ctx.restore();
}

function onStart(e) {
  e.preventDefault();
  const [x, y] = getPos(e);
  if (tool === "fill") {
    pushHist();
    floodFill(x, y, color);
    return;
  }
  if (tool === "shape") {
    shStart = [x, y];
    shSnap = ctx.getImageData(0, 0, cv.width, cv.height);
    drawing = true;
    return;
  }
  drawing = true;
  lx = x;
  ly = y;
  pushHist();
  ctx.beginPath();
  if (tool === "eraser") {
    ctx.arc(x, y, brushSize * 1.5, 0, Math.PI * 2);
    ctx.fillStyle = bgColor;
  } else {
    ctx.arc(x, y, brushSize / 2, 0, Math.PI * 2);
    ctx.fillStyle = color;
  }
  ctx.fill();
}

function onMove(e) {
  e.preventDefault();
  if (!drawing) return;
  const [x, y] = getPos(e);
  if (tool === "shape" && shStart && shSnap) {
    ctx.putImageData(shSnap, 0, 0);
    drawShape(activeShape, shStart[0], shStart[1], x, y, true);
    return;
  }
  ctx.beginPath();
  ctx.moveTo(lx, ly);
  ctx.lineTo(x, y);
  ctx.strokeStyle = tool === "eraser" ? bgColor : color;
  ctx.lineWidth = tool === "eraser" ? brushSize * 3 : brushSize;
  ctx.lineCap = "round";
  ctx.lineJoin = "round";
  ctx.stroke();
  lx = x;
  ly = y;
}

function onEnd(e) {
  if (tool === "shape" && shStart) {
    let x, y;
    if (e.changedTouches && e.changedTouches.length) {
      const r = cv.getBoundingClientRect();
      x = (e.changedTouches[0].clientX - r.left) * (cv.width / r.width);
      y = (e.changedTouches[0].clientY - r.top) * (cv.height / r.height);
    } else {
      [x, y] = getPos(e);
    }
    if (shSnap) ctx.putImageData(shSnap, 0, 0);
    drawShape(activeShape, shStart[0], shStart[1], x, y, false);
    pushHist();
    shStart = null;
    shSnap = null;
  }
  drawing = false;
}

cv.addEventListener("mousedown", onStart);
cv.addEventListener("mousemove", onMove);
cv.addEventListener("mouseup", onEnd);
cv.addEventListener("mouseleave", onEnd);
cv.addEventListener("touchstart", onStart, { passive: false });
cv.addEventListener("touchmove", onMove, { passive: false });
cv.addEventListener("touchend", onEnd);

document.addEventListener("keydown", (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === "z") {
    e.preventDefault();
    undo();
  }
  if (e.key === "p" && document.activeElement === document.body) setTool("pen");
  if (e.key === "e" && document.activeElement === document.body)
    setTool("eraser");
  if (e.key === "f" && document.activeElement === document.body)
    setTool("fill");
});

let rzTimer;
window.addEventListener("resize", () => {
  clearTimeout(rzTimer);
  rzTimer = setTimeout(() => {
    const prev = hist.length ? hist[hist.length - 1] : null;
    initCv();
    if (prev) {
      const img = new Image();
      img.src = prev;
      img.onload = () => ctx.drawImage(img, 0, 0);
    }
  }, 200);
});


buildColors();
buildShapes();
buildBg();
initCv();
pushHist();
updSz();
