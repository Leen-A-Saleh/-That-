const appointments = window.appointmentData || [];
const appointmentStats = window.appointmentStats || {
  total: 0,
  confirmed: 0,
  pending: 0,
  online: 0,
};
const therapistLegend = window.therapistLegend || [];

let currentDate = new Date();

function createTextElement(tagName, className, text) {
  const element = document.createElement(tagName);
  element.className = className;
  element.textContent = text;
  return element;
}

function getFullDate(year, month, day) {
  const monthText = String(month + 1).padStart(2, "0");
  const dayText = String(day).padStart(2, "0");

  return `${year}-${monthText}-${dayText}`;
}

function renderCalendar() {
  const calendar = document.getElementById("calendar");
  const monthTitle = document.getElementById("monthTitle");

  if (!calendar || !monthTitle) {
    return;
  }

  calendar.innerHTML = "";

  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const monthNames = [
    "يناير",
    "فبراير",
    "مارس",
    "أبريل",
    "مايو",
    "يونيو",
    "يوليو",
    "أغسطس",
    "سبتمبر",
    "أكتوبر",
    "نوفمبر",
    "ديسمبر",
  ];

  monthTitle.textContent = `${monthNames[month]} ${year}`;

  for (let i = 0; i < firstDay; i++) {
    const emptyCell = document.createElement("div");
    emptyCell.className = "day empty-day";
    calendar.appendChild(emptyCell);
  }

  for (let day = 1; day <= daysInMonth; day++) {
    const fullDate = getFullDate(year, month, day);
    const dayCell = document.createElement("div");
    const dayNumber = createTextElement("div", "day-number", day);

    dayCell.className = "day";
    dayCell.appendChild(dayNumber);

    const dayAppointments = appointments.filter(function (appointment) {
      return appointment.date === fullDate && appointment.status === "confirmed";
    });

    dayAppointments.forEach(function (appointment) {
      const event = document.createElement("div");
      event.className = "event";
      event.style.backgroundColor = appointment.color;
      event.textContent = `${appointment.time} ${appointment.client_name}`;
      dayCell.appendChild(event);
    });

    calendar.appendChild(dayCell);
  }
}

function updateStats() {
  const total = document.getElementById("total");
  const confirmed = document.getElementById("confirmed");
  const pending = document.getElementById("pending");
  const online = document.getElementById("online");

  if (total) {
    total.textContent = appointmentStats.total;
  }

  if (confirmed) {
    confirmed.textContent = appointmentStats.confirmed;
  }

  if (pending) {
    pending.textContent = appointmentStats.pending;
  }

  if (online) {
    online.textContent = appointmentStats.online;
  }
}

function renderLegend() {
  const legend = document.getElementById("legend");

  if (!legend) {
    return;
  }

  legend.innerHTML = "";

  therapistLegend.slice(0, 6).forEach(function (therapist) {
    const legendItem = document.createElement("div");
    const colorBox = document.createElement("div");
    const name = createTextElement("span", "", therapist.name);

    legendItem.className = "legend-item";
    colorBox.className = "color-box";
    colorBox.style.backgroundColor = therapist.color;

    legendItem.appendChild(colorBox);
    legendItem.appendChild(name);
    legend.appendChild(legendItem);
  });
}

function setupMonthButtons() {
  const prevMonth = document.getElementById("prevMonth");
  const nextMonth = document.getElementById("nextMonth");

  if (prevMonth) {
    prevMonth.addEventListener("click", function () {
      currentDate.setMonth(currentDate.getMonth() - 1);
      renderCalendar();
    });
  }

  if (nextMonth) {
    nextMonth.addEventListener("click", function () {
      currentDate.setMonth(currentDate.getMonth() + 1);
      renderCalendar();
    });
  }
}

function setupSidebar() {
  const menuBtn = document.querySelector(".menu-btn");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.querySelector(".sidebar-overlay");

  if (!menuBtn || !sidebar || !overlay) {
    return;
  }

  menuBtn.addEventListener("click", function () {
    sidebar.classList.add("open");
    overlay.classList.add("open");
  });

  overlay.addEventListener("click", function () {
    sidebar.classList.remove("open");
    overlay.classList.remove("open");
  });
}

updateStats();
renderLegend();
renderCalendar();
setupMonthButtons();
setupSidebar();
