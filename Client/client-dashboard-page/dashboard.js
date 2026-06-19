// ─── Sidebar

const menuBtn = document.getElementById("menuBtn");
const sidebar = document.querySelector(".sidebar");

let overlay = document.querySelector(".sidebar-overlay");
if (!overlay) {
  overlay = document.createElement("div");
  overlay.className = "sidebar-overlay";
  document.body.appendChild(overlay);
}

if (menuBtn && sidebar) {
  menuBtn.addEventListener("click", function () {
    sidebar.classList.toggle("open");
    overlay.classList.toggle("open");
  });
}

overlay.addEventListener("click", function () {
  if (!sidebar) return;
  sidebar.classList.remove("open");
  overlay.classList.remove("open");
});

// ─── Utilities

function escapeHtml(value) {
  return String(value || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

// ─── Doctors

const container = document.getElementById("doctorsContainer");
const count = document.getElementById("doctorCount");
const searchInput = document.getElementById("searchInput");
let doctors = Array.isArray(doctorsFromDatabase) ? doctorsFromDatabase : [];

function formatAvailability(slots, fallback) {
  if (!Array.isArray(slots) || slots.length === 0) {
    return fallback;
  }

  const grouped = [];
  const dayIndexes = new Map();

  slots.forEach(function (slot) {
    const day = String(slot.day_label || "").trim();
    const range = (slot.start + " - " + slot.end).trim();

    if (!day || range === " - ") {
      return;
    }

    if (!dayIndexes.has(day)) {
      dayIndexes.set(day, grouped.length);
      grouped.push({ day: day, ranges: [] });
    }

    grouped[dayIndexes.get(day)].ranges.push(range);
  });

  if (grouped.length === 0) {
    return fallback;
  }

  return grouped
    .map(function (item) { return item.day + " " + item.ranges.join("، "); })
    .join("<br>");
}

function renderDoctors(list) {
  if (!container) return;

  container.innerHTML = "";

  if (count) {
    count.innerText = list.length;
  }

  list.forEach(function (doc) {
    const availability = formatAvailability(doc.availability, doc.work);

    container.innerHTML += `
<div class="doctor-card">
<img src="${escapeHtml(doc.image)}" onerror="this.src='../../storage/avatars/user.png'">
<div class="doctor-info">
<div class="doctor-name">${escapeHtml(doc.name)}</div>
<div class="doctor-special">${escapeHtml(doc.special)}</div>
<div class="doctor-degree">${escapeHtml(doc.degree)}</div>
<div class="doctor-details">
<i class="fa fa-briefcase"></i> ${escapeHtml(doc.experience)}<br>
<i class="fa fa-clock"></i> ${availability}<br>
<i class="fa fa-sack-dollar"></i> جلسة استشارية: ${escapeHtml(doc.consultPrice)}<br>
<i class="fa fa-coins"></i> جلسة علاجية: ${escapeHtml(doc.therapyPrice)}<br>
<i class="fa fa-envelope"></i>
<a href="mailto:${escapeHtml(doc.email)}">${escapeHtml(doc.email)}</a>
</div>
<button class="book-btn" data-id="${escapeHtml(String(doc.id))}">
حجز موعد
</button>
</div>
</div>
`;
  });
}

renderDoctors(doctors);

if (searchInput) {
  searchInput.addEventListener("input", function () {
    const value = this.value.toLowerCase();
    const filtered = doctors.filter(function (doc) {
      return (
        String(doc.name || "").toLowerCase().includes(value) ||
        String(doc.special || "").toLowerCase().includes(value) ||
        String(doc.degree || "").toLowerCase().includes(value)
      );
    });
    renderDoctors(filtered);
  });
}

document.addEventListener("click", function (e) {
  if (e.target.classList.contains("book-btn")) {
    const doctorId = Number(e.target.dataset.id || 0);
    if (!Number.isInteger(doctorId) || doctorId <= 0) {
      showErrorAlert("تعذر تحديد الأخصائي. يرجى المحاولة مرة أخرى.");
      return;
    }
    window.location.href = "../client-booking-page/booking.php?id=" + encodeURIComponent(doctorId);
  }
});

// ─── Booking Error

if (bookingError === "missing_therapist_id") {
  showWarningAlert("لا يمكن فتح صفحة الحجز بدون تحديد أخصائي.");
}
