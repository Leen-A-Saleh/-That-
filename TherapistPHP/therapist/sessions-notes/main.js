// تفعيل الروابط في الشريط الجانبي
document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
  link.addEventListener('click', () => {
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(i => i.classList.remove('active'));
    link.classList.add('active');
  });
});

// =========================================
// =========================================
const searchInput = document.getElementById('noteSearch');
let searchTimer;
if (searchInput) {
  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      document.getElementById('searchForm').submit();
    }, 600);
  });
}

// =========================================
// =========================================
(function () {
  const modal = document.getElementById('noteModal');
  const closeBtn = document.getElementById('noteModalClose');
  if (!modal || !closeBtn) return;

  const el = {
    avatar:   document.getElementById('noteModalAvatar'),
    name:     document.getElementById('noteModalName'),
    caseT:    document.getElementById('noteModalCase'),
    date:     document.getElementById('noteModalDate'),
    time:     document.getElementById('noteModalTime'),
    duration: document.getElementById('noteModalDuration'),
    mode:     document.getElementById('noteModalMode'),
  };

  var noteFields = [
    ['goals',          'noteFieldGoals',      'noteModalGoals'],
    ['mood',           'noteFieldMood',       'noteModalMood'],
    ['topics',         'noteFieldTopics',     'noteModalTopics'],
    ['techniques',     'noteFieldTechniques', 'noteModalTechniques'],
    ['progress',       'noteFieldProgress',   'noteModalProgress'],
    ['risk',           'noteFieldRisk',       'noteModalRisk'],
    ['therapistNotes', 'noteFieldTherapist',  'noteModalTherapist'],
    ['homework',       'noteFieldHomework',   'noteModalHomework'],
    ['nextPlan',       'noteFieldNextPlan',   'noteModalNextPlan'],
  ];

  function openModal(btn) {
    const d = btn.dataset;
    el.avatar.textContent = d.letter;
    el.name.textContent = d.name;
    el.caseT.textContent = d.case;
    el.date.textContent = d.date;
    el.time.textContent = d.time;
    el.duration.textContent = d.duration;
    el.mode.textContent = d.mode || '—';

    noteFields.forEach(function (f) {
      var value = d[f[0]] || '';
      var wrap = document.getElementById(f[1]);
      var content = document.getElementById(f[2]);
      if (value) {
        content.textContent = value;
        wrap.style.display = '';
      } else {
        wrap.style.display = 'none';
      }
    });

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }

  document.querySelectorAll('.btn-view-note').forEach(btn => {
    btn.addEventListener('click', () => openModal(btn));
  });

  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
  });
})();

// =========================================
// =========================================
const form = document.getElementById('sessionNotesForm');
if (form) {
  form.addEventListener('submit', function (e) {
    if (!confirm('هل أنت متأكد من إنهاء الجلسة وحفظ الملاحظات؟\nسيتم تغيير حالة الموعد إلى "مكتمل".')) {
      e.preventDefault();
    }
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
