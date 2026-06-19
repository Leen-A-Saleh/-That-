<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';
require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/client.php';
require_once __DIR__ . '/appointments-database.php';

start_secure_session();
require_auth();
require_role(['CLIENT']);

$clientId      = (int) current_user()['user_id'];
cancelExpiredRequestedAppointments($clientId);
$appointments  = getClientAppointments($clientId);
$stats         = getClientAppointmentStats($clientId);
$currentUser   = current_user();
?>
<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>المواعيد</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="icon" type="image/png" href="../images/Silver.png" />
  <link rel="stylesheet" href="../client-dashboard-page/style.css" />
  <link rel="stylesheet" href="./appointments.css" />
</head>

<body>
  <header class="navbar">
    <div class="menu-btn" id="menuBtn"><i class="fa fa-bars"></i></div>
    <div class="nav-title">المواعيد</div>
    <div class="nav-right">
      <a href="../client-notifications-page/notifications.php" class="bell">
        <i class="fa-regular fa-bell"></i>
        <span class="dot" id="notificationDot"></span>
      </a>
    </div>
  </header>

  <?php
  $clientSidebarActive = 'appointments';
  include __DIR__ . '/../partials/sidebar.php';
  ?>

  <main class="main">

    <!-- Stats cards-->
    <section class="stats-row">
      <div class="stat-card">
        <div class="stat-info">
          <div class="stat-label">المواعيد القادمة</div>
          <div class="stat-value" id="totalCases">
            <?= htmlspecialchars((string) $stats['upcoming']) ?>
          </div>
        </div>
        <div class="stat-icon">
          <img src="../images/Container.png" alt="المواعيد القادمة" />
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <div class="stat-label">الجلسات المكتملة</div>
          <div class="stat-value" id="activeCases">
            <?= htmlspecialchars((string) $stats['completed']) ?>
          </div>
        </div>
        <div class="stat-icon">
          <img src="../images/Container (1).png" alt="الجلسات المكتملة" />
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <div class="stat-label">إجمالي الجلسات</div>
          <div class="stat-value" id="todayAppointments">
            <?= htmlspecialchars((string) ($stats['upcoming'] + $stats['completed'])) ?> </div>
        </div>
        <div class="stat-icon">
          <img src="../images/Container (2).png" alt="إجمالي الجلسات" />
        </div>
      </div>
    </section>

    <!-- Calendar -->
    <section class="appointments-section">
      <h2>تقويم مواعيدي</h2>
      <div class="calendar-header">
        <button id="prevMonth">&lt;</button>
        <h3 id="monthYear"></h3>
        <button id="nextMonth">&gt;</button>
      </div>
      <div class="weekdays">
        <div>أحد</div>
        <div>اثنين</div>
        <div>ثلاثاء</div>
        <div>أربعاء</div>
        <div>خميس</div>
        <div>جمعة</div>
        <div>سبت</div>
      </div>
      <div id="calendar"></div>
    </section>

    <!-- Booking details popup -->
    <div id="bookingDetails" class="booking-details hidden">
      <h3>تفاصيل الموعد</h3>
      <div id="detailsContent"></div>
      <button id="closeDetails">إغلاق</button>
    </div>

    <!-- Appointments list -->
    <section class="appointments-list">
      <h2>جميع المواعيد</h2>
      <div id="appointmentsContainer"></div>
    </section>

  </main>

  <div class="sidebar-overlay"></div>

  <!-- Inject DB data for JS  -->
  <script>
    const APPOINTMENTS = <?= json_encode($appointments, JSON_UNESCAPED_UNICODE) ?>;
    const CURRENT_USER_NAME = "<?= htmlspecialchars($currentUser['name'] ?? '', ENT_QUOTES) ?>";
  </script>

  <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
  <script src="./appointments.js"></script>
</body>

</html>