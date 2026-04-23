<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'THERAPIST') {
    header("Location: /homepage/index.php");
    exit;
}

require_once __DIR__ . '/../../connection.php';

$user_id = $_SESSION['user_id'];
$therapist_id = $user_id;

$current_search = trim($_GET['search'] ?? '');

$sql = "
    SELECT s.session_id, s.appointment_id, s.case_id, s.start_time, s.end_time,
           u.name AS client_name, c.date_of_birth,
           cs.title AS case_title, cs.status AS case_status,
           a.mode, a.duration_min,
           sn.session_goals, sn.mood, sn.topics, sn.techniques,
           sn.progress, sn.risk_assessment, sn.therapist_notes,
           sn.homework, sn.next_plan
    FROM sessions s
    JOIN cases cs ON s.case_id = cs.case_id
    JOIN clients c ON cs.client_id = c.client_id
    JOIN users u ON c.client_id = u.user_id
    LEFT JOIN appointments a ON s.appointment_id = a.appointment_id
    LEFT JOIN session_notes sn ON sn.session_id = s.session_id
    WHERE cs.therapist_id = ?
";
$types = "i";
$params = [$therapist_id];

if ($current_search !== '') {
    $sql .= " AND u.name LIKE ?";
    $types .= "s";
    $params[] = "%$current_search%";
}

$sql .= " ORDER BY s.start_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_count = count($sessions);

function firstLetter($name) {
    return mb_substr(trim($name), 0, 1, 'UTF-8');
}

function formatTimeAr($datetime) {
    $hour = (int) date('G', strtotime($datetime));
    $minute = date('i', strtotime($datetime));
    $period = $hour >= 12 ? 'مساءً' : 'صباحاً';
    $hour12 = $hour % 12 ?: 12;
    return sprintf('%02d:%s %s', $hour12, $minute, $period);
}

function calcDuration($start, $end) {
    if (!$end) return null;
    $diff = strtotime($end) - strtotime($start);
    return (int) round($diff / 60);
}

function truncateText($text, $length = 120) {
    $text = trim($text);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length, 'UTF-8') . '...';
}

