<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'THERAPIST') {
    header("Location: /homepage/index.php");
    exit;
}

require_once __DIR__ . '/../../connection.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';

// Get therapist_id
$stmt = $conn->prepare("SELECT therapist_id FROM therapists WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$therapist_row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$therapist_id = $therapist_row['therapist_id'] ?? 0;

// Read filter & search from GET params
$current_filter = $_GET['filter'] ?? 'all';
$current_search = trim($_GET['search'] ?? '');

// Build query with filters
$sql = "
    SELECT cs.case_id, cs.title, cs.status, cs.priority, cs.created_at,
           u.name AS client_name, c.date_of_birth,
           COUNT(s.session_id) AS total_sessions,
           MAX(s.start_time) AS last_session
    FROM cases cs
    JOIN clients c ON cs.client_id = c.client_id
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN sessions s ON s.case_id = cs.case_id
    WHERE cs.therapist_id = ?
";
$types = "i";
$params = [$therapist_id];

if ($current_filter === 'active') {
    $sql .= " AND cs.status IN ('NEW','IN_ASSESSMENT','IN_THERAPY')";
} elseif (in_array($current_filter, ['low', 'medium', 'high'])) {
    $sql .= " AND cs.priority = ?";
    $types .= "s";
    $params[] = strtoupper($current_filter);
}

if ($current_search !== '') {
    $sql .= " AND u.name LIKE ?";
    $types .= "s";
    $params[] = "%$current_search%";
}

$sql .= " GROUP BY cs.case_id ORDER BY cs.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$cases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_count = count($cases);

// Filter labels for the button text
$filter_labels = [
    'all' => 'جميع الحالات',
    'active' => 'نشطة',
    'low' => 'حدة منخفضة',
    'medium' => 'حدة متوسطة',
    'high' => 'حدة عالية',
];

// Helpers
function calcAge($dob) {
    if (!$dob) return null;
    return (int) date_diff(date_create($dob), date_create('today'))->y;
}

function firstLetterCases($name) {
    return mb_substr(trim($name), 0, 1, 'UTF-8');
}

$priority_labels = ['LOW' => 'منخفضة', 'MEDIUM' => 'متوسطة', 'HIGH' => 'عالية'];
$priority_classes = ['LOW' => 'severity-low', 'MEDIUM' => 'severity-medium', 'HIGH' => 'severity-high'];
$status_labels = [
    'NEW' => 'جديدة', 'IN_ASSESSMENT' => 'نشطة', 'IN_THERAPY' => 'نشطة',
    'ON_HOLD' => 'معلقة', 'RECOVERED' => 'متعافية'
];
$status_classes = [
    'NEW' => 'status-active', 'IN_ASSESSMENT' => 'status-active', 'IN_THERAPY' => 'status-active',
    'ON_HOLD' => 'status-hold', 'RECOVERED' => 'status-recovered'
];
$status_progress = [
    'NEW' => 10, 'IN_ASSESSMENT' => 35, 'IN_THERAPY' => 60,
    'ON_HOLD' => 50, 'RECOVERED' => 100
];
// Map status to filter data-status value
$status_filter = [
    'NEW' => 'active', 'IN_ASSESSMENT' => 'active', 'IN_THERAPY' => 'active',
    'ON_HOLD' => 'hold', 'RECOVERED' => 'recovered'
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>الحالات - ذات للاستشارات النفسية</title>
    <link rel="icon" type="./img/Silver.png" href="img/Silver.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

 <link rel="stylesheet" href="../thirapist.css">
  <link rel="stylesheet" href="../total.css"/>
    <link rel="stylesheet" href="style.css">
  
</head>

<body>

    <div class="app">

   <?php $activePage = 'cases'; ?>
<?php include "../layouts/sidebar.php"; ?>

        <!-- المحتوى الرئيسي -->
        <main class="main">

            <!-- شريط الحالة أعلى الصفحة -->
            <header class="main-header">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="فتح القائمة" >
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="page-title">الحالات</h1>
                <button class="status-btn">
                    <span class="status-dot"></span>
                    متصل
                </button>
            </header>

            <section class="cases-topbar">
                <!-- ملخص -->
                <section class="cases-summary">
                    <div class="summary-title">حالاتي</div>
                    <div class="summary-count">إجمالي <?= $total_count ?> حالة</div>
                </section>

                <!-- شريط الفلتر + البحث -->
                <section class="cases-toolbar">
                    <button class="filter-btn" id="filterToggle" type="button">
                        <i class="fa-solid fa-filter"></i>
                        <span id="filterLabel"><?= $filter_labels[$current_filter] ?? 'جميع الحالات' ?></span>
                        <i class="fa-solid fa-chevron-down chevron"></i>
                    </button>

                    <form id="searchForm" method="GET" class="search-box">
                        <input type="hidden" name="filter" value="<?= htmlspecialchars($current_filter) ?>">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input id="caseSearch" name="search" type="text" placeholder="البحث عن حالة..." value="<?= htmlspecialchars($current_search) ?>">
                    </form>

                    <!-- قائمة الفلاتر -->
                    <div class="filter-menu" id="filterMenu">
                        <?php
                        $filters = ['all' => 'جميع الحالات', 'active' => 'نشطة', 'low' => 'حدة منخفضة', 'medium' => 'حدة متوسطة', 'high' => 'حدة عالية'];
                        foreach ($filters as $key => $label): ?>
                            <a href="?filter=<?= $key ?>&search=<?= urlencode($current_search) ?>" class="filter-item <?= $current_filter === $key ? 'active' : '' ?>"><?= $label ?></a>
                        <?php endforeach; ?>
                    </div>
                </section>
            </section>

            <section class="cases-grid">

                <?php if (empty($cases)): ?>
                    <p class="muted" style="text-align:center; padding:40px; grid-column: 1/-1;">لا توجد حالات حالياً</p>
                <?php else: ?>
                    <?php foreach ($cases as $case):
                        $age = calcAge($case['date_of_birth']);
                        $progress = $status_progress[$case['status']] ?? 0;
                        $priorityLower = strtolower($case['priority']);
                        $filterStatus = $status_filter[$case['status']] ?? 'active';
                    ?>
                    <article class="case-card"
                        data-name="<?= htmlspecialchars($case['client_name']) ?>"
                        data-urgency="<?= $priorityLower ?>"
                        data-status="<?= $filterStatus ?>">
                        <header class="case-card-header">
                            <div class="case-avatar"><?= firstLetterCases($case['client_name']) ?></div>
                            <div class="case-header-text">
                                <div class="case-name"><?= htmlspecialchars($case['client_name']) ?></div>
                                <?php if ($age): ?>
                                    <div class="case-age">عمر <?= $age ?> سنة</div>
                                <?php endif; ?>
                            </div>
                            <span class="severity-badge <?= $priority_classes[$case['priority']] ?? 'severity-medium' ?>"><?= $priority_labels[$case['priority']] ?? 'متوسطة' ?></span>
                        </header>

                        <div class="case-body">
                            <div class="case-diagnosis-label">التشخيص:</div>
                            <div class="case-diagnosis"><?= htmlspecialchars($case['title']) ?></div>

                            <div class="case-progress">
                                <div class="progress-top">
                                    <span class="progress-label">التقدم العلاجي</span>
                                    <span class="progress-percent"><?= $progress ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                                </div>
                            </div>
                        </div>

                        <footer class="case-footer">
                            <div class="case-meta">
                                <div class="meta-label">إجمالي الجلسات</div>
                                <div class="meta-value"><?= (int)$case['total_sessions'] ?></div>
                            </div>
                            <div class="case-meta">
                                <div class="meta-label">آخر جلسة</div>
                                <div class="meta-value"><?= $case['last_session'] ? date('Y-m-d', strtotime($case['last_session'])) : 'لا يوجد' ?></div>
                            </div>

                            <span class="status-pill <?= $status_classes[$case['status']] ?? 'status-active' ?>"><?= $status_labels[$case['status']] ?? $case['status'] ?></span>
                        </footer>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>

            </section>

 <?php include "../layouts/footer.php"; ?>

        </main>
    </div>

    <script src="main.js"></script>
    <script src="../sidebar-toggle.js"></script>
</body>

</html>