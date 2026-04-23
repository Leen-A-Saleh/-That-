const datePicker = document.getElementById('datePicker');
if (datePicker) {
  datePicker.addEventListener('change', () => {
    const url = new URL(window.location.href);
    url.searchParams.set('date', datePicker.value);
    window.location.href = url.toString();
  });
}

const dayButtons = document.querySelectorAll('.day-pill');
dayButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    const filter = btn.dataset.filter;
    const url = new URL(window.location.href);
    url.searchParams.set('view', filter);
    if (!url.searchParams.get('date')) {
      url.searchParams.set('date', datePicker ? datePicker.value : new Date().toISOString().slice(0, 10));
    }
    window.location.href = url.toString();
  });
});

// =========================================
// =========================================
(function () {
  const modal = document.getElementById('detailsModal');
  const closeBtn = document.getElementById('modalClose');
  if (!modal || !closeBtn) return;

  var currentApptId = null;

  const el = {
    avatar:       document.getElementById('modalAvatar'),
    name:         document.getElementById('modalName'),
    age:          document.getElementById('modalAge'),
    status:       document.getElementById('modalStatus'),
    date:         document.getElementById('modalDate'),
    time:         document.getElementById('modalTime'),
    duration:     document.getElementById('modalDuration'),
    mode:         document.getElementById('modalMode'),
    session:      document.getElementById('modalSession'),
    room:         document.getElementById('modalRoom'),
    roomWrap:     document.getElementById('modalRoomWrap'),
    caseTitle:    document.getElementById('modalCase'),
    caseDesc:     document.getElementById('modalCaseDesc'),
    caseDescLabel:document.getElementById('modalCaseDescLabel'),
    caseWrap:     document.getElementById('modalCaseWrap'),
    zoomLink:     document.getElementById('modalZoomLink'),
    zoomWrap:     document.getElementById('modalZoomWrap'),
    zoomInput:    document.getElementById('modalZoomInput'),
    zoomSave:     document.getElementById('modalZoomSave'),
    zoomFeedback: document.getElementById('modalZoomFeedback'),
    created:      document.getElementById('modalCreated'),
    completeWrap: document.getElementById('modalCompleteWrap'),
    completeBtn:  document.getElementById('modalCompleteBtn'),
  };

  function openModal(btn) {
    const d = btn.dataset;
    currentApptId = d.id;

    el.avatar.textContent = d.letter;
    el.name.textContent = d.name;
    el.age.textContent = d.age;

    el.status.textContent = d.status;
    el.status.className = 'severity-badge ' + d.statusClass;

    el.date.textContent = d.date;
    el.time.textContent = d.time;
    el.duration.textContent = d.duration;
    el.mode.textContent = d.mode;
    el.session.textContent = d.session;

    if (d.room) {
      el.room.textContent = d.room;
      el.roomWrap.style.display = '';
    } else {
      el.roomWrap.style.display = 'none';
    }

    if (d.case) {
      el.caseTitle.textContent = d.case;
      el.caseWrap.style.display = '';
      if (d.caseDesc) {
        el.caseDesc.textContent = d.caseDesc;
        el.caseDescLabel.style.display = '';
      } else {
        el.caseDesc.textContent = '';
        el.caseDescLabel.style.display = 'none';
      }
    } else {
      el.caseWrap.style.display = 'none';
    }

    if (d.modeRaw === 'ONLINE') {
      el.zoomWrap.style.display = '';
      el.zoomInput.value = d.zoom || '';
      el.zoomFeedback.textContent = '';
      el.zoomFeedback.className = 'zoom-feedback';

      if (d.zoom) {
        el.zoomLink.href = d.zoom;
        el.zoomLink.style.display = '';
      } else {
        el.zoomLink.style.display = 'none';
      }
    } else {
      el.zoomWrap.style.display = 'none';
    }

    if (d.statusRaw === 'CONFIRMED') {
      el.completeWrap.style.display = '';
      el.completeBtn.href = '../sessions-notes/note-form.php?appointment_id=' + d.id;
    } else {
      el.completeWrap.style.display = 'none';
    }

    el.created.textContent = 'تاريخ إنشاء الموعد: ' + d.created;

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    modal.classList.remove('active');
    document.body.style.overflow = '';
    currentApptId = null;
  }

  el.zoomSave.addEventListener('click', function () {
    if (!currentApptId) return;

    var zoomValue = el.zoomInput.value.trim();
    el.zoomFeedback.textContent = '';
    el.zoomFeedback.className = 'zoom-feedback';

    el.zoomSave.disabled = true;
    el.zoomSave.style.opacity = '0.6';

    var formData = new FormData();
    formData.append('action', 'update_zoom');
    formData.append('appointment_id', currentApptId);
    formData.append('zoom_link', zoomValue);

    fetch('index.php', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (data.success) {
        el.zoomFeedback.textContent = 'تم حفظ الرابط بنجاح';
        el.zoomFeedback.className = 'zoom-feedback success';

        if (zoomValue) {
          el.zoomLink.href = zoomValue;
          el.zoomLink.style.display = '';
        } else {
          el.zoomLink.style.display = 'none';
        }

        var card = document.querySelector('[data-id="' + currentApptId + '"].btn-details');
        if (card) card.dataset.zoom = zoomValue;

        var articleCard = document.querySelector('article[data-id="' + currentApptId + '"]');
        if (articleCard) {
          var footer = articleCard.querySelector('.request-footer');
          var firstBtn = footer.querySelector('.btn-primary, a.btn-primary');
          if (firstBtn) {
            if (zoomValue) {
              var link = document.createElement('a');
              link.href = zoomValue;
              link.target = '_blank';
              link.className = 'btn-primary btn-accept';
              link.innerHTML = '<i class="fa-solid fa-video" style="color:#fff;"></i> انضم للجلسة';
              firstBtn.replaceWith(link);
            } else {
              var disabledBtn = document.createElement('button');
              disabledBtn.className = 'btn-primary btn-accept';
              disabledBtn.disabled = true;
              disabledBtn.style.opacity = '0.6';
              disabledBtn.style.cursor = 'not-allowed';
              disabledBtn.innerHTML = '<i class="fa-solid fa-video" style="color:#fff;"></i> انضم للجلسة';
              firstBtn.replaceWith(disabledBtn);
            }
          }
        }
      } else {
        el.zoomFeedback.textContent = 'حدث خطأ، حاول مرة أخرى';
        el.zoomFeedback.className = 'zoom-feedback error';
      }
    })
    .catch(function () {
      el.zoomFeedback.textContent = 'حدث خطأ في الاتصال';
      el.zoomFeedback.className = 'zoom-feedback error';
    })
    .finally(function () {
      el.zoomSave.disabled = false;
      el.zoomSave.style.opacity = '';
    });
  });

  document.querySelectorAll('.btn-details').forEach(btn => {
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
