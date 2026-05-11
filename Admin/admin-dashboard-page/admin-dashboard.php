<?php

declare(strict_types=1);

require_once __DIR__ . '/../partials/require-admin.php';
require_once __DIR__ . '/admin-dashboard-database.php';

// Fetch all data once
$stats       = get_dashboard_stats();
$activities  = get_recent_activities();
$specialists = get_top_specialists();
$weeklyData  = get_weekly_sessions();
$growthData  = get_monthly_growth();
?>
<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>لوحة التحكم</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="icon" type="image/png" sizes="32x32" href="../images/Silver.png" />
  <link rel="shortcut icon" sizes="10x10" href="../images/Silver.png" />
  <link rel="stylesheet" href="./admin-dashboard.css" />
</head>

<body>
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <div class="main-wrapper">
    <header class="navbar">
      <div class="menu-btn">
        <i class="fa fa-bars"></i>
      </div>
      <div class="nav-title">لوحة التحكم</div>
      <div class="nav-right">
        <button class="status-btn">
          <span class="status-dot"></span>
          متصل
        </button>
      </div>
    </header>

    <main class="page-content">

      <!-- Stat Cards -->
      <div class="stats-grid">

        <div class="stat-card">
          <div class="stat-top">
            <div class="stat-icon bg-blue"><img src="../images/Container.svg" /></div>
            <span class="stat-badge"><?= e((string) $stats['rating']) ?>%+</span>
          </div>
          <div class="stat-label">المستخدمين النشطين</div>
          <div class="stat-value"><?= e((string) $stats['activeUsers']) ?></div>
        </div>

        <div class="stat-card">
          <div class="stat-top">
            <div class="stat-icon bg-purple"><img src="../images/Container (1).svg" /></div>
            <span class="stat-badge">
              <?= e((string) $stats['therapistsGrowth']) ?>%
            </span>
          </div>
          <div class="stat-label">الأخصائيين</div>
          <div class="stat-value"><?= e((string) $stats['therapists']) ?></div>
        </div>

        <div class="stat-card">
          <div class="stat-top">
            <div class="stat-icon bg-teal"><img src="../images/Container (2).svg" /></div>
            <span class="stat-badge">
              <?= e((string) $stats['sessionsGrowth']) ?>%
            </span>
          </div>
          <div class="stat-label">الجلسات هذا الشهر</div>
          <div class="stat-value"><?= e((string) $stats['sessions']) ?></div>
        </div>

        <div class="stat-card">
          <div class="stat-top">
            <div class="stat-icon bg-orange"><img src="../images/Container (3).svg" /></div>
            <span class="stat-badge">
              <?= e((string) $stats['testsGrowth']) ?>%
            </span>

          </div>
          <div class="stat-label">إكمال الاختبارات</div>
          <div class="stat-value"><?= e((string) $stats['tests']) ?>%</div>
        </div>

        <div class="stat-card">
          <div class="stat-top">
            <div class="stat-icon bg-green"><img src="../images/Container (4).svg" /></div>
            <span class="stat-badge">0000</span>
          </div>
          <div class="stat-label">الإيرادات</div>
          <div class="stat-value">
            <?= $stats['revenue'] ? e(number_format((int)$stats['revenue'])) : '—' ?>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-top">
            <div class="stat-icon bg-pink"><img src="../images/Container (5).svg" /></div>
            <span class="stat-badge">
              <?= e((string) $stats['ratingGrowth']) ?>
            </span>
          </div>
          <div class="stat-label">معدل الرضا</div>
          <div class="stat-value">
            <?= $stats['rating'] > 0 ? e(number_format($stats['rating'], 1)) . '/5' : '—' ?>
          </div>
        </div>

      </div>

      <!-- Charts -->
      <div class="charts-row">
        <div class="chart-card">
          <div class="chart-card-header">
            <img src="../images/Activity.svg" class="chart-hdr-icon" />
            <h3>الجلسات الأسبوعية</h3>
          </div>
          <div class="chart-box">
            <canvas id="weeklyChart"></canvas>
          </div>
        </div>

        <div class="chart-card">
          <div class="chart-card-header">
            <img src="../images/TrendingUp.svg" class="chart-hdr-icon" />
            <h3>النمو الشهري</h3>
          </div>
          <div class="chart-legend">
            <span class="leg">
              <span class="leg-dot" style="background:#3b82f6"></span>المستخدمون
            </span>
            <span class="leg">
              <span class="leg-dot" style="background:#0ea5b0"></span>الجلسات
            </span>
          </div>
          <div class="chart-box">
            <canvas id="growthChart"></canvas>
          </div>
        </div>
      </div>

      <!--  Bottom Row -->
      <div class="bottom-row">

        <!-- Recent Activities -->
        <div class="bottom-card">
          <div class="bc-header">
            <h3>الأنشطة الأخيرة</h3>
          </div>
          <div class="activities-list">
            <?php if (empty($activities)): ?>
              <p class="no-data">لا توجد أنشطة حديثة</p>
            <?php else: ?>
              <?php foreach ($activities as $activity): ?>
                <div class="act-item">
                  <span class="act-dot"></span>
                  <div class="act-body">
                    <p class="act-title"><?= e($activity['title']) ?></p>
                    <span class="act-time"><?= e($activity['time']) ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Top Specialists -->
        <div class="bottom-card">
          <div class="bc-header">
            <h3>أفضل الأخصائيين</h3>
          </div>
          <div class="specialists-list">
            <?php if (empty($specialists)): ?>
              <p class="no-data">لا يوجد أخصائيون مسجلون بعد</p>
            <?php else: ?>
              <?php foreach ($specialists as $index => $specialist): ?>
                <div class="spec-item">
                  <div class="spec-rank"><?= $index + 1 ?></div>
                  <div class="spec-info">
                    <p class="spec-name"><?= e($specialist['name']) ?></p>
                    <span class="spec-sess"><?= e((string) $specialist['sessions']) ?> جلسة</span>
                  </div>
                  <div class="spec-rating">
                    <i class="fas fa-star"></i>
                    <span><?= e($specialist['rating']) ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </main>

    <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
  </div>

  <!-- Pass chart data from PHP to JS -->
  <script>
    const WEEKLY_DATA = <?= json_encode($weeklyData, JSON_UNESCAPED_UNICODE) ?>;
    const GROWTH_DATA = <?= json_encode($growthData, JSON_UNESCAPED_UNICODE) ?>;
  </script>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="./admin-dashboard.js"></script>
</body>

</html>