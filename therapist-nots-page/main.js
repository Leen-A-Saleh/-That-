// تفعيل الروابط في الشريط الجانبي (للشكل فقط في هذه الصفحة)
document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
  link.addEventListener('click', () => {
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(i => i.classList.remove('active'));
    link.classList.add('active');
  });
});
// قائمة اختيار الجلسة
const sessionToggle = document.getElementById('sessionToggle');
const sessionMenu = document.getElementById('sessionMenu');

if (sessionToggle && sessionMenu) {
  sessionToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    sessionMenu.classList.toggle('open');
  });

  document.addEventListener('click', () => {
    sessionMenu.classList.remove('open');
  });

  sessionMenu.querySelectorAll('button').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const num = btn.dataset.session;
      sessionToggle.firstChild.nodeValue = `جلسة رقم ${num} `;
      sessionMenu.classList.remove('open');
    });
  });
}

// طي/فتح أقسام النموذج
document.querySelectorAll('[data-section]').forEach(section => {
  const toggleBtn = section.querySelector('.section-toggle');
  const icon = toggleBtn?.querySelector('i');
  const body = section.querySelector('.section-body');
  const hint = section.querySelector('.section-hint');

  if (!toggleBtn || !body) return;

  toggleBtn.addEventListener('click', () => {
    const isHidden = body.style.display === 'none';
    body.style.display = isHidden ? '' : 'none';
    if (hint) hint.style.display = isHidden ? '' : 'none';
    if (icon) {
      icon.classList.toggle('fa-chevron-up', isHidden);
      icon.classList.toggle('fa-chevron-down', !isHidden);
    }
  });
});

// حفظ وإلغاء (عرض رسائل فقط)
const form = document.getElementById('sessionNotesForm');
const cancelBtn = document.getElementById('cancelNotes');

if (form) {
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    alert('تم حفظ ملاحظات الجلسة (نموذج تجريبي).');
  });
}

if (cancelBtn) {
  cancelBtn.addEventListener('click', () => {
    if (confirm('هل أنت متأكد من إلغاء الحفظ؟ سيتم فقدان التعديلات غير المحفوظة.')) {
      form?.reset();
    }
  });
}