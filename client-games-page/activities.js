const logoutBtn = document.getElementById("logoutBtn");
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
const fileInput = document.getElementById("fileInput");
async function loadStats() {
  try {
    const res = await fetch("/api/activities/stats", {
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

if (logoutBtn) {
  logoutBtn.addEventListener("click", function () {
    localStorage.removeItem("token");

    window.location.href = "login.html";
  });
}
