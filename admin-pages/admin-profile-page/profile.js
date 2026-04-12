document.addEventListener("DOMContentLoaded", () => {

  const menuBtn = document.querySelector(".menu-btn");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.querySelector(".sidebar-overlay");

  if (menuBtn && sidebar && overlay) {
    menuBtn.onclick = () => {
      sidebar.classList.toggle("open");
      overlay.classList.toggle("active");
    };
    overlay.onclick = () => {
      sidebar.classList.remove("open");
      overlay.classList.remove("active");
    };
  }

  function showToast(message, type = "success") {
    let toast = document.createElement("div");
    toast.className = `toast ${type}`;
    toast.innerText = message;
    document.body.appendChild(toast);

    setTimeout(() => toast.classList.add("show"), 10);

    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  async function loadProfile() {
    try {
      const data = {
        fullName: "محمد أحمد الخالدي",
        email: "admin@mentalhealth.sa",
        role: "Super Admin",
        joinDate: "15 يناير 2024",
        initials: "م أ",
        lastPasswordChange: "آخر تغيير كان منذ 30 يوماً",
        twoFaEnabled: true,
        permissions: [
          "إدارة المستخدمين",
          "إدارة الأخصائيين",
          "إدارة الاختبارات",
          "عرض التقارير"
        ],
        loginLogs: [
          {
            date: "2 مارس 2026",
            time: "09:15 ص",
            device: "Chrome — Windows",
            ip: "192.168.1.45",
            location: "الرياض، السعودية"
          },
          {
            date: "1 مارس 2026",
            time: "02:30 م",
            device: "Chrome — Windows",
            ip: "192.168.1.45",
            location: "الرياض، السعودية"
          },
          {
            date: "28 فبراير 2026",
            time: "11:00 ص",
            device: "Safari — macOS",
            ip: "10.0.0.12",
            location: "جدة، السعودية"
          }
        ]
      };

      fillProfile(data);
    } catch {
      showToast("فشل تحميل البيانات", "error");
    }
  }

  function fillProfile(data) {
    document.getElementById("profileFullName").textContent = data.fullName;
    document.getElementById("profileEmail").textContent = data.email;
    document.getElementById("profileRole").textContent = data.role;
    document.getElementById("profileJoinDate").textContent = data.joinDate;
    document.getElementById("lastPasswordChange").textContent = data.lastPasswordChange;

    setAvatarInitials(data.initials);

    const label = document.getElementById("twoFaLabel");
    const status = document.getElementById("twoFaStatus");

    if (data.twoFaEnabled) {
      label.textContent = "مفعّلة";
      status.textContent = "مفعّلة — المصادقة الثنائية نشطة";
    } else {
      label.textContent = "غير مفعّلة";
      status.textContent = "غير مفعّلة";
    }

    renderPermissions(data.permissions);
    renderLoginLogs(data.loginLogs);
  }

  function renderPermissions(list) {
    const grid = document.getElementById("permissionsGrid");
    grid.innerHTML = list.map(p => `
      <div class="perm-item">
        <img src="../images/CheckCircle.svg" alt="Check">
        ${p}
      </div>
    `).join("");
  }

 function renderLoginLogs(logs) {
  const tbody = document.getElementById("loginLogsBody");
  tbody.innerHTML = logs.map(log => `
    <tr>
      <td data-label="التاريخ"><span class="cell-icon"><img src="../images/footerIcon.png"/>${log.date}</span></td>
      <td data-label="الوقت">${log.time}</td>
      <td data-label="الجهاز"><span class="cell-icon"><img src="../images/footerIcon (1).png"/>${log.device}</span></td>
      <td data-label="عنوان IP">${log.ip}</td>
      <td data-label="الموقع"><span class="cell-icon"><img src="../images/footerIcon (2).png"/>${log.location}</span></td>
    </tr>
  `).join("");
}

  function setAvatarInitials(initials) {
    const circle = document.getElementById("profileAvatar");
    const editBtn = document.getElementById("avatarEditBtn");

    circle.style.backgroundImage = "";
    circle.innerHTML = "";
    circle.appendChild(document.createTextNode(initials));
    circle.appendChild(editBtn);
  }

  function setAvatarImage(url) {
    const circle = document.getElementById("profileAvatar");
    const editBtn = document.getElementById("avatarEditBtn");

    circle.style.backgroundImage = `url(${url})`;
    circle.style.backgroundSize = "cover";
    circle.style.backgroundPosition = "center";

    circle.innerHTML = "";
    circle.appendChild(editBtn);
  }

  const avatarEditBtn = document.getElementById("avatarEditBtn");

  if (avatarEditBtn) {
    avatarEditBtn.onclick = (e) => {
      e.stopPropagation();

      const input = document.createElement("input");
      input.type = "file";
      input.accept = "image/*";

      input.onchange = (ev) => {
        const file = ev.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (res) => setAvatarImage(res.target.result);
        reader.readAsDataURL(file);

        showToast("تم تحديث الصورة");
      };

      input.click();
    };
  }

  const changePasswordBtn = document.getElementById("changePasswordBtn");

  if (changePasswordBtn) {
    changePasswordBtn.onclick = () => {
      showToast("جاري تحويلك لتغيير كلمة المرور");
      setTimeout(() => {
        window.location.href = "../change-password/change-password.html";
      }, 800);
    };
  }

  const logoutAllBtn = document.getElementById("logoutAllBtn");

  if (logoutAllBtn) {
    logoutAllBtn.onclick = () => {
      showToast("تم تسجيل الخروج من جميع الأجهزة");
    };
  }

  const logoutBtn = document.getElementById("logoutBtn");

  if (logoutBtn) {
    logoutBtn.onclick = () => {
      showToast("تم تسجيل الخروج");
      setTimeout(() => {
        window.location.href = "../login/login.html";
      }, 800);
    };
  }

  loadProfile();

});