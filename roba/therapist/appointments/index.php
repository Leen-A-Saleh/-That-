<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'THERAPIST') {
    header("Location: /homepage/index.php");
    exit;
}

require_once __DIR__ . '/../../connection.php';

$user_id = $_SESSION['user_id'];
$therapist_id = $user_id;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_zoom') {
    $appt_id = (int) ($_POST['appointment_id'] ?? 0);
    $zoom_link = trim($_POST['zoom_link'] ?? '');

    if ($appt_id > 0) {
        $stmt = $conn->prepare("UPDATE appointments SET zoom_link = ? WHERE appointment_id = ? AND therapist_id = ? AND mode = 'ONLINE'");
        $stmt->bind_param("sii", $zoom_link, $appt_id, $therapist_id);
        $stmt->execute();
        $success = $stmt->affected_rows >= 0;
        $stmt->close();
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success ?? false]);
        exit;
    }

    header("Location: index.php" . (isset($_GET['date']) ? "?date=" . $_GET['date'] : ''));
    exit;
}

$selected_date = $_GET['date'] ?? date('Y-m-d');
$view_mode = $_GET['view'] ?? 'daily';

if ($view_mode === 'weekly') {
    $day_of_week = date('w', strtotime($selected_date));
    $start_date = date('Y-m-d', strtotime("-{$day_of_week} days", strtotime($selected_date)));
    $end_date = date('Y-m-d', strtotime("+" . (6 - $day_of_week) . " days", strtotime($selected_date)));
    $date_condition = "DATE(a.date_time) BETWEEN ? AND ?";
} else {
    $start_date = $selected_date;
    $end_date = $selected_date;
    $date_condition = "DATE(a.date_time) = ? AND 1=?";
}

$sql = "
    SELECT a.appointment_id, a.date_time, a.duration_min, a.mode, a.status,
           a.zoom_link, a.room_number, a.created_at, a.case_id,
           u.name AS client_name, c.date_of_birth,
           cs.title AS case_title, cs.description AS case_description,
           (SELECT COUNT(*) FROM sessions s WHERE s.case_id = a.case_id) AS total_sessions
    FROM appointments a
    JOIN clients c ON a.client_id = c.client_id
    JOIN users u ON c.client_id = u.user_id
    LEFT JOIN cases cs ON a.case_id = cs.case_id
    WHERE a.therapist_id = ?
      AND a.status IN ('CONFIRMED', 'REQUESTED')
      AND {$date_condition}
    ORDER BY a.date_time ASC
";

$stmt = $conn->prepare($sql);
if ($view_mode === 'weekly') {
    $stmt->bind_param("iss", $therapist_id, $start_date, $end_date);
} else {
    $dummy = 1;
    $stmt->bind_param("isi", $therapist_id, $start_date, $dummy);
}
$stmt->execute();
$appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_count = count($appointments);
$confirmed_count = 0;
$pending_count = 0;
foreach ($appointments as $a) {
    if ($a['status'] === 'CONFIRMED') $confirmed_count++;
    if ($a['status'] === 'REQUESTED') $pending_count++;
}

function calcAge($dob) {
    if (!$dob) return null;
    return (int) date_diff(date_create($dob), date_create('today'))->y;
}

function firstLetter($name) {
    return mb_substr(trim($name), 0, 1, 'UTF-8');
}

function formatTime($datetime) {
    $hour = (int) date('G', strtotime($datetime));
    $minute = date('i', strtotime($datetime));
    $period = $hour >= 12 ? 'مساءً' : 'صباحاً';
    $hour12 = $hour % 12 ?: 12;
    return sprintf('%02d:%s %s', $hour12, $minute, $period);
}

function formatDateAr($date) {
    return date('d-m-Y', strtotime($date));
}

