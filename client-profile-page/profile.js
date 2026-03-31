let user = JSON.parse(localStorage.getItem("user")) || {};

function loadUserData() {
  const elements = [
    { id: "profileName", value: user.name },
    { id: "profileEmail", value: user.email },
    { id: "profilePhone", value: user.phone },
    { id: "profileBirth", value: user.birthdate },
  ];

  elements.forEach((el) => {
    const dom = document.getElementById(el.id);
    if (dom) dom.innerText = el.value || "";
  });

  const doctorName = document.querySelector(".doctor-name");
  if (doctorName) doctorName.innerText = user.name || "مستخدم";

  const avatarInitial = document.querySelector(".avatar-initial");
  if (avatarInitial) {
    avatarInitial.innerText = user.name ? user.name.charAt(0) : "م";
  }

  if (user.avatar) {
    const img = document.getElementById("avatarImage");
    if (img) {
      img.src = user.avatar;
      img.style.display = "block";
    }
  }
}

function enableEdit() {
  document.getElementById("nameInput").value = user.name || "";
  document.getElementById("emailInput").value = user.email || "";
  document.getElementById("phoneInput").value = user.phone || "";
  document.getElementById("birthInput").value = user.birthdate || "";
  document.getElementById("editForm").style.display = "block";
}

function saveProfile() {
  user.name = document.getElementById("nameInput").value;
  user.email = document.getElementById("emailInput").value;
  user.phone = document.getElementById("phoneInput").value;
  user.birthdate = document.getElementById("birthInput").value;

  localStorage.setItem("user", JSON.stringify(user));

  loadUserData();

  document.getElementById("editForm").style.display = "none";

  showMessage("تم تحديث البيانات بنجاح");
}

function changePassword() {
  const oldPass = document.getElementById("oldPassword").value;
  const newPass = document.getElementById("newPassword").value;

  if (!oldPass || !newPass) {
    showMessage("يرجى إدخال كلمة المرور", true);
    return;
  }

  localStorage.setItem("password", newPass);

  document.getElementById("oldPassword").value = "";
  document.getElementById("newPassword").value = "";

  showMessage("تم تغيير كلمة المرور");
}

function saveNotifications() {
  const settings = {
    appointments: document.getElementById("notifyAppointments")?.checked,
    messages: document.getElementById("notifyMessages")?.checked,
    activities: document.getElementById("notifyActivities")?.checked,
  };

  localStorage.setItem("notifications", JSON.stringify(settings));

  showMessage("تم حفظ الإعدادات");
}

function deleteAccount() {
  if (!confirm("هل أنت متأكد من حذف الحساب؟")) return;

  localStorage.clear();

  window.location.href = "../login.html";
}

function logout() {
  localStorage.removeItem("token");
  window.location.href = "../login.html";
}

function showMessage(text, error = false) {
  let toast = document.getElementById("toast");

  if (!toast) {
    toast = document.createElement("div");
    toast.id = "toast";
    toast.style.position = "fixed";
    toast.style.top = "20px";
    toast.style.left = "50%";
    toast.style.transform = "translateX(-50%)";
    toast.style.padding = "12px 24px";
    toast.style.borderRadius = "8px";
    toast.style.color = "white";
    toast.style.fontWeight = "bold";
    toast.style.zIndex = "9999";
    document.body.appendChild(toast);
  }

  toast.innerText = text;
  toast.style.background = error ? "#e74c3c" : "#2ecc71";
  toast.style.display = "block";

  setTimeout(() => {
    toast.style.display = "none";
  }, 3000);
}

function handleAvatarUpload(file) {
  const reader = new FileReader();

  reader.onload = function (e) {
    user.avatar = e.target.result;
    localStorage.setItem("user", JSON.stringify(user));
    loadUserData();
  };

  reader.readAsDataURL(file);
}

document.addEventListener("DOMContentLoaded", () => {
  loadUserData();

  const notify = JSON.parse(localStorage.getItem("notifications"));

  if (notify) {
    const a = document.getElementById("notifyAppointments");
    const m = document.getElementById("notifyMessages");
    const ac = document.getElementById("notifyActivities");

    if (a) a.checked = notify.appointments;
    if (m) m.checked = notify.messages;
    if (ac) ac.checked = notify.activities;
  }

  

  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) logoutBtn.addEventListener("click", logout);

  const avatarBtn = document.querySelector(".avatar-change-btn");
  const avatarInput = document.getElementById("avatarInput");

  if (avatarBtn && avatarInput) {
    avatarBtn.addEventListener("click", () => avatarInput.click());

    avatarInput.addEventListener("change", (e) => {
      if (e.target.files.length > 0) {
        handleAvatarUpload(e.target.files[0]);
      }
    });
  }
});

if (logoutBtn) {
  logoutBtn.addEventListener("click", function () {
    localStorage.removeItem("token");
    window.location.href = "login.html";
  });
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