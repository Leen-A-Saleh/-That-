const API_URL = "memory-database.php";

let firstCard = null;
let lockBoard = false;
let matchedCount = 0;
let moves = 0;
let timer = 0;
let interval = null;
let timerStarted = false;
let size = 4;
let gameSession;
let finalSaveStarted = false;
let finalSaveCompleted = false;
let sessionRunId = 0;

const game = document.getElementById("game");
const timeDisplay = document.getElementById("time");
const movesDisplay = document.getElementById("moves");
const difficultySelect = document.getElementById("difficulty");
const restartBtn = document.getElementById("restartBtn");
const resultDisplay = document.getElementById("result");
const winPopup = document.getElementById("winPopup");
const finalTime = document.getElementById("finalTime");
const finalMoves = document.getElementById("finalMoves");
const playAgainBtn = document.getElementById("playAgainBtn");
const saveStatus = document.getElementById("saveStatus");
const historyPopup = document.getElementById("historyPopup");
const historyList = document.getElementById("historyList");
const historyBtn = document.getElementById("historyBtn");
const closeHistoryBtn = document.getElementById("closeHistoryBtn");

const symbols = [
  "./images/apple.png",
  "./images/banana.png",
  "./images/blackberry.png",
  "./images/blueberry.png",
  "./images/cherry.png",
  "./images/coconut.png",
  "./images/dragonfruit.png",
  "./images/raspberry.png",
  "./images/pear.png",
  "./images/grapes.png",
  "./images/kiwi.png",
  "./images/mango.png",
  "./images/orange.png",
  "./images/pomegranate.png",
  "./images/peach.png",
  "./images/watermelon.png",
  "./images/pineapple.png",
  "./images/strawberry.png",
];

const flipSound = new Audio("sounds/flip.mp3");
const correctSound = new Audio("sounds/correct.mp3");
const wrongSound = new Audio("sounds/wrong.mp3");

flipSound.volume = 0.3;
correctSound.volume = 0.4;
wrongSound.volume = 0.3;

difficultySelect.addEventListener("change", () => {
  size = parseInt(difficultySelect.value);
  startGame();
});

restartBtn.addEventListener("click", startGame);

function startGame() {
  sessionRunId++;
  clearInterval(interval);
  timer = 0;
  moves = 0;
  matchedCount = 0;
  firstCard = null;
  lockBoard = false;
  timerStarted = false;
  finalSaveStarted = false;
  finalSaveCompleted = false;
  gameSession = {
    difficulty: difficultyForSize(size),
    time_seconds: 0,
    moves: 0,
    is_completed: false,
  };
  resultDisplay.textContent = "";
  if (saveStatus) saveStatus.textContent = "";

  timeDisplay.textContent = 0;
  movesDisplay.textContent = 0;

  game.innerHTML = "";
  game.style.gridTemplateColumns = `repeat(${size}, 1fr)`;

  if (size === 4) {
    game.style.maxWidth = "500px";
  } else if (size === 6) {
    game.style.maxWidth = "530px";
  }

  let neededPairs = (size * size) / 2;
  let selectedSymbols = symbols.slice(0, neededPairs);
  let cards = [...selectedSymbols, ...selectedSymbols];

  cards.sort(() => 0.5 - Math.random());

  cards.forEach((symbol) => {
    const card = document.createElement("div");
    card.classList.add("card");
    card.dataset.symbol = symbol;
    card.addEventListener("click", () => flipCard(card));
    game.appendChild(card);
  });
}

function startTimer() {
  if (!timerStarted) {
    timerStarted = true;
    interval = setInterval(() => {
      timer++;
      timeDisplay.textContent = timer;
    }, 1000);
  }
}

