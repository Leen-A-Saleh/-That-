<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'THERAPIST') {
    header("Location: /homepage/index.php");
    exit;
}

require_once __DIR__ . '/../../connection.php';

$user_id      = $_SESSION['user_id'];
$therapist_id = $user_id;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    header('Content-Type: application/json');
    $rid    = (int) ($_POST['result_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $allowed = ['PENDING', 'REVIEWED', 'DISCUSSED'];

    if ($rid <= 0 || !in_array($status, $allowed, true)) {
        echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            UPDATE assessment_results ar
            INNER JOIN cases c ON c.case_id = ar.case_id
            SET ar.status = ?, ar.reviewed_at = IF(? = 'PENDING', NULL, NOW())
            WHERE ar.result_id = ? AND c.therapist_id = ?
        ");
        $stmt->bind_param("ssii", $status, $status, $rid, $therapist_id);
        $stmt->execute();
        $ok = $stmt->affected_rows >= 0;
        $stmt->close();
        echo json_encode(['success' => $ok]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'تعذّر تحديث الحالة']);
    }
    exit;
}

$search        = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$type_filter   = trim($_GET['type'] ?? '');

$statsSql = "
    SELECT
        SUM(CASE WHEN ar.status = 'PENDING'   THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN ar.status = 'REVIEWED'  THEN 1 ELSE 0 END) AS reviewed,
        SUM(CASE WHEN ar.status = 'DISCUSSED' THEN 1 ELSE 0 END) AS discussed,
        COUNT(*) AS total
    FROM assessment_results ar
    INNER JOIN cases c ON c.case_id = ar.case_id
    WHERE c.therapist_id = ?
