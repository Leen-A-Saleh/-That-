// All data comes from APPOINTMENTS (injected by appointments.php).

// Username (injected from PHP session) 
const userNameEl = document.querySelector('.user-name');
if (userNameEl && typeof CURRENT_USER_NAME !== 'undefined') {
  userNameEl.innerText = CURRENT_USER_NAME;
}

// Status label map 
const STATUS_LABELS = {
  confirmed           : 'قادم',
  completed           : 'مكتمل',
  cancelled           : 'ملغي',
  pending             : 'قيد الانتظار',
  requested           : 'قيد الانتظار',
  awaiting_payment    : 'بانتظار الدفع',
  rejected            : 'مرفوض',
  payment_expired     : 'انتهت مهلة الدفع',
  cancelled_by_client : 'ملغي من قبلك',
};

const STATUS_CSS = {
  confirmed           : 'status-upcoming',
  completed           : 'status-finished',
  cancelled           : 'status-cancelled',
  pending             : 'status-upcoming',
  requested           : 'status-upcoming',
  awaiting_payment    : 'status-upcoming',
  rejected            : 'status-cancelled',
  payment_expired     : 'status-cancelled',
  cancelled_by_client : 'status-cancelled',
};

// Calendar 
let currentDate = new Date();

const calendar     = document.getElementById('calendar');
const monthYear    = document.getElementById('monthYear');
const prevMonthBtn = document.getElementById('prevMonth');
const nextMonthBtn = document.getElementById('nextMonth');
const detailsBox   = document.getElementById('bookingDetails');
const detailsContent = document.getElementById('detailsContent');

document.getElementById('closeDetails').addEventListener('click', () => {
  detailsBox.classList.add('hidden');
});

function renderCalendar(date) {
  calendar.innerHTML = '';

  const year  = date.getFullYear();
  const month = date.getMonth();
  monthYear.innerText = `${year} / ${month + 1}`;

  // Empty cells before first day
  const firstDay = new Date(year, month, 1).getDay();
  for (let i = 0; i < firstDay; i++) {
    const empty = document.createElement('div');
    empty.classList.add('calendar-empty');
    calendar.appendChild(empty);
  }

  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const today = new Date();

  for (let day = 1; day <= daysInMonth; day++) {
    const div = document.createElement('div');
    div.classList.add('calendar-day');

    // Highlight today
    if (
      day === today.getDate() &&
      month === today.getMonth() &&
      year  === today.getFullYear()
    ) {
      div.classList.add('today');
    }

    const dayNumber = document.createElement('span');
    dayNumber.innerText = day;
    div.appendChild(dayNumber);

    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    const dayAppointments = APPOINTMENTS.filter(a => a.date === dateStr);

    if (dayAppointments.length > 0) {
      div.classList.add('booked');
      const dot = document.createElement('div');
      dot.classList.add('booking-dot');
      div.appendChild(dot);

      div.addEventListener('click', () => showDetails(dayAppointments));
    }

    calendar.appendChild(div);
  }
}

function showDetails(appointments) {
  let html = '';
  appointments.forEach(a => {
    html += `
      <div class="booking-item">
        <strong>المعالج:</strong> ${a.therapist_name}<br>
        <strong>الحالة:</strong> ${STATUS_LABELS[a.status] ?? a.status}<br>
        <strong>طريقة الجلسة:</strong> ${a.mode === 'ONLINE' ? 'إلكترونية' : 'وجاهي'}<br>
        <strong>الوقت:</strong> ${a.time}<br>
        <strong>التاريخ:</strong> ${a.date}
        <hr>
      </div>
    `;
  });
  detailsContent.innerHTML = html;
  detailsBox.classList.remove('hidden');
}

prevMonthBtn.addEventListener('click', () => {
  currentDate.setMonth(currentDate.getMonth() - 1);
  renderCalendar(currentDate);
});
nextMonthBtn.addEventListener('click', () => {
  currentDate.setMonth(currentDate.getMonth() + 1);
  renderCalendar(currentDate);
});

