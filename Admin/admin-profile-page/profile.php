<?php

declare(strict_types=1);

require_once __DIR__ . '/../partials/require-admin.php';

require_once __DIR__ . '/profile-database.php';
require_once __DIR__ . '/../../Database/avatar-storage.php';

// ─── AJAX Handlers ────────────────────────────────────────────────────────────

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && ($_GET['action'] ?? '') === 'ready_avatars') {
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(admin_profile_list_ready_avatars(), JSON_UNESCAPED_UNICODE);
  exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(admin_profile_handle_post(), JSON_UNESCAPED_UNICODE);
  exit;
}

// ─── Page Data ────────────────────────────────────────────────────────────────

$userId  = (int) $_SESSION['auth']['user_id'];
$profile = getAdminProfile($userId);
$logs    = getLoginActivities($userId, 5);

if (empty($profile)) {
  $profile = [
    'name' => $_SESSION['auth']['name'] ?? 'Admin',
    'email' => $_SESSION['auth']['email'] ?? '',
    'role' => $_SESSION['auth']['role'] ?? 'ADMIN',
    'avatar' => null,
    'initials' => 'A',
    'is_2fa_enabled' => false,
    'join_date' => '-',
    'gender' => 'MALE',
  ];
}

$profile['name'] = (string) ($profile['name'] ?? 'Admin');
$profile['email'] = (string) ($profile['email'] ?? '');
$profile['role'] = (string) ($profile['role'] ?? 'ADMIN');
$profile['avatar'] = $profile['avatar'] ?? null;
$profile['initials'] = (string) ($profile['initials'] ?? 'A');
$profile['is_2fa_enabled'] = (bool) ($profile['is_2fa_enabled'] ?? false);
$profile['join_date'] = (string) ($profile['join_date'] ?? '-');
$profile['gender'] = (string) ($profile['gender'] ?? 'MALE');

if ($profile['join_date'] === '-') {
  $profile['join_date'] = getUserJoinDate($userId, $profile['email']);
}

$permissions = [
  'إدارة المستخدمين',
  'إدارة الأخصائيين',
  'إدارة الاختبارات',
  'عرض التقارير',
];

$twoFaLabel  = $profile['is_2fa_enabled'] ? 'مفعّلة'     : 'غير مفعّلة';
$twoFaStatus = $profile['is_2fa_enabled']
  ? 'مفعّلة — المصادقة الثنائية نشطة'
  : 'غير مفعّلة — سيُطلب منك رمز التحقق عند تفعيلها';

$hasAvatar        = $profile['avatar'] !== null && trim((string) $profile['avatar']) !== '';
$profileAvatarUrl = $hasAvatar ? avatar_public_url(trim((string) $profile['avatar'])) : '';
$genderLabel      = admin_profile_gender_label(admin_profile_gender_folder($profile['gender']));

?>
<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>الملف الشخصي</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="icon" type="image/png" sizes="32x32" href="../images/Silver.png" />
  <link rel="shortcut icon" sizes="10x10" href="../images/Silver.png" />
  <link rel="stylesheet" href="../admin-dashboard-page/admin-dashboard.css" />
  <link rel="stylesheet" href="./profile.css" />
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>" />
</head>

