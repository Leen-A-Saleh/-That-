const notifications = [
  {
    id: 1,
    type: "reminder",
    icon: "../images/Container.png",
    title: "تذكير بموعد قادم",
    desc: "لديك موعد غداً الساعة 10:00 صباحاً مع د. سارة أحمد",
    badge: "تذكير",
    audience: "المرضى",
    audienceIcon: "fa-solid fa-user",
    count: 45,
    date: "2026-03-02 09:00",
    status: "sent",
  },
  {
    id: 2,
    type: "announcement",
    icon: "../images/Container (1).png",
    title: "اختبار نفسي جديد متاح",
    desc: "تم إضافة مقياس جديد لتقييم القلق الاجتماعي",
    badge: "إعلان",
    audience: "الجميع",
    audienceIcon: "fa-solid fa-users",
    count: 370,
    date: "2026-03-01 14:30",
    status: "sent",
  },
  {
    id: 3,
    type: "message",
    icon: "../images/Container (2).png",
    title: "رسالة جديدة من أخصائيك",
    desc: "د. محمد علي أرسل لك رسالة جديدة",
    badge: "رسالة",
    audience: "المرضى",
    audienceIcon: "fa-solid fa-user",
    count: 12,
    date: "2026-03-03 08:15",
    status: "sent",
  },
  {
    id: 4,
    type: "system",
    icon: "../images/Container (3).png",
    title: "تحديث النظام",
    desc: "سيتم إجراء صيانة للنظام يوم الجمعة من الساعة 2-4 صباحاً",
    badge: "نظامي",
    audience: "الجميع",
    audienceIcon: "fa-solid fa-users",
    count: 370,
    date: "2026-02-28 10:00",
    status: "sent",
  },
  {
    id: 5,
    type: "alert",
    icon: "../images/Container (4).png",
    title: "طلب موعد جديد",
    desc: "لديك 3 طلبات مواعيد جديدة تنتظر الموافقة",
    badge: "تنبيه",
    audience: "الأخصائيين",
    audienceIcon: "fa-solid fa-user-tie",
    count: 28,
    date: "2026-03-02 16:45",
    status: "sent",
  },
  {
    id: 6,
    type: "announcement",
    icon: "../images/Container (1).png",
    title: "نشاط جديد متاح",
    desc: "تم إضافة تمرين تنفس جديد في قسم الأنشطة",
    badge: "إعلان",
    audience: "المرضى",
    audienceIcon: "fa-solid fa-user",
    count: 342,
    date: "2026-02-29 11:20",
    status: "sent",
  },
  {
    id: 7,
    type: "reminder",
    icon: "../images/Container.png",
    title: "تقييم الجلسة",
    desc: "يرجى تقييم جلستك الأخيرة مع د. فاطمة حسن",
    badge: "تذكير",
    audience: "المرضى",
    audienceIcon: "fa-solid fa-user",
    count: 18,
    date: "2026-03-01 18:00",
    status: "scheduled",
  },
];

const badgeClassMap = {
  reminder: "badge-reminder",
  announcement: "badge-announcement",
  message: "badge-message",
  system: "badge-system",
  alert: "badge-alert",
};

function buildStatusBadge(status) {
  if (status === "sent") {
    return `<span class="status-sent"><img src="../images/CheckCircle.svg" alt="check"/> تم الإرسال</span>`;
  }
  return `<span class="status-scheduled"><img src="../images/Clock.svg" alt="clock" /> مجدولة</span>`;
}

function buildCard(n) {
  return `
    <div class="notif-card" data-type="${n.type}" data-id="${n.id}">
      <div class="notif-icon-wrap ${n.type}">
        <img src="${n.icon}" alt="icon"/>
      </div>
      <div class="notif-body">
        <div class="notif-header">
          <span class="notif-title">${n.title}</span>
          <span>${buildStatusBadge(n.status)}</span>
        </div>
        <div class="notif-desc">${n.desc}</div>
        <div class="notif-meta">
          <span class="badge ${badgeClassMap[n.type]}">${n.badge}</span>
          <span class="meta-sep">|</span>
          <span class="aud-info"><i class="${n.audienceIcon}"></i> ${n.audience}</span>
          <span class="aud-info"><i class="fa-solid fa-user-group"></i> ${n.count} مستلم</span>
          <span class="meta-sep">|</span>
          <span class="meta-time"><i class="fa-regular fa-clock"></i> ${n.date}</span>
        </div>
      </div>
    </div>
  `;
}

function renderList(filter) {
  const list = document.getElementById("notifList");
  const filtered =
    filter === "all"
      ? notifications
      : notifications.filter((n) => n.type === filter);
  list.innerHTML = filtered.length
    ? filtered.map(buildCard).join("")
    : `<div style="text-align:center;padding:40px;color:#718096;font-size:14px;">لا توجد إشعارات في هذه الفئة</div>`;
}

