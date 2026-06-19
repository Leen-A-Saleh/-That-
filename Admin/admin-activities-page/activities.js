let _activities = typeof activitiesData !== 'undefined' ? [...activitiesData] : [];

let activeTab       = 'all';
let editingId       = null;
let pendingDeleteId = null;

const TYPE_CONFIG = {
  'تفاعل': { cls: 'badge-interactive', icon: 'fa-solid fa-play'        },
  'تمرين': { cls: 'badge-interactive', icon: 'fa-solid fa-heart-pulse' },
  'مهمة':  { cls: 'badge-interactive', icon: 'fa-solid fa-list-check'  },
};

const THUMB_IMG = {
  'تفاعل': '../../storage/admin-activities-page/GAME.PNG',
  'تمرين': '../../storage/admin-activities-page/EXERCISE.PNG',
  'مهمة':  '../../storage/admin-activities-page/TASK.PNG',
};

const LOADER_BASE = '../client-games-page/loader.php';

function buildPreviewUrl(activity) {
  if (!activity.game_key) return null;
  return `${LOADER_BASE}?key=${encodeURIComponent(activity.game_key)}`;
}

//  AJAX helper

async function postAction(data) {
  const res = await fetch('activities.php', {
    method: 'POST',
    body: new URLSearchParams(data),
  });
  return res.json();
}

//  FILTER

function getFiltered() {
  if (activeTab === 'all') return _activities;
  return _activities.filter((a) => a.cat === activeTab);
}


//  RENDER — Stats

function renderStats() {
  const total     = _activities.length;
  const views     = _activities.reduce((s, a) => s + a.views, 0);
  const active    = _activities.filter((a) => a.status === 'active').length;

  document.getElementById('statTotal').textContent      = total;
  document.getElementById('statViews').textContent      = views.toLocaleString('en-US');
  document.getElementById('statActive').textContent     = active;
}


//  RENDER — Category tabs

function renderTabs() {
  const cats      = ['all', ...new Set(_activities.map((a) => a.cat))];
  const container = document.getElementById('tabsContainer');

  container.innerHTML = cats
    .map((cat) => {
      const count = cat === 'all'
        ? _activities.length
        : _activities.filter((a) => a.cat === cat).length;
      const label = cat === 'all' ? 'الكل' : cat;
      return `
        <button class="tab-btn${activeTab === cat ? ' active' : ''}" data-cat="${cat}">
          ${label}
          <span class="tab-count">${count}</span>
        </button>`;
    })
    .join('');

  container.querySelectorAll('.tab-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      activeTab = btn.dataset.cat;
      renderTabs();
      renderGrid();
    });
  });
}


//  RENDER — Cards grid

