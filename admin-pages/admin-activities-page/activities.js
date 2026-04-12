let activitiesData = [
  {
    id: 1,
    title: "تمرين التنفس",
    type: "تفاعل",
    cat: "تمارين",
    duration: "5 دقائق",
    views: 1234,
    downloads: 542,
    status: "active",
    link: "loader.html?game=./breathing-game/index.html",
    date: "15-01-2024",
  },
  {
    id: 2,
    title: "الفروقات بين الصور",
    type: "تفاعل",
    cat: "ألعاب التركيز للأطفال",
    duration: "10 دقائق",
    views: 876,
    downloads: 398,
    status: "active",
    link: "loader.html?game=./difference-game/index.html",
    date: "01-02-2024",
  },
  {
    id: 3,
    title: "ابحث عن العنصر المفقود",
    type: "تفاعل",
    cat: "ألعاب التركيز للأطفال",
    duration: "8 دقائق",
    views: 1456,
    downloads: 621,
    status: "active",
    link: "loader.html?game=./misplacedpin/index.html",
    date: "20-01-2024",
  },
  {
    id: 4,
    title: "لعبة البطاقات",
    type: "تفاعل",
    cat: "ألعاب الذاكرة للأطفال",
    duration: "15 دقائق",
    views: 1089,
    downloads: 489,
    status: "active",
    link: "loader.html?game=./memory-game/index.html",
    date: "10-02-2024",
  },
  {
    id: 5,
    title: "الكلمات المتقاطعة",
    type: "تفاعل",
    cat: "ألعاب الذاكرة للأطفال",
    duration: "12 دقائق",
    views: 1678,
    downloads: 734,
    status: "active",
    link: "loader.html?game=./crossword-game/index.html",
    date: "25-01-2024",
  },
  {
    id: 6,
    title: "صورة وعليها أسئلة بعد ما تختفي",
    type: "تفاعل",
    cat: "ألعاب الذاكرة للأطفال",
    duration: "10 دقائق",
    views: 923,
    downloads: 445,
    status: "inactive",
    link: "loader.html?game=./questions/index.html",
    date: "05-02-2024",
  },
];

let activeTab = "all";
let editingId = null;
let pendingDeleteId = null;

const TYPE_CONFIG = {
  تفاعل: { cls: "badge-interactive", icon: "fa-solid fa-play" },
  فيديو: { cls: "badge-video", icon: "fa-solid fa-video" },
  PDF: { cls: "badge-pdf", icon: "fa-regular fa-file-pdf" },
  صوت: { cls: "badge-audio", icon: "fa-solid fa-headphones" },
  صورة: { cls: "badge-image", icon: "fa-regular fa-image" },
};

const THUMB_ICON = {
  تفاعل: "fa-solid fa-play",
  فيديو: "fa-solid fa-video",
  PDF: "fa-regular fa-file-lines",
  صوت: "fa-solid fa-headphones",
  صورة: "fa-regular fa-image",
};

function getFiltered() {
  if (activeTab === "all") return activitiesData;
  return activitiesData.filter((a) => a.cat === activeTab);
}

function renderStats() {
  const total = activitiesData.length;
  const views = activitiesData.reduce((s, a) => s + a.views, 0);
  const downloads = activitiesData.reduce((s, a) => s + a.downloads, 0);
  const active = activitiesData.filter((a) => a.status === "active").length;

  document.getElementById("statTotal").textContent = total;
  document.getElementById("statViews").textContent =
    views.toLocaleString("ar-EG");
  document.getElementById("statDownloads").textContent =
    downloads.toLocaleString("ar-EG");
  document.getElementById("statActive").textContent = active;
}

function renderTabs() {
  const cats = ["all", ...new Set(activitiesData.map((a) => a.cat))];
  const container = document.getElementById("tabsContainer");
  container.innerHTML = cats
    .map((cat) => {
      const count =
        cat === "all"
          ? activitiesData.length
          : activitiesData.filter((a) => a.cat === cat).length;
      const label = cat === "all" ? "الكل" : cat;
      return `
      <button class="tab-btn${activeTab === cat ? " active" : ""}" data-cat="${cat}">
        ${label}
        <span class="tab-count">${count}</span>
      </button>`;
    })
    .join("");

  container.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      activeTab = btn.dataset.cat;
      renderTabs();
      renderGrid();
    });
  });
}

