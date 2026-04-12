const TOKEN = () => localStorage.getItem("token");

let tests = [
  {
    id: 1,
    nameAr: "إختبار بيك للإكتئاب",
    nameEn: "Beck Depression Inventory",
    cat: "الاكتئاب",
    questions: 21,
    completions: 342,
    avg: 16.5,
    status: "active",
    link: "beck.html",
    desc: "يقيس مستوى الإكتئاب ويحدد شدة الأعراض المرتبطة بالمزاج والحياة اليومية.",
  },
  {
    id: 2,
    nameAr: "إختبار هوبكنز للقلق والإكتئاب",
    nameEn: "Hopkins Symptom Checklist",
    cat: "القلق والاكتئاب",
    questions: 25,
    completions: 298,
    avg: 12.3,
    status: "active",
    link: "hopkins.html",
    desc: "يساعد على تقييم أعراض القلق والاكتئاب وتأثيرها على حياتك اليومية.",
  },
  {
    id: 3,
    nameAr: "إختبار الحالة النفسية للأطفال",
    nameEn: "Children Mental Health Test",
    cat: "الأطفال",
    questions: 30,
    completions: 156,
    avg: 18.4,
    status: "active",
    link: "childrenmentalhealth.html",
    desc: "كشف الحالة النفسية للأطفال وتقييم صحتهم النفسية.",
  },
  {
    id: 4,
    nameAr: "مقياس SNAP",
    nameEn: "SNAP Scale",
    cat: "فرط الحركة",
    questions: 18,
    completions: 412,
    avg: 22.4,
    status: "active",
    link: "snap.html",
    desc: "يساعد على تقييم أعراض اضطراب فرط الحركة وتشتت الإنتباه (ADHD).",
  },
  {
    id: 5,
    nameAr: "مقياس التوتر العام",
    nameEn: "Perceived Stress Scale",
    cat: "الضغط النفسي",
    questions: 10,
    completions: 187,
    avg: 24.1,
    status: "active",
    link: "stress.html",
    desc: "يقيس مستوى التوتر والضغوط النفسية التي يمر بها الفرد.",
  },
  {
    id: 6,
    nameAr: "مقياس القلق الإجتماعي",
    nameEn: "Social Anxiety Scale",
    cat: "القلق الاجتماعي",
    questions: 24,
    completions: 124,
    avg: 45.2,
    status: "active",
    link: "social-anxiety.html",
    desc: "يقيم مستوى القلق في المواقف الإجتماعية والتفاعل مع الآخرين.",
  },
];

const MAIN_COLOR = "linear-gradient(180deg, #30B7C4 0%, #83C2B9 100%)";

const CAT_CONFIG = {
  الاكتئاب: { badge: "badge-dep", bar: MAIN_COLOR },
  "القلق والاكتئاب": { badge: "badge-anx", bar: MAIN_COLOR },
  الأطفال: { badge: "badge-child", bar: MAIN_COLOR },
  "فرط الحركة": { badge: "badge-adhd", bar: MAIN_COLOR },
  "الضغط النفسي": { badge: "badge-stress", bar: MAIN_COLOR },
  "القلق الاجتماعي": { badge: "badge-social", bar: MAIN_COLOR },
};

let editingId = null;
let chartInst = null;

document.addEventListener("DOMContentLoaded", () => {
  injectMobileCardContainer();
  initSidebar();
  initModal();
  initFilters();
  loadTests();
});

function injectMobileCardContainer() {
  const tableCard = document.querySelector(".tests-table-card");
  if (!tableCard) return;
  if (document.getElementById("mobileCardsList")) return;
  const div = document.createElement("div");
  div.className = "mobile-cards-list";
  div.id = "mobileCardsList";
  tableCard.appendChild(div);
}

async function fetchTests() {
  return tests;
}
async function createTest(payload) {
  const newId = tests.length ? Math.max(...tests.map((t) => t.id)) + 1 : 1;
  const record = { id: newId, completions: 0, avg: 0, ...payload };
  tests.push(record);
  return record;
}
async function updateTest(id, payload) {
  const idx = tests.findIndex((t) => t.id === id);
  if (idx !== -1) tests[idx] = { ...tests[idx], ...payload };
  return tests[idx];
}
async function deleteTestById(id) {
  tests = tests.filter((t) => t.id !== id);
}

async function loadTests() {
  tests = await fetchTests();
  renderAll();
}

function renderAll() {
  renderStats();
  renderCatBars();
  renderChart();
  renderTable(getFiltered());
}

