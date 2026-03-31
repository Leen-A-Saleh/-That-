
let notifications = [
  {
    id: 1,
    title: "موعد قادم",
    message: "لديك جلسة غدًا الساعة 10:00 صباحًا",
    time: "منذ ساعة",
    type: "appointment",
    read: false,
  },
  {
    id: 2,
    title: "رسالة جديدة",
    message: "تم إرسال رسالة جديدة لك من الأخصائي",
    time: "منذ 3 ساعات",
    type: "message",
    read: false,
  },
  {
    id: 3,
    title: "اختبار مكتمل",
    message: "تم تقييم اختبار القلق بنجاح",
    time: "منذ 5 ساعات",
    type: "test",
    read: false,
  },
  {
    id: 4,
    title: "تذكير بالتمرين",
    message: "لا تنسَ أداء التمارين اليومية اليوم",
    time: "منذ 6 ساعات",
    type: "reminder",
    read: false,
  },
  {
    id: 5,
    title: "نشاط جديد",
    message: "تم إضافة نشاط جديد يمكنك تجربته الآن",
    time: "منذ يوم",
    type: "activity",
    read: false,
  },
  {
    id: 6,
    title: "تأجيل الموعد",
    message: "تم تأجيل موعدك إلى الساعة 3:00 مساءً",
    time: "منذ يوم",
    type: "appointment",
    read: false,
  },
  {
    id: 7,
    title: "إلغاء الموعد",
    message: "تم إلغاء موعدك، يرجى الحجز مرة أخرى",
    time: "منذ يومين",
    type: "warning",
    read: false,
  },
  {
    id: 8,
    title: "تحديث النظام",
    message: "تم تحديث النظام وإضافة ميزات جديدة",
    time: "منذ يومين",
    type: "info",
    read: false,
  },
  {
    id: 9,
    title: "نجاح العملية",
    message: "تم حفظ بياناتك بنجاح",
    time: "منذ 3 أيام",
    type: "success",
    read: false,
  }
];


const list = document.getElementById("notificationsList");
const dot = document.getElementById("notificationDot");
const logoutBtn = document.getElementById("logoutBtn");


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
function toggleRead(id) {
  const notif = notifications.find((n) => n.id === id);
  if (!notif) return;

  notif.read = !notif.read;

 
  renderNotifications();
}

document.getElementById("markAllRead").onclick = () => {
  notifications.forEach((n) => (n.read = true));
  renderNotifications();
};

document.getElementById("deleteAll").onclick = () => {
  notifications = [];
  renderNotifications();
};


function updateStats() {
  const unread = notifications.filter((n) => !n.read).length;
  const total = notifications.length;

  document.getElementById("todayAppointments").innerText = unread;
  document.getElementById("activeCases").innerText = Math.min(total, 3);
  document.getElementById("totalCases").innerText = total;
}


function updateDot() {
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
    window.location.href = "login.html";
  });
}


renderNotifications();