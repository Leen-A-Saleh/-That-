// تفعيل حالة "active" على عناصر القائمة الجانبية
document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
  link.addEventListener('click', () => {
    document
      .querySelectorAll('.sidebar-nav .nav-link')
      .forEach(i => i.classList.remove('active'));
    link.classList.add('active');
  });
});

// جمع كروت الطلبات
const requestCards = document.querySelectorAll('[data-status="pending"]');

// زر فلترة الطلبات العاجلة
const urgentToggle = document.getElementById('urgentToggle');

if (urgentToggle) {
  let showUrgentOnly = false;

  function applyUrgentFilter() {
    requestCards.forEach(card => {
      const isUrgent = card.dataset.urgent === 'true';
      card.style.display = showUrgentOnly && !isUrgent ? 'none' : '';
    });
  }

  urgentToggle.addEventListener('click', () => {
    showUrgentOnly = !showUrgentOnly;
    urgentToggle.classList.toggle('active', showUrgentOnly);
    applyUrgentFilter();
  });

  urgentToggle.classList.remove('active');
  applyUrgentFilter();
}

// Accept/reject handled by ../js/appointment-request-actions.js
