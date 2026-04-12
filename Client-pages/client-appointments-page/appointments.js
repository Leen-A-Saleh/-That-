const data = { total: 24, active: 18, today: 5 };
function renderStats(stats) {
  document.getElementById("totalCases").innerText = stats.total;
  document.getElementById("activeCases").innerText = stats.active;
  document.getElementById("todayAppointments").innerText = stats.today;
}
renderStats(data);

let user = localStorage.getItem("username");
if (user) {
  document.querySelector(".user-name").innerText = user;
}

const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) {
  logoutBtn.addEventListener("click", () => {
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

document.getElementById("closeDetails").addEventListener("click", () => {
  detailsBox.classList.add("hidden");
});

function renderCalendar(date) {
  calendar.innerHTML = "";

  const year = date.getFullYear();
  const month = date.getMonth();
  monthYear.innerText = `${year} / ${month + 1}`;

  let firstDay = new Date(year, month, 1).getDay();
  for (let i = 0; i < firstDay; i++) {
    const empty = document.createElement("div");
    empty.classList.add("calendar-empty");
    calendar.appendChild(empty);
  }

  const daysInMonth = new Date(year, month + 1, 0).getDate();

  for (let day = 1; day <= daysInMonth; day++) {
    const div = document.createElement("div");
    div.classList.add("calendar-day");

    const today = new Date();
    if (
      day === today.getDate() &&
      month === today.getMonth() &&
      year === today.getFullYear()
    ) {
      div.classList.add("today");
    }

    const dayNumber = document.createElement("span");
    dayNumber.innerText = day;
    div.appendChild(dayNumber);

    const dateStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(
      day,
    ).padStart(2, "0")}`;

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
  bookings.forEach((b) => {
    let meetingInfo = "";
    if (b.meetingType === "online" && b.meetingLink) {
      meetingInfo = `<br><strong>رابط الجلسة:</strong> <a href="${b.meetingLink}" target="_blank">الدخول للجلسة</a>`;
    }
    if (b.meetingType === "offline" && b.roomNumber) {
      meetingInfo = `<br><strong>الغرفة:</strong> ${b.roomNumber}`;
    }
    html += `
      <div class="booking-item">
        <strong>الطبيب:</strong> ${b.doctorName}<br>
        <strong>نوع الجلسة:</strong> ${b.sessionType === "consult" ? "استشارية" : "علاجية"}<br>
        <strong>طريقة الجلسة:</strong> ${b.meetingType === "online" ? "إلكترونية" : "وجاهي"}<br>
        <strong>الوقت:</strong> ${b.time}<br>
        <strong>التاريخ:</strong> ${b.date}
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

  sortedBookings.forEach((b) => {
    const card = document.createElement("div");
    card.className = "appointment-card";

    const firstLetter = b.doctorName ? b.doctorName.charAt(0) : "د";

    const bookingDate = new Date(b.date + " " + b.time);
    const status =
      bookingDate > now
        ? '<span class="appointment-status status-upcoming">قادم</span>'
        : '<span class="appointment-status status-finished">منتهي</span>';

    let actionButton = "";
    if (b.meetingType === "online") {
      actionButton = b.meetingLink
        ? `<a class="join-link" href="${b.meetingLink}" target="_blank">الانضمام للجلسة</a>`
        : `<span class="waiting-link">سيتم إرسال رابط الجلسة لاحقاً</span>`;
    }
    if (b.meetingType === "offline") {
      actionButton = b.roomNumber
        ? `<span class="room-number"> الغرفة : ${b.roomNumber}</span>`
        : `<span class="waiting-room">سيتم تحديد الغرفة لاحقاً</span>`;
    }

    card.innerHTML = `
      <div class="appointment-info">
        <h4>${b.doctorName}</h4>
        ${status}
        <div class="appointment-meta">
          <i class="fa-regular fa-clock"></i> ${b.time} &nbsp;&nbsp;
          <i class="fa-regular fa-calendar"></i> ${b.date}
        </div>
        ${actionButton}
      </div>
      <div class="avatar-circle">${firstLetter}</div>
    `;
    container.appendChild(card);
  });
}

const menuBtn = document.getElementById("menuBtn");
const sidebar = document.querySelector(".sidebar");
const overlay = document.querySelector(".sidebar-overlay");

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
