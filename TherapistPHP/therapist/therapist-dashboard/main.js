// تفعيل الرابط النشط في القائمة الجانبية (شكل فقط في هذه الصفحة)
document.querySelectorAll(".sidebar-nav .nav-link").forEach((link) => {
  link.addEventListener("click", () => {
    document
      .querySelectorAll(".sidebar-nav .nav-link")
      .forEach((i) => i.classList.remove("active"));
    link.classList.add("active");
  });
});

// أزرار قبول / رفض في الطلبات
document.querySelectorAll(".request-item").forEach((item) => {
  const acceptBtn = item.querySelector(".btn-accept");
  const rejectBtn = item.querySelector(".btn-reject");

  if (acceptBtn) {
    acceptBtn.addEventListener("click", () => {
      acceptBtn.textContent = "تم القبول";
      acceptBtn.disabled = true;
      if (rejectBtn) rejectBtn.disabled = true;
    });
  }

  if (rejectBtn) {
    rejectBtn.addEventListener("click", () => {
      rejectBtn.textContent = "تم الرفض";
      rejectBtn.disabled = true;
      if (acceptBtn) acceptBtn.disabled = true;
    });
  }
});