function renderGrid() {
  const list = getFiltered();
  const grid = document.getElementById('actsGrid');

  if (!list.length) {
    grid.innerHTML = `
      <div class="empty-acts">
        <i class="fa-solid fa-gamepad"></i>
        <p>لا توجد أنشطة في هذه الفئة</p>
      </div>`;
    return;
  }

  grid.innerHTML = list
    .map((a) => {
      const typeCfg   = TYPE_CONFIG[a.type] || TYPE_CONFIG['تفاعل'];
      const thumbSrc  = THUMB_IMG[a.type]  || THUMB_IMG['تفاعل'];
      const isActive  = a.status === 'active';

      // Description shown in place of raw category label on the card body
      const descText = a.description
        ? `<div class="act-card-cat">${a.description}</div>`
        : `<div class="act-card-cat">${a.cat}</div>`;   // fallback to category if no description

      return `
        <div class="act-card" data-id="${a.id}">
          <div class="act-card-thumb">
            <img src="${thumbSrc}" alt="${a.type}" />
          </div>
          <div class="act-card-body">

            <div class="act-card-title-row">
              <span class="act-card-title">${a.title}</span>
              <div class="toggle-wrap">
                <input
                  type="checkbox"
                  class="toggle-input"
                  id="toggle-${a.id}"
                  ${isActive ? 'checked' : ''}
                  data-id="${a.id}"
                />
                <label class="toggle-label" for="toggle-${a.id}"></label>
              </div>
            </div>

            <div class="act-card-badges">
              <span class="type-badge ${typeCfg.cls}">
                <i class="${typeCfg.icon}" style="font-size:10px;"></i>
                ${a.type}
              </span>
            </div>

            ${descText}

            ${a.duration
              ? `<div class="act-card-duration">
                   <i class="fa-regular fa-clock"></i>${a.duration}
                 </div>`
              : ''}

            <hr class="act-card-divider" />

            <div class="act-card-stats">
              <div class="act-stat-item">
                <span class="act-stat-num">${a.views.toLocaleString('ar-EG')}</span>
                <span class="act-stat-label">المشاهدات</span>
              </div>
            </div>

            <div class="act-card-actions">
              <button class="act-action-btn act-btn-delete"
                      data-action="delete" data-id="${a.id}" title="حذف">
                <i class="fa-regular fa-trash-can"></i>
              </button>
              <button class="act-action-btn act-btn-edit"
                      data-action="edit" data-id="${a.id}" title="تعديل">
                <i class="fa-regular fa-pen-to-square"></i>
              </button>
              <button class="preview-btn"
                      data-action="preview" data-id="${a.id}">
                <i class="fa-regular fa-eye"></i>
                معاينة
              </button>
            </div>

            <div class="act-card-date">تم الرفع: ${a.date}</div>
          </div>
        </div>`;
    })
    .join('');

  // ── Toggle switch — optimistic UI + fire-and-forget POST ──
  grid.querySelectorAll('.toggle-input').forEach((toggle) => {
    toggle.addEventListener('change', async () => {
      const id     = parseInt(toggle.dataset.id, 10);
      const status = toggle.checked ? 'active' : 'inactive';

      const item = _activities.find((a) => a.id === id);
      if (item) item.status = status;
      renderStats();
      renderTabs();

      try {
        const result = await postAction({ action: 'toggle', id, status });
        if (!result.success) throw new Error(result.error);
      } catch (err) {
        // Revert on failure
        if (item) item.status = status === 'active' ? 'inactive' : 'active';
        toggle.checked = !toggle.checked;
        renderStats();
        renderTabs();
        showErrorAlert(err.message || 'فشل تحديث الحالة');
      }
    });
  });

  // ── Action buttons ──
  grid.querySelectorAll('[data-action]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const action = btn.dataset.action;
      const id     = parseInt(btn.dataset.id, 10);
      if (action === 'delete')  openDeleteModal(id);
      if (action === 'edit')    openEditModal(id);
      if (action === 'preview') handlePreview(id);
    });
  });
}


function renderAll() {
  renderStats();
  renderTabs();
  renderGrid();
}


function handlePreview(id) {
  const a = _activities.find((x) => x.id === id);
  if (!a) return;

  if (a.link) {
    window.open(a.link, '_blank');
  } else {
    showInfoToast('لا يوجد رابط لهذا النشاط بعد.');
  }
}

//  ADD / EDIT modal

function openAddModal() {
  editingId = null;
  document.getElementById('modalTitle').textContent = 'رفع نشاط جديد';
  document.getElementById('fieldTitle').value    = '';
  document.getElementById('fieldDesc').value     = '';
  const catSelect = document.getElementById('fieldCat');
  if (catSelect.options.length > 0) catSelect.selectedIndex = 0;
  document.getElementById('fieldType').value     = 'تفاعل';
  document.getElementById('fieldDurationMin').value = '';
  document.getElementById('fieldStatus').value   = 'active';
  clearFieldErrors();
  document.getElementById('modalOverlay').classList.add('open');
}

function openEditModal(id) {
  const a = _activities.find((x) => x.id === id);
  if (!a) return;

  editingId = id;
  document.getElementById('modalTitle').textContent = 'تعديل النشاط';
  document.getElementById('fieldTitle').value    = a.title;
  document.getElementById('fieldDesc').value     = a.description  || '';
  document.getElementById('fieldCat').value      = a.cat;
  document.getElementById('fieldType').value     = a.type;
  document.getElementById('fieldDurationMin').value = a.duration      || '';
  document.getElementById('fieldStatus').value   = a.status;
  clearFieldErrors();
  document.getElementById('modalOverlay').classList.add('open');
}

