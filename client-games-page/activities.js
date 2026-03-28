const games = [
  {
    title: "تمرين التنفس",
    desc: "تهدئة القلق",
    icon: "fa-wind",
    link: "breathing-game/index.html",
    type: "relax",
  },
  {
    title: "لعبة الذاكرة",
    desc: "تقوية التركيز",
    icon: "fa-brain",
    link: "memory-game/index.html",
    type: "memory",
  },
  {
    title: "كلمات متقاطعة",
    desc: "تنشيط الدماغ",
    icon: "fa-puzzle-piece",
    link: "crossword-game/index.html",
    type: "brain",
  },
  {
    title: "اختلاف الصور",
    desc: "قوة الملاحظة",
    icon: "fa-eye",
    link: "difference-game/index.html",
    type: "focus",
  },
  {
    title: "حدد المكان",
    desc: "لعبة ممتعة",
    icon: "fa-map-marker",
    link: "misplacedpin/index.html",
    type: "focus",
  },
  {
    title: "ترتيب الصور",
    desc: "تنمية التفكير",
    icon: "fa-images",
    link: "sorting-game/index.html",
    type: "memory",
  },
];

const container = document.getElementById("gamesContainer");

function renderGames() {
  container.innerHTML = games
    .map(
      (g) => `
    <div class="game-card">

      <div class="icon-circle">
        <i class="fa ${g.icon}"></i>
      </div>

      <h3>${g.title}</h3>
      <p>${g.desc}</p>

      <button class="start-btn" onclick="startGame('${g.link}')">
        ابدأ اللعب 🎮
      </button>

    </div>
  `,
    )
    .join("");
}

function startGame(link) {
  window.location.href = link;
}

renderGames();

const uploadArea = document.getElementById("uploadArea");
const fileInput = document.getElementById("fileInput");

uploadArea.onclick = () => fileInput.click();

fileInput.addEventListener("change", () => {
  console.log(fileInput.files);
});

if (logoutBtn) {
  logoutBtn.addEventListener("click", function () {
    localStorage.removeItem("token");

    window.location.href = "login.html";
  });
}
