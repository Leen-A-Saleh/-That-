let overlay = document.querySelector(".sidebar-overlay");
const menuBtn = document.querySelector(".menu-btn");
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

// ─── Charts ───────────────────────────────────────────────────────────────────
// WEEKLY_DATA and GROWTH_DATA are injected by admin-dashboard.php as globals.

const weeklyCtx = document.getElementById("weeklyChart").getContext("2d");
const growthCtx = document.getElementById("growthChart").getContext("2d");

new Chart(weeklyCtx, {
  type: "bar",
  data: {
    labels: WEEKLY_DATA.labels,
    datasets: [
      {
        data: WEEKLY_DATA.data,
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

new Chart(growthCtx, {
  type: "line",
  data: {
    labels: GROWTH_DATA.labels,
    datasets: [
      {
        label: "المستخدمون",
        data: GROWTH_DATA.users,
        borderColor: "#3b82f6",
        backgroundColor: "transparent",
        tension: 0.4,
        borderWidth: 2,
        pointRadius: 4,
      },
      {
        label: "الجلسات",
        data: GROWTH_DATA.sessions,
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