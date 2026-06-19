<?php

declare(strict_types=1);

require_once __DIR__ . '/../partials/require-admin.php';
require_once __DIR__ . '/users-database.php';

// ─── AJAX Handler ─────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
  header('Content-Type: application/json; charset=utf-8');

  try {
    $action = $_POST['action'];

    if ($action === 'add') {
      $name   = trim($_POST['name']   ?? '');
      $email  = trim($_POST['email']  ?? '');
      $phone  = trim($_POST['phone']  ?? '');
      $status = $_POST['status'] ?? 'active';
      $id = users_add($name, $email, $phone, $status);
      echo json_encode(['success' => true, 'id' => $id]);
    } elseif ($action === 'edit') {
      $id     = (int)   ($_POST['id'] ?? 0);
      $name   = trim($_POST['name']   ?? '');
      $email  = trim($_POST['email']  ?? '');
      $phone  = trim($_POST['phone']  ?? '');
      $status = $_POST['status'] ?? 'active';
      users_update($id, $name, $email, $phone, $status);
      echo json_encode(['success' => true]);
    } elseif ($action === 'toggle') {
      $id = (int) ($_POST['id'] ?? 0);
      $newStatus = users_toggleStatus($id);
      echo json_encode(['success' => true, 'status' => $newStatus]);
    } elseif ($action === 'delete') {
      $id = (int) ($_POST['id'] ?? 0);
      users_delete($id);
      echo json_encode(['success' => true]);
    } else {
      throw new RuntimeException('إجراء غير معروف');
    }
  } catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
  }

  exit;
}

// ─── Page Data ────────────────────────────────────────────────────────────────

$stats = users_getStats();
$allUsers = users_getAll();

$usersForJs = array_map(function (array $u): array {
  return [
    'id'      => (int) $u['id'],
    'name'    => $u['name'],
    'email'   => $u['email'],
    'phone'   => $u['phone'],
    'regDate' => $u['reg_date'],
    'sessions' => (int) $u['sessions'],
    'status'  => ((int) $u['is_active'] === 1) ? 'active' : 'suspended',
    'avatar'  => mb_substr($u['name'], 0, 1, 'UTF-8'),
  ];
}, $allUsers);
?>
<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>المستخدمين</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap"
    rel="stylesheet" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="icon" type="image/png" sizes="32x32" href="../images/Silver.png" />
  <link rel="shortcut icon" sizes="10x10" href="../images/Silver.png" />
  <link rel="stylesheet" href="../admin-dashboard-page/admin-dashboard.css" />
  <link rel="stylesheet" href="./users.css" />
</head>

