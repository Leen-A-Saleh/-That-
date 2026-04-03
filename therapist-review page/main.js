// عند الضغط على زر العين يتم إظهار اسم المريض في تنبيه بسيط
document.querySelectorAll(".action-view").forEach(function (btn) {
  btn.addEventListener("click", function () {
    const name = this.dataset.name || "مريض";
    alert("عرض تفاصيل المريض: " + name);
  });
});