var NOSE_BOT = 362;
var NOSE_H = 187;

var TRI = {
  apex: { x: 200, y: 42 },
  botLeft: { x: 10, y: 478 },
  botRight: { x: 390, y: 478 },
};

var PHASES = [
  {
    id: "inhale",
    label: "استنشق",
    instr: "تنفّس ببطء وعمق من أنفك",
    dur: 4,
    color: "#0d9488",
    stroke: "#0d948899",
    dotFrom: { x: 10, y: 478 },
    dotTo: { x: 200, y: 42 },
    fillFrom: 0,
    fillTo: 1,
    arrows: "up",
  },
  {
    id: "hold",
    label: " احبس نفسك",
    instr: "احبس الهواء بداخلك ",
    dur: 7,
    color: "#6366f1",
    stroke: "#6366f199",
    dotFrom: { x: 200, y: 42 },
    dotTo: { x: 390, y: 478 },
    fillFrom: 1,
    fillTo: 1,
    arrows: "none",
  },
  {
    id: "exhale",
    label: " أخرج الهواء",
    instr: "أخرج الهواء ببطء من فمك",
    dur: 8,
    color: "#f59e0b",
    stroke: "#f59e0b99",
    dotFrom: { x: 390, y: 478 },
    dotTo: { x: 10, y: 478 },
    fillFrom: 1,
    fillTo: 0,
    arrows: "down",
  },
];

var dotEl = document.getElementById("dot");
var fillRectEl = document.getElementById("fillRect");
var triPolyEl = document.getElementById("triPoly");
var phaseLblEl = document.getElementById("svgPhaseLabel");
var timerEl = document.getElementById("svgTimer");
var instrBoxEl = document.getElementById("instrBox");
var arrowsLEl = document.getElementById("arrowsLeft");
var arrowsREl = document.getElementById("arrowsRight");
var startAreaEl = document.getElementById("startArea");
var retryAreaEl = document.getElementById("retryArea");
var startBtn = document.getElementById("startBtn");
var retryBtn = document.getElementById("retryBtn");
var sound = document.getElementById("breathSound");

var phaseIdx = 0;
var phaseStart = 0;
var raf = null;
var running = false;

function lerp(a, b, t) {
  return a + (b - a) * t;
}
function easeInOut(t) {
  return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
}

function setDot(x, y) {
  dotEl.setAttribute("cx", x.toFixed(2));
  dotEl.setAttribute("cy", y.toFixed(2));
}

function setFillLevel(level) {
  var lvl = Math.max(0, Math.min(1, level));
  var h = NOSE_H * lvl;
  var y = NOSE_BOT - h;
  fillRectEl.setAttribute("y", y.toFixed(2));
  fillRectEl.setAttribute("height", (h + 2).toFixed(2));
  fillRectEl.setAttribute("opacity", lvl > 0.005 ? "0.82" : "0");
}

function setArrows(dir, color) {
  if (dir === "none") {
    arrowsLEl.style.opacity = "0";
    arrowsREl.style.opacity = "0";
    return;
  }
  var sym = dir === "up" ? "↑" : "↓";
  [arrowsLEl, arrowsREl].forEach(function (g) {
    g.style.opacity = "1";
    g.querySelectorAll("text").forEach(function (t) {
      t.textContent = sym;
      t.setAttribute("fill", color);
    });
  });
}

function beginPhase(idx, ts) {
  phaseIdx = idx;
  phaseStart = ts;
  var ph = PHASES[idx];
  phaseLblEl.textContent = ph.label;
  phaseLblEl.setAttribute("fill", ph.color);
  instrBoxEl.textContent = ph.instr;
  instrBoxEl.style.color = ph.color;
  timerEl.setAttribute("fill", ph.color);
  timerEl.setAttribute("opacity", "1");
  timerEl.textContent = "1s";
  dotEl.setAttribute("fill", ph.color);
  setDot(ph.dotFrom.x, ph.dotFrom.y);
  triPolyEl.setAttribute("stroke", ph.stroke);
  fillRectEl.setAttribute("fill", ph.color);
  setArrows(ph.arrows, ph.color);
  setFillLevel(ph.fillFrom);
}

function gameLoop(ts) {
  if (!running) return;
  var ph = PHASES[phaseIdx];
  var elapsed = (ts - phaseStart) / 1000;
  var progress = Math.min(elapsed / ph.dur, 1);
  var eased = easeInOut(progress);
  setDot(
    lerp(ph.dotFrom.x, ph.dotTo.x, eased),
    lerp(ph.dotFrom.y, ph.dotTo.y, eased),
  );
  setFillLevel(lerp(ph.fillFrom, ph.fillTo, eased));
  timerEl.textContent = Math.min(Math.floor(elapsed) + 1, ph.dur) + "s";
  if (progress >= 1) {
    var next = phaseIdx + 1;
    if (next >= PHASES.length) {
      running = false;
      sound.pause();
      sound.currentTime = 0;
      timerEl.setAttribute("opacity", "0");
      setArrows("none", "#fff");
      setFillLevel(0);
      instrBoxEl.textContent = " أحسنت! أنهيت تمرين التنفس!";
      instrBoxEl.style.color = "#0d9488";
      phaseLblEl.textContent = " انتهى!";
      phaseLblEl.setAttribute("fill", "#0d9488");
      triPolyEl.setAttribute("stroke", "#0d948899");
      retryAreaEl.classList.remove("hidden");
      retryAreaEl.querySelector(".btn-retry").classList.add("success-anim");
      return;
    } else {
      beginPhase(next, ts);
    }
  }
  raf = requestAnimationFrame(gameLoop);
}

function startGame() {
  running = true;
  phaseIdx = 0;
  sound.currentTime = 0;
  sound.play().catch(function () {});
  startAreaEl.classList.add("hidden");
  retryAreaEl.classList.add("hidden");
  beginPhase(0, performance.now());
  raf = requestAnimationFrame(gameLoop);
}

function resetGame() {
  running = false;
  if (raf) cancelAnimationFrame(raf);
  sound.pause();
  sound.currentTime = 0;
  setFillLevel(0);
  setDot(TRI.botLeft.x, TRI.botLeft.y);
  dotEl.setAttribute('fill', '#0f766e');
  phaseLblEl.textContent = "استنشق";
  phaseLblEl.setAttribute("fill", "#0f766e");
  timerEl.setAttribute("opacity", "0");
  instrBoxEl.textContent = "اضغط على الزر لنبدأ ";
  instrBoxEl.style.color = "#64748b";
  triPolyEl.setAttribute("stroke", "#9dd8d4");
  setArrows("none", "#fff");
  retryAreaEl.classList.add("hidden");
  retryAreaEl.querySelector(".btn-retry").classList.remove("success-anim");
  startAreaEl.classList.remove("hidden");
}

setFillLevel(0);
setDot(TRI.botLeft.x, TRI.botLeft.y);
startBtn.addEventListener("click", startGame);
retryBtn.addEventListener("click", resetGame);

document.getElementById('backBtn').addEventListener('click', function() {
  window.location.href = '../index.php'; 
 });
