const logoutBtn = document.getElementById("logoutBtn");
const games = [
  {
    title: "تمرين التنفس",
    desc: "تهدئة القلق",
    icon: "fa-wind",
    link: "breathing-game/index.php",
    type: "relax",
  },
  {
    title: "لعبة الذاكرة",
    desc: "تقوية التركيز",
    icon: "fa-brain",
    link: "memory-game/index.php",
    type: "memory",
  },
  {
    title: "كلمات متقاطعة",
    desc: "تنشيط الدماغ",
    icon: "fa-puzzle-piece",
    link: "crossword-game/index.php",
    type: "brain",
  },
  {
    title: "اختلاف الصور",
    desc: "قوة الملاحظة",
    icon: "fa-eye",
    link: "difference-game/index.php",
    type: "focus",
  },
  {
    title: "حدد المكان",
    desc: "لعبة ممتعة",
    icon: "fa-map-marker",
    link: "misplacedpin/index.php",
    type: "focus",
  },
  {
    title: "ترتيب الصور",
    desc: "تنمية التفكير",
    icon: "fa-images",
    link: "sorting-game/index.php",
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

