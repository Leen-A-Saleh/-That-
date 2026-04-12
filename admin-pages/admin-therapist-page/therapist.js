const ITEMS_PER_PAGE = 6;

let therapistsData = [
  {
    id: 1,
    name: "د. سارة أحمد الزهراني",
    email: "sara.ahmed@example.com",
    specialty: "علم النفس السريري",
    experience: 8,
    cases: 24,
    rating: 4.9,
    status: "متاح",
  },
  {
    id: 2,
    name: "د. محمد علي الشهري",
    email: "mohammed.ali@example.com",
    specialty: "الإرشاد النفسي",
    experience: 12,
    cases: 32,
    rating: 4.8,
    status: "متاح",
  },
  {
    id: 3,
    name: "د. فاطمة حسن القرني",
    email: "fatima.hassan@example.com",
    specialty: "علاج الإدمان",
    experience: 6,
    cases: 18,
    rating: 4.9,
    status: "مشغول",
  },
  {
    id: 4,
    name: "د. خالد يوسف العمري",
    email: "khaled.yousef@example.com",
    specialty: "علم نفس الأطفال",
    experience: 10,
    cases: 28,
    rating: 4.7,
    status: "متاح",
  },
  {
    id: 5,
    name: "د. نورة سعيد الغامدي",
    email: "nora.saeed@example.com",
    specialty: "العلاج الأسري",
    experience: 7,
    cases: 21,
    rating: 4.8,
    status: "في إجازة",
  },
  {
    id: 6,
    name: "د. عبدالله محمد الدوسري",
    email: "abdullah.m@example.com",
    specialty: "علاج القلق والاكتئاب",
    experience: 15,
    cases: 45,
    rating: 4.9,
    status: "متاح",
  },
];

let filteredData = [...therapistsData];
let currentPage = 1;
let editingId = null;
let activeDropdownRow = null;
let deletingId = null;

function getInitial(name) {
  return name
    .replace(/^د\.?\s*/, "")
    .replace(/^دكتور\s*/, "")
    .replace(/^دكتورة\s*/, "")
    .trim()
    .charAt(0);
}

function getStatusClass(status) {
  if (status === "متاح") return "available";
  if (status === "مشغول") return "busy";
  if (status === "في إجازة") return "vacation";
  return "available";
}

function openViewModal(id) {
  const t = therapistsData.find((x) => x.id === id);
  if (!t) return;
  document.getElementById("vm-avatar").textContent = getInitial(t.name);
  document.getElementById("vm-name").textContent = t.name;
  document.getElementById("vm-email").textContent = t.email;
  document.getElementById("vm-specialty").textContent = t.specialty;
  document.getElementById("vm-experience").textContent =
    t.experience + " سنوات";
  document.getElementById("vm-cases").innerHTML =
    `<span class="cases-badge">${t.cases}</span>`;
  document.getElementById("vm-rating").innerHTML =
    `<i class="fa fa-star"></i> ${t.rating}`;
  document.getElementById("vm-status").innerHTML =
    `<span class="status-badge ${getStatusClass(t.status)}">${t.status}</span>`;
  document.getElementById("viewModal").classList.add("open");
  document.body.style.overflow = "hidden";
}

function closeViewModal() {
  document.getElementById("viewModal").classList.remove("open");
  document.body.style.overflow = "";
}
window.closeViewModal = closeViewModal;

function handleDelete(id) {
  deletingId = id;
  document.getElementById("confirmModal").classList.add("open");
}

function deleteTherapist(id) {
  therapistsData = therapistsData.filter((x) => x.id !== id);
  filteredData = [...therapistsData];
  if (
    (currentPage - 1) * ITEMS_PER_PAGE >= filteredData.length &&
    currentPage > 1
  )
    currentPage--;
  applyFilters();
  updateStats();
}

function closeDropdown() {
  document.getElementById("dropdownMenu").classList.remove("open");
  activeDropdownRow = null;
}