<body>
  <?php $adminSidebarUserName = $profile['name']; ?>
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <div class="main-wrapper">
    <header class="navbar">
      <div class="menu-btn"><i class="fa fa-bars"></i></div>
      <div class="nav-title">الملف الشخصي</div>
      <div class="nav-right">
        <button class="status-btn">
          <span class="status-dot"></span>
          متصل
        </button>
      </div>
    </header>

    <main class="page-content">
      <div class="profile-wrapper">

        <!--  Basic Info  -->
        <div class="profile-card">
          <div class="card-header-teal card-header-with-action">
            <div class="card-header-title">
              <img src="../images/profile.svg" alt="" />
              المعلومات الأساسية
            </div>
            <button type="button" class="btn-edit-info" id="editInfoBtn">تعديل المعلومات</button>
          </div>
          <div class="card-body">
            <div class="basic-info-layout">

              <div class="avatar-block">
                <div class="avatar-circle" id="profileAvatar"
                  <?php if ($profileAvatarUrl !== ''): ?>
                  style="background-image:url('<?= e($profileAvatarUrl) ?>');background-size:cover;background-position:center;"
                  <?php endif; ?>>

                  <span id="profileAvatarInitials" class="avatar-initials-text" <?= $profileAvatarUrl !== '' ? ' style="visibility:hidden"' : '' ?>><?= e($profile['initials']) ?></span>

                  <div class="avatar-edit" id="avatarEditBtn" title="تعديل الصورة">
                    <img src="../images/profileedit.svg" alt="" />
                  </div>
                </div>
                <input type="file" id="adminAvatarInput" accept="image/jpeg,image/png,image/webp,image/gif" hidden />
              </div>

              <div class="basic-fields">
                <div class="field-group">
                  <div class="field-icon"><img src="../images/Container.jpg" alt="" /></div>
                  <div class="field-text">
                    <span class="field-label">الاسم الكامل</span>
                    <span class="field-value" id="displayName"><?= htmlspecialchars($profile['name']) ?></span>
                  </div>
                </div>

                <div class="field-group">
                  <div class="field-icon"><img src="../images/Container (2).jpg" alt="" /></div>
                  <div class="field-text">
                    <span class="field-label">نوع الحساب</span>
                    <span class="field-value">
                      <span class="badge-super"><?= htmlspecialchars($profile['role']) ?></span>
                    </span>
                  </div>
                </div>

                <div class="field-group">
                  <div class="field-icon"><img src="../images/Container (1).jpg" alt="" /></div>
                  <div class="field-text">
                    <span class="field-label">البريد الإلكتروني</span>
                    <span class="field-value" id="displayEmail"><?= htmlspecialchars($profile['email']) ?></span>
                  </div>
                </div>

                <div class="field-group">
                  <div class="field-icon"><img src="../images/Container (3).jpg" alt="" /></div>
                  <div class="field-text">
                    <span class="field-label">تاريخ الانضمام</span>
                    <span class="field-value"><?= htmlspecialchars($profile['join_date']) ?></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Permissions -->
        <div class="profile-card">
          <div class="card-title-plain">
            <div class="title-row">
              <img src="../images/Icon.jpg" alt="" />
              الصلاحيات
            </div>
            <span class="title-sub">لا يمكن تعديل الصلاحيات من هذه الصفحة</span>
          </div>
          <div class="permissions-body">
            <div class="permissions-grid">
              <?php foreach ($permissions as $perm): ?>
                <div class="perm-item">
                  <img src="../images/CheckCircle.svg" alt="Check" />
                  <?= htmlspecialchars($perm) ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!--  Security Settings  -->
        <div class="profile-card">
          <div class="card-title-plain">
            <div class="title-row">
              <img src="../images/Icons.jpg" alt="Lock" />
              إعدادات الأمان
            </div>
          </div>
          <div class="security-body">
            <div class="security-inner">

              <!-- Change password -->
              <div class="sec-row">
                <div class="sec-right">
                  <div class="sec-icon-wrap"><img src="../images/footers.svg" alt="" /></div>
                  <div class="sec-text">
                    <span class="sec-title">تغيير كلمة المرور</span>
                    <span class="sec-sub">يمكنك تغيير كلمة المرور من هنا</span>
                  </div>
                </div>
                <div class="sec-left">
                  <button class="btn-change" id="changePasswordBtn">تغيير</button>
                </div>
              </div>

              <!-- 2FA status (read-only — toggling is done at login) -->
              <div class="sec-row">
                <div class="sec-right">
                  <div class="sec-icon-wrap"><img src="../images/footers (1).svg" alt="" /></div>
                  <div class="sec-text">
                    <span class="sec-title">المصادقة الثنائية (2FA)</span>
                    <span class="sec-sub"><?= htmlspecialchars($twoFaStatus) ?></span>
                  </div>
                </div>
                <div class="sec-left">
                  <span class="sec-status-text <?= $profile['is_2fa_enabled'] ? 'enabled' : 'disabled' ?>">
                    <?= htmlspecialchars($twoFaLabel) ?>
                  </span>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!--  Login Activity -->
        <div class="profile-card">
          <div class="card-header-teal">
            <i class="fa-regular fa-clock"></i>
            سجل الدخول الأخير
          </div>
          <div class="login-table-wrap">
            <table class="login-table">
              <thead>
                <tr>
                  <th>التاريخ</th>
                  <th>الوقت</th>
                  <th>الجهاز</th>
                  <th>عنوان IP</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($logs)): ?>
                  <tr>
                    <td colspan="5" style="text-align:center;color:#9ca3af;">لا يوجد سجل دخول حتى الآن</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($logs as $log): ?>
                    <tr>
                      <td data-label="التاريخ">
                        <span class="cell-icon">
                          <img src="../images/footerIcon.png" alt="" />
                          <?= htmlspecialchars($log['date']) ?>
                        </span>
                      </td>
                      <td data-label="الوقت"><?= htmlspecialchars($log['time']) ?></td>
                      <td data-label="الجهاز">
                        <span class="cell-icon">
                          <img src="../images/footerIcon (1).png" alt="" />
                          <?= htmlspecialchars($log['device']) ?>
                        </span>
                      </td>
                      <td data-label="عنوان IP"><?= htmlspecialchars($log['ip']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="logout-all-bar">
            <button class="logout-all-btn" id="logoutAllBtn">
              <i class="fa-solid fa-arrow-right-from-bracket"></i>
              تسجيل الخروج من جميع الأجهزة
            </button>
          </div>
        </div>

      </div>
    </main>

    <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
  </div>

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
        <button type="button" class="modal-save-btn" id="uploadFromDeviceBtn">
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

  <div class="avatar-modal" id="editInfoModal" hidden>
    <div class="avatar-modal-backdrop" id="editInfoModalBackdrop"></div>
    <div class="avatar-modal-dialog" role="dialog" aria-labelledby="editInfoModalTitle" aria-modal="true">
      <div class="avatar-modal-header">
        <h3 id="editInfoModalTitle">تعديل المعلومات</h3>
        <button type="button" class="avatar-modal-close" id="editInfoModalClose" aria-label="إغلاق">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <p class="avatar-modal-subtitle">يمكنك تعديل الاسم والبريد الإلكتروني</p>

      <div class="profile-modal-form">
        <label class="profile-modal-label" for="editNameInput">الاسم الكامل</label>
        <input type="text" id="editNameInput" class="profile-modal-input" value="<?= e($profile['name']) ?>" maxlength="100" />

        <label class="profile-modal-label" for="editEmailInput">البريد الإلكتروني</label>
        <input type="email" id="editEmailInput" class="profile-modal-input" value="<?= e($profile['email']) ?>" maxlength="150" />

        <button type="button" class="modal-save-btn" id="saveProfileBtn">حفظ التغييرات</button>
      </div>
    </div>
  </div>

  <script src="./profile.js"></script>
</body>

</html>