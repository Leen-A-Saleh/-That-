<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'THERAPIST') {
    header("Location: /homepage/index.php");
    exit;
}

require_once __DIR__ . '/../../connection.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';
$therapist_id = $user_id;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appointment_id'])) {
    $appt_id = (int) $_POST['appointment_id'];
    $action = $_POST['action'];

    if ($action === 'accept') {
        $stmt = $conn->prepare("UPDATE appointments SET status = 'CONFIRMED' WHERE appointment_id = ? AND therapist_id = ? AND status = 'REQUESTED'");
        $stmt->bind_param("ii", $appt_id, $therapist_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE appointments SET status = 'CANCELLED', cancel_reason = 'رفض من قبل المعالج' WHERE appointment_id = ? AND therapist_id = ? AND status = 'REQUESTED'");
        $stmt->bind_param("ii", $appt_id, $therapist_id);
        $stmt->execute();
        $stmt->close();
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT a.appointment_id, a.date_time, a.mode, a.created_at, a.case_id,
           u.name AS client_name, c.date_of_birth,
           cs.title AS case_title, cs.priority,
           (SELECT COUNT(*) FROM sessions s WHERE s.case_id = a.case_id) AS total_sessions
    FROM appointments a
    JOIN clients c ON a.client_id = c.client_id
    JOIN users u ON c.client_id = u.user_id
    LEFT JOIN cases cs ON a.case_id = cs.case_id
    WHERE a.therapist_id = ? AND a.status = 'REQUESTED'
    ORDER BY a.created_at DESC
");
$stmt->bind_param("i", $therapist_id);
$stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_count = count($requests);
$urgent_count = 0;
foreach ($requests as $r) {
    if (($r['priority'] ?? '') === 'HIGH') $urgent_count++;
}

function calcAge($dob) {
    if (!$dob) return null;
    return (int) date_diff(date_create($dob), date_create('today'))->y;
}

function firstLetter($name) {
    return mb_substr(trim($name), 0, 1, 'UTF-8');
}

function formatTime($datetime) {
    $hour = (int) date('G', strtotime($datetime));
    $minute = date('i', strtotime($datetime));
    $period = $hour >= 12 ? 'مساءً' : 'صباحاً';
    $hour12 = $hour % 12 ?: 12;
    return sprintf('%02d:%s %s', $hour12, $minute, $period);
}

$priority_labels = ['LOW' => 'عادي', 'MEDIUM' => 'متوسطة', 'HIGH' => 'عاجل'];
$priority_classes = ['LOW' => 'severity-low', 'MEDIUM' => 'severity-medium', 'HIGH' => 'severity-high'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>طلبات المواعيد</title>
  <link rel="icon" type="./img/Silver.png" href="img/Silver.png">

  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
    <?php $activePage = 'requests'; ?>
    <?php include "../layouts/sidebar.php"; ?>


    <main class="main">

      <header class="main-header">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="فتح القائمة">
          <i class="fa-solid fa-bars"></i>
        </button>
        <h1 class="page-title">طلبات المواعيد </h1>
        <button class="status-btn">
          <span class="status-dot"></span>
          متصل
        </button>
      </header>
      <section class="requests-wrapper dash-card">

        <section class="cases-summary">
          <div class="cases-summary-info">
            <div class="summary-title">طلبات المواعيد </div>
            <div class="summary-count"><?= $total_count ?> طلب في الانتظار</div>
          </div>

          <?php if ($urgent_count > 0): ?>
          <button id="urgentToggle" class="urgent-pill active">
            <span class="urgent-count"><?= $urgent_count ?></span>
            <span> طلب عاجل </span>
          </button>
          <?php endif; ?>
        </section>

        <div class="requests-list">

          <?php if (empty($requests)): ?>
            <p class="muted" style="text-align:center; padding:40px;">لا توجد طلبات مواعيد حالياً</p>
          <?php else: ?>
            <?php foreach ($requests as $req):
                $age = calcAge($req['date_of_birth']);
                $priority = $req['priority'] ?? 'MEDIUM';
                $isUrgent = $priority === 'HIGH';
                $isNewPatient = (int)$req['total_sessions'] === 0;
                $apptDate = date('Y-m-d', strtotime($req['date_time']));
                $apptTime = formatTime($req['date_time']);
                $requestDate = date('d-m-Y', strtotime($req['created_at']));
                $requestTime = formatTime($req['created_at']);

                if ($isUrgent) {
                    $cardClass = 'request-card-urgent';
                } elseif ($priority === 'MEDIUM') {
                    $cardClass = 'request-card-mid';
                } else {
                    $cardClass = 'request-card';
                }

                if ($isNewPatient) {
                    $sessionType = 'جلسة استشارية';
                } else {
                    $sessionType = 'جلسة متابعة';
                }
            ?>
          <article class="<?= $cardClass ?>" data-urgent="<?= $isUrgent ? 'true' : 'false' ?>" data-status="pending" data-id="<?= $req['appointment_id'] ?>">
            <header class="request-header">
              <div class="request-main">
                <div class="request-person">
                  <div class="avatar-circle"><?= firstLetter($req['client_name']) ?></div>
                  <div>
                    <div class="request-name"><?= htmlspecialchars($req['client_name']) ?></div>
                    <?php if ($age): ?>
                    <div class="request-type"><?= $age ?> سنة</div>
                    <?php endif; ?>
                  </div>
                  <?php if ($isNewPatient): ?>
                  <button class="patient-type">مريض جديد</button>
                  <?php endif; ?>
                  <span class="severity-badge <?= $priority_classes[$priority] ?? 'severity-medium' ?>"><?= $priority_labels[$priority] ?? 'متوسطة' ?></span>
                </div>
              </div>
            </header>
            <div class="request-info-row">
              <div class="info-block">
                <div class="info-label"> <i class="fa-solid fa-calendar-days" style="color: #30B7C4;"></i> التاريخ المفضل </div>
                <div class="info-value"><?= $apptDate ?></div>
              </div>
              <div class="info-block">
                <div class="info-label"> <i class="fa-slab-press fa-regular fa-clock" style="color:#30B7C4"></i> الوقت المفضل </div>
                <div class="info-value"><?= $apptTime ?></div>
              </div>
            </div>
            <div class="info-block">
              <div class="info-label">نوع الجلسة</div>
              <div class="info-value"> <?= $sessionType ?></div>
            </div>
            <?php if (!empty($req['case_title'])): ?>
            <div class="info-block">
              <div class="info-label"> سبب الطلب</div>
              <div class="info-value">
                <?= htmlspecialchars($req['case_title']) ?>
              </div>
            </div>
            <?php endif; ?>
            <div class="request-meta">
              ⓘ تاريخ الطلب: <?= $requestDate ?> • الساعة <?= $requestTime ?>
            </div>
            <footer class="request-footer">
              <button class="btn-primary btn-accept" data-id="<?= $req['appointment_id'] ?>">قبول الطلب</button>
              <button class="btn-outline btn-reject" data-id="<?= $req['appointment_id'] ?>">رفض الطلب</button>
            </footer>
          </article>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>

      </section>


      <?php include "../layouts/footer.php"; ?>

    </main>
  </div>

  <script src="main.js"></script>
  <script src="../sidebar-toggle.js"></script>
</body>

</html>
