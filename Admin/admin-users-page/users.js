const ROWS_PER_PAGE = 8;

let currentPage    = 1;
let filteredUsers  = [...usersData];   // updated by applyFilters()
let editingUserId  = null;
let deletingUserId = null;

document.addEventListener("DOMContentLoaded", () => {
  renderTable();
  attachEvents();
});

function attachEvents() {
  document.getElementById("searchInput")  .addEventListener("input",  applyFilters);
  document.getElementById("statusFilter") .addEventListener("change", applyFilters);
  document.getElementById("addUserBtn")   .addEventListener("click",  openAddModal);
  document.getElementById("exportBtn")    .addEventListener("click",  exportCSV);
  document.getElementById("saveUserBtn")  .addEventListener("click",  saveUser);
  document.getElementById("cancelModalBtn").addEventListener("click", () => closeModal("userModal"));
  document.getElementById("cancelDeleteBtn").addEventListener("click", () => closeModal("confirmModal"));
  document.getElementById("confirmDeleteBtn").addEventListener("click", confirmDelete);

  document.getElementById("userModal")   .addEventListener("click", (e) => { if (e.target.id === "userModal")    closeModal("userModal"); });
  document.getElementById("confirmModal").addEventListener("click", (e) => { if (e.target.id === "confirmModal") closeModal("confirmModal"); });

  document.getElementById("usersTableBody").addEventListener("click", (e) => {

    const viewBtn = e.target.closest(".view-btn");
    if (viewBtn) {
      viewUser(parseInt(viewBtn.dataset.id));
      return;
    }

    const moreBtn = e.target.closest(".more-btn");
    if (moreBtn) {
      e.stopPropagation();
      const menu = moreBtn.closest(".actions-wrapper").querySelector(".dropdown-menu");
      closeAllDropdowns(menu);
      menu.classList.toggle("open");
      return;
    }

    const item = e.target.closest(".dropdown-item");
    if (item) {
      const action = item.dataset.action;
      const id     = parseInt(item.dataset.id);
      closeAllDropdowns();
      if (action === "edit")   openEditModal(id);
      if (action === "toggle") toggleStatus(id);
      if (action === "delete") openDeleteConfirm(id);
    }
  });

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".actions-wrapper")) closeAllDropdowns();
  });
}

