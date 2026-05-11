<?php
declare(strict_types=1);

require_once __DIR__ . '/../partials/require-admin.php';
require_once __DIR__ . '/appointments-database.php';
?>
<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>المواعيد</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="32x32"
      href="../images/Silver.png"
    />
    <link rel="shortcut icon" sizes="10x10" href="../images/Silver.png" />
    <link rel="stylesheet" href="../admin-dashboard-page/admin-dashboard.css" />
    <link rel="stylesheet" href="./appointments.css" />
  </head>

  <body>
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

<div class="main-wrapper">
      <header class="navbar">
        <button class="menu-btn" type="button" aria-label="فتح القائمة">
          <i class="fa fa-bars"></i>
        </button>

        <div class="nav-title">المواعيد</div>

        <div class="nav-right">
          <button class="status-btn" type="button">
            <span class="status-dot"></span>
            متصل
          </button>
        </div>
      </header>

      <main class="page-content">
        <section class="stats-row stats">
          <div class="stat-card">
            <span>إجمالي المواعيد</span>
            <h3 id="total"><?php echo htmlspecialchars((string) $stats['total'], ENT_QUOTES, 'UTF-8'); ?></h3>
          </div>
          <div class="stat-card">
            <span>مؤكدة</span>
            <h3 id="confirmed"><?php echo htmlspecialchars((string) $stats['confirmed'], ENT_QUOTES, 'UTF-8'); ?></h3>
          </div>
          <div class="stat-card">
            <span>قيد الانتظار</span>
            <h3 id="pending"><?php echo htmlspecialchars((string) $stats['pending'], ENT_QUOTES, 'UTF-8'); ?></h3>
          </div>
          <div class="stat-card">
            <span>أونلاين</span>
            <h3 id="online"><?php echo htmlspecialchars((string) $stats['online'], ENT_QUOTES, 'UTF-8'); ?></h3>
          </div>
        </section>

        <section class="calendar-card">
          <div class="calendar-header">
            <button id="prevMonth" type="button" aria-label="الشهر السابق">
              <img src="../images/button.svg" alt="" />
            </button>
            <h2 id="monthTitle"></h2>
            <button id="nextMonth" type="button" aria-label="الشهر التالي">
              <img src="../images/button (1).svg" alt="" />
            </button>
          </div>

          <div class="days-names">
            <span>الأحد</span>
            <span>الإثنين</span>
            <span>الثلاثاء</span>
            <span>الأربعاء</span>
            <span>الخميس</span>
            <span>الجمعة</span>
            <span>السبت</span>
          </div>

          <div class="calendar-grid" id="calendar"></div>
        </section>

        <section class="legend-box">
          <h4>الأخصائيين:</h4>
          <div class="legend" id="legend"></div>
        </section>
      </main>

      <footer>© 2026 ذات للاستشارات النفسية جميع الحقوق محفوظة</footer>
    </div>

    <script>
      window.appointmentData = <?php echo json_encode($appointments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
      window.appointmentStats = <?php echo json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
      window.therapistLegend = <?php echo json_encode($therapistLegend, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    </script>
    <script src="./appointments.js"></script>
  </body>
</html>
