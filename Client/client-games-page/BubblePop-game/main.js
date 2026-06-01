const COLORS = [
  {
    fill: "rgba(237,100,166,0.85)",
    border: "rgba(200,60,130,0.75)",
    pt: "#ED64A6",
  },
  {
    fill: "rgba(99,170,235,0.85)",
    border: "rgba(60,130,210,0.75)",
    pt: "#63AAeb",
  },
  {
    fill: "rgba(72,210,160,0.85)",
    border: "rgba(30,170,120,0.75)",
    pt: "#48D2A0",
  },
  {
    fill: "rgba(160,130,230,0.85)",
    border: "rgba(120,90,200,0.75)",
    pt: "#A082E6",
  },
  {
    fill: "rgba(250,170,60,0.85)",
    border: "rgba(210,130,20,0.75)",
    pt: "#FAAA3C",
  },
  {
    fill: "rgba(80,200,210,0.85)",
    border: "rgba(40,160,170,0.75)",
    pt: "#50C8D2",
  },
  {
    fill: "rgba(230,120,140,0.85)",
    border: "rgba(190,80,100,0.75)",
    pt: "#E6788C",
  },
  {
    fill: "rgba(130,200,100,0.85)",
    border: "rgba(90,160,60,0.75)",
    pt: "#82C864",
  },
  {
    fill: "rgba(190,100,210,0.85)",
    border: "rgba(150,60,180,0.75)",
    pt: "#BE64D2",
  },
  {
    fill: "rgba(240,180,60,0.85)",
    border: "rgba(200,140,20,0.75)",
    pt: "#F0B43C",
  },
  {
    fill: "rgba(100,160,240,0.85)",
    border: "rgba(60,120,210,0.75)",
    pt: "#64A0F0",
  },
  {
    fill: "rgba(220,100,100,0.85)",
    border: "rgba(180,60,60,0.75)",
    pt: "#DC6464",
  },
];

const RING_C = 2 * Math.PI * 20;
const MAX_TIME = 30;

let gameOn = false,
  paused = false;
let score = 0,
  best = parseInt(localStorage.getItem("bbest2") || "0");
let timeLeft = MAX_TIME;
let combo = 1,
  bestCombo = 1,
  pops = 0;
let comboTO = null,
  spawnTO = null,
  timerIV = null;
let bubbles = [];

const canvas = document.getElementById("canvas");
const ctx = canvas.getContext("2d");

const $home = document.getElementById("screen-home");
const $game = document.getElementById("screen-game");
const $over = document.getElementById("screen-over");
const $score = document.getElementById("hud-score");
const $time = document.getElementById("hud-time");
const $combo = document.getElementById("hud-combo");
const $ring = document.getElementById("tr-fg");
const $fx = document.getElementById("fx-layer");

let BG = [];

function resizeCanvas() {
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
}

function initBG() {
  resizeCanvas();
  BG = [];
  const W = canvas.width,
    H = canvas.height;
  const n = Math.min(28, Math.floor((W * H) / 20000));
  for (let i = 0; i < n; i++) {
    const c = COLORS[Math.floor(Math.random() * COLORS.length)];
    BG.push({
      x: Math.random() * W,
      y: Math.random() * H,
      r: 18 + Math.random() * 65,
      vx: (Math.random() - 0.5) * 0.18,
      vy: -(0.05 + Math.random() * 0.14),
      fill: c.fill.replace("0.85", "0.22"),
      border: c.border.replace("0.75", "0.15"),
      phase: Math.random() * Math.PI * 2,
    });
  }
}

function drawBG(t) {
  const W = canvas.width,
    H = canvas.height;
  ctx.clearRect(0, 0, W, H);
  ctx.fillStyle = "#ffffff";
  ctx.fillRect(0, 0, W, H);

  for (const b of BG) {
    b.x += b.vx + Math.sin(t * 0.0004 + b.phase) * 0.09;
    b.y += b.vy;
    if (b.y + b.r < 0) {
      b.y = H + b.r;
      b.x = Math.random() * W;
    }
    if (b.x + b.r < 0) b.x = W + b.r;
    if (b.x - b.r > W) b.x = -b.r;

    ctx.beginPath();
    ctx.arc(b.x, b.y, b.r, 0, Math.PI * 2);
    ctx.fillStyle = b.fill;
    ctx.fill();
    ctx.strokeStyle = b.border;
    ctx.lineWidth = 1;
    ctx.stroke();

    ctx.save();
    ctx.translate(b.x - b.r * 0.28, b.y - b.r * 0.3);
    ctx.rotate(-0.4);
    ctx.scale(1, 0.55);
    ctx.beginPath();
    ctx.arc(0, 0, b.r * 0.22, 0, Math.PI * 2);
    ctx.fillStyle = "rgba(255,255,255,0.45)";
    ctx.fill();
    ctx.restore();
  }
}

