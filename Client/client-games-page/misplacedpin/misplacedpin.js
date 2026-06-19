const pokemon = [
  "abra",
  "azumarill",
  "charmander",
  "chikorita",
  "dragonite",
  "golem",
  "ponyta",
  "shroomish",
  "spearow",
  "vaporeon",
  "weezing",
  "zubat",
];

const API_URL = "misplacedpin-database.php";
const POINTS_PER_LEVEL = 10;
const COMPLETION_LEVEL = 30;
const BOARD_SIZE = 500;
const IMAGE_SIZE = 50;
const GRID_ROWS = Math.floor(BOARD_SIZE / IMAGE_SIZE);
const GRID_COLS = Math.floor(BOARD_SIZE / IMAGE_SIZE);

let level = 1;
let pins = 5;
let correctImage = null;
let points = 0;
let gameEnded = false;
let finalSaveStarted = false;
let finalSaveCompleted = false;
let sessionRunId = 0;
let gameSession;

const left = document.getElementById("left");
const right = document.getElementById("right");
const modal = document.getElementById("gameOverModal");
const scoreDisplay = document.getElementById("score");
const saveStatus = document.getElementById("saveStatus");
const historyModal = document.getElementById("historyModal");
const historyList = document.getElementById("historyList");

const bgMusic = new Audio("./assest/bgMusic.mp3");
bgMusic.loop = true;
bgMusic.volume = 0.2;

const winSound = new Audio("./assest/win.mp3");
const loseSound = new Audio("./assest/lose-sound.mp3");

bgMusic.play().catch(() => {});

function resetSession() {
  sessionRunId++;
  level = 1;
  pins = 5;
  points = 0;
  gameEnded = false;
  finalSaveStarted = false;
  finalSaveCompleted = false;
  gameSession = {
    highestLevel: 0,
    points: 0,
    isCompleted: false,
  };
}

function generatePositions(count) {
  const allCells = [];
  for (let r = 0; r < GRID_ROWS; r++) {
    for (let c = 0; c < GRID_COLS; c++) {
      allCells.push({ top: r * IMAGE_SIZE + 5, left: c * IMAGE_SIZE + 5 });
    }
  }

  for (let i = allCells.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [allCells[i], allCells[j]] = [allCells[j], allCells[i]];
  }
  return allCells.slice(0, count);
}

function startGame() {
  document.getElementById("level").innerText = "Level " + level;
  scoreDisplay.innerText = "Points: " + points;

  left.innerHTML = "";
  right.innerHTML = "";

  const positions = generatePositions(pins);
  let images = [];

  for (let i = 0; i < pins; i++) {
    let img = document.createElement("img");
    let randomPokemon = pokemon[Math.floor(Math.random() * pokemon.length)];
    img.src = `https://img.pokemondb.net/sprites/home/normal/${randomPokemon}.png`;

    img.style.top = positions[i].top + "px";
    img.style.left = positions[i].left + "px";
    img.style.width = IMAGE_SIZE + "px";
    img.style.height = IMAGE_SIZE + "px";

    left.appendChild(img);
    images.push(img);
  }

  correctImage = images[images.length - 1];
  for (let i = 0; i < images.length - 1; i++) {
    let clone = images[i].cloneNode(true);
    right.appendChild(clone);
  }
}

left.onclick = function (e) {
  if (gameEnded) return;

  if (e.target === correctImage) {
    recordSuccessfulLevel();

    winSound.currentTime = 0;
    winSound.play();

    if (gameSession.isCompleted) {
      completeGame();
      return;
    }

    startGame();
    return;
  }

  endGame();
};

function recordSuccessfulLevel() {
  points += POINTS_PER_LEVEL;
  gameSession.highestLevel = level;
  gameSession.points = points;
  gameSession.isCompleted = gameSession.highestLevel >= COMPLETION_LEVEL;

  level++;
  pins += 2;
}