$status_labels = ['CONFIRMED' => 'مؤكد', 'REQUESTED' => 'قيد الانتظار'];
$status_classes = ['CONFIRMED' => 'severity-green', 'REQUESTED' => 'severity-orange'];
$mode_labels = ['ONLINE' => 'مكالمة فيديو', 'IN_CENTER' => 'حضوري في المركز'];
$mode_icons = ['ONLINE' => 'fa-solid fa-video', 'IN_CENTER' => 'fa-solid fa-building'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>مواعيدي - ذات للاستشارات النفسية</title>
  <link rel="icon" type="./img/Silver.png" href="img/Silver.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

 <link rel="stylesheet" href="../thirapist.css">
  <link rel="stylesheet" href="../total.css"/>
    <link rel="stylesheet" href="style.css" />
</head>

<body>

  <div class="app">
        <?php $activePage = 'appointments'; ?>
    <?php include "../layouts/sidebar.php"; ?>

    <main class="main">
      <header class="main-header">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="فتح القائمة">
          <i class="fa-solid fa-bars"></i>
        </button>
        <h1 class="page-title"> مواعيدي</h1>
        <button class="status-btn">
          <span class="status-dot"></span>
          متصل
        </button>
      </header>

      <!-- Top bar with date picker and view toggle -->
      <section class="appts-top-bar dash-card">
        <div class="appts-top-left">
          <div class="date-label"> مواعيدي </div>
          <div class="date-sub">
            <?= $total_count ?> موعد في <?= formatDateAr($selected_date) ?>
            <?php if ($view_mode === 'weekly'): ?>
              — <?= formatDateAr($end_date) ?>
            <?php endif; ?>
          </div>
        </div>

        <div class="appts-top-right">
          <div class="date-input">
            <input type="date" id="datePicker" value="<?= htmlspecialchars($selected_date) ?>">
          </div>

          <div class="day-filters">
            <button class="day-pill <?= $view_mode === 'daily' ? 'active' : '' ?>" data-filter="daily">يومي</button>
            <button class="day-pill <?= $view_mode === 'weekly' ? 'active' : '' ?>" data-filter="weekly">اسبوعي</button>
          </div>
        </div>
      </section>

      <!-- Summary stats -->
      <section class="appts-summary-card dash-card">
        <div class="appts-stats-row">
          <div class="appts-stat">
            <div class="stat-label">إجمالي المواعيد</div>
            <div class="stat-value"><?= $total_count ?></div>
          </div>
          <div class="appts-statt">
            <div class="stat-label">مواعيد مؤكدة</div>
            <div class="stat-value"><?= $confirmed_count ?></div>
          </div>
          <div class="appts-stattt">
            <div class="stat-label">في الانتظار</div>
            <div class="stat-value"><?= $pending_count ?></div>
          </div>
        </div>
      </section>

      <!-- Appointments list -->
      <div class="appointments-list">

        <?php if (empty($appointments)): ?>
          <p style="text-align:center; padding:40px; color:#6A7282;">لا توجد مواعيد في هذا التاريخ</p>
        <?php else: ?>
          <?php foreach ($appointments as $appt):
              $age = calcAge($appt['date_of_birth']);
              $apptTime = formatTime($appt['date_time']);
              $isNewPatient = (int)$appt['total_sessions'] === 0;
              $status = $appt['status'];
              $mode = $appt['mode'];

              if ($isNewPatient) {
                  $sessionType = 'جلسة أولى';
              } else {
                  $sessionType = 'جلسة متابعة';
              }

              if ($mode === 'ONLINE') {
                  $joinIcon = 'fa-solid fa-video';
                  $joinLabel = 'انضم للجلسة';
              } else {
                  $joinIcon = 'fa-solid fa-building';
                  $joinLabel = 'عرض تفاصيل الموقع';
              }
          ?>
        <article class="appointment-card status-<?= strtolower($status) ?>" data-id="<?= $appt['appointment_id'] ?>">
          <header class="request-header">
            <div class="request-main">
              <div class="request-person">
                <div class="avatar-circle"><?= firstLetter($appt['client_name']) ?></div>
                <div>
                  <div class="request-name"><?= htmlspecialchars($appt['client_name']) ?></div>
                  <?php if ($age): ?>
                  <div class="request-type"><?= $age ?> سنة</div>
                  <?php endif; ?>
                </div>
                <span class="severity-badge <?= $status_classes[$status] ?? 'severity-green' ?>"><?= $status_labels[$status] ?? $status ?></span>
              </div>
            </div>
          </header>

          <div class="request-info-row">
            <!-- Time & session type -->
            <div class="info-block">
              <div class="info-head">
                <i class="fa-regular fa-clock" style="color:#30B7C4;"></i>
                <span class="info-label">الوقت</span>
              </div>
              <div class="info-value"><?= $apptTime ?></div>
              <div class="info-label">نوع الجلسة</div>
              <div class="info-value"><?= $sessionType ?></div>
            </div>
            <!-- Meeting mode & notes -->
            <div class="info-block">
              <div class="info-head">
                <i class="<?= $mode_icons[$mode] ?? 'fa-solid fa-video' ?>" style="color:#30B7C4;"></i>
                <span class="info-label">نوع الاجتماع</span>
              </div>
              <div class="info-value"><?= $mode_labels[$mode] ?? $mode ?></div>

              <?php if (!empty($appt['case_title'])): ?>
              <div class="info-label">ملاحظات</div>
              <div class="info-value"><?= htmlspecialchars($appt['case_title']) ?></div>
              <?php endif; ?>
            </div>
            <!-- Duration -->
            <div class="info-block">
              <div class="info-head">
                <i class="fa-solid fa-calendar-days" style="color:#30B7C4;"></i>
                <span class="info-label">المدة</span>
              </div>
              <div class="info-value"><?= (int)$appt['duration_min'] ?> دقيقة</div>
            </div>
          </div>

          <footer class="request-footer">
            <?php if ($status === 'CONFIRMED' && $mode === 'ONLINE' && !empty($appt['zoom_link'])): ?>
              <a href="<?= htmlspecialchars($appt['zoom_link']) ?>" target="_blank" class="btn-primary btn-accept"> <i class="fa-solid fa-video" style="color:#fff;"></i> انضم للجلسة </a>
            <?php elseif ($status === 'CONFIRMED'): ?>
              <button class="btn-primary btn-accept" disabled style="opacity:0.6;cursor:not-allowed;"> <i class="<?= $joinIcon ?>" style="color:#fff;"></i> <?= $joinLabel ?> </button>
            <?php else: ?>
              <button class="btn-primary btn-accept" disabled style="opacity:0.6;cursor:not-allowed;"> <i class="fa-regular fa-clock" style="color:#fff;"></i> بانتظار التأكيد </button>
            <?php endif; ?>
            <button class="btn-outline btn-details"
              data-id="<?= $appt['appointment_id'] ?>"
              data-name="<?= htmlspecialchars($appt['client_name']) ?>"
              data-age="<?= $age ? $age . ' سنة' : '' ?>"
              data-letter="<?= firstLetter($appt['client_name']) ?>"
              data-date="<?= date('Y-m-d', strtotime($appt['date_time'])) ?>"
              data-time="<?= $apptTime ?>"
              data-duration="<?= (int)$appt['duration_min'] ?> دقيقة"
              data-mode="<?= $mode_labels[$mode] ?? $mode ?>"
              data-mode-raw="<?= $mode ?>"
              data-session="<?= $sessionType ?>"
              data-status="<?= $status_labels[$status] ?? $status ?>"
              data-status-class="<?= $status_classes[$status] ?? 'severity-green' ?>"
              data-case="<?= htmlspecialchars($appt['case_title'] ?? '') ?>"
              data-case-desc="<?= htmlspecialchars($appt['case_description'] ?? '') ?>"
              data-room="<?= htmlspecialchars($appt['room_number'] ?? '') ?>"
              data-zoom="<?= htmlspecialchars($appt['zoom_link'] ?? '') ?>"
              data-created="<?= formatDateAr($appt['created_at']) . ' • ' . formatTime($appt['created_at']) ?>"
              data-status-raw="<?= $status ?>"
            >عرض التفاصيل</button>
          </footer>
        </article>
          <?php endforeach; ?>
        <?php endif; ?>

      </div>

      <?php include "../layouts/footer.php"; ?>
    </main>
  </div>

  <!-- Details Modal -->
  <div class="modal-overlay" id="detailsModal">
    <div class="modal-card">
      <header class="modal-header">
        <h2 class="modal-title">تفاصيل الموعد</h2>
        <button class="modal-close" id="modalClose">&times;</button>
      </header>

      <div class="modal-body">
        <!-- Client info -->
        <div class="modal-client">
          <div class="avatar-circle" id="modalAvatar"></div>
          <div>
            <div class="request-name" id="modalName"></div>
            <div class="request-type" id="modalAge"></div>
          </div>
          <span class="severity-badge" id="modalStatus"></span>
        </div>

        <!-- Info grid -->
        <div class="modal-grid">
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-calendar-days" style="color:#30B7C4;"></i> التاريخ</div>
            <div class="info-value" id="modalDate"></div>
          </div>
          <div class="modal-info">
            <div class="info-label"><i class="fa-regular fa-clock" style="color:#30B7C4;"></i> الوقت</div>
            <div class="info-value" id="modalTime"></div>
          </div>
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-hourglass-half" style="color:#30B7C4;"></i> المدة</div>
            <div class="info-value" id="modalDuration"></div>
          </div>
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-video" style="color:#30B7C4;"></i> نوع الاجتماع</div>
            <div class="info-value" id="modalMode"></div>
          </div>
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-notes-medical" style="color:#30B7C4;"></i> نوع الجلسة</div>
            <div class="info-value" id="modalSession"></div>
          </div>
          <div class="modal-info" id="modalRoomWrap" style="display:none;">
            <div class="info-label"><i class="fa-solid fa-door-open" style="color:#30B7C4;"></i> رقم الغرفة</div>
            <div class="info-value" id="modalRoom"></div>
          </div>
        </div>

        <!-- Case info -->
        <div id="modalCaseWrap" style="display:none;">
          <div class="modal-section-title">معلومات الحالة</div>
          <div class="modal-case-box">
            <div class="info-label">عنوان الحالة</div>
            <div class="info-value" id="modalCase"></div>
            <div class="info-label" id="modalCaseDescLabel" style="margin-top:8px; display:none;">وصف الحالة</div>
            <div class="info-value" id="modalCaseDesc"></div>
          </div>
        </div>

        <!-- Zoom link (only for ONLINE) -->
        <div id="modalZoomWrap" style="display:none;">
          <div class="modal-section-title">رابط الاجتماع</div>
          <div class="zoom-editor">
            <div class="zoom-input-row">
              <input type="url" id="modalZoomInput" class="zoom-input" placeholder="https://zoom.us/j/..." dir="ltr">
              <button class="btn-primary zoom-save-btn" id="modalZoomSave">
                <i class="fa-solid fa-floppy-disk"></i> حفظ
              </button>
            </div>
            <div class="zoom-feedback" id="modalZoomFeedback"></div>
            <a href="#" target="_blank" class="btn-primary modal-zoom-btn" id="modalZoomLink" style="display:none;">
              <i class="fa-solid fa-video" style="color:#fff;"></i> انضم للجلسة
            </a>
          </div>
        </div>

        <!-- Complete session button (only for CONFIRMED) -->
        <div id="modalCompleteWrap" style="display:none;">
          <a href="#" id="modalCompleteBtn" class="btn-complete-session">
            <i class="fa-solid fa-clipboard-check"></i> إنهاء الجلسة وكتابة الملاحظات
          </a>
        </div>

        <!-- Created at -->
        <div class="modal-meta" id="modalCreated"></div>
      </div>
    </div>
  </div>

  <script src="main.js"></script>
  <script src="../sidebar-toggle.js"></script>
</body>

</html>
