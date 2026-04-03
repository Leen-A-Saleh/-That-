// تفعيل الروابط في الشريط الجانبي (للشكل فقط في هذه الصفحة)
document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
  link.addEventListener('click', () => {
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(i => i.classList.remove('active'));
    link.classList.add('active');
  });
});

// فلترة المواعيد حسب اليوم (اليوم / الغد / الكل)
const dayButtons = document.querySelectorAll('.day-pill');
const appointmentCards = document.querySelectorAll('.appointment-card');

dayButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    dayButtons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const filter = btn.dataset.filter; // today / tomorrow / all

    appointmentCards.forEach(card => {
      const day = card.dataset.day; // today / tomorrow
      if (filter === 'all' || filter === day) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });
  });
});

appointmentCards.forEach(card => {
  const startBtn = card.querySelector('.start-btn');
  const cancelBtn = card.querySelector('.cancel-btn');
  const statusChip = card.querySelector('.appt-status-chip');

  const nameEl = card.querySelector('.appt-name');
  const clientName = nameEl ? nameEl.textContent.trim() : '';

  if (startBtn) {
    startBtn.addEventListener('click', () => {
      alert('بدء الجلسة مع: ' + clientName);
    });
  }

  if (cancelBtn && statusChip) {
    cancelBtn.addEventListener('click', () => {
      statusChip.textContent = 'ملغاة';
      statusChip.classList.remove('confirmed', 'pending');
      statusChip.classList.add('cancelled');
    });
  }
});