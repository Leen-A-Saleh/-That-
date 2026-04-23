document.addEventListener('DOMContentLoaded', function () {

  var searchInput = document.getElementById('searchInput');
  var searchForm = document.getElementById('searchForm');
  var searchTimer = null;

  if (searchInput && searchForm) {
    searchInput.addEventListener('input', function () {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(function () {
        searchForm.submit();
      }, 600);
    });
  }

  var viewModal = document.getElementById('viewModal');
  var viewClose = document.getElementById('viewModalClose');

  document.querySelectorAll('.btn-view').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var d = this.dataset;
      document.getElementById('viewTitle').textContent = d.title;
      document.getElementById('viewDesc').textContent = d.description || 'لا يوجد وصف';
      document.getElementById('viewCategory').textContent = d.category || '—';
      document.getElementById('viewType').textContent = d.typeLabel;
      document.getElementById('viewDuration').textContent = d.duration + ' دقيقة';
      document.getElementById('viewDifficulty').textContent = d.difficultyLabel;
      document.getElementById('viewAssigned').textContent = d.assigned + ' مريض';
      document.getElementById('viewCompletion').textContent = d.completion + '%';
      document.getElementById('viewStatus').textContent = d.statusLabel;
      document.getElementById('viewCreated').textContent = d.created;
      viewModal.classList.add('show');
    });
  });

  if (viewClose) {
    viewClose.addEventListener('click', function () {
      viewModal.classList.remove('show');
    });
  }

  viewModal.addEventListener('click', function (e) {
    if (e.target === viewModal) viewModal.classList.remove('show');
  });

  var formModal = document.getElementById('formModal');
  var formClose = document.getElementById('formModalClose');
  var formCancel = document.getElementById('formCancelBtn');
  var formTitle = document.getElementById('formModalTitle');
  var addBtn = document.getElementById('addActivityBtn');

  function openFormModal(mode, data) {
    if (mode === 'edit') {
      formTitle.textContent = 'تعديل النشاط';
      document.getElementById('formAction').value = 'edit';
      document.getElementById('formActivityId').value = data.id;
      document.getElementById('formTitle').value = data.title;
      document.getElementById('formDescription').value = data.description || '';
      document.getElementById('formCategory').value = data.category || '';
      document.getElementById('formType').value = data.type;
      document.getElementById('formDuration').value = data.duration;
      document.getElementById('formDifficulty').value = data.difficulty;
      document.getElementById('formStatus').value = data.status;
    } else {
      formTitle.textContent = 'إضافة نشاط جديد';
      document.getElementById('formAction').value = 'add';
      document.getElementById('formActivityId').value = '';
      document.getElementById('activityForm').reset();
    }
    formModal.classList.add('show');
  }

  function closeFormModal() {
    formModal.classList.remove('show');
  }

  if (addBtn) {
    addBtn.addEventListener('click', function () {
      openFormModal('add');
    });
  }

  document.querySelectorAll('.btn-edit').forEach(function (btn) {
    btn.addEventListener('click', function () {
      openFormModal('edit', this.dataset);
    });
  });

  if (formClose) formClose.addEventListener('click', closeFormModal);
  if (formCancel) formCancel.addEventListener('click', closeFormModal);

  formModal.addEventListener('click', function (e) {
    if (e.target === formModal) closeFormModal();
  });

  document.querySelectorAll('.btn-delete').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = this.dataset.id;
      var title = this.dataset.title;
      var row = this.closest('tr');

      if (!confirm('هل أنت متأكد من حذف النشاط "' + title + '"؟')) return;

      var fd = new FormData();
      fd.append('action', 'delete');
      fd.append('activity_id', id);

      fetch('index.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            row.style.transition = 'opacity 0.3s';
            row.style.opacity = '0';
            setTimeout(function () {
              row.remove();
              var tbody = document.querySelector('.activities-table tbody');
              if (tbody && tbody.children.length === 0) {
                location.reload();
              }
            }, 300);
          } else {
            alert(data.message || 'حدث خطأ أثناء الحذف');
          }
        })
        .catch(function () {
          alert('حدث خطأ في الاتصال');
        });
    });
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      viewModal.classList.remove('show');
      closeFormModal();
    }
  });

});