function renderGrid() {
  const list = getFiltered();
  const grid = document.getElementById("actsGrid");

  if (!list.length) {
    grid.innerHTML = `<div class="empty-acts"><i class="fa-solid fa-gamepad"></i><p>لا توجد أنشطة في هذه الفئة</p></div>`;
    return;
  }

  grid.innerHTML = list
    .map((a) => {
      const typeCfg = TYPE_CONFIG[a.type] || TYPE_CONFIG["تفاعل"];
      const thumbIcon = THUMB_ICON[a.type] || "fa-solid fa-play";
      const isActive = a.status === "active";
      return `
      <div class="act-card" data-id="${a.id}">
        <div class="act-card-thumb">
          <i class="${thumbIcon}"></i>
        </div>
        <div class="act-card-body">
          <div class="act-card-title-row">
            <span class="act-card-title">${a.title}</span>
            <div class="toggle-wrap">
              <input type="checkbox" class="toggle-input" id="toggle-${a.id}" ${isActive ? "checked" : ""} data-id="${a.id}" />
              <label class="toggle-label" for="toggle-${a.id}"></label>
            </div>
          </div>
          <div class="act-card-badges">
            <span class="type-badge ${typeCfg.cls}">
              <i class="${typeCfg.icon}" style="font-size:10px;"></i>
              ${a.type}
            </span>
          </div>
          <div class="act-card-cat">${a.cat}</div>
          ${a.duration ? `<div class="act-card-duration"><i class="fa-regular fa-clock"></i>${a.duration}</div>` : ""}
          <hr class="act-card-divider" />
          <div class="act-card-stats">
            <div class="act-stat-item">
              <span class="act-stat-num">${a.views.toLocaleString("ar-EG")}</span>
              <span class="act-stat-label">المشاهدات</span>
            </div>
            <div class="act-stat-item">
              <span class="act-stat-num">${a.downloads.toLocaleString("ar-EG")}</span>
              <span class="act-stat-label">التزيلات</span>
            </div>
          </div>
          <div class="act-card-actions">
            <button class="act-action-btn act-btn-delete" data-action="delete" data-id="${a.id}" title="حذف">
              <i class="fa-regular fa-trash-can"></i>
            </button>
            <button class="act-action-btn act-btn-edit" data-action="edit" data-id="${a.id}" title="تعديل">
              <i class="fa-regular fa-pen-to-square"></i>
            </button>
            <button class="preview-btn" data-action="preview" data-id="${a.id}">
              <i class="fa-regular fa-eye"></i>
              معاينة
            </button>
          </div>
          <div class="act-card-date">تم الرفع: ${a.date}</div>
        </div>
      </div>`;
    })
    .join("");

  grid.querySelectorAll(".toggle-input").forEach((toggle) => {
    toggle.addEventListener("change", () => {
      const id = parseInt(toggle.dataset.id);
      const item = activitiesData.find((a) => a.id === id);
      if (item) {
        item.status = toggle.checked ? "active" : "inactive";
        renderStats();
        renderTabs();
      }
    });
  });

  grid.querySelectorAll("[data-action]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const action = btn.dataset.action;
      const id = parseInt(btn.dataset.id);
      if (action === "delete") openDeleteModal(id);
      if (action === "edit") openModal(id);
      if (action === "preview") handlePreview(id);
    });
  });
}

function renderAll() {
  renderStats();
  renderTabs();
  renderGrid();
}

function handlePreview(id) {
  const a = activitiesData.find((x) => x.id === id);
  if (a && a.link) window.open(a.link, "_blank");
}

function openModal(id = null) {
  editingId = id;
  document.getElementById("modalTitle").textContent = id
    ? "تعديل النشاط"
    : "رفع نشاط جديد";

  if (id) {
    const a = activitiesData.find((x) => x.id === id);
    if (a) {
      document.getElementById("fieldTitle").value = a.title;
      document.getElementById("fieldCat").value = a.cat;
      document.getElementById("fieldType").value = a.type;
      document.getElementById("fieldDuration").value = a.duration || "";
      document.getElementById("fieldLink").value = a.link;
      document.getElementById("fieldStatus").value = a.status;
    }
  } else {
    document.getElementById("fieldTitle").value = "";
    document.getElementById("fieldCat").value = "تمارين";
    document.getElementById("fieldType").value = "تفاعل";
    document.getElementById("fieldDuration").value = "";
    document.getElementById("fieldLink").value = "";
    document.getElementById("fieldStatus").value = "active";
  }

  document.getElementById("modalOverlay").classList.add("open");
}

