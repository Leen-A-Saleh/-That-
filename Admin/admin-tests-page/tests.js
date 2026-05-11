// testsData, monthlyData, catData are injected by tests.php

const MAIN_COLOR = "linear-gradient(180deg, #30B7C4 0%, #83C2B9 100%)";

const CAT_CONFIG = {
  "الاكتئاب":        { badge: "badge-dep",    bar: MAIN_COLOR },
  "القلق والاكتئاب": { badge: "badge-anx",    bar: MAIN_COLOR },
  "الأطفال":         { badge: "badge-child",  bar: MAIN_COLOR },
  "فرط الحركة":      { badge: "badge-adhd",   bar: MAIN_COLOR },
  "الضغط النفسي":    { badge: "badge-stress", bar: MAIN_COLOR },
  "القلق الاجتماعي": { badge: "badge-social", bar: MAIN_COLOR },
};

let editingId      = null;
let pendingDeleteId = null;
let chartInst      = null;

document.addEventListener("DOMContentLoaded", () => {
  injectMobileCardContainer();
  renderCatBars();
  renderChart();
  renderTable(testsData);
  initFilters();
  initModal();
  initSidebar();
});

// Mobile card container

function injectMobileCardContainer() {
  const tableCard = document.querySelector(".tests-table-card");
  if (!tableCard || document.getElementById("mobileCardsList")) return;
  const div = document.createElement("div");
  div.className = "mobile-cards-list";
  div.id = "mobileCardsList";
  tableCard.appendChild(div);
}

// Category bars

function renderCatBars() {
  const totComp = catData.reduce((s, c) => s + c.total, 0);
  const el = document.getElementById("catList");
  el.innerHTML = "";

  catData.forEach(({ cat, total }) => {
    const pct   = totComp ? Math.round((total / totComp) * 100) : 0;
    const color = CAT_CONFIG[cat]?.bar || "#2fa4a9";
    el.innerHTML += `
      <div class="cat-item">
        <div class="cat-header">
          <span class="cat-name">${cat}</span>
          <span class="cat-count">${total.toLocaleString("ar-EG")} (${pct}%)</span>
        </div>
        <div class="cat-bar-bg">
          <div class="cat-bar-fill" style="width:${pct}%; background:${color};"></div>
        </div>
      </div>`;
  });
}

// Monthly chart

function renderChart() {
  const ctx = document.getElementById("monthlyChart").getContext("2d");
  if (chartInst) chartInst.destroy();
  chartInst = new Chart(ctx, {
    type: "bar",
    data: {
      labels: monthlyData.labels,
      datasets: [{
        data: monthlyData.data,
        backgroundColor: "#2fa4a9",
        borderRadius: 8,
        borderSkipped: false,
      }],
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
          ticks: { font: { family: "Cairo", size: 11 }, color: "#9ca3af", maxTicksLimit: 5 },
          beginAtZero: true,
        },
      },
    },
  });
}

// Table rendering

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

  tbody.innerHTML = list.map((t) => {
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
  }).join("");
}

function renderMobileCards(list) {
  const container = document.getElementById("mobileCardsList");
  if (!container) return;

  if (!list.length) {
    container.innerHTML = `<div class="empty-state">لا توجد نتائج مطابقة</div>`;
    return;
  }

  container.innerHTML = list.map((t) => {
    const badge = CAT_CONFIG[t.cat]?.badge || "badge-dep";
    const stCls = t.status === "active" ? "status-active" : "status-draft";
    const stTxt = t.status === "active" ? "نشط" : "مسودة";
    return `
      <div class="test-mobile-card">
        <div class="test-card-top">
          <div class="test-card-names">
            <div class="test-card-name-ar">${t.nameAr}</div>
            <div class="test-card-name-en">${t.nameEn}</div>
          </div>
          <span class="status-badge ${stCls}">${stTxt}</span>
        </div>
        <div><span class="cat-badge ${badge}">${t.cat}</span></div>
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
        <div class="test-card-footer">
          <div class="test-card-actions">
            <button class="action-btn btn-view"   title="عرض"   onclick="handleView(${t.id})"><i class="fa-regular fa-eye"></i></button>
            <button class="action-btn btn-edit"   title="تعديل" onclick="handleEdit(${t.id})"><i class="fa-regular fa-pen-to-square"></i></button>
            <button class="action-btn btn-delete" title="حذف"   onclick="handleDelete(${t.id})"><i class="fa-regular fa-trash-can"></i></button>
          </div>
        </div>
      </div>`;
  }).join("");
}