function endGame() {
  gameEnded = true;
  modal.style.display = "flex";
  const modalTitle = modal.querySelector("h2");
  if (modalTitle) modalTitle.textContent = "Game Over!";
  loseSound.currentTime = 0;
  loseSound.play();
  saveFinalResult();
}

function completeGame() {
  gameEnded = true;
  modal.style.display = "flex";
  const modalTitle = modal.querySelector("h2");
  if (modalTitle) modalTitle.textContent = "You Win!";
  if (saveStatus) saveStatus.textContent = "";
  bgMusic.pause();
  saveFinalResult();
}

function saveFinalResult() {
  if (finalSaveStarted || finalSaveCompleted) return;

  const runId = sessionRunId;
  finalSaveStarted = true;
  if (saveStatus) saveStatus.textContent = "جاري حفظ النتيجة...";

  misplacedpinApi("save", {
    level: gameSession.highestLevel,
    points: gameSession.points,
  })
    .then(() => {
      if (runId !== sessionRunId) return;
      finalSaveCompleted = true;
      if (saveStatus) saveStatus.textContent = "تم حفظ النتيجة";
    })
    .catch((error) => {
      if (runId !== sessionRunId) return;
      console.error(error);
      if (saveStatus) saveStatus.textContent = "تعذر حفظ النتيجة";
    })
    .finally(() => {
      if (runId !== sessionRunId) return;
      finalSaveStarted = false;
    });
}

function restartGame() {
  resetSession();
  modal.style.display = "none";
  if (saveStatus) saveStatus.textContent = "";

  bgMusic.currentTime = 0;
  bgMusic.play();

  startGame();
}

function retryGame() {
  restartGame();
}

async function showHistory() {
  historyModal.style.display = "flex";
  historyList.innerHTML = '<p style="color:#777;">جاري تحميل السجل...</p>';

  try {
    const data = await misplacedpinGet("history");
    const sessions = data.sessions || [];

    if (sessions.length === 0) {
      historyList.innerHTML = '<p style="color:#777;">لا يوجد سجل بعد</p>';
      return;
    }

    historyList.innerHTML = "";
    sessions.forEach((session) => {
      const item = document.createElement("div");
      item.className = "history-item";
      item.innerHTML = `
        <span>Level ${escapeHtml(session.level || 0)} | Points: ${escapeHtml(session.points || 0)}</span>
        <span>${Number(session.is_completed) === 1 ? "مكتملة" : "غير مكتملة"}</span>
        <small>${escapeHtml(session.played_at || "")}</small>
      `;
      historyList.appendChild(item);
    });
  } catch (error) {
    console.error(error);
    historyList.innerHTML = '<p style="color:#777;">تعذر تحميل السجل</p>';
  }
}

function hideHistory() {
  historyModal.style.display = "none";
}

async function misplacedpinApi(action, payload) {
  const response = await fetch(`${API_URL}?action=${encodeURIComponent(action)}&_=${Date.now()}`, {
    method: "POST",
    credentials: "same-origin",
    cache: "no-store",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });
  const data = await response.json().catch(() => ({}));

  if (!response.ok || !data.success) {
    throw new Error(data.message || "Misplacedpin result could not be saved.");
  }

  return data;
}

async function misplacedpinGet(action) {
  const response = await fetch(`${API_URL}?action=${encodeURIComponent(action)}&_=${Date.now()}`, {
    method: "GET",
    credentials: "same-origin",
    cache: "no-store",
  });
  const data = await response.json().catch(() => ({}));

  if (!response.ok || !data.success) {
    throw new Error(data.message || "Misplacedpin request failed.");
  }

  return data;
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

left.style.width = BOARD_SIZE + "px";
left.style.height = BOARD_SIZE + "px";
right.style.width = BOARD_SIZE + "px";
right.style.height = BOARD_SIZE + "px";

resetSession();
startGame();

document.getElementById("backBtn").addEventListener("click", function () {
  window.location.href = "../index.php";
});