function closeModal() {
  document.getElementById("modalOverlay").classList.remove("open");
  editingId = null;
  clearFieldErrors();
}

function setFieldError(fieldId, msg) {
  const input = document.getElementById(fieldId);
  input.classList.add("input-error");
  let err = input.parentElement.querySelector(".field-error-msg");
  if (!err) {
    err = document.createElement("span");
    err.className = "field-error-msg";
    input.parentElement.appendChild(err);
  }
  err.textContent = msg;
}

function clearFieldErrors() {
  document
    .querySelectorAll(".input-error")
    .forEach((el) => el.classList.remove("input-error"));
  document.querySelectorAll(".field-error-msg").forEach((el) => el.remove());
}

function saveActivity() {
  clearFieldErrors();
  const title = document.getElementById("fieldTitle").value.trim();
  const cat = document.getElementById("fieldCat").value;
  const type = document.getElementById("fieldType").value;
  const duration = document.getElementById("fieldDuration").value.trim();
  const link = document.getElementById("fieldLink").value.trim();
  const status = document.getElementById("fieldStatus").value;

  let hasError = false;
  if (!title) {
    setFieldError("fieldTitle", "هذا الحقل مطلوب");
    hasError = true;
  }
  if (!link) {
    setFieldError("fieldLink", "هذا الحقل مطلوب");
    hasError = true;
  }
  if (hasError) return;

  if (editingId !== null) {
    const idx = activitiesData.findIndex((a) => a.id === editingId);
    if (idx !== -1)
      activitiesData[idx] = {
        ...activitiesData[idx],
        title,
        cat,
        type,
        duration,
        link,
        status,
      };
  } else {
    const today = new Date();
    const d = String(today.getDate()).padStart(2, "0");
    const m = String(today.getMonth() + 1).padStart(2, "0");
    const y = today.getFullYear();
    activitiesData.push({
      id: Date.now(),
      title,
      cat,
      type,
      duration,
      link,
      status,
      views: 0,
      downloads: 0,
      date: `${d}-${m}-${y}`,
    });
  }

  closeModal();
  renderAll();
}

function openDeleteModal(id) {
  pendingDeleteId = id;
  const a = activitiesData.find((x) => x.id === id);
  document.getElementById("deleteActivityName").textContent = a ? a.title : "";
  document.getElementById("deleteModal").classList.add("open");
}

function closeDeleteModal() {
  document.getElementById("deleteModal").classList.remove("open");
  pendingDeleteId = null;
}

document.addEventListener("DOMContentLoaded", () => {
  renderAll();

  document
    .getElementById("openModalBtn")
    .addEventListener("click", () => openModal());
  document
    .getElementById("closeModalBtn")
    .addEventListener("click", closeModal);
  document
    .getElementById("cancelModalBtn")
    .addEventListener("click", closeModal);
  document
    .getElementById("saveActivityBtn")
    .addEventListener("click", saveActivity);

  document.getElementById("modalOverlay").addEventListener("click", (e) => {
    if (e.target === document.getElementById("modalOverlay")) closeModal();
  });

  document.getElementById("confirmDeleteBtn").addEventListener("click", () => {
    if (pendingDeleteId !== null) {
      activitiesData = activitiesData.filter((a) => a.id !== pendingDeleteId);
      pendingDeleteId = null;
      renderAll();
    }
    closeDeleteModal();
  });

  document
    .getElementById("cancelDeleteBtn")
    .addEventListener("click", closeDeleteModal);

  document.getElementById("deleteModal").addEventListener("click", (e) => {
    if (e.target === document.getElementById("deleteModal")) closeDeleteModal();
  });

  const menuBtn = document.querySelector(".menu-btn");
  const sidebar = document.querySelector(".sidebar");
  const sidebarOverlay = document.querySelector(".sidebar-overlay");

  if (menuBtn && sidebar && sidebarOverlay) {
    menuBtn.addEventListener("click", () => {
      sidebar.classList.toggle("open");
      sidebarOverlay.classList.toggle("active");
    });
    sidebarOverlay.addEventListener("click", () => {
      sidebar.classList.remove("open");
      sidebarOverlay.classList.remove("active");
    });
  }
});
const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn)
  logoutBtn.addEventListener("click", () => {
    window.location.href = "../index.html";
  });