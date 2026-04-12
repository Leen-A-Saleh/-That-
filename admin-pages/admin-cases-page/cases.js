const casesData = [
  {
    id: 1,
    name: "أحمد محمد السعيد",
    condition: "قلق واكتئاب",
    doctor: "د. سارة أحمد",
    date: "2026-03-01",
    sessions: 12,
    progress: 75,
    status: "active",
  },
  {
    id: 2,
    name: "فاطمة علي حسن",
    condition: "اضطراب ما بعد الصدمة",
    doctor: "د. محمد علي",
    date: "2026-02-28",
    sessions: 8,
    progress: 45,
    status: "active",
  },
  {
    id: 3,
    name: "خالد يوسف العمري",
    condition: "اضطرابات النوم",
    doctor: "د. سارة أحمد",
    date: "2026-02-25",
    sessions: 10,
    progress: 60,
    status: "review",
  },
  {
    id: 4,
    name: "نورة سعيد الغامدي",
    condition: "إدمان إلكتروني",
    doctor: "د. فاطمة حسن",
    date: "2026-03-02",
    sessions: 6,
    progress: 30,
    status: "active",
  },
  {
    id: 5,
    name: "عمر حسن القحطاني",
    condition: "اضطراب فرط الحركة",
    doctor: "د. خالد يوسف",
    date: "2026-01-20",
    sessions: 15,
    progress: 100,
    status: "pending",
  },
  {
    id: 6,
    name: "ريم عبدالله المطيري",
    condition: "مشاكل أسرية",
    doctor: "د. نورة سعيد",
    date: "2026-03-01",
    sessions: 9,
    progress: 55,
    status: "active",
  },
  {
    id: 7,
    name: "محمد سالم العتيبي",
    condition: "اكتئاب حاد",
    doctor: "د. عبدالله محمد",
    date: "2026-02-27",
    sessions: 7,
    progress: 40,
    status: "review",
  },
  {
    id: 8,
    name: "سارة فهد الدوسري",
    condition: "قلق اجتماعي",
    doctor: "د. سارة أحمد",
    date: "2026-03-03",
    sessions: 4,
    progress: 20,
    status: "active",
  },
];

const statusMap = {
  active: {
    label: "جارية",
    icon: "<img src='../images/StatusIcon.svg' alt='جارية'>",
    cls: "active",
  },
  review: {
    label: "تحت المراجعة",
    icon: "<img src='../images/StatusIcon (1).svg' alt='تحت المراجعة'>",
    cls: "review",
  },
  pending: {
    label: "مغلقة",
    icon: "<img src='../images/Calendar.svg' alt='مغلقة'>",
    cls: "pending",
  },
};

function getProgressColor(pct) {
  if (pct <= 30) return "#ef4444";
  if (pct <= 60) return "#f59e0b";
  if (pct < 100) return "#22b0a8";
  return "#22c55e";
}

let currentPage = 1;
const itemsPerPage = 8;
let filteredData = [...casesData];

function updateStats() {
  document.getElementById("stat-total").textContent = casesData.length;
  document.getElementById("stat-active").textContent = casesData.filter(
    (c) => c.status === "active",
  ).length;
  document.getElementById("stat-review").textContent = casesData.filter(
    (c) => c.status === "review",
  ).length;
  document.getElementById("stat-pending").textContent = casesData.filter(
    (c) => c.status === "pending",
  ).length;
}

function renderCases() {
  const start = (currentPage - 1) * itemsPerPage;
  const pageData = filteredData.slice(start, start + itemsPerPage);
  const container = document.getElementById("cases-container");

  if (pageData.length === 0) {
    container.innerHTML = `<div style="text-align:center;padding:40px;color:#aaa;font-family:'Cairo',sans-serif;font-size:14px;">لا توجد حالات مطابقة</div>`;
    return;
  }

  container.innerHTML = pageData
    .map((c) => {
      const st = statusMap[c.status];
      const pc = getProgressColor(c.progress);
      const initial = c.name.trim().charAt(0);
      return `
      <div class="case-card" data-id="${c.id}">
        <div class="avatar">${initial}</div>

        <div class="case-info">
          <span class="case-name">${c.name}</span>
          <span class="case-condition">${c.condition}</span>
        </div>

        <div class="case-doctor">
          <img src="../images/UserCog.svg" alt="${c.doctor}"> ${c.doctor}
        </div>

        <div class="case-date-col">
          <span class="case-date-label">آخر جلسة</span>
          <span class="case-date-val"><img src="../images/Calendar.svg" alt="التاريخ">${c.date}</span>
        </div>

        <div class="case-progress-col">
          <div class="progress-top">
            <span class="progress-label">التقدم</span>
            <span class="progress-pct">${c.progress}%</span>
          </div>
          <div class="progress-bar-wrap">
            <div class="progress-bar-fill" style="width:${c.progress}%;"></div>
          </div>
          <div class="progress-sessions">${c.sessions} جلسة</div>
        </div>

        <div class="badge ${st.cls}">
          ${st.icon} ${st.label}
        </div>

        <button class="btn-details" onclick="viewDetails(${c.id})">عرض التفاصيل</button>
      </div>`;
    })
    .join("");
}