function loop(t) {
  requestAnimationFrame(loop);
  drawBG(t);
}

function showScreen(name) {
  $home.classList.toggle("active", name === "home");
  $game.classList.toggle("active", name === "game");
  $over.classList.toggle("active", name === "over");
}

function startGame() {
  clearBubbles();
  $fx.innerHTML = "";
  score = 0;
  combo = 1;
  bestCombo = 1;
  pops = 0;
  timeLeft = MAX_TIME;
  $score.textContent = "0";
  $time.textContent = MAX_TIME;
  $combo.textContent = "x1";
  $combo.classList.remove("fire");
  $ring.style.strokeDashoffset = "0";
  $ring.classList.remove("warn", "crit");
  showScreen("game");
  gameOn = true;
  paused = false;
  spawnBatch(10);
  scheduleSpawn();
  startTimer();
}

function startTimer() {
  clearInterval(timerIV);
  timerIV = setInterval(() => {
    if (!gameOn || paused) return;
    timeLeft--;
    $time.textContent = timeLeft;
    const pct = timeLeft / MAX_TIME;
    $ring.style.strokeDashoffset = RING_C * (1 - pct);
    if (pct <= 0.2) {
      $ring.classList.add("crit");
      $ring.classList.remove("warn");
    } else if (pct <= 0.45) {
      $ring.classList.add("warn");
    }
    if (timeLeft <= 0) endGame();
  }, 1000);
}

function scheduleSpawn() {
  if (!gameOn || paused) return;
  const prog = (MAX_TIME - timeLeft) / MAX_TIME;
const iv = Math.max(180, 320 - prog * 100);
  spawnTO = setTimeout(
    () => {
      if (gameOn && !paused) {
        makeBubble();
        scheduleSpawn();
      }
    },
    iv + (Math.random() - 0.5) * 150,
  );
}

function spawnBatch(n) {
  for (let i = 0; i < n; i++) {
    setTimeout(() => {
      if (gameOn) makeBubble();
    }, i * 220);
  }
}

function makeBubble() {
  if (!gameOn || paused) return;
  const W = window.innerWidth;
  const H = window.innerHeight;
  const hudH = document.getElementById("hud").getBoundingClientRect().height;

  const prog = (MAX_TIME - timeLeft) / MAX_TIME;
  const szMin = 58,
    szMax = 105;
  const sz = szMin + Math.random() * (szMax - szMin);
  const mg = 10;
  const x = mg + Math.random() * Math.max(0, W - sz - mg * 2);
  const col = COLORS[Math.floor(Math.random() * COLORS.length)];
  const spd = Math.max(4.5, 8.0 - prog * 3.5) + Math.random() * 1.0;
  const sway = (Math.random() - 0.5) * 44;
  const id = "b" + Date.now() + Math.random().toString(36).slice(2);

  const el = document.createElement("div");
  el.className = "bubble";
  el.id = id;

  const startY = H + sz;
  const dist = startY - (hudH - sz - 10);

  el.style.cssText = `
    width:${sz}px; height:${sz}px;
    left:${x}px; top:${startY}px;
    animation: bubble-rise ${spd}s ease-in forwards,
               bubble-wobble ${1.8 + Math.random() * 0.9}s ease-in-out infinite;
    --dy:${-dist}px;
    --dx:${sway}px;
  `;

  el.innerHTML = `
    <div class="binner" style="
      background:${col.fill};
      border:2.5px solid ${col.border};
      box-shadow:inset -4px -4px 12px rgba(0,0,0,0.1),
                 inset 4px 4px 10px rgba(255,255,255,0.55),
                 0 4px 18px ${col.pt}55;
    ">
      <div class="bshine"></div>
      <div class="bshine2"></div>
    </div>
  `;

  el.dataset.pt = col.pt;
  el.dataset.sz = sz;
  el.dataset.popped = "0";

  el.addEventListener("pointerdown", onPop, { once: true });

  document.body.appendChild(el);
  bubbles.push(id);

  el.addEventListener("animationend", (e) => {
    if (e.animationName === "bubble-rise") removeBubble(id);
  });
}

