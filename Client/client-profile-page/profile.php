<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/client.php';
require_once __DIR__ . '/../../Database/profile-database.php';
require_once __DIR__ . '/../../Database/avatar-storage.php';

start_secure_session();
require_role(['CLIENT']);

// ── Handle POST requests (AJAX JSON or traditional form) ───────
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isJson = stripos($contentType, 'application/json') !== false;

    if ($isJson) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    } else {
        $input = $_POST;
    }

    $action    = trim((string) ($input['action'] ?? ''));
    $csrfToken = trim((string) ($input['csrf_token'] ?? ''));

    $response = ['success' => false, 'message' => 'إجراء غير معروف.'];

    if (!verify_csrf($csrfToken)) {
        $response = [
            'success' => false,
            'message' => 'انتهت صلاحية الطلب. يرجى تحديث الصفحة والمحاولة مجدداً.',
        ];
    } elseif ($action === 'update_profile') {
        $response = client_update_profile_personal_info(
            (string) ($input['name'] ?? ''),
            (string) ($input['email'] ?? ''),
            (string) ($input['phone'] ?? ''),
            (string) ($input['birthdate'] ?? '')
        );
        // Keep session cache in sync on success
        if ($response['success']) {
            $_SESSION['auth']['name']  = trim((string) ($input['name'] ?? ''));
            $_SESSION['auth']['email'] = trim((string) ($input['email'] ?? ''));
        }
    } elseif ($action === 'change_password') {
        $response = client_change_password(
            (string) ($input['current_password'] ?? ''),
            (string) ($input['new_password'] ?? '')
        );
    } elseif ($action === 'delete_account') {
        $deleted = client_delete_account_hard();
        if ($deleted) {
            $response = [
                'success'  => true,
                'message'  => 'تم حذف الحساب بنجاح.',
                'redirect' => '/That-Copy/Auth/login/index.php',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'تعذر حذف الحساب حالياً. يرجى المحاولة لاحقاً.',
            ];
        }
    }

    // AJAX → return JSON and stop
    if ($isJson) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ── Load page data ─────────────────────────────────────────────
$personalInfo      = ['name' => '', 'email' => '', 'phone' => '', 'birthdate' => '', 'avatar' => null];
$sessionStats        = ['total' => 0, 'scheduled' => 0, 'completed' => 0];
$firstAppointment    = ['date' => null, 'therapist_name' => null];
$showNotificationDot = false;

try {
    $personalInfo = client_get_profile_personal_info();
} catch (Throwable $e) {
    // defaults
}

try {
    $sessionStats = client_get_session_stats();
} catch (Throwable $e) {
    // defaults
}

try {
    $firstAppointment = client_get_first_appointment_info();
} catch (Throwable $e) {
    // defaults
}

try {
    $showNotificationDot = client_has_unread_notifications_for_current_user();
} catch (Throwable $e) {
    $showNotificationDot = false;
}

// Format the first appointment date for display
$treatmentStartDate = null;
if ($firstAppointment['date'] !== null && $firstAppointment['date'] !== '') {
    try {
        $dt = new DateTimeImmutable($firstAppointment['date']);
        $treatmentStartDate = $dt->format('Y/m/d');
    } catch (Throwable $e) {
        // keep null
    }
}
$therapistName = $firstAppointment['therapist_name'];

$csrfToken = csrf_token();
$config = require __DIR__ . '/../../Database/config.php';
$avatarUploadEndpoint = rtrim((string) $config['app_url'], '/') . '/Auth/handlers/upload-avatar.php';

$hasClientAvatar = isset($personalInfo['avatar']) && $personalInfo['avatar'] !== null && trim((string) $personalInfo['avatar']) !== '';
$clientAvatarUrl = $hasClientAvatar ? avatar_public_url(trim((string) $personalInfo['avatar'])) : '';
?>

<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="<?= e($csrfToken) ?>" />
  <meta name="avatar-upload-endpoint" content="<?= e($avatarUploadEndpoint) ?>" />
  <title>الملف الشخصي</title>
  <link rel="stylesheet" href="../client-dashboard-page/style.css" />
  <link rel="stylesheet" href="./profile.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="icon" type="image/png" href="../images/Silver.png" />
</head>