<body>
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <div class="main-wrapper">
    <header class="navbar">
      <div class="menu-btn"><i class="fa fa-bars"></i></div>
      <div class="nav-title">المستخدمين/المرضى</div>
      <div class="nav-right">
        <button class="status-btn"><span class="status-dot"></span>متصل</button>
      </div>
    </header>

    <main class="page-content">

      <!-- Stat cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-label">إجمالي المستخدمين</div>
          <div class="stat-value" id="statTotal"><?= $stats['total'] ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">المستخدمين النشطين</div>
          <div class="stat-value" id="statActive"><?= $stats['active'] ?></div>
        </div>
        <div class="stat-card suspended">
          <div class="stat-label">الحسابات الموقوفة</div>
          <div class="stat-value" id="statSuspended"><?= $stats['suspended'] ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">إجمالي الجلسات</div>
          <div class="stat-value" id="statSessions"><?= $stats['sessions'] ?></div>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="users-toolbar">
        <div class="search-box">
          <i class="fa fa-search search-icon"></i>
          <input type="text" id="searchInput" placeholder="البحث عن مستخدم..." />
        </div>
        <select class="filter-select" id="statusFilter">
          <option value="all">جميع الحالات</option>
          <option value="active">نشط</option>
          <option value="suspended">موقوف</option>
        </select>
        <button class="btn-export" id="exportBtn">
          <img src="../images/Download.svg" /> تصدير
        </button>
        <button class="btn-add-user" id="addUserBtn">
          <img src="../images/UserPlus.svg" /> إضافة مستخدم
        </button>
      </div>

      <!-- rows rendered by JS from the PHP data below -->
      <div class="users-table-wrapper">
        <table class="users-table">
          <thead>
            <tr>
              <th>الاسم</th>
              <th>البريد الإلكتروني</th>
              <th>رقم الجوال</th>
              <th>تاريخ التسجيل</th>
              <th>عدد الجلسات</th>
              <th>حالة الحساب</th>
              <th>الإجراءات</th>
            </tr>
          </thead>
          <tbody id="usersTableBody"></tbody>
        </table>
        <div class="pagination-wrapper">
          <div class="pagination-info" id="paginationInfo"></div>
          <div class="pagination-btns" id="paginationBtns"></div>
        </div>
      </div>

      <!-- Add / Edit modal -->
      <div class="modal-overlay" id="userModal">
        <div class="modal-box">
          <h2><span></span><span id="modalTitle">إضافة مستخدم جديد</span></h2>
          <div class="modal-form-grid">
            <div class="form-group">
              <label for="fieldFirstName">الاسم الأول</label>
              <input type="text" id="fieldFirstName" placeholder="الاسم الأول" />
            </div>
            <div class="form-group">
              <label for="fieldLastName">اسم العائلة</label>
              <input type="text" id="fieldLastName" placeholder="اسم العائلة" />
            </div>
            <div class="form-group full-width">
              <label for="fieldEmail">البريد الإلكتروني</label>
              <input type="email" id="fieldEmail" placeholder="example@email.com" />
            </div>
            <div class="form-group">
              <label for="fieldPhone">رقم الجوال</label>
              <input type="tel" id="fieldPhone" placeholder="05xxxxxxxx" />
            </div>
            <div class="form-group">
              <label for="fieldStatus">الحالة</label>
              <select id="fieldStatus">
                <option value="active">نشط</option>
                <option value="suspended">موقوف</option>
              </select>
            </div>
          </div>
          <div class="modal-actions">
            <button class="btn-modal-save" id="saveUserBtn">حفظ</button>
            <button class="btn-modal-cancel" id="cancelModalBtn">إلغاء</button>
          </div>
        </div>
      </div>

      <!-- Delete confirm modal -->
      <div class="modal-overlay" id="confirmModal">
        <div class="confirm-modal-box">
          <div class="confirm-icon"><i class="fa fa-trash"></i></div>
          <h3>تأكيد الحذف</h3>
          <p>هل أنت متأكد من حذف هذا المستخدم؟<br />لا يمكن التراجع عن هذا الإجراء.</p>
          <div class="confirm-actions">
            <button class="btn-confirm-delete" id="confirmDeleteBtn">حذف</button>
            <button class="btn-modal-cancel" id="cancelDeleteBtn">إلغاء</button>
          </div>
        </div>
      </div>

      <!-- View user card -->
      <div id="userModalCard" class="user-modal">
        <div class="user-modal-backdrop" onclick="closeUserCard()"></div>
        <div class="user-modal-content">
          <div class="user-modal-header">
            <div class="user-modal-avatar" id="vm-avatar"></div>
            <div class="user-modal-header-info">
              <h3 id="vm-name"></h3>
              <span id="vm-status-badge"></span>
            </div>
            <button class="user-modal-close" onclick="closeUserCard()">
              <i class="fas fa-xmark"></i>
            </button>
          </div>
          <div class="user-modal-body">
            <div class="user-modal-row">
              <span class="user-modal-label"><i class="fas fa-envelope"></i>البريد الإلكتروني</span>
              <span class="user-modal-val" id="vm-email"></span>
            </div>
            <div class="user-modal-row">
              <span class="user-modal-label"><i class="fas fa-phone"></i>رقم الجوال</span>
              <span class="user-modal-val" id="vm-phone"></span>
            </div>
            <div class="user-modal-row">
              <span class="user-modal-label"><i class="fas fa-calendar-days"></i>تاريخ التسجيل</span>
              <span class="user-modal-val" id="vm-date"></span>
            </div>
            <div class="user-modal-row">
              <span class="user-modal-label"><i class="fas fa-layer-group"></i>عدد الجلسات</span>
              <span class="user-modal-val" id="vm-sessions"></span>
            </div>
          </div>
        </div>
      </div>

    </main>

    <div class="toast" id="toast"></div>
    <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
  </div>

  <!-- Pass PHP data to JS  -->
  <script>
    // JS uses this array for rendering, search, filter, pagination, and modals.
    const usersData = <?= json_encode($usersForJs, JSON_UNESCAPED_UNICODE) ?>;
  </script>

  <script src="./users.js"></script>
</body>

</html>