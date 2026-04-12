const container = document.getElementById("doctorsContainer");
const count = document.getElementById("doctorCount");
const searchInput = document.getElementById("searchInput");

const mockDoctors = [
  {
    id: 1,
    name: "زينب كرمي",
    image: "../images/zaineb.png",
    special: "ماجستير إرشاد نفسي وتوجيه",
    degree: "مرخصة لعلاج الصدمات والعلاج السلوكي المعرفي",
    experience:
      "خبرة أكثر من 8 سنوات في جلسات إرشادية للأطفال والبالغين والإرشاد الوالدي",
    work: "من السبت إلى الخميس من 9 إلى 3:30",
    consultPrice: "150 شيكل",
    therapyPrice: "120 شيكل",
    email: "Zainab.karmi@hotmail.com",
  },

  {
    id: 2,
    name: "تسنيم زيدان",
    image: "../images/tasnem.png",
    special: "ماجستير الصحة النفسية العلاجية",
    degree: "مرخصة لتقديم العلاج السلوكي المعرفي وعلاج الصدمات",
    experience: "خبرة لأكثر من 6 سنوات في التعامل مع المراهقين والبالغين",
    work: "الأحد والخميس من 8:30 إلى 3",
    consultPrice: "200 شيكل",
    therapyPrice: "150 شيكل",
    email: "tasneemtherapist@gmail.com",
  },

  {
    id: 3,
    name: "هديل ابو رميلة",
    image: "../images/hadeel.png",
    special: "ماجستير إرشاد نفسي وتوجيه",
    degree: "إرشاد فردي وزواجي",
    experience: "خبرة أكثر من 4 سنوات في مجال الإرشاد",
    work: "جلسات Online حسب الطلب",
    consultPrice: "170 شيكل",
    therapyPrice: "130 شيكل",
    email: "hadeel.basman98@gmail.com",
  },
  {
    id: 4,
    name: "مها الرفاعي",
    image: "../images/maha.jpeg",
    special: "مختصة نفسية اجتماعية",
    degree: "بكالوريوس علم نفس فرعي علم اجتماع",
    experience:
      "خبرة أكثر من 6 سنوات في الإرشاد الفردي والأسري وإدارة المجموعات",
    work: "من الاثنين إلى الخميس بعد الساعة 3",
    consultPrice: "150 شيكل",
    therapyPrice: "120 شيكل",
    email: "mahaalrefaie96@gmail.com",
  },

  {
    id: 5,
    name: "نيروز نجم الدين",
    image: "../images/neroz.png",
    special: "بكالوريوس السمع والنطق",
    degree: "تطوير اللغة وتصحيح النطق وعلاج التأتأة",
    experience: "خبرة أكثر من 6 سنوات في علاج اضطرابات النطق عند الأطفال",
    work: "السبت والأحد والثلاثاء والأربعاء من 9 إلى 4",
    consultPrice: "150 شيكل",
    therapyPrice: "80 شيكل",
    email: "n.nijem.aldeen98@gmail.com",
  },
  {
    id: 6,
    name: "عمر قدح",
    image: "../images/omar.png",
    special: "ماجستير الصحة النفسية العلاجية",
    degree: "مرخص لإدارة اختبارات نفسية واختبارات الذكاء",
    experience:
      "خبرة لأكثر من 10 سنوات في التعامل مع مختلف الأعمار والاضطرابات النفسية",
    work: "من الأحد إلى الأربعاء - حسب الطلب",
    consultPrice: "200 شيكل",
    therapyPrice: "150 شيكل",
    email: "omaraaq1989@gmail.com",
  },
];

localStorage.setItem("mockDoctors", JSON.stringify(mockDoctors));

let doctors = [];

function renderDoctors(list) {
  if (!container) return;

  container.innerHTML = "";

  if (count) {
    count.innerText = list.length;
  }

  list.forEach((doc) => {
    container.innerHTML += `

<div class="doctor-card">

<img src="${doc.image}" onerror="this.src='../images/default-doctor.png'">

<div class="doctor-info">

<div class="doctor-name">${doc.name}</div>

<div class="doctor-special">${doc.special}</div>

<div class="doctor-degree">${doc.degree}</div>

<div class="doctor-details">

<i class="fa fa-briefcase"></i> ${doc.experience}<br>

<i class="fa fa-clock"></i> ${doc.work}<br>

<i class="fa fa-sack-dollar"></i> جلسة استشارية: ${doc.consultPrice}<br>

<i class="fa fa-coins"></i> جلسة علاجية: ${doc.therapyPrice}<br>

<i class="fa fa-envelope"></i>

<a href="mailto:${doc.email}">
${doc.email}
</a>

</div>

<button class="book-btn" data-id="${doc.id}">
حجز موعد
</button>

</div>

</div>

`;
  });
}

async function loadDoctors() {
  try {
    const res = await fetch("/api/doctors");

    if (!res.ok) throw new Error();

    doctors = await res.json();
  } catch {
    doctors = mockDoctors;
  }

  renderDoctors(doctors);
}

loadDoctors();

if (searchInput) {
  searchInput.addEventListener("input", function () {
    let value = this.value.toLowerCase();

    let filtered = doctors.filter(
      (doc) =>
        doc.name.toLowerCase().includes(value) ||
        doc.special.toLowerCase().includes(value) ||
        doc.degree.toLowerCase().includes(value),
    );

    renderDoctors(filtered);
  });
}

document.addEventListener("click", function (e) {
  if (e.target.classList.contains("book-btn")) {
    let doctorId = e.target.dataset.id;

    localStorage.setItem("doctorId", doctorId);

    window.location.href = "../client-booking-page/booking.html";
  }
});

let user = localStorage.getItem("username");

const usernameEl = document.getElementById("username");

if (user && usernameEl) {
  usernameEl.innerText = user;
}

const logoutBtn = document.getElementById("logoutBtn");

if (logoutBtn) {
  logoutBtn.addEventListener("click", function () {
    localStorage.removeItem("token");

    window.location.href = "login.html";
  });
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

menuBtn.addEventListener("click", () => {
  sidebar.classList.toggle("open");
  overlay.classList.toggle("open");
});

overlay.addEventListener("click", () => {
  sidebar.classList.remove("open");
  overlay.classList.remove("open");
});
