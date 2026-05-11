let user = localStorage.getItem("username");

const usernameEl = document.getElementById("username");

if (user && usernameEl) {
  usernameEl.innerText = user;
}

document.addEventListener("DOMContentLoaded", function () {
  const menuBtn = document.getElementById("menuBtn");
  const sidebar = document.querySelector(".sidebar");

  if (menuBtn && sidebar) {
    menuBtn.addEventListener("click", function () {
      sidebar.classList.toggle("active");
    });
  }
});

const dot = document.getElementById("notificationDot");

let hasNotifications = localStorage.getItem("hasNotifications");

if (hasNotifications === "true") {
  dot.style.display = "block";
} else {
  dot.style.display = "none";
}
localStorage.setItem("hasNotifications", "true");

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
}

overlay.addEventListener("click", () => {
  if (!sidebar) return;
  sidebar.classList.remove("open");
  overlay.classList.remove("open");
});
