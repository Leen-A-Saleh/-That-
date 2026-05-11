<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';
require_once __DIR__ . '/notifications-database.php';

start_secure_session();
require_role(['CLIENT']);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && (string) ($_GET['action'] ?? '') === 'badge_state') {
  header('Content-Type: application/json; charset=UTF-8');

  try {
    echo json_encode([
      'success' => true,
      'has_unread' => client_has_unread_notifications_for_current_user(),
    ], JSON_UNESCAPED_UNICODE);
  } catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
      'success' => false,
      'has_unread' => false,
    ], JSON_UNESCAPED_UNICODE);
  }
  exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  header('Content-Type: application/json; charset=UTF-8');

  $rawBody = (string) file_get_contents('php://input');
  $decodedBody = json_decode($rawBody, true);
  $action = trim((string) (($decodedBody['action'] ?? $_POST['action'] ?? '')));

  try {
    if ($action === 'toggle_read') {
      $notificationId = (int) ($decodedBody['id'] ?? $_POST['id'] ?? 0);
      $readState = client_toggle_notification_read_for_current_user($notificationId);
      if ($readState === null) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'الإشعار غير موجود.'], JSON_UNESCAPED_UNICODE);
        exit;
      }

      echo json_encode(['success' => true, 'read' => $readState], JSON_UNESCAPED_UNICODE);
      exit;
    }

    if ($action === 'mark_all_read') {
      $affectedRows = client_mark_all_notifications_as_read_for_current_user();
      echo json_encode(['success' => true, 'affected' => $affectedRows], JSON_UNESCAPED_UNICODE);
      exit;
    }

    if ($action === 'delete_all') {
      $affectedRows = client_delete_all_notifications_for_current_user();
      echo json_encode(['success' => true, 'affected' => $affectedRows], JSON_UNESCAPED_UNICODE);
      exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'إجراء غير صالح.'], JSON_UNESCAPED_UNICODE);
    exit;
  } catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'تعذر تنفيذ العملية حالياً.'], JSON_UNESCAPED_UNICODE);
    exit;
  }
}

$notifications = [];
$stats = [
  'total' => 0,
  'this_week' => 0,
  'unread' => 0,
];
$showNotificationDot = false;

try {
  $notifications = client_get_notifications_for_current_user();
  $stats = client_get_notification_stats_for_current_user();
  $showNotificationDot = ((int) ($stats['unread'] ?? 0)) > 0;
} catch (Throwable $exception) {
  $notifications = [];
  $showNotificationDot = false;
}
?>
<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>الإشعارات</title>
  <link rel="stylesheet" href="../client-dashboard-page/style.css" />
  <link rel="stylesheet" href="./notifications.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="icon" type="image/png" href="../images/Silver.png" />
  <link rel="shortcut icon" href="../images/Silver.png" />
  <link rel="apple-touch-icon" href="../images/Silver.png" />
</head>

<body>
  <header class="navbar">
    <div class="menu-btn" id="menuBtn">
      <i class="fa fa-bars"></i>
    </div>
    <div class="nav-title">الإشعارات</div>
    <div class="nav-right">
      <a href="../client-notifications-page/notifications.php" class="bell">
        <i class="fa-regular fa-bell"></i>
        <span class="dot" id="notificationDot" <?= $showNotificationDot ? '' : ' style="display:none;"' ?>></span>
      </a>
    </div>
  </header>

  <?php $clientSidebarActive = 'notifications';
  include __DIR__ . '/../partials/sidebar.php'; ?>

  <div class="main">
    <div class="notif-hero">
      <div>
        <h2>الإشعارات</h2>
        <p>تابع جميع التحديثات والإشعارات المهمة</p>
      </div>
    </div>

    <section class="stats-row">
      <div class="stat-card">
        <div class="stat-info">
          <div class="stat-label">إجمالي الإشعارات</div>
          <div class="stat-value" id="totalCases"><?= e((string) $stats['total']) ?></div>
        </div>
        <div class="stat-icon">
          <img src="../images/Container7.png" alt="إجمالي الحالات" />
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <div class="stat-label">هذا الأسبوع</div>
          <div class="stat-value" id="activeCases"><?= e((string) $stats['this_week']) ?></div>
        </div>
        <div class="stat-icon">
          <img src="../images/Container 8.png" alt=" لحالات النشطة" />
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <div class="stat-label">غير مقروءة</div>
          <div class="stat-value" id="todayAppointments"><?= e((string) $stats['unread']) ?></div>
        </div>
        <div class="stat-icon">
          <img src="../images/Container 9.png" alt="مواعيد اليوم" />
        </div>
      </div>
    </section>

    <div class="notif-actions">
      <button id="markAllRead">
        <i class="fa fa-check"></i> تحديد الكل كمقروء
      </button>
      <button id="deleteAll" class="danger">
        <i class="fa fa-trash"></i> حذف الكل
      </button>
    </div>

    <div id="notificationsList" class="notifications-list"></div>
  </div>
  <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>

  <script>
    window.initialNotifications = <?= json_encode($notifications, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.initialNotificationStats = <?= json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  </script>
  <script src="./notifications.js"></script>
</body>

</html>