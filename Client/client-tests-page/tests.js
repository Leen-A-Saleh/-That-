const menuBtn = document.getElementById("menuBtn");
const sidebar = document.querySelector(".sidebar");

let overlay = document.querySelector(".sidebar-overlay");
if (!overlay) {
  overlay = document.createElement("div");
  overlay.className = "sidebar-overlay";
  document.body.appendChild(overlay);
}

menuBtn.addEventListener("click", () => {
  sidebar.classList.toggle("open");
  overlay.classList.toggle("open");
});

overlay.addEventListener("click", () => {
  sidebar.classList.remove("open");
  overlay.classList.remove("open");
});

const recommendedBtn = document.getElementById("recommendedTestBtn");

if (recommendedBtn) {
  recommendedBtn.addEventListener("click", () => {
    const href = recommendedBtn.getAttribute("data-href");
    if (href) {
      window.location.href = href;
    } else {
      showInfoToast("لا يوجد اختبار موصى به حالياً");
    }
  });
}