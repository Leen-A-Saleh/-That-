let usersData = [
  {
    id: 1,
    name: "أحمد محمد السعيد",
    email: "ahmed.m@example.com",
    phone: "0501234567",
    regDate: "2024-01-15",
    sessions: 12,
    status: "active",
    avatar: "أ",
  },
  {
    id: 2,
    name: "فاطمة علي حسن",
    email: "fatima.ali@example.com",
    phone: "0509876543",
    regDate: "2024-02-20",
    sessions: 8,
    status: "active",
    avatar: "ف",
  },
  {
    id: 3,
    name: "خالد يوسف العمري",
    email: "khaled.y@example.com",
    phone: "0551234567",
    regDate: "2024-01-30",
    sessions: 15,
    status: "active",
    avatar: "خ",
  },
  {
    id: 4,
    name: "نورة سعيد الغامدي",
    email: "nora.s@example.com",
    phone: "0558765432",
    regDate: "2023-12-10",
    sessions: 24,
    status: "active",
    avatar: "ن",
  },
  {
    id: 5,
    name: "عمر حسن القحطاني",
    email: "omar.h@example.com",
    phone: "0541234567",
    regDate: "2024-02-01",
    sessions: 6,
    status: "suspended",
    avatar: "ع",
  },
  {
    id: 6,
    name: "ريم عبدالله المطيري",
    email: "reem.a@example.com",
    phone: "0549876543",
    regDate: "2024-01-05",
    sessions: 18,
    status: "active",
    avatar: "ر",
  },
  {
    id: 7,
    name: "محمد سالم العتيبي",
    email: "mohammed.s@example.com",
    phone: "0501112233",
    regDate: "2023-11-20",
    sessions: 30,
    status: "active",
    avatar: "م",
  },
  {
    id: 8,
    name: "سارة فهد الدوسري",
    email: "sara.f@example.com",
    phone: "0559988776",
    regDate: "2024-02-15",
    sessions: 4,
    status: "active",
    avatar: "س",
  },
  {
    id: 9,
    name: "ناصر علي الشهري",
    email: "nasser.a@example.com",
    phone: "0562345678",
    regDate: "2024-03-01",
    sessions: 9,
    status: "active",
    avatar: "ن",
  },
  {
    id: 10,
    name: "هند سالم الزهراني",
    email: "hind.s@example.com",
    phone: "0574567890",
    regDate: "2024-03-10",
    sessions: 3,
    status: "suspended",
    avatar: "ه",
  },
];

let currentPage = 1;
const rowsPerPage = 8;
let filteredUsers = [...usersData];
let editingUserId = null;
let deletingUserId = null;

document.addEventListener("DOMContentLoaded", () => {
  updateStats();
  renderTable();
  attachEvents();
});

function normalize(text) {
  return text.toLowerCase().trim().replace(/\s+/g, " ");
}

function attachEvents() {
  document
    .getElementById("searchInput")
    .addEventListener("input", applyFilters);
  document
    .getElementById("statusFilter")
    .addEventListener("change", applyFilters);
  document.getElementById("addUserBtn").addEventListener("click", openAddModal);
  document.getElementById("exportBtn").addEventListener("click", exportCSV);
  document.getElementById("saveUserBtn").addEventListener("click", saveUser);
  document
    .getElementById("cancelModalBtn")
    .addEventListener("click", () => closeModal("userModal"));
  document
    .getElementById("cancelDeleteBtn")
    .addEventListener("click", () => closeModal("confirmModal"));
  document
    .getElementById("confirmDeleteBtn")
    .addEventListener("click", confirmDelete);

  document.getElementById("userModal").addEventListener("click", (e) => {
    if (e.target.id === "userModal") closeModal("userModal");
  });

  document.getElementById("confirmModal").addEventListener("click", (e) => {
    if (e.target.id === "confirmModal") closeModal("confirmModal");
  });

  document.getElementById("usersTableBody").addEventListener("click", (e) => {
    const viewBtn = e.target.closest(".view-btn");
    if (viewBtn) {
      viewUser(parseInt(viewBtn.dataset.id));
      return;
    }

    const moreBtn = e.target.closest(".more-btn");
    if (moreBtn) {
      e.stopPropagation();
      const menu = moreBtn
        .closest(".actions-wrapper")
        .querySelector(".dropdown-menu");
      closeAllDropdowns(menu);
      menu.classList.toggle("open");
      return;
    }

    const item = e.target.closest(".dropdown-item");
    if (item) {
      const action = item.dataset.action;
      const id = parseInt(item.dataset.id);
      closeAllDropdowns();
      if (action === "edit") openEditModal(id);
      if (action === "toggle") toggleStatus(id);
      if (action === "delete") openDeleteConfirm(id);
    }
  });

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".actions-wrapper")) closeAllDropdowns();
  });
}

