const appointments = [
  {
    date: "2026-03-03",
    name: "أحمد محمد",
    time: "10:00",
    color: "#3b82f6",
    status: "confirmed",
  },
  {
    date: "2026-03-03",
    name: "فاطمة علي",
    time: "11:30",
    color: "#a855f7",
    status: "pending",
  },
  {
    date: "2026-03-04",
    name: "عائلة خالد",
    time: "14:00",
    color: "#f97316",
    status: "confirmed",
  },
  {
    date: "2026-03-05",
    name: "خالد يوسف",
    time: "09:00",
    color: "#ec4899",
    status: "confirmed",
  },
  {
    date: "2026-03-05",
    name: "نورا سعيد",
    time: "15:30",
    color: "#22c55e",
    status: "online",
  },
  {
    date: "2026-03-06",
    name: "عمر حسن",
    time: "10:30",
    color: "#14b8a6",
    status: "online",
  },
];

let currentDate = new Date(2026, 2);

function renderCalendar() {
  const calendar = document.getElementById("calendar");
  calendar.innerHTML = "";

  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();

  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  document.getElementById("monthTitle").innerText = `${year} - ${month + 1}`;

  for (let i = 0; i < firstDay; i++) {
    calendar.innerHTML += `<div></div>`;
  }

  for (let d = 1; d <= daysInMonth; d++) {
    const fullDate = `${year}-${String(month + 1).padStart(2, "0")}-${String(d).padStart(2, "0")}`;

    const dayEvents = appointments.filter((a) => a.date === fullDate);

    let eventsHTML = "";
    dayEvents.forEach((e) => {
      eventsHTML += `
        <div class="event" style="background:${e.color}">
          ${e.time} ${e.name}
        </div>
      `;
    });

    calendar.innerHTML += `
      <div class="day">
        <div class="day-number">${d}</div>
        ${eventsHTML}
      </div>
    `;
  }

  updateStats();
  renderLegend();
}

function updateStats() {
  document.getElementById("total").innerText = appointments.length;
  document.getElementById("confirmed").innerText = appointments.filter(
    (a) => a.status === "confirmed",
  ).length;
  document.getElementById("pending").innerText = appointments.filter(
    (a) => a.status === "pending",
  ).length;
  document.getElementById("online").innerText = appointments.filter(
    (a) => a.status === "online",
  ).length;
}

function renderLegend() {
  const legend = document.getElementById("legend");
  legend.innerHTML = "";

  const therapists = [
    { name: "د. عبدالله محمد", color: "#14b8a6" },
    { name: "نورا سعيد", color: "#f97316" },
    { name: "خالد يوسف", color: "#22c55e" },
    { name: "فاطمة حسن", color: "#ec4899" },
    { name: "د. محمد علي", color: "#a855f7" },
    { name: "سارة أحمد", color: "#3b82f6" },
  ];

  therapists.forEach((t) => {
    legend.innerHTML += `
      <div class="legend-item">
        <div class="color-box" style="background:${t.color}"></div>
        <span>${t.name}</span>
      </div>
    `;
  });
}

document.getElementById("prevMonth").onclick = () => {
  currentDate.setMonth(currentDate.getMonth() - 1);
  renderCalendar();
};

document.getElementById("nextMonth").onclick = () => {
  currentDate.setMonth(currentDate.getMonth() + 1);
  renderCalendar();
};

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
renderCalendar();