function renderTable() {
  const tbody = document.getElementById("usersTableBody");
  const start = (currentPage - 1) * ROWS_PER_PAGE;
  const pageUsers = filteredUsers.slice(start, start + ROWS_PER_PAGE);

  if (pageUsers.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="fa fa-users"></i><p>لا توجد نتائج</p></div></td></tr>`;
  } else {
    tbody.innerHTML = pageUsers.map((user) => `
      <tr>
        <td>
          <div class="user-name-cell">
            <div class="user-avatar">${user.avatar}</div>
            <span>${user.name}</span>
          </div>
        </td>
        <td data-label="البريد الإلكتروني">${user.email}</td>
        <td data-label="رقم الجوال" dir="ltr">${user.phone || '—'}</td>
        <td data-label="تاريخ التسجيل">${user.regDate}</td>
        <td data-label="عدد الجلسات"><span class="sessions-count">${user.sessions}</span></td>
        <td data-label="حالة الحساب">
          <span class="status-badge ${user.status}">
            ${user.status === "active" ? "نشط" : "موقوف"}
          </span>
        </td>
        <td>
          <div class="actions-cell">
            <button class="action-btn view-btn" data-id="${user.id}" title="عرض">
              <i class="fa fa-eye"></i>
            </button>
            <div class="actions-wrapper">
              <button class="action-btn more-btn" title="المزيد">
                <i class="fa fa-ellipsis-v"></i>
              </button>
              <div class="dropdown-menu">
                <div class="dropdown-item" data-action="edit" data-id="${user.id}">
                  <i class="fa fa-edit"></i> تعديل
                </div>
                <div class="dropdown-item" data-action="toggle" data-id="${user.id}">
                  <i class="fa fa-${user.status === "active" ? "ban" : "check-circle"}"></i>
                  ${user.status === "active" ? "تعليق الحساب" : "تفعيل الحساب"}
                </div>
                <div class="dropdown-item danger" data-action="delete" data-id="${user.id}">
                  <i class="fa fa-trash"></i> حذف
                </div>
              </div>
            </div>
          </div>
        </td>
      </tr>`).join("");
  }

  renderPagination();
}

function renderPagination() {
  const total      = filteredUsers.length;
  const totalPages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));
  const start      = Math.min((currentPage - 1) * ROWS_PER_PAGE + 1, total || 0);
  const end        = Math.min(currentPage * ROWS_PER_PAGE, total);

  document.getElementById("paginationInfo").textContent =
    `عرض ${start}–${end} من ${total} مستخدم`;

  let html = `<button class="page-btn page-nav" ${currentPage === 1 ? "disabled" : ""} onclick="goPage(${currentPage - 1})">السابق</button>`;
  for (let i = 1; i <= totalPages; i++) {
    html += `<button class="page-btn ${i === currentPage ? "active" : ""}" onclick="goPage(${i})">${i}</button>`;
  }
  html += `<button class="page-btn page-nav" ${currentPage === totalPages ? "disabled" : ""} onclick="goPage(${currentPage + 1})">التالي</button>`;
  document.getElementById("paginationBtns").innerHTML = html;
}

function goPage(n) {
  const totalPages = Math.ceil(filteredUsers.length / ROWS_PER_PAGE);
  if (n < 1 || n > totalPages) return;
  currentPage = n;
  renderTable();
}

function normalize(text) {
  return text.toLowerCase().trim().replace(/\s+/g, " ");
}

function applyFilters() {
  const query  = normalize(document.getElementById("searchInput").value);
  const status = document.getElementById("statusFilter").value;

  filteredUsers = usersData.filter((u) => {
    const matchSearch =
      !query ||
      normalize(u.name).includes(query)  ||
      normalize(u.email).includes(query) ||
      normalize(u.phone).includes(query);
    const matchStatus = status === "all" || u.status === status;
    return matchSearch && matchStatus;
  });

  currentPage = 1;
  renderTable();
}

function openModal(id)  { document.getElementById(id).classList.add("open"); }
function closeModal(id) { document.getElementById(id).classList.remove("open"); }

function clearModalForm() {
  ["fieldFirstName", "fieldLastName", "fieldEmail", "fieldPhone"].forEach(
    (id) => (document.getElementById(id).value = "")
  );
  document.getElementById("fieldStatus").value = "active";
}

function openAddModal() {
  editingUserId = null;
  document.getElementById("modalTitle").textContent = "إضافة مستخدم جديد";
  clearModalForm();
  openModal("userModal");
}

function openEditModal(id) {
  const user = usersData.find((u) => u.id === id);
  if (!user) return;
  editingUserId = id;
  document.getElementById("modalTitle").textContent = "تعديل بيانات المستخدم";
  const parts = user.name.split(" ");
  document.getElementById("fieldFirstName").value = parts[0] || "";
  document.getElementById("fieldLastName").value  = parts.slice(1).join(" ") || "";
  document.getElementById("fieldEmail").value     = user.email;
  document.getElementById("fieldPhone").value     = user.phone;
  document.getElementById("fieldStatus").value    = user.status;
  openModal("userModal");
}

function openDeleteConfirm(id) {
  deletingUserId = id;
  openModal("confirmModal");
}

async function postAction(data) {
  const body = new URLSearchParams(data);
  const res  = await fetch("users.php", { method: "POST", body });
  return res.json();
}

async function saveUser() {
  const firstName = document.getElementById("fieldFirstName").value.trim();
  const lastName  = document.getElementById("fieldLastName").value.trim();
  const email     = document.getElementById("fieldEmail").value.trim();
  const phone     = document.getElementById("fieldPhone").value.trim();
  const status    = document.getElementById("fieldStatus").value;

  if (!firstName || !lastName || !email) {
    showToast("يرجى تعبئة جميع الحقول المطلوبة", "error");
    return;
  }

  const name = `${firstName} ${lastName}`;

  const payload = editingUserId
    ? { action: "edit", id: editingUserId, name, email, phone, status }
    : { action: "add",                     name, email, phone, status };

  try {
    const result = await postAction(payload);
    if (!result.success) throw new Error(result.error);
    showToast(editingUserId ? "تم تعديل بيانات المستخدم بنجاح" : "تم إضافة المستخدم بنجاح");
    closeModal("userModal");
    // Short delay so the toast is visible before reload
    setTimeout(() => window.location.reload(), 800);
  } catch (err) {
    showToast(err.message || "حدث خطأ، يرجى المحاولة مجدداً", "error");
  }
}

async function toggleStatus(id) {
  try {
    const result = await postAction({ action: "toggle", id });
    if (!result.success) throw new Error(result.error);
    showToast(result.status === "active" ? "تم تفعيل الحساب" : "تم تعليق الحساب");
    setTimeout(() => window.location.reload(), 800);
  } catch (err) {
    showToast(err.message || "حدث خطأ، يرجى المحاولة مجدداً", "error");
  }
}

async function confirmDelete() {
  if (!deletingUserId) return;
  try {
    const result = await postAction({ action: "delete", id: deletingUserId });
    if (!result.success) throw new Error(result.error);
    closeModal("confirmModal");
    showToast("تم حذف المستخدم");
    setTimeout(() => window.location.reload(), 800);
  } catch (err) {
    showToast(err.message || "حدث خطأ، يرجى المحاولة مجدداً", "error");
  }
}

function viewUser(id) {
  const user = usersData.find((u) => u.id === id);
  if (!user) return;

  document.getElementById("vm-avatar").textContent   = user.avatar;
  document.getElementById("vm-name").textContent     = user.name;
  document.getElementById("vm-email").textContent    = user.email;
  document.getElementById("vm-phone").setAttribute("dir", "ltr");
  document.getElementById("vm-phone").textContent    = user.phone || '—';
  document.getElementById("vm-date").textContent     = user.regDate;
  document.getElementById("vm-sessions").textContent = user.sessions + " جلسة";
  document.getElementById("vm-status-badge").innerHTML =
    `<span class="status-badge ${user.status}">${user.status === "active" ? "نشط" : "موقوف"}</span>`;

  document.getElementById("userModalCard").classList.add("show");
  document.body.style.overflow = "hidden";
}

function closeUserCard() {
  document.getElementById("userModalCard").classList.remove("show");
  document.body.style.overflow = "";
}

function exportCSV() {
  const headers = ["الاسم", "البريد الإلكتروني", "رقم الجوال", "تاريخ التسجيل", "عدد الجلسات", "الحالة"];
  const rows = filteredUsers.map((u) => [
    u.name, u.email, u.phone, u.regDate, u.sessions,
    u.status === "active" ? "نشط" : "موقوف",
  ]);
  const csv  = "\uFEFF" + [headers, ...rows].map((r) => r.join(",")).join("\n");
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  link.href     = URL.createObjectURL(blob);
  link.download = "users_export.csv";
  link.click();
  showToast("تم تصدير البيانات بنجاح");
}

function closeAllDropdowns(except = null) {
  document.querySelectorAll(".dropdown-menu.open").forEach((m) => {
    if (m !== except) m.classList.remove("open");
  });
}

function showToast(msg, type = "success") {
  const toast = document.getElementById("toast");
  if (!toast) return;
  toast.textContent = msg;
  toast.className   = `toast show${type === "error" ? " error" : ""}`;
  setTimeout(() => toast.classList.remove("show"), 3000);
}

const menuBtn       = document.querySelector(".menu-btn");
const sidebar       = document.querySelector(".sidebar");
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
if (logoutBtn) {
  logoutBtn.addEventListener("click", () => {
    localStorage.removeItem("token");
    window.location.href = "../login-page/login.php";
  });
}