function onPop(e) {
  e.preventDefault();
  e.stopPropagation();
  if (!gameOn || paused) return;

  const el = e.currentTarget;
  if (el.dataset.popped === "1") return;
  el.dataset.popped = "1";

  const id = el.id;
  const r = el.getBoundingClientRect();
  const cx = r.left + r.width / 2;
  const cy = r.top + r.height / 2;
  const sz = parseFloat(el.dataset.sz);
  const pt = el.dataset.pt;

  el.style.animation = "none";
  el.style.pointerEvents = "none";
  el.classList.add("bpop");

  spawnParticles(cx, cy, pt, sz);
  combo++;
  bestCombo = Math.max(bestCombo, combo);
  pops++;
  const pts = combo <= 2 ? 1 : combo <= 4 ? 2 : combo <= 7 ? 3 : 5;

  score += pts;

  $score.textContent = score;
  $score.classList.remove("pop");
  void $score.offsetWidth;
  $score.classList.add("pop");

  $combo.textContent = "x" + combo;
  $combo.classList.toggle("fire", combo >= 4);

  if (comboTO) clearTimeout(comboTO);
  comboTO = setTimeout(() => {
    combo = 1;
    $combo.textContent = "x1";
    $combo.classList.remove("fire");
  }, 1600);

  setTimeout(() => removeBubble(id), 280);
}

function removeBubble(id) {
  const el = document.getElementById(id);
  if (el) el.remove();
  bubbles = bubbles.filter((b) => b !== id);
}

function clearBubbles() {
  bubbles.forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.remove();
  });
  bubbles = [];
}

function spawnParticles(cx, cy, color, sz) {
  const n = Math.floor(6 + sz / 12);
  for (let i = 0; i < n; i++) {
    const p = document.createElement("div");
    p.className = "ptc";
    const angle = (Math.PI * 2 * i) / n + Math.random() * 0.5;
    const dist = 30 + Math.random() * sz * 0.55;
    p.style.cssText = `
      width:${5 + Math.random() * 6}px;
      height:${5 + Math.random() * 6}px;
      background:${color};
      left:${cx}px; top:${cy}px;
      --tx:${Math.cos(angle) * dist}px;
      --ty:${Math.sin(angle) * dist}px;
      --d:${0.4 + Math.random() * 0.35}s;
      opacity:0.9;
    `;
    $fx.appendChild(p);
    setTimeout(() => p.remove(), 800);
  }
}

function endGame() {
  gameOn = false;
  clearInterval(timerIV);
  clearTimeout(spawnTO);
  clearTimeout(comboTO);

  const isRec = score > best;
  if (isRec) {
    best = score;
    localStorage.setItem("bbest2", best);
  }

  const levels = [
    [0, "حاول مجدداً 💪"],
    [8, "جيد جداً 🌟"],
    [20, "رائع! 🎉"],
    [45, "مذهل! 🚀"],
    [75, "أسطورة! 🏆"],
    [110, "خارق! ⚡"],
  ];
  const emojis = [
    [0, "😊"],
    [8, "🎉"],
    [20, "🌟"],
    [45, "🏆"],
    [75, "🔥"],
    [110, "⚡"],
  ];
  let title = levels[0][1],
    em = emojis[0][1];
  for (const [t, v] of levels) if (score >= t) title = v;
  for (const [t, v] of emojis) if (score >= t) em = v;

  document.getElementById("over-emoji").textContent = em;
  document.getElementById("over-title").textContent = title;
  document.getElementById("over-score").textContent = score;
  document
    .getElementById("new-rec")
    .classList.toggle("hidden", !(isRec && score > 0));

  setTimeout(() => showScreen("over"), 260);
}

function goHome() {
  gameOn = false;
  paused = false;
  clearInterval(timerIV);
  clearTimeout(spawnTO);
  clearTimeout(comboTO);
  clearBubbles();
  $fx.innerHTML = "";
  showScreen("home");
}

window.addEventListener("resize", initBG);
document.addEventListener("visibilitychange", () => {
  if (document.hidden && gameOn && !paused) {
    paused = true;
  }
});

const styleEl = document.createElement("style");
styleEl.textContent = `
@keyframes bubble-rise {
  0%   { transform:translateY(0) translateX(0); opacity:1; }
  5%   { opacity:1; }
  90%  { opacity:0.95; }
  100% { transform:translateY(var(--dy)) translateX(var(--dx)); opacity:0; }
}
@keyframes bubble-wobble {
  0%,100% { border-radius:50%; }
  30% { border-radius:54% 46% 48% 52%/46% 54% 46% 54%; }
  65% { border-radius:47% 53% 52% 48%/53% 47% 53% 47%; }
}
@keyframes hud-pop { 50% { transform:scale(1.35); } }
#hud-score.pop { animation:hud-pop 0.18s ease-out; }
`;
document.head.appendChild(styleEl);

initBG();
requestAnimationFrame(loop);
