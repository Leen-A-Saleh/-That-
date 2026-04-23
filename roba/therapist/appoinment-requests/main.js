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

// منطق القبول والرفض لكل طلب عبر AJAX
function handleAction(appointmentId, action, card) {
  const formData = new FormData();
  formData.append('appointment_id', appointmentId);
  formData.append('action', action);

  fetch('index.php', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      if (action === 'accept') {
        card.style.opacity = '0.5';
        card.querySelector('.request-footer').innerHTML = '<span style="color: #28a745; font-weight: 600;">✓ تم قبول الطلب</span>';
      } else {
        card.style.opacity = '0.5';
        card.querySelector('.request-footer').innerHTML = '<span style="color: #dc3545; font-weight: 600;">✗ تم رفض الطلب</span>';
      }
      card.dataset.status = action === 'accept' ? 'accepted' : 'rejected';
    }
  })
  .catch(() => {
    alert('حدث خطأ، يرجى المحاولة مرة أخرى');
  });
}

document.querySelectorAll('.btn-accept').forEach(btn => {
  btn.addEventListener('click', () => {
    const card = btn.closest('[data-status]');
    handleAction(btn.dataset.id, 'accept', card);
  });
});

document.querySelectorAll('.btn-reject').forEach(btn => {
  btn.addEventListener('click', () => {
    const card = btn.closest('[data-status]');
    handleAction(btn.dataset.id, 'reject', card);
  });
});
