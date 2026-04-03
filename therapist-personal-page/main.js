// تفعيل الرابط النشط في القائمة الجانبية (للشكل فقط)
document.querySelectorAll(".sidebar-nav .nav-link").forEach((link) => {
  link.addEventListener("click", (e) => {
    // لو كنت لا تريد الانتقال لصفحة أخرى أثناء التجربة، ألغِ التعليق عن السطر التالي:
    // e.preventDefault();

    document
      .querySelectorAll(".sidebar-nav .nav-link")
      .forEach((i) => i.classList.remove("active"));
    link.classList.add("active");
  });
});
document.addEventListener("DOMContentLoaded", () => {
  const fileInput  = document.getElementById("avatarInput");
  const imgEl      = document.getElementById("avatarImage");
  const initialEl  = document.querySelector(".avatar-initial");
  const changeBtn  = document.querySelector(".avatar-change-btn");

  if (!fileInput || !imgEl || !initialEl || !changeBtn) return;

  // عند الضغط على زر الكاميرا نفتح اختيار الملف
  changeBtn.addEventListener("click", () => {
    fileInput.click();
  });

  // عند اختيار صورة
  fileInput.addEventListener("change", () => {
    const file = fileInput.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = e => {
      imgEl.src = e.target.result; // نحط الصورة في <img>
      imgEl.style.display = "block"; // نظهر الصورة
      initialEl.style.display = "none"; // نُخفي الحرف
    };
    reader.readAsDataURL(file);
  });
});



const editBtn = document.getElementById("editScheduleBtn");
const scheduleCard = document.querySelector(".schedule-card");

if (editBtn && scheduleCard) {
  let editing = false;

  editBtn.addEventListener("click", () => {
    editing = !editing;
    scheduleCard.classList.toggle("editing", editing);
    editBtn.innerHTML = editing
      ? '<i class="fa-regular fa-floppy-disk"></i> حفظ التعديلات'
      : '<i class="fa-regular fa-pen-to-square"></i> تعديل الجدول';
  });
}
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".rating-fill").forEach((bar) => {
    const finalWidth = bar.style.width;   // مثلاً "75%"
    bar.style.width = "0";
    setTimeout(() => {
      bar.style.width = finalWidth;
    }, 50);
  });
});