function updateStats() {
  document.getElementById("statTotal").textContent = usersData.length;
  document.getElementById("statActive").textContent = usersData.filter(
    (u) => u.status === "active",
  ).length;
  document.getElementById("statSuspended").textContent = usersData.filter(
    (u) => u.status === "suspended",
  ).length;
  document.getElementById("statSessions").textContent = usersData.reduce(
    (s, u) => s + u.sessions,
    0,
  );
}

function renderTable() {
  const tbody = document.getElementById("usersTableBody");
  const start = (currentPage - 1) * rowsPerPage;
  const pageUsers = filteredUsers.slice(start, start + rowsPerPage);

  if (pageUsers.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="fa fa-users"></i><p>لا توجد نتائج</p></div></td></tr>`;
  } else {
    tbody.innerHTML = pageUsers
      .map(
        (user) => `
      <tr>
        <td>
          <div class="user-name-cell">
            <div class="user-avatar">${user.avatar}</div>
            <span>${user.name}</span>
          </div>
        </td>
        <td data-label="البريد الإلكتروني">${user.email}</td>
        <td data-label="رقم الجوال">${user.phone}</td>
        <td data-label="تاريخ التسجيل">${user.regDate}</td>
        <td data-label="عدد الجلسات"><span class="sessions-count">${user.sessions}</span></td>
        <td data-label="حالة الحساب">
          <span class="status-badge ${user.status}">${user.status === "active" ? "نشط" : "موقوف"}</span>
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
                <div class="dropdown-item" data-action="edit" data-id="${user.id}"><i class="fa fa-edit"></i> تعديل</div>
                <div class="dropdown-item" data-action="toggle" data-id="${user.id}">
                  <i class="fa fa-${user.status === "active" ? "ban" : "check-circle"}"></i>
                  ${user.status === "active" ? "تعليق الحساب" : "تفعيل الحساب"}
                </div>
                <div class="dropdown-item danger" data-action="delete" data-id="${user.id}"><i class="fa fa-trash"></i> حذف</div>
              </div>
            </div>
          </div>
        </td>
      </tr>`,
      )
      .join("");
  }

  renderPagination();
}

function renderPagination() {
  const total = filteredUsers.length;
  const totalPages = Math.max(1, Math.ceil(total / rowsPerPage));
  const start = Math.min((currentPage - 1) * rowsPerPage + 1, total || 0);
  const end = Math.min(currentPage * rowsPerPage, total);

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
  const totalPages = Math.ceil(filteredUsers.length / rowsPerPage);
  if (n < 1 || n > totalPages) return;
  currentPage = n;
  renderTable();
}

function applyFilters() {
  const query = normalize(document.getElementById("searchInput").value);
  const status = document.getElementById("statusFilter").value;

  filteredUsers = usersData.filter((u) => {
    const name = normalize(u.name);
    const email = normalize(u.email);
    const phone = normalize(u.phone);
    const matchSearch =
      !query ||
      name.includes(query) ||
      email.includes(query) ||
      phone.includes(query) ||
      name.split(" ").some((part) => part.includes(query));
    const matchStatus = status === "all" || u.status === status;
    return matchSearch && matchStatus;
  });

  currentPage = 1;
  renderTable();
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
  const nameParts = user.name.split(" ");
  document.getElementById("fieldFirstName").value = nameParts[0] || "";
  document.getElementById("fieldLastName").value =
    nameParts.slice(1).join(" ") || "";
  document.getElementById("fieldEmail").value = user.email;
  document.getElementById("fieldPhone").value = user.phone;
  document.getElementById("fieldStatus").value = user.status;
  openModal("userModal");
}

function openDeleteConfirm(id) {
  deletingUserId = id;
  openModal("confirmModal");
}

function openModal(id) {
  document.getElementById(id).classList.add("open");
}
function closeModal(id) {
  document.getElementById(id).classList.remove("open");
}

function clearModalForm() {
  ["fieldFirstName", "fieldLastName", "fieldEmail", "fieldPhone"].forEach(
    (id) => {
      document.getElementById(id).value = "";
    },
  );
  document.getElementById("fieldStatus").value = "active";
}

function saveUser() {
  const firstName = document.getElementById("fieldFirstName").value.trim();
  const lastName = document.getElementById("fieldLastName").value.trim();
  const email = document.getElementById("fieldEmail").value.trim();
  const phone = document.getElementById("fieldPhone").value.trim();
  const status = document.getElementById("fieldStatus").value;

  if (!firstName || !lastName || !email || !phone) {
    showToast("يرجى تعبئة جميع الحقول المطلوبة", "error");
    return;
  }

  const fullName = `${firstName} ${lastName}`;
  const avatarChar = firstName.charAt(0);

  if (editingUserId) {
    const idx = usersData.findIndex((u) => u.id === editingUserId);
    if (idx !== -1) {
      usersData[idx] = {
        ...usersData[idx],
        name: fullName,
        email,
        phone,
        status,
        avatar: avatarChar,
      };
    }
    showToast("تم تعديل بيانات المستخدم بنجاح");
  } else {
    usersData.unshift({
      id: Date.now(),
      name: fullName,
      email,
      phone,
      regDate: new Date().toISOString().split("T")[0],
      sessions: 0,
      status,
      avatar: avatarChar,
    });
    showToast("تم إضافة المستخدم بنجاح");
  }

  applyFilters();
  updateStats();
  closeModal("userModal");
}

function toggleStatus(id) {
  const user = usersData.find((u) => u.id === id);
  if (!user) return;
  user.status = user.status === "active" ? "suspended" : "active";
  applyFilters();
  updateStats();
  showToast(user.status === "active" ? "تم تفعيل الحساب" : "تم تعليق الحساب");
}

function confirmDelete() {
  if (!deletingUserId) return;
  usersData = usersData.filter((u) => u.id !== deletingUserId);
  deletingUserId = null;
  applyFilters();
  updateStats();
  closeModal("confirmModal");
  showToast("تم حذف المستخدم");
}

function viewUser(id) {
  const user = usersData.find((u) => u.id === id);
  if (!user) return;

  document.getElementById("vm-avatar").textContent = user.avatar;
  document.getElementById("vm-name").textContent = user.name;
  document.getElementById("vm-email").textContent = user.email;
  document.getElementById("vm-phone").textContent = user.phone;
  document.getElementById("vm-date").textContent = user.regDate;
  document.getElementById("vm-sessions").textContent = user.sessions + " جلسة";

  const statusBadge = document.getElementById("vm-status-badge");
  statusBadge.innerHTML = `<span class="status-badge ${user.status}">${user.status === "active" ? "نشط" : "موقوف"}</span>`;

  document.getElementById("userModalCard").classList.add("show");
  document.body.style.overflow = "hidden";
}

function closeUserCard() {
  document.getElementById("userModalCard").classList.remove("show");
  document.body.style.overflow = "";
}

function exportCSV() {
  const headers = [
    "الاسم",
    "البريد الإلكتروني",
    "رقم الجوال",
    "تاريخ التسجيل",
    "عدد الجلسات",
    "الحالة",
  ];
  const rows = filteredUsers.map((u) => [
    u.name,
    u.email,
    u.phone,
    u.regDate,
    u.sessions,
    u.status === "active" ? "نشط" : "موقوف",
  ]);
  const csv = "\uFEFF" + [headers, ...rows].map((r) => r.join(",")).join("\n");
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
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
  toast.className = `toast show${type === "error" ? " error" : ""}`;
  setTimeout(() => toast.classList.remove("show"), 3000);
}

const menuBtn = document.querySelector(".menu-btn");
const sidebar = document.querySelector(".sidebar");
const sidebarOverlay = document.querySelector(".sidebar-overlay");
const logoutBtn = document.getElementById("logoutBtn");

menuBtn.addEventListener("click", () => {
  sidebar.classList.toggle("open");
  sidebarOverlay.classList.toggle("open");
});

sidebarOverlay.addEventListener("click", () => {
  sidebar.classList.remove("open");
  sidebarOverlay.classList.remove("open");
});

logoutBtn.addEventListener("click", () => {
  localStorage.removeItem("token");
  window.location.href = "../login-page/login.html";
});
