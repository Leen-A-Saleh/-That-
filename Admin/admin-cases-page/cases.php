<?php

declare(strict_types=1);
require_once __DIR__ . '/../partials/require-admin.php';
require_once __DIR__ . '/cases-database.php';

$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$limit  = 8;


$stats = getCasesStats();
$result = getCases($page, $limit, $search, $status);
$cases = $result['cases'];
$totalFound = $result['totalFound'];
$totalPages = (int) max(0, (int) ceil($totalFound / $limit));

$casesPageUrl = static function (int $p) use ($search, $status): string {
  $q = ['page' => $p];
  if ($search !== '') {
    $q['search'] = $search;
  }
  if ($status !== '') {
    $q['status'] = $status;
  }
  return 'cases.php?' . http_build_query($q);
};

$shownCount = $totalFound === 0 ? 0 : count($cases);
$paginationInfo = 'عرض ' . $shownCount . ' من ' . $totalFound . ' حالة';
?>
<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>إدارة الحالات</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="icon" type="image/png" sizes="32x32" href="../images/Silver.png" />
  <link rel="shortcut icon" sizes="10x10" href="../images/Silver.png" />
  <link rel="stylesheet" href="../admin-dashboard-page/admin-dashboard.css" />
  <link rel="stylesheet" href="./cases.css" />
  <style>
    /* without style here the case card will be too wide and overflow the page */
    .case-card {
      gap: 10px;
      padding: 15px;
    }

    .case-date-val {
      font-size: 12px;
      color: #666;
    }
  </style>
</head>

