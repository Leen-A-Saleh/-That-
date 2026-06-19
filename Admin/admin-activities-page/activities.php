<?php

declare(strict_types=1);

require_once __DIR__ . '/../partials/require-admin.php';
require_once __DIR__ . '/activities-database.php';


//  AJAX / POST handler — must come before any HTML output

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $action = $_POST['action'];

        if ($action === 'add') {
            $id = activities_add(
                trim($_POST['title']        ?? ''),
                trim($_POST['description']  ?? ''),
                trim($_POST['cat']          ?? ''),
                trim($_POST['type']         ?? 'تفاعل'),
                trim($_POST['duration_min'] ?? ''),
                $_POST['status']            ?? 'active'
            );
            echo json_encode(['success' => true, 'id' => $id]);

        } elseif ($action === 'edit') {
            activities_update(
                (int)  ($_POST['id']            ?? 0),
                trim($_POST['title']            ?? ''),
                trim($_POST['description']      ?? ''),
                trim($_POST['cat']              ?? ''),
                trim($_POST['type']             ?? 'تفاعل'),
                trim($_POST['duration_min']     ?? ''),
                $_POST['status']                ?? 'active'
            );
            echo json_encode(['success' => true]);

        } elseif ($action === 'toggle') {
            // Status toggle from the card switch — no page reload needed
            activities_toggleStatus(
                (int) ($_POST['id']     ?? 0),
                $_POST['status']        ?? 'inactive'
            );
            echo json_encode(['success' => true]);

        } elseif ($action === 'delete') {
            activities_delete((int) ($_POST['id'] ?? 0));
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


//  Page data — fetched once, injected into JS

$stats          = activities_getStats();
$activitiesData = activities_getAll();

$categories = activities_getCategories();

?>
<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>الأنشطة والألعاب</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />
    <link rel="icon" type="image/png" sizes="32x32" href="../images/Silver.png" />
    <link rel="shortcut icon" sizes="10x10" href="../images/Silver.png" />
    <link rel="stylesheet" href="../admin-dashboard-page/admin-dashboard.css" />
    <link rel="stylesheet" href="./activities.css" />
  </head>

  <body>
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main-wrapper">
      <header class="navbar">
        <div class="menu-btn"><i class="fa fa-bars"></i></div>
        <div class="nav-title">الأنشطة والألعاب</div>
        <div class="nav-right">
          <button class="status-btn">
            <span class="status-dot"></span>متصل
          </button>
        </div>
      </header>

      <main class="page-content">
        <div class="acts-container">

          <!-- ── Page header ── -->
          <div class="acts-page-header">
            <div class="acts-page-title">
              <h1>الأنشطة والألعاب العلاجية</h1>
              <p>إدارة المحتوى التفاعلي والتعليمي</p>
            </div>
            <button class="upload-btn" id="openModalBtn">
              <img src="../images/Uploads.svg" alt="" />
              رفع نشاط جديد
            </button>
          </div>

          <!-- ── Stats cards — values rendered by PHP, kept fresh on reload ── -->
          <div class="acts-stats-grid">
            <div class="acts-stat-card">
              <div class="acts-stat-label">إجمالي الأنشطة</div>
              <div class="acts-stat-value" id="statTotal"><?= $stats['total'] ?></div>
            </div>
            <div class="acts-stat-card">
              <div class="acts-stat-label">إجمالي المشاهدات</div>
              <div class="acts-stat-value teal" id="statViews">
                <?= number_format($stats['views']) ?>
              </div>
            </div>
            <div class="acts-stat-card">
              <div class="acts-stat-label">مشاركات الألعاب</div>
              <div class="acts-stat-value teal" id="statGamePlays">
                <?= number_format($stats['gamePlays']) ?>
              </div>
            </div>
            <div class="acts-stat-card">
              <div class="acts-stat-label">الأنشطة النشطة</div>
              <div class="acts-stat-value" id="statActive"><?= $stats['active'] ?></div>
            </div>
          </div>

          <!-- ── Category tabs ── -->
          <div class="acts-tabs-wrap">
            <div class="acts-tabs" id="tabsContainer"></div>
          </div>

          <!-- ── Activity cards grid ── -->
          <div class="acts-grid" id="actsGrid"></div>

          <!-- ── Upload instructions ── -->
          <div class="upload-instructions">
            <div class="upload-instructions-title">
              <img src="../images/Upload.svg" alt="" />
              إرشادات الرفع
            </div>
            <ul>
              <li>الفيديوهات: MP4, MOV (الحد الأقصى 100 ميجابايت)</li>
              <li>الملفات: PDF (الحد الأقصى 10 ميجابايت)</li>
              <li>الصور: JPG, PNG (الحد الأقصى 5 ميجابايت)</li>
              <li>الصوتيات: MP3, WAV (الحد الأقصى 50 ميجابايت)</li>
              <li>يجب أن يكون المحتوى ملائماً ومفيداً من الناحية العلاجية</li>
            </ul>
          </div>

        </div><!-- /.acts-container -->
      </main>

      <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
    </div><!-- /.main-wrapper -->


         <!-- Add / Edit modal -->
    <div class="modal-overlay" id="modalOverlay">
      <div class="modal-box">
        <div class="modal-head">
          <h2 id="modalTitle">رفع نشاط جديد</h2>
          <button class="modal-close" id="closeModalBtn">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <!-- Row 1: title + category -->
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">اسم النشاط *</label>
            <input type="text" class="form-input" id="fieldTitle" placeholder="اسم النشاط" />
          </div>
          <div class="form-group">
            <label class="form-label">الفئة *</label>
            <select class="form-input" id="fieldCat">
              <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat, ENT_QUOTES) ?>"><?= htmlspecialchars($cat) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Row 2: type + duration -->
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">نوع النشاط *</label>
            <select class="form-input" id="fieldType">
              <!-- Values are Arabic UI labels; JS maps them to DB enums -->
              <option value="تفاعل">تفاعل (GAME)</option>
              <option value="تمرين">تمرين (EXERCISE)</option>
              <option value="مهمة">مهمة (TASK)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">المدة</label>
            <input
              type="text"
              class="form-input"
              id="fieldDurationMin"
              placeholder="5 دقائق"
            />
          </div>
        </div>

        <!-- Description -->
        <div class="form-group">
          <label class="form-label">وصف النشاط</label>
          <textarea
            class="form-input form-textarea"
            id="fieldDesc"
            placeholder="وصف مختصر للنشاط..."
          ></textarea>
        </div>

        <!-- Status -->
        <div class="form-group">
          <label class="form-label">الحالة</label>
          <select class="form-input" id="fieldStatus">
            <option value="active">نشط</option>
            <option value="inactive">غير نشط</option>
          </select>
        </div>

        <div class="modal-footer">
          <button class="btn-cancel" id="cancelModalBtn">إلغاء</button>
          <button class="btn-save"   id="saveActivityBtn">حفظ النشاط</button>
        </div>
      </div>
    </div>


         <!-- Delete confirmation modal -->
    <div class="modal-overlay" id="deleteModal">
      <div class="delete-box">
        <div class="delete-icon-wrap">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="delete-title">تأكيد الحذف</h3>
        <p class="delete-msg">
          هل أنت متأكد من حذف<br />
          <strong id="deleteActivityName"></strong>؟<br />
          <span>لا يمكن التراجع عن هذا الإجراء.</span>
        </p>
        <div class="delete-btns">
          <button class="btn-cancel-del"  id="cancelDeleteBtn">إلغاء</button>
          <button class="btn-confirm-del" id="confirmDeleteBtn">
            <i class="fa-solid fa-trash-can"></i> حذف
          </button>
        </div>
      </div>
    </div>


    <script>
      // Injected by PHP 
      const activitiesData = <?= json_encode($activitiesData, JSON_UNESCAPED_UNICODE) ?>;
      const categoriesData = <?= json_encode($categories,     JSON_UNESCAPED_UNICODE) ?>;
    </script>

    <script src="./activities.js"></script>
  </body>
</html>