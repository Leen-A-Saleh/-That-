let overlay = document.querySelector(".sidebar-overlay");
const menuBtn = document.querySelector(".menu-btn");
const logoutBtn = document.getElementById("logoutBtn");
const sidebar = document.querySelector(".sidebar");

if (!overlay) {
  overlay = document.createElement("div");
  overlay.className = "sidebar-overlay";
  document.body.appendChild(overlay);
}

menuBtn.addEventListener("click", () => {
  sidebar.classList.toggle("open");
  overlay.classList.toggle("open");
});

overlay.addEventListener("click", () => {
  sidebar.classList.remove("open");
  overlay.classList.remove("open");
});

if (logoutBtn) {
  logoutBtn.addEventListener("click", function () {
    localStorage.removeItem("token");
    window.location.href = "../login-page/login.html";
  });
}

const API = {
  stats: "/api/admin/stats",
  weekly: "/api/admin/weekly",
  growth: "/api/admin/growth",
  activities: "/api/admin/activities",
  specialists: "/api/admin/specialists",
};

async function fetchData(url) {
  try {
    const res = await fetch(url);
    return await res.json();
  } catch {
    return null;
  }
}

async function loadStats() {
  const data = await fetchData(API.stats);
  if (!data) return;

  const values = document.querySelectorAll(".stat-value");
  values[0].textContent = data.activeUsers;
  values[1].textContent = data.therapists;
  values[2].textContent = data.sessions;
  values[3].textContent = data.tests + "%";
  values[4].textContent = data.revenue;
  values[5].textContent = data.rating + "/5";
}

async function loadActivities() {
  const data = await fetchData(API.activities);
  if (!data) return;

  const container = document.querySelector(".activities-list");
  container.innerHTML = "";

  data.forEach((item) => {
    container.innerHTML += `
      <div class="act-item">
        <span class="act-dot"></span>
        <div class="act-body">
          <p class="act-title">${item.title}</p>
          <span class="act-time">${item.time}</span>
        </div>
      </div>
    `;
  });
}

async function loadSpecialists() {
  const data = await fetchData(API.specialists);
  if (!data) return;

  const container = document.querySelector(".specialists-list");
  container.innerHTML = "";

  data.forEach((item, index) => {
    container.innerHTML += `
      <div class="spec-item">
        <div class="spec-rank">${index + 1}</div>
        <div class="spec-info">
          <p class="spec-name">${item.name}</p>
          <span class="spec-sess">${item.sessions} جلسة</span>
        </div>
        <div class="spec-rating">
          <i class="fas fa-star"></i><span>${item.rating}</span>
        </div>
      </div>
    `;
  });
}

let weeklyChart;
let growthChart;

async function loadCharts() {
  const weeklyData = await fetchData(API.weekly);
  const growthData = await fetchData(API.growth);

  const weeklyCtx = document.getElementById("weeklyChart").getContext("2d");
  const growthCtx = document.getElementById("growthChart").getContext("2d");

  if (weeklyChart) weeklyChart.destroy();
  if (growthChart) growthChart.destroy();

  weeklyChart = new Chart(weeklyCtx, {
    type: "bar",
    data: {
      labels: weeklyData?.labels || [
        "السبت",
        "الأحد",
        "الاثنين",
        "الثلاثاء",
        "الأربعاء",
        "الخميس",
        "الجمعة",
      ],
      datasets: [
        {
          data: weeklyData?.data || [30, 45, 60, 40, 70, 55, 65],
          backgroundColor: "#0ea5b0",
          borderRadius: 6,
          barThickness: 20,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false } },
        y: {
          beginAtZero: true,
          ticks: { stepSize: 20 },
          grid: { color: "#eee" },
        },
      },
    },
  });

  growthChart = new Chart(growthCtx, {
    type: "line",
    data: {
      labels: growthData?.labels || [
        "يناير",
        "فبراير",
        "مارس",
        "أبريل",
        "مايو",
        "يونيو",
      ],
      datasets: [
        {
          label: "المستخدمون",
          data: growthData?.users || [50, 80, 120, 150, 200, 260],
          borderColor: "#3b82f6",
          backgroundColor: "transparent",
          tension: 0.4,
          borderWidth: 2,
          pointRadius: 4,
        },
        {
          label: "الجلسات",
          data: growthData?.sessions || [40, 60, 100, 130, 170, 220],
          borderColor: "#0ea5b0",
          backgroundColor: "transparent",
          tension: 0.4,
          borderWidth: 2,
          pointRadius: 4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, grid: { color: "#eee" } },
      },
    },
  });
}

function initDashboard() {
  loadStats();
  loadActivities();
  loadSpecialists();
  loadCharts();
}

initDashboard();
