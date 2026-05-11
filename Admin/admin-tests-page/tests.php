<?php

declare(strict_types=1);

require_once __DIR__ . '/../partials/require-admin.php';
require_once __DIR__ . '/tests-database.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
  header('Content-Type: application/json; charset=utf-8');

  try {
    $action = $_POST['action'];

    if ($action === 'add') {
      $id = tests_add(
        trim($_POST['nameAr'] ?? ''),
        trim($_POST['nameEn'] ?? ''),
        trim($_POST['cat'] ?? ''),
        (int) ($_POST['questions'] ?? 0),
        $_POST['status'] ?? 'active',
        trim($_POST['description'] ?? '')
      );
      echo json_encode(['success' => true, 'id' => $id]);
    } elseif ($action === 'edit') {
      tests_update(
        (int) ($_POST['id'] ?? 0),
        trim($_POST['nameAr'] ?? ''),
        trim($_POST['nameEn'] ?? ''),
        trim($_POST['cat'] ?? ''),
        (int) ($_POST['questions'] ?? 0),
        $_POST['status'] ?? 'active',
        trim($_POST['description'] ?? '')
      );
      echo json_encode(['success' => true]);
    } elseif ($action === 'delete') {
      tests_delete((int) ($_POST['id'] ?? 0));
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

$stats = tests_getStats();
$monthly  = tests_getMonthly();
$catBars  = tests_getCategoryBars();
$allTests = tests_getAll();

$categories = array_unique(array_column($allTests, 'cat'));
sort($categories);
?>
<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>الاختبارات النفسية</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="icon" type="image/png" sizes="32x32" href="../images/Silver.png" />
  <link rel="shortcut icon" sizes="10x10" href="../images/Silver.png" />
  <link rel="stylesheet" href="../admin-dashboard-page/admin-dashboard.css" />
  <link rel="stylesheet" href="./tests.css" />
</head>

<body>
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <div class="main-wrapper">
    <header class="navbar">
      <div class="menu-btn"><i class="fa fa-bars"></i></div>
      <div class="nav-title">الاختبارات النفسية</div>
      <div class="nav-right">
        <button class="status-btn"><span class="status-dot"></span>متصل</button>
      </div>
    </header>

    <main class="page-content">
      <div class="tests-page-header">
        <div class="tests-page-title">
          <h1>الاختبارات النفسية</h1>
          <p>إدارة الاختبارات والمقاييس النفسية</p>
        </div>
        <button class="add-test-btn" id="openModalBtn">
          <i class="fa-solid fa-plus"></i>
          إضافة اختبار جديد
        </button>
      </div>

      <div class="tests-stats-grid">
        <div class="tests-stat-card">
          <div class="tests-stat-icon icon-teal"><img src="../images/Containers.svg" /></div>
          <div class="tests-stat-info">
            <div class="tests-stat-label">إجمالي الاختبارات</div>
            <div class="tests-stat-value" id="statTotal"><?= $stats['total_tests'] ?></div>
          </div>
        </div>
        <div class="tests-stat-card">
          <div class="tests-stat-icon icon-purple"><img src="../images/Containers (1).svg" /></div>
          <div class="tests-stat-info">
            <div class="tests-stat-label">إجمالي الإكمالات</div>
            <div class="tests-stat-value" id="statCompletions"><?= number_format($stats['completions']) ?></div>
          </div>
        </div>
        <div class="tests-stat-card">
          <div class="tests-stat-icon icon-orange"><img src="../images/Containers (2).svg" /></div>
          <div class="tests-stat-info">
            <div class="tests-stat-label">هذا الشهر</div>
            <div class="tests-stat-value" id="statMonth"><?= $stats['this_month'] ?></div>
          </div>
        </div>
        <div class="tests-stat-card">
          <div class="tests-stat-icon icon-green"><img src="../images/Containers (3).svg" /></div>
          <div class="tests-stat-info">
            <div class="tests-stat-label">متوسط يومي</div>
            <div class="tests-stat-value" id="statDaily"><?= $stats['daily_avg'] ?></div>
          </div>
        </div>
      </div>

      <!-- Charts row -->
      <div class="tests-charts-row">
        <div class="tests-chart-card">
          <h3>اتجاه الإكمالات الشهرية</h3>
          <div class="chart-wrap">
            <canvas id="monthlyChart"></canvas>
          </div>
        </div>
        <div class="tests-chart-card">
          <h3>الإكمالات حسب الفئة</h3>
          <div class="cat-list" id="catList"></div>
        </div>
      </div>

      <!-- Table -->
      <div class="tests-table-card">
        <div class="tests-table-header">
          <h3>قائمة الاختبارات</h3>
          <div class="tests-table-actions">
            <div class="search-wrap">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" class="search-input" id="searchInput" placeholder="ابحث عن اختبار..." />
            </div>
            <select class="filter-select" id="statusFilter">
              <option value="">كل الحالات</option>
              <option value="active">نشط</option>
              <option value="draft">مسودة</option>
            </select>
            <select class="filter-select" id="catFilter">
              <option value="">كل الفئات</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="table-scroll">
          <table>
            <thead>
              <tr>
                <th>اسم الاختبار</th>
                <th>الفئة</th>
                <th>عدد الأسئلة</th>
                <th>مرات الإكمال</th>
                <th>متوسط النتائج</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
              </tr>
            </thead>
            <tbody id="testsTableBody"></tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Add / Edit modal -->
    <div class="modal-overlay" id="modalOverlay">
      <div class="modal-box">
        <div class="modal-head">
          <h2 id="modalTitle">إضافة اختبار جديد</h2>
          <button class="modal-close" id="closeModalBtn"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">اسم الاختبار (عربي) *</label>
            <input type="text" class="form-input" id="fieldNameAr" placeholder="إختبار بيك للإكتئاب" />
            <div class="form-error" id="errorNameAr">هذا الحقل مطلوب</div>
          </div>
          <div class="form-group">
            <label class="form-label">اسم الاختبار (إنجليزي) *</label>
            <input type="text" class="form-input" id="fieldNameEn" placeholder="Beck Depression Inventory" />
            <div class="form-error" id="errorNameEn">هذا الحقل مطلوب</div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">الفئة *</label>
            <select class="form-input" id="fieldCat">
              <option value="الاكتئاب">الاكتئاب</option>
              <option value="القلق والاكتئاب">القلق والاكتئاب</option>
              <option value="الأطفال">الأطفال</option>
              <option value="فرط الحركة">فرط الحركة</option>
              <option value="الضغط النفسي">الضغط النفسي</option>
              <option value="القلق الاجتماعي">القلق الاجتماعي</option>
            </select>
            <div class="form-error" id="errorCat">هذا الحقل مطلوب</div>
          </div>
          <div class="form-group">
            <label class="form-label">عدد الأسئلة *</label>
            <input type="number" class="form-input" id="fieldQuestions" placeholder="21" min="1" />
            <div class="form-error" id="errorQuestions">هذا الحقل مطلوب</div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">الحالة</label>
            <select class="form-input" id="fieldStatus">
              <option value="active">نشط</option>
              <option value="draft">مسودة</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">وصف الاختبار</label>
          <textarea class="form-input form-textarea" id="fieldDesc" placeholder="وصف مختصر للاختبار..."></textarea>
          <div class="form-error" id="errorDesc">هذا الحقل مطلوب</div>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" id="cancelModalBtn">إلغاء</button>
          <button class="btn-save" id="saveTestBtn">حفظ الاختبار</button>
        </div>
      </div>
    </div>

    <!-- View modal -->
    <div class="modal-overlay" id="viewModal">
      <div class="modal-box">
        <div class="modal-head">
          <h2>تفاصيل الاختبار</h2>
          <button class="modal-close" id="closeViewBtn"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="view-modal-top">
          <div class="view-modal-title-block">
            <div class="view-modal-name-ar" id="viewNameAr"></div>
            <div class="view-modal-name-en" id="viewNameEn"></div>
          </div>
          <span class="status-badge" id="viewStatus"></span>
        </div>
        <div style="margin-bottom: 16px">
          <span class="cat-badge" id="viewCatBadge"></span>
        </div>
        <div class="view-modal-stats">
          <div class="view-stat-box">
            <div class="view-stat-value" id="viewStatQuestions">—</div>
            <div class="view-stat-label">عدد الأسئلة</div>
          </div>
          <div class="view-stat-box">
            <div class="view-stat-value" id="viewStatCompletions">—</div>
            <div class="view-stat-label">مرات الإكمال</div>
          </div>
          <div class="view-stat-box">
            <div class="view-stat-value" id="viewStatAvg">—</div>
            <div class="view-stat-label">متوسط النتيجة</div>
          </div>
        </div>
        <div class="view-modal-desc-block">
          <div class="view-modal-desc-label">وصف الاختبار</div>
          <div class="view-modal-desc-text" id="viewDesc"></div>
        </div>
        <div class="view-modal-footer">
          <button class="btn-close-view" onclick="document.getElementById('viewModal').classList.remove('open')">إغلاق</button>
        </div>
      </div>
    </div>

    <!-- Delete confirm modal -->
    <div class="modal-overlay" id="deleteModal">
      <div class="modal-box">
        <div class="delete-icon-wrap"><i class="fa-solid fa-trash-can"></i></div>
        <div class="delete-modal-title">تأكيد الحذف</div>
        <div class="delete-modal-msg">
          هل أنت متأكد من حذف اختبار<br />
          <strong id="deleteTestName"></strong>؟<br />
          <span style="font-size:13px;color:#9ca3af">لا يمكن التراجع عن هذا الإجراء.</span>
        </div>
        <div class="delete-modal-btns">
          <button class="btn-cancel-delete" id="cancelDeleteBtn">إلغاء</button>
          <button class="btn-confirm-delete" id="confirmDeleteBtn">
            <i class="fa-solid fa-trash-can" style="margin-left:6px"></i>حذف
          </button>
        </div>
      </div>
    </div>

    <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
  </div>
  <!-- PHP data injected for JS -->
  <script>
    const testsData = <?= json_encode($allTests,  JSON_UNESCAPED_UNICODE) ?>;
    const monthlyData = <?= json_encode($monthly,   JSON_UNESCAPED_UNICODE) ?>;
    const catData = <?= json_encode($catBars,   JSON_UNESCAPED_UNICODE) ?>;
  </script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
  <script src="./tests.js"></script>
</body>

</html>