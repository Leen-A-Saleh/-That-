document.addEventListener("DOMContentLoaded", () => {

  const fileInput  = document.getElementById("avatarInput");
  const imgEl      = document.getElementById("avatarImage");
  const initialEl  = document.querySelector(".avatar-initial");
  const changeBtn  = document.querySelector(".avatar-change-btn");

  if (changeBtn && fileInput) {
    changeBtn.addEventListener("click", () => fileInput.click());

    fileInput.addEventListener("change", () => {
      const file = fileInput.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = e => {
        imgEl.src = e.target.result;
        imgEl.style.display = "block";
        if (initialEl) initialEl.style.display = "none";
      };
      reader.readAsDataURL(file);

      const fd = new FormData();
      fd.append("action", "upload_avatar");
      fd.append("avatar", file);
      fetch("index.php", {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: fd
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          showToast("تم تحديث الصورة بنجاح");
        } else {
          showToast("فشل رفع الصورة");
        }
      })
      .catch(() => showToast("حدث خطأ أثناء رفع الصورة"));
    });
  }

  const editProfileBtn   = document.getElementById("editProfileBtn");
  const editProfileModal = document.getElementById("editProfileModal");
  const editProfileForm  = document.getElementById("editProfileForm");

  if (editProfileBtn && editProfileModal) {
    editProfileBtn.addEventListener("click", () => {
      editProfileModal.classList.add("show");
    });

    editProfileModal.querySelectorAll("[data-close]").forEach(btn => {
      btn.addEventListener("click", () => editProfileModal.classList.remove("show"));
    });

    editProfileModal.addEventListener("click", e => {
      if (e.target === editProfileModal) editProfileModal.classList.remove("show");
    });
  }

  if (editProfileForm) {
    editProfileForm.addEventListener("submit", e => {
      e.preventDefault();
      const fd = new FormData(editProfileForm);
      fetch("index.php", {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: fd
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          showToast("تم تحديث الملف الشخصي");
          setTimeout(() => location.reload(), 600);
        }
      })
      .catch(() => showToast("حدث خطأ"));
    });
  }

  const passwordForm = document.getElementById("passwordForm");
  const passwordMsg  = document.getElementById("passwordMsg");

  if (passwordForm) {
    passwordForm.addEventListener("submit", e => {
      e.preventDefault();
      const fd = new FormData(passwordForm);
      fetch("index.php", {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: fd
      })
      .then(r => r.json())
      .then(data => {
        passwordMsg.textContent = data.message;
        passwordMsg.className = "form-msg " + (data.success ? "success" : "error");
        if (data.success) passwordForm.reset();
      })
      .catch(() => {
        passwordMsg.textContent = "حدث خطأ";
        passwordMsg.className = "form-msg error";
      });
    });
  }

  const editScheduleBtn   = document.getElementById("editScheduleBtn");
  const scheduleContainer = document.getElementById("scheduleContainer");
  let editing = false;

  if (editScheduleBtn && scheduleContainer) {
    editScheduleBtn.addEventListener("click", () => {
      editing = !editing;

      if (editing) {
        enterScheduleEdit();
        editScheduleBtn.innerHTML = '<i class="fa-regular fa-floppy-disk"></i> حفظ التعديلات';
      } else {
        saveSchedule();
      }
    });
  }

  function enterScheduleEdit() {
    scheduleContainer.querySelectorAll(".schedule-day").forEach(dayEl => {
      const day = dayEl.dataset.day;
      const slotsDiv = dayEl.querySelector(".day-slots");

      const noSlots = slotsDiv.querySelector(".no-slots");
      if (noSlots) noSlots.remove();

      slotsDiv.querySelectorAll(".slot-pill").forEach(pill => {
        if (!pill.querySelector(".remove-slot")) {
          const removeBtn = document.createElement("button");
          removeBtn.type = "button";
          removeBtn.className = "remove-slot";
          removeBtn.innerHTML = "&times;";
          removeBtn.addEventListener("click", () => pill.remove());
          pill.appendChild(removeBtn);
        }
      });

      if (!slotsDiv.querySelector(".add-slot-btn")) {
        const addBtn = document.createElement("button");
        addBtn.type = "button";
        addBtn.className = "add-slot-btn";
        addBtn.innerHTML = '<i class="fa-solid fa-plus"></i>';
        addBtn.addEventListener("click", () => addSlotInput(slotsDiv, day));
        slotsDiv.appendChild(addBtn);
      }
    });
  }

  function addSlotInput(slotsDiv, day) {
    const wrapper = document.createElement("div");
    wrapper.className = "slot-input-row";
    wrapper.innerHTML = `
      <input type="time" class="slot-start" value="09:00">
      <span>-</span>
      <input type="time" class="slot-end" value="10:00">
      <button type="button" class="slot-confirm"><i class="fa-solid fa-check"></i></button>
      <button type="button" class="slot-cancel"><i class="fa-solid fa-xmark"></i></button>
    `;

    wrapper.querySelector(".slot-confirm").addEventListener("click", () => {
      const start = wrapper.querySelector(".slot-start").value;
      const end   = wrapper.querySelector(".slot-end").value;
      if (start && end && start < end) {
        const pill = document.createElement("span");
        pill.className = "slot-pill";
        pill.dataset.start = start;
        pill.dataset.end = end;
        pill.innerHTML = `<i class="fa-regular fa-clock"></i> ${start} - ${end}
          <button type="button" class="remove-slot">&times;</button>`;
        pill.querySelector(".remove-slot").addEventListener("click", () => pill.remove());
        slotsDiv.insertBefore(pill, slotsDiv.querySelector(".add-slot-btn"));
        wrapper.remove();
      } else {
        showToast("تأكد من صحة الوقت");
      }
    });

    wrapper.querySelector(".slot-cancel").addEventListener("click", () => wrapper.remove());

    const addBtn = slotsDiv.querySelector(".add-slot-btn");
    slotsDiv.insertBefore(wrapper, addBtn);
  }

  function saveSchedule() {
    const slots = [];
    scheduleContainer.querySelectorAll(".schedule-day").forEach(dayEl => {
      const day = dayEl.dataset.day;
      dayEl.querySelectorAll(".slot-pill").forEach(pill => {
        slots.push({
          day:   day,
          start: pill.dataset.start,
          end:   pill.dataset.end
        });
      });

      dayEl.querySelectorAll(".remove-slot, .add-slot-btn, .slot-input-row").forEach(el => el.remove());

      const slotsDiv = dayEl.querySelector(".day-slots");
      if (!slotsDiv.querySelector(".slot-pill")) {
        const noSlots = document.createElement("span");
        noSlots.className = "no-slots";
        noSlots.textContent = "لا توجد مواعيد متاحة";
        slotsDiv.appendChild(noSlots);
      }
    });

    const fd = new FormData();
    fd.append("action", "save_schedule");
    fd.append("slots", JSON.stringify(slots));

    fetch("index.php", {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      body: fd
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast("تم حفظ الجدول بنجاح");
        editScheduleBtn.innerHTML = '<i class="fa-regular fa-pen-to-square"></i> تعديل الجدول';
      }
    })
    .catch(() => showToast("حدث خطأ أثناء الحفظ"));
  }

  document.querySelectorAll(".rating-fill").forEach(bar => {
    const finalWidth = bar.style.width;
    bar.style.width = "0";
    setTimeout(() => { bar.style.width = finalWidth; }, 50);
  });

  document.addEventListener("keydown", e => {
    if (e.key === "Escape") {
      document.querySelectorAll(".modal-overlay.show").forEach(m => m.classList.remove("show"));
    }
  });

  function showToast(message) {
    const toast = document.createElement("div");
    toast.style.cssText = `
      position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
      background-color: #333; color: white; padding: 12px 24px;
      border-radius: 8px; font-family: 'Cairo', sans-serif; font-size: 14px;
      z-index: 10000; box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
      toast.style.transition = "0.3s";
      toast.style.opacity = "0";
      setTimeout(() => toast.remove(), 300);
    }, 2000);
  }

});
