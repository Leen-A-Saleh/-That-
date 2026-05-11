const menuBtn = document.getElementById("menuBtn");
const sidebar = document.querySelector(".sidebar");
let overlay = document.querySelector(".sidebar-overlay");

if (!overlay) {
  overlay = document.createElement("div");
  overlay.className = "sidebar-overlay";
  document.body.appendChild(overlay);
}

menuBtn.addEventListener("click", function () {
  sidebar.classList.toggle("active");
  sidebar.classList.toggle("open");
  overlay.classList.toggle("open");
});

overlay.addEventListener("click", function () {
  sidebar.classList.remove("active");
  sidebar.classList.remove("open");
  overlay.classList.remove("open");
});

const doctorImg = document.getElementById("doctorImg");
doctorImg.onerror = function () {
  doctorImg.src = "../images/default-doctor.png";
};

if (bookingErrorMessage) {
  alert(bookingErrorMessage);
}

const bookingForm = document.getElementById("bookingForm");
const confirmBtn = document.getElementById("confirmBooking");
const successMsg = document.getElementById("successMsg");
const dateInput = document.getElementById("sessionDate");
const timeInput = document.getElementById("sessionTime");

const dayNames = [
  "SUNDAY",
  "MONDAY",
  "TUESDAY",
  "WEDNESDAY",
  "THURSDAY",
  "FRIDAY",
  "SATURDAY",
];

function showMessage(message, isError) {
  successMsg.style.display = "block";
  successMsg.innerText = message;

  if (isError) {
    successMsg.classList.add("error");
  } else {
    successMsg.classList.remove("error");
  }
}

function showError(input, message) {
  input.style.border = "1.5px solid red";

  if (!input.nextElementSibling || !input.nextElementSibling.classList.contains("error-msg")) {
    const span = document.createElement("span");
    span.className = "error-msg";
    span.innerText = message;
    input.insertAdjacentElement("afterend", span);
  }
}

function removeError(input) {
  input.style.border = "1.5px solid #e0e0e0";

  if (input.nextElementSibling && input.nextElementSibling.classList.contains("error-msg")) {
    input.nextElementSibling.remove();
  }
}

function getSelectedDay(dateValue) {
  const date = new Date(dateValue + "T00:00:00");
  return dayNames[date.getDay()];
}

function loadTimes() {
  const selectedDate = dateInput.value;
  timeInput.innerHTML = "";

  if (!selectedDate) {
    timeInput.disabled = true;
    timeInput.append(new Option("اختر التاريخ أولا", ""));
    return;
  }

  const selectedDay = getSelectedDay(selectedDate);
  const slots = [];

  for (let i = 0; i < bookingAvailability.length; i++) {
    if (bookingAvailability[i].day === selectedDay) {
      slots.push(bookingAvailability[i]);
    }
  }

  if (slots.length === 0) {
    timeInput.disabled = true;
    timeInput.append(new Option("لا توجد أوقات متاحة لهذا اليوم", ""));
    return;
  }

  timeInput.disabled = false;
  timeInput.append(new Option("اختر الوقت", ""));

  for (let i = 0; i < slots.length; i++) {
    timeInput.append(new Option(slots[i].start + " - " + slots[i].end, slots[i].start));
  }
}

function checkForm() {
  let isValid = true;

  if (!dateInput.value) {
    showError(dateInput, "يرجى اختيار التاريخ");
    isValid = false;
  } else {
    removeError(dateInput);
  }

  if (!timeInput.value) {
    showError(timeInput, "يرجى اختيار الوقت");
    isValid = false;
  } else {
    removeError(timeInput);
  }

  return isValid;
}

const availableDays = [];
for (let i = 0; i < bookingAvailability.length; i++) {
  if (!availableDays.includes(bookingAvailability[i].day)) {
    availableDays.push(bookingAvailability[i].day);
  }
}

flatpickr("#sessionDate", {
  locale: "ar",
  dateFormat: "Y-m-d",
  minDate: "today",
  disableMobile: true,
  disable: [
    function (date) {
      const day = dayNames[date.getDay()];
      return !availableDays.includes(day);
    },
  ],
  onChange: loadTimes,
});

dateInput.addEventListener("change", loadTimes);

bookingForm.addEventListener("submit", function (event) {
  event.preventDefault();

  if (!checkForm()) {
    return;
  }

  confirmBtn.disabled = true;

  fetch(window.location.href, {
    method: "POST",
    body: new FormData(bookingForm),
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (!data.success) {
        showMessage(data.message || "تعذر إرسال طلب الحجز.", true);
        return;
      }

      showMessage(data.message, false);
      bookingForm.reset();
      loadTimes();
    })
    .catch(function () {
      showMessage("تعذر إرسال طلب الحجز.", true);
    })
    .finally(function () {
      confirmBtn.disabled = false;
    });
});

if (bookingAvailability.length === 0) {
  confirmBtn.disabled = true;
  showMessage("لا توجد أوقات متاحة لهذا الأخصائي حاليا.", true);
}
