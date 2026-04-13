<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'THERAPIST') {
    header("Location: /homepage/index.php");
    exit;
}

require_once __DIR__ . '/../../connection.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';

// Get therapist_id from user_id
$stmt = $conn->prepare("SELECT therapist_id FROM therapists WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$therapist_row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$therapist_id = $therapist_row['therapist_id'] ?? 0;

// Total cases
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM cases WHERE therapist_id = ?");
$stmt->bind_param("i", $therapist_id);
$stmt->execute();
$total_cases = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Active cases (not ON_HOLD or RECOVERED)
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM cases WHERE therapist_id = ? AND status IN ('NEW','IN_ASSESSMENT','IN_THERAPY')");
$stmt->bind_param("i", $therapist_id);
$stmt->execute();
$active_cases = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Today's appointments
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE therapist_id = ? AND DATE(date_time) = ? AND status != 'CANCELLED'");
$stmt->bind_param("is", $therapist_id, $today);
$stmt->execute();
$today_appointments = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Pending requests
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE therapist_id = ? AND status = 'REQUESTED'");
$stmt->bind_param("i", $therapist_id);
$stmt->execute();
$pending_requests = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Upcoming appointments (confirmed, from now onward, limit 5)
$now = date('Y-m-d H:i:s');
$stmt = $conn->prepare("
    SELECT a.appointment_id, a.date_time, a.status, u.name AS client_name,
           CASE WHEN s.session_id IS NOT NULL THEN 'جلسة متابعة' ELSE 'جلسة أولى' END AS session_type
    FROM appointments a
    JOIN clients c ON a.client_id = c.client_id
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN sessions s ON s.case_id = a.case_id AND s.session_id != (
        SELECT MAX(s2.session_id) FROM sessions s2 WHERE s2.case_id = a.case_id
    )
    WHERE a.therapist_id = ? AND a.date_time >= ? AND a.status IN ('CONFIRMED','REQUESTED')
    ORDER BY a.date_time ASC
    LIMIT 5
");
$stmt->bind_param("is", $therapist_id, $now);
$stmt->execute();
$upcoming_appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// New requests (REQUESTED appointments, limit 5)
$stmt = $conn->prepare("
    SELECT a.appointment_id, a.date_time, u.name AS client_name, a.mode
    FROM appointments a
    JOIN clients c ON a.client_id = c.client_id
    JOIN users u ON c.user_id = u.user_id
    WHERE a.therapist_id = ? AND a.status = 'REQUESTED'
    ORDER BY a.created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $therapist_id);
$stmt->execute();
$new_requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Recent cases (latest 3)
$stmt = $conn->prepare("
    SELECT cs.case_id, cs.title AS condition_name, cs.status, cs.created_at, u.name AS client_name
    FROM cases cs
    JOIN clients c ON cs.client_id = c.client_id
    JOIN users u ON c.user_id = u.user_id
    WHERE cs.therapist_id = ?
    ORDER BY cs.created_at DESC
    LIMIT 3
");
$stmt->bind_param("i", $therapist_id);
$stmt->execute();
$recent_cases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Helper: format Arabic time
function formatArabicTime($datetime) {
    $ts = strtotime($datetime);
    $hour = (int)date('g', $ts);
    $min = date('i', $ts);
    $ampm = date('A', $ts) === 'AM' ? 'صباحاً' : 'مساءً';
    return "$hour:$min $ampm";
}

// Helper: time ago in Arabic
function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'الآن';
    if ($diff < 3600) return 'منذ ' . floor($diff / 60) . ' دقيقة';
    if ($diff < 86400) return 'منذ ' . floor($diff / 3600) . ' ساعة';
    if ($diff < 604800) return 'منذ ' . floor($diff / 86400) . ' يوم';
    return 'منذ ' . floor($diff / 604800) . ' أسبوع';
}

// Helper: first letter of Arabic name
function firstLetter($name) {
    return mb_substr(trim($name), 0, 1, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>لوحة التحكم</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link rel="icon" type="./img/Silver.png" href="img/Silver.png">
  
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
  />

 
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
  />
<link rel="stylesheet" href="../thirapist.css">
  <link rel="stylesheet" href="../total.css"/>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="app">

<?php $activePage = 'dashboard'; ?>
<?php include "../layouts/sidebar.php"; ?>

  <main class="main">
    <header class="main-header">
      <button class="sidebar-toggle" id="sidebarToggle" aria-label="فتح القائمة">
        <i class="fa-solid fa-bars"></i>
      </button>
      <h1 class="page-title">لوحة التحكم</h1>
      <button class="status-btn">
        <span class="status-dot"></span>
        متصل
      </button>
    </header>
    <section class="stats-row">
      <div class="stat-card ">
        <div class="stat-info">
          <div class="stat-label">إجمالي الحالات</div>
          <div class="stat-value"><?= $total_cases ?></div>
        </div>
        <div class="stat-icon">
          <img src="./img/اجمالي الحالات.png" alt="إجمالي الحالات">
        </div>
      </div>

      <div class="stat-card ">
        <div class="stat-info">
          <div class="stat-label">الحالات النشطة</div>
          <div class="stat-value"><?= $active_cases ?></div>
        </div>
        <div class="stat-icon">
         <img src="./img/الحالات النشطة.png" alt=" لحالات النشطة">
        </div>
      </div>

      
      <div class="stat-card ">
        <div class="stat-info">
          <div class="stat-label">مواعيد اليوم</div>
          <div class="stat-value"><?= $today_appointments ?></div>
        </div>
        <div class="stat-icon">
         <img src="./img/مواعيد اليوم.png" alt="مواعيد اليوم">
        </div>
      </div>

      <div class="stat-card ">
        <div class="stat-info">
          <div class="stat-label">طلبات الانتظار</div>
          <div class="stat-value"><?= $pending_requests ?></div>
        </div>
        <div class="stat-icon">
         <img  src="./img/طلبات الانتظار.png" alt="طلبات الانتظار"> 
        </div>
      </div>

    </section>

    <section class="middle-row">

      <div class="col-large">
        <div class="dash-card section-card appointments-card">
          <div class="card-header">
            <h2>المواعيد القادمة</h2>
            <button class="link-btn">عرض الكل</button>
          </div>

          <?php if (empty($upcoming_appointments)): ?>
            <p class="muted" style="text-align:center; padding:20px;">لا توجد مواعيد قادمة</p>
          <?php else: ?>
            <?php foreach ($upcoming_appointments as $appt): ?>
            <div class="appointment-item">
              <div class="appt-main">
                <div class="case-avatar"><?= firstLetter($appt['client_name']) ?></div>
                <div class="appt-text">
                  <div class="appt-name"><?= htmlspecialchars($appt['client_name']) ?></div>
                  <div class="muted small"><?= htmlspecialchars($appt['session_type']) ?></div>
                </div>
              </div>
              <div class="appt-time">
                <div class="time">
                  <img class="appt-icon" src="img/hour.png" alt="time" />
                  <?= formatArabicTime($appt['date_time']) ?>
                </div>
                <?php if ($appt['status'] === 'CONFIRMED'): ?>
                  <button class="muted-btn">مؤكد</button>
                <?php else: ?>
                  <button class="mutedd-btn">قيد الانتظار</button>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>
      </div>

      <!-- طلبات جديدة (العمود الصغير) -->
      <div class="col-small">
        <div class="dash-card section-card requests-card">
          <div class="card-header">
            <h2>طلبات جديدة</h2>
            <button class="link-btn">عرض الكل</button>
          </div>

          <div class="list">
            <?php if (empty($new_requests)): ?>
              <p class="muted" style="text-align:center; padding:20px;">لا توجد طلبات جديدة</p>
            <?php else: ?>
              <?php foreach ($new_requests as $req): ?>
              <div class="request-item">
                <div class="line-1">
                  <img src="img/vector.png" alt="!" class="warn-icon">
                  <span class="name"><?= htmlspecialchars($req['client_name']) ?></span>
                </div>
                <div class="line-2">
                  <span class="muted small">طلب جلسة <?= $req['mode'] === 'ONLINE' ? 'أونلاين' : 'في المركز' ?></span>
                  <span class="muted small"><?= timeAgo($req['date_time']) ?></span>
                </div>
                <div class="actions">
                  <button class="btn-primary btn-accept" data-id="<?= $req['appointment_id'] ?>">قبول</button>
                  <button class="btn-outline btn-reject" data-id="<?= $req['appointment_id'] ?>">رفض</button>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </section>

    <section class="dash-card card-recent">
      <div class="card-header">
        <h2 class="card-title">الحالات الحديثة</h2>
        <button class="link-btn">عرض الكل</button>
      </div>

      <div class="recent-grid">
        <?php if (empty($recent_cases)): ?>
          <p class="muted" style="text-align:center; padding:20px;">لا توجد حالات حديثة</p>
        <?php else: ?>
          <?php
          // Map case status to progress percentage
          $status_progress = [
              'NEW' => 10,
              'IN_ASSESSMENT' => 35,
              'IN_THERAPY' => 60,
              'ON_HOLD' => 50,
              'RECOVERED' => 100,
          ];
          ?>
          <?php foreach ($recent_cases as $case):
            $progress = $status_progress[$case['status']] ?? 0;
          ?>
          <div class="case-card">
            <div class="case-header">
              <div class="case-avatar"><?= firstLetter($case['client_name']) ?></div>
              <div>
                <div class="case-name"><?= htmlspecialchars($case['client_name']) ?></div>
                <div class="case-meta"><?= timeAgo($case['created_at']) ?></div>
              </div>
            </div>
            <div class="case-condition"><?= htmlspecialchars($case['condition_name']) ?></div>
            <div class="progress-label">
              <span><?= $progress ?>%</span>
              <span>التقدم</span>
            </div>
            <div class="progress">
              <div class="progress-bar" style="width: <?= $progress ?>%"></div>
            </div>
          </div>
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