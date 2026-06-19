<?php

declare(strict_types=1);

require_once __DIR__ . '/../partials/require-admin.php';
require_once __DIR__ . '/therapists-database.php';

// ─── AJAX Handler ─────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  header('Content-Type: application/json');
  $action = $_POST['action'] ?? '';

  if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    echo json_encode(['success' => deleteTherapist($id)]);
    exit;
  }

  if ($action === 'save') {
    $data = [
      'name' => trim($_POST['name'] ?? ''),
      'email' => trim($_POST['email'] ?? ''),
      'specialization' => trim($_POST['specialization']  ?? ''),
      'experience_years' => (int)   ($_POST['experience_years'] ?? 0),
      'rating' => (float) ($_POST['rating'] ?? 0),
      'status' => $_POST['status'] ?? 'AVAILABLE',
    ];

    $id = (int) ($_POST['id'] ?? 0);
    $ok = $id > 0 ? updateTherapist($id, $data) : addTherapist($data);
    echo json_encode(['success' => $ok]);
    exit;
  }

  echo json_encode(['success' => false, 'error' => 'unknown action']);
  exit;
}

// ─── Page Data ────────────────────────────────────────────────────────────────

$stats = getTherapistsStats();
$therapists = getAllTherapists();

?>
<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>الأخصائيين</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="icon" type="image/png" sizes="32x32" href="../images/Silver.png" />
  <link rel="shortcut icon" sizes="10x10" href="../images/Silver.png" />
  <link rel="stylesheet" href="../admin-dashboard-page/admin-dashboard.css" />
  <link rel="stylesheet" href="./therapist.css" />
</head>

