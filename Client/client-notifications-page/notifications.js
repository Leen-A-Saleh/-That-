let notifications = Array.isArray(window.initialNotifications)
  ? window.initialNotifications.map((notification) => ({
      ...notification,
      read: Boolean(notification.read),
    }))
  : [];


const list = document.getElementById("notificationsList");
const dot = document.getElementById("notificationDot");
const logoutBtn = document.getElementById("logoutBtn");
const markAllReadBtn = document.getElementById("markAllRead");
const deleteAllBtn = document.getElementById("deleteAll");


function getIcon(type) {
  if (type === "appointment") return "../images/appointment.png";
  if (type === "message") return "../images/message.png";
  if (type === "test") return "../images/test.png";
  if (type === "reminder") return "../images/reminder.png";
  if (type === "activity") return "../images/activity.png";
  if (type === "warning") return "../images/warning.png";
  if (type === "info") return "../images/info.png";
  if (type === "success") return "../images/success.png";

  return "../images/default.png";
}

function renderNotifications() {
  if (!list) return;
  list.innerHTML = "";

  if (notifications.length === 0) {
    list.innerHTML = `<p style="text-align:center;color:gray;">لا توجد إشعارات</p>`;
    updateStats();
    updateDot();
    return;
  }

  notifications.forEach((n) => {
    const div = document.createElement("div");
    div.className = `notification ${n.read ? "read" : "unread"}`;

    div.innerHTML = `
      <div class="notif-content">
        <div class="notif-icon icon-${n.type}">
          <img src="${getIcon(n.type)}" alt="icon">
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

// ================== ACTIONS ==================
async function toggleRead(id) {
  const notif = notifications.find((n) => n.id === id);
  if (!notif) return;

  const previousState = notif.read;
  notif.read = !notif.read;
  renderNotifications();

  try {
    const response = await fetch("notifications.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "toggle_read",
        id,
      }),
    });

    const result = await response.json();
    if (!response.ok || !result.success) {
      throw new Error("toggle failed");
    }

    notif.read = Boolean(result.read);
  } catch (error) {
    notif.read = previousState;
  }

  renderNotifications();
}

if (markAllReadBtn) {
  markAllReadBtn.onclick = async () => {
    const previousNotifications = notifications.map((n) => ({ ...n }));
    notifications.forEach((n) => {
      n.read = true;
    });
    renderNotifications();

    try {
      const response = await fetch("notifications.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          action: "mark_all_read",
        }),
      });

      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error("mark all read failed");
      }
    } catch (error) {
      notifications = previousNotifications;
      renderNotifications();
    }
  };
}

if (deleteAllBtn) {
  deleteAllBtn.onclick = async () => {
    const previousNotifications = notifications.map((n) => ({ ...n }));
    notifications = [];
    renderNotifications();

    try {
      const response = await fetch("notifications.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          action: "delete_all",
        }),
      });

      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error("delete all failed");
      }
    } catch (error) {
      notifications = previousNotifications;
      renderNotifications();
    }
  };
}


function updateStats() {
  const unread = notifications.filter((n) => !n.read).length;
  const total = notifications.length;
  const thisWeek = notifications.filter((n) => isCurrentWeek(n.created_at)).length;

  document.getElementById("todayAppointments").innerText = unread;
  document.getElementById("activeCases").innerText = thisWeek;
  document.getElementById("totalCases").innerText = total;
}

function isCurrentWeek(createdAt) {
  if (!createdAt) return false;

  const createdDate = new Date(createdAt);
  if (Number.isNaN(createdDate.getTime())) return false;

  const now = new Date();
  const currentMonday = getMonday(now);
  const nextMonday = new Date(currentMonday);
  nextMonday.setDate(currentMonday.getDate() + 7);

  return createdDate >= currentMonday && createdDate < nextMonday;
}

function getMonday(date) {
  const result = new Date(date);
  const day = result.getDay();
  const delta = day === 0 ? -6 : 1 - day;
  result.setDate(result.getDate() + delta);
  result.setHours(0, 0, 0, 0);
  return result;
}


function updateDot() {
  if (!dot) return;
  const unread = notifications.filter((n) => !n.read).length;
  dot.style.display = unread > 0 ? "block" : "none";
}

const menuBtn = document.getElementById("menuBtn");
const sidebar = document.querySelector(".sidebar");

let overlay = document.querySelector(".sidebar-overlay");
if (!overlay) {
  overlay = document.createElement("div");
  overlay.className = "sidebar-overlay";
  document.body.appendChild(overlay);
}

if (menuBtn && sidebar) {
  menuBtn.addEventListener("click", () => {
    sidebar.classList.toggle("open");
    overlay.classList.toggle("open");
  });
}

overlay.addEventListener("click", () => {
  if (!sidebar) return;
  sidebar.classList.remove("open");
  overlay.classList.remove("open");
});

renderNotifications();
