document.addEventListener("DOMContentLoaded", () => {
  // ── CSRF token (from <meta name="csrf-token">) ──────────────
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";
  const avatarEndpointMeta = document.querySelector('meta[name="avatar-upload-endpoint"]');
  const avatarUploadEndpoint = avatarEndpointMeta ? avatarEndpointMeta.getAttribute("content") : "";

  // ── Helper: send JSON POST to current page ──────────────────
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

  // ── Toast notification ──────────────────────────────────────
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

  // ── Edit profile: show form ─────────────────────────────────
  const editBtn = document.getElementById("editBtn");
  const editForm = document.getElementById("editForm");

  if (editBtn && editForm) {
    editBtn.addEventListener("click", () => {
      editForm.style.display = editForm.style.display === "none" ? "block" : "none";
    });
  }

  // ── Save profile: POST update_profile ───────────────────────
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
        // Update display values in the DOM
        const displayName = document.getElementById("displayName");
        const displayEmail = document.getElementById("displayEmail");
        const displayPhone = document.getElementById("displayPhone");
        const displayBirth = document.getElementById("displayBirth");
        const heroName = document.getElementById("heroName");
        const heroInitial = document.getElementById("heroInitial");

        if (displayName) displayName.textContent = name;
        if (displayEmail) displayEmail.textContent = email;
        if (displayPhone) displayPhone.textContent = phone || "غير متاح";
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

  // ── Change password: POST change_password ───────────────────
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

  // ── Delete account: POST delete_account ─────────────────────
  const deleteAccountBtn = document.getElementById("deleteAccountBtn");

  if (deleteAccountBtn) {
    deleteAccountBtn.addEventListener("click", async () => {
      if (!confirm("هل أنت متأكد من حذف الحساب؟ هذا الإجراء لا يمكن التراجع عنه.")) {
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

  // ── Avatar upload → storage + users.avatar ──────────────────
  const avatarBtn = document.querySelector(".avatar-change-btn");
  const avatarInput = document.getElementById("avatarInput");

  if (avatarBtn && avatarInput) {
    avatarBtn.addEventListener("click", () => avatarInput.click());

    avatarInput.addEventListener("change", async (e) => {
      const file = e.target.files && e.target.files[0];
      avatarInput.value = "";
      if (!file) return;

      if (!avatarUploadEndpoint) {
        showToast("رابط رفع الصورة غير مهيأ.", true);
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
        const result = await res.json();

        if (result.success && result.avatar_url) {
          const img = document.getElementById("avatarImage");
          const hi = document.getElementById("heroInitial");
          if (img) {
            img.src = result.avatar_url;
            img.style.display = "block";
          }
          if (hi) hi.style.visibility = "hidden";
          showToast(result.message || "تم تحديث الصورة");
        } else {
          showToast(result.message || "تعذر رفع الصورة", true);
        }
      } catch {
        showToast("حدث خطأ في الاتصال بالخادم.", true);
      }
    });
  }

  // ── Notification settings (local only — ignore per spec) ────
  window.saveNotifications = function () {
    showToast("تم حفظ الإعدادات");
  };

  // ── Sidebar toggle ─────────────────────────────────────────
  const menuBtn = document.getElementById("menuBtn");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.querySelector(".sidebar-overlay");

  if (menuBtn && sidebar) {
    menuBtn.addEventListener("click", () => {
      sidebar.classList.toggle("open");
      if (overlay) overlay.classList.toggle("open");
    });

    if (overlay) {
      overlay.addEventListener("click", () => {
        sidebar.classList.remove("open");
        overlay.classList.remove("open");
      });
    }
  }
});