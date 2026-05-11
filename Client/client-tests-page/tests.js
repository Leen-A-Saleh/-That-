const menuBtn = document.getElementById("menuBtn");
const sidebar = document.querySelector(".sidebar");
const logoutBtn = document.getElementById("logoutBtn");

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

if (logoutBtn) {
  logoutBtn.addEventListener("click", function () {
    localStorage.removeItem("token");
    window.location.href = "login.php";
  });
}

const recommendedBtn = document.getElementById("recommendedTestBtn");
let recommendedTest = "beck.php";

if (recommendedBtn) {
  recommendedBtn.addEventListener("click", () => {
    if (recommendedTest) {
      window.location.href = recommendedTest;
    } else {
      alert("لا يوجد اختبار موصى به حالياً");
    }
  });
}

fetch("http://localhost:3000/api/recommended-test", {
  headers: {
    Authorization: "Bearer " + localStorage.getItem("token"),
  },
})
  .then((res) => res.json())
  .then((data) => {
    if (data.recommendedTest) {
      recommendedTest = data.recommendedTest + ".php";
    }
  })
  .catch((err) => {
    console.error(err);
  });

const totalCasesEl = document.getElementById("totalCases");
const activeCasesEl = document.getElementById("activeCases");
const todayAppointmentsEl = document.getElementById("todayAppointments");

const statsData = {
  totalCompleted: 4,
  totalTests: 6,
  completionRate: "33%",
};

if (totalCasesEl) totalCasesEl.textContent = statsData.totalCompleted;
if (activeCasesEl) activeCasesEl.textContent = statsData.totalTests;
if (todayAppointmentsEl)
  todayAppointmentsEl.textContent = statsData.completionRate;