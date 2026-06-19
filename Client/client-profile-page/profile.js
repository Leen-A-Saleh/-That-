document.addEventListener("DOMContentLoaded", () => {
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";

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

  function showToast(text, isError = false) {
    let toast = document.getElementById("toast");

    if (!toast) {
      toast = document.createElement("div");
      toast.id = "toast";
      Object.assign(toast.style, {
        position: "fixed",
        top: "20px",
        left: "50%",
        transform: "translateX(-50%)",
        padding: "12px 24px",
        borderRadius: "8px",
        color: "white",
        fontWeight: "bold",
        zIndex: "9999",
        transition: "opacity 0.3s ease",
        fontFamily: "Cairo, sans-serif",
      });
      document.body.appendChild(toast);
    }

    toast.innerText = text;
    toast.style.background = isError ? "#e74c3c" : "#2ecc71";
    toast.style.opacity = "1";
    toast.style.display = "block";

    setTimeout(() => {
      toast.style.opacity = "0";
      setTimeout(() => (toast.style.display = "none"), 300);
    }, 3000);
  }

  function applyAvatarUrl(url) {
    const img = document.getElementById("avatarImage");
    const initial = document.getElementById("heroInitial");

    if (img && url) {
      img.src = url;
      img.style.display = "block";
    }
    if (initial) {
      initial.style.visibility = "hidden";
    }
  }

  const editBtn = document.getElementById("editBtn");
  const editForm = document.getElementById("editForm");

  if (editBtn && editForm) {
    editBtn.addEventListener("click", () => {
      editForm.style.display = editForm.style.display === "none" ? "block" : "none";
    });
  }

  const saveProfileBtn = document.getElementById("saveProfileBtn");

  if (saveProfileBtn) {
    saveProfileBtn.addEventListener("click", async () => {
      const name = document.getElementById("nameInput").value.trim();
      const email = document.getElementById("emailInput").value.trim();
      const phone = document.getElementById("phoneInput").value.trim();
      const birthdate = document.getElementById("birthInput").value.trim();

      if (!name || !email) {
        showToast("الاسم والبريد الإلكتروني مطلوبان.", true);
        return;
      }

      saveProfileBtn.disabled = true;
      saveProfileBtn.textContent = "جاري الحفظ...";

      const result = await postAction({
        action: "update_profile",
        name,
        email,
        phone,
        birthdate,
      });

      saveProfileBtn.disabled = false;
      saveProfileBtn.textContent = "حفظ";

      if (result.success) {
        const displayName = document.getElementById("displayName");
        const displayEmail = document.getElementById("displayEmail");
        const displayPhone = document.getElementById("displayPhone");
        const displayBirth = document.getElementById("displayBirth");
        const heroName = document.getElementById("heroName");
        const heroInitial = document.getElementById("heroInitial");

        if (displayName) displayName.textContent = name;
        if (displayEmail) displayEmail.textContent = email;
        if (displayPhone) {
          displayPhone.textContent = phone || "غير متاح";
          displayPhone.dir = phone ? "ltr" : "rtl";
        }
        if (displayBirth) displayBirth.textContent = birthdate || "غير متاح حالياً";
        if (heroName) heroName.textContent = name;
        if (heroInitial) heroInitial.textContent = name.charAt(0) || "";

        if (editForm) editForm.style.display = "none";
        showToast(result.message);
      } else {
        showToast(result.message, true);
      }
    });
  }

  const changePasswordBtn = document.getElementById("changePasswordBtn");

  if (changePasswordBtn) {
    changePasswordBtn.addEventListener("click", async () => {
      const oldPassEl = document.getElementById("oldPassword");
      const newPassEl = document.getElementById("newPassword");
      const confirmPassEl = document.getElementById("confirmPassword");
      const currentPassword = oldPassEl.value;
      const newPassword = newPassEl.value;
      const confirmPassword = confirmPassEl.value;

      if (!currentPassword || !newPassword || !confirmPassword) {
        showToast("يرجى تعبئة جميع حقول كلمة المرور.", true);
        return;
      }

      if (newPassword !== confirmPassword) {
        showToast("كلمة المرور الجديدة وتأكيدها غير متطابقتين.", true);
        return;
      }

      changePasswordBtn.disabled = true;
      changePasswordBtn.textContent = "جاري التغيير...";

      const result = await postAction({
        action: "change_password",
        current_password: currentPassword,
        new_password: newPassword,
      });

      changePasswordBtn.disabled = false;
      changePasswordBtn.textContent = "حفظ التغيير";

      if (result.success) {
        oldPassEl.value = "";
        newPassEl.value = "";
        confirmPassEl.value = "";
        showToast(result.message);
      } else {
        showToast(result.message, true);
      }
    });
  }

  const deleteAccountBtn = document.getElementById("deleteAccountBtn");

  if (deleteAccountBtn) {
    deleteAccountBtn.addEventListener("click", async () => {
      if (!(await showConfirm("هل أنت متأكد من حذف الحساب؟ هذا الإجراء لا يمكن التراجع عنه.", { icon: "warning", confirmText: "نعم، احذف" }))) {
        return;
      }

      deleteAccountBtn.disabled = true;
      deleteAccountBtn.textContent = "جاري الحذف...";

      const result = await postAction({ action: "delete_account" });

      if (result.success && result.redirect) {
        showToast(result.message);
        setTimeout(() => {
          window.location.href = result.redirect;
        }, 800);
      } else {
        deleteAccountBtn.disabled = false;
        deleteAccountBtn.textContent = "حذف الحساب نهائياً";
        showToast(result.message, true);
      }
    });
  }

  const avatarModal = document.getElementById("avatarModal");
  const avatarModalBackdrop = document.getElementById("avatarModalBackdrop");
  const avatarModalClose = document.getElementById("avatarModalClose");
  const avatarChangeBtn = document.getElementById("avatarChangeBtn");
  const uploadFromDeviceBtn = document.getElementById("uploadFromDeviceBtn");
  const avatarInput = document.getElementById("avatarInput");
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

      data.avatars.forEach(function (item) {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "ready-avatar-item";
        button.title = item.label || "";
        const img = document.createElement("img");
        img.src = item.url;
        img.alt = item.label || "";
        button.appendChild(img);

        button.addEventListener("click", async function () {
          readyAvatarsGrid.querySelectorAll(".ready-avatar-item").forEach(function (el) {
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
            showToast(result.message || "تعذر اختيار الصورة", true);
          }
        });

        readyAvatarsGrid.appendChild(button);
      });
    } catch {
      readyAvatarsGrid.innerHTML =
        '<p class="ready-avatars-empty">تعذر تحميل الصور الجاهزة.</p>';
    }
  }

  if (avatarChangeBtn) {
    avatarChangeBtn.addEventListener("click", openAvatarModal);
  }

  if (avatarModalClose) {
    avatarModalClose.addEventListener("click", closeAvatarModal);
  }

  if (avatarModalBackdrop) {
    avatarModalBackdrop.addEventListener("click", closeAvatarModal);
  }

  if (uploadFromDeviceBtn && avatarInput) {
    uploadFromDeviceBtn.addEventListener("click", () => avatarInput.click());
  }

  if (avatarInput) {
    avatarInput.addEventListener("change", async function (e) {
      const file = e.target.files && e.target.files[0];
      avatarInput.value = "";
      if (!file) return;

      const fd = new FormData();
      fd.append("action", "upload_avatar");
      fd.append("csrf_token", csrfToken);
      fd.append("avatar", file);

      uploadFromDeviceBtn.disabled = true;

      const result = await postFormData(fd);

      uploadFromDeviceBtn.disabled = false;

      if (result.success && result.avatar_url) {
        applyAvatarUrl(result.avatar_url);
        closeAvatarModal();
        showToast(result.message || "تم تحديث الصورة");
      } else {
        showToast(result.message || "تعذر رفع الصورة", true);
      }
    });
  }

  const saveNotificationsBtn = document.getElementById("saveNotificationsBtn");

  if (saveNotificationsBtn) {
    saveNotificationsBtn.addEventListener("click", async () => {
      const notifyAppointments = document.getElementById("notifyAppointments");
      const notifyMessages = document.getElementById("notifyMessages");
      const notifyActivities = document.getElementById("notifyActivities");

      saveNotificationsBtn.disabled = true;
      saveNotificationsBtn.textContent = "جاري الحفظ...";

      const result = await postAction({
        action: "save_notifications",
        appointment_notifications: Boolean(notifyAppointments && notifyAppointments.checked),
        message_notifications: Boolean(notifyMessages && notifyMessages.checked),
        activity_reminder_notifications: Boolean(notifyActivities && notifyActivities.checked),
      });

      saveNotificationsBtn.disabled = false;
      saveNotificationsBtn.textContent = "حفظ الإعدادات";

      showToast(result.message || "تم حفظ الإعدادات", !result.success);
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

  if (menuBtn && sidebar) {
    menuBtn.addEventListener("click", () => {
      sidebar.classList.toggle("open");
      overlay.classList.toggle("open");
    });

    overlay.addEventListener("click", () => {
      sidebar.classList.remove("open");
      overlay.classList.remove("open");
    });
  }
});
