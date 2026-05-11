<?php

declare(strict_types=1);

require_once __DIR__ . '/../partials/require-admin.php';
require_once __DIR__ . '/notifications-database.php';

// POST handler

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
  header('Content-Type: application/json; charset=utf-8');

  try {
    if ($_POST['action'] === 'send') {
      $count = notifications_send(
        trim($_POST['title']    ?? ''),
        trim($_POST['body']     ?? ''),
        $_POST['audience']      ?? 'all'
      );
      echo json_encode(['success' => true, 'count' => $count]);
    } else {
      throw new RuntimeException('إجراء غير معروف');
    }
  } catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
  }

  exit;
}

// Page data

$stats    = notifications_getStats();
$audience = notifications_getAudienceCounts();
$allNotifs = notifications_getAll();
?>
<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>الإشعارات</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="icon" type="image/png" sizes="32x32" href="../images/Silver.png" />
  <link rel="shortcut icon" sizes="10x10" href="../images/Silver.png" />
  <link rel="stylesheet" href="../admin-dashboard-page/admin-dashboard.css" />
  <link rel="stylesheet" href="./notifications.css" />
</head>

<body>
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <div class="main-wrapper">
    <header class="navbar">
      <div class="menu-btn"><i class="fa fa-bars"></i></div>
      <div class="nav-title">الإشعارات</div>
      <div class="nav-right">
        <button class="status-btn"><span class="status-dot"></span>متصل</button>
      </div>
    </header>

    <main class="page-content">
      <div class="notifications-wrapper">
        <div class="page-header">
          <div class="page-header-text">
            <h1 class="page-title">الإشعارات والتنبيهات</h1>
            <p class="page-subtitle">إدارة وإرسال الإشعارات للمستخدمين</p>
          </div>
          <button class="btn-send-new" id="openModalBtn">
            <img src="../images/Send.svg" />
            إرسال إشعار جديد
          </button>
        </div>

        <!-- Stat cards (PHP values) -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-label">إجمالي الإشعارات</div>
            <div class="stat-value" id="statTotal"><?= $stats['total'] ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">تم الإرسال</div>
            <div class="stat-value" id="statSent"><?= $stats['sent'] ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">مجدولة</div>
            <div class="stat-value" id="statScheduled"><?= $stats['scheduled'] ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">إجمالي المستلمين</div>
            <div class="stat-value" id="statRecipients"><?= number_format($stats['recipients']) ?></div>
          </div>
        </div>

        <div class="filters-bar">
          <button class="filter-btn active" data-filter="all">الكل</button>
          <button class="filter-btn" data-filter="reminder">تذكيرات</button>
          <button class="filter-btn" data-filter="message">رسائل</button>
          <button class="filter-btn" data-filter="announcement">إعلانات</button>
          <button class="filter-btn" data-filter="system">نظامية</button>
        </div>

        <div class="notifications-list" id="notifList"></div>
      </div>
    </main>

    <!-- Send modal -->
    <div class="modal-overlay hidden" id="modalOverlay">
      <div class="modal-box">
        <button class="modal-close" id="closeModalBtn">
          <i class="fa-solid fa-xmark"></i>
        </button>
        <h2 class="modal-title">إرسال إشعار جديد</h2>

        <div class="form-group">
          <label class="form-label">الفئة المستهدفة</label>
          <div class="audience-group">
            <button class="aud-btn active" data-aud="all" data-count="<?= $audience['all'] ?>">
              <i class="fa-solid fa-users"></i> الجميع (<?= $audience['all'] ?>)
            </button>
            <button class="aud-btn" data-aud="patients" data-count="<?= $audience['clients'] ?>">
              <i class="fa-solid fa-user"></i> المرضى (<?= $audience['clients'] ?>)
            </button>
            <button class="aud-btn" data-aud="specialists" data-count="<?= $audience['therapists'] ?>">
              <i class="fa-solid fa-user-tie"></i> الأخصائيين (<?= $audience['therapists'] ?>)
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="notifTitle">عنوان الإشعار</label>
          <input class="form-input" id="notifTitle" type="text" placeholder="مثال: تذكير بموعد قادم" />
          <span class="field-error hidden" id="titleError">هذا الحقل مطلوب</span>
        </div>

        <div class="form-group">
          <label class="form-label" for="notifBody">نص الإشعار</label>
          <textarea class="form-textarea" id="notifBody" placeholder="اكتب نص الإشعار هنا..."></textarea>
          <span class="field-error hidden" id="bodyError">هذا الحقل مطلوب</span>
        </div>

        <div class="modal-actions">
          <button class="btn-modal-send" id="sendBtn">
            <i class="fa-solid fa-paper-plane"></i> إرسال الإشعار
          </button>
          <button class="btn-modal-cancel" id="cancelBtn">إلغاء</button>
        </div>
      </div>
    </div>

    <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
  </div>

  <!-- PHP data injected for JS -->
  <script>
    const notificationsData = <?= json_encode($allNotifs, JSON_UNESCAPED_UNICODE) ?>;
  </script>

  <script src="./notifications.js"></script>
</body>

</html>