function renderStats() {
  const total = tests.length;
  const completions = tests.reduce((s, t) => s + t.completions, 0);
  document.getElementById("statTotal").textContent = total;
  document.getElementById("statCompletions").textContent =
    completions.toLocaleString("ar-EG");
}

function renderCatBars() {
  const totComp = tests.reduce((s, t) => s + t.completions, 0);
  const cats = {};
  tests.forEach((t) => {
    cats[t.cat] = (cats[t.cat] || 0) + t.completions;
  });

  const el = document.getElementById("catList");
  el.innerHTML = "";
  Object.entries(cats).forEach(([cat, count]) => {
    const pct = totComp ? Math.round((count / totComp) * 100) : 0;
    const color = CAT_CONFIG[cat]?.bar || "#2fa4a9";
    el.innerHTML += `
      <div class="cat-item">
        <div class="cat-header">
          <span class="cat-name">${cat}</span>
          <span class="cat-count">${count.toLocaleString("ar-EG")} (${pct}%)</span>
        </div>
        <div class="cat-bar-bg">
          <div class="cat-bar-fill" style="width:${pct}%; background:${color};"></div>
        </div>
      </div>`;
  });
}

const MONTHLY = {
  labels: ["سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر", "يناير", "فبراير"],
  data: [185, 210, 300, 320, 370, 421],
};

function renderChart() {
  const ctx = document.getElementById("monthlyChart").getContext("2d");
  if (chartInst) chartInst.destroy();
  chartInst = new Chart(ctx, {
    type: "bar",
    data: {
      labels: MONTHLY.labels,
      datasets: [
        {
          data: MONTHLY.data,
          backgroundColor: "#2fa4a9",
          borderRadius: 8,
          borderSkipped: false,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { family: "Cairo", size: 12 }, color: "#9ca3af" },
        },
        y: {
          grid: { color: "#f0f4f8" },
          ticks: {
            font: { family: "Cairo", size: 11 },
            color: "#9ca3af",
            maxTicksLimit: 5,
          },
          beginAtZero: true,
        },
      },
    },
  });
}

function renderTable(list) {
  renderDesktopTable(list);
  renderMobileCards(list);
}

function renderDesktopTable(list) {
  const tbody = document.getElementById("testsTableBody");
  if (!tbody) return;
  if (!list.length) {
    tbody.innerHTML = `<tr><td colspan="7" class="empty-state">لا توجد نتائج مطابقة</td></tr>`;
    return;
  }
  tbody.innerHTML = list
    .map((t) => {
      const badge = CAT_CONFIG[t.cat]?.badge || "badge-dep";
      const stCls = t.status === "active" ? "status-active" : "status-draft";
      const stTxt = t.status === "active" ? "نشط" : "مسودة";
      return `
      <tr>
        <td>
          <div class="test-name-ar">${t.nameAr}</div>
          <div class="test-name-en">${t.nameEn}</div>
        </td>
        <td><span class="cat-badge ${badge}">${t.cat}</span></td>
        <td>${t.questions} سؤال</td>
        <td>
          <div class="completions-cell">
            <i class="fa-solid fa-users"></i>
            ${t.completions.toLocaleString("ar-EG")}
          </div>
        </td>
        <td><span class="avg-score">${t.avg.toFixed(1)}</span></td>
        <td><span class="status-badge ${stCls}">${stTxt}</span></td>
        <td>
          <div class="row-actions">
            <button class="action-btn btn-view"   title="عرض"   onclick="handleView(${t.id})"><i class="fa-regular fa-eye"></i></button>
            <button class="action-btn btn-edit"   title="تعديل" onclick="handleEdit(${t.id})"><i class="fa-regular fa-pen-to-square"></i></button>
            <button class="action-btn btn-delete" title="حذف"   onclick="handleDelete(${t.id})"><i class="fa-regular fa-trash-can"></i></button>
          </div>
        </td>
      </tr>`;
    })
    .join("");
}

