<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'THERAPIST') {
    header("Location: /homepage/index.php");
    exit;
}

require_once __DIR__ . '/../../connection.php';

$user_id = $_SESSION['user_id'];
$therapist_id = $user_id;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    header('Content-Type: application/json');
    $aid = (int) ($_POST['activity_id'] ?? 0);
    if ($aid <= 0) {
        echo json_encode(['success' => false, 'message' => 'معرف النشاط غير صالح']);
        exit;
    }

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("
            DELETE sub FROM activity_submissions sub
            INNER JOIN case_activities ca ON ca.id = sub.case_activity_id
            WHERE ca.activity_id = ?
        ");
        $stmt->bind_param("i", $aid);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM case_activities WHERE activity_id = ?");
        $stmt->bind_param("i", $aid);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM activities WHERE activity_id = ? AND created_by = ?");
        $stmt->bind_param("ii", $aid, $user_id);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        if ($deleted) {
            $conn->commit();
            echo json_encode(['success' => true]);
        } else {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'النشاط غير موجود']);
        }
    } catch (Throwable $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'تعذّر حذف النشاط']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['add', 'edit'])) {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $activity_type = $_POST['activity_type'] ?? 'TASK';
    $duration    = (int) ($_POST['duration_min'] ?? 0);
    $difficulty  = $_POST['difficulty'] ?? 'EASY';
    $status      = $_POST['status'] ?? 'ACTIVE';

    if ($title !== '') {
        if ($_POST['action'] === 'add') {
            $stmt = $conn->prepare("INSERT INTO activities (title, description, category, activity_type, duration_min, difficulty, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssissi", $title, $description, $category, $activity_type, $duration, $difficulty, $status, $user_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $aid = (int) ($_POST['activity_id'] ?? 0);
            if ($aid > 0) {
                $stmt = $conn->prepare("UPDATE activities SET title = ?, description = ?, category = ?, activity_type = ?, duration_min = ?, difficulty = ?, status = ? WHERE activity_id = ? AND created_by = ?");
                $stmt->bind_param("ssssissii", $title, $description, $category, $activity_type, $duration, $difficulty, $status, $aid, $user_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    header("Location: index.php" . (isset($_GET['search']) ? '?search=' . urlencode($_GET['search']) : ''));
    exit;
}

$current_search = trim($_GET['search'] ?? '');

$sql = "
    SELECT a.activity_id, a.title, a.description, a.category, a.activity_type,
           a.duration_min, a.difficulty, a.status, a.created_at,
           COUNT(DISTINCT ca.id) AS assigned_count,
           COUNT(DISTINCT CASE WHEN sub.status = 'COMPLETED' THEN sub.submission_id END) AS completed_submissions,
           COUNT(DISTINCT sub.submission_id) AS total_submissions
    FROM activities a
    LEFT JOIN case_activities ca ON ca.activity_id = a.activity_id
    LEFT JOIN activity_submissions sub ON sub.case_activity_id = ca.id
    WHERE a.created_by = ?
";
$types = "i";
$params = [$therapist_id];

if ($current_search !== '') {
    $sql .= " AND a.title LIKE ?";
    $types .= "s";
    $params[] = "%$current_search%";
}

$sql .= " GROUP BY a.activity_id ORDER BY a.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_count = count($activities);
$active_count = 0;
$total_assigned = 0;
$total_completion = 0;
$completion_items = 0;

foreach ($activities as $act) {
    if ($act['status'] === 'ACTIVE') $active_count++;
    $total_assigned += (int) $act['assigned_count'];
    if ((int) $act['total_submissions'] > 0) {
        $total_completion += round(((int) $act['completed_submissions'] / (int) $act['total_submissions']) * 100);
        $completion_items++;
    }
}
$avg_completion = $completion_items > 0 ? round($total_completion / $completion_items) : 0;

$difficulty_labels = ['EASY' => 'سهل', 'MEDIUM' => 'متوسط', 'HARD' => 'صعب'];
$difficulty_classes = ['EASY' => 'easy', 'MEDIUM' => 'medium', 'HARD' => 'hard'];
$status_labels = ['ACTIVE' => 'نشط', 'DRAFT' => 'مسودة'];
$status_classes = ['ACTIVE' => 'active', 'DRAFT' => 'completed'];
$type_labels = ['TASK' => 'مهمة', 'GAME' => 'لعبة', 'EXERCISE' => 'تمرين'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <title>إدارة الأنشطة - ذات للاستشارات النفسية</title>
  <link rel="icon" type="./img/Silver.png" href="img/Silver.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

  <link rel="stylesheet" href="../thirapist.css">
  <link rel="stylesheet" href="../total.css"/>
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <div class="app">

    <?php $activePage = 'activities'; ?>
    <?php include "../layouts/sidebar.php"; ?>

    <main class="main">
      <header class="main-header">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="فتح القائمة">
          <i class="fa-solid fa-bars"></i>
        </button>
        <h1 class="page-title">إدارة الأنشطة</h1>
        <button class="status-btn">
          <span class="status-dot"></span>
          متصل
        </button>
      </header>

      <!-- Stats -->
      <section class="stats-row">
        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">إجمالي الأنشطة</div>
            <div class="stat-value"><?= $total_count ?></div>
          </div>
          <div class="stat-icon-blue">
            <i class="fa-solid fa-wave-square"></i>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">الأنشطة النشطة</div>
            <div class="stat-value"><?= $active_count ?></div>
          </div>
          <div class="stat-icon-green">
            <i class="fa-solid fa-wave-square"></i>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">إجمالي التكليفات</div>
            <div class="stat-value"><?= $total_assigned ?></div>
          </div>
          <div class="stat-icon-purple">
            <i class="fa-solid fa-user-group"></i>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">متوسط الإنجاز</div>
            <div class="stat-value"><?= $avg_completion ?>%</div>
          </div>
          <div class="stat-icon-teal">
            <i class="fa-regular fa-calendar"></i>
          </div>
        </div>
      </section>

      <!-- Toolbar -->
      <section class="add-activity-section">
        <div class="toolbar-container">
          <div class="search-container">
            <form id="searchForm" method="GET" class="search-box">
              <i class="fa-solid fa-magnifying-glass search-icon"></i>
              <input type="text" class="search-input" name="search" placeholder="البحث عن نشاط..." id="searchInput" value="<?= htmlspecialchars($current_search) ?>">
            </form>
          </div>
          <button class="add-btn" id="addActivityBtn">
            <i class="fa-solid fa-plus"></i>
            إضافة نشاط جديد
          </button>
        </div>
      </section>

      <!-- Activities table -->
      <section class="activities-section">
        <div class="table-container">
          <?php if (empty($activities)): ?>
            <p class="empty-state">
              <i class="fa-solid fa-wave-square"></i>
              لا توجد أنشطة حالياً
            </p>
          <?php else: ?>
          <table class="activities-table">
            <thead>
              <tr>
                <th>عنوان النشاط</th>
                <th>الفئة</th>
                <th>المدة</th>
                <th>الصعوبة</th>
                <th>المكلفين</th>
                <th>نسبة الإنجاز</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($activities as $act):
                  $completion = (int) $act['total_submissions'] > 0
                      ? round(((int) $act['completed_submissions'] / (int) $act['total_submissions']) * 100)
                      : 0;
              ?>
              <tr data-id="<?= $act['activity_id'] ?>">
                <td class="activity-title">
                  <?= htmlspecialchars($act['title']) ?>
                  <?php if ($act['description']): ?>
                  <p class="activity-text"><?= htmlspecialchars(mb_substr($act['description'], 0, 80, 'UTF-8')) ?><?= mb_strlen($act['description']) > 80 ? '...' : '' ?></p>
                  <?php endif; ?>
                </td>
                <td><span class="type rating"><?= htmlspecialchars($act['category'] ?: $type_labels[$act['activity_type']] ?? $act['activity_type']) ?></span></td>
                <td><?= (int) $act['duration_min'] ?> دقيقة</td>
                <td><span class="type <?= $difficulty_classes[$act['difficulty']] ?? 'easy' ?>"><?= $difficulty_labels[$act['difficulty']] ?? $act['difficulty'] ?></span></td>
                <td><?= (int) $act['assigned_count'] ?> مريض</td>
                <td class="progress-cell">
                  <div class="progress-bar-bg">
                    <div class="progress-bar" style="width: <?= $completion ?>%"></div>
                  </div>
                  <span class="progress-text"><?= $completion ?>%</span>
                </td>
                <td><span class="status <?= $status_classes[$act['status']] ?? 'active' ?>"><?= $status_labels[$act['status']] ?? $act['status'] ?></span></td>
                <td class="actions-cell">
                  <button class="action-icon btn-view"
                    data-id="<?= $act['activity_id'] ?>"
                    data-title="<?= htmlspecialchars($act['title']) ?>"
                    data-description="<?= htmlspecialchars($act['description'] ?? '') ?>"
                    data-category="<?= htmlspecialchars($act['category'] ?? '') ?>"
                    data-type="<?= htmlspecialchars($act['activity_type']) ?>"
                    data-type-label="<?= $type_labels[$act['activity_type']] ?? $act['activity_type'] ?>"
                    data-duration="<?= (int) $act['duration_min'] ?>"
                    data-difficulty="<?= $act['difficulty'] ?>"
                    data-difficulty-label="<?= $difficulty_labels[$act['difficulty']] ?? $act['difficulty'] ?>"
                    data-status="<?= $act['status'] ?>"
                    data-status-label="<?= $status_labels[$act['status']] ?? $act['status'] ?>"
                    data-assigned="<?= (int) $act['assigned_count'] ?>"
                    data-completion="<?= $completion ?>"
                    data-created="<?= date('Y-m-d', strtotime($act['created_at'])) ?>"
                    title="عرض">
                    <i class="fa-solid fa-eye" style="color: #155DFC;"></i>
                  </button>
                  <button class="action-icon btn-edit"
                    data-id="<?= $act['activity_id'] ?>"
                    data-title="<?= htmlspecialchars($act['title']) ?>"
                    data-description="<?= htmlspecialchars($act['description'] ?? '') ?>"
                    data-category="<?= htmlspecialchars($act['category'] ?? '') ?>"
                    data-type="<?= htmlspecialchars($act['activity_type']) ?>"
                    data-duration="<?= (int) $act['duration_min'] ?>"
                    data-difficulty="<?= $act['difficulty'] ?>"
                    data-status="<?= $act['status'] ?>"
                    title="تعديل">
                    <i class="fa-solid fa-pen-to-square" style="color:#30B7C4;"></i>
                  </button>
                  <button class="action-icon btn-delete" data-id="<?= $act['activity_id'] ?>" data-title="<?= htmlspecialchars($act['title']) ?>" title="حذف">
                    <i class="fa-solid fa-trash" style="color: #E7000B;"></i>
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </section>

      <?php include "../layouts/footer.php"; ?>
    </main>
  </div>

  <!-- View Modal -->
  <div class="modal-overlay" id="viewModal">
    <div class="modal-card">
      <header class="modal-header">
        <h2 class="modal-title">تفاصيل النشاط</h2>
        <button class="modal-close" id="viewModalClose">&times;</button>
      </header>
      <div class="modal-body">
        <h3 class="view-activity-title" id="viewTitle"></h3>
        <p class="view-activity-desc" id="viewDesc"></p>

        <div class="modal-grid">
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-layer-group" style="color:#30B7C4;"></i> الفئة</div>
            <div class="info-value" id="viewCategory"></div>
          </div>
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-shapes" style="color:#30B7C4;"></i> النوع</div>
            <div class="info-value" id="viewType"></div>
          </div>
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-hourglass-half" style="color:#30B7C4;"></i> المدة</div>
            <div class="info-value" id="viewDuration"></div>
          </div>
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-signal" style="color:#30B7C4;"></i> الصعوبة</div>
            <div class="info-value" id="viewDifficulty"></div>
          </div>
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-user-group" style="color:#30B7C4;"></i> المكلفين</div>
            <div class="info-value" id="viewAssigned"></div>
          </div>
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-chart-line" style="color:#30B7C4;"></i> نسبة الإنجاز</div>
            <div class="info-value" id="viewCompletion"></div>
          </div>
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-toggle-on" style="color:#30B7C4;"></i> الحالة</div>
            <div class="info-value" id="viewStatus"></div>
          </div>
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-calendar-plus" style="color:#30B7C4;"></i> تاريخ الإنشاء</div>
            <div class="info-value" id="viewCreated"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add / Edit Modal -->
  <div class="modal-overlay" id="formModal">
    <div class="modal-card modal-card-form">
      <header class="modal-header">
        <h2 class="modal-title" id="formModalTitle">إضافة نشاط جديد</h2>
        <button class="modal-close" id="formModalClose">&times;</button>
      </header>
      <div class="modal-body">
        <form method="POST" id="activityForm">
          <input type="hidden" name="action" id="formAction" value="add">
          <input type="hidden" name="activity_id" id="formActivityId" value="">

          <div class="form-grid">
            <div class="form-group form-full">
              <label for="formTitle">عنوان النشاط <span class="required">*</span></label>
              <input type="text" id="formTitle" name="title" required placeholder="مثال: تمرين التنفس العميق">
            </div>

            <div class="form-group form-full">
              <label for="formDescription">الوصف</label>
              <textarea id="formDescription" name="description" rows="3" placeholder="وصف مختصر للنشاط..."></textarea>
            </div>

            <div class="form-group">
              <label for="formCategory">الفئة</label>
              <input type="text" id="formCategory" name="category" placeholder="مثال: استرخاء، تأمل، حركة">
            </div>

            <div class="form-group">
              <label for="formType">نوع النشاط</label>
              <select id="formType" name="activity_type">
                <option value="TASK">مهمة</option>
                <option value="GAME">لعبة</option>
                <option value="EXERCISE">تمرين</option>
              </select>
            </div>

            <div class="form-group">
              <label for="formDuration">المدة (بالدقائق)</label>
              <input type="number" id="formDuration" name="duration_min" min="0" placeholder="مثال: 15">
            </div>

            <div class="form-group">
              <label for="formDifficulty">مستوى الصعوبة</label>
              <select id="formDifficulty" name="difficulty">
                <option value="EASY">سهل</option>
                <option value="MEDIUM">متوسط</option>
                <option value="HARD">صعب</option>
              </select>
            </div>

            <div class="form-group">
              <label for="formStatus">الحالة</label>
              <select id="formStatus" name="status">
                <option value="ACTIVE">نشط</option>
                <option value="DRAFT">مسودة</option>
              </select>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary btn-save">
              <i class="fa-solid fa-floppy-disk"></i> حفظ
            </button>
            <button type="button" class="btn-outline btn-cancel" id="formCancelBtn">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="main.js"></script>
  <script src="../sidebar-toggle.js"></script>
</body>

</html>
