document.addEventListener("DOMContentLoaded", () => {
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";

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

  async function postAction(payload) {
    try {
      const res = await fetch(window.location.href, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ ...payload, csrf_token: csrfToken }),
      });
      return await res.json();
    } catch {
      return { success: false, message: "حدث خطأ في الاتصال بالخادم." };
    }
  }

  async function postFormData(formData) {
    try {
      const res = await fetch(window.location.href, {
        method: "POST",
        body: formData,
        credentials: "same-origin",
      });
      return await res.json();
    } catch {
      return { success: false, message: "حدث خطأ في الاتصال بالخادم." };
    }
  }

  function applyAvatarUrl(url) {
    const circle = document.getElementById("profileAvatar");
    const initials = document.getElementById("profileAvatarInitials");

    if (circle && url) {
      circle.style.backgroundImage = `url('${url}')`;
      circle.style.backgroundSize = "cover";
      circle.style.backgroundPosition = "center";
    }
    if (initials) {
      initials.style.visibility = "hidden";
    }
  }

  function applyProfileInfo(name, email, initials) {
    const displayName = document.getElementById("displayName");
    const displayEmail = document.getElementById("displayEmail");
    const avatarInitials = document.getElementById("profileAvatarInitials");
    const sidebarUserName = document.getElementById("sidebarUserName");

    if (displayName) displayName.textContent = name;
    if (displayEmail) displayEmail.textContent = email;
    if (avatarInitials && initials) avatarInitials.textContent = initials;
    if (sidebarUserName) sidebarUserName.textContent = name;
  }

  const avatarModal = document.getElementById("avatarModal");
  const avatarModalBackdrop = document.getElementById("avatarModalBackdrop");
  const avatarModalClose = document.getElementById("avatarModalClose");
  const profileAvatar = document.getElementById("profileAvatar");
  const avatarEditBtn = document.getElementById("avatarEditBtn");
  const uploadFromDeviceBtn = document.getElementById("uploadFromDeviceBtn");
  const adminAvatarInput = document.getElementById("adminAvatarInput");
  const readyAvatarsGrid = document.getElementById("readyAvatarsGrid");

  function openAvatarModal() {
    if (!avatarModal) return;
    avatarModal.hidden = false;
    document.body.style.overflow = "hidden";
    loadReadyAvatars();
  }

  function closeAvatarModal() {
    if (!avatarModal) return;
    avatarModal.hidden = true;
    document.body.style.overflow = "";
  }

  async function loadReadyAvatars() {
    if (!readyAvatarsGrid) return;

    readyAvatarsGrid.innerHTML = '<p class="ready-avatars-empty">جاري التحميل...</p>';

    try {
      const res = await fetch(window.location.pathname + "?action=ready_avatars", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
        cache: "no-store",
      });
      const data = await res.json();

      if (!data.success || !Array.isArray(data.avatars) || data.avatars.length === 0) {
        readyAvatarsGrid.innerHTML =
          '<p class="ready-avatars-empty">لا توجد صور جاهزة حالياً. يمكنك الرفع من جهازك.</p>';
        return;
      }

      readyAvatarsGrid.innerHTML = "";

      data.avatars.forEach((item) => {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "ready-avatar-item";
        button.title = item.label || "";

        const img = document.createElement("img");
        img.src = item.url;
        img.alt = item.label || "";
        button.appendChild(img);

        button.addEventListener("click", async () => {
          readyAvatarsGrid.querySelectorAll(".ready-avatar-item").forEach((el) => {
            el.classList.remove("is-selected");
          });
          button.classList.add("is-selected");
          button.disabled = true;

          const result = await postAction({
            action: "select_ready_avatar",
            avatar_key: item.key,
          });

          button.disabled = false;

          if (result.success && result.avatar_url) {
            applyAvatarUrl(result.avatar_url);
            closeAvatarModal();
            showToast(result.message || "تم تحديث الصورة");
          } else {
            button.classList.remove("is-selected");
            showToast(result.message || "تعذر اختيار الصورة", "error");
          }
        });

        readyAvatarsGrid.appendChild(button);
      });
    } catch {
      readyAvatarsGrid.innerHTML =
        '<p class="ready-avatars-empty">تعذر تحميل الصور الجاهزة.</p>';
    }
  }

  if (profileAvatar) {
    profileAvatar.addEventListener("click", openAvatarModal);
  }

  if (avatarEditBtn) {
    avatarEditBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      openAvatarModal();
    });
  }

  if (avatarModalClose) {
    avatarModalClose.addEventListener("click", closeAvatarModal);
  }

  if (avatarModalBackdrop) {
    avatarModalBackdrop.addEventListener("click", closeAvatarModal);
  }

  if (uploadFromDeviceBtn && adminAvatarInput) {
    uploadFromDeviceBtn.addEventListener("click", () => adminAvatarInput.click());
  }

  if (adminAvatarInput) {
    adminAvatarInput.addEventListener("change", async () => {
      const file = adminAvatarInput.files && adminAvatarInput.files[0];
      adminAvatarInput.value = "";
      if (!file) return;

      const fd = new FormData();
      fd.append("action", "upload_avatar");
      fd.append("csrf_token", csrfToken);
      fd.append("avatar", file);

      if (uploadFromDeviceBtn) uploadFromDeviceBtn.disabled = true;

      const result = await postFormData(fd);

      if (uploadFromDeviceBtn) uploadFromDeviceBtn.disabled = false;

      if (result.success && result.avatar_url) {
        applyAvatarUrl(result.avatar_url);
        closeAvatarModal();
        showToast(result.message || "تم تحديث الصورة");
      } else {
        showToast(result.message || "تعذر رفع الصورة", "error");
      }
    });
  }

  const editInfoModal = document.getElementById("editInfoModal");
  const editInfoModalBackdrop = document.getElementById("editInfoModalBackdrop");
  const editInfoModalClose = document.getElementById("editInfoModalClose");
  const editInfoBtn = document.getElementById("editInfoBtn");
  const saveProfileBtn = document.getElementById("saveProfileBtn");
  const editNameInput = document.getElementById("editNameInput");
  const editEmailInput = document.getElementById("editEmailInput");

  function openEditInfoModal() {
    if (!editInfoModal) return;

    const displayName = document.getElementById("displayName");
    const displayEmail = document.getElementById("displayEmail");

    if (editNameInput && displayName) {
      editNameInput.value = displayName.textContent.trim();
    }
    if (editEmailInput && displayEmail) {
      editEmailInput.value = displayEmail.textContent.trim();
    }

    editInfoModal.hidden = false;
    document.body.style.overflow = "hidden";
  }

  function closeEditInfoModal() {
    if (!editInfoModal) return;
    editInfoModal.hidden = true;
    document.body.style.overflow = "";
  }

  if (editInfoBtn) {
    editInfoBtn.addEventListener("click", openEditInfoModal);
  }

  if (editInfoModalClose) {
    editInfoModalClose.addEventListener("click", closeEditInfoModal);
  }

  if (editInfoModalBackdrop) {
    editInfoModalBackdrop.addEventListener("click", closeEditInfoModal);
  }

  if (saveProfileBtn && editNameInput && editEmailInput) {
    saveProfileBtn.addEventListener("click", async () => {
      const name = editNameInput.value.trim();
      const email = editEmailInput.value.trim();

      if (!name || !email) {
        showToast("الاسم والبريد الإلكتروني مطلوبان.", "error");
        return;
      }

      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(email)) {
        showToast("صيغة البريد الإلكتروني غير صحيحة.", "error");
        return;
      }

      saveProfileBtn.disabled = true;
      saveProfileBtn.textContent = "جاري الحفظ...";

      const result = await postAction({
        action: "update_profile",
        name,
        email,
      });

      saveProfileBtn.disabled = false;
      saveProfileBtn.textContent = "حفظ التغييرات";

      if (result.success) {
        applyProfileInfo(name, email, result.initials || "");
        closeEditInfoModal();
        showToast(result.message || "تم تحديث المعلومات");
      } else {
        showToast(result.message || "تعذر حفظ المعلومات", "error");
      }
    });
  }

  const changePasswordBtn = document.getElementById("changePasswordBtn");
  if (changePasswordBtn) {
    changePasswordBtn.onclick = () => {
      showToast("جاري تحويلك لتغيير كلمة المرور");
      setTimeout(() => {
        window.location.href = "../change-password/change-password.php";
      }, 800);
    };
  }

  const logoutAllBtn = document.getElementById("logoutAllBtn");
  if (logoutAllBtn) {
    logoutAllBtn.addEventListener("click", async () => {
      if (
        !(await showConfirm(
          "هل أنت متأكد من تسجيل الخروج من جميع الأجهزة؟ سيتم إنهاء جميع الجلسات النشطة بما فيها هذا الجهاز.",
          { icon: "warning" }
        ))
      ) {
        return;
      }

      logoutAllBtn.disabled = true;
      const originalText = logoutAllBtn.innerHTML;
      logoutAllBtn.innerHTML =
        '<i class="fa-solid fa-spinner fa-spin"></i> جاري تسجيل الخروج...';

      const result = await postAction({ action: "logout_all_devices" });

      if (result.success && result.redirect) {
        showToast(result.message || "تم تسجيل الخروج من جميع الأجهزة");
        setTimeout(() => {
          window.location.href = result.redirect;
        }, 800);
        return;
      }

      logoutAllBtn.disabled = false;
      logoutAllBtn.innerHTML = originalText;
      showToast(result.message || "تعذر تسجيل الخروج من جميع الأجهزة", "error");
    });
  }
});