// Filter

function getFiltered() {
  const q   = document.getElementById("searchInput").value.trim().toLowerCase();
  const st  = document.getElementById("statusFilter").value;
  const cat = document.getElementById("catFilter").value;
  return testsData.filter((t) =>
    (!q  || t.nameAr.includes(q) || t.nameEn.toLowerCase().includes(q)) &&
    (!st  || t.status === st) &&
    (!cat || t.cat === cat)
  );
}

function initFilters() {
  ["searchInput", "statusFilter", "catFilter"].forEach((id) => {
    document.getElementById(id).addEventListener("input",  () => renderTable(getFiltered()));
    document.getElementById(id).addEventListener("change", () => renderTable(getFiltered()));
  });
}

// View modal

function handleView(id) {
  const t = testsData.find((t) => t.id === id);
  if (!t) return;

  const stCls = t.status === "active" ? "status-active" : "status-draft";
  const stTxt = t.status === "active" ? "نشط" : "مسودة";

  document.getElementById("viewNameAr").textContent          = t.nameAr;
  document.getElementById("viewNameEn").textContent          = t.nameEn;
  document.getElementById("viewStatQuestions").textContent   = t.questions;
  document.getElementById("viewStatCompletions").textContent = t.completions.toLocaleString("ar-EG");
  document.getElementById("viewStatAvg").textContent         = t.avg.toFixed(1);
  document.getElementById("viewCatBadge").textContent        = t.cat;
  document.getElementById("viewDesc").textContent            = t.description || "—";

  const statusEl = document.getElementById("viewStatus");
  statusEl.textContent = stTxt;
  statusEl.className   = `status-badge ${stCls}`;

  document.getElementById("viewModal").classList.add("open");
}

// Add / Edit modal

function openModal(data) {
  document.getElementById("fieldNameAr").value   = data?.nameAr      || "";
  document.getElementById("fieldNameEn").value   = data?.nameEn      || "";
  document.getElementById("fieldCat").value      = data?.cat         || "الاكتئاب";
  document.getElementById("fieldQuestions").value = data?.questions  || "";
  document.getElementById("fieldStatus").value   = data?.status      || "active";
  document.getElementById("fieldDesc").value     = data?.description || "";
  document.getElementById("modalTitle").textContent = data ? "تعديل الاختبار" : "إضافة اختبار جديد";
  clearErrors();
  document.getElementById("modalOverlay").classList.add("open");
}

function closeModal() {
  document.getElementById("modalOverlay").classList.remove("open");
  editingId = null;
}

function handleEdit(id) {
  const t = testsData.find((t) => t.id === id);
  if (!t) return;
  editingId = id;
  openModal(t);
}

function handleDelete(id) {
  const t = testsData.find((t) => t.id === id);
  if (!t) return;
  pendingDeleteId = id;
  document.getElementById("deleteTestName").textContent = t.nameAr;
  document.getElementById("deleteModal").classList.add("open");
}

// Mutations

async function postAction(data) {
  const res = await fetch("tests.php", { method: "POST", body: new URLSearchParams(data) });
  return res.json();
}

