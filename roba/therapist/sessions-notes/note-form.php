<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'THERAPIST') {
    header("Location: /homepage/index.php");
    exit;
}

require_once __DIR__ . '/../../connection.php';

$user_id = $_SESSION['user_id'];
$therapist_id = $user_id;

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appt_id = (int) ($_POST['appointment_id'] ?? 0);
    $session_goals = trim($_POST['session_goals'] ?? '');
    $mood = trim($_POST['mood'] ?? '');
    $topics = trim($_POST['topics'] ?? '');
    $techniques = trim($_POST['techniques'] ?? '');
    $progress = trim($_POST['progress'] ?? '');
    $risk_assessment = trim($_POST['risk_assessment'] ?? '');
    $therapist_notes_text = trim($_POST['therapist_notes'] ?? '');
    $homework = trim($_POST['homework'] ?? '');
    $next_plan = trim($_POST['next_plan'] ?? '');

    if ($appt_id <= 0) {
        $error_msg = 'معرف الموعد غير صالح';
    } else {
        $stmt = $conn->prepare("SELECT appointment_id, case_id, date_time, duration_min FROM appointments WHERE appointment_id = ? AND therapist_id = ? AND status = 'CONFIRMED'");
        $stmt->bind_param("ii", $appt_id, $therapist_id);
        $stmt->execute();
        $appt = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$appt) {
            $error_msg = 'الموعد غير موجود أو غير مؤكد';
        } else {
            $conn->begin_transaction();
            try {
                $start_time = $appt['date_time'];
                $end_minutes = (int) $appt['duration_min'];
                $end_time = date('Y-m-d H:i:s', strtotime($start_time) + ($end_minutes * 60));

                $stmt = $conn->prepare("INSERT INTO sessions (appointment_id, case_id, start_time, end_time) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $appt_id, $appt['case_id'], $start_time, $end_time);
                $stmt->execute();
                $session_id = $stmt->insert_id;
                $stmt->close();

                $stmt = $conn->prepare("
                    INSERT INTO session_notes (session_id, session_goals, mood, topics, techniques, progress, risk_assessment, therapist_notes, homework, next_plan)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("isssssssss", $session_id, $session_goals, $mood, $topics, $techniques, $progress, $risk_assessment, $therapist_notes_text, $homework, $next_plan);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE appointments SET status = 'COMPLETED' WHERE appointment_id = ?");
                $stmt->bind_param("i", $appt_id);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                $success_msg = 'تم حفظ ملاحظات الجلسة وإنهاء الموعد بنجاح';
            } catch (Exception $e) {
                $conn->rollback();
                $error_msg = 'حدث خطأ أثناء الحفظ، حاول مرة أخرى';
            }
        }
    }
}

$appointment = null;
$client_name = '';
$case_title = '';
$session_type_label = '';

$appt_id_param = (int) ($_GET['appointment_id'] ?? $_POST['appointment_id'] ?? 0);