function renderPagination() {
  const totalPages = Math.ceil(filteredData.length / itemsPerPage);
  document.getElementById("pagination-info").textContent =
    `عرض ${filteredData.length} من ${casesData.length} حالة`;

  const btns = document.getElementById("pagination-btns");
  let html = `<button class="pg-btn" ${currentPage === 1 ? "disabled" : ""} onclick="goPage(${currentPage - 1})">السابق</button>`;
  for (let i = 1; i <= totalPages; i++) {
    html += `<button class="pg-btn ${i === currentPage ? "active-page" : ""}" onclick="goPage(${i})">${i}</button>`;
  }
  html += `<button class="pg-btn" ${currentPage === totalPages || totalPages === 0 ? "disabled" : ""} onclick="goPage(${currentPage + 1})">التالي</button>`;
  btns.innerHTML = html;
}

function goPage(page) {
  const totalPages = Math.ceil(filteredData.length / itemsPerPage);
  if (page < 1 || page > totalPages) return;
  currentPage = page;
  renderCases();
  renderPagination();
}

function normalizeAr(str) {
  return str
    .toLowerCase()
    .replace(/[أإآا]/g, "ا")
    .replace(/[ىي]/g, "ي")
    .replace(/ة/g, "ه")
    .replace(/[ًٌٍَُِّْ]/g, "");
}

function applyFilters() {
  const raw = document.getElementById("search-input").value.trim();
  const q = normalizeAr(raw);
  const status = document.getElementById("status-filter").value;
  const doctor = document.getElementById("doctor-filter").value;

  filteredData = casesData.filter((c) => {
    const firstName = normalizeAr(c.name.trim().split(" ")[0]);
    const matchQ = !q || firstName.startsWith(q);
    const matchStatus = !status || c.status === status;
    const matchDoctor = !doctor || c.doctor === doctor;
    return matchQ && matchStatus && matchDoctor;
  });

  currentPage = 1;
  renderCases();
  renderPagination();
}

function populateDoctors() {
  const doctors = [...new Set(casesData.map((c) => c.doctor))];
  const sel = document.getElementById("doctor-filter");
  doctors.forEach((d) => {
    const opt = document.createElement("option");
    opt.value = d;
    opt.textContent = d;
    sel.appendChild(opt);
  });
}

function viewDetails(id) {
  const c = casesData.find((x) => x.id === id);
  if (!c) return;

  const st = statusMap[c.status];
  const pc = getProgressColor(c.progress);
  const initial = c.name.trim().charAt(0);

  document.getElementById("modal-avatar").textContent = initial;
  document.getElementById("modal-name").textContent = c.name;
  document.getElementById("modal-condition").textContent = c.condition;
  document.getElementById("modal-doctor").textContent = c.doctor;
  document.getElementById("modal-date").textContent = c.date;
  document.getElementById("modal-sessions").textContent = c.sessions + " جلسة";
  document.getElementById("modal-progress-pct").textContent = c.progress + "%";
  document.getElementById("modal-progress-fill").style.width = c.progress + "%";
  document.getElementById("modal-progress-fill").style.background = pc;

  const badgeEl = document.getElementById("modal-badge");
  badgeEl.textContent = st.label;
  badgeEl.className = "badge " + st.cls;

  document.getElementById("case-modal").classList.add("open");
  document.body.style.overflow = "hidden";
}

function closeModal() {
  document.getElementById("case-modal").classList.remove("open");
  document.body.style.overflow = "";
}

document.addEventListener("DOMContentLoaded", () => {
  document
    .getElementById("modal-overlay")
    .addEventListener("click", closeModal);
});

document.getElementById("search-input").addEventListener("input", applyFilters);
document
  .getElementById("status-filter")
  .addEventListener("change", applyFilters);
document
  .getElementById("doctor-filter")
  .addEventListener("change", applyFilters);

const menuBtn = document.querySelector(".menu-btn");
const sidebar = document.querySelector(".sidebar");
const overlay = document.querySelector(".sidebar-overlay");

if (menuBtn && sidebar) {
  menuBtn.addEventListener("click", () => {
    sidebar.classList.toggle("open");
    overlay.classList.toggle("active");
  });
  overlay.addEventListener("click", () => {
    sidebar.classList.remove("open");
    overlay.classList.remove("active");
  });
}

const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) {
  logoutBtn.addEventListener("click", () => {
    window.location.href = "../login-page/login.html";
  });
}

updateStats();
populateDoctors();
renderCases();
renderPagination();