function renderMobileCards(list) {
  const container = document.getElementById("mobileCardsList");
  if (!container) return;

  if (!list.length) {
    container.innerHTML = `<div class="empty-state">لا توجد نتائج مطابقة</div>`;
    return;
  }

  container.innerHTML = list
    .map((t) => {
      const badge = CAT_CONFIG[t.cat]?.badge || "badge-dep";
      const stCls = t.status === "active" ? "status-active" : "status-draft";
      const stTxt = t.status === "active" ? "نشط" : "مسودة";
      return `
      <div class="test-mobile-card">
        <!-- top: name + status -->
        <div class="test-card-top">
          <div class="test-card-names">
            <div class="test-card-name-ar">${t.nameAr}</div>
            <div class="test-card-name-en">${t.nameEn}</div>
          </div>
          <span class="status-badge ${stCls}">${stTxt}</span>
        </div>

        <!-- category badge -->
        <div>
          <span class="cat-badge ${badge}">${t.cat}</span>
        </div>

        <!-- meta grid -->
        <div class="test-card-meta">
          <div class="test-card-meta-item">
            <span class="test-card-meta-label">عدد الأسئلة</span>
            <span class="test-card-meta-value">
              <i class="fa-regular fa-circle-question"></i>
              ${t.questions} سؤال
            </span>
          </div>
          <div class="test-card-meta-item">
            <span class="test-card-meta-label">مرات الإكمال</span>
            <span class="test-card-meta-value">
              <i class="fa-solid fa-users"></i>
              ${t.completions.toLocaleString("ar-EG")}
            </span>
          </div>
          <div class="test-card-meta-item">
            <span class="test-card-meta-label">متوسط النتائج</span>
            <span class="test-card-meta-value avg">${t.avg.toFixed(1)}</span>
          </div>
          <div class="test-card-meta-item">
            <span class="test-card-meta-label">الفئة</span>
            <span class="test-card-meta-value">${t.cat}</span>
          </div>
        </div>

        <!-- footer: actions -->
        <div class="test-card-footer">
          <div class="test-card-actions">
            <button class="action-btn btn-view"   title="عرض"   onclick="handleView(${t.id})"><i class="fa-regular fa-eye"></i></button>
            <button class="action-btn btn-edit"   title="تعديل" onclick="handleEdit(${t.id})"><i class="fa-regular fa-pen-to-square"></i></button>
            <button class="action-btn btn-delete" title="حذف"   onclick="handleDelete(${t.id})"><i class="fa-regular fa-trash-can"></i></button>
          </div>
        </div>
      </div>`;
    })
    .join("");
}

function getFiltered() {
  const q = document.getElementById("searchInput").value.trim().toLowerCase();
  const st = document.getElementById("statusFilter").value;
  const cat = document.getElementById("catFilter").value;
  return tests.filter(
    (t) =>
      (!q || t.nameAr.includes(q) || t.nameEn.toLowerCase().includes(q)) &&
      (!st || t.status === st) &&
      (!cat || t.cat === cat),
  );
}

function initFilters() {
  ["searchInput", "statusFilter", "catFilter"].forEach((id) => {
    document
      .getElementById(id)
      .addEventListener("input", () => renderTable(getFiltered()));
    document
      .getElementById(id)
      .addEventListener("change", () => renderTable(getFiltered()));
  });
}

function handleView(id) {
  const t = tests.find((t) => t.id === id);
  if (!t) return;

  const stCls = t.status === "active" ? "status-active" : "status-draft";
  const stTxt = t.status === "active" ? "نشط" : "مسودة";

  document.getElementById("viewNameAr").textContent = t.nameAr;
  document.getElementById("viewNameEn").textContent = t.nameEn;
  document.getElementById("viewStatQuestions").textContent = t.questions;
  document.getElementById("viewStatCompletions").textContent =
    t.completions.toLocaleString("ar-EG");
  document.getElementById("viewStatAvg").textContent = t.avg.toFixed(1);
  document.getElementById("viewCatBadge").textContent = t.cat;
  document.getElementById("viewDesc").textContent = t.desc || "—";

  const statusEl = document.getElementById("viewStatus");
  statusEl.textContent = stTxt;
  statusEl.className = `status-badge ${stCls}`;

  document.getElementById("viewModal").classList.add("open");
}

let pendingDeleteId = null;

function handleEdit(id) {
  const t = tests.find((t) => t.id === id);
  if (!t) return;
  editingId = id;
  openModal(t);
}

async function handleDelete(id) {
  const t = tests.find((t) => t.id === id);
  if (!t) return;
  pendingDeleteId = id;
  document.getElementById("deleteTestName").textContent = t.nameAr;
  document.getElementById("deleteModal").classList.add("open");
}

