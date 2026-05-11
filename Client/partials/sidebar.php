<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/client.php';

// Logout handler (kept in Database/auth.php, included via Database/client.php)
handle_logout_post('/That-Copy/Auth/login/index.php');

/**
 * Usage:
 *   <?php $clientSidebarActive = 'dashboard'; include __DIR__ . '/../partials/sidebar.php'; ?>
 *
 * Allowed values: dashboard|appointments|chat|tests|activities|notifications|profile
 */
$clientSidebarActive = $clientSidebarActive ?? '';

function client_sidebar_li_class(string $key, string $active): string
{
    return $key === $active ? ' class="active"' : '';
}
?>

<style>
  /* Keep sidebar usable across pages with different CSS bundles loaded */
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
    overflow: visible; /* scrolling handled by .sidebar-scroll */
  }

  /* Logout: same layout as other buttons, but red + right hover decoration */
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
    color: #e74c3c;
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
        <div class="user-name"><?= e(client_display_name()) ?></div>
        <div class="user-role">مريض</div>
      </div>
    </div>

    <ul>
      <li<?= client_sidebar_li_class('dashboard', (string) $clientSidebarActive) ?>>
        <a href="../client-dashboard-page/index.php">
          <img src="../images/Icon.svg" alt="" />
          الرئيسية
        </a>
      </li>

      <li<?= client_sidebar_li_class('appointments', (string) $clientSidebarActive) ?>>
        <a href="../client-appointments-page/appointments.php">
          <img src="../images/Icon1.svg" alt="" />
          المواعيد
        </a>
      </li>

      <li<?= client_sidebar_li_class('chat', (string) $clientSidebarActive) ?>>
        <a href="../client-chat-page/chat.php">
          <img src="../images/Icon2.svg" alt="" />
          الرسائل
        </a>
      </li>

      <li<?= client_sidebar_li_class('tests', (string) $clientSidebarActive) ?>>
        <a href="../client-tests-page/tests.php">
          <img src="../images/Icon3.svg" alt="" />
          الإختبارات
        </a>
      </li>

      <li<?= client_sidebar_li_class('activities', (string) $clientSidebarActive) ?>>
        <a href="../client-games-page/index.php">
          <img src="../images/Icon4.svg" alt="" />
          الأنشطة
        </a>
      </li>

      <li<?= client_sidebar_li_class('notifications', (string) $clientSidebarActive) ?>>
        <a href="../client-notifications-page/notifications.php">
          <img src="../images/Icon6.svg" alt="" />
          الإشعارات
        </a>
      </li>

      <li<?= client_sidebar_li_class('profile', (string) $clientSidebarActive) ?>>
        <a href="../client-profile-page/profile.php">
          <img src="../images/Icon7.svg" alt="" />
          الملف الشخصي
        </a>
      </li>

      <li class="logout-item">
        <form method="post" action="/That-Copy/Auth/handlers/logout.php">
          <?= csrf_input() ?>
          <input type="hidden" name="action" value="logout" />
          <button class="logout-btn" id="logoutBtn" type="submit" aria-label="تسجيل الخروج">
            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
            <span>تسجيل الخروج</span>
          </button>
        </form>
      </li>
    </ul>
  </div>
</aside>
<script>
  (() => {
    const dot = document.getElementById("notificationDot");
    if (!dot) return;

    fetch("/That-Copy/Client/client-notifications-page/notifications.php?action=badge_state", {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
      cache: "no-store",
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("badge_state_failed");
        }
        return response.json();
      })
      .then((payload) => {
        const hasUnread = Boolean(payload && payload.success && payload.has_unread);
        dot.style.display = hasUnread ? "block" : "none";
      })
      .catch(() => {
        // Keep server-rendered fallback state if request fails.
      });
  })();
</script>