function renderTable() {
  const tbody = document.getElementById("therapistsTableBody");
  const start = (currentPage - 1) * ITEMS_PER_PAGE;
  const pageData = filteredData.slice(start, start + ITEMS_PER_PAGE);

  if (!pageData.length) {
    tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="fa fa-users"></i><p>لا يوجد أخصائيون مطابقون للبحث</p></div></td></tr>`;
    return;
  }

  tbody.innerHTML = pageData
    .map(
      (t) => `
    <tr data-id="${t.id}">
      <td>
        <div class="therapist-info">
          <div class="therapist-avatar">${getInitial(t.name)}</div>
          <div>
            <div class="therapist-name">${t.name}</div>
            <div class="therapist-email"><img src="../images/Mail.svg" />${t.email}</div>
          </div>
        </div>
      </td>
      <td><div class="specialty-cell"><img src="../images/Award.svg" />${t.specialty}</div></td>
      <td class="experience-cell">${t.experience} سنوات</td>
      <td><span class="cases-badge">${t.cases}</span></td>
      <td><div class="rating-cell"><i class="fa fa-star"></i>${t.rating}</div></td>
      <td><span class="status-badge ${getStatusClass(t.status)}">${t.status}</span></td>
      <td>
        <div class="actions-cell">
          <button class="action-btn more-btn" data-action="more" data-id="${t.id}" title="المزيد"><i class="fa fa-ellipsis-v"></i></button>
          <button class="action-btn" data-action="edit" data-id="${t.id}" title="تعديل"><i class="fa fa-edit"></i></button>
          <button class="action-btn" data-action="view" data-id="${t.id}" title="عرض"><i class="fa fa-eye"></i></button>
        </div>
      </td>
    </tr>`,
    )
    .join("");
}

function renderMobileCards() {
  const container = document.getElementById("mobileCards");
  const start = (currentPage - 1) * ITEMS_PER_PAGE;
  const pageData = filteredData.slice(start, start + ITEMS_PER_PAGE);

  if (!pageData.length) {
    container.innerHTML = `<div class="empty-state"><i class="fa fa-users"></i><p>لا يوجد أخصائيون مطابقون للبحث</p></div>`;
    return;
  }

  container.innerHTML = pageData
    .map(
      (t) => `
    <div class="therapist-card" data-id="${t.id}">
      <div class="therapist-card-header">
        <div class="therapist-card-info">
          <div class="therapist-avatar">${getInitial(t.name)}</div>
          <div>
            <div class="therapist-name">${t.name}</div>
            <div class="therapist-email"><i class="fa fa-envelope" style="font-size:11px;color:#9ca3af;"></i>${t.email}</div>
          </div>
        </div>
        <span class="status-badge ${getStatusClass(t.status)}">${t.status}</span>
      </div>
      <div class="therapist-card-body">
        <div class="therapist-card-field">
          <span class="therapist-card-field-label">التخصص</span>
          <span class="therapist-card-field-value">${t.specialty}</span>
        </div>
        <div class="therapist-card-field">
          <span class="therapist-card-field-label">الخبرة</span>
          <span class="therapist-card-field-value">${t.experience} سنوات</span>
        </div>
        <div class="therapist-card-field">
          <span class="therapist-card-field-label">عدد الحالات</span>
          <span class="therapist-card-field-value"><span class="cases-badge">${t.cases}</span></span>
        </div>
        <div class="therapist-card-field">
          <span class="therapist-card-field-label">التقييم</span>
          <span class="therapist-card-field-value"><i class="fa fa-star"></i>${t.rating}</span>
        </div>
      </div>
      <div class="therapist-card-footer">
        <div class="actions-cell">
          <button class="action-btn" data-action="view" data-id="${t.id}" title="عرض"><i class="fa fa-eye"></i></button>
          <button class="action-btn" data-action="edit" data-id="${t.id}" title="تعديل"><i class="fa fa-edit"></i></button>
          <button class="action-btn action-btn-delete" data-action="delete" data-id="${t.id}" title="حذف"><i class="fa fa-trash"></i></button>
        </div>
      </div>
    </div>`,
    )
    .join("");
}

function renderAll() {
  renderTable();
  renderMobileCards();
  renderPagination();
  updatePaginationInfo();
}

function renderPagination() {
  const totalPages = Math.ceil(filteredData.length / ITEMS_PER_PAGE) || 1;
  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");
  const pageNumbers = document.getElementById("pageNumbers");

  prevBtn.disabled = currentPage === 1;
  nextBtn.disabled = currentPage === totalPages;

  pageNumbers.innerHTML = "";
  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement("button");
    btn.className = "page-number-btn" + (i === currentPage ? " active" : "");
    btn.textContent = i;
    btn.addEventListener("click", () => {
      currentPage = i;
      renderAll();
    });
    pageNumbers.appendChild(btn);
  }
}

function updatePaginationInfo() {
  const total = filteredData.length;
  const start = total === 0 ? 0 : (currentPage - 1) * ITEMS_PER_PAGE + 1;
  const end = Math.min(currentPage * ITEMS_PER_PAGE, total);
  document.getElementById("paginationInfo").textContent =
    `عرض ${total === 0 ? 0 : end - start + 1} من ${total} أخصائي`;
}

function updateStats() {
  const total = therapistsData.length;
  document.getElementById("statTotal").textContent = total;
  document.getElementById("statAvailable").textContent = therapistsData.filter(
    (t) => t.status === "متاح",
  ).length;
  document.getElementById("statCases").textContent = therapistsData.reduce(
    (s, t) => s + t.cases,
    0,
  );
  document.getElementById("statRating").textContent = total
    ? (therapistsData.reduce((s, t) => s + t.rating, 0) / total).toFixed(1)
    : "0.0";
}

function applyFilters() {
  const search = document
    .getElementById("searchInput")
    .value.trim()
    .toLowerCase();
  filteredData = therapistsData.filter(
    (t) =>
      !search ||
      t.name.toLowerCase().includes(search) ||
      t.email.toLowerCase().includes(search) ||
      t.specialty.toLowerCase().includes(search),
  );
  currentPage = 1;
  renderAll();
}

function clearValidation() {
  document
    .querySelectorAll(".input-error")
    .forEach((el) => el.classList.remove("input-error"));
  document.querySelectorAll(".field-error-msg").forEach((el) => el.remove());
  const banner = document.querySelector(".modal-error-banner");
  if (banner) banner.remove();
}

function markFieldError(fieldId, msg) {
  const input = document.getElementById(fieldId);
  input.classList.add("input-error");
  const err = document.createElement("span");
  err.className = "field-error-msg";
  err.innerHTML = `<i class="fa fa-circle-exclamation"></i>${msg}`;
  input.parentNode.appendChild(err);
  input.addEventListener(
    "input",
    function fix() {
      input.classList.remove("input-error");
      err.remove();
      input.removeEventListener("input", fix);
      if (!document.querySelector(".input-error")) {
        const banner = document.querySelector(".modal-error-banner");
        if (banner) banner.remove();
      }
    },
    { once: true },
  );
}

function openModal(mode, id = null) {
  editingId = id;
  document.getElementById("modalTitle").textContent =
    mode === "add" ? "إضافة أخصائي جديد" : "تعديل بيانات الأخصائي";

  if (mode === "add") {
    [
      "fieldName",
      "fieldEmail",
      "fieldSpecialty",
      "fieldExperience",
      "fieldRating",
    ].forEach((f) => (document.getElementById(f).value = ""));
    document.getElementById("fieldStatus").value = "متاح";
  } else {
    const t = therapistsData.find((x) => x.id === id);
    if (t) {
      document.getElementById("fieldName").value = t.name;
      document.getElementById("fieldEmail").value = t.email;
      document.getElementById("fieldSpecialty").value = t.specialty;
      document.getElementById("fieldExperience").value = t.experience;
      document.getElementById("fieldStatus").value = t.status;
      document.getElementById("fieldRating").value = t.rating;
    }
  }

  document.getElementById("modalOverlay").classList.add("open");
  document.body.style.overflow = "hidden";
}

function closeModal() {
  clearValidation();
  document.getElementById("modalOverlay").classList.remove("open");
  document.body.style.overflow = "";
  editingId = null;
}

function saveTherapist() {
  clearValidation();

  const name = document.getElementById("fieldName").value.trim();
  const email = document.getElementById("fieldEmail").value.trim();
  const specialty = document.getElementById("fieldSpecialty").value;
  const experience =
    parseInt(document.getElementById("fieldExperience").value) || 0;
  const status = document.getElementById("fieldStatus").value;
  const rating = parseFloat(document.getElementById("fieldRating").value) || 0;

  let hasError = false;

  if (!name) {
    markFieldError("fieldName", "الاسم الكامل مطلوب");
    hasError = true;
  }
  if (!email) {
    markFieldError("fieldEmail", "البريد الإلكتروني مطلوب");
    hasError = true;
  }
  if (!specialty) {
    markFieldError("fieldSpecialty", "يرجى اختيار التخصص");
    hasError = true;
  }

  if (hasError) {
    const banner = document.createElement("div");
    banner.className = "modal-error-banner";
    banner.innerHTML = `<i class="fa fa-triangle-exclamation"></i>يرجى تعبئة الحقول المطلوبة أدناه`;
    document.querySelector(".modal-body").prepend(banner);
    return;
  }

  if (editingId !== null) {
    const idx = therapistsData.findIndex((x) => x.id === editingId);
    if (idx !== -1)
      therapistsData[idx] = {
        ...therapistsData[idx],
        name,
        email,
        specialty,
        experience,
        status,
        rating,
      };
  } else {
    therapistsData.push({
      id: Date.now(),
      name,
      email,
      specialty,
      experience,
      cases: 0,
      rating,
      status,
    });
  }

  filteredData = [...therapistsData];
  applyFilters();
  updateStats();
  closeModal();
}

document
  .getElementById("addTherapistBtn")
  .addEventListener("click", () => openModal("add"));
document.getElementById("modalClose").addEventListener("click", closeModal);
document.getElementById("modalCancel").addEventListener("click", closeModal);
document.getElementById("modalSave").addEventListener("click", saveTherapist);
document.getElementById("modalOverlay").addEventListener("click", (e) => {
  if (e.target === document.getElementById("modalOverlay")) closeModal();
});

document.getElementById("prevBtn").addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage--;
    renderAll();
  }
});
document.getElementById("nextBtn").addEventListener("click", () => {
  if (currentPage < Math.ceil(filteredData.length / ITEMS_PER_PAGE)) {
    currentPage++;
    renderAll();
  }
});

document.getElementById("searchInput").addEventListener("input", applyFilters);

document
  .getElementById("therapistsTableBody")
  .addEventListener("click", (e) => {
    const btn = e.target.closest("[data-action]");
    if (!btn) return;
    const action = btn.dataset.action;
    const id = parseInt(btn.dataset.id);
    if (action === "view") {
      openViewModal(id);
    } else if (action === "edit") {
      openModal("edit", id);
    } else if (action === "more") {
      const dropdown = document.getElementById("dropdownMenu");
      const rect = btn.getBoundingClientRect();
      dropdown.style.top = rect.bottom + window.scrollY + 4 + "px";
      dropdown.style.left = rect.left + window.scrollX - 120 + "px";
      dropdown.classList.add("open");
      activeDropdownRow = id;
    }
  });

document.getElementById("mobileCards").addEventListener("click", (e) => {
  const btn = e.target.closest("[data-action]");
  if (!btn) return;
  const action = btn.dataset.action;
  const id = parseInt(btn.dataset.id);
  if (action === "view") openViewModal(id);
  if (action === "edit") openModal("edit", id);
  if (action === "delete") handleDelete(id);
});

document.getElementById("dropdownView").addEventListener("click", () => {
  if (activeDropdownRow !== null) openViewModal(activeDropdownRow);
  closeDropdown();
});
document.getElementById("dropdownEdit").addEventListener("click", () => {
  if (activeDropdownRow !== null) openModal("edit", activeDropdownRow);
  closeDropdown();
});
document.getElementById("dropdownDelete").addEventListener("click", () => {
  if (activeDropdownRow !== null) handleDelete(activeDropdownRow);
  closeDropdown();
});

document.addEventListener("click", (e) => {
  if (
    !e.target.closest("[data-action='more']") &&
    !e.target.closest("#dropdownMenu")
  ) {
    closeDropdown();
  }
});

document.getElementById("confirmDeleteBtn").addEventListener("click", () => {
  if (deletingId !== null) {
    deleteTherapist(deletingId);
    deletingId = null;
  }
  document.getElementById("confirmModal").classList.remove("open");
});
document.getElementById("cancelDeleteBtn").addEventListener("click", () => {
  deletingId = null;
  document.getElementById("confirmModal").classList.remove("open");
});
document.getElementById("confirmModal").addEventListener("click", (e) => {
  if (e.target === document.getElementById("confirmModal")) {
    deletingId = null;
    document.getElementById("confirmModal").classList.remove("open");
  }
});

document
  .getElementById("viewModalBackdrop")
  .addEventListener("click", closeViewModal);

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

const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn)
  logoutBtn.addEventListener("click", () => {
    window.location.href = "../index.html";
  });

applyFilters();
updateStats();
