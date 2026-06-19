<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/client.php';
require_once __DIR__ . '/../../Database/profile-database.php';
require_once __DIR__ . '/profile-database.php';

start_secure_session();
require_auth();
require_role(['CLIENT']);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && ($_GET['action'] ?? '') === 'ready_avatars') {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(client_profile_list_ready_avatars(), JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(client_profile_handle_post(), JSON_UNESCAPED_UNICODE);
    exit;
}

$personalInfo = ['name' => '', 'email' => '', 'phone' => '', 'birthdate' => '', 'avatar' => null, 'gender' => 'MALE', 'avatar_url' => ''];
$sessionStats = ['total' => 0, 'scheduled' => 0, 'completed' => 0];
$firstAppointment = ['date' => null, 'therapist_name' => null];
$notificationPreferences = client_default_notification_preferences();
$showNotificationDot = false;

try {
    $personalInfo = client_get_profile_personal_info();
} catch (Throwable $e) {
}

try {
    $sessionStats = client_get_session_stats();
} catch (Throwable $e) {
}

try {
    $firstAppointment = client_get_first_appointment_info();
} catch (Throwable $e) {
}

try {
    $notificationPreferences = client_get_notification_preferences_for_current_user();
} catch (Throwable $e) {
}

try {
    $showNotificationDot = hasUnreadNotifications();
} catch (Throwable $e) {
    $showNotificationDot = false;
}

$treatmentStartDate = null;
if ($firstAppointment['date'] !== null && $firstAppointment['date'] !== '') {
    try {
        $dt = new DateTimeImmutable($firstAppointment['date']);
        $treatmentStartDate = $dt->format('Y/m/d');
    } catch (Throwable $e) {
    }
}
$therapistName = $firstAppointment['therapist_name'];

$csrfToken = csrf_token();
$hasClientAvatar = ($personalInfo['avatar_url'] ?? '') !== '';
$clientAvatarUrl = (string) ($personalInfo['avatar_url'] ?? '');
$genderLabel = client_profile_gender_label(client_profile_gender_folder((string) ($personalInfo['gender'] ?? 'MALE')));
?>

<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="<?= e($csrfToken) ?>" />
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
              <button type="button" class="avatar-change-btn" id="avatarChangeBtn" aria-label="تغيير الصورة">
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
          <button class="edit-btn" id="editBtn">تعديل</button>
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
            <span id="displayPhone" dir="<?= $personalInfo['phone'] ? 'ltr' : 'rtl' ?>"><?= e($personalInfo['phone'] ?: 'غير متاح') ?></span>
          </span>
        </div>
        <div class="info">
          <span>
            <img src="../images/Container16.png" class="small-icon" alt="birthday" />
            <span id="displayBirth"><?= e($personalInfo['birthdate'] ?: 'غير متاح حالياً') ?></span>
          </span>
        </div>

        <div id="editForm" style="display: none; margin-top: 20px">
          <input type="text" id="nameInput" placeholder="الاسم" value="<?= e($personalInfo['name']) ?>" />
          <input type="email" id="emailInput" placeholder="البريد الإلكتروني" value="<?= e($personalInfo['email']) ?>" />
          <input type="text" id="phoneInput" placeholder="رقم الهاتف" value="<?= e($personalInfo['phone']) ?>" />
          <input type="date" id="birthInput" value="<?= e($personalInfo['birthdate']) ?>" />
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
        <button class="save-btn" id="changePasswordBtn">حفظ التغيير</button>
      </div>
    </div>

    <div class="card settings">
      <h3 class="card-header">إعدادات الإشعارات</h3>
      <div class="setting">
        <span>إشعارات المواعيد</span><label class="switch"><input type="checkbox" id="notifyAppointments"<?= $notificationPreferences['appointment_notifications'] ? ' checked' : '' ?> /><span></span></label>
      </div>
      <div class="setting">
        <span>إشعارات الرسائل</span><label class="switch"><input type="checkbox" id="notifyMessages"<?= $notificationPreferences['message_notifications'] ? ' checked' : '' ?> /><span></span></label>
      </div>
      <div class="setting">
        <span>تذكير بالأنشطة</span><label class="switch"><input type="checkbox" id="notifyActivities"<?= $notificationPreferences['activity_reminder_notifications'] ? ' checked' : '' ?> /><span></span></label>
      </div>
      <button class="save-btn" id="saveNotificationsBtn">حفظ الإعدادات</button>
    </div>

    <div class="danger">
      <h3><img src="../images/Container17.png" class="small-icon" alt="warning" /> منطقة الخطر</h3>
      <p>حذف الحساب سيؤدي إلى فقدان جميع بياناتك بشكل نهائي. هذا الإجراء لا يمكن التراجع عنه.</p>
      <button class="delete-btn" id="deleteAccountBtn">حذف الحساب نهائياً</button>
    </div>
  </section>

  <div class="avatar-modal" id="avatarModal" hidden>
    <div class="avatar-modal-backdrop" id="avatarModalBackdrop"></div>
    <div class="avatar-modal-dialog" role="dialog" aria-labelledby="avatarModalTitle" aria-modal="true">
      <div class="avatar-modal-header">
        <h3 id="avatarModalTitle">تغيير الصورة الشخصية</h3>
        <button type="button" class="avatar-modal-close" id="avatarModalClose" aria-label="إغلاق">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <p class="avatar-modal-subtitle">صور جاهزة لـ <?= e($genderLabel) ?> — أو ارفع صورة من جهازك</p>

      <div class="avatar-modal-upload">
        <button type="button" class="save-btn" id="uploadFromDeviceBtn">
          <i class="fa-solid fa-upload"></i>
          رفع من الجهاز
        </button>
      </div>

      <div class="avatar-modal-section-title">اختر صورة جاهزة</div>
      <div class="ready-avatars-grid" id="readyAvatarsGrid">
        <p class="ready-avatars-empty">جاري التحميل...</p>
      </div>
    </div>
  </div>

  <div class="sidebar-overlay"></div>
  <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
  <script src="./profile.js"></script>
</body>

</html>