// Appointments list 
function renderAppointmentsList() {
  const container = document.getElementById('appointmentsContainer');
  if (!container) return;

  container.innerHTML = '';

  if (APPOINTMENTS.length === 0) {
    container.innerHTML = '<p class="no-appointments">لا توجد مواعيد حالياً.</p>';
    return;
  }

  const now = new Date();

  // Upcoming first (confirmed, future), then the rest
  const sorted = [...APPOINTMENTS].sort((a, b) => {
    const da = new Date(a.date + ' ' + a.time);
    const db = new Date(b.date + ' ' + b.time);
    const aUp = da >= now && a.status === 'confirmed';
    const bUp = db >= now && b.status === 'confirmed';
    if (aUp && !bUp) return -1;
    if (!aUp && bUp) return 1;
    return da - db;
  });

  const defaultAvatar = '../images/default-doctor.png';

  sorted.forEach(a => {
    const card        = document.createElement('div');
    card.className    = 'appointment-card';
    const firstLetter = a.therapist_name ? a.therapist_name.charAt(0) : 'م';
    const labelText   = STATUS_LABELS[a.status] ?? a.status;
    const cssClass    = STATUS_CSS[a.status]    ?? 'status-upcoming';

    let actionHtml = '';
    if (a.status === 'confirmed') {
      actionHtml = `
        <span class="waiting-link" style="display:block; margin-bottom:5px;">سيتم إرسال رابط الجلسة لاحقاً</span>
        <button onclick="cancelAppointment(${a.id})" style="background:none; border:none; color:#dc2626; cursor:pointer; font-size:12px; text-decoration:underline;">إلغاء الموعد (مسترد 50%)</button>
      `;
    } else if (a.status === 'awaiting_payment') {
      actionHtml = `
        <a href="../../Public/handlers/mock-checkout.php?appointment_id=${a.id}" style="display:inline-block; padding:6px 12px; background-color:#2563eb; color:white; text-decoration:none; border-radius:4px; font-size:13px; margin-top:8px;">ادفع الآن لتأكيد الحجز</a>
      `;
    }

    const avatarHtml = a.therapist_avatar
      ? `<div class="avatar-circle avatar-circle--image">
          <img src="${a.therapist_avatar}" alt="" />
          <span class="avatar-initial">${firstLetter}</span>
        </div>`
      : `<div class="avatar-circle">
          <span class="avatar-initial">${firstLetter}</span>
        </div>`;

    card.innerHTML = `
      <div class="appointment-info">
        <h4>${a.therapist_name}</h4>
        <span class="appointment-status ${cssClass}">${labelText}</span>
        <div class="appointment-meta">
          <i class="fa-regular fa-clock"></i> ${a.time} &nbsp;&nbsp;
          <i class="fa-regular fa-calendar"></i> ${a.date}
        </div>
        ${actionHtml}
      </div>
      ${avatarHtml}
    `;

    const avatarImg = card.querySelector('.avatar-circle img');
    if (avatarImg) {
      avatarImg.addEventListener('error', function () {
        this.src = defaultAvatar;
        this.classList.add('avatar-img-fallback');
      });
    }

    container.appendChild(card);
  });
}

function cancelAppointment(id) {
  if (!confirm('هل أنت متأكد من رغبتك في إلغاء هذا الموعد؟ سيتم استرداد 50% فقط من المبلغ المدفوع.')) return;
  fetch('cancel.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'appointment_id=' + encodeURIComponent(id)
  })
  .then(res => res.json())
  .then(data => {
    alert(data.message);
    if (data.success) location.reload();
  })
  .catch(err => {
    alert('حدث خطأ أثناء الإلغاء.');
  });
}

// Sidebar toggle 
const menuBtn = document.getElementById('menuBtn');
const sidebar = document.querySelector('.sidebar');
const overlay = document.querySelector('.sidebar-overlay');

menuBtn.addEventListener('click', () => {
  sidebar.classList.toggle('open');
  overlay.classList.toggle('open');
});
overlay.addEventListener('click', () => {
  sidebar.classList.remove('open');
  overlay.classList.remove('open');
});

// Init 
renderCalendar(currentDate);
renderAppointmentsList();
