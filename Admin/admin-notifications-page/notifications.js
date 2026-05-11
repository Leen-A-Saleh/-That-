// notificationsData is injected by notifications.php

const badgeClassMap = {
  reminder:     "badge-reminder",
  announcement: "badge-announcement",
  message:      "badge-message",
  system:       "badge-system",
  alert:        "badge-alert",
};

// Rendering

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
          <span class="badge ${badgeClassMap[n.type] || 'badge-system'}">${n.badge}</span>
          <span class="meta-sep">|</span>
          <span class="aud-info"><i class="${n.audienceIcon}"></i> ${n.audience}</span>
          <span class="aud-info"><i class="fa-solid fa-user-group"></i> ${n.count} مستلم</span>
          <span class="meta-sep">|</span>
          <span class="meta-time"><i class="fa-regular fa-clock"></i> ${n.date}</span>
        </div>
      </div>
    </div>`;
}

function renderList(filter) {
  const list     = document.getElementById("notifList");
  const filtered = filter === "all"
    ? notificationsData
    : notificationsData.filter((n) => n.type === filter);

  list.innerHTML = filtered.length
    ? filtered.map(buildCard).join("")
    : `<div style="text-align:center;padding:40px;color:#718096;font-size:14px;">لا توجد إشعارات في هذه الفئة</div>`;
}

// Filter buttons

document.querySelectorAll(".filter-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    document.querySelectorAll(".filter-btn").forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");
    renderList(btn.dataset.filter);
  });
});

// Modal

const overlay    = document.getElementById("modalOverlay");
const notifTitle = document.getElementById("notifTitle");
const notifBody  = document.getElementById("notifBody");
const titleError = document.getElementById("titleError");
const bodyError  = document.getElementById("bodyError");

document.getElementById("openModalBtn").addEventListener("click", () => {
  overlay.classList.remove("hidden");
});

function closeModal() {
  overlay.classList.add("hidden");
  notifTitle.value = "";
  notifBody.value  = "";
  notifTitle.classList.remove("error");
  notifBody.classList.remove("error");
  titleError.classList.add("hidden");
  bodyError.classList.add("hidden");
  document.querySelectorAll(".aud-btn").forEach((b) => b.classList.remove("active"));
  document.querySelector('.aud-btn[data-aud="all"]').classList.add("active");
}

document.getElementById("closeModalBtn").addEventListener("click", closeModal);
document.getElementById("cancelBtn").addEventListener("click", closeModal);
overlay.addEventListener("click", (e) => { if (e.target === overlay) closeModal(); });

document.querySelectorAll(".aud-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    document.querySelectorAll(".aud-btn").forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");
  });
});

// Send mutation

document.getElementById("sendBtn").addEventListener("click", async () => {
  const title = notifTitle.value.trim();
  const body  = notifBody.value.trim();
  let valid   = true;

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

  const audience = document.querySelector(".aud-btn.active").dataset.aud;

  try {
    const res    = await fetch("notifications.php", {
      method: "POST",
      body:   new URLSearchParams({ action: "send", title, body, audience }),
    });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);
    closeModal();
    setTimeout(() => window.location.reload(), 600);
  } catch (err) {
    alert(err.message || "حدث خطأ، يرجى المحاولة مجدداً");
  }
});

// Sidebar & logout

const menuBtn        = document.querySelector(".menu-btn");
const sidebar        = document.querySelector(".sidebar");
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
if (logoutBtn) logoutBtn.addEventListener("click", () => { window.location.href = "../index.php"; });

// Boot

renderList("all");