function initModal() {
  document.getElementById("openModalBtn").addEventListener("click", () => {
    editingId = null;
    openModal(null);
  });
  document
    .getElementById("closeModalBtn")
    .addEventListener("click", closeModal);
  document
    .getElementById("cancelModalBtn")
    .addEventListener("click", closeModal);
  document.getElementById("modalOverlay").addEventListener("click", (e) => {
    if (e.target === document.getElementById("modalOverlay")) closeModal();
  });
  document.getElementById("saveTestBtn").addEventListener("click", handleSave);

  document
    .getElementById("closeViewBtn")
    .addEventListener("click", () =>
      document.getElementById("viewModal").classList.remove("open"),
    );
  document.getElementById("viewModal").addEventListener("click", (e) => {
    if (e.target === document.getElementById("viewModal"))
      document.getElementById("viewModal").classList.remove("open");
  });

  document.getElementById("cancelDeleteBtn").addEventListener("click", () => {
    document.getElementById("deleteModal").classList.remove("open");
    pendingDeleteId = null;
  });
  document
    .getElementById("confirmDeleteBtn")
    .addEventListener("click", async () => {
      if (pendingDeleteId !== null) {
        await deleteTestById(pendingDeleteId);
        pendingDeleteId = null;
        renderAll();
      }
      document.getElementById("deleteModal").classList.remove("open");
    });
  document.getElementById("deleteModal").addEventListener("click", (e) => {
    if (e.target === document.getElementById("deleteModal")) {
      document.getElementById("deleteModal").classList.remove("open");
      pendingDeleteId = null;
    }
  });
}

function openModal(data) {
  document.getElementById("fieldNameAr").value = data?.nameAr || "";
  document.getElementById("fieldNameEn").value = data?.nameEn || "";
  document.getElementById("fieldCat").value = data?.cat || "الاكتئاب";
  document.getElementById("fieldQuestions").value = data?.questions || "";
  document.getElementById("fieldStatus").value = data?.status || "active";
  document.getElementById("fieldLink").value = data?.link || "";
  document.getElementById("fieldDesc").value = data?.desc || "";
  document.getElementById("modalTitle").textContent = data
    ? "تعديل الاختبار"
    : "إضافة اختبار جديد";
  document.getElementById("modalOverlay").classList.add("open");
}

function closeModal() {
  document.getElementById("modalOverlay").classList.remove("open");
  editingId = null;
}

async function handleSave() {
  const nameAr = document.getElementById("fieldNameAr").value.trim();
  const nameEn = document.getElementById("fieldNameEn").value.trim();
  const cat = document.getElementById("fieldCat").value;
  const questions =
    parseInt(document.getElementById("fieldQuestions").value) || 0;
  const status = document.getElementById("fieldStatus").value;
  const link = document.getElementById("fieldLink").value.trim();
  const desc = document.getElementById("fieldDesc").value.trim();

  let isValid = true;


clearError("fieldNameAr", "errorNameAr");
clearError("fieldNameEn", "errorNameEn");
clearError("fieldQuestions", "errorQuestions");

if (!nameAr) {
  showError("fieldNameAr", "errorNameAr", "الحقل مطلوب");
  isValid = false;
}

if (!nameEn) {
  showError("fieldNameEn", "errorNameEn", "الحقل مطلوب");
  isValid = false;
}

if (!questions || questions <= 0) {
  showError("fieldQuestions", "errorQuestions", "الحقل مطلوب");
  isValid = false;
}

if (!isValid) return;

  const payload = { nameAr, nameEn, cat, questions, status, link, desc };

  if (editingId !== null) {
    await updateTest(editingId, payload);
  } else {
    await createTest(payload);
  }

  closeModal();
  renderAll();
}

function initSidebar() {
  const menuBtn = document.querySelector(".menu-btn");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.querySelector(".sidebar-overlay");
  if (!menuBtn || !sidebar || !overlay) return;
  menuBtn.addEventListener("click", () => {
    sidebar.classList.toggle("open");
    overlay.classList.toggle("open");
  });
  overlay.addEventListener("click", () => {
    sidebar.classList.remove("open");
    overlay.classList.remove("open");
  });
}

function showError(inputId, errorId, message) {
  const input = document.getElementById(inputId);
  const error = document.getElementById(errorId);

  input.classList.add("error");
  error.style.display = "block";
  error.textContent = message;
}

function clearError(inputId, errorId) {
  const input = document.getElementById(inputId);
  const error = document.getElementById(errorId);

  input.classList.remove("error");
  error.style.display = "none";
}

["fieldNameAr", "fieldNameEn", "fieldQuestions"].forEach((id) => {
  const input = document.getElementById(id);
  input.addEventListener("input", () => {
    input.classList.remove("error");
    const error = input.nextElementSibling;
    if (error) error.style.display = "none";
  });
});

const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn)
  logoutBtn.addEventListener("click", () => {
    window.location.href = "../index.html";
  });