const logoutBtn = document.getElementById("logoutBtn");
const games = [
  {
    title: "تمرين التنفس",
    desc: "تهدئة القلق",
    icon: "fa-wind",
    link: "loader.php?game=./breathing-game/index.php&key=Breathing",
    type: "relax",
  },
  {
    title: "لعبة الذاكرة",
    desc: "تقوية التركيز",
    icon: "fa-brain",
    link: "loader.php?game=./memory-game/index.php&key=Cards",
    type: "memory",
  },
  {
    title: "كلمات متقاطعة",
    desc: "تنشيط الدماغ",
    icon: "fa-puzzle-piece",
    link: "loader.php?game=./crossword-game/index.php&key=Crossword",
    type: "brain",
  },
  {
    title: "اختلاف الصور",
    desc: "قوة الملاحظة",
    icon: "fa-eye",
    link: "loader.php?game=./difference-game/index.php&key=Difference",
    type: "focus",
  },
  {
    title: "حدد المكان",
    desc: "لعبة ممتعة",
    icon: "fa-map-marker",
    link: "loader.php?game=./misplacedpin/index.php&key=Misplacedpin",
    type: "focus",
  },
  {
    title: "ترتيب الصور",
    desc: "تنمية التفكير",
    icon: "fa-images",
    link: "loader.php?game=./Questions/index.php&key=Questions",
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
const fileInput = document.getElementById("fileInput");
async function loadStats() {
  try {
    const res = await fetch("/That-Copy/Client/client-games-page/activities.php?action=stats", {
      headers: {
        Authorization: "Bearer " + localStorage.getItem("token"),
      },
    });

    const data = await res.json();

    document.getElementById("totalCases").textContent = data.completed;
    document.getElementById("activeCases").textContent = data.pending;
    document.getElementById("todayAppointments").textContent = data.total;
  } catch (err) {
    console.log("فشل تحميل الإحصائيات");
  }
}

loadStats();