<body>
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <div class="main-wrapper">
    <header class="navbar">
      <div class="menu-btn">
        <i class="fa fa-bars"></i>
      </div>
      <div class="nav-title">الأخصائيين</div>
      <div class="nav-right">
        <button class="status-btn">
          <span class="status-dot"></span>
          متصل
        </button>
      </div>
    </header>

    <main class="page-content">
      <div class="therapists-container">
        <!-- Stats -->
        <div class="stats-row">
          <div class="stat-card">
            <div class="stat-label">إجمالي الأخصائيين</div>
            <div class="stat-value" id="statTotal"><?= $stats['total'] ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">متاح الآن</div>
            <div class="stat-value" id="statAvailable"><?= $stats['available'] ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">متوسط التقييم</div>
            <div class="stat-value rating-value">
              <span id="statRating"><?= $stats['avg_rating'] ?></span>
              <img src="../images/Vector.svg" />
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-label">إجمالي الحالات</div>
            <div class="stat-value" id="statCases"><?= $stats['total_cases'] ?></div>
          </div>
        </div>
        <div class="top-bar">
          <div class="top-bar-filters">
            <div class="search-box">
              <i class="fa fa-search search-icon"></i>
              <input type="text" placeholder="البحث عن أخصائي..." id="searchInput" />
            </div>
          </div>
          <button class="add-btn" id="addTherapistBtn">
            <img src="../images/UserPlus.svg" />
            <span>إضافة أخصائي جديد</span>
          </button>
        </div>
        <div class="table-card">
          <div class="table-responsive">
            <table class="therapists-table">
              <thead>
                <tr>
                  <th>الأخصائي</th>
                  <th>التخصص</th>
                  <th>الخبرة</th>
                  <th>عدد الحالات</th>
                  <th>التقييم</th>
                  <th>الحالة</th>
                  <th>الإجراءات</th>
                </tr>
              </thead>
              <tbody id="therapistsTableBody"></tbody>
            </table>
          </div>

          <div class="mobile-cards" id="mobileCards"></div>

          <div class="pagination-bar">
            <div class="pagination-info" id="paginationInfo"></div>
            <div class="pagination-controls">
              <button class="page-btn prev-btn" id="prevBtn">السابق</button>
              <div class="page-numbers" id="pageNumbers"></div>
              <button class="page-btn next-btn" id="nextBtn">التالي</button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
  </div>

  <!-- Add / Edit Modal -->
  <div class="modal-overlay" id="modalOverlay">
    <div class="modal" id="therapistModal">
      <div class="modal-header">
        <h3 class="modal-title" id="modalTitle">إضافة أخصائي جديد</h3>
        <button class="modal-close" id="modalClose">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label>الاسم الكامل</label>
            <input type="text" id="fieldName" placeholder="د. الاسم الكامل" />
          </div>
          <div class="form-group">
            <label>البريد الإلكتروني</label>
            <input type="email" id="fieldEmail" placeholder="example@domain.com" />
          </div>
          <div class="form-group">
            <label>التخصص</label>
            <select id="fieldSpecialty">
              <option value="">اختر التخصص</option>
              <option value="علم النفس السريري">علم النفس السريري</option>
              <option value="الإرشاد النفسي">الإرشاد النفسي</option>
              <option value="علاج الإدمان">علاج الإدمان</option>
              <option value="علم نفس الأطفال">علم نفس الأطفال</option>
              <option value="العلاج الأسري">العلاج الأسري</option>
              <option value="علاج القلق والاكتئاب">علاج القلق والاكتئاب</option>
            </select>
          </div>
          <div class="form-group">
            <label>سنوات الخبرة</label>
            <input type="number" id="fieldExperience" placeholder="0" min="0" />
          </div>
          <div class="form-group">
            <label>الحالة</label>
            <!-- Values match DB: AVAILABLE / BUSY / ON_LEAVE -->
            <select id="fieldStatus">
              <option value="AVAILABLE">متاح</option>
              <option value="BUSY">مشغول</option>
              <option value="ON_LEAVE">في إجازة</option>
            </select>
          </div>
          <div class="form-group">
            <label>التقييم</label>
            <input type="number" id="fieldRating" placeholder="4.5" min="0" max="5" step="0.1" />
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-cancel" id="modalCancel">إلغاء</button>
        <button class="btn-save" id="modalSave">حفظ</button>
      </div>
    </div>
  </div>

  <!-- Delete Confirm Modal -->
  <div class="modal-overlay" id="confirmModal">
    <div class="confirm-modal-box">
      <div class="confirm-icon"><i class="fa fa-trash"></i></div>
      <h3>تأكيد الحذف</h3>
      <p>هل أنت متأكد من حذف هذا الأخصائي؟<br />لا يمكن التراجع عن هذا الإجراء.</p>
      <div class="confirm-actions">
        <button class="btn-confirm-delete" id="confirmDeleteBtn">حذف</button>
        <button class="btn-modal-cancel" id="cancelDeleteBtn">إلغاء</button>
      </div>
    </div>
  </div>

  <!-- View Modal -->
  <div class="view-modal-wrap" id="viewModal">
    <div class="view-modal-backdrop" id="viewModalBackdrop"></div>
    <div class="view-modal-box">
      <div class="view-modal-header">
        <div class="view-modal-avatar" id="vm-avatar"></div>
        <div class="view-modal-header-info">
          <h3 id="vm-name"></h3>
          <span><i class="fas fa-envelope"></i><span id="vm-email"></span></span>
        </div>
        <button class="view-modal-close" onclick="closeViewModal()">
          <i class="fas fa-xmark"></i>
        </button>
      </div>
      <div class="view-modal-body">
        <div class="view-modal-row">
          <span class="view-modal-label"><i class="fas fa-award"></i>التخصص</span>
          <span class="view-modal-val" id="vm-specialty"></span>
        </div>
        <div class="view-modal-row">
          <span class="view-modal-label"><i class="fas fa-briefcase"></i>سنوات الخبرة</span>
          <span class="view-modal-val" id="vm-experience"></span>
        </div>
        <div class="view-modal-row">
          <span class="view-modal-label"><i class="fas fa-folder-open"></i>عدد الحالات</span>
          <span class="view-modal-val" id="vm-cases"></span>
        </div>
        <div class="view-modal-row">
          <span class="view-modal-label"><i class="fas fa-star"></i>التقييم</span>
          <span class="view-modal-val" id="vm-rating"></span>
        </div>
        <div class="view-modal-row">
          <span class="view-modal-label"><i class="fas fa-circle-dot"></i>الحالة</span>
          <span class="view-modal-val" id="vm-status"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Inject DB data as a JS variable — therapist.js reads this -->
  <script>
    window.THERAPISTS_DATA = <?= json_encode($therapists, JSON_UNESCAPED_UNICODE) ?>;
  </script>
  <script src="./therapist.js"></script>
</body>

</html>