function closeModal() {
  document.getElementById('modalOverlay').classList.remove('open');
  editingId = null;
  clearFieldErrors();
}

//  VALIDATION helpers

function setFieldError(fieldId, msg) {
  const input = document.getElementById(fieldId);
  input.classList.add('input-error');
  let err = input.parentElement.querySelector('.field-error-msg');
  if (!err) {
    err = document.createElement('span');
    err.className = 'field-error-msg';
    input.parentElement.appendChild(err);
  }
  err.textContent = msg;
}

function clearFieldErrors() {
  document.querySelectorAll('.input-error').forEach((el) => el.classList.remove('input-error'));
  document.querySelectorAll('.field-error-msg').forEach((el) => el.remove());
}

//  SAVE (add or edit) — POST then reload

async function saveActivity() {
  clearFieldErrors();

  const title       = document.getElementById('fieldTitle').value.trim();
  const description = document.getElementById('fieldDesc').value.trim();
  const cat         = document.getElementById('fieldCat').value;
  const type        = document.getElementById('fieldType').value;
  const duration_min = document.getElementById('fieldDurationMin').value.trim();
  const status      = document.getElementById('fieldStatus').value;

  let hasError = false;
  if (!title) { setFieldError('fieldTitle', 'هذا الحقل مطلوب'); hasError = true; }
  if (hasError) return;

  const payload = editingId !== null
    ? { action: 'edit', id: editingId, title, description, cat, type, duration_min, status }
    : { action: 'add',                  title, description, cat, type, duration_min, status };

  const saveBtn = document.getElementById('saveActivityBtn');
  saveBtn.disabled = true;

  try {
    const result = await postAction(payload);
    if (!result.success) throw new Error(result.error);
    closeModal();
    setTimeout(() => window.location.reload(), 600);
  } catch (err) {
    saveBtn.disabled = false;
    showErrorAlert(err.message || 'حدث خطأ، يرجى المحاولة مجدداً');
  }
}

//  DELETE modal

function openDeleteModal(id) {
  pendingDeleteId = id;
  const a = _activities.find((x) => x.id === id);
  document.getElementById('deleteActivityName').textContent = a ? a.title : '';
  document.getElementById('deleteModal').classList.add('open');
}

function closeDeleteModal() {
  document.getElementById('deleteModal').classList.remove('open');
  pendingDeleteId = null;
}

async function confirmDelete() {
  if (pendingDeleteId === null) return;

  const confirmBtn = document.getElementById('confirmDeleteBtn');
  confirmBtn.disabled = true;

  try {
    const result = await postAction({ action: 'delete', id: pendingDeleteId });
    if (!result.success) throw new Error(result.error);
    closeDeleteModal();
    setTimeout(() => window.location.reload(), 600);
  } catch (err) {
    confirmBtn.disabled = false;
    showErrorAlert(err.message || 'حدث خطأ أثناء الحذف');
  }
}

//  BOOT

document.addEventListener('DOMContentLoaded', () => {
  renderAll();

  document.getElementById('openModalBtn')   .addEventListener('click', openAddModal);
  document.getElementById('closeModalBtn')  .addEventListener('click', closeModal);
  document.getElementById('cancelModalBtn') .addEventListener('click', closeModal);
  document.getElementById('saveActivityBtn').addEventListener('click', saveActivity);

  document.getElementById('modalOverlay').addEventListener('click', (e) => {
    if (e.target === document.getElementById('modalOverlay')) closeModal();
  });

  document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);
  document.getElementById('cancelDeleteBtn') .addEventListener('click', closeDeleteModal);
  document.getElementById('deleteModal')     .addEventListener('click', (e) => {
    if (e.target === document.getElementById('deleteModal')) closeDeleteModal();
  });

  const menuBtn        = document.querySelector('.menu-btn');
  const sidebar        = document.querySelector('.sidebar');
  const sidebarOverlay = document.querySelector('.sidebar-overlay');

  if (menuBtn && sidebar && sidebarOverlay) {
    menuBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      sidebarOverlay.classList.toggle('active');
    });
    sidebarOverlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      sidebarOverlay.classList.remove('active');
    });
  }
});