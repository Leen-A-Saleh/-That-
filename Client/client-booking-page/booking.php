<?php
require_once __DIR__ . '/booking-database.php';

start_secure_session();
require_role(['CLIENT']);

$therapistId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$doctor = null;
$availability = [];
$bookingErrorMessage = '';
$showNotificationDot = false;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  header('Content-Type: application/json; charset=utf-8');

  if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    echo json_encode([
      'success' => false,
      'message' => 'انتهت صلاحية الطلب. أعد تحميل الصفحة وحاول مرة أخرى.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  try {
    $result = saveBookingRequest(
      getCurrentClientId(),
      (int) ($_POST['therapist_id'] ?? 0),
      trim($_POST['session_type'] ?? ''),
      trim($_POST['meeting_type'] ?? ''),
      trim($_POST['session_date'] ?? ''),
      trim($_POST['session_time'] ?? '')
    );
  } catch (Throwable $error) {
    $result = [
      'success' => false,
      'message' => 'حدث خطأ أثناء إرسال طلب الحجز.',
    ];
  }

  if (!$result['success']) {
    http_response_code(422);
  }

  echo json_encode($result, JSON_UNESCAPED_UNICODE);
  exit;
}

if ($therapistId <= 0) {
  header('Location: ../client-dashboard-page/index.php?booking_error=missing_therapist_id');
  exit;
}

try {
  $doctor = getBookingTherapist($therapistId);
  $availability = getTherapistAvailability($therapistId);
} catch (Throwable $error) {
  $doctor = null;
  $availability = [];
}

try {
  $showNotificationDot = hasUnreadNotifications();
} catch (Throwable $error) {
  $showNotificationDot = false;
}

if (!$doctor) {
  $bookingErrorMessage = 'تعذر العثور على بيانات الأخصائي المطلوب.';
}
?>
<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>حجز جلسة</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" sizes="32x32" href="../images/Silver.png" />
  <link rel="shortcut icon" sizes="10x10" href="../images/Silver.png" />
  <link rel="apple-touch-icon" href="../images/Silver.png" />
  <link rel="stylesheet" href="../client-dashboard-page/style.css" />
  <link rel="stylesheet" href="./booking.css" />
</head>

<body class="page-booking">
  <header class="navbar">
    <div class="menu-btn" id="menuBtn">
      <i class="fa fa-bars"></i>
    </div>
    <div class="nav-title">حجز جلسة</div>
    <div class="nav-right">
      <a href="../client-notifications-page/notifications.php" class="bell">
        <i class="fa-regular fa-bell"></i>
        <span class="dot" id="notificationDot" <?= $showNotificationDot ? '' : ' style="display:none;"' ?>></span>
      </a>
    </div>
  </header>

  <?php $clientSidebarActive = 'dashboard';
  include __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="main">
    <section class="booking-section">
      <div class="doctor-card" id="doctorCard">
        <img src="<?= e($doctor['image'] ?? '../images/default-doctor.png') ?>" alt="<?= e($doctor['name'] ?? '') ?>" id="doctorImg" />
        <div class="doctor-overlay">
          <h2 id="doctorName"><?= e($doctor['name'] ?? '') ?></h2>
          <div class="info-row">
            <i class="fa fa-graduation-cap"></i><span id="doctorSpecial"><?= e($doctor['special'] ?? '') ?></span>
          </div>
          <div class="info-row">
            <i class="fa fa-certificate"></i><span id="doctorDegree"><?= e($doctor['degree'] ?? '') ?></span>
          </div>
          <div class="info-row">
            <i class="fa fa-briefcase"></i><span id="doctorExperience"><?= e($doctor['experience'] ?? '') ?></span>
          </div>
          <div class="info-row">
            <i class="fa fa-clock"></i>
            <span id="doctorWork">
              <?php if (!empty($doctor['availability'])): ?>
                <?php foreach ($doctor['availability'] as $slot): ?>
                  <?= e($slot['day_label'] . ' ' . $slot['start'] . ' - ' . $slot['end']) ?><br>
                <?php endforeach; ?>
              <?php else: ?>
                <?= e($doctor['work'] ?? 'غير متاح') ?>
              <?php endif; ?>
            </span>
          </div>
          <div class="info-row">
            <i class="fa fa-envelope"></i><a href="<?= !empty($doctor['email']) ? 'mailto:' . e($doctor['email']) : '#' ?>" id="doctorEmail"><?= e($doctor['email'] ?? '') ?></a>
          </div>
          <div class="price-row">
            <div class="price-tag">
              استشارية: <span id="doctorConsult"><?= e(($doctor ?? [])['consultPrice'] ?? '150 شيكل') ?></span>
            </div>
            <div class="price-tag">
              علاجية: <span id="doctorTherapy"><?= e(($doctor ?? [])['therapyPrice'] ?? '120 شيكل') ?></span>
            </div>
          </div>
        </div>
      </div>

      <form class="booking-form" id="bookingForm">
        <?= csrf_input() ?>
        <input type="hidden" name="therapist_id" value="<?= e((string) ($doctor['id'] ?? 0)) ?>" />
        <label for="sessionType">نوع الجلسة:</label>
        <select id="sessionType" name="session_type">
          <option value="consult">جلسة استشارية</option>
          <option value="therapy">جلسة علاجية</option>
        </select>

        <label for="meetingType">طريقة الجلسة:</label>
        <select id="meetingType" name="meeting_type">
          <option value="online">جلسة إلكترونية</option>
          <option value="offline">جلسة وجاهي</option>
        </select>

        <label for="sessionDate">إختر التاريخ:</label>
        <input type="date" id="sessionDate" name="session_date" />

        <label for="sessionTime">إختر الوقت:</label>
        <select id="sessionTime" name="session_time" disabled>
          <option value="">اختر التاريخ أولا</option>
        </select>

        <button id="confirmBooking" type="submit">تأكيد الحجز</button>

        <p class="success-msg" id="successMsg"></p>
      </form>
    </section>
  </main>

  <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
  <script>
    const bookingErrorMessage = <?= json_encode($bookingErrorMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const bookingAvailability = <?= json_encode($availability, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  </script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>
  <script src="./booking.js"></script>
</body>

</html>