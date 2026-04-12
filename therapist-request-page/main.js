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
const requestCards = document.querySelectorAll('.request-card');

// زر فلترة الطلبات العاجلة
const urgentToggle = document.getElementById('urgentToggle');

if (urgentToggle) {
  // افتراضياً: عرض كل الطلبات
  let showUrgentOnly = false;

  function applyUrgentFilter() {
    requestCards.forEach(card => {
      const isUrgent = card.dataset.urgent === 'true';
      // إن كان الفلتر مفعّل نعرض العاجلة فقط، غير ذلك نعرض الكل
      card.style.display = showUrgentOnly && !isUrgent ? 'none' : '';
    });
  }

  // عند الضغط على زر "طلبات قيد الانتظار"
  urgentToggle.addEventListener('click', () => {
    showUrgentOnly = !showUrgentOnly;
    urgentToggle.classList.toggle('active', showUrgentOnly);
    applyUrgentFilter();
  });

  // عند تحميل الصفحة: عرض الكل
  urgentToggle.classList.remove('active'); // اختياري
  applyUrgentFilter();
}

// منطق القبول والرفض لكل طلب
requestCards.forEach(card => {
  const acceptBtn = card.querySelector('.btn-accept');
  const rejectBtn = card.querySelector('.btn-reject');
  const statusChip = card.querySelector('.status-chip');

  if (!statusChip) return;

  if (acceptBtn) {
    acceptBtn.addEventListener('click', () => {
      statusChip.textContent = 'تم القبول';
      statusChip.classList.remove('status-pending', 'status-rejected');
      statusChip.classList.add('status-accepted');
      card.dataset.status = 'accepted';
    });
  }

  if (rejectBtn) {
    rejectBtn.addEventListener('click', () => {
      statusChip.textContent = 'تم الرفض';
      statusChip.classList.remove('status-pending', 'status-accepted');
      statusChip.classList.add('status-rejected');
      card.dataset.status = 'rejected';
    });
  }
});