function updateStats() {
  const sent = notifications.filter((n) => n.status === "sent").length;
  const scheduled = notifications.filter(
    (n) => n.status === "scheduled",
  ).length;
  const recipients = notifications.reduce((s, n) => s + n.count, 0);
  document.getElementById("statTotal").textContent = notifications.length;
  document.getElementById("statSent").textContent = sent;
  document.getElementById("statScheduled").textContent = scheduled;
  document.getElementById("statRecipients").textContent =
    recipients.toLocaleString("ar");
}

document.querySelectorAll(".filter-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    document
      .querySelectorAll(".filter-btn")
      .forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");
    renderList(btn.dataset.filter);
  });
});

const overlay = document.getElementById("modalOverlay");
const notifTitle = document.getElementById("notifTitle");
const notifBody = document.getElementById("notifBody");
const titleError = document.getElementById("titleError");
const bodyError = document.getElementById("bodyError");

document.getElementById("openModalBtn").addEventListener("click", () => {
  overlay.classList.remove("hidden");
});

function closeModal() {
  overlay.classList.add("hidden");
  notifTitle.value = "";
  notifBody.value = "";
  notifTitle.classList.remove("error");
  notifBody.classList.remove("error");
  titleError.classList.add("hidden");
  bodyError.classList.add("hidden");
  document
    .querySelectorAll(".aud-btn")
    .forEach((b) => b.classList.remove("active"));
  document.querySelector('.aud-btn[data-aud="all"]').classList.add("active");
}

document.getElementById("closeModalBtn").addEventListener("click", closeModal);
document.getElementById("cancelBtn").addEventListener("click", closeModal);
overlay.addEventListener("click", (e) => {
  if (e.target === overlay) closeModal();
});

document.querySelectorAll(".aud-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    document
      .querySelectorAll(".aud-btn")
      .forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");
  });
});

document.getElementById("sendBtn").addEventListener("click", () => {
  const title = notifTitle.value.trim();
  const body = notifBody.value.trim();
  let valid = true;

  if (!title) {
    notifTitle.classList.add("error");
    titleError.classList.remove("hidden");
    valid = false;
  } else {
    notifTitle.classList.remove("error");
    titleError.classList.add("hidden");
  }

  if (!body) {
    notifBody.classList.add("error");
    bodyError.classList.remove("hidden");
    valid = false;
  } else {
    notifBody.classList.remove("error");
    bodyError.classList.add("hidden");
  }

  if (!valid) return;

  const activeAud = document.querySelector(".aud-btn.active");
  const audLabel =
    activeAud.dataset.aud === "all"
      ? "الجميع"
      : activeAud.dataset.aud === "patients"
        ? "المرضى"
        : "الأخصائيين";
  const audIcon =
    activeAud.dataset.aud === "all"
      ? "fa-solid fa-users"
      : activeAud.dataset.aud === "specialists"
        ? "fa-solid fa-user-tie"
        : "fa-solid fa-user";

  const typeMap = {
    all: "announcement",
    patients: "reminder",
    specialists: "alert",
  };

  const badgeMap = {
    all: "إعلان",
    patients: "تذكير",
    specialists: "تنبيه",
  };

  const iconMap = {
    all: "../images/Container (1).png",
    patients: "../images/Container.png",
    specialists: "../images/Container (4).png",
  };

  const aud = activeAud.dataset.aud;
  const now = new Date();
  const dateStr =
    now.getFullYear() +
    "-" +
    String(now.getMonth() + 1).padStart(2, "0") +
    "-" +
    String(now.getDate()).padStart(2, "0") +
    " " +
    String(now.getHours()).padStart(2, "0") +
    ":" +
    String(now.getMinutes()).padStart(2, "0");

  notifications.unshift({
    id: Date.now(),
    type: typeMap[aud],
    icon: iconMap[aud],
    title: title,
    desc: body,
    badge: badgeMap[aud],
    audience: audLabel,
    audienceIcon: audIcon,
    count: parseInt(activeAud.dataset.count),
    date: dateStr,
    status: "sent",
  });

  updateStats();
  const activeFilter =
    document.querySelector(".filter-btn.active").dataset.filter;
  renderList(activeFilter);
  closeModal();
});
const menuBtn = document.querySelector(".menu-btn");
const sidebar = document.querySelector(".sidebar");
const sidebarOverlay = document.querySelector(".sidebar-overlay");

menuBtn.addEventListener("click", () => {
  sidebar.classList.toggle("open");
  sidebarOverlay.classList.toggle("open");
});

sidebarOverlay.addEventListener("click", () => {
  sidebar.classList.remove("open");
  sidebarOverlay.classList.remove("open");
});

const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn)
  logoutBtn.addEventListener("click", () => {
    window.location.href = "../index.html";
  });

renderList("all");
updateStats();
