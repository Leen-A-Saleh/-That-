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

let level = 1;
let pins = 5;
let correctImage = null;
let points = 0;
const POINTS_PER_LEVEL = 10;

const left = document.getElementById("left");
const right = document.getElementById("right");
const modal = document.getElementById("gameOverModal");
const scoreDisplay = document.getElementById("score");

const bgMusic = new Audio("./assest/bg-music.mp3");
bgMusic.loop = true;
bgMusic.volume = 0.2;

const winSound = new Audio("./assest/win.mp3");
const loseSound = new Audio("./assest/lose-sound.mp3");

bgMusic.play().catch((e) => console.log("Autoplay blocked", e));

const BOARD_SIZE = 500;
const IMAGE_SIZE = 50;

const GRID_ROWS = Math.floor(BOARD_SIZE / IMAGE_SIZE);
const GRID_COLS = Math.floor(BOARD_SIZE / IMAGE_SIZE);

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

  left.onclick = function (e) {
    if (e.target === correctImage) {
      points += POINTS_PER_LEVEL;
      level++;
      pins += 2;

      winSound.currentTime = 0;
      winSound.play();

      startGame();
    } else {
      modal.style.display = "flex";
      points = 0;
      loseSound.currentTime = 0;
      loseSound.play();
    }
  };
}

function restartGame() {
  level = 1;
  pins = 5;
  points = 0;
  modal.style.display = "none";

  bgMusic.currentTime = 0;
  bgMusic.play();

  startGame();
}

function retryGame() {
  level = 1;
  pins = 5;
  points = 0;
  modal.style.display = "none";

  bgMusic.currentTime = 0;
  bgMusic.play();

  startGame();
}

 

left.style.width = BOARD_SIZE + "px";
left.style.height = BOARD_SIZE + "px";
right.style.width = BOARD_SIZE + "px";
right.style.height = BOARD_SIZE + "px";

startGame();

document.getElementById('backBtn').addEventListener('click', function() {
  window.location.href = '../index.html'; 
 });