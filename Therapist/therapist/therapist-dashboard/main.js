// تفعيل الرابط النشط في القائمة الجانبية (شكل فقط في هذه الصفحة)
document.querySelectorAll(".sidebar-nav .nav-link").forEach((link) => {
  link.addEventListener("click", () => {
    document
      .querySelectorAll(".sidebar-nav .nav-link")
      .forEach((i) => i.classList.remove("active"));
    link.classList.add("active");
  });
});

// Accept/reject handled by ../js/appointment-request-actions.js