async function handleSave() {
  const nameAr    = document.getElementById("fieldNameAr").value.trim();
  const nameEn    = document.getElementById("fieldNameEn").value.trim();
  const cat       = document.getElementById("fieldCat").value;
  const questions = parseInt(document.getElementById("fieldQuestions").value) || 0;
  const status    = document.getElementById("fieldStatus").value;
  const description = document.getElementById("fieldDesc").value.trim();

  clearErrors();
  let isValid = true;

  if (!nameAr)           { showError("fieldNameAr",   "errorNameAr",   "الحقل مطلوب"); isValid = false; }
  if (!nameEn)           { showError("fieldNameEn",   "errorNameEn",   "الحقل مطلوب"); isValid = false; }
  if (questions <= 0)    { showError("fieldQuestions","errorQuestions","الحقل مطلوب"); isValid = false; }
  if (!isValid) return;

  const payload = editingId !== null
    ? { action: "edit", id: editingId, nameAr, nameEn, cat, questions, status, description }
    : { action: "add",                 nameAr, nameEn, cat, questions, status, description };

  try {
    const result = await postAction(payload);
    if (!result.success) throw new Error(result.error);
    closeModal();
    setTimeout(() => window.location.reload(), 600);
  } catch (err) {
    alert(err.message || "حدث خطأ، يرجى المحاولة مجدداً");
  }
}

async function confirmDelete() {
  if (pendingDeleteId === null) return;
  try {
    const result = await postAction({ action: "delete", id: pendingDeleteId });
    if (!result.success) throw new Error(result.error);
    document.getElementById("deleteModal").classList.remove("open");
    pendingDeleteId = null;
    setTimeout(() => window.location.reload(), 600);
  } catch (err) {
    alert(err.message || "حدث خطأ، يرجى المحاولة مجدداً");
  }
}

// Validation helpers

function showError(inputId, errorId, message) {
  document.getElementById(inputId).classList.add("error");
  const el = document.getElementById(errorId);
  el.style.display = "block";
  el.textContent   = message;
}

function clearErrors() {
  ["fieldNameAr", "fieldNameEn", "fieldQuestions"].forEach((id) => {
    document.getElementById(id).classList.remove("error");
  });
  ["errorNameAr", "errorNameEn", "errorQuestions"].forEach((id) => {
    document.getElementById(id).style.display = "none";
  });
}

["fieldNameAr", "fieldNameEn", "fieldQuestions"].forEach((id) => {
  document.getElementById(id).addEventListener("input", () => {
    document.getElementById(id).classList.remove("error");
    const next = document.getElementById(id).nextElementSibling;
    if (next) next.style.display = "none";
  });
});

// Event wiring

function initModal() {
  document.getElementById("openModalBtn") .addEventListener("click", () => { editingId = null; openModal(null); });
  document.getElementById("closeModalBtn").addEventListener("click", closeModal);
  document.getElementById("cancelModalBtn").addEventListener("click", closeModal);
  document.getElementById("modalOverlay") .addEventListener("click", (e) => { if (e.target.id === "modalOverlay") closeModal(); });
  document.getElementById("saveTestBtn")  .addEventListener("click", handleSave);

  document.getElementById("closeViewBtn").addEventListener("click", () => document.getElementById("viewModal").classList.remove("open"));
  document.getElementById("viewModal")   .addEventListener("click", (e) => { if (e.target.id === "viewModal") document.getElementById("viewModal").classList.remove("open"); });

  document.getElementById("cancelDeleteBtn") .addEventListener("click", () => { document.getElementById("deleteModal").classList.remove("open"); pendingDeleteId = null; });
  document.getElementById("confirmDeleteBtn").addEventListener("click", confirmDelete);
  document.getElementById("deleteModal")     .addEventListener("click", (e) => { if (e.target.id === "deleteModal") { document.getElementById("deleteModal").classList.remove("open"); pendingDeleteId = null; } });
}

// Sidebar

function initSidebar() {
  const menuBtn = document.querySelector(".menu-btn");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.querySelector(".sidebar-overlay");
  if (!menuBtn || !sidebar || !overlay) return;
  menuBtn.addEventListener("click", () => { sidebar.classList.toggle("open"); overlay.classList.toggle("open"); });
  overlay.addEventListener("click", () => { sidebar.classList.remove("open"); overlay.classList.remove("open"); });
}

const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) logoutBtn.addEventListener("click", () => { window.location.href = "../index.php"; });