function flipCard(card) {
  if (lockBoard || card.classList.contains("flipped") || finalSaveStarted) return;

  startTimer();

  flipSound.currentTime = 0;
  flipSound.play();

  card.classList.add("flipped");
  card.innerHTML = `<img src="${card.dataset.symbol}" class="card-image">`;

  if (!firstCard) {
    firstCard = card;
  } else {
    moves++;
    movesDisplay.textContent = moves;

    if (firstCard.dataset.symbol === card.dataset.symbol) {
      correctSound.currentTime = 0;
      correctSound.play();

      firstCard.classList.add("matched");
      card.classList.add("matched");
      matchedCount += 2;

      if (matchedCount === size * size) {
        finishGame();
      }

      firstCard = null;
    } else {
      setTimeout(() => {
        wrongSound.currentTime = 0;
        wrongSound.play();
      }, 250);

      lockBoard = true;

      setTimeout(() => {
        firstCard.classList.remove("flipped");
        card.classList.remove("flipped");
        firstCard.innerHTML = "";
        card.innerHTML = "";
        firstCard = null;
        lockBoard = false;
      }, 800);
    }
  }
}

function finishGame() {
  clearInterval(interval);
  gameSession.time_seconds = timer;
  gameSession.moves = moves;
  gameSession.difficulty = difficultyForSize(size);
  gameSession.is_completed = true;

  finalTime.textContent = `الوقت المستغرق: ${timer} ثانية`;
  finalMoves.textContent = `عدد المحاولات: ${moves}`;
  winPopup.style.display = "flex";
  saveFinalResult();
}

function saveFinalResult() {
  if (finalSaveStarted || finalSaveCompleted) return;

  const runId = sessionRunId;
  finalSaveStarted = true;
  if (saveStatus) saveStatus.textContent = "جاري حفظ النتيجة...";

  memoryApi("save", {
    time_seconds: gameSession.time_seconds,
    moves: gameSession.moves,
    difficulty: gameSession.difficulty,
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

function difficultyForSize(boardSize) {
  return boardSize === 6 ? "MEDIUM" : "EASY";
}

async function showHistory() {
  historyPopup.style.display = "flex";
  historyList.innerHTML = "<p>جاري تحميل السجل...</p>";

  try {
    const data = await memoryGet("history");
    const sessions = data.sessions || [];

    if (sessions.length === 0) {
      historyList.innerHTML = "<p>لا يوجد سجل بعد</p>";
      return;
    }

    historyList.innerHTML = "";
    sessions.forEach((session) => {
      const item = document.createElement("div");
      item.className = "history-item";
      item.innerHTML = `
        <span>${difficultyLabel(session.difficulty)} | ${escapeHtml(session.time_seconds || 0)} ثانية | ${escapeHtml(session.moves || 0)} محاولة</span>
        <small>${escapeHtml(session.played_at || "")}</small>
      `;
      historyList.appendChild(item);
    });
  } catch (error) {
    console.error(error);
    historyList.innerHTML = "<p>تعذر تحميل السجل</p>";
  }
}

function hideHistory() {
  historyPopup.style.display = "none";
}

async function memoryApi(action, payload) {
  const response = await fetch(`${API_URL}?action=${encodeURIComponent(action)}&_=${Date.now()}`, {
    method: "POST",
    credentials: "same-origin",
    cache: "no-store",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });
  const data = await response.json().catch(() => ({}));

  if (!response.ok || !data.success) {
    throw new Error(data.message || "Memory result could not be saved.");
  }

  return data;
}

async function memoryGet(action) {
  const response = await fetch(`${API_URL}?action=${encodeURIComponent(action)}&_=${Date.now()}`, {
    method: "GET",
    credentials: "same-origin",
    cache: "no-store",
  });
  const data = await response.json().catch(() => ({}));

  if (!response.ok || !data.success) {
    throw new Error(data.message || "Memory request failed.");
  }

  return data;
}

function difficultyLabel(difficulty) {
  return difficulty === "MEDIUM" ? "متوسط" : "سهل";
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

startGame();

playAgainBtn.addEventListener("click", () => {
  winPopup.style.display = "none";
  startGame();
});

if (historyBtn) {
  historyBtn.addEventListener("click", showHistory);
}

if (closeHistoryBtn) {
  closeHistoryBtn.addEventListener("click", hideHistory);
}

document.getElementById("backBtn").addEventListener("click", function () {
  window.location.href = "../index.php";
});