<body>
  <header class="navbar">
    <div class="menu-btn" id="menuBtn"><i class="fa fa-bars"></i></div>
    <div class="nav-title">الملف الشخصي</div>
    <div class="nav-right">
      <a href="../client-notifications-page/notifications.php" class="bell">
        <i class="fa-regular fa-bell"></i>
        <span class="dot" id="notificationDot"<?= $showNotificationDot ? '' : ' style="display:none;"' ?>></span>
      </a>
    </div>
  </header>

  <?php $clientSidebarActive = 'profile';
  include __DIR__ . '/../partials/sidebar.php'; ?>

  <section class="profile-container">
    <section class="profile-hero dash-card">
      <div class="profile-hero-bio">
        <div class="profile-hero-main">
          <div class="profile-hero-info">
            <div class="profile-name-row">
              <h2 class="doctor-name" id="heroName"><?= e($personalInfo['name']) ?></h2>
            </div>

            <div class="doctor-meta-row">
              <div class="meta-item"></div>
              <?php if ($treatmentStartDate !== null): ?>
              <div class="meta-item">
                <img src="../images/Calendar.png" alt="" />
                <span> بدأ العلاج في <?= e($treatmentStartDate) ?></span>
              </div>
              <?php endif; ?>
              <?php if ($therapistName !== null && $therapistName !== ''): ?>
              <div class="meta-item">
                <img src="../images/User.png" alt="" />
                <span> مع <?= e($therapistName) ?></span>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="profile-hero-avatar">
            <div class="avatar-circle-lg">
              <span class="avatar-initial" id="heroInitial"<?= $hasClientAvatar ? ' style="visibility:hidden"' : '' ?>><?= e(mb_substr($personalInfo['name'], 0, 1)) ?></span>
              <img id="avatarImage" alt="" src="<?= e($clientAvatarUrl) ?>" style="display: <?= $hasClientAvatar ? 'block' : 'none' ?>;" />
              <button type="button" class="avatar-change-btn">
                <i class="fa-solid fa-camera"></i>
              </button>
            </div>
            <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp,image/gif" hidden />
          </div>
        </div>
      </div>
    </section>

    <section class="stats-row">
      <div class="stat-card">
        <div class="stat-info">
          <div class="stat-label">إجمالي الجلسات</div>
          <div class="stat-value" id="totalCases"><?= (int) $sessionStats['total'] ?></div>
        </div>
        <div class="stat-icon">
          <img src="../images/Container10.png" alt="" />
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <div class="stat-label">الجلسات القادمة</div>
          <div class="stat-value" id="activeCases"><?= (int) $sessionStats['scheduled'] ?></div>
        </div>
        <div class="stat-icon">
          <img src="../images/Container11.png" alt="" />
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <div class="stat-label">الجلسات المكتملة</div>
          <div class="stat-value" id="todayAppointments"><?= (int) $sessionStats['completed'] ?></div>
        </div>
        <div class="stat-icon">
          <img src="../images/Container12.png" alt="" />
        </div>
      </div>
    </section>

    <div class="profile-grid">
      <div class="card">
        <div class="card-header">
          <h3>المعلومات الشخصية</h3>
          <button class="edit-btn" id="editBtn">
            تعديل
          </button>
        </div>
        <div class="info">
          <span>
            <img src="../images/Container13.png" class="small-icon" alt="user" />
            <span id="displayName"><?= e($personalInfo['name']) ?></span>
          </span>
        </div>
        <div class="info">
          <span>
            <img src="../images/Container14.png" class="small-icon" alt="email" />
            <span id="displayEmail"><?= e($personalInfo['email']) ?></span>
          </span>
        </div>
        <div class="info">
          <span>
            <img src="../images/Container15.png" class="small-icon" alt="phone" />
            <span id="displayPhone"><?= e($personalInfo['phone'] ?: 'غير متاح') ?></span>
          </span>
        </div>
        <div class="info">
          <span>
            <img src="../images/Container16.png" class="small-icon" alt="birthday" />
            <span id="displayBirth"><?= e($personalInfo['birthdate'] ?: 'غير متاح حالياً') ?></span>
          </span>
        </div>

        <div id="editForm" style="display: none; margin-top: 20px">
          <input type="text" id="nameInput" placeholder="الاسم"
                 value="<?= e($personalInfo['name']) ?>" />
          <input type="email" id="emailInput" placeholder="البريد الإلكتروني"
                 value="<?= e($personalInfo['email']) ?>" />
          <input type="text" id="phoneInput" placeholder="رقم الهاتف"
                 value="<?= e($personalInfo['phone']) ?>" />
          <input type="date" id="birthInput"
                 value="<?= e($personalInfo['birthdate']) ?>" />
          <button class="save-btn" id="saveProfileBtn">حفظ</button>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3>تغيير كلمة المرور</h3>
        </div>
        <input type="password" id="oldPassword" placeholder="كلمة المرور الحالية" />
        <input type="password" id="newPassword" placeholder="كلمة المرور الجديدة" />
        <input type="password" id="confirmPassword" placeholder="تأكيد كلمة المرور الجديدة" />
        <button class="save-btn" id="changePasswordBtn">
          حفظ التغيير
        </button>
      </div>
    </div>

    <div class="card settings">
      <h3 class="card-header">إعدادات الإشعارات</h3>
      <div class="setting">
        <span>إشعارات المواعيد</span><label class="switch"><input type="checkbox"
            id="notifyAppointments" /><span></span></label>
      </div>
      <div class="setting">
        <span>إشعارات الرسائل</span><label class="switch"><input type="checkbox"
            id="notifyMessages" /><span></span></label>
      </div>
      <div class="setting">
        <span>تذكير بالأنشطة</span><label class="switch"><input type="checkbox"
            id="notifyActivities" /><span></span></label>
      </div>
      <button class="save-btn" onclick="saveNotifications()">
        حفظ الإعدادات
      </button>
    </div>

    <div class="danger">
      <h3><img src="../images/Container17.png" class="small-icon" alt="warning" /> منطقة الخطر</h3>
      <p>حذف الحساب سيؤدي إلى فقدان جميع بياناتك بشكل نهائي. هذا الإجراء لا يمكن التراجع عنه.</p>
      <button class="delete-btn" id="deleteAccountBtn">حذف الحساب نهائياً</button>
    </div>
  </section>

  <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
   <div class="sidebar-overlay"></div>
  <script src="./profile.js"></script>
</body>

</html>
