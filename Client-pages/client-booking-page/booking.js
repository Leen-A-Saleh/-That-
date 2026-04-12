const menuBtn = document.getElementById("menuBtn");
const sidebar = document.querySelector(".sidebar");

menuBtn?.addEventListener("click", () => {
  sidebar.classList.toggle("active");
});

let user = localStorage.getItem("username");

if (user) {
  document.getElementById("username").innerText = user;
}

document.getElementById("logoutBtn").addEventListener("click", function () {
  localStorage.removeItem("token");
  window.location.href = "login.html";
});

const doctorCard = document.getElementById("doctorCard");
const doctorImg = document.getElementById("doctorImg");
const doctorName = document.getElementById("doctorName");
const doctorSpecial = document.getElementById("doctorSpecial");
const doctorDegree = document.getElementById("doctorDegree");
const doctorExperience = document.getElementById("doctorExperience");
const doctorWork = document.getElementById("doctorWork");
const doctorConsult = document.getElementById("doctorConsult");
const doctorTherapy = document.getElementById("doctorTherapy");
const doctorEmail = document.getElementById("doctorEmail");

const doctorId = localStorage.getItem("doctorId");
const mockDoctors = JSON.parse(localStorage.getItem("mockDoctors")) || [];

let doctor = mockDoctors.find((d) => d.id == doctorId) || {
  id: 1,
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
};

doctorImg.src = doctor.image;
doctorName.innerText = doctor.name;
doctorSpecial.innerText = doctor.special;
doctorDegree.innerText = doctor.degree;
doctorExperience.innerText = doctor.experience;
doctorWork.innerText = doctor.work;
doctorConsult.innerText = doctor.consultPrice;
doctorTherapy.innerText = doctor.therapyPrice;
doctorEmail.innerText = doctor.email;
doctorEmail.href = `mailto:${doctor.email}`;

const confirmBtn = document.getElementById("confirmBooking");
const successMsg = document.getElementById("successMsg");

confirmBtn.addEventListener("click", () => {
  const sessionType = document.getElementById("sessionType").value;
  const sessionDate = document.getElementById("sessionDate").value;
  const sessionTime = document.getElementById("sessionTime").value;
  const meetingType = document.getElementById("meetingType").value;

  let hasError = false;
  const dateInput = document.getElementById("sessionDate");
  const timeInput = document.getElementById("sessionTime");

  if (!sessionDate) {
    dateInput.style.border = "1.5px solid red";
    if (!dateInput.nextElementSibling?.classList.contains("error-msg")) {
      const err = document.createElement("span");
      err.className = "error-msg";
      err.innerText = "يرجى اختيار التاريخ";
      dateInput.insertAdjacentElement("afterend", err);
    }
    hasError = true;
  } else {
    dateInput.style.border = "1.5px solid #e0e0e0";
    if (dateInput.nextElementSibling?.classList.contains("error-msg")) {
      dateInput.nextElementSibling.remove();
    }
  }

  if (!sessionTime) {
    timeInput.style.border = "1.5px solid red";
    if (!timeInput.nextElementSibling?.classList.contains("error-msg")) {
      const err = document.createElement("span");
      err.className = "error-msg";
      err.innerText = "يرجى اختيار الوقت";
      timeInput.insertAdjacentElement("afterend", err);
    }
    hasError = true;
  } else {
    timeInput.style.border = "1.5px solid #e0e0e0";
    if (timeInput.nextElementSibling?.classList.contains("error-msg")) {
      timeInput.nextElementSibling.remove();
    }
  }

  if (hasError) return;

  let bookings = JSON.parse(localStorage.getItem("bookings")) || [];

  bookings.push({
    doctorId: doctor.id,
    doctorName: doctor.name,
    doctorImage: doctor.image,
    sessionType: sessionType,
    meetingType: meetingType,
    date: sessionDate,
    time: sessionTime,
    status: "pending",
    meetingLink: "",
    roomNumber: "",
  });

  localStorage.setItem("bookings", JSON.stringify(bookings));

  successMsg.style.display = "block";
  successMsg.innerText = `تم إرسال طلب الحجز بنجاح.
نوع الجلسة: ${sessionType === "consult" ? "جلسة استشارية" : "جلسة علاجية"}
طريقة الجلسة: ${meetingType === "online" ? "إلكترونية" : "وجاهي"}
التاريخ: ${sessionDate}
الوقت: ${sessionTime}
بانتظار موافقة الأخصائي.`;
});

flatpickr("#sessionDate", {
  locale: "ar",
  dateFormat: "Y-m-d",
  minDate: "today",
  disableMobile: true,
});

flatpickr("#sessionTime", {
  enableTime: true,
  noCalendar: true,
  dateFormat: "H:i",
  time_24hr: false,
  disableMobile: true,
});

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