";
$stmt = $conn->prepare($statsSql);
$stmt->bind_param("i", $therapist_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

$sql = "
    SELECT ar.result_id, ar.trait_score, ar.level, ar.status, ar.created_at,
           a.title AS assessment_title, a.category, a.max_score,
           u.name AS patient_name,
           (
             SELECT ar2.trait_score
             FROM assessment_results ar2
             WHERE ar2.assessment_id = ar.assessment_id
               AND ar2.client_id = ar.client_id
               AND ar2.created_at < ar.created_at
             ORDER BY ar2.created_at DESC
             LIMIT 1
           ) AS prev_score
    FROM assessment_results ar
    INNER JOIN cases c      ON c.case_id = ar.case_id
    INNER JOIN assessments a ON a.assessment_id = ar.assessment_id
    INNER JOIN clients cl   ON cl.client_id = ar.client_id
    INNER JOIN users u      ON u.user_id    = cl.client_id
    WHERE c.therapist_id = ?
";
$types = "i";
$params = [$therapist_id];

if ($search !== '') {
    $sql .= " AND (u.name LIKE ? OR a.title LIKE ?)";
    $like = '%' . $search . '%';
    $types .= "ss";
    $params[] = $like;
    $params[] = $like;
}
if (in_array($status_filter, ['PENDING','REVIEWED','DISCUSSED'], true)) {
    $sql .= " AND ar.status = ?";
    $types .= "s";
    $params[] = $status_filter;
}
if ($type_filter !== '') {
    $sql .= " AND a.category = ?";
    $types .= "s";
    $params[] = $type_filter;
}
$sql .= " ORDER BY ar.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$resultsRes = $stmt->get_result();
$results = [];
while ($row = $resultsRes->fetch_assoc()) {
    $results[] = $row;
}
$stmt->close();

$stmt = $conn->prepare("
    SELECT DISTINCT a.category
    FROM assessment_results ar
    INNER JOIN cases c       ON c.case_id = ar.case_id
    INNER JOIN assessments a ON a.assessment_id = ar.assessment_id
    WHERE c.therapist_id = ? AND a.category IS NOT NULL AND a.category <> ''
    ORDER BY a.category
");
$stmt->bind_param("i", $therapist_id);
$stmt->execute();
$catRes = $stmt->get_result();
$categories = [];
while ($row = $catRes->fetch_assoc()) {
    $categories[] = $row['category'];
}
$stmt->close();

function levelLabel($level) {
    return [
        'LOW'    => 'منخفض',
        'MEDIUM' => 'متوسط',
        'HIGH'   => 'مرتفع',
        'SEVERE' => 'شديد',
    ][$level] ?? ($level ?: '—');
}
function levelClass($level) {
    return [
        'LOW'    => 'pill-intensity-low',
        'MEDIUM' => 'pill-intensity-mid',
        'HIGH'   => 'pill-intensity-high',
        'SEVERE' => 'pill-intensity-up',
    ][$level] ?? 'pill-intensity-mid';
}
function statusLabel($s) {
    return [
        'PENDING'   => 'بانتظار المراجعة',
        'REVIEWED'  => 'تمت المراجعة',
        'DISCUSSED' => 'تمت المناقشة',
    ][$s] ?? $s;
}
function statusClass($s) {
    return [
        'PENDING'   => 'status-wait',
        'REVIEWED'  => 'status-review',
        'DISCUSSED' => 'status-discuss',
    ][$s] ?? 'status-wait';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <title>مراجعة نتائج الاختبار</title>
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

    <?php $activePage = 'review'; ?>
    <?php include "../layouts/sidebar.php"; ?>

    <main class="main">
      <header class="main-header">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="فتح القائمة">
          <i class="fa-solid fa-bars"></i>
        </button>
        <h1 class="page-title">مراجعة نتائج الاختبار</h1>
      </header>

      <section class="stats-row">
        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">بانتظار المراجعة</div>
            <div class="stat-value"><?= (int)($stats['pending'] ?? 0) ?></div>
          </div>
          <div class="stat-icon-red">
            <i class="fa-solid fa-clock"></i>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">تمت المراجعة</div>
            <div class="stat-value"><?= (int)($stats['reviewed'] ?? 0) ?></div>
          </div>
          <div class="stat-icon-green">
            <i class="fa-regular fa-circle-check"></i>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">تمت المناقشة</div>
            <div class="stat-value"><?= (int)($stats['discussed'] ?? 0) ?></div>
          </div>
          <div class="stat-icon-blue">
            <i class="fa-solid fa-file-lines"></i>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">إجمالي الاختبارات</div>
            <div class="stat-value"><?= (int)($stats['total'] ?? 0) ?></div>
          </div>
          <div class="stat-icon-teal">
            <i class="fa-solid fa-file-lines"></i>
          </div>
        </div>
      </section>

      <section class="add-activity-section">
        <div class="container">
          <form method="GET" class="search-container" id="filterForm">
            <div class="search-box">
              <i class="fa-solid fa-magnifying-glass search-icon"></i>
              <input type="text"
                     class="search-input"
                     name="search"
                     placeholder="البحث باسم المريض أو الاختبار..."
                     value="<?= htmlspecialchars($search) ?>"
                     id="searchInput">
            </div>

            <div class="filter-btn">
              <i class="fa-solid fa-filter"></i>
              <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                <option value="">كل الحالات</option>
                <option value="PENDING"   <?= $status_filter === 'PENDING'   ? 'selected' : '' ?>>بانتظار المراجعة</option>
                <option value="REVIEWED"  <?= $status_filter === 'REVIEWED'  ? 'selected' : '' ?>>تمت المراجعة</option>
                <option value="DISCUSSED" <?= $status_filter === 'DISCUSSED' ? 'selected' : '' ?>>تمت المناقشة</option>
              </select>
            </div>

            <div class="filter-btn">
              <i class="fa-solid fa-filter"></i>
              <select name="type" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                <option value="">كل الأنواع</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= htmlspecialchars($cat) ?>" <?= $type_filter === $cat ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </form>
        </div>
      </section>

      <section class="tests-section">
        <div class="table-wrapper">
          <div class="cardo">
            <table>
              <thead>
                <tr>
                  <th>المريض</th>
                  <th>الاختبار</th>
                  <th>النوع</th>
                  <th>النتيجة</th>
                  <th>الشدة</th>
                  <th>الاتجاه</th>
                  <th class="hide-sm">التاريخ</th>
                  <th>الحالة</th>
                  <th>الإجراءات</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($results)): ?>
                  <tr>
                    <td colspan="9" class="no-results">لا توجد نتائج مطابقة</td>
                  </tr>
                <?php else: foreach ($results as $r):
                  $score    = (float) $r['trait_score'];
                  $maxScore = (float) ($r['max_score'] ?? 0);
                  $pct      = ($maxScore > 0) ? round(($score / $maxScore) * 100) : null;

                  $prev     = $r['prev_score'];
                  if ($prev === null) {
                      $trendClass = 'stable';
                      $trendIcon  = 'fa-minus';
                      $trendLabel = 'جديد';
                  } elseif ($score > $prev) {
                      $trendClass = 'up';
                      $trendIcon  = 'fa-arrow-trend-up';
                      $trendLabel = 'ارتفاع';
                  } elseif ($score < $prev) {
                      $trendClass = 'down';
                      $trendIcon  = 'fa-arrow-trend-down';
                      $trendLabel = 'تحسن';
                  } else {
                      $trendClass = 'stable';
                      $trendIcon  = 'fa-minus';
                      $trendLabel = 'مستقر';
                  }
                ?>
                <tr>
                  <td>
                    <span class="patient-name"><?= htmlspecialchars($r['patient_name']) ?></span>
                  </td>
                  <td>
                    <span class="test-title"><?= htmlspecialchars($r['assessment_title']) ?></span>
                  </td>
                  <td>
                    <span class="pill pill-type"><?= htmlspecialchars($r['category'] ?? '—') ?></span>
                  </td>
                  <td>
                    <div class="result">
                      <span class="result-score">
                        <?= rtrim(rtrim(number_format($score, 2, '.', ''), '0'), '.') ?><?= $maxScore > 0 ? '/' . rtrim(rtrim(number_format($maxScore, 2, '.', ''), '0'), '.') : '' ?>
                      </span>
                      <?php if ($pct !== null): ?>
                        <span class="result-percent"><?= $pct ?>%</span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td>
                    <span class="pill <?= levelClass($r['level']) ?>"><?= levelLabel($r['level']) ?></span>
                  </td>
                  <td>
                    <span class="trend <?= $trendClass ?>">
                      <i class="fa-solid <?= $trendIcon ?>"></i>
                      <?= $trendLabel ?>
                    </span>
                  </td>
                  <td class="hide-sm">
                    <div class="date-cell">
                      <i class="fa-regular fa-calendar"></i>
                      <span><?= date('Y/m/d', strtotime($r['created_at'])) ?></span>
                    </div>
                  </td>
                  <td>
                    <span class="status-pill <?= statusClass($r['status']) ?>">
                      <?= statusLabel($r['status']) ?>
                    </span>
                  </td>
                  <td class="actions">
                    <button class="action-btn action-view"
                            data-id="<?= $r['result_id'] ?>"
                            data-name="<?= htmlspecialchars($r['patient_name']) ?>"
                            data-title="<?= htmlspecialchars($r['assessment_title']) ?>"
                            data-category="<?= htmlspecialchars($r['category'] ?? '—') ?>"
                            data-score="<?= rtrim(rtrim(number_format($score, 2, '.', ''), '0'), '.') ?>"
                            data-max="<?= $maxScore > 0 ? rtrim(rtrim(number_format($maxScore, 2, '.', ''), '0'), '.') : '' ?>"
                            data-percent="<?= $pct !== null ? $pct : '' ?>"
                            data-level="<?= htmlspecialchars($r['level'] ?? '') ?>"
                            data-level-label="<?= htmlspecialchars(levelLabel($r['level'])) ?>"
                            data-level-class="<?= levelClass($r['level']) ?>"
                            data-trend-class="<?= $trendClass ?>"
                            data-trend-icon="<?= $trendIcon ?>"
                            data-trend-label="<?= $trendLabel ?>"
                            data-prev="<?= $prev !== null ? rtrim(rtrim(number_format((float)$prev, 2, '.', ''), '0'), '.') : '' ?>"
                            data-date="<?= date('Y/m/d', strtotime($r['created_at'])) ?>"
                            data-status="<?= $r['status'] ?>"
                            data-status-label="<?= htmlspecialchars(statusLabel($r['status'])) ?>"
                            data-status-class="<?= statusClass($r['status']) ?>">
                      <i class="fa-regular fa-eye"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <div class="modal-overlay" id="resultModal">
        <div class="modal-card">
          <div class="modal-header">
            <div class="modal-titles">
              <h3 id="modalPatient"></h3>
              <p id="modalTest"></p>
            </div>
            <button class="modal-close" data-close aria-label="إغلاق">&times;</button>
          </div>

          <div class="modal-body">
            <div class="detail-grid">
              <div class="detail-item">
                <div class="detail-label">النوع</div>
                <div class="detail-value"><span id="modalCategory" class="pill pill-type"></span></div>
              </div>

              <div class="detail-item">
                <div class="detail-label">النتيجة</div>
                <div class="detail-value">
                  <span id="modalScore" class="result-score"></span>
                  <span id="modalPercent" class="result-percent"></span>
                </div>
              </div>

              <div class="detail-item">
                <div class="detail-label">الشدة</div>
                <div class="detail-value"><span id="modalLevel" class="pill"></span></div>
              </div>

              <div class="detail-item">
                <div class="detail-label">الاتجاه</div>
                <div class="detail-value">
                  <span id="modalTrend" class="trend">
                    <i id="modalTrendIcon" class="fa-solid"></i>
                    <span id="modalTrendLabel"></span>
                  </span>
                  <span id="modalPrev" class="prev-score"></span>
                </div>
              </div>

              <div class="detail-item">
                <div class="detail-label">التاريخ</div>
                <div class="detail-value">
                  <span class="date-cell"><i class="fa-regular fa-calendar"></i> <span id="modalDate"></span></span>
                </div>
              </div>

              <div class="detail-item">
                <div class="detail-label">الحالة</div>
                <div class="detail-value"><span id="modalStatus" class="status-pill"></span></div>
              </div>
            </div>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-status" data-set-status="REVIEWED">
              <i class="fa-regular fa-circle-check"></i> تمت المراجعة
            </button>
            <button type="button" class="btn-status btn-status-blue" data-set-status="DISCUSSED">
              <i class="fa-solid fa-file-lines"></i> تمت المناقشة
            </button>
            <button type="button" class="btn-cancel" data-close>إغلاق</button>
          </div>
        </div>
      </div>

      <?php include "../layouts/footer.php"; ?>
    </main>
  </div>
  <script src="main.js"></script>
  <script src="../sidebar-toggle.js"></script>
</body>

</html>
