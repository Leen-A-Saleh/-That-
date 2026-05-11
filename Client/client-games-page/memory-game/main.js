let firstCard = null;
let lockBoard = false;
let matchedCount = 0;
let moves = 0;
let timer = 0;
let interval = null;
let timerStarted = false;
let size = 4;

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
  clearInterval(interval);
  timer = 0;
  moves = 0;
  matchedCount = 0;
  firstCard = null;
  lockBoard = false;
  timerStarted = false;
  resultDisplay.textContent = "";

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
  if (lockBoard || card.classList.contains("flipped")) return;

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
        clearInterval(interval);

        finalTime.textContent = `الوقت المستغرق: ${timer} ثانية`;
        finalMoves.textContent = `عدد المحاولات: ${moves}`;
        winPopup.style.display = "flex";
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

startGame();

playAgainBtn.addEventListener("click", () => {
  winPopup.style.display = "none";
  startGame();
});

document.getElementById("backBtn").addEventListener("click", function () {
  window.location.href = "../index.php";
});
