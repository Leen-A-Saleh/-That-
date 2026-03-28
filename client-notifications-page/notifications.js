let notifications = [
  {
    id: 1,
    title: "موعد قادم",
    message: "لديك جلسة غدًا الساعة 10",
    time: "منذ ساعة",
    type: "appointment",
    read: false,
  },
  {
    id: 2,
    title: "رسالة جديدة",
    message: "الأخصائي أرسل لك رسالة",
    time: "منذ 3 ساعات",
    type: "message",
    read: false,
  },
  {
    id: 3,
    title: "اختبار مكتمل",
    message: "تم تقييم اختبار القلق",
    time: "منذ 5 ساعات",
    type: "test",
    read: true,
  },
];

const list = document.getElementById("notificationsList");
const dot = document.getElementById("notificationDot");

function getIcon(type) {
  if (type === "appointment") return "fa-calendar icon-appointment";
  if (type === "message") return "fa-comment icon-message";
  if (type === "test") return "fa-brain icon-test";
}

function renderNotifications() {
  list.innerHTML = "";

  notifications.forEach((n) => {
    const div = document.createElement("div");
    div.className = `notification ${n.read ? "read" : "unread"}`;

    div.innerHTML = `
      <div class="notif-content">
        <div class="notif-icon ${getIcon(n.type)}">
          <i class="fa ${getIcon(n.type)}"></i>
        </div>

        <div>
          <div class="title">${n.title}</div>
          <div>${n.message}</div>
          <div class="time">${n.time}</div>
        </div>
      </div>

      <button class="read-btn" onclick="toggleRead(${n.id})">
        ${n.read ? '<i class="fa fa-check-circle"></i>' : '<i class="fa fa-circle"></i>'}
      </button>
    `;

    list.appendChild(div);
  });

  updateStats();
  updateDot();
}

function toggleRead(id) {
  const notif = notifications.find((n) => n.id === id);
  notif.read = !notif.read;
  renderNotifications();
}

function updateStats() {
  const unread = notifications.filter((n) => !n.read).length;

  document.getElementById("unreadCount").innerText = unread;
  document.getElementById("unreadStat").innerText = unread;
  document.getElementById("totalCount").innerText = notifications.length;
}

function updateDot() {
  const unread = notifications.filter((n) => !n.read).length;
  dot.style.display = unread > 0 ? "block" : "none";
}

document.getElementById("markAllRead").onclick = () => {
  notifications.forEach((n) => (n.read = true));
  renderNotifications();
};

document.getElementById("deleteAll").onclick = () => {
  notifications = [];
  renderNotifications();
};

const menuBtn = document.getElementById("menuBtn");
const sidebar = document.querySelector(".sidebar");

let overlay = document.querySelector(".sidebar-overlay");
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

renderNotifications();

if (logoutBtn) {
  logoutBtn.addEventListener("click", function () {
    localStorage.removeItem("token");

    window.location.href = "login.html";
  });
}
