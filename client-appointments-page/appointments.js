const menuBtn = document.getElementById("menuBtn");
const sidebar = document.querySelector(".sidebar");

menuBtn?.addEventListener("click", () => {
  sidebar.classList.toggle("active");
});

let user = localStorage.getItem("username");
if (user) {
  document.getElementById("username").innerText = user;
}

const logoutBtn = document.getElementById("logoutBtn");

if (logoutBtn) {
  logoutBtn.addEventListener("click", function () {
    localStorage.removeItem("token");
    window.location.href = "login.html";
  });
}

const bookings = JSON.parse(localStorage.getItem("bookings")) || [];

const approvedBookings = bookings.filter(
  (v, i, a) =>
    i ===
    a.findIndex(
      (t) =>
        t.date === v.date && t.time === v.time && t.doctorName === v.doctorName,
    ),
);

let currentDate = new Date();

const calendar = document.getElementById("calendar");
const monthYear = document.getElementById("monthYear");
const prevMonthBtn = document.getElementById("prevMonth");
const nextMonthBtn = document.getElementById("nextMonth");

const detailsBox = document.getElementById("bookingDetails");
const detailsContent = document.getElementById("detailsContent");

document
  .getElementById("closeDetails")
  .addEventListener("click", () => detailsBox.classList.add("hidden"));

function renderCalendar(date) {
  calendar.innerHTML = "";

  const year = date.getFullYear();
  const month = date.getMonth();

  monthYear.innerText = `${year} / ${month + 1}`;

  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  for (let i = 0; i < firstDay; i++) {
    const empty = document.createElement("div");
    empty.classList.add("calendar-empty");
    calendar.appendChild(empty);
  }

  for (let day = 1; day <= daysInMonth; day++) {
    const div = document.createElement("div");
    div.classList.add("calendar-day");

    const dayNumber = document.createElement("span");
    dayNumber.innerText = day;

    div.appendChild(dayNumber);

    const dateStr = `${year}-${String(month + 1).padStart(
      2,
      "0",
    )}-${String(day).padStart(2, "0")}`;

    const dayBookings = approvedBookings.filter((b) => b.date === dateStr);

    if (dayBookings.length > 0) {
      div.classList.add("booked");

      const dot = document.createElement("div");
      dot.classList.add("booking-dot");
      div.appendChild(dot);
    }

    div.addEventListener("click", () => {
      if (dayBookings.length > 0) {
        showDetails(dayBookings);
      }
    });

    calendar.appendChild(div);
  }
}

function showDetails(bookings) {
  let html = "";

  bookings.forEach((booking) => {
    let meetingInfo = "";

    if (booking.meetingType === "online" && booking.meetingLink) {
      meetingInfo = `<br><strong>رابط الجلسة:</strong> 
      <a href="${booking.meetingLink}" target="_blank">الدخول للجلسة</a>`;
    }

    if (booking.meetingType === "offline" && booking.roomNumber) {
      meetingInfo = `<br><strong>الغرفة:</strong> ${booking.roomNumber}`;
    }

    html += `
      <div class="booking-item">

        <strong>الطبيب:</strong> ${booking.doctorName}<br>

        <strong>نوع الجلسة:</strong> ${booking.sessionType === "consult" ? "استشارية" : "علاجية"}<br>

        <strong>طريقة الجلسة:</strong> ${booking.meetingType === "online" ? "إلكترونية" : "وجاهي"}<br>

        <strong>الوقت:</strong> ${booking.time}<br>

        <strong>التاريخ:</strong> ${booking.date}

        ${meetingInfo}

        <hr>

      </div>
    `;
  });

  detailsContent.innerHTML = html;
  detailsBox.classList.remove("hidden");
}

prevMonthBtn.addEventListener("click", () => {
  currentDate.setMonth(currentDate.getMonth() - 1);
  renderCalendar(currentDate);
});

nextMonthBtn.addEventListener("click", () => {
  currentDate.setMonth(currentDate.getMonth() + 1);
  renderCalendar(currentDate);
});

function renderAppointmentsList() {
  const container = document.getElementById("appointmentsContainer");

  if (!container) return;

  container.innerHTML = "";

  const now = new Date();

  const upcoming = approvedBookings
    .filter((b) => new Date(b.date + " " + b.time) >= now)
    .sort(
      (a, b) =>
        new Date(a.date + " " + a.time) - new Date(b.date + " " + b.time),
    );

  const finished = approvedBookings
    .filter((b) => new Date(b.date + " " + b.time) < now)
    .sort(
      (a, b) =>
        new Date(b.date + " " + b.time) - new Date(a.date + " " + a.time),
    );

  const sortedBookings = [...upcoming, ...finished];

  sortedBookings.forEach((booking) => {
    const card = document.createElement("div");
    card.className = "appointment-card";

    const firstLetter = booking.doctorName ? booking.doctorName.charAt(0) : "د";

    const now = new Date();
    const bookingDate = new Date(booking.date + " " + booking.time);

    const status =
      bookingDate > now
        ? '<span class="appointment-status status-upcoming">قادم</span>'
        : '<span class="appointment-status status-finished">منتهي</span>';

    let actionButton = "";

    if (booking.meetingType === "online") {
      if (booking.meetingLink) {
        actionButton = `<a class="join-link" href="${booking.meetingLink}" target="_blank">الانضمام للجلسة</a>`;
      } else {
        actionButton = `<span class="waiting-link">سيتم إرسال رابط الجلسة لاحقاً</span>`;
      }
    }

    if (booking.meetingType === "offline") {
      if (booking.roomNumber) {
        actionButton = `<span class="room-number"> الغرفة : ${booking.roomNumber}</span>`;
      } else {
        actionButton = `<span class="waiting-room">سيتم تحديد الغرفة لاحقاً</span>`;
      }
    }

    card.innerHTML = `

      <div class="appointment-info">

      <h4>${booking.doctorName}</h4>

      ${status}

      <div class="appointment-meta">

      <i class="fa-regular fa-clock"></i> ${booking.time}

      &nbsp;&nbsp;

      <i class="fa-regular fa-calendar"></i> ${booking.date}

      </div>

      ${actionButton}

      </div>

      <div class="avatar-circle">${firstLetter}</div>
  `;

    container.appendChild(card);
  });
}

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
renderCalendar(currentDate);
renderAppointmentsList();