$mode_labels = ['ONLINE' => 'أونلاين', 'IN_CENTER' => 'حضوري'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <title>ملاحظات الجلسات</title>
  <link rel="icon" type="./img/Silver.png" href="img/Silver.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

  <link rel="stylesheet" href="../thirapist.css">
  <link rel="stylesheet" href="../total.css"/>
  <link rel="stylesheet" href="style.css" />
</head>

<body>

  <div class="app">
    <?php $activePage = 'notes'; ?>
    <?php include "../layouts/sidebar.php"; ?>

    <main class="main">
      <header class="main-header">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="فتح القائمة">
          <i class="fa-solid fa-bars"></i>
        </button>
        <h1 class="page-title">ملاحظات الجلسات</h1>
        <button class="status-btn">
          <span class="status-dot"></span>
          متصل
        </button>
      </header>

      <!-- Top bar -->
      <section class="notes-topbar">
        <section class="notes-summary">
          <div class="summary-title">ملاحظات الجلسات</div>
          <div class="summary-count">إجمالي <?= $total_count ?> ملاحظة</div>
        </section>

        <section class="notes-toolbar">
          <form id="searchForm" method="GET" class="search-box">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input id="noteSearch" name="search" type="text" placeholder="البحث باسم المريض..." value="<?= htmlspecialchars($current_search) ?>">
          </form>
        </section>
      </section>

      <!-- Notes list -->
      <div class="notes-list">

        <?php if (empty($sessions)): ?>
          <p class="empty-state">
            <i class="fa-regular fa-note-sticky"></i>
            لا توجد ملاحظات جلسات حالياً
          </p>
        <?php else: ?>
          <?php foreach ($sessions as $sess):
              $duration = calcDuration($sess['start_time'], $sess['end_time']) ?? (int)($sess['duration_min'] ?? 0);
              $mode = $sess['mode'] ?? '';
              $previewParts = array_filter([
                  $sess['session_goals'] ?? '',
                  $sess['mood'] ?? '',
                  $sess['topics'] ?? '',
              ]);
              $notePreview = truncateText(implode(' — ', $previewParts));
          ?>
          <article class="note-card">
            <header class="note-card-header">
              <div class="note-person">
                <div class="avatar-circle"><?= firstLetter($sess['client_name']) ?></div>
                <div>
                  <div class="note-client-name"><?= htmlspecialchars($sess['client_name']) ?></div>
                  <div class="note-case-title"><?= htmlspecialchars($sess['case_title'] ?? '') ?></div>
                </div>
              </div>
              <div class="note-badges">
                <?php if ($mode): ?>
                <span class="note-badge badge-mode"><?= $mode_labels[$mode] ?? $mode ?></span>
                <?php endif; ?>
                <span class="note-badge badge-completed"><i class="fa-solid fa-circle-check"></i> مكتملة</span>
              </div>
            </header>

            <div class="note-card-body">
              <div class="note-info-row">
                <div class="note-info-item">
                  <i class="fa-solid fa-calendar-days" style="color:#30B7C4;"></i>
                  <span><?= date('Y-m-d', strtotime($sess['start_time'])) ?></span>
                </div>
                <div class="note-info-item">
                  <i class="fa-regular fa-clock" style="color:#30B7C4;"></i>
                  <span><?= formatTimeAr($sess['start_time']) ?></span>
                </div>
                <div class="note-info-item">
                  <i class="fa-solid fa-hourglass-half" style="color:#30B7C4;"></i>
                  <span><?= $duration ?> دقيقة</span>
                </div>
              </div>

              <?php if ($notePreview): ?>
              <div class="note-preview">
                <?= htmlspecialchars($notePreview) ?>
              </div>
              <?php endif; ?>
            </div>

            <footer class="note-card-footer">
              <button class="btn-outline btn-view-note"
                data-name="<?= htmlspecialchars($sess['client_name']) ?>"
                data-letter="<?= firstLetter($sess['client_name']) ?>"
                data-case="<?= htmlspecialchars($sess['case_title'] ?? '') ?>"
                data-date="<?= date('Y-m-d', strtotime($sess['start_time'])) ?>"
                data-time="<?= formatTimeAr($sess['start_time']) ?>"
                data-duration="<?= $duration ?> دقيقة"
                data-mode="<?= $mode_labels[$mode] ?? $mode ?>"
                data-goals="<?= htmlspecialchars($sess['session_goals'] ?? '') ?>"
                data-mood="<?= htmlspecialchars($sess['mood'] ?? '') ?>"
                data-topics="<?= htmlspecialchars($sess['topics'] ?? '') ?>"
                data-techniques="<?= htmlspecialchars($sess['techniques'] ?? '') ?>"
                data-progress="<?= htmlspecialchars($sess['progress'] ?? '') ?>"
                data-risk="<?= htmlspecialchars($sess['risk_assessment'] ?? '') ?>"
                data-therapist-notes="<?= htmlspecialchars($sess['therapist_notes'] ?? '') ?>"
                data-homework="<?= htmlspecialchars($sess['homework'] ?? '') ?>"
                data-next-plan="<?= htmlspecialchars($sess['next_plan'] ?? '') ?>"
              ><i class="fa-solid fa-eye"></i> عرض الملاحظات</button>
            </footer>
          </article>
          <?php endforeach; ?>
        <?php endif; ?>

      </div>

      <?php include "../layouts/footer.php"; ?>
    </main>
  </div>

  <!-- View Note Modal -->
  <div class="modal-overlay" id="noteModal">
    <div class="modal-card">
      <header class="modal-header">
        <h2 class="modal-title">تفاصيل ملاحظات الجلسة</h2>
        <button class="modal-close" id="noteModalClose">&times;</button>
      </header>
      <div class="modal-body">
        <div class="modal-client">
          <div class="avatar-circle" id="noteModalAvatar"></div>
          <div>
            <div class="note-client-name" id="noteModalName"></div>
            <div class="note-case-title" id="noteModalCase"></div>
          </div>
        </div>

        <div class="modal-grid">
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-calendar-days" style="color:#30B7C4;"></i> التاريخ</div>
            <div class="info-value" id="noteModalDate"></div>
          </div>
          <div class="modal-info">
            <div class="info-label"><i class="fa-regular fa-clock" style="color:#30B7C4;"></i> الوقت</div>
            <div class="info-value" id="noteModalTime"></div>
          </div>
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-hourglass-half" style="color:#30B7C4;"></i> المدة</div>
            <div class="info-value" id="noteModalDuration"></div>
          </div>
          <div class="modal-info">
            <div class="info-label"><i class="fa-solid fa-video" style="color:#30B7C4;"></i> نوع الاجتماع</div>
            <div class="info-value" id="noteModalMode"></div>
          </div>
        </div>

        <div id="noteModalFields">
          <div class="modal-note-field" id="noteFieldGoals">
            <div class="modal-section-title">أهداف الجلسة</div>
            <div class="modal-notes-content" id="noteModalGoals"></div>
          </div>
          <div class="modal-note-field" id="noteFieldMood">
            <div class="modal-section-title">الحالة المزاجية</div>
            <div class="modal-notes-content" id="noteModalMood"></div>
          </div>
          <div class="modal-note-field" id="noteFieldTopics">
            <div class="modal-section-title">المواضيع المناقشة</div>
            <div class="modal-notes-content" id="noteModalTopics"></div>
          </div>
          <div class="modal-note-field" id="noteFieldTechniques">
            <div class="modal-section-title">التقنيات المستخدمة</div>
            <div class="modal-notes-content" id="noteModalTechniques"></div>
          </div>
          <div class="modal-note-field" id="noteFieldProgress">
            <div class="modal-section-title">تقدم المريض</div>
            <div class="modal-notes-content" id="noteModalProgress"></div>
          </div>
          <div class="modal-note-field" id="noteFieldRisk">
            <div class="modal-section-title">تقييم المخاطر</div>
            <div class="modal-notes-content" id="noteModalRisk"></div>
          </div>
          <div class="modal-note-field" id="noteFieldTherapist">
            <div class="modal-section-title">ملاحظات المعالج</div>
            <div class="modal-notes-content" id="noteModalTherapist"></div>
          </div>
          <div class="modal-note-field" id="noteFieldHomework">
            <div class="modal-section-title">الواجبات المنزلية</div>
            <div class="modal-notes-content" id="noteModalHomework"></div>
          </div>
          <div class="modal-note-field" id="noteFieldNextPlan">
            <div class="modal-section-title">خطة الجلسة القادمة</div>
            <div class="modal-notes-content" id="noteModalNextPlan"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="main.js"></script>
  <script src="../sidebar-toggle.js"></script>
</body>
</html>