<body>
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <div class="main-wrapper">
    <header class="navbar">
      <div class="menu-btn"><i class="fa fa-bars"></i></div>
      <div class="nav-title">إدارة الحالات</div>
      <div class="nav-right"><button class="status-btn"><span class="status-dot"></span>متصل</button></div>
    </header>

    <main class="page-content">

      <div class="stats-row">
        <div class="stat-card">
          <span class="stat-label">إجمالي الحالات</span>
          <span class="stat-value"><?= $stats['total'] ?></span>
        </div>
        <div class="stat-card active-stat">
          <span class="stat-label">حالات جارية</span>
          <span class="stat-value"><?= $stats['active'] ?></span>
        </div>
        <div class="stat-card review-stat">
          <span class="stat-label">تحت المراجعة</span>
          <span class="stat-value"><?= $stats['review'] ?></span>
        </div>
        <div class="stat-card pending-stat">
          <span class="stat-label">حالات مغلقة</span>
          <span class="stat-value"><?= $stats['closed'] ?></span>
        </div>
      </div>
      
   <form method="GET" action="cases.php" class="filter-bar">
        <div class="search-wrapper">
          <i class="fas fa-search"></i>
          <input type="text" name="search" id="search-input" placeholder="البحث في الحالات..." value="<?= htmlspecialchars($search) ?>" onchange="this.form.submit()" />
        </div>
        <select class="filter-select" name="status" id="status-filter" onchange="this.form.submit()">
          <option value="">كل الحالات</option>
          <option value="IN_PROGRESS" <?= $status === 'IN_PROGRESS' ? 'selected' : '' ?>>جارية</option>
          <option value="UNDER_REVIEW" <?= $status === 'UNDER_REVIEW' ? 'selected' : '' ?>>تحت المراجعة</option>
          <option value="CLOSED" <?= $status === 'CLOSED' ? 'selected' : '' ?>>مغلقة</option>
        </select>
      </form>
      <div class="cases-list" id="cases-container">
        <?php foreach ($cases as $c):
          $initial = mb_substr($c['client_name'], 0, 1, 'UTF-8');

          $formattedDate = $c['last_session']
            ? date("Y-m-d H:i", strtotime($c['last_session']))
            : 'لا يوجد جلسات';

          $statusMap = [
            'IN_PROGRESS'  => ['label' => 'جارية', 'cls' => 'active', 'icon' => '../images/StatusIcon.svg'],
            'UNDER_REVIEW' => ['label' => 'تحت المراجعة', 'cls' => 'review', 'icon' => '../images/StatusIcon (1).svg'],
            'CLOSED'       => ['label' => 'مغلقة', 'cls' => 'pending', 'icon' => '../images/Calendar.svg']
          ];
          $st = $statusMap[$c['status']] ?? $statusMap['IN_PROGRESS'];
        ?>
          <div class="case-card">
            <div class="avatar"><?= $initial ?></div>
            <div class="case-info">
              <span class="case-name"><?= htmlspecialchars($c['client_name']) ?></span>
              <span class="case-condition">رقم الحالة: #<?= $c['case_id'] ?></span>
            </div>
            <div class="case-doctor"><img src="../images/UserCog.svg"> <?= htmlspecialchars($c['therapist_name']) ?></div>
            <div class="case-date-col">
              <span class="case-date-label">آخر جلسة</span>
              <span class="case-date-val"><img src="../images/Calendar.svg"> <?= $formattedDate ?></span>
            </div>
            <div class="case-progress-col">
              <div class="progress-top">
                <span class="progress-label">التقدم</span>
                <span class="progress-pct"><?= $c['progress'] ?>%</span>
              </div>
              <div class="progress-bar-wrap">
                <div class="progress-bar-fill" style="width:<?= $c['progress'] ?>%;"></div>
              </div>
              <div class="progress-sessions"><?= $c['sessions_count'] ?> جلسة</div>
            </div>
            <div class="badge <?= $st['cls'] ?>">
              <img src="<?= $st['icon'] ?>"> <?= $st['label'] ?>
            </div>
            <button class="btn-details" onclick='viewDetails(<?= json_encode($c) ?>, "<?= $formattedDate ?>")'>عرض التفاصيل</button>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="pagination-bar">
        <div class="pagination-info"><?= htmlspecialchars($paginationInfo) ?></div>
        <div class="pagination-controls">
          <?php if ($page > 1 && $totalPages > 0): ?>
            <a class="page-btn prev-btn" href="<?= htmlspecialchars($casesPageUrl($page - 1)) ?>">السابق</a>
          <?php else: ?>
            <span class="page-btn prev-btn" aria-disabled="true">السابق</span>
          <?php endif; ?>
          <div class="page-numbers">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <a
                href="<?= htmlspecialchars($casesPageUrl($i)) ?>"
                class="page-number-btn<?= $i === $page ? ' active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
          </div>
          <?php if ($page < $totalPages): ?>
            <a class="page-btn next-btn" href="<?= htmlspecialchars($casesPageUrl($page + 1)) ?>">التالي</a>
          <?php else: ?>
            <span class="page-btn next-btn" aria-disabled="true">التالي</span>
          <?php endif; ?>
        </div>
      </div>
    </main>

    <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
  </div>

  <div class="modal-wrap" id="case-modal">
    <div class="modal-overlay" id="modal-overlay"></div>
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-avatar" id="modal-avatar"></div>
        <div class="modal-header-info">
          <h3 id="modal-name"></h3>
          <span id="modal-condition"></span>
        </div>
        <button class="modal-close" onclick="closeModal()"><i class="fas fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="modal-row"><span class="modal-row-label"><i class="fas fa-user-doctor"></i>الأخصائي</span><span class="modal-row-val" id="modal-doctor"></span></div>
        <div class="modal-row"><span class="modal-row-label"><i class="fas fa-calendar-days"></i>آخر جلسة</span><span class="modal-row-val" id="modal-date"></span></div>
        <div class="modal-row"><span class="modal-row-label"><i class="fas fa-layer-group"></i>عدد الجلسات</span><span class="modal-row-val" id="modal-sessions"></span></div>
        <div class="modal-row"><span class="modal-row-label"><i class="fas fa-circle-dot"></i>الحالة</span><span class="badge" id="modal-badge"></span></div>
        <div class="modal-row modal-progress-section">
          <div class="modal-progress-top" style="width: 100%"><span><i class="fas fa-chart-line"></i>نسبة التقدم</span><span class="modal-progress-pct" id="modal-progress-pct"></span></div>
          <div class="modal-bar-wrap" style="width: 100%">
            <div class="modal-bar-fill" id="modal-progress-fill"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="./cases.js"></script>
  <script>
    function viewDetails(data, dateStr) {
      document.getElementById("modal-avatar").textContent = data.client_name.charAt(0);
      document.getElementById("modal-name").textContent = data.client_name;
      document.getElementById("modal-condition").textContent = "رقم الحالة: #" + data.case_id;
      document.getElementById("modal-doctor").textContent = data.therapist_name;
      document.getElementById("modal-date").textContent = dateStr;
      document.getElementById("modal-sessions").textContent = data.sessions_count + " جلسة";
      document.getElementById("modal-progress-pct").textContent = data.progress + "%";
      document.getElementById("modal-progress-fill").style.width = data.progress + "%";

      const badge = document.getElementById("modal-badge");
      const map = {
        'IN_PROGRESS': 'جارية',
        'UNDER_REVIEW': 'تحت المراجعة',
        'CLOSED': 'مغلقة'
      };
      const clsMap = {
        'IN_PROGRESS': 'active',
        'UNDER_REVIEW': 'review',
        'CLOSED': 'pending'
      };

      badge.textContent = map[data.status];
      badge.className = "badge " + (clsMap[data.status] || "active");

      document.getElementById("case-modal").classList.add("open");
      document.body.style.overflow = "hidden";
    }

    function closeModal() {
      document.getElementById("case-modal").classList.remove("open");
      document.body.style.overflow = "";
    }
    document.getElementById("modal-overlay").onclick = closeModal;
  </script>
</body>

</html>