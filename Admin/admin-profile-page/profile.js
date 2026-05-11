document.addEventListener("DOMContentLoaded", () => {

  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";
  const avatarEndpointMeta = document.querySelector('meta[name="avatar-upload-endpoint"]');
  const avatarUploadEndpoint = avatarEndpointMeta ? avatarEndpointMeta.getAttribute("content") : "";

  // Sidebar toggle (mobile) 
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

  // Toast notification 
  function showToast(message, type = "success") {
    const toast = document.createElement("div");
    toast.className = `toast ${type}`;
    toast.innerText = message;
    document.body.appendChild(toast);

    setTimeout(() => toast.classList.add("show"), 10);
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  //  Avatar: upload to storage + users.avatar 
  const avatarEditBtn = document.getElementById("avatarEditBtn");
  const adminAvatarInput = document.getElementById("adminAvatarInput");

  if (avatarEditBtn && adminAvatarInput) {
    avatarEditBtn.onclick = (e) => {
      e.stopPropagation();
      adminAvatarInput.click();
    };

    adminAvatarInput.onchange = async () => {
      const file = adminAvatarInput.files && adminAvatarInput.files[0];
      adminAvatarInput.value = "";
      if (!file) return;

      if (!avatarUploadEndpoint) {
        showToast("رابط رفع الصورة غير مهيأ.", "error");
        return;
      }

      const fd = new FormData();
      fd.append("csrf_token", csrfToken);
      fd.append("avatar", file);

      try {
        const res = await fetch(avatarUploadEndpoint, {
          method: "POST",
          body: fd,
          credentials: "same-origin",
        });
        const data = await res.json();

        if (data.success && data.avatar_url) {
          const circle = document.getElementById("profileAvatar");
          const initials = document.getElementById("profileAvatarInitials");
          if (circle) {
            circle.style.backgroundImage = `url('${data.avatar_url}')`;
            circle.style.backgroundSize = "cover";
            circle.style.backgroundPosition = "center";
          }
          if (initials) initials.style.visibility = "hidden";
          showToast(data.message || "تم تحديث الصورة");
        } else {
          showToast(data.message || "تعذر رفع الصورة", "error");
        }
      } catch {
        showToast("حدث خطأ في الاتصال بالخادم.", "error");
      }
    };
  }

  //  Change password  
  const changePasswordBtn = document.getElementById("changePasswordBtn");
  if (changePasswordBtn) {
    changePasswordBtn.onclick = () => {
      showToast("جاري تحويلك لتغيير كلمة المرور");
      setTimeout(() => {
        window.location.href = "../change-password/change-password.php";
      }, 800);
    };
  }

  //  Logout from all devices 
  const logoutAllBtn = document.getElementById("logoutAllBtn");
  if (logoutAllBtn) {
    logoutAllBtn.onclick = () => {
      showToast("تم تسجيل الخروج من جميع الأجهزة");
    };
  }

  // Logout 
  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) {
    logoutBtn.onclick = () => {
      showToast("تم تسجيل الخروج");
      setTimeout(() => {
        window.location.href = "/That-Copy/Auth/login/index.php";
      }, 800);
    };
  }

});