if ($appt_id_param > 0 && empty($success_msg)) {
    $stmt = $conn->prepare("
        SELECT a.appointment_id, a.date_time, a.duration_min, a.mode, a.status, a.case_id,
               u.name AS client_name, cs.title AS case_title,
               (SELECT COUNT(*) FROM sessions s WHERE s.case_id = a.case_id) AS total_sessions
        FROM appointments a
        JOIN clients c ON a.client_id = c.client_id
        JOIN users u ON c.client_id = u.user_id
        LEFT JOIN cases cs ON a.case_id = cs.case_id
        WHERE a.appointment_id = ? AND a.therapist_id = ?
    ");
    $stmt->bind_param("ii", $appt_id_param, $therapist_id);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($appointment) {
        $client_name = $appointment['client_name'];
        $case_title = $appointment['case_title'] ?? '';
        $session_type_label = ((int)$appointment['total_sessions'] === 0) ? 'جلسة أولى' : 'جلسة متابعة';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <title>كتابة ملاحظات الجلسة</title>
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
        <h1 class="page-title">كتابة ملاحظات الجلسة</h1>
        <button class="status-btn">
          <span class="status-dot"></span>
          متصل
        </button>
      </header>

      <?php if ($success_msg): ?>
        <div class="alert-success">
          <i class="fa-solid fa-circle-check"></i>
          <?= htmlspecialchars($success_msg) ?>
          <a href="index.php" class="alert-link">عرض جميع الملاحظات</a>
        </div>
      <?php endif; ?>

      <?php if ($error_msg): ?>
        <div class="alert-error">
          <i class="fa-solid fa-circle-xmark"></i>
          <?= htmlspecialchars($error_msg) ?>
        </div>
      <?php endif; ?>

      <?php if ($appointment && $appointment['status'] !== 'CONFIRMED' && empty($success_msg)): ?>
        <div class="alert-error">
          <i class="fa-solid fa-circle-xmark"></i>
          هذا الموعد غير مؤكد ولا يمكن كتابة ملاحظات له
          <a href="../appointments/index.php" class="alert-link">العودة للمواعيد</a>
        </div>
      <?php elseif (!$appointment && empty($success_msg)): ?>
        <div class="alert-error">
          <i class="fa-solid fa-circle-xmark"></i>
          لم يتم تحديد موعد صالح
          <a href="../appointments/index.php" class="alert-link">العودة للمواعيد</a>
        </div>
      <?php elseif ($appointment && empty($success_msg)): ?>

      <header class="notes-header">
        <div>
          <h2 class="notes-title"><i class="fa-regular fa-file-lines session-notes"></i>ملاحظات الجلسة</h2>
          <p class="notes-subtitle">توثيق تفاصيل الجلسة العلاجية</p>
        </div>
      </header>

      <form id="sessionNotesForm" method="POST" action="note-form.php">
        <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">

      <section class="notes-wrapper dash-card">
        <section class="notes-section" data-section>
          <header class="section-header">
            <div class="section-title-wrap">
              <h3 class="section-title"><i class="fa-regular fa-user section-icon" style="color:#30B7C4;"></i> معلومات أساسية</h3>
            </div>
          </header>
          <div class="section-body">
            <div class="form-grid-2">
              <div class="form-group">
                <label>اسم المريض</label>
                <input type="text" value="<?= htmlspecialchars($client_name) ?>" readonly class="readonly-input">
              </div>
              <div class="form-group">
                <label>نوع الجلسة</label>
                <input type="text" value="<?= htmlspecialchars($session_type_label) ?>" readonly class="readonly-input">
              </div>
            </div>
          </div>
          <div class="form-grid-3">
            <div class="form-group">
              <label>تاريخ الجلسة</label>
              <input type="date" value="<?= date('Y-m-d', strtotime($appointment['date_time'])) ?>" readonly class="readonly-input">
            </div>
            <div class="form-group">
              <label>وقت الجلسة</label>
              <input type="text" value="<?= date('H:i', strtotime($appointment['date_time'])) ?>" readonly class="readonly-input">
            </div>
            <div class="form-group">
              <label>مدة الجلسة (بالدقائق)</label>
              <input type="number" value="<?= (int)$appointment['duration_min'] ?>" readonly class="readonly-input">
            </div>
          </div>
        </section>

        <section class="notes-section" data-section>
          <header class="section-header">
            <div class="section-title-wrap">
              <i class="fa-regular fa-file-lines section-icon"></i>
              <h3 class="section-title">محتوى الجلسة</h3>
            </div>
          </header>
          <div class="section-body">
            <div class="form-group">
              <label for="sessionGoals">أهداف الجلسة<span class="required">*</span></label>
              <textarea id="sessionGoals" name="session_goals" rows="3" required placeholder="أكتب الأهداف الرئيسية للجلسة ..."></textarea>
            </div>
            <div class="form-group">
              <label for="mood">الحالة المزاجية للمريض <span class="required">*</span></label>
              <textarea id="mood" name="mood" rows="2" required placeholder="مثال: هادئ، قلق، مكتئب، متفائل"></textarea>
            </div>
            <div class="form-group">
              <label for="topics">المواضيع التي تمت مناقشتها <span class="required">*</span></label>
              <textarea id="topics" name="topics" rows="4" required placeholder="اذكر المواضيع والقضايا التي تمت مناقشتها خلال الجلسة..."></textarea>
            </div>
            <div class="form-group">
              <label for="techniques">التقنيات والأدوات المستخدمة</label>
              <textarea id="techniques" name="techniques" rows="2" placeholder="مثال: العلاج المعرفي السلوكي، تمارين التنفس، إعادة الهيكلة المعرفية..."></textarea>
            </div>
          </div>
        </section>

        <section class="notes-section" data-section>
          <header class="section-header">
            <div class="section-title-wrap">
              <i class="fa-regular fa-calendar section-icon"></i>
              <h3 class="section-title">التقييم والتقدم</h3>
            </div>
          </header>
          <div class="section-body">
            <div class="form-group">
              <label for="progress">تقدم المريض <span class="required">*</span></label>
              <textarea id="progress" name="progress" rows="3" required placeholder="صف التقدم الذي أحرزه المريض منذ الجلسة السابقة..."></textarea>
            </div>
            <div class="form-group">
              <label for="riskAssessment">تقييم المخاطر <span class="required">*</span></label>
              <textarea id="riskAssessment" name="risk_assessment" rows="2" required placeholder="منخفض"></textarea>
            </div>
            <p class="notes-footer-text">تقييم احتمالية إيذاء النفس أو الآخرين</p>
            <div class="form-group">
              <label for="therapistNotes">ملاحظات المعالج</label>
              <textarea id="therapistNotes" name="therapist_notes" rows="2" placeholder="أي ملاحظات أو تأملات حول الجلسة..."></textarea>
            </div>
          </div>
        </section>

        <section class="notes-section" data-section>
          <header class="section-header">
            <div class="section-title-wrap">
              <i class="fa-regular fa-circle-check section-icon"></i>
              <h3 class="section-title">الخطوات التالية</h3>
            </div>
          </header>
          <div class="section-body">
            <div class="form-group">
              <label for="homework">الواجبات المنزلية</label>
              <textarea id="homework" name="homework" rows="3" placeholder="الواجبات أو التمارين التي يجب على المريض القيام بها قبل الجلسة القادمة..."></textarea>
            </div>
            <div class="form-group">
              <label for="nextPlan">خطة الجلسة القادمة</label>
              <textarea id="nextPlan" name="next_plan" rows="4" placeholder="ما الذي سيتم التركيز عليه في الجلسة القادمة..."></textarea>
            </div>
          </div>
        </section>
      </section>

      <div class="form-actions">
        <button type="submit" class="btn-primary wide">
          <i class="fa-solid fa-clipboard-check"></i> إنهاء الجلسة وحفظ الملاحظات
        </button>
        <a href="../appointments/index.php" class="cancel-link">إلغاء</a>
      </div>

      </form>

      <?php endif; ?>

    <?php include "../layouts/footer.php"; ?>

    </main>
  </div>

  <script src="main.js"></script>
  <script src="../sidebar-toggle.js"></script>
</body>
</html>
