// حذف الإشعار
document.querySelectorAll(".icon-btn.delete").forEach(function (btn) {
  btn.addEventListener("click", function () {
    const row = btn.closest(".notification");
    row.style.height = row.offsetHeight + "px";
    row.style.transition =
      "opacity 0.2s ease, height 0.25s ease, margin 0.25s ease";
    row.style.opacity = "0";
    row.style.margin = "0";
    row.style.height = "0";
    setTimeout(() => row.remove(), 260);
  });
});

// تعليم الإشعار كمقروء / غير مقروء
document.querySelectorAll(".icon-btn.done").forEach(function (btn) {
  btn.addEventListener("click", function () {
    const row = btn.closest(".notification");
    row.classList.toggle("read");
  });
});