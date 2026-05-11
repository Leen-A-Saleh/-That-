<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';

start_secure_session();

$adminSidebarCurrent = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$adminSidebarUserName = (string) ($_SESSION['auth']['name'] ?? 'المدير العام');

$adminSidebarItems = [
  [
    'page' => 'admin-dashboard.php',
    'href' => '../admin-dashboard-page/admin-dashboard.php',
    'icon' => '../images/Icon.svg',
    'label' => 'لوحة التحكم',
  ],
  [
    'page' => 'users.php',
    'href' => '../admin-users-page/users.php',
    'icon' => '../images/Icon (1).svg',
    'label' => 'المستخدمين/المرضى',
  ],
  [
    'page' => 'therapist.php',
    'href' => '../admin-therapist-page/therapist.php',
    'icon' => '../images/Icon (2).svg',
    'label' => 'الأخصائيين',
  ],
  [
    'page' => 'cases.php',
    'href' => '../admin-cases-page/cases.php',
    'icon' => '../images/Icon (3).svg',
    'label' => 'إدارة الحالات',
  ],
  [
    'page' => 'appointments.php',
    'href' => '../admin-appointments-page/appointments.php',
    'icon' => '../images/Icon (4).svg',
    'label' => 'المواعيد',
  ],
  [
    'page' => 'tests.php',
    'href' => '../admin-tests-page/tests.php',
    'icon' => '../images/Icon (6).svg',
    'label' => 'الاختبارات النفسية',
  ],
  [
    'page' => 'activities.php',
    'href' => '../admin-activities-page/activities.php',
    'icon' => '../images/Icon (7).svg',
    'label' => 'الأنشطة والألعاب',
  ],
  [
    'page' => 'notifications.php',
    'href' => '../admin-notifications-page/notifications.php',
    'icon' => '../images/Icon (7).svg',
    'label' => 'الإشعارات',
  ],
  [
    'page' => 'profile.php',
    'href' => '../admin-profile-page/profile.php',
    'icon' => '../images/Icon7.svg',
    'label' => 'الملف الشخصي',
  ],
];
?>

<style>
  .sidebar {
    height: 100vh;
    max-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  .sidebar .sidebar-scroll {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
  }

  .sidebar .sidebar-scroll ul {
    overflow: visible;
  }

  .sidebar .logout-btn {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    border-radius: 8px;
    background: transparent;
    border: none;
    border-right: 4px solid transparent;
    cursor: pointer;
    transition: 0.2s;
    color: #e74c3c;
    font: inherit;
    text-align: start;
  }

  .sidebar .logout-btn:hover {
    background: rgba(231, 76, 60, 0.12);
    border-right-color: #e74c3c;
  }

  .sidebar .logout-btn i {
    width: 20px;
    min-width: 20px;
    text-align: center;
    font-size: 18px;
  }
</style>

<aside class="sidebar">
  <div class="sidebar-scroll">

    <div class="logo">
      <img src="../images/Frame 393.svg" alt="logo" />
    </div>

    <div class="user-card">
      <div class="user-info">
        <div class="user-greeting">مرحباً،</div>
        <div class="user-name"><?= e($adminSidebarUserName) ?></div>
        <div class="user-role">لوحة تحكم الإدارة</div>
      </div>
    </div>

    <ul>
      <?php foreach ($adminSidebarItems as $item): ?>
        <li<?= $adminSidebarCurrent === $item['page'] ? ' class="active"' : '' ?>>
          <a href="<?= e($item['href']) ?>">
            <img src="<?= e($item['icon']) ?>" alt="" />
            <?= $item['label'] ?>
          </a>
          </li>
        <?php endforeach; ?>

        <li class="logout-item">
          <form method="post" action="../partials/logout-handler.php">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="logout" />

            <button class="logout-btn" type="submit">
              <i class="fa-solid fa-right-from-bracket"></i>
              <span>تسجيل الخروج</span>
            </button>
          </form>
        </li>
    </ul>

  </div>
</aside>

<div class="sidebar-overlay"></div>