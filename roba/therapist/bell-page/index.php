<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'THERAPIST') {
    header("Location: /homepage/index.php");
    exit;
}

require_once __DIR__ . '/../../connection.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $nid    = (int) ($_POST['notification_id'] ?? 0);

    if ($nid <= 0) {
        echo json_encode(['success' => false, 'message' => 'معرف غير صالح']);
        exit;
    }

    try {
        if ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $nid, $user_id);
            $stmt->execute();
            $ok = $stmt->affected_rows > 0;
            $stmt->close();
            echo json_encode(['success' => $ok]);
            exit;
        }

        if ($action === 'toggle_read') {
            $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 - is_read WHERE notification_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $nid, $user_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("SELECT is_read FROM notifications WHERE notification_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $nid, $user_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            echo json_encode(['success' => true, 'is_read' => (int)($row['is_read'] ?? 0)]);
            exit;
        }
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'تعذّر تنفيذ الطلب']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
    exit;
}

$stmt = $conn->prepare("
    SELECT
        COUNT(*)                                           AS total,
        SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END)       AS unread,
        SUM(CASE WHEN priority = 'URGENT' THEN 1 ELSE 0 END) AS urgent,
        SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END)       AS read_count
    FROM notifications
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total_n   = (int)($stats['total']      ?? 0);
$unread_n  = (int)($stats['unread']     ?? 0);
$urgent_n  = (int)($stats['urgent']     ?? 0);
$read_n    = (int)($stats['read_count'] ?? 0);
$read_pct  = $total_n > 0 ? round(($read_n / $total_n) * 100) : 0;

$stmt = $conn->prepare("
    SELECT notification_id, title, body, type, priority, is_read, created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY is_read ASC, created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$notifications = [];
while ($row = $res->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

$typeMeta = [
    'GENERAL'              => ['class' => 'reminder',     'icon' => 'fa-regular fa-bell'],
    'APPOINTMENT_REMINDER' => ['class' => 'appointment',  'icon' => 'fa-regular fa-calendar'],
    'SESSION_CONFIRMATION' => ['class' => 'appointment',  'icon' => 'fa-regular fa-calendar-check'],
    'ALERT'                => ['class' => 'test-alert',   'icon' => 'fa-solid fa-circle-exclamation'],
    'ACTIVITY_ASSIGNED'    => ['class' => 'new-patient',  'icon' => 'fa-solid fa-wave-square'],
    'ASSESSMENT_READY'     => ['class' => 'test-alert',   'icon' => 'fa-solid fa-file-lines'],
];

function timeAgo($datetime) {
    $now  = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);
    if ($diff->y > 0) return 'منذ ' . $diff->y . ' سنة';
    if ($diff->m > 0) return 'منذ ' . $diff->m . ' شهر';
    if ($diff->d >= 2) return 'منذ ' . $diff->d . ' أيام';
    if ($diff->d === 1) return 'أمس';
    if ($diff->h >= 2) return 'منذ ' . $diff->h . ' ساعات';
    if ($diff->h === 1) return 'منذ ساعة';
    if ($diff->i >= 2) return 'منذ ' . $diff->i . ' دقيقة';
    if ($diff->i === 1) return 'منذ دقيقة';
    return 'منذ قليل';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <title>التنبيهات</title>
  <link rel="icon" type="image/png" href="img/Silver.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="../thirapist.css">
  <link rel="stylesheet" href="../total.css" />
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <div class="app">

    <?php $activePage = 'notifications'; ?>
    <?php include "../layouts/sidebar.php"; ?>

    <main class="main">
      <header class="main-header">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="فتح القائمة">
          <i class="fa-solid fa-bars"></i>
        </button>
        <h1 class="page-title">التنبيهات</h1>
        <button class="status-btn">
          <span class="status-dot"></span>
          متصل
        </button>
      </header>

      <section class="stats-row">
        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">جميع التنبيهات</div>
            <div class="stat-value"><?= $total_n ?></div>
          </div>
          <div class="stat-icon-teal"><i class="fa-solid fa-bell"></i></div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">غير مقروءة</div>
            <div class="stat-value"><?= $unread_n ?></div>
          </div>
          <div class="stat-icon-orange"><i class="fa-solid fa-exclamation-circle"></i></div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">تنبيهات عالية</div>
            <div class="stat-value"><?= $urgent_n ?></div>
          </div>
          <div class="stat-icon-red"><i class="fa-solid fa-exclamation-circle"></i></div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">نسبة المقروء</div>
            <div class="stat-value"><?= $read_pct ?>%</div>
          </div>
          <div class="stat-icon-blue"><i class="fa-solid fa-clock-four"></i></div>
        </div>
      </section>

      <section class="notifications-section" dir="rtl">
        <div class="notifications">
          <?php if (empty($notifications)): ?>
            <p class="no-notifs">لا توجد تنبيهات</p>
          <?php else: foreach ($notifications as $n):
            $meta = $typeMeta[$n['type']] ?? $typeMeta['GENERAL'];
          ?>
          <div class="notification <?= $meta['class'] ?><?= $n['is_read'] ? ' read' : '' ?>" data-id="<?= $n['notification_id'] ?>">
            <div class="notif-icon">
              <i class="<?= $meta['icon'] ?>"></i>
            </div>
            <div class="notif-content">
              <div class="notif-title-row">
                <span class="notif-title"><?= htmlspecialchars($n['title']) ?></span>
                <?php if (!$n['is_read']): ?>
                  <span class="status-dot"></span>
                <?php endif; ?>
              </div>
              <p class="notif-text"><?= nl2br(htmlspecialchars($n['body'])) ?></p>
              <div class="notif-meta">
                <span class="notif-time"><?= timeAgo($n['created_at']) ?></span>
                <?php if ($n['priority'] === 'URGENT'): ?>
                  <span class="badge badge-urgent">عاجل</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="notif-actions">
              <?php if (!$n['is_read']): ?>
              <button class="icon-btn done" title="تعليم كمقروء">
                <i class="fa-regular fa-circle-check"></i>
              </button>
              <?php endif; ?>
              <button class="icon-btn delete" title="حذف">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </section>

      <?php include "../layouts/footer.php"; ?>
    </main>
  </div>
  <script src="main.js"></script>
  <script src="../sidebar-toggle.js"></script>
</